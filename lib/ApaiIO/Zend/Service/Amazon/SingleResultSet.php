<?php
/**
 * Project: ama
 * (c) 2014 Giacomo Barbalinardo <info@ready24it.eu>
 * Date: 11.11.2014
 * Time: 22:12
 */

namespace ApaiIO\Zend\Service\Amazon;

class SingleResultSet
{
    /**
     * Amazon Web Service Return Document
     *
     * @var \DOMDocument
     */
    protected $_dom;

    /**
     * XPath Object for $this->_dom
     *
     * @var \DOMXPath
     */
    protected $_xpath;

    /**
     * Returns the item
     *
     * @var Item
     */
    protected $_item;

    /**
     * @var ResultSet
     */
    protected $_result;

    /**
     * Create an instance of Zend_Service_Amazon_ResultSet and create the necessary data objects
     *
     * @param  \DOMDocument $dom
     */
    public function __construct(\DOMDocument $dom)
    {
        $this->_dom = $dom;

        $this->_xpath = new \DOMXPath($dom);


        $this->_xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $items = $this->_xpath->query('//az:Items/az:Item');

        if ($items->length == 1) {
            $this->_item = new Item($items->item(0));
        }

        $this->_result = new ResultSet($dom);
    }

    /**
     * @return bool
     */
    public function hasItem()
    {
        return $this->_item ? true : false;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * @return ResultSet
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Implement SeekableIterator::valid()
     *
     * @return boolean
     */
    public function isValid()
    {
        $valid = (string)$this->_xpath->query('./az:Items/az:Request/az:IsValid/text()')->item(0)->data;

        return $valid == 'True' ? true : false;
    }
}
