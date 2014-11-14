<?php
require_once ABSPATH.'/wp-load.php';
require_once dirname(__FILE__).'/lib/bootstrap.php';
require_once dirname(__FILE__).'/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

$conf = new GenericConfiguration();
try {
    $conf
        ->setCountry(AWS_COUNTRY)
        ->setAccessKey(AWS_API_KEY)
        ->setSecretKey(AWS_API_SECRET_KEY)
        ->setAssociateTag(AWS_ASSOCIATE_TAG)
        ->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToSingleResponseSet');
} catch (\Exception $e) {
    echo $e->getMessage();
}
$apaiIO = new ApaiIO($conf);

$nonce = $_POST['_wpnonce'];
global $wpdb;

$products = $wpdb->get_results(
	$wpdb->prepare( "
		SELECT pm.post_id, pm.meta_value as \"asin\", a.meta_value as \"last\" FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		LEFT JOIN {$wpdb->postmeta} a ON p.ID = a.post_id 
		WHERE pm.meta_key = '%s' AND a.meta_key = '%s' AND a.meta_value+3600 < UNIX_TIMESTAMP(CURRENT_TIMESTAMP())
		AND p.post_type = '%s' LIMIT 0,999", 'amazon_produkt_id', 'last_amazon_check', 'produkt' 
	)
);

print_r($products);
if($products) {
	foreach($products as $product) {
		$lookup = new Lookup();
		$lookup->setItemId($product->asin);
		$lookup->setResponseGroup(array('OfferSummary', 'Offers', 'OfferFull'));
	
		/* @var $formattedResponse Amazon\SingleResultSet */
		$formattedResponse = $apaiIO->runOperation($lookup);
		/* @var $item Amazon\Item */
		$item = $formattedResponse->getItem();
		
		try {
			$price = $item->getAmountForAvailability();
			update_post_meta($product->post_id, 'produkt_verfuegbarkeit', '1');
			update_post_meta($product->post_id, 'preis', $price);
			update_post_meta($product->post_id, 'last_amazon_check', time());
			//last erneuern
		} catch(\Exception $e) {
			update_post_meta($product->post_id, 'produkt_verfuegbarkeit', '0');
			update_post_meta($product->post_id, 'last_amazon_check', time());
			//benachrichtigung
		}
	}
}

exit();