<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;


use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
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
        return $this->item->getItemInfo()->getTitle();
    }

    public function getDescription()
    {
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

    public function getPrice()
    {
        if ($this->hasListing()) {
            return $this->item->getOffers()->getListings()[0]->getPrice()->getAmount();
        }

        return null;
    }

    public function getPriceList()
    {

    }

    public function getPriceAmount()
    {

    }

    public function getCurrency()
    {

    }

    public function getCategory()
    {
        return $this->item->getItemInfo()->getClassifications()->getBinding()->getDisplayValue();
    }

    public function getCategoryMargin()
    {
        $marginCategories = array(
            'Kindle Edition'     => 10,
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
        return (count($this->item->getOffers()->getListings()) >= 1) ? 0 : 1;
    }

    public function isPrime()
    {
        if ($this->hasListing()) {
            return ($this->item->getOffers()->getListings()[0]->getDeliveryInfo()->getIsPrimeEligible() ? 1 : 0);
        }

        return 0;
    }

    protected function hasListing()
    {
        return (count($this->item->getOffers()->getListings()) > 0);
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
