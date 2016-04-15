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
$search->setCategory($_POST['category']);
$search->setKeywords($query);
$search->setAvailability('Available');
$search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'SalesRank'));
$search->setPage($_POST['page']);

$sortCategories = array(
    'All', 'Outlet'
);

if(!in_array($_POST['category'], $sortCategories)){
    $search->setSort('price');
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