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
//$search->setMerchantId('Amazon');
$search->setKeywords($query);
$search->setAvailability('Available');
$search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary'));
$search->setPage($_POST['page']);

/* @var $formattedResponse Amazon\ResultSet */
$formattedResponse = $apaiIO->runOperation($search);

/* @var $singleItem Amazon\Item */
foreach ($formattedResponse as $singleItem) {
    $data = array();

    $data['asin'] = $singleItem->ASIN;
    $data['Title'] = $singleItem->Title;
    $data['url'] = $singleItem->DetailPageURL;
    if ($singleItem->SmallImage != null && $singleItem->SmallImage->Url) {
        $data['img'] = $singleItem->SmallImage->Url->getUri();
    }
    $data['price'] = $singleItem->getUserFormattedPrice();
    $data['price_amount'] = $singleItem->getAmountForAvailability();
    $data['currency'] = $singleItem->getCurrencyCode();
    $data['category'] = $singleItem->getBinding();
    $data['cat_margin'] = $singleItem->getMarginForBinding();

    $data['average_rating'] = $singleItem->getAverageRating();
    $data['total_reviews'] = $singleItem->getTotalReviews();
    $data['ean'] = $singleItem->getEan();

    $data['edi_content'] = DotDotText::truncate($singleItem->getItemDescription());
    $data['external'] = $singleItem->isExternalProduct();

    global $wpdb;	
	$check = $wpdb->get_var(
        $wpdb->prepare("
			SELECT p.ID FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s'
			AND pm.meta_value = %s
			AND p.post_type = '%s'
		", AWS_METAKEY_ID, $singleItem->ASIN, 'product')
    );
	
    if ($check) 
        $data['exists'] = $check;
	else
        $data['exists'] = 'false';

    $output['items'][] = $data;
}

$output['rmessage']['totalpages'] = $formattedResponse->totalPages();
$output['rmessage']['errormsg'] = $formattedResponse->getErrorMessage();

echo json_encode($output);

exit();