<?php

/**
 * Project: affilliate_amazon
 * (c) 2014 Giacomo Barbalinardo <info@ready24it.eu>
 * Date: 29.11.2014
 * Time: 21:58
 */

namespace ApaiIO\Features;

class CustomerReview {

    /**
     * @var float
     */
    protected $_averageRating = 0.0;

    protected $_imgTag;
    
    protected $_imgSrc;

    /**
     * @var int
     */
    protected $_totalReviews = 0;

    public function __construct($url)
    {
        if (ini_get('allow_url_fopen')) {
            $this->url = $url;

            $plainHtml = file_get_contents($this->url);

            $dom = new \DOMDocument();
            @$dom->loadHTML($plainHtml);

            $finder = new \DOMXPath($dom);

            $classname = 'crIFrameNumCustReviews';
            $nodes = $finder->query("//*[contains(@class, '$classname')]");

            $content = '';

            if ($nodes->length == 1) {
                $content .= $dom->saveXML($nodes->item(0));
            }

            if ($content) {
                $this->_getReviewsData($content);
            }
        }
    }

    /**
     * @param $contents
     */
    protected function _getReviewsData ($contents)
    {
        $patternTag = '~<img[^>]+>~is';
        $patternSrc = '/(src)="([^"]*)"/i';
        $patternAlt = '/(alt)="([^"]*)"/i';
        $patternCnt = '~<a\b[^>]*>(.*?)</a>~is';

        if (preg_match($patternTag, $contents, $matchTag) == 1) {
            $this->_imgTag = $matchTag[0];

            if (preg_match($patternSrc, $this->_imgTag, $matchSrc) == 1) {
                $this->_imgSrc = $matchSrc[2];
            }
            if (preg_match($patternAlt, $this->_imgTag, $matchAlt) == 1) {
                $alt = explode(' ', $matchAlt[2]);
                $this->_averageRating = (float) $alt[0];
            }
        }

        if (preg_match_all($patternCnt, $contents, $matchCnt)) {
            $count = explode(' ', $matchCnt[1][1]);
            $count = str_replace(',', '', $count);
            $count = str_replace('.', '', $count);
            $this->_totalReviews = intval($count[0]);
        }
    }

    public function getAverageRating(){
        return $this->_averageRating;
    }

    public function getTotalReviews(){
        return $this->_totalReviews;
    }
} 