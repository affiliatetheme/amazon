<?php
/**
 * Project: ama
 * (c) 2014-2015 Giacomo Barbalinardo <info@ready24it.eu>
 * Date: 31.03.2015
 * Time: 23:03
 */

namespace ApaiIO\Zend\Service\Amazon;

/**
 * Class ItemVariantSet
 * Represent now the VariationSummary.
 *
 * @package ApaiIO\Zend\Service\Amazon
 */
class ItemVariantSet {

    /**
     * @var int
     */
    public $LowestPrice;

    /**
     * @var string
     */
    public $LowestFormattedPrice;

    /**
     * @var string
     */
    public $LowestPriceCurrency;

    /**
     * @var int
     */
    public $HighestPrice;

    /**
     * @var string
     */
    public $HighestFormattedPrice;

    /**
     * @var string
     */
    public $HighestPriceCurrency;

    /**
     * @param \DOMElement $dom
     */
    public function __construct(\DOMElement $dom)
    {
        $xpath = new \DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');

        $variation = $xpath->query('./az:VariationSummary', $dom);

//        var_dump($variation->item(0)->C14N());die;

        if ($variation->length == 1) {
            $lowestPrice = $xpath->query('./az:VariationSummary/az:LowestPrice/az:Amount', $dom);
            if ($lowestPrice->length == 1) {
                $this->LowestPrice = (int) $xpath->query('./az:VariationSummary/az:LowestPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestFormattedPrice = (string) $xpath->query('./az:VariationSummary/az:LowestPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->LowestPriceCurrency = (string) $xpath->query('./az:VariationSummary/az:LowestPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }

            $highestPrice = $xpath->query('./az:VariationSummary/az:HighestPrice/az:Amount', $dom);
            if ($highestPrice->length == 1) {
                $this->HighestPrice = (int) $xpath->query('./az:VariationSummary/az:HighestPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->HighestFormattedPrice = (string) $xpath->query('./az:VariationSummary/az:HighestPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->HighestPriceCurrency = (string) $xpath->query('./az:VariationSummary/az:HighestPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
        }
    }
}