<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;


use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;

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

}
