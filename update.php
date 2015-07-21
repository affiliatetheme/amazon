<?php
/**
 * Copyright 2015 - endcore
 * update
 */
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

if(get_option('amazon_public_key') != "" &&  get_option('amazon_secret_key') != "") {
    if( !wp_next_scheduled( 'affiliatetheme_amazon_api_update', $args = array('hash' => AWS_CRON_HASH))) {
        wp_schedule_event(time(), 'hourly', 'affiliatetheme_amazon_api_update', $args = array('hash' => AWS_CRON_HASH));
    }
} else {

}

add_action('wp_ajax_amazon_api_update', 'amazon_api_update');
add_action('wp_ajax_nopriv_amazon_api_update', 'amazon_api_update');
add_action('affiliatetheme_amazon_api_update', 'amazon_api_update');

function amazon_api_update($args = array()) {
    $hash = AWS_CRON_HASH;
    $check_hash = ($args ? $args : (isset($_GET['hash']) ? $_GET['hash'] : ''));

    if($check_hash != $hash) {
        wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', $args = array('hash' => $check_hash));

        die('Security check failed.');
    }

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

    global $wpdb;

    $products = $wpdb->get_results(
        $wpdb->prepare("
            SELECT pm.post_id, pm.meta_value as \"asin\", a.meta_value as \"last\" FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            LEFT JOIN {$wpdb->postmeta} a ON p.ID = a.post_id
            WHERE pm.meta_key LIKE '%s' AND a.meta_key = '%s' AND a.meta_value+3600 < UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AND pm.meta_value != ''
            AND p.post_type = '%s' LIMIT 0,999", 'product_shops_%_' . AWS_METAKEY_ID, 'last_product_price_check', 'product'
        )
    );

    $wlProducts = $wpdb->get_results("
            SELECT {$wpdb->posts}.ID as post_id, mt1.meta_value as asin, 0 as last FROM {$wpdb->posts}
            LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = 'last_product_price_check')
            INNER JOIN {$wpdb->postmeta} AS mt1 ON ({$wpdb->posts}.ID = mt1.post_id)
            WHERE 1=1 AND {$wpdb->posts}.post_type = 'product' AND ( {$wpdb->postmeta}.post_id IS NULL
            AND (mt1.meta_key LIKE 'product_shops_%_" . AWS_METAKEY_ID ."' AND CAST(mt1.meta_value AS CHAR) != '') ) GROUP BY {$wpdb->posts}.ID ORDER BY {$wpdb->posts}.post_date DESC
        "
    );

    $products = array_merge($products, $wlProducts);

    at_write_api_log('amazon', 'system', 'start cron');

    if ($products) {
        foreach ($products as $product) {
            $lookup = new Lookup();
            $lookup->setItemId($product->asin);
            $lookup->setResponseGroup(array('OfferSummary', 'Offers', 'OfferFull', 'Variations'));
            $lookup->setAvailability('Available');

            /* @var $formattedResponse Amazon\SingleResultSet */
            $formattedResponse = $apaiIO->runOperation($lookup);
            /* @var $item Amazon\Item */
            $item = $formattedResponse->getItem();

            try {
                if (!($item instanceof Amazon\Item)) {
                    throw new \Exception(sprintf('Item %s not found on Amazon.', $product->asin), 505);
                }

                $product_shops = get_field('product_shops', $product->post_id);
                $product_index = getRepeaterRowID($product_shops, AWS_METAKEY_ID, $product->asin);

                if(false !== $product_index) {
                    $old_price = $product_shops[$product_index]['price'];
                    $price = $item->getAmountForAvailability();

                    if(update_post_meta($product->post_id, 'product_shops_'.$product_index.'_price', $price, $old_price)) {
                        //update_post_meta($product->post_id, 'product_shops_'.$product_index.'_price_old', $old_price);
                        at_write_api_log('amazon', $product->post_id, 'updated price from ' . $old_price . ' to ' . $price);
                    }

                    update_post_meta($product->post_id, 'last_product_price_check', time());
                    update_post_meta($product->post_id, 'product_not_avail', '0');
                    remove_product_notification($product->post_id);
                    wp_publish_post($product->post_id);
                }
            } catch (\Exception $e) {
                update_post_meta($product->post_id, 'last_product_price_check', time());

                // action
                if (505 === $e->getCode()) {
                    at_write_api_log('amazon', $product->post_id, 'error (no/incorrect asin?)');
                    continue;
                }

                if(!update_post_meta($product->post_id, 'product_not_avail', '1'))
                    continue;

                at_write_api_log('amazon', $product->post_id, 'product not available');

                switch (get_option('amazon_notification')) {
                    case 'email':
                        set_product_notification($product->post_id);
                        break;

                    case 'draft':
                        $args = array(
                            'ID' => $product->post_id,
                            'post_status' => 'draft'
                        );
                        wp_update_post($args);

                        break;

                    case 'email_draft':
                        set_product_notification($product->post_id);
                        $args = array(
                            'ID' => $product->post_id,
                            'post_status' => 'draft'
                        );
                        wp_update_post($args);
                        break;
                }
            }
        }
    }

    at_write_api_log('amazon', 'system', 'end cron');

    exit();
}