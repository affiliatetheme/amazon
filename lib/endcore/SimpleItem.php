<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2020
 *
 * Updated December 2024: Migrated to OffersV2 API using Wirecutter SDK
 */

namespace Endcore;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ImageType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferListingV2;

class SimpleItem {
	/**
	 * @var Item
	 */
	private $item;

	public function __construct( Item $item ) {
		$this->item = $item;
	}

	/**
	 * Get the first OffersV2 listing (Buy Box winner or first available)
	 * @return OfferListingV2|null
	 */
	protected function getOffersV2Listing() {
		if ( $this->item->getOffersV2() === null ) {
			return null;
		}

		$listings = $this->item->getOffersV2()->getListings();
		if ( $listings === null || count( $listings ) === 0 ) {
			return null;
		}

		// Try to find the Buy Box winner first
		foreach ( $listings as $listing ) {
			if ( $listing->getIsBuyBoxWinner() === true ) {
				return $listing;
			}
		}

		// Otherwise return the first listing
		return $listings[0];
	}

	public function getEAN() {
		if ( $this->item->getItemInfo() === null || $this->item->getItemInfo()->getExternalIds() === null ) {
			return null;
		}

		if ( $this->item->getItemInfo()->getExternalIds()->getEANs() === null ) {
			return null;
		}

		$values = $this->item->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues();

		if ( is_array( $values ) && count( $values ) > 0 ) {
			return $values[0];
		}

		return null;
	}

	public function getASIN() {
		return $this->item->getASIN();
	}

	public function getTitle() {
		if ( $this->item->getItemInfo() === null || $this->item->getItemInfo()->getTitle() === null ) {
			return '';
		}
		return $this->item->getItemInfo()->getTitle()->getDisplayValue();
	}

	public function getDescription() {
		if ( $this->item->getItemInfo() === null || $this->item->getItemInfo()->getFeatures() === null ) {
			return '';
		}

		$values = $this->item->getItemInfo()->getFeatures()->getDisplayValues();

		if ( is_array( $values ) ) {
			$html = '<ul class="amazon-features">';
			foreach ( $values as $value ) {
				$html .= '<li>' . $value . '</li>';
			}
			$html .= '</ul>';

			return $html;
		}

		return $values;
	}

	public function getUrl() {
		return $this->item->getDetailPageURL();
	}

	public function getUserPrice( $formatted = true ) {
		if ( ! $this->item ) {
			return '';
		}

		$listing = $this->getOffersV2Listing();

		if ( $listing === null ) {
			return false;
		}

		$price = $listing->getPrice();
		if ( $price === null ) {
			return false;
		}

		// OffersV2 uses Money object for price data
		$money = $price->getMoney();
		if ( $money === null ) {
			return false;
		}

		if ( $formatted ) {
			return $money->getDisplayAmount();
		} else {
			return $money->getAmount();
		}
	}

	public function getPriceList() {
		if ( ! $this->item ) {
			return '';
		}

		$listing = $this->getOffersV2Listing();
		if ( $listing !== null && $listing->getPrice() !== null ) {
			$price = $listing->getPrice();
			// Use savingBasis for the original/list price
			if ( $price->getSavingBasis() !== null && $price->getSavingBasis()->getMoney() !== null ) {
				return $price->getSavingBasis()->getMoney()->getAmount();
			}
		}

		return 'kA';
	}

	public function getPriceAmount() {
		return $this->getUserPrice( false );
	}

	public function getCurrency() {
		if ( ! $this->item ) {
			return '';
		}

		$listing = $this->getOffersV2Listing();
		if ( $listing !== null && $listing->getPrice() !== null ) {
			$money = $listing->getPrice()->getMoney();
			if ( $money !== null ) {
				$currency = $money->getCurrency();
				return $currency !== null ? $currency : 'EUR';
			}
		}

		return 'EUR';
	}

	public function getCategory() {
		if ( $this->item->getItemInfo() === null ||
		     $this->item->getItemInfo()->getClassifications() === null ||
		     $this->item->getItemInfo()->getClassifications()->getBinding() === null ) {
			return '';
		}
		return $this->item->getItemInfo()->getClassifications()->getBinding()->getDisplayValue();
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
			'Textilien'          => 10
		);

		if ( in_array( $this->getCategory(), array_keys( $marginCategories ) ) ) {
			return $marginCategories[ $this->getCategory() ];
		}

		return 0;
	}

	public function isExternal() {
		$listings = $this->item->getOffersV2() !== null ? $this->item->getOffersV2()->getListings() : null;
		if ( $listings === null || count( $listings ) === 0 ) {
			return 1;
		}
		return 0;
	}

	public function isPrime() {
		$listing = $this->getOffersV2Listing();

		if ( $listing !== null && $listing->getMerchantInfo() !== null ) {
			// Check by merchant name for Amazon
			$merchantName = $listing->getMerchantInfo()->getName();
			if ( $merchantName !== null && stripos( $merchantName, 'Amazon' ) !== false ) {
				return 1;
			}
		}

		// Check listing type for Prime indicator
		if ( $listing !== null && $listing->getType() !== null ) {
			$type = $listing->getType();
			// OfferType is an object, get its string value
			$typeString = is_object( $type ) ? (string)$type : $type;
			if ( stripos( $typeString, 'prime' ) !== false ) {
				return 1;
			}
		}

		return 0;
	}

	protected function hasListing() {
		if ( $this->item->getOffersV2() === null ) {
			return false;
		}
		$listings = $this->item->getOffersV2()->getListings();
		return ( $listings !== null && count( $listings ) > 0 );
	}

	protected function hasImages() {
		if ( $this->getImages() === null ) {
			return 0;
		}

		if ( is_array( $this->getImages() ) || is_object( $this->getImages() ) ) {
			return true;
		}

		return 0;
	}

	/**
	 * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Images
	 */
	public function getImages() {
		return $this->item->getImages();
	}

	public function getSmallImage() {
		if ( $this->hasImages() && $this->getImages()->getPrimary() !== null ) {
			$small = $this->getImages()->getPrimary()->getSmall();
			return $small !== null ? $small->getURL() : null;
		}

		return null;
	}

	public function getAllSmallImages() {
		$images = [];

		foreach ( $this->getAllImages() as $image ) {
			if ( $image !== null && $image->getSmall() !== null ) {
				$images[] = $image->getSmall()->getURL();
			}
		}

		return $images;
	}

	public function getAllMediumImages() {
		$images = [];

		foreach ( $this->getAllImages() as $image ) {
			if ( $image !== null && $image->getMedium() !== null ) {
				$images[] = $image->getMedium()->getURL();
			}
		}

		return $images;
	}

	public function getAllLargeImages() {
		$images = [];

		foreach ( $this->getAllImages() as $image ) {
			if ( $image !== null && $image->getLarge() !== null ) {
				$images[] = $image->getLarge()->getURL();
			}
		}

		return $images;
	}

	/**
	 * @return ImageType[]
	 */
	public function getAllImages() {
		$images = [];

		if ( $this->hasImages() ) {
			$images[] = $this->getImages()->getPrimary();

			if ( $this->getImages()->getVariants() !== null ) {
				foreach ( $this->getImages()->getVariants() as $variant ) {
					$images[] = $variant;
				}
			}
		}

		return $images;
	}

	public function getExternalImages() {
		if ( $this->hasImages() ) {
			$size = ( get_option( 'amazon_images_external_size' ) ? get_option( 'amazon_images_external_size' ) : 'SmallImage' );

			if ( $size == 'SmallImage' ) {
				return $this->getAllSmallImages();
			}

			if ( $size == 'MediumImage' ) {
				return $this->getAllMediumImages();
			}

			if ( $size == 'LargeImage' ) {
				return $this->getAllLargeImages();
			}
		}

		return [];
	}

	public function getSalesRank() {
		if ( $this->item->getBrowseNodeInfo() !== null && $this->item->getBrowseNodeInfo()->getWebsiteSalesRank() !== null ) {
			return $this->item->getBrowseNodeInfo()->getWebsiteSalesRank()->getSalesRank();
		}

		return 0;
	}

	public function getAttributes() {
		$attributes = array();
		$attributes['Title'] = $this->getTitle();
		return $attributes;
	}

	public function getTechnicalInfo() {
		if ( $this->item->getItemInfo() !== null && $this->item->getItemInfo()->getTechnicalInfo() !== null ) {
			$techInfo = $this->item->getItemInfo()->getTechnicalInfo();
			if ( $techInfo->getFormats() !== null ) {
				return array(
					'key'   => $techInfo->getFormats()->getLabel(),
					'value' => implode( ' ', $techInfo->getFormats()->getDisplayValues() )
				);
			}
		}
		return null;
	}

	protected function setProductInfo( &$attributes ) {
		if ( $this->item->getItemInfo() === null || $this->item->getItemInfo()->getProductInfo() === null ) {
			return;
		}

		$productInfo = $this->item->getItemInfo()->getProductInfo();

		if ( $productInfo->getColor() !== null ) {
			$color = $productInfo->getColor();
			$attributes[ $color->getLabel() ] = $color->getDisplayValue();
		}

		if ( $productInfo->getItemDimensions() !== null ) {
			$dimensions = $productInfo->getItemDimensions();
			if ( $dimensions->getHeight() !== null ) {
				$attributes[ $dimensions->getHeight()->getLabel() ] = $dimensions->getHeight()->getDisplayValue() . ' ' . $dimensions->getHeight()->getUnit();
			}
			if ( $dimensions->getLength() !== null ) {
				$attributes[ $dimensions->getLength()->getLabel() ] = $dimensions->getLength()->getDisplayValue() . ' ' . $dimensions->getLength()->getUnit();
			}
			if ( $dimensions->getWeight() !== null ) {
				$attributes[ $dimensions->getWeight()->getLabel() ] = $dimensions->getWeight()->getDisplayValue() . ' ' . $dimensions->getWeight()->getUnit();
			}
			if ( $dimensions->getWidth() !== null ) {
				$attributes[ $dimensions->getWidth()->getLabel() ]  = $dimensions->getWidth()->getDisplayValue() . ' ' . $dimensions->getWidth()->getUnit();
			}
		}
	}

	protected function setReleaseDate( &$attributes ) {
		if ( $this->item->getItemInfo() === null ||
		     $this->item->getItemInfo()->getProductInfo() === null ||
		     $this->item->getItemInfo()->getProductInfo()->getReleaseDate() === null ) {
			return;
		}
		$release = $this->item->getItemInfo()->getProductInfo()->getReleaseDate();
		$attributes[ $release->getLabel() ] = $release->getDisplayValue();
	}

	protected function setSize( &$attributes ) {
		if ( $this->item->getItemInfo() === null ||
		     $this->item->getItemInfo()->getProductInfo() === null ||
		     $this->item->getItemInfo()->getProductInfo()->getSize() === null ) {
			return;
		}
		$size = $this->item->getItemInfo()->getProductInfo()->getSize();
		$attributes[ $size->getLabel() ] = $size->getDisplayValue();
	}
}
