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

class Offer
{
    /**
     * @var string
     */
    public $MerchantId;

    /**
     * @var string
     */
    public $MerchantName;

    /**
     * @var string
     */
    public $GlancePage;

    /**
     * @var string
     */
    public $Condition;

    /**
     * @var string
     */
    public $OfferListingId;

    /**
     * @var string
     */
    public $Price;

    /**
     * @var string
     */
    public $SalesPrice;

    /**
     * @var string
     */
    public $CurrencyCode;

    /**
     * @var string
     */
    public $SalesCurrencyCode;

    /**
     * @var string
     */
    public $FormattedPrice;

    /**
     * @var string
     */
    public $FormattedSalesPrice;

    /**
     * @var string
     */
    public $Availability;

    /**
     * @var boolean
     */
    public $IsEligibleForSuperSaverShipping = false;

    /**
     * Parse the given Offer element
     *
     * @param  \DOMElement $dom
     */
    public function __construct(\DOMElement $dom)
    {
        //var_dump($dom->ownerDocument->saveHTML());die;

        $xpath = new \DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $merchantId = $xpath->query('./az:Merchant/az:MerchantId/text()', $dom);
        if ($merchantId->length == 1) {
            $this->MerchantId = (string) $merchantId->item(0)->data;
        }
        $name = $xpath->query('./az:Merchant/az:Name/text()', $dom);
        if ($name->length == 1) {
          $this->MerchantName = (string) $name->item(0)->data;
        }
        $glancePage = $xpath->query('./az:Merchant/az:GlancePage/text()', $dom);
        if ($glancePage->length == 1) {
            $this->GlancePage = (string) $glancePage->item(0)->data;
        }
        $this->Condition = (string) $xpath->query('./az:OfferAttributes/az:Condition/text()', $dom)->item(0)->data;
        $this->OfferListingId = (string) $xpath->query('./az:OfferListing/az:OfferListingId/text()', $dom)->item(0)->data;
        $Price = $xpath->query('./az:OfferListing/az:Price/az:Amount', $dom);
        if ($Price->length == 1) {
            $this->Price = (int) $xpath->query('./az:OfferListing/az:Price/az:Amount/text()', $dom)->item(0)->data;
            $this->CurrencyCode = (string) $xpath->query('./az:OfferListing/az:Price/az:CurrencyCode/text()', $dom)->item(0)->data;
        }
        $FormattedPrice = $xpath->query('./az:OfferListing/az:Price/az:FormattedPrice', $dom);
        if ($FormattedPrice->length == 1) {
            $this->FormattedPrice = (string) $xpath->query('./az:OfferListing/az:Price/az:FormattedPrice/text()', $dom)->item(0)->data;
        }
        $SalesPrice = $xpath->query('./az:OfferListing/az:SalePrice/az:Amount', $dom);
        if ($SalesPrice->length == 1) {
            $this->SalesPrice = (int) $xpath->query('./az:OfferListing/az:SalePrice/az:Amount/text()', $dom)->item(0)->data;
            $this->SalesCurrencyCode = (string) $xpath->query('./az:OfferListing/az:SalePrice/az:CurrencyCode/text()', $dom)->item(0)->data;
        }
        $FormattedSalesPrice = $xpath->query('./az:OfferListing/az:SalePrice/az:FormattedPrice', $dom);
        if ($FormattedSalesPrice->length == 1) {
            $this->FormattedSalesPrice = (string) $xpath->query('./az:OfferListing/az:SalePrice/az:FormattedPrice/text()', $dom)->item(0)->data;
        } else {
            $this->FormattedSalesPrice = $this->FormattedPrice;
        }

        $availability = $xpath->query('./az:OfferListing/az:Availability/text()', $dom)->item(0);
        if($availability instanceof \DOMText) {
            $this->Availability = (string) $availability->data;
        }
        $result = $xpath->query('./az:OfferListing/az:IsEligibleForSuperSaverShipping/text()', $dom);
        if ($result->length >= 1) {
            $this->IsEligibleForSuperSaverShipping = (bool) $result->item(0)->data;
        }
    }
}
