<?php

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Availability;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\MaxPrice;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\MinPrice;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SortBy;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;
use Endcore\AmazonApi;
use Endcore\Price;

add_action('wp_ajax_amazon_api_search', 'at_aws_search');
add_action('wp_ajax_at_aws_search', 'at_aws_search');
add_action('wp_ajax_nopriv_at_aws_search', 'at_aws_search');
function at_aws_search() {
    $config = new Configuration();
    $config->setAccessKey(AWS_API_KEY);
    $config->setSecretKey(AWS_API_SECRET_KEY);
    $partnerTag = AWS_ASSOCIATE_TAG;
    $config->setHost('webservices.amazon.de');
    $config->setRegion('eu-west-1');
    $apiInstance = new AmazonApi(new GuzzleHttp\Client(), $config);

    // vars
    $grabbedasins = (isset($_POST['grabbedasins']) ? $_POST['grabbedasins'] : '');    
    $keywords = (isset($_POST['q']) ? $_POST['q'] : '');
    $title = (isset($_POST['title']) ? $_POST['title'] : '');    
    $category = (isset($_POST['category']) ? $_POST['category'] : 'All');
    $page = (isset($_POST['page']) ? $_POST['page'] : '1');
    $sort = (isset($_POST['sort']) ? $_POST['sort'] : SortBy::RELEVANCE);
    $merchant = (isset($_POST['merchant']) ? $_POST['merchant'] : 'All');
    $min_price = (isset($_POST['min_price']) ? $_POST['min_price'] : '');
    $max_price = (isset($_POST['max_price']) ? $_POST['max_price'] : '');
    
    // overwrite keywords with asins
    if($grabbedasins) {
        $keywords = implode("|", explode("\n", $_POST['grabbedasins']));
    }

    $resources = SearchItemsResource::getAllowableEnumValues();

    $search = new SearchItemsRequest();
    $search->setAvailability(Availability::AVAILABLE);
    $search->setResources($resources);
    $search->setItemPage((int)$page);
    $search->setSearchIndex($category);
    $search->setKeywords('mi smart tv 55 4k');
    if ($title) {
        $search->setTitle($title);
    }
    $search->setSortBy($sort);
    $search->setPartnerTag($partnerTag);
    $search->setPartnerType(PartnerType::ASSOCIATES);
    $search->setMerchant($merchant);
//    if ($min_price && $min_price !== 'undefined') {
//        $search->setMinPrice(new MinPrice(Price::convert($min_price)));
//    }
//    if ($max_price && $max_price !== 'undefined') {
//        $search->setMaxPrice(new MaxPrice(Price::convert($max_price)));
//    }

    $invalidPropertyList = $search->listInvalidProperties();
    $length = count($invalidPropertyList);
    if ($length > 0) {
        echo "Error forming the request", PHP_EOL;
        foreach ($invalidPropertyList as $invalidProperty) {
            echo $invalidProperty, PHP_EOL;
        }
        return;
    }


    $searchItemsResponse = $apiInstance->searchItems($search);
    $formattedResponse = $searchItemsResponse->getSearchResult()->getItems();

    // http://ama.local/wp-admin/admin-ajax.php?action=amazon_api_search&keywords=matrix
    if($formattedResponse) {
        /* @var $singleItem Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item */
        foreach ($formattedResponse as $singleItem) {
            try {

                var_dump($singleItem);die;

                $data = array(
//                    'ean' => $singleItem->getParentASIN() . $singleItem->getItemInfo()->get,
                    'asin' => $singleItem->getASIN(),
                    'title' => $singleItem->getItemInfo()->getTitle(),
                    'description' => DotDotText::truncate($singleItem->getItemInfo()->getContentInfo()),
                    'url' => $data['url'] = $singleItem->getDetailPageURL(),
                    'price' => $data['price'] = $singleItem->getOffers()->getListings()[0]->getPrice()->getAmount(),
                    'price' => $data['price'] = $singleItem->getUserFormattedPrice(),
                    'price_list' => ($singleItem->getFormattedListPrice() ? $singleItem->getFormattedListPrice() : 'kA'),
                    'price_amount' => $singleItem->getAmountForAvailability(),
                    'currency' => ($singleItem->getOffers()->getListings()[0]->getPrice()->getCurrency() ? $singleItem->getOffers()->getListings()[0]->getPrice()->getCurrency() : 'EUR'),
                    'category' => $singleItem->getBinding(),
                    'category_margin' => $singleItem->getMarginForBinding(),
                    'external' => $singleItem->isExternalProduct(),
                    'prime' => ($singleItem->isPrime() ? 1 : 0),
                    'exists' => 'false'
                );

                if ($singleItem->SmallImage != null && $singleItem->SmallImage->Url) {
                    $data['img'] = $singleItem->SmallImage->Url->getUri();
                }

                if ($check = at_get_product_id_by_metakey('product_shops_%_' . AWS_METAKEY_ID, $singleItem->ASIN, 'LIKE')) {
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

    $output['rmessage']['totalpages'] = $formattedResponse->totalPages();
    $output['rmessage']['errormsg'] = $formattedResponse->getErrorMessage();

    echo json_encode($output);

    exit();
}
