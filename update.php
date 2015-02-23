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

$wlProducts = $wpdb->get_results("
        SELECT {$wpdb->posts}.ID as post_id, mt1.meta_value as asin, 0 as last FROM {$wpdb->posts}
        LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = 'last_amazon_check')
        INNER JOIN {$wpdb->postmeta} AS mt1 ON ({$wpdb->posts}.ID = mt1.post_id)
        WHERE 1=1 AND {$wpdb->posts}.post_type = 'produkt' AND ( {$wpdb->postmeta}.post_id IS NULL
        AND (mt1.meta_key = 'amazon_produkt_id' AND CAST(mt1.meta_value AS CHAR) != '') ) GROUP BY {$wpdb->posts}.ID ORDER BY {$wpdb->posts}.post_date DESC
    "
);

$products = array_merge($products, $wlProducts);

//print_r($products);die;

if($products) {
	foreach($products as $product) {
		$lookup = new Lookup();
		$lookup->setItemId($product->asin);
		$lookup->setResponseGroup(array('OfferSummary', 'Offers', 'OfferFull'));
        $lookup->setAvailability('Available');
	
		/* @var $formattedResponse Amazon\SingleResultSet */
		$formattedResponse = $apaiIO->runOperation($lookup);
		/* @var $item Amazon\Item */
		$item = $formattedResponse->getItem();
		
		try {
			if (!($item instanceof Amazon\Item)) {
				throw new \Exception(sprintf('Item %s not found on Amazon.', $product->asin));
			}

			$price = $item->getAmountForAvailability();
			update_post_meta($product->post_id, 'produkt_verfuegbarkeit', '1');
			update_post_meta($product->post_id, 'preis', $price);
			update_post_meta($product->post_id, 'last_amazon_check', time());
			wp_publish_post($product->post_id);
		} catch(\Exception $e) {			
			// action
			switch(get_option('amazon_benachrichtigung')) {
				case 'email':
					if(get_post_meta($product->post_id, 'produkt_verfuegbarkeit', true) != "0") { send_amazon_notifictaion_mail($product->post_id); }
					break;
					
				case 'draft':
					$args = array(
						'ID'			=> $product->post_id,
						'post_status'	=> 'draft'
					);
					wp_update_post($args);
					
					break;
					
				case 'email_draft':
					if(get_post_meta($product->post_id, 'produkt_verfuegbarkeit', true) != "0") { send_amazon_notifictaion_mail($product->post_id); }
					$args = array(
						'ID'			=> $product->post_id,
						'post_status'	=> 'draft'
					);
					wp_update_post($args);
					break;
			}
			
			update_post_meta($product->post_id, 'produkt_verfuegbarkeit', '0');
			update_post_meta($product->post_id, 'last_amazon_check', time());
		}
	}
}

exit();