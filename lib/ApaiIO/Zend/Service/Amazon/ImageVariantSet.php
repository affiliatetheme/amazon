<?php
/**
 * Project: ama
 * (c) 2014 Giacomo Barbalinardo <info@ready24it.eu>
 * Date: 12.11.2014
 * Time: 15:50
 */

namespace ApaiIO\Zend\Service\Amazon;


class ImageVariantSet
{

    /**
     * @var array
     */
    protected $_images = array();

    public function __construct(\DOMNodeList $variants = null)
    {
        if ($variants != null) {
            $i = 0;
            foreach ($variants as $variant) {
                foreach (array('SwatchImage', 'SmallImage', 'MediumImage', 'LargeImage') as $im) {
                    //var_dump($variant->c14N(false, true));die;

                    $document = new \DOMDocument('1.0', 'UTF-8');
                    $document->loadXML($variant->c14N(false, true));

                    $xpath = new \DOMXPath($document);
                    $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');

                    $result = $xpath->query("./az:ImageSet[@Category='variant']/az:$im", $document);

                    if ($result->length == 1) {
                        /**
                         * @see Image
                         */
                        $this->_images[$i][$im] = new Image($result->item(0));
                    }
                }
                $i++;
            }
        }
    }


    public function getSwatchImages()
    {
        return $this->_getImageCollectionByType('SwatchImage');
    }

    public function getSmallImages()
    {
        return $this->_getImageCollectionByType('SmallImage');
    }

    public function getMediumImages()
    {
        return $this->_getImageCollectionByType('MediumImage');
    }

    public function getLargeImages()
    {
        return $this->_getImageCollectionByType('LargeImage');
    }

    public function addDefaultImageSet(Image $smallImage = null, Image $mediumImage = null, Image $largeImage = null){
        if($smallImage != null ) {
            $this->_images[]['SmallImage'] = $smallImage;
        }

        if($mediumImage != null) {
            $this->_images[]['MediumImage'] = $mediumImage;
        }

        if($largeImage != null) {
            $this->_images[]['LargeImage'] = $largeImage;
        }
    }


    protected function _getImageCollectionByType($type)
    {
        $data = array();
        foreach ($this->_images as $image) {

            if($image[$type] != null) {
                $data[] = $image[$type]->Url->getUri();
            }
        }

        return array_unique($data);
    }
} 