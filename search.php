<?php
/*
 * Copyright 2013 Jan Eichhorn <exeu65@googlemail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
//Preis wählbar in der Config!
require_once ABSPATH.'/wp-load.php';
require_once dirname(__FILE__).'/lib/bootstrap.php';
require_once dirname(__FILE__).'/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;
//use ApaiIO\Zend\Service\Amazon\Validate;

$conf = new GenericConfiguration();

try {
    $conf
        ->setCountry(AWS_COUNTRY)
        ->setAccessKey(AWS_API_KEY)
        ->setSecretKey(AWS_API_SECRET_KEY)
        ->setAssociateTag(AWS_ASSOCIATE_TAG)
        ->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToResponseSet');

} catch (\Exception $e) {
    echo $e->getMessage();
}
$apaiIO = new ApaiIO($conf);

$search = new Search();
$search->setCategory($_GET['category']);
$search->setKeywords($_GET['q']);
$search->setAvailability('Available');
$search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary'));
$search->setPage($_GET['page']);

/* @var $formattedResponse Amazon\ResultSet */
$formattedResponse = $apaiIO->runOperation($search);

/* @var $singleItem Amazon\Item */
foreach ($formattedResponse as $singleItem) {
    $data = array();

    $data['asin'] = $singleItem->ASIN;
    $data['Title'] = $singleItem->Title;
    $data['url'] = $singleItem->DetailPageURL;
	if($singleItem->SmallImage->Url) { $data['img'] = $singleItem->SmallImage->Url->getUri(); }
    $data['price'] = $singleItem->getUserFormattedPrice();
    $data['category'] = $singleItem->getBinding();

    $data['edi_content'] = DotDotText::truncate($singleItem->getItemDescription());

	global $wpdb;
	$imported = $wpdb->get_results(
		$wpdb->prepare( "
			SELECT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s'
			AND pm.meta_value = %s
			AND p.post_type = '%s'", 'amazon_produkt_id', $singleItem->ASIN, 'produkt' )
		)
	;	
			
	if($imported) {
		$data['exists'] = 'true';
	} else {
		$data['exists'] = 'false';
	}

    $output['items'][] = $data;
}

$output['rmessage']['totalpages'] = $formattedResponse->totalPages();

echo json_encode($output);

exit();