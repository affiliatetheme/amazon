<?php
/**
 * Copyright 2014 - endcore
 */
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;

add_action('wp_ajax_amazon_feed_cron', 'amazon_feed_cron');
function amazon_feed_cron() {
    $feed = at_amazon_feed_read();

    if(!$feed)
        exit;

    /*
     * Load Amazon API
     */
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

    /*
     * Iterate between feed items
     */
    foreach($feed as $item) {
        if($item->keyword == '' || $item->keyword == 'undefined')
            continue;

        $keyword = $item->keyword;
        $category = $item->category;

        $search = new Search();
        $search->setCategory($category);
        $search->setKeywords($keyword);
        $search->setAvailability('Available');
        $search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'SalesRank'));
        $search->setPage('1');

        $formattedResponse = $apaiIO->runOperation($search);

        $nonce = wp_create_nonce("at_amazon_import_wpnonce");

        echo '<h2>' . $keyword . '</h2>';

        foreach ($formattedResponse as $singleItem) {
            try {
                $asin = $singleItem->ASIN;
                $title = $singleItem->Title;
                $ean = $singleItem->getEan();
                $price = $singleItem->getAmountForAvailability();
                $price_list = $singleItem->getAmountListPrice();
                $salesrank = ($singleItem->getSalesRank() ? $singleItem->getSalesRank() : '');
                $url = $singleItem->getUrl();
                $currency = $singleItem->getCurrencyCode();
                $rating = $singleItem->getAverageRating();
                $rating_cnt = ($singleItem->getTotalReviews() ? $singleItem->getTotalReviews() : '0');
                $images = array();

                if ($singleItem->getAllImages()->getLargeImages()) {
                    $i = 1;
                    foreach ($singleItem->getAllImages()->getLargeImages() as $image) {
                        $images[$i]['filename'] = sanitize_title($title . '-' . $i);
                        $images[$i]['alt'] = $title . ' - ' . $i;
                        $images[$i]['url'] = $image;

                        if ($i == 1)
                            $images[$i]['thumb'] = 'true';

                        $i++;
                    }
                }

                if ('1' == get_option('amazon_import_description'))
                    $description = $singleItem->getItemDescription();

                if (false == ($check = at_get_product_id_by_metakey('product_shops_%_' . AWS_METAKEY_ID, $asin, 'LIKE'))) {
                    $args = array(
                        'post_title' => $title,
                        'post_status' => (get_option('amazon_post_status') ? get_option('amazon_post_status') : 'publish'),
                        'post_type' => 'product',
                        'post_content' => ($description ? $description : '')
                    );

                    $post_id = wp_insert_post($args);

                    if ($post_id) {
                        //fix rating
                        $rating = round($rating*2) / 2;

                        //customfields
                        update_post_meta($post_id, AWS_METAKEY_ID, $asin);
                        update_post_meta($post_id, AWS_METAKEY_LAST_UPDATE, time());
                        update_post_meta($post_id, 'product_ean', $ean);
                        update_post_meta($post_id, 'product_rating', $rating);
                        update_post_meta($post_id, 'product_rating_cnt', $rating_cnt);

                        $shop_info[] = array(
                            'price' => $price,
                            'price_old' => ($price_list ? $price_list : ''),
                            'currency' => $currency,
                            'portal' => 'amazon',
                            'amazon_asin' => $asin,
                            'amazon_salesrank' => $salesrank,
                            'shop' => (get_amazon_shop_id() ? get_amazon_shop_id() : ''),
                            'link' => $url,
                        );
                        update_field('field_557c01ea87000', $shop_info, $post_id);

                        // product image
                        if ($images) {
                            $attachments = array();

                            foreach ($images as $image) {
                                $image_filename = sanitize_title($image['filename']);
                                $image_alt = (isset($image['alt']) ? $image['alt'] : '');
                                $image_url = $image['url'];
                                $image_thumb = (isset($image['thumb']) ? $image['thumb'] : '');
                                $image_exclude = (isset($image['exclude']) ? $image['exclude'] : '');

                                if ("true" == $image_exclude)
                                    continue;

                                if ("true" == $image_thumb) {
                                    $att_id = at_attach_external_image($image_url, $post_id, true, $image_filename, array('post_title' => $image_alt));
                                    update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
                                } else {
                                    $att_id = at_attach_external_image($image_url, $post_id, false, $image_filename, array('post_title' => $image_alt));
                                    update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
                                    $attachments[] = $att_id;
                                }

                                if ($attachments)
                                    update_field('field_553b84fb117b1', $attachments, $post_id);
                            }
                        }

                        at_write_api_log('amazon', $post_id, 'imported product successfully');

                        $output['rmessage']['success'] = 'true';
                        $output['rmessage']['post_id'] = $post_id;
                    }
                }
            } catch (\Exception $e) {
                at_write_api_log('amazon', 'system', $e->getMessage());
                continue;
            }
        }

        //$feed[$k]['last_message'] = sprintf(__('Suche ausgeführt: %s', 'affiliatetheme-api'), date('d.m.Y G:i:s'));
        //$feed[$k]['last_update'] = time();
    }
    update_option('at_amazon_feed_items', serialize($feed));

    exit;
}