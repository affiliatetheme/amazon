<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ApaiIO\Zend\Service\Amazon;

class OfferSet
{
    /**
     * @var string
     */
    public $LowestNewPrice;

    /**
     * @var string
     */
    public $LowestNewFormattedPrice;

    /**
     * @var string
     */
    public $LowestNewPriceCurrency;

    /**
     * @var string
     */
    public $LowestUsedPrice;

    /**
     * @var string
     */
    public $LowestUsedFormattedPrice;

    /**
     * @var string
     */
    public $LowestUsedPriceCurrency;

    /**
     * @var string
     */
    public $LowestCollectiblePrice;

    /**
     * @var string
     */
    public $LowestCollectibleFormattedPrice;

    /**
     * @var string
     */
    public $LowestCollectiblePriceCurrency;

    /**
     * @var string
     */
    public $LowestRefurbishedPrice;

    /**
     * @var string
     */
    public $LowestRefurbishedFormattedPrice;

    /**
     * @var string
     */
    public $LowestRefurbishedPriceCurrency;




    /**
     * @var int
     */
    public $TotalNew;

    /**
     * @var int
     */
    public $TotalUsed;

    /**
     * @var int
     */
    public $TotalCollectible;

    /**
     * @var int
     */
    public $TotalRefurbished;

    /**
     * @var Offer[]
     */
    public $Offers;

    /**
     * Parse the given Offer Set Element
     *
     * @param  \DOMElement $dom
     */
    public function __construct(\DOMElement $dom)
    {
        $xpath = new \DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');

        $offer = $xpath->query('./az:OfferSummary', $dom);
        if ($offer->length == 1) {
            $lowestNewPrice = $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:Amount', $dom);
            if ($lowestNewPrice->length == 1) {
                $this->LowestNewPrice = (int) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestNewFormattedPrice = (string) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->LowestNewPriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $lowestUsedPrice = $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:Amount', $dom);
            if ($lowestUsedPrice->length == 1) {
                $this->LowestUsedPrice = (int) $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestUsedFormattedPrice = (string) $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->LowestUsedPriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $lowestCollectiblePrice = $xpath->query('./az:OfferSummary/az:LowestCollectiblePrice/az:Amount', $dom);
            if ($lowestCollectiblePrice->length == 1) {
                $this->LowestCollectiblePrice = (int) $xpath->query('./az:OfferSummary/az:LowestCollectiblePrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestCollectibleFormattedPrice = (string) $xpath->query('./az:OfferSummary/az:LowestCollectiblePrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->LowestCollectiblePriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestCollectiblePrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $lowestRefurbishedPrice = $xpath->query('./az:OfferSummary/az:LowestRefurbishedPrice/az:Amount', $dom);
            if ($lowestRefurbishedPrice->length == 1) {
                $this->LowestRefurbishedPrice = (int) $xpath->query('./az:OfferSummary/az:LowestRefurbishedPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestRefurbishedFormattedPrice = (string) $xpath->query('./az:OfferSummary/az:LowestRefurbishedPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
                $this->LowestRefurbishedPriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestRefurbishedPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $this->TotalNew = (int) $xpath->query('./az:OfferSummary/az:TotalNew/text()', $dom)->item(0)->data;
            $this->TotalUsed = (int) $xpath->query('./az:OfferSummary/az:TotalUsed/text()', $dom)->item(0)->data;
            $this->TotalCollectible = (int) $xpath->query('./az:OfferSummary/az:TotalCollectible/text()', $dom)->item(0)->data;
            $this->TotalRefurbished = (int) $xpath->query('./az:OfferSummary/az:TotalRefurbished/text()', $dom)->item(0)->data;
        }
        $offers = $xpath->query('./az:Offers/az:Offer', $dom);
        if ($offers->length >= 1) {
            /**
             * @see Offer
             */
            foreach ($offers as $offer) {
                $this->Offers[] = new Offer($offer);
            }
        }
    }
}
