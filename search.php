<?php
/**
 * Copyright 2014 - endcore
 */

//Preis wählbar in der Config!
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;

$conf = new GenericConfiguration();

try {
    $conf->setCountry(AWS_COUNTRY)
        ->setAccessKey(AWS_API_KEY)
        ->setSecretKey(AWS_API_SECRET_KEY)
        ->setAssociateTag(AWS_ASSOCIATE_TAG)
        ->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToResponseSet');

} catch (\Exception $e) {
    echo $e->getMessage();
}
$apaiIO = new ApaiIO($conf);

if(isset($_POST['grabbedasins']) && ("" != $_POST['grabbedasins'])) {
    $exploded = explode("\n", $_POST['grabbedasins']);
    $query = implode("|", $exploded);
} else {
    $query = $_POST['q'];
}

$search = new Search();
$search->setAvailability('Available');
$search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'SalesRank'));
$search->setPage($_POST['page']);

// set searchindex
$search->setCategory($_POST['category']);

// set keywords
if($query) {
    $search->setKeywords($query);
}

// set title
if(isset($_POST['title']) && $_POST['title'] != 'undefined' && $_POST['category'] != 'All') {
    $search->setTitle($_POST['title']);
}

// set sort
if(isset($_POST['sort']) && $_POST['sort'] != 'undefined') {
    if(at_aws_search_check_allowed_sort($_POST['category'])) {
        $search->setSort($_POST['sort']);
    }
}

// set merchant
if(isset($_POST['merchant']) && $_POST['merchant'] != 'undefined') {
    $search->setMerchantId($_POST['merchant']);
}

// set min_price
if(isset($_POST['min_price']) && $_POST['min_price'] != 'undefined' && $_POST['min_price'] != '') {
    if(at_aws_search_check_allowed_param('MinimumPrice', $_POST['category'])) {
        $price = $_POST['min_price'] * 100;
        $search->setMinimumPrice($price);
    }
}

// set max_price
if(isset($_POST['max_price']) && $_POST['max_price'] != 'undefined' && $_POST['max_price'] != '') {
    if(at_aws_search_check_allowed_param('MaximumPrice', $_POST['category'])) {
        $price = $_POST['max_price'] * 100;
        $search->setMaximumPrice($price);
    }
}

/* @var $formattedResponse Amazon\ResultSet */
$formattedResponse = $apaiIO->runOperation($search);

/* @var $singleItem Amazon\Item */
foreach ($formattedResponse as $singleItem) {
    try {
        $data = array();
        $data['asin'] = $singleItem->ASIN;
        $data['Title'] = $singleItem->Title;
        $data['url'] = $singleItem->DetailPageURL;
        if ($singleItem->SmallImage != null && $singleItem->SmallImage->Url) {
            $data['img'] = $singleItem->SmallImage->Url->getUri();
        }
        $data['price'] = $singleItem->getUserFormattedPrice();
        $data['price_list'] = ($singleItem->getFormattedListPrice() ? $singleItem->getFormattedListPrice() : 'kA');
        $data['price_amount'] = $singleItem->getAmountForAvailability();
        $data['currency'] = ($singleItem->getCurrencyCode() ? $singleItem->getCurrencyCode() : 'EUR');
        $data['category'] = $singleItem->getBinding();
        $data['cat_margin'] = $singleItem->getMarginForBinding();
        $data['average_rating'] = $singleItem->getAverageRating();
        $data['total_reviews'] = $singleItem->getTotalReviews();
        $data['ean'] = $singleItem->getEan();
        $data['edi_content'] = DotDotText::truncate($singleItem->getItemDescription());
        $data['external'] = $singleItem->isExternalProduct();
        if ($check = at_get_product_id_by_metakey('product_shops_%_'.AWS_METAKEY_ID, $singleItem->ASIN, 'LIKE')) {
            $data['exists'] = $check;
        } else {
            $data['exists'] = 'false';
        }

        $output['items'][] = $data;
    } catch (\Exception $e) {
        //$output['items'][] = $e->getMessage();
        at_write_api_log('amazon', 'system', $e->getMessage());
        continue;
    }
}

$output['rmessage']['totalpages'] = $formattedResponse->totalPages();
$output['rmessage']['errormsg'] = $formattedResponse->getErrorMessage();

echo json_encode($output);

exit();