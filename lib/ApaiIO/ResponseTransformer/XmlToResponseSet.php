<?php
/**
 * Created by PhpStorm.
 * User: GiacomoBarbalinardo
 * Date: 09.11.2014
 * Time: 21:48
 */

namespace ApaiIO\ResponseTransformer;
use ApaiIO\Zend\Service\Amazon\ResultSet;

class XmlToResponseSet implements ResponseTransformerInterface {

    /**
     * @param mixed $response
     * @return ResultSet
     */
    public function transform($response)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadXML($response);

        //echo $response;die;

        $result = new ResultSet($document);

        return $result;
    }

} 