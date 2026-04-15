<?php

use Endcore\AmazonApi;
use Endcore\FormattedResponse;
use Endcore\Price;
use Endcore\SimpleItem;

add_action('wp_ajax_amazon_api_search', 'at_aws_search');
add_action('wp_ajax_at_aws_search', 'at_aws_search');
function at_aws_search() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '-1', 403 );
    }

    $apiInstance = AmazonApi::fromWpOptions();

    // vars
    $grabbedasins = (isset($_POST['grabbedasins']) ? trim($_POST['grabbedasins']) : '');
    $keywords = (isset($_POST['q']) ? $_POST['q'] : '');
    $title = (isset($_POST['title']) ? $_POST['title'] : '');
    $category = (isset($_POST['category']) ? $_POST['category'] : 'All');
    $page = (isset($_POST['page']) ? $_POST['page'] : '1');
    $sort = (isset($_POST['sort']) && $_POST['sort'] !== '' ? $_POST['sort'] : 'Relevance');
    $merchant = (isset($_POST['merchant']) ? $_POST['merchant'] : 'All');
    $min_price = (isset($_POST['min_price']) ? $_POST['min_price'] : '');
    $max_price = (isset($_POST['max_price']) ? $_POST['max_price'] : '');

    // overwrite keywords with asins
    if($grabbedasins) {
        $keywords = implode("|", explode("\n", $_POST['grabbedasins']));
    }

    $searchArgs = array(
        'availability' => 'Available',
        'itemPage'     => (int) $page,
        'searchIndex'  => $category,
        'keywords'     => $keywords,
        'sortBy'       => $sort,
        'merchant'     => $merchant,
    );

    if ($title) {
        $searchArgs['title'] = $title;
    }

    if ($min_price && $min_price !== 'undefined') {
        $converted = Price::convert($min_price);
        $searchArgs['minPrice'] = is_array($converted) ? (int) $converted[0] : (int) $converted;
    }
    if ($max_price && $max_price !== 'undefined') {
        $converted = Price::convert($max_price);
        $searchArgs['maxPrice'] = is_array($converted) ? (int) $converted[0] : (int) $converted;
    }

    try {
        $searchItemsResponse = $apiInstance->searchItems($searchArgs);
        $formattedResponse = new FormattedResponse($searchItemsResponse);
    } catch (\Exception $e) {
        at_write_api_log('amazon', 'system', $e->getMessage());
        $code = (int) $e->getCode();
        http_response_code( $code >= 100 && $code < 600 ? $code : 500 );
        exit();
    }

    $output = [];

    // http://ama.local/wp-admin/admin-ajax.php?action=amazon_api_search&keywords=matrix
    if($formattedResponse->hasResult()) {
        /* @var $singleItem SimpleItem */
        foreach ($formattedResponse->getItems() as $item) {
            try {
                $data = array(
                    'ean' => $item->getEAN(),
                    'asin' => $item->getASIN(),
                    'title' => $item->getTitle(),
                    'description' => $item->getDescription(),
                    'url' => $data['url'] = $item->getUrl(),
                    'price' => $data['price'] = $item->getUserPrice(),
                    'price_list' => $item->getPriceList(),
                    'price_amount' => $item->getPriceAmount(),
                    'currency' => $item->getCurrency(),
                    'category' => $item->getCategory(),
                    'category_margin' => $item->getCategoryMargin(),
                    'external' => $item->isExternal(),
                    'prime' => $item->isPrime(),
                    'exists' => 'false'
                );

                if ($item->getSmallImage() !== null) {
                    $data['img'] = $item->getSmallImage();
                }

                if ($check = at_get_product_id_by_metakey('product_shops_%_' . AWS_METAKEY_ID, $item->getASIN(), 'LIKE')) {
                    $data['exists'] = $check;
                }

                $output['items'][] = $data;
            } catch (\Exception $e) {
                //$output['items'][] = $e->getMessage();
                at_write_api_log('amazon', 'system', $e->getMessage());
                continue;
            }
        }
    }

    $output['rmessage']['totalpages'] = $formattedResponse->getTotalPages();
    $output['rmessage']['errormsg'] = $formattedResponse->getErrorMessage();

    echo json_encode($output);

    exit();
}
