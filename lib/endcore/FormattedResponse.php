<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResponse;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchResult;

class FormattedResponse
{
    /**
     * @var SearchResult
     */
    private $result;

    /**
     * @var SearchItemsResponse
     */
    private $response;

    /**
     * FormattedResponse constructor.
     * @param SearchItemsResponse $response
     */
    public function __construct(SearchItemsResponse $response)
    {
        $this->response = $response;

        var_dump($response);die;

        $this->result = $response->getSearchResult();
    }

    /**
     * @param int $itemsPerPage
     * @return float|int
     */
    public function getTotalPages($itemsPerPage = 10)
    {
        if ($this->hasResult()) {
            return ceil($this->result->getTotalResultCount() / $itemsPerPage);
        }

        return 0;
    }

    /**
     * @return SimpleItem[]|bool
     */
    public function getItems()
    {
        if ($this->hasResult()) {
            $items = array_map(function($item) {
                return new SimpleItem($item);
            }, $this->result->getItems());

            return $items;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        if (count($this->response->getErrors()) > 0) {
            return $this->response->getErrors()[0]->getMessage();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasResult()
    {
        return $this->result !== null;
    }
}
