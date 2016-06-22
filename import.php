<?php
/**
 * Copyright 2015 - endcore
 * import
 */
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

global $wpdb;
$nonce = $_POST['_wpnonce'];

if (!wp_verify_nonce($nonce, 'at_amazon_import_wpnonce')) {

    die('Security Check failed');

} else {

    $asin = $_POST['asin'];
    $description = '';

    if (!$asin)
        die();

    if (isset($_POST['func']) && ($_POST['func'] == 'quick-import')) {
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

        $lookup = new Lookup();
        $lookup->setItemId($asin);
        $lookup->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'Offers', 'OfferFull', 'Images', 'Reviews', 'Variations'));

        /* @var $formattedResponse Amazon\SingleResultSet */
        $formattedResponse = $apaiIO->runOperation($lookup);

        if ($formattedResponse->hasItem()) {
            $item = $formattedResponse->getItem();

            $title = $item->Title;
            $ean = $item->getEan();
            $price = $item->getAmountForAvailability();
            $price_list = $item->getAmountListPrice();
            $salesrank = ($item->getSalesRank() ? $item->getSalesRank() : '');
            $url = $item->getUrl();
            $currency = $item->getCurrencyCode();
            $rating = $item->getAverageRating();
            $rating_cnt = ($item->getTotalReviews() ? $item->getTotalReviews() : '0');
            $taxs = isset($_POST['tax']) ? $_POST['tax'] : array();
            $images = array();

            // crawl images
            $amazon_images_external = get_option('amazon_images_external');
            if($amazon_images_external) {
                $amazon_images = $item->getAllImages()->getMediumImages();
            } else {
                $amazon_images = $item->getAllImages()->getLargeImages();
            }

            if ($amazon_images) {
                $i = 1;
                foreach ($amazon_images as $image) {
                    $images[$i]['filename'] = sanitize_title($title . '-' . $i);
                    $images[$i]['alt'] = $title . ' - ' . $i;
                    $images[$i]['url'] = $image;

                    if ($i == 1)
                        $images[$i]['thumb'] = 'true';

                    $i++;
                }
            }

            if ('1' == get_option('amazon_import_description'))
                $description = $item->getItemDescription();
        }
    } else {
        $title = $_POST['title'];
        $ean = $_POST['ean'];
        $price = floatval($_POST['price']);
        $price_list = $_POST['price_list'];
        $salesrank = ($_POST['salesrank'] ? $_POST['salesrank'] : '');
        $currency = $_POST['currency'];
        $url = $_POST['url'];
        $rating = floatval($_POST['rating']);
        $rating_cnt = $_POST['rating_cnt'];
        $taxs = isset($_POST['tax']) ? $_POST['tax'] : array();
        $images = $_POST['image'];
        $exists = $_POST['ex_page_id'];

        if ('1' == get_option('amazon_import_description'))
            $description = (isset($_POST['description']) ? $_POST['description'] : '');
    }

    if (false == ($check = at_get_product_id_by_metakey('product_shops_%_' . AWS_METAKEY_ID, $asin, 'LIKE'))) {

        if ($exists) {
            $post_id = $exists;
        } else {
            $args = array(
                'post_title' => $title,
                'post_status' => (get_option('amazon_post_status') ? get_option('amazon_post_status') : 'publish'),
                'post_type' => 'product',
                'post_content' => ($description ? $description : '')
            );

            $post_id = wp_insert_post($args);
        }

        if ($post_id) {
            //fix rating
            $rating = round($rating * 2) / 2;

            //customfields
            update_post_meta($post_id, AWS_METAKEY_ID, $asin);
            update_post_meta($post_id, AWS_METAKEY_LAST_UPDATE, time());
            update_post_meta($post_id, 'product_ean', $ean);
            update_post_meta($post_id, 'product_rating', $rating);
            update_post_meta($post_id, 'product_rating_cnt', $rating_cnt);

            if($exists) {
                $shop_info = get_field('field_557c01ea87000', $post_id);
            }

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

            //taxonomie
            if ($taxs) {
                foreach ($taxs as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            if (strpos($v, ',') !== false) {
                                $value[$k] = '';
                                $exploded = explode(',', $v);

                                $value = array_merge($value, $exploded);
                            }
                        }
                    }

                    $value = array_filter($value);
                    wp_set_object_terms($post_id, $value, $key, true);
                }
            }

            // product image
            if ($images) {
                $attachments = array();

                foreach ($images as $image) {
                    $image_filename = substr(sanitize_title($image['filename']), 0, 30);
                    $image_alt = (isset($image['alt']) ? $image['alt'] : '');
                    $image_url = $image['url'];
                    $image_thumb = (isset($image['thumb']) ? $image['thumb'] : '');
                    $image_exclude = (isset($image['exclude']) ? $image['exclude'] : '');

                    $amazon_images_external = get_option('amazon_images_external');
                    if ($amazon_images_external) {
                        // load images form extern
                        if ("true" == $image_thumb) {
                            update_post_meta($post_id, '_thumbnail_ext_url', $image_url);
                            update_post_meta($post_id, '_thumbnail_id', 'by_url');
                        } else {
                            $attachments[] = array(
                                'url' => $image_url,
                                'alt' => $image_alt,
                                'hide' => ("true" == $image_exclude ? '1' : '')
                            );
                        }
                    } else {
                        // load images in local database
                        if ("true" == $image_exclude) {
                            continue;
                        }

                        if ("true" == $image_thumb) {
                            $att_id = at_attach_external_image($image_url, $post_id, true, $image_filename, array('post_title' => $image_alt));
                            update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
                        } else {
                            $att_id = at_attach_external_image($image_url, $post_id, false, $image_filename, array('post_title' => $image_alt));
                            update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
                            $attachments[] = $att_id;
                        }
                    }
                }

                if ($attachments) {
                    if ($amazon_images_external) {
                        update_field('field_57486088e1f0d', $attachments, $post_id);
                    } else {
                        update_field('field_553b84fb117b1', $attachments, $post_id);
                    }
                }
            }

            at_write_api_log('amazon', $post_id, 'imported product successfully');

            do_action('at_amazon_import_product', $post_id, $item);
            $output['rmessage']['success'] = 'true';
            $output['rmessage']['post_id'] = $post_id;
        }

    } else {

        $output['rmessage']['success'] = 'false';
        $output['rmessage']['reason'] = 'Dieses Produkt existiert bereits.';
        $output['rmessage']['post_id'] = $check;

    }
}

echo json_encode($output);
exit();
?>