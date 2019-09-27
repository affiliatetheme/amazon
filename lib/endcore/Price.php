<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;


class Price
{
    public static function convert($price)
    {
        $price = $price * 100;
        if (true === is_numeric($price)  && $price > 0) {
            return array($price);
        }

        throw new \InvalidArgumentException(
            sprintf(
                '%s is an invalid price value. It has to be numeric and >= than 1',
                $price
            )
        );
    }
}
