<?php
/**
 * Created by PhpStorm.
 * User: GiacomoBarbalinardo
 * Date: 09.11.2014
 * Time: 21:48
 */

namespace ApaiIO\ResponseTransformer;

use ApaiIO\Zend\Service\Amazon\SingleResultSet;

class XmlToSingleResponseSet implements ResponseTransformerInterface
{

    /**
     * @param mixed $response
     * @return SingleResultSet
     */
    public function transform($response)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        //$document->loadXML($response);

        if (!@$document->loadXML($response)){
            return false;
        }

        $result = new SingleResultSet($document);

        return $result;
    }

} 