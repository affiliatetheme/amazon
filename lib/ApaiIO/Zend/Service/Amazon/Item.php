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

class Item
{
    /**
     * @var string
     */
    public $ASIN;

    /**
     * @var string
     */
    public $DetailPageURL;

    /**
     * @var int
     */
    public $SalesRank;

    /**
     * @var int
     */
    public $TotalReviews;

    /**
     * @var int
     */
    public $AverageRating;

    /**
     * @var string
     */
    public $SmallImage;

    /**
     * @var string
     */
    public $MediumImage;

    /**
     * @var string
     */
    public $LargeImage;

    /**
     * @var string
     */
    public $Subjects;

    /**
     * @var OfferSet
     */
    public $Offers;

    /**
     * @var CustomerReview[]
     */
    public $CustomerReviews = array();

    /**
     * @var SimilarProducts[]
     */
    public $SimilarProducts = array();

    /**
     * @var Accessories[]
     */
    public $Accessories = array();

    /**
     * @var array
     */
    public $Tracks = array();

    /**
     * @var ListmaniaLists[]
     */
    public $ListmaniaLists = array();

    /**
     * @var ImageVariantSet
     */
    protected $_imageSet;

    /**
     * @var \DOMElement
     */
    protected $_dom;


    /**
     * Parse the given <Item> element
     *
     * @param  null|\DOMElement $dom
     * @throws Exception
     * @return \ApaiIO\Zend\Service\Amazon\Item
     */
    public function __construct($dom)
    {
        if (null === $dom) {
            throw new Exception('Item element is empty');
        }
        if (!$dom instanceof \DOMElement) {
            throw new Exception('Item is not a valid DOM element');
        }
        $xpath = new \DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $this->ASIN = $xpath->query('./az:ASIN/text()', $dom)->item(0)->data;

        $result = $xpath->query('./az:DetailPageURL/text()', $dom);
        if ($result->length == 1) {
            $this->DetailPageURL = $result->item(0)->data;
        }

        if ($xpath->query('./az:ItemAttributes/az:ListPrice', $dom)->length >= 1) {
            $this->CurrencyCode = (string)$xpath->query('./az:ItemAttributes/az:ListPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            $this->Amount = (int)$xpath->query('./az:ItemAttributes/az:ListPrice/az:Amount/text()', $dom)->item(0)->data;
            $this->FormattedPrice = (string)$xpath->query('./az:ItemAttributes/az:ListPrice/az:FormattedPrice/text()', $dom)->item(0)->data;
        }

        $result = $xpath->query('./az:ItemAttributes/az:*/text()', $dom);
        if ($result->length >= 1) {
            foreach ($result as $v) {
                if (isset($this->{$v->parentNode->tagName})) {
                    if (is_array($this->{$v->parentNode->tagName})) {
                        array_push($this->{$v->parentNode->tagName}, (string)$v->data);
                    } else {
                        $this->{$v->parentNode->tagName} = array($this->{$v->parentNode->tagName}, (string)$v->data);
                    }
                } else {
                    $this->{$v->parentNode->tagName} = (string)$v->data;
                }
            }
        }

        foreach (array('SmallImage', 'MediumImage', 'LargeImage') as $im) {
            $result = $xpath->query("./az:ImageSets/az:ImageSet[position() = 1]/az:$im", $dom);
            if ($result->length == 1) {
                /**
                 * @see Image
                 */
                $this->$im = new Image($result->item(0));
            }
        }

        $result = $xpath->query("./az:ImageSets/az:ImageSet[@Category='variant']", $dom);
        if ($result->length >= 1) {
            $this->_imageSet = new ImageVariantSet($result);
        }

        $result = $xpath->query('./az:SalesRank/text()', $dom);
        if ($result->length == 1) {
            $this->SalesRank = (int)$result->item(0)->data;
        }

        $result = $xpath->query('./az:CustomerReviews/az:Review', $dom);
        if ($result->length >= 1) {
            foreach ($result as $review) {
                $this->CustomerReviews[] = new CustomerReview($review);
            }
            $this->AverageRating = (float)$xpath->query('./az:CustomerReviews/az:AverageRating/text()', $dom)->item(0)->data;
            $this->TotalReviews = (int)$xpath->query('./az:CustomerReviews/az:TotalReviews/text()', $dom)->item(0)->data;
        }

        $result = $xpath->query('./az:EditorialReviews/az:*', $dom);
        if ($result->length >= 1) {
            foreach ($result as $r) {
                $this->EditorialReviews[] = new EditorialReview($r);
            }
        }

        $result = $xpath->query('./az:SimilarProducts/az:*', $dom);
        if ($result->length >= 1) {
            foreach ($result as $r) {
                $this->SimilarProducts[] = new SimilarProduct($r);
            }
        }

        $result = $xpath->query('./az:ListmaniaLists/*', $dom);
        if ($result->length >= 1) {
            foreach ($result as $r) {
                $this->ListmaniaLists[] = new ListmaniaList($r);
            }
        }

        $result = $xpath->query('./az:Tracks/az:Disc', $dom);
        if ($result->length > 1) {
            foreach ($result as $disk) {
                foreach ($xpath->query('./*/text()', $disk) as $t) {
                    // TODO: For consistency in a bugfix all tracks are appended to one single array
                    // Erroreous line: $this->Tracks[$disk->getAttribute('number')] = (string) $t->data;
                    $this->Tracks[] = (string)$t->data;
                }
            }
        } else if ($result->length == 1) {
            foreach ($xpath->query('./*/text()', $result->item(0)) as $t) {
                $this->Tracks[] = (string)$t->data;
            }
        }

        $result = $xpath->query('./az:Offers', $dom);
        $resultSummary = $xpath->query('./az:OfferSummary', $dom);
        if ($result->length > 1 || $resultSummary->length == 1) {
            $this->Offers = new OfferSet($dom);
        }

        $result = $xpath->query('./az:Accessories/*', $dom);
        if ($result->length > 1) {
            foreach ($result as $r) {
                $this->Accessories[] = new Accessories($r);
            }
        }

        $this->_dom = $dom;
    }

    /**
     * @return string
     */
    public function getItemDescription()
    {
        if (count($this->EditorialReviews)) {
            if ($this->EditorialReviews[0]->Source == 'Product Description') {
                return $this->EditorialReviews[0]->Content;
            }
        }

        return 'Keine Produktbeschreibung gefunden!';
    }

    /**
     * @return mixed
     */
    public function getProductGroup()
    {
        return $this->ProductGroup;
    }

    /**
     * @return mixed
     */
    public function getBinding()
    {
        return $this->Binding;
    }

    /**
     * @return string
     */
    public function getUserFormattedPrice()
    {
        $price = '';

        switch (AWS_PRICE) {
            case('new'):
                $price = $this->Offers->LowestNewFormattedPrice;
                break;
            case('used'):
                $price = $this->Offers->LowestUsedFormattedPrice;
                break;
            case('collect'):
                $price = $this->Offers->LowestCollectibleFormattedPrice;
                break;
            case('refurbished'):
                $price = $this->Offers->LowestRefurbishedFormattedPrice;
            case('list'):
                $price = $this->FormattedPrice;
                break;
            default:
                $price = $this->Offers->Offers[0]->FormattedPrice;
                break;
        }

        return $price;
    }

    /**
     * amount nach config
     */
    public function getAmount($toDecimal = true)
    {
        $price = '';

        switch (AWS_PRICE) {
            case('new'):
                $price = $this->Offers->LowestNewPrice;
                break;
            case('used'):
                $price = $this->Offers->LowestUsedPrice;
                break;
            case('collect'):
                $price = $this->Offers->LowestCollectiblePrice;
                break;
            case('refurbished'):
                $price = $this->Offers->LowestRefurbishedPrice;
            case('list'):
                $price = $this->Amount;
                break;
            default:
                $price = $this->Offers->Offers[0]->Price;
                break;
        }

        return ($price > 0 && $toDecimal) ? floatval($price) / 100 : 0;
    }

    /**
     * Preisabgleich
     * checks all prices and throws an exception if 'item out of stock'
     * price not available
     *
     * @return float
     * @throws \Exception
     */
    public function getAmountForAvailability()
    {
        $price = $this->getAmount(false);

        if ($price <= 0 || $price == '') {
            $price = $this->Offers->Offers[0]->Price;
            if ($price <= 0 || $price == '') {
                $price = $this->Offers->LowestNewPrice;
                if ($price <= 0 || $price == '') {
                    $price = $this->Offers->LowestUsedPrice;
                    if ($price <= 0 || $price == '') {
                        $price = $this->Offers->LowestCollectiblePrice;
                        if ($price <= 0 || $price == '') {
                            $price = $this->Offers->LowestRefurbishedPrice;
                            if ($price <= 0 || $price == '') {
                                $price = $this->Amount;
                            }
                        }
                    }
                }
            }
        }

        if ($price <= 0 || $price == '') {
            //fix for app that are for free
            if($price == 0 && $this->isFreeCategory()) {
                return 0;
            }
            throw new \Exception('IOOS');
        } else {
            return floatval($price) / 100;
        }
    }

    /**
     * Decide if category is free and cant be out of stock.
     *
     * @return bool
     */
    public function isFreeCategory(){
        $freeCategories = array('App', 'Kindle Edition');
        return in_array($this->getBinding(), $freeCategories);
    }

    /**
     * Only avaible items if TotalNew is set,
     * $this->Offers->Offers[0]->Availability is wrong
     * @see http://docs.aws.amazon.com/AWSECommerceService/latest/DG/ReturningOnlyAvailableItems.html#note
     *
     * @return bool
     */
    public function isAvailable()
    {
        return (bool)$this->Offers->TotalNew;
    }

    /**
     * Returns the item's original XML
     *
     * @return string
     */
    public function asXml()
    {
        return $this->_dom->ownerDocument->saveXML($this->_dom);
    }

    /**
     * @return ImageVariantSet
     * @throws \Exception
     */
    public function getAllImages()
    {
        if ($this->hasImages()) {

            //add images to imageset
            $this->_imageSet->addDefaultImageSet($this->SmallImage, $this->MediumImage, $this->LargeImage);

            return $this->_imageSet;
        }

        throw new \Exception('No images found!');
    }

    /**
     * Check if images are available
     *
     * @return bool
     */
    public function hasImages(){
        $check = false;

        if ($this->_imageSet == null) {
            $this->_imageSet = new ImageVariantSet();
            $this->_imageSet->addDefaultImageSet($this->SmallImage, $this->MediumImage, $this->LargeImage);
            $check = true;
        } else if ($this->_imageSet != null ){
            $check = true;
        }

        return $check;
    }
}