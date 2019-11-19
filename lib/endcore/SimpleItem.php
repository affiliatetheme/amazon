<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;


use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ImageType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferListing;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferSummary;
use ApaiIO\Helper\DotDotText;

class SimpleItem
{
    /**
     * @var Item
     */
    private $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getEAN()
    {
        if ($this->item->getItemInfo()->getExternalIds() === null) {
            return null;
        }

        $values = $this->item->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues();
        if (is_array($values) && count($values) > 0) {
            return $values[0];
        }

        return null;
    }

    public function getASIN()
    {
        return $this->item->getASIN();
    }

    public function getTitle()
    {
        return $this->item->getItemInfo()->getTitle()->getDisplayValue();
    }

    public function getDescription()
    {
        if ($this->item->getItemInfo()->getFeatures() === null) {
            return '';
        }

        $values = $this->item->getItemInfo()->getFeatures()->getDisplayValues();
        if (is_array($values)) {
            $values = implode(', ', $values);
        }
        return DotDotText::truncate($values);
    }

    public function getUrl()
    {
        return $this->item->getDetailPageURL();
    }

    public function getUserPrice()
    {
        if ( ! $this->item ) {
            return '';
        }

        if ( ! $this->item->getOffers() ) {
            return '';
        }

        /** @var OfferSummary[] $offers */
        $offers = [];

        foreach ($this->item->getOffers()->getSummaries() as $offer) {
            $offers[strtolower($offer->getCondition()->getValue())] = $offer;
        }

	    if ( key_exists( AWS_PRICE, $offers ) ) {
		    return $offers[AWS_PRICE]->getLowestPrice()->getDisplayAmount();
	    }

	    if ( key_exists( 'new', $offers ) ) {
		    return $offers['new']->getLowestPrice()->getDisplayAmount();
	    }

	    return false;
    }

    public function getPriceList()
    {
        if (!$this->item) {
            return '';
        }

        if ( ! $this->item->getOffers() ) {
            return '';
        }

        /** @var OfferListing[] $offers */
        $offers = [];

        foreach ($this->item->getOffers()->getListings() as $offer) {
            $offers[strtolower($offer->getCondition()->getValue())] = $offer;
        }

        $condition = $this->getAwsPriceCondition();
        if (key_exists($condition, $offers) && $offers[$condition]->getSavingBasis() !== null) {
            return $offers[$condition]->getSavingBasis()->getAmount();
        }

        return 'kA';
    }

    // getAmountForAvailability
    public function getPriceAmount()
    {
        if (!$this->item) {
            return '';
        }

        if ( ! $this->item->getOffers() ) {
            return '';
        }

        /** @var OfferSummary[] $offers */
        $offers = [];

        foreach ($this->item->getOffers()->getSummaries() as $offer) {
            $offers[strtolower($offer->getCondition()->getValue())] = $offer;
        }

        if (key_exists($this->getAwsPriceCondition(), $offers)) {
            return $offers[$this->getAwsPriceCondition()]->getLowestPrice()->getAmount();
        }

        if (!array_key_exists('new', $offers)) {
            return '';
        }

        return $offers['new']->getLowestPrice()->getAmount();
    }

    public function getCurrency()
    {
        if (!$this->item) {
            return '';
        }

        if ( ! $this->item->getOffers() ) {
            return '';
        }

        /** @var OfferSummary[] $offers */
        $offers = [];

        foreach ($this->item->getOffers()->getSummaries() as $offer) {
            $offers[strtolower($offer->getCondition()->getValue())] = $offer;
        }

        if (key_exists($this->getAwsPriceCondition(), $offers)) {
            return $offers[$this->getAwsPriceCondition()]->getLowestPrice()->getCurrency();
        }

        return 'EUR';
    }

    public function getCategory()
    {
        if ($this->item->getItemInfo()->getClassifications()->getBinding() !== null) {
            return $this->item->getItemInfo()->getClassifications()->getBinding()->getDisplayValue();
        }

        return '';
    }

    public function getCategoryMargin()
    {
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

        if (in_array($this->getCategory(), array_keys($marginCategories))) {
            return $marginCategories[$this->getCategory()];
        }

        return 0;
    }

    public function isExternal()
    {
        if ( ! $this->item->getOffers() ) {
            return '';
        }
        
        return (count($this->item->getOffers()->getListings()) >= 1) ? 0 : 1;
    }

    public function isPrime()
    {
        if ( ! $this->item->getOffers() ) {
            return '';
        }

        if ($this->hasListing()) {
            return ($this->item->getOffers()->getListings()[0]->getDeliveryInfo()->getIsPrimeEligible() ? 1 : 0);
        }

        return 0;
    }

    protected function hasListing()
    {
        if ( ! $this->item->getOffers() ) {
            return '';
        }

        return (count($this->item->getOffers()->getListings()) > 0);
    }

    protected function hasSummaries()
    {
        if (!$this->item) {
            return '';
        }

        if ( ! $this->item->getOffers() ) {
            return '';
        }

        return (count($this->item->getOffers()->getSummaries()) > 0);
    }

    protected function hasImages()
    {
        return count($this->getImages()) > 0;
    }

    /**
     * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Images
     */
    public function getImages()
    {
        return $this->item->getImages();
    }

    public function getSmallImage()
    {
        if ($this->hasImages()) {
            return $this->getImages()->getPrimary()->getSmall()->getURL();
        }

        return null;
    }

    public function getAllSmallImages()
    {
        $images = [];

        foreach ($this->getAllImages() as $image) {
            $images[] = $image->getSmall()->getURL();
        }

        return $images;
    }

    public function getAllMediumImages()
    {
        $images = [];

        foreach ($this->getAllImages() as $image) {
            $images[] = $image->getMedium()->getURL();
        }

        return $images;
    }

    public function getAllLargeImages()
    {
        $images = [];

        foreach ($this->getAllImages() as $image) {
            $images[] = $image->getLarge()->getURL();
        }

        return $images;
    }

    /**
     * @return ImageType[]
     */
    public function getAllImages()
    {
        $images = [];

        if ($this->hasImages()) {
            $images[] = $this->getImages()->getPrimary();

            if ($this->getImages()->getVariants() !== null) {
                foreach ($this->getImages()->getVariants() as $variant) {
                    $images[] = $variant;
                }
            }
        }

        return $images;
    }

    public function getExternalImages()
    {
        if ($this->hasImages()) {
            $size = (get_option('amazon_images_external_size') ? get_option('amazon_images_external_size') : 'SmallImage');

            if ($size == 'SmallImage') {
                return $this->getAllSmallImages();
            }

            if ($size == 'MediumImage') {
                return $this->getAllMediumImages();
            }

            if ($size == 'LargeImage') {
                return $this->getAllLargeImages();
            }
        }

        return [];
    }

    protected function getAwsPriceCondition()
    {
        if (AWS_PRICE === 'default') {
            return 'new';
        }

        return AWS_PRICE;
    }

    public function getSalesRank()
    {
        if ($this->item->getBrowseNodeInfo()->getWebsiteSalesRank() !== null) {
            return $this->item->getBrowseNodeInfo()->getWebsiteSalesRank()->getSalesRank();
        }

        return 0;
    }

    public function getAttributes()
    {
        $attributes = array();

        $attributes['Title'] = $this->getTitle();

        /**
         * 2019-10-29 Christian
         * Removed not used attributes, throws an 500

        if ($this->item->getItemInfo()->getProductInfo() !== null) {
            $this->setProductInfo($attributes);
            $this->setReleaseDate($attributes);
            $this->setSize($attributes);
        } */

        return $attributes;
    }

    public function getTechnicalInfo()
    {
        if ($this->item->getItemInfo()->getTechnicalInfo() !== null) {
            return array(
                'key'   => $this->item->getItemInfo()->getTechnicalInfo()->getFormats()->getLabel(),
                'value' => implode(' ', $this->item->getItemInfo()->getTechnicalInfo()->getFormats()->getDisplayValues())
            );
        }
    }

    protected function setProductInfo(&$attributes)
    {

        if ($this->item->getItemInfo()->getProductInfo()->getColor() !== null) {
            $color = $this->item->getItemInfo()->getProductInfo()->getColor();
            $attributes[$color->getLabel()] = $color->getDisplayValue();
        }

        if ($this->item->getItemInfo()->getProductInfo()->getItemDimensions() !== null) {
            $dimensions = $this->item->getItemInfo()->getProductInfo()->getItemDimensions();
            $attributes[$dimensions->getHeight()->getLabel()] = $dimensions->getHeight()->getDisplayValue() . ' ' . $dimensions->getHeight()->getUnit();
            $attributes[$dimensions->getLength()->getLabel()] = $dimensions->getLength()->getDisplayValue() . ' ' . $dimensions->getLength()->getUnit();
            $attributes[$dimensions->getWeight()->getLabel()] = $dimensions->getWeight()->getDisplayValue() . ' ' . $dimensions->getWeight()->getUnit();
            $attributes[$dimensions->getWidth()->getLabel()] = $dimensions->getWidth()->getDisplayValue() . ' ' . $dimensions->getWidth()->getUnit();
        }
    }

    protected function setReleaseDate(&$attributes)
    {
        if ($this->item->getItemInfo()->getProductInfo()->getReleaseDate() !== null) {
            $release = $this->item->getItemInfo()->getProductInfo()->getReleaseDate();
            $attributes[$release->getLabel()] = $release->getDisplayValue();
        }
    }

    protected function setSize(&$attributes)
    {
        if ($this->item->getItemInfo()->getProductInfo()->getSize() !== null) {
            $size = $this->item->getItemInfo()->getProductInfo()->getSize();
            $attributes[$size->getLabel()] = $size->getDisplayValue();
        }
    }


    /*
     *   'ean' => $singleItem->getParentASIN() . $singleItem->getItemInfo()->get,
        'asin' => $singleItem->getASIN(),
        'title' => $singleItem->getItemInfo()->getTitle(),
        'description' => DotDotText::truncate($singleItem->getItemInfo()->getFeatures()->getDisplayValues()),
        'url' => $data['url'] = $singleItem->getDetailPageURL(),
        'price' => $data['price'] = $singleItem->getOffers()->getListings()[0]->getPrice()->getAmount(),
        'price' => $data['price'] = $singleItem->getUserFormattedPrice(),
        'price_list' => ($singleItem->getFormattedListPrice() ? $singleItem->getFormattedListPrice() : 'kA'),
        'price_amount' => $singleItem->getAmountForAvailability(),
        'currency' => ($singleItem->getOffers()->getListings()[0]->getPrice()->getCurrency() ? $singleItem->getOffers()->getListings()[0]->getPrice()->getCurrency() : 'EUR'),
        'category' => $singleItem->getItemInfo()->getClassifications()->getBinding()->getDisplayValue(),
        'category_margin' => $singleItem->getMarginForBinding(),
        'external' => (count($singleItem->getOffers()->getListings()) >= 1) ? 0 : 1,
        'prime' => ($singleItem->getOffers()->getListings()[0]->getDeliveryInfo()->getIsPrimeEligible() ? 1 : 0),
        'exists' => 'false'
     */

}
