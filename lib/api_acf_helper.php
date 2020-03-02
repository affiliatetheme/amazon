<?php

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Endcore\AmazonApi;
use Endcore\FormattedItemResponse;

add_action('wp_ajax_at_amazon_add_acf', 'at_amazon_add_acf');
function at_amazon_add_acf()
{
    $nonce = $_POST['_wpnonce'];

    if (!wp_verify_nonce($nonce, 'at_amazon_import_wpnonce')) {
        die('Security Check failed');
    }

    // quick import
    $hostAndRegion = at_amazon_get_host_region();

    $config = new Configuration();
    $config->setAccessKey(AWS_API_KEY);
    $config->setSecretKey(AWS_API_SECRET_KEY);
    $partnerTag = AWS_ASSOCIATE_TAG;
    $config->setHost($hostAndRegion['host']);
    $config->setRegion($hostAndRegion['region']);
    $apiInstance = new AmazonApi(new EnGuzzleHttp\Client(), $config);

    // vars
    $id = $_POST['id'];
    $resources = GetItemsResource::getAllowableEnumValues();

    $lookup = new GetItemsRequest();
    $lookup->setItemIds([$id]);
    $lookup->setPartnerTag($partnerTag);
    $lookup->setPartnerType(PartnerType::ASSOCIATES);
    $lookup->setResources($resources);

    $invalidPropertyList = $lookup->listInvalidProperties();
    $length = count($invalidPropertyList);
    if ($length > 0) {
        echo "Error forming the request", PHP_EOL;
        foreach ($invalidPropertyList as $invalidProperty) {
            echo $invalidProperty, PHP_EOL;
        }
        return;
    }

    try {
        $getItemsResponse = $apiInstance->getItems($lookup);
        $formattedResponse = new FormattedItemResponse($getItemsResponse);
    } catch (Exception $e) {
        at_write_api_log('amazon', 'system', $e->getMessage());
        http_response_code($e->getCode());
        exit();
    }

    if ($formattedResponse->hasResult()) {
        $item = $formattedResponse->getItem();

        if($item) {
            $price = $item->getPriceAmount();
            $price_list = $item->getPriceList();
            $url = $item->getUrl();
            $currency = $item->getCurrency();

        }
    }

    $portal = 'amazon';
    $output['rmessage']['success'] = 'true';
    $output['shop_info']['price'] = $price;
    $output['shop_info']['currency'] = (strtolower($currency) == 'eur' ) ? 'euro' : strtolower($currency);
    $output['shop_info']['portal'] = $portal;
    $output['shop_info']['metakey'] = $id;
    $output['shop_info']['link'] = $url;
    $output['shop_info']['shop'] = (at_aws_get_amazon_shop_id() ? at_aws_get_amazon_shop_id() : '');
    $output['shop_info']['shopname'] = 'Amazon';
    $output['shop_info']['price_old'] = ($price_list ? $price_list : '');
    echo json_encode($output);
    exit();
}
