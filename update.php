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
        $wpdb->prepare(
            "
                SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                LEFT JOIN {$wpdb->postmeta} a ON p.ID = a.post_id
                WHERE a.meta_key = '%s' AND (a.meta_value+3600 < UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) OR a.meta_id IS NULL) AND pm.meta_key LIKE '%s' AND p.post_type = '%s'
                LIMIT 0,999
            ",
            AWS_METAKEY_LAST_UPDATE, 'product_shops_%_' . AWS_METAKEY_ID, 'product'
        )
    );

    $wlProducts = $wpdb->get_results(
        $wpdb->prepare(
            "
                SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '%s')
                INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key LIKE '%s')
                WHERE pm1.meta_key IS NULL AND pm2.meta_value != '' AND p.post_type = '%s'
                LIMIT 0,999
            ",
            AWS_METAKEY_LAST_UPDATE, 'product_shops_%_' . AWS_METAKEY_ID, 'product'
        )
    );

    $products = array_merge($products, $wlProducts);

    at_write_api_log('amazon', 'system', 'start cron');

    if ($products) {
        foreach ($products as $product) {
            try {
                // ProductShops
                $shops = (get_field('product_shops', $product->ID) ? get_field('product_shops', $product->ID) : array());
                if($shops) {
                    foreach($shops as $key => $val) {
                        if($val['portal'] == 'amazon') { // check if amazon product
                            try {
                                // amazon item
                                $lookup = new Lookup();
                                $lookup->setItemId($val[AWS_METAKEY_ID]);
                                $lookup->setResponseGroup(array('ItemAttributes', 'OfferSummary', 'Offers', 'OfferFull', 'Variations', 'SalesRank', 'Reviews'));
                                $lookup->setAvailability('Available');
                                $formattedResponse = $apaiIO->runOperation($lookup);
                                $item = $formattedResponse->getItem();

                                if (!($item instanceof Amazon\Item)) {
                                    throw new \Exception(sprintf('Item %s not found on Amazon.', $val[AWS_METAKEY_ID]), 505);
                                }

                                if($item) {
                                    $old_price = ($val['price'] ? $val['price'] : '');
                                    $price = ($item->getAmountForAvailability() ? $item->getAmountForAvailability() : '');
                                    $old_link = ($val['link'] ? $val['link'] : '');
                                    $link = ($item->getUrl() ? $item->getUrl() : '');
                                    $old_salesrank = (isset($val['salesrank']) ? $val['salesrank'] : '0');
                                    $salesrank = $item->getSalesRank();

                                    // update price
                                    if ($price != $old_price) {
                                        $shops[$key]['price'] = $price;
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') updated price from ' . $old_price . ' to ' . $price);
                                    }

                                    // update url
                                    if ($link != $old_link) {
                                        $shops[$key]['link'] = $link;
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') changed amazon url');
                                    }

                                    // update salesrank
                                    if ($salesrank != $old_salesrank && $salesrank != "") {
                                        $shops[$key]['salesrank'] = $salesrank;
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') changed amazon salesrank from ' . $old_salesrank . ' to ' . $salesrank);
                                    }
                                }

                                //update rating
                                if(get_option('amazon_update_rating') == '1') {
                                    $rating = $item->getAverageRating();
                                    $rating_cnt = ($item->getTotalReviews() ? $item->getTotalReviews() : '0');

                                    //fix rating
                                    $rating = round($rating*2) / 2;

                                    update_post_meta($product->ID, 'product_rating', $rating);
                                    update_post_meta($product->ID, 'product_rating_cnt', $rating_cnt);
                                }

                                update_post_meta($product->ID, 'product_not_avail', '0');
                                remove_product_notification($product->ID);

                                if(get_option('amazon_notification') == 'draft' || get_option('amazon_notification') == 'email_draft') {
                                    wp_publish_post($product->ID);
                                }
                            } catch (\Exception $e) { // produkt nicht verfügbar
                                update_post_meta($product->ID, AWS_METAKEY_LAST_UPDATE, time());

                                // action
                                if (505 === $e->getCode()) {
                                    at_write_api_log('amazon', $product->ID, 'error (no/incorrect asin?)');
                                    continue;
                                }

                                if(!update_post_meta($product->ID, 'product_not_avail', '1'))
                                    continue;

                                at_write_api_log('amazon', $product->ID, 'product not available');

                                switch (get_option('amazon_notification')) {
                                    case 'email':
                                        set_product_notification($product->ID);
                                        break;

                                    case 'draft':
                                        $args = array(
                                            'ID' => $product->ID,
                                            'post_status' => 'draft'
                                        );
                                        wp_update_post($args);

                                        break;

                                    case 'email_draft':
                                        set_product_notification($product->ID);
                                        $args = array(
                                            'ID' => $product->ID,
                                            'post_status' => 'draft'
                                        );
                                        wp_update_post($args);
                                        break;
                                }
                            }
                        }
                    }

                    update_field('product_shops', $shops, $product->ID);
                    update_post_meta($product->ID, AWS_METAKEY_LAST_UPDATE, time());
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    at_write_api_log('amazon', 'system', 'end cron');

    exit();
}