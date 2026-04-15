<?php

use Endcore\AmazonApi;
use Endcore\FormattedItemResponse;

add_action('wp_ajax_at_amazon_add_acf', 'at_amazon_add_acf');
function at_amazon_add_acf()
{
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '-1', 403 );
    }

    $nonce = $_POST['_wpnonce'];

    if (!wp_verify_nonce($nonce, 'at_amazon_import_wpnonce')) {
        die('Security Check failed');
    }

    // quick import
    $apiInstance = AmazonApi::fromWpOptions();

    // vars
    $asin_raw = isset( $_POST['id'] ) ? (string) $_POST['id'] : '';
    $id       = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $asin_raw ) );
    if ( ! preg_match( '/^[A-Z0-9]{10}$/', $id ) ) {
        wp_die( 'Invalid ASIN', '', [ 'response' => 400 ] );
    }

    try {
        $getItemsResponse = $apiInstance->getItems([$id]);
        $formattedResponse = new FormattedItemResponse($getItemsResponse);
    } catch (Exception $e) {
        at_write_api_log('amazon', 'system', $e->getMessage());
        $code = (int) $e->getCode();
        http_response_code( $code >= 100 && $code < 600 ? $code : 500 );
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
