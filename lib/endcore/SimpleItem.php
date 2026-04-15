<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2020
 *
 * Rewritten 2026: Migrated from Amazon PAAPI 5 SDK objects to Amazon Creators API
 * associative-array responses (Jakiboy/apaapi v2.0.5 vendored in lib/apaapi/).
 *
 * The constructor now takes a plain associative array (one item from the
 * Creators-API response). All public methods keep the same signature and
 * return-shape as the previous PAAPI-5 implementation.
 */

namespace Endcore;

class SimpleItem {

	/**
	 * Raw item array as returned by the Creators API
	 * (one element of $response['searchResult']['items']
	 * or $response['itemsResult']['items']).
	 *
	 * @var array
	 */
	private $item;

	/**
	 * @param array $item
	 */
	public function __construct( $item ) {
		$this->item = is_array( $item ) ? $item : array();
	}

	/* ===================================================================
	 * Internal array helper
	 * =================================================================== */

	/**
	 * Null-safe nested array accessor with dot-notation.
	 *
	 * @param mixed       $array
	 * @param string|null $path     Dot-separated key path, e.g. "itemInfo.title.displayValue".
	 *                              If null/empty, $array itself is returned.
	 * @param mixed       $default
	 *
	 * @return mixed
	 */
	protected function arrayGet( $array, $path = null, $default = null ) {
		if ( $path === null || $path === '' ) {
			return is_array( $array ) ? $array : $default;
		}

		if ( ! is_array( $array ) ) {
			return $default;
		}

		$segments = explode( '.', $path );
		$current  = $array;

		foreach ( $segments as $segment ) {
			if ( is_array( $current ) && array_key_exists( $segment, $current ) ) {
				$current = $current[ $segment ];
			} else {
				return $default;
			}
		}

		return $current;
	}

	/* ===================================================================
	 * Listing helpers (offersV2)
	 * =================================================================== */

	/**
	 * Return the relevant offers listing (Buy-Box winner preferred,
	 * otherwise the first listing). Matches old PAAPI-5 logic.
	 *
	 * @return array|null
	 */
	protected function getOffersV2Listing() {
		$listings = $this->arrayGet( $this->item, 'offersV2.listings' );

		if ( ! is_array( $listings ) || count( $listings ) === 0 ) {
			return null;
		}

		foreach ( $listings as $listing ) {
			if ( is_array( $listing ) && ! empty( $listing['isBuyBoxWinner'] ) ) {
				return $listing;
			}
		}

		$first = reset( $listings );
		return is_array( $first ) ? $first : null;
	}

	protected function hasListing() {
		$listings = $this->arrayGet( $this->item, 'offersV2.listings' );
		return is_array( $listings ) && count( $listings ) > 0;
	}

	/* ===================================================================
	 * External IDs
	 * =================================================================== */

	/**
	 * Get the first EAN. The Creators API exposes the field as "EANs"
	 * which after lowerCamelCase conversion becomes "eaNs". We also
	 * defensively check 'eans' and 'eAn' as fallbacks.
	 */
	public function getEAN() {
		$candidates = array(
			'itemInfo.externalIds.eaNs.displayValues',
			'itemInfo.externalIds.eans.displayValues',
			'itemInfo.externalIds.eAn.displayValues',
			'itemInfo.externalIds.EANs.displayValues',
		);

		foreach ( $candidates as $path ) {
			$values = $this->arrayGet( $this->item, $path );
			if ( is_array( $values ) && count( $values ) > 0 ) {
				return $values[0];
			}
		}

		return null;
	}

	// Note: PHP method names are case-insensitive, so getEAN() is also
	// callable as getEan() — both spellings used across the codebase work.

	public function getASIN() {
		return $this->arrayGet( $this->item, 'asin' );
	}

	/* ===================================================================
	 * Title / description / URL
	 * =================================================================== */

	public function getTitle() {
		$title = $this->arrayGet( $this->item, 'itemInfo.title.displayValue' );
		return $title !== null ? $title : '';
	}

	public function getDescription() {
		$values = $this->arrayGet( $this->item, 'itemInfo.features.displayValues' );

		if ( is_array( $values ) ) {
			$html = '<ul class="amazon-features">';
			foreach ( $values as $value ) {
				$escaped = function_exists( 'esc_html' )
					? esc_html( $value )
					: htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
				$html .= '<li>' . $escaped . '</li>';
			}
			$html .= '</ul>';
			return $html;
		}

		return $values !== null ? $values : '';
	}

	public function getUrl() {
		return $this->arrayGet( $this->item, 'detailPageURL' );
	}

	/* ===================================================================
	 * Price / currency
	 * =================================================================== */

	public function getUserPrice( $formatted = true ) {
		$listing = $this->getOffersV2Listing();
		if ( $listing === null ) {
			return false;
		}

		$money = $this->arrayGet( $listing, 'price.money' );
		if ( ! is_array( $money ) ) {
			return false;
		}

		if ( $formatted ) {
			$display = isset( $money['displayAmount'] ) ? $money['displayAmount'] : null;
			return $display !== null ? $display : false;
		}

		return isset( $money['amount'] ) ? $money['amount'] : false;
	}

	public function getPriceList() {
		$listing = $this->getOffersV2Listing();
		if ( $listing !== null ) {
			$amount = $this->arrayGet( $listing, 'price.savingBasis.money.amount' );
			if ( $amount !== null ) {
				return $amount;
			}
		}

		return 'kA';
	}

	public function getPriceAmount() {
		return $this->getUserPrice( false );
	}

	public function getCurrency() {
		$listing = $this->getOffersV2Listing();
		if ( $listing !== null ) {
			$currency = $this->arrayGet( $listing, 'price.money.currency' );
			if ( $currency !== null && $currency !== '' ) {
				return $currency;
			}
		}

		return 'EUR';
	}

	/* ===================================================================
	 * Category / margin
	 * =================================================================== */

	public function getCategory() {
		$binding = $this->arrayGet( $this->item, 'itemInfo.classifications.binding.displayValue' );
		return $binding !== null ? $binding : '';
	}

	public function getCategoryMargin() {
		$marginCategories = array(
			'Kindle Edition'     => 10,
			'Kindle Ausgabe'     => 10,
			'Gebundene Ausgabe'  => 10,
			'Broschiert'         => 10,
			'Taschenbuch'        => 10,
			'Wireless Phone'     => 1,
			'Elektronik'         => 3,
			'Gartenartikel'      => 7,
			'Haushaltswaren'     => 7,
			'Personal Computers' => 3,
			'DVD'                => 5,
			'Blu-ray'            => 5,
			'Software Download'  => 10,
			'Baumarkt'           => 5,
			'Werkzeug'           => 5,
			'Spielzeug'          => 5,
			'Uhr'                => 10,
			'Schuhe'             => 10,
			'Schmuck'            => 10,
			'Kleidung'           => 10,
			'Textilien'          => 10,
		);

		$category = $this->getCategory();
		if ( isset( $marginCategories[ $category ] ) ) {
			return $marginCategories[ $category ];
		}

		return 0;
	}

	/* ===================================================================
	 * External / Prime flags
	 * =================================================================== */

	public function isExternal() {
		return $this->hasListing() ? 0 : 1;
	}

	public function isPrime() {
		$listing = $this->getOffersV2Listing();
		if ( $listing === null ) {
			return 0;
		}

		// Merchant name "Amazon" => Prime fulfilled.
		$merchantName = $this->arrayGet( $listing, 'merchantInfo.name' );
		if ( is_string( $merchantName ) && stripos( $merchantName, 'Amazon' ) !== false ) {
			return 1;
		}

		// Listing type may come as string or as nested object/array.
		$type = isset( $listing['type'] ) ? $listing['type'] : null;
		if ( is_array( $type ) ) {
			$typeString = '';
			foreach ( $type as $value ) {
				if ( is_scalar( $value ) ) {
					$typeString .= ' ' . $value;
				}
			}
		} else {
			$typeString = is_scalar( $type ) ? (string) $type : '';
		}

		if ( $typeString !== '' && stripos( $typeString, 'prime' ) !== false ) {
			return 1;
		}

		return 0;
	}

	/* ===================================================================
	 * Images
	 * =================================================================== */

	protected function hasImages() {
		$images = $this->getImages();
		return is_array( $images ) && count( $images ) > 0;
	}

	/**
	 * Returns the raw "images" sub-array (containing 'primary' and 'variants').
	 *
	 * @return array|null
	 */
	public function getImages() {
		$images = $this->arrayGet( $this->item, 'images' );
		return is_array( $images ) ? $images : null;
	}

	public function getSmallImage() {
		$url = $this->arrayGet( $this->item, 'images.primary.small.url' );
		return $url !== null ? $url : null;
	}

	public function getAllSmallImages() {
		return $this->collectImageUrls( 'small' );
	}

	public function getAllMediumImages() {
		return $this->collectImageUrls( 'medium' );
	}

	public function getAllLargeImages() {
		return $this->collectImageUrls( 'large' );
	}

	/**
	 * @param string $size 'small'|'medium'|'large'
	 * @return string[]
	 */
	protected function collectImageUrls( $size ) {
		$urls = array();
		foreach ( $this->getAllImages() as $image ) {
			$url = $this->arrayGet( $image, $size . '.url' );
			if ( $url !== null ) {
				$urls[] = $url;
			}
		}
		return $urls;
	}

	/**
	 * Returns the primary image plus all variants (each as an associative array
	 * with 'small'/'medium'/'large' sub-arrays).
	 *
	 * @return array[]
	 */
	public function getAllImages() {
		$images = array();

		if ( ! $this->hasImages() ) {
			return $images;
		}

		$primary = $this->arrayGet( $this->item, 'images.primary' );
		if ( is_array( $primary ) ) {
			$images[] = $primary;
		}

		$variants = $this->arrayGet( $this->item, 'images.variants' );
		if ( is_array( $variants ) ) {
			foreach ( $variants as $variant ) {
				if ( is_array( $variant ) ) {
					$images[] = $variant;
				}
			}
		}

		return $images;
	}

	public function getExternalImages() {
		if ( ! $this->hasImages() ) {
			return array();
		}

		$size = function_exists( 'get_option' ) ? get_option( 'amazon_images_external_size' ) : '';
		if ( ! $size ) {
			$size = 'SmallImage';
		}

		if ( $size === 'SmallImage' ) {
			return $this->getAllSmallImages();
		}
		if ( $size === 'MediumImage' ) {
			return $this->getAllMediumImages();
		}
		if ( $size === 'LargeImage' ) {
			return $this->getAllLargeImages();
		}

		return array();
	}

	/* ===================================================================
	 * Sales rank / attributes / technical info
	 * =================================================================== */

	public function getSalesRank() {
		$rank = $this->arrayGet( $this->item, 'browseNodeInfo.websiteSalesRank.salesRank' );
		return $rank !== null ? $rank : 0;
	}

	public function getAttributes() {
		$attributes          = array();
		$attributes['Title'] = $this->getTitle();

		$this->setProductInfo( $attributes );
		$this->setReleaseDate( $attributes );
		$this->setSize( $attributes );

		return $attributes;
	}

	public function getTechnicalInfo() {
		$formats = $this->arrayGet( $this->item, 'itemInfo.technicalInfo.formats' );
		if ( ! is_array( $formats ) ) {
			return null;
		}

		$displayValues = isset( $formats['displayValues'] ) && is_array( $formats['displayValues'] )
			? $formats['displayValues']
			: array();

		return array(
			'key'   => isset( $formats['label'] ) ? $formats['label'] : '',
			'value' => implode( ' ', $displayValues ),
		);
	}

	/* ===================================================================
	 * Customer reviews (new in Creators API)
	 * =================================================================== */

	/**
	 * @return float|null
	 */
	public function getAverageRating() {
		$value = $this->arrayGet( $this->item, 'customerReviews.starRating.value' );
		if ( $value === null || $value === '' ) {
			return null;
		}
		return (float) $value;
	}

	/**
	 * @return int|null
	 */
	public function getTotalReviews() {
		$count = $this->arrayGet( $this->item, 'customerReviews.count' );
		if ( $count === null || $count === '' ) {
			return null;
		}
		return (int) $count;
	}

	/* ===================================================================
	 * Internal attribute fillers (used by getAttributes)
	 * =================================================================== */

	protected function setProductInfo( &$attributes ) {
		$productInfo = $this->arrayGet( $this->item, 'itemInfo.productInfo' );
		if ( ! is_array( $productInfo ) ) {
			return;
		}

		$color = isset( $productInfo['color'] ) ? $productInfo['color'] : null;
		if ( is_array( $color ) && isset( $color['displayValue'] ) ) {
			$label = isset( $color['label'] ) ? $color['label'] : 'Color';
			$attributes[ $label ] = $color['displayValue'];
		}

		$dimensions = isset( $productInfo['itemDimensions'] ) ? $productInfo['itemDimensions'] : null;
		if ( is_array( $dimensions ) ) {
			foreach ( array( 'height', 'length', 'weight', 'width' ) as $dimKey ) {
				$dim = isset( $dimensions[ $dimKey ] ) ? $dimensions[ $dimKey ] : null;
				if ( is_array( $dim ) && isset( $dim['label'], $dim['displayValue'] ) ) {
					$unit = isset( $dim['unit'] ) ? ' ' . $dim['unit'] : '';
					$attributes[ $dim['label'] ] = $dim['displayValue'] . $unit;
				}
			}
		}
	}

	protected function setReleaseDate( &$attributes ) {
		$release = $this->arrayGet( $this->item, 'itemInfo.productInfo.releaseDate' );
		if ( is_array( $release ) && isset( $release['label'], $release['displayValue'] ) ) {
			$attributes[ $release['label'] ] = $release['displayValue'];
		}
	}

	protected function setSize( &$attributes ) {
		$size = $this->arrayGet( $this->item, 'itemInfo.productInfo.size' );
		if ( is_array( $size ) && isset( $size['label'], $size['displayValue'] ) ) {
			$attributes[ $size['label'] ] = $size['displayValue'];
		}
	}
}
