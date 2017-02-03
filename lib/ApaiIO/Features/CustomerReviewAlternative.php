<?php

/**
 * Project: affilliate_amazon
 * (c) 2014 Giacomo Barbalinardo <info@ready24it.eu>
 * Date: 29.11.2014
 * Time: 21:58
 */

namespace ApaiIO\Features;

class CustomerReviewAlternative {

    /**
     * @var float
     */
    protected $_averageRating = 0.0;

    /**
     * @var int
     */
    protected $_totalReviews = 0;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param $url
     * @param $asin
     */
    public function __construct($url, $asin)
    {
        $this->setUrlByAsin($url, $asin);

        if (ini_get('allow_url_fopen')) {
            $this->url = $this->getUrl();

            $plainHtml = file_get_contents($this->url);

            $dom = new \DOMDocument();
            @$dom->loadHTML($plainHtml);

            $finder = new \DOMXPath($dom);

            $nodeRating = $finder->query("//*[contains(@class, 'a-color-secondary')]");

            if ($nodeRating->length > 0) {
                $this->setAverageRating($nodeRating->item(0));
            }

            $nodeReviews = $finder->query("//*[contains(@class, 'a-link-emphasis')]");
            if ($nodeReviews->length > 0) {
                $this->setTotalReviews($nodeReviews->item(0));
            }
        }
    }

    public function getAverageRating(){
        return $this->_averageRating;
    }

    public function getTotalReviews(){
        return $this->_totalReviews;
    }

    public function getUrl()
    {
        return $this->url;
    }

    private function setUrlByAsin($url, $asin)
    {
        $urlPart = 'gp/customer-reviews/widgets/average-customer-review/popover/ref=dpx_acr_pop_?contextId=dpx&asin=';

        if (preg_match('~^(http|https)\:\/\/[a-z\.]+\/~', $url, $matches) == 1) {
            $url = $matches[0] . $urlPart . $asin;
        }

        $this->url = $url;
    }

    private function setAverageRating(\DOMNode $item)
    {
        $pattern = '~([0-9\.]+)[\s]~s';

        if (preg_match($pattern, trim($item->textContent), $matches) > 0) {
            $this->_averageRating = (float) $matches[1];
        }
    }

    private function setTotalReviews(\DOMNode $item)
    {
        $pattern = '~([0-9\.]+)[\s]~s';

        if (preg_match($pattern, trim($item->textContent), $matches) > 0) {
            $this->_totalReviews = intval($matches[1]);
        }
    }
} 