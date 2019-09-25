<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 */

namespace Endcore;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use GuzzleHttp\Psr7\Request;

class AmazonApi extends DefaultApi
{
    protected function getItemsRequest($getItemsRequest)
    {
        // verify the required parameter 'getItemsRequest' is set
        if ($getItemsRequest === null) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $getItemsRequest when calling getItems'
            );
        }

        $operation = 'GetItems';
        $resourcePath = '/paapi5/getitems';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        $awsv4 = new AwsV4($this->config->getAccessKey(), $this->config->getSecretKey());
        $awsv4->setRegionName($this->config->getRegion());
        $awsv4->setServiceName("ProductAdvertisingAPI");
        $awsv4->setPath($resourcePath);
        $awsv4->setPayload($getItemsRequest->__toString());
        $awsv4->setRequestMethod("POST");
        $awsv4->addHeader('content-encoding', 'amz-1.0');
        $awsv4->addHeader('content-type', 'application/json; charset=utf-8');
        $awsv4->addHeader('host', str_replace('https://', '', $this->config->getHost()));
        $awsv4->addHeader('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $operation);

        $request = new Request(
            'POST',
            $this->config->getHost() . $resourcePath,
            $awsv4->getHeaders(),
            $getItemsRequest->__toString()
        );

        return $request;
    }

}
