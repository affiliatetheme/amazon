<?php

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Endcore\AmazonApi;
use Endcore\FormattedItemResponse;
use Endcore\SimpleItem;

add_action( 'wp_ajax_at_aws_update', 'at_aws_update' );
add_action( 'wp_ajax_nopriv_at_aws_update', 'at_aws_update' );
add_action( 'wp_ajax_amazon_api_update', 'at_aws_update' );
add_action( 'wp_ajax_nopriv_amazon_api_update', 'at_aws_update' );
add_action( 'affiliatetheme_amazon_api_update', 'at_aws_update' );
add_action( 'affiliatetheme_amazon_api_update_feeds', 'at_aws_update_feeds' );
function at_aws_update( $args = array() )
{
    global $wpdb;

    $hash       = AWS_CRON_HASH;
    $check_hash = ( $args ? $args : ( isset( $_GET['hash'] ) ? $_GET['hash'] : '' ) );

    // Clear old cron
    wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', array('hash' => AWS_CRON_HASH));

    if ( $check_hash != $hash ) {
        wp_clear_scheduled_hook( 'affiliatetheme_amazon_api_update', $args = array( $check_hash ) );
        die( 'Security check failed.' );
    }

    $interval = ( at_amazon_product_skip_interval() ? at_amazon_product_skip_interval() : 3600 );

    // get products
    $products = $wpdb->get_results(
        $wpdb->prepare(
            "
                SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                LEFT JOIN {$wpdb->postmeta} a ON p.ID = a.post_id
				WHERE a.meta_key = '%s' AND (a.meta_value+" . $interval . " < UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) OR a.meta_id IS NULL) AND pm.meta_key LIKE '%s' AND  p.post_type = '%s' AND p.post_status != 'trash'
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
                WHERE pm1.meta_key IS NULL AND pm2.meta_value != '' AND  p.post_type = '%s' AND p.post_status != 'trash'
                LIMIT 0,999
            ",
            AWS_METAKEY_LAST_UPDATE, 'product_shops_%_' . AWS_METAKEY_ID, 'product'
        )
    );

    $products = array_merge( $products, $wlProducts );

    at_write_api_log( 'amazon', 'system', 'start cron' );

    if ( $products ) {
        $hostAndRegion = at_amazon_get_host_region();

        $config = new Configuration();
        $config->setAccessKey( AWS_API_KEY );
        $config->setSecretKey( AWS_API_SECRET_KEY );
        $partnerTag = AWS_ASSOCIATE_TAG;
        $config->setHost( $hostAndRegion['host'] );
        $config->setRegion( $hostAndRegion['region'] );
        $apiInstance = new AmazonApi( new EnGuzzleHttp\Client(), $config );

        foreach ( $products as $product ) {
            try {
                // ProductShops
                $shops = ( get_field( 'product_shops', $product->ID ) ? get_field( 'product_shops', $product->ID ) : array() );
                if ( $shops ) {
                    foreach ( $shops as $key => $val ) {
                        if ( $val['portal'] == 'amazon' ) { // check if amazon product
                            try {
                                $resources = GetItemsResource::getAllowableEnumValues();

                                $lookup = new GetItemsRequest();

                                if ( empty( $val[AWS_METAKEY_ID] ) || $val[AWS_METAKEY_ID] === '' ) {
                                    throw new \Exception( 'Item not found on Amazon.', 505 );
                                }

                                $lookup->setItemIds( [ $val[AWS_METAKEY_ID] ] );
                                $lookup->setPartnerTag( $partnerTag );
                                $lookup->setPartnerType( PartnerType::ASSOCIATES );
                                $lookup->setResources( $resources );

                                $invalidPropertyList = $lookup->listInvalidProperties();
                                $length              = count( $invalidPropertyList );
                                if ( $length > 0 ) {
                                    echo "Error forming the request", PHP_EOL;
                                    foreach ( $invalidPropertyList as $invalidProperty ) {
                                        echo $invalidProperty, PHP_EOL;
                                    }

                                    return;
                                }

                                $getItemsResponse  = $apiInstance->getItems( $lookup );
                                $formattedResponse = new FormattedItemResponse( $getItemsResponse );

                                $item = $formattedResponse->getItem();

                                if ( ! ( $item instanceof SimpleItem ) ) {
                                    throw new \Exception( sprintf( 'Item %s not found on Amazon.', $val[AWS_METAKEY_ID] ), 505 );
                                }

                                if ( $item->getPriceAmount() === '' ) {
                                    throw new \Exception( sprintf( 'Item %s not available.', $val[AWS_METAKEY_ID] ), 506 );
                                }

                                if ( $item ) {
                                    $asin          = $val[AWS_METAKEY_ID];
                                    $title         = get_the_title( $product->ID );
                                    $old_ean       = get_post_meta( $product->ID, 'product_ean', true );
                                    $ean           = $item->getEAN();
                                    $old_price     = ( $val['price'] ? $val['price'] : '' );
                                    $price         = ( $item->getPriceAmount() ? $item->getPriceAmount() : '' );
                                    $old_link      = ( $val['link'] ? $val['link'] : '' );
                                    $link          = ( $item->getUrl() ? $item->getUrl() : '' );
                                    $old_salesrank = get_post_meta( $product->ID, 'amazon_salesrank_' . $key, true );
                                    $salesrank     = $item->getSalesRank();
                                    $prime         = $item->isPrime();

                                    // update ean
                                    if ( $ean && $ean != $old_ean && get_option( 'amazon_update_ean' ) != 'no' ) {
                                        update_post_meta( $product->ID, 'product_ean', $ean );
                                        at_write_api_log( 'amazon', $product->ID, '(' . $key . ') updated ean from ' . $old_ean . ' to ' . $ean );
                                        do_action( 'at_amazon_updated_ean', $product->ID, $ean, $old_ean );
                                    }

                                    // update price
                                    if ( $price != $old_price && get_option( 'amazon_update_price' ) != 'no' ) {
                                        $shops[$key]['price'] = $price;
                                        at_write_api_log( 'amazon', $product->ID, '(' . $key . ') updated price from ' . $old_price . ' to ' . $price );
                                        do_action( 'at_amazon_updated_price', $product->ID, $price, $old_price );
                                    }

                                    // update url
                                    if ( $link != $old_link && get_option( 'amazon_update_url' ) != 'no' ) {
                                        $shops[$key]['link'] = $link;
                                        at_write_api_log( 'amazon', $product->ID, '(' . $key . ') changed amazon url' );
                                        do_action( 'at_amazon_updated_url', $product->ID, $link, $old_link );
                                    }

                                    // update salesrank
                                    if ( $salesrank != $old_salesrank && $salesrank != "" ) {
                                        update_post_meta( $product->ID, 'amazon_salesrank_' . $key, $salesrank );
                                        at_write_api_log( 'amazon', $product->ID, '(' . $key . ') changed amazon salesrank from ' . $old_salesrank . ' to ' . $salesrank );
                                        do_action( 'at_amazon_updated_salesrank', $product->ID, $salesrank, $old_salesrank );
                                    }

                                    // update prime status
                                    if ( $prime ) {
                                        update_post_meta( $product->ID, 'product_amazon_prime', 'true' );
                                        update_post_meta( $product->ID, 'product_amazon_prime_' . $asin, 'true' );
                                    } else {
                                        delete_post_meta( $product->ID, 'product_amazon_prime_' . $asin, 'true' );
                                    }

                                    // update external images
                                    if ( get_option( 'amazon_update_external_images' ) == 'yes' ) {
                                        if ( get_option( 'amazon_images_external' ) == '1' ) {
                                            $amazon_images = $item->getExternalImages();
                                            $images        = array();
                                            $attachments   = array();

                                            if ( $amazon_images ) {
                                                $i = 1;
                                                foreach ( $amazon_images as $image ) {
                                                    $images[$i]['filename'] = sanitize_title( get_the_title( $product->ID ) . '-' . $i );
                                                    $images[$i]['alt']      = get_the_title( $product->ID ) . ' - ' . $i;
                                                    $images[$i]['url']      = $image;

                                                    if ( $i == 1 ) {
                                                        $images[$i]['thumb'] = 'true';
                                                    }

                                                    $i++;
                                                }
                                            }


                                            if ( $images ) {
                                                $_thumbnail_ext_url = get_post_meta( $product->ID, '_thumbnail_ext_url', true );;

                                                foreach ( $images as $image ) {
                                                    $image_filename = substr( sanitize_title( $image['filename'] ), 0, apply_filters( 'at_amazon_strip_title', 80 ) );
                                                    $image_alt      = ( isset( $image['alt'] ) ? $image['alt'] : '' );
                                                    $image_url      = $image['url'];
                                                    $image_thumb    = ( isset( $image['thumb'] ) ? $image['thumb'] : '' );

                                                    // skip if image already exists as post thumbnail
                                                    if ( $image_url == $_thumbnail_ext_url ) {
                                                        continue;
                                                    }

                                                    // load images form extern
                                                    if ( "true" == $image_thumb ) {
                                                        // check if thumbnail does not exist
                                                        if ( $_thumbnail_ext_url != $image_url ) {
                                                            update_post_meta( $product->ID, '_thumbnail_ext_url', $image_url );
                                                            update_post_meta( $product->ID, '_thumbnail_id', 'by_url' );
                                                        } else {
                                                            // check if ssl is available and image is ssl
                                                            if ( is_ssl() && strpos( $_thumbnail_ext_url, 'https://' ) !== 0 ) {
                                                                update_post_meta( $product->ID, '_thumbnail_ext_url', $image_url );
                                                                update_post_meta( $product->ID, '_thumbnail_id', 'by_url' );
                                                            }
                                                        }
                                                    } else {
                                                        $attachments[] = array(
                                                            'url'  => $image_url,
                                                            'alt'  => $image_alt,
                                                            'hide' => ''
                                                        );
                                                    }
                                                }
                                            }

                                            if ( $attachments ) {
                                                $product_gallery_external = get_field( 'product_gallery_external', $product->ID );

                                                // set old attributes for hide
                                                $i = 0;
                                                foreach ( $attachments as $attachment ) {
                                                    if ( $product_gallery_external ) {
                                                        foreach ( $product_gallery_external as $k => $v ) {
                                                            // fix ssl
                                                            if ( is_ssl() && strpos( $product_gallery_external[$k]['url'], 'https://' ) !== 0 ) {
                                                                $product_gallery_external[$k]['url'] = str_replace( 'http://ecx.images-amazon.com/images/', 'https://images-na.ssl-images-amazon.com/images/', $v );
                                                            }

                                                            if ( $attachment['url'] == $product_gallery_external[$k]['url'] ) {
                                                                // overwrite url, fix damaged images
                                                                $attachments[$i]['url'] = $product_gallery_external[$k]['url'];

                                                                if ( $product_gallery_external[$k]['hide'] == '1' ) {
                                                                    $attachments[$i]['hide'] = '1';
                                                                }

                                                                if ( $product_gallery_external[$k]['alt'] != '' ) {
                                                                    $attachments[$i]['alt'] = $product_gallery_external[$k]['alt'];
                                                                }
                                                            }
                                                        }
                                                    }

                                                    $i++;
                                                }

                                                update_field( 'field_57486088e1f0d', $attachments, $product->ID );
                                            }
                                        } else {
                                            // remove old external images
                                            update_field( 'field_57486088e1f0d', array(), $product->ID );
                                            $product_gallery = get_field( 'field_553b84fb117b1', $product->ID );
                                            $images          = array();

                                            // no external images, check if we should add internal images
                                            // check thumbnail & set post thumbnail
                                            if ( ! has_post_thumbnail( $product->ID ) ) {
                                                $_thumbnail_ext_url = get_post_meta( $product->ID, '_thumbnail_ext_url', true );
                                                $_thumbnail_id      = get_post_meta( $product->ID, '_thumbnail_id', true );

                                                if ( $_thumbnail_id == 'by_url' ) {
                                                    if ( $_thumbnail_ext_url ) {
                                                        // try to set the external image as product thumbnail
                                                        $att_id = at_attach_external_image( $_thumbnail_ext_url, $product->ID, true );
                                                    } else {
                                                        // no image found? fuck. just set the first image from the gallery as product thumbnail
                                                        foreach ( $product_gallery as $new_att ) {
                                                            $att_id = $new_att['ID'];
                                                            break;
                                                        }

                                                        // remove this image from gallery
                                                        unset( $product_gallery[0] );
                                                        update_field( 'field_553b84fb117b1', $product_gallery, $product->ID );
                                                    }

                                                    update_post_meta( $product->ID, '_thumbnail_id', $att_id );
                                                    update_post_meta( $product->ID, '_thumbnail_ext_url', '' );
                                                } else {
                                                    // check database for images
                                                    $args                = array(
                                                        'post_type'      => 'attachment',
                                                        'post_parent'    => $product->ID,
                                                        'posts_per_page' => 1
                                                    );
                                                    $product_attachments = get_posts( $args );
                                                    if ( $product_attachments ) {
                                                        foreach ( $product_attachments as $attachment ) {
                                                            set_post_thumbnail( $product->ID, $attachment->ID );
                                                        }
                                                    } else {
                                                        $amazon_images = $item->getAllLargeImages();
                                                        if ( $amazon_images ) {
                                                            foreach ( $amazon_images as $image ) {
                                                                $att_id = at_attach_external_image( $image, $product->ID, true, get_the_title( $product->ID ), array( 'post_title' => get_the_title( $product->ID ) ) );
                                                                update_post_meta( $att_id, '_wp_attachment_image_alt', get_the_title( $product->ID ) );
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if ( ! $product_gallery ) {
                                                $attachments = array();
                                                // try to get local imagess
                                                $args                = array(
                                                    'post_type'      => 'attachment',
                                                    'post_parent'    => $product->ID,
                                                    'posts_per_page' => -1,
                                                    'post__not_in'   => array( get_post_thumbnail_id( $product->ID ) )
                                                );
                                                $product_attachments = get_posts( $args );
                                                if ( $product_attachments ) {
                                                    foreach ( $product_attachments as $attachment ) {
                                                        $attachments[] = $attachment->ID;
                                                    }
                                                } else {
                                                    // fallback to amazon images
                                                    $amazon_images = $item->getAllLargeImages();
                                                    if ( $amazon_images ) {
                                                        foreach ( $amazon_images as $image ) {
                                                            $images[$c]['filename'] = sanitize_title( $title . '-' . $c );
                                                            $images[$c]['alt']      = $title . ' - ' . $c;
                                                            $images[$c]['url']      = $image;
                                                        }
                                                    }

                                                    if ( $images ) {
                                                        foreach ( $images as $image ) {
                                                            $image_filename = substr( sanitize_title( $image['filename'] ), 0, apply_filters( 'at_amazon_strip_title', 80 ) );
                                                            $image_alt      = ( isset( $image['alt'] ) ? $image['alt'] : '' );
                                                            $image_url      = $image['url'];
                                                            $image_thumb    = ( isset( $image['thumb'] ) ? $image['thumb'] : '' );
                                                            $image_exclude  = ( isset( $image['exclude'] ) ? $image['exclude'] : '' );

                                                            if ( "true" == $image_exclude || "true" == $image_thumb ) {
                                                                continue;
                                                            }

                                                            $att_id = at_attach_external_image( $image_url, $product->ID, false, $image_filename, array( 'post_title' => $image_alt ) );
                                                            update_post_meta( $att_id, '_wp_attachment_image_alt', $image_alt );
                                                            $attachments[] = $att_id;
                                                        }
                                                    }
                                                }

                                                if ( $attachments ) {
                                                    update_field( 'field_553b84fb117b1', $attachments, $product->ID );
                                                }
                                            }
                                        }
                                    }

                                    update_post_meta( $product->ID, 'product_not_avail', '0' );
                                    at_aws_remove_product_notification( $product->ID );

                                    if ( get_option( 'amazon_notification' ) == 'draft' || get_option( 'amazon_notification' ) == 'email_draft' ) {
                                        wp_publish_post( $product->ID );
                                    }
                                }
                            } catch ( Exception $e ) {
                                if ( 429 === $e->getCode() ) {
                                    at_write_api_log( 'amazon', $product->ID, 'Too many requests. Skipping product and take a little break. (Exception: ' . $e->getCode() . ' - ' . $e->getMessage() . ')' );
                                    sleep( 5 );
                                    continue;
                                } elseif ( 403 === $e->getCode() ) {
                                    at_write_api_log( 'amazon', $product->ID, 'Access Denied, please check your Login credentials. (Exception: ' . $e->getCode() . ' - ' . $e->getMessage() . ')' );
                                    continue;
                                } else {
                                    at_write_api_log( 'amazon', $product->ID, 'Error during request. (Exception: ' . $e->getCode() . ' - ' . $e->getMessage() . ')' );
                                }

                                // set timestamp & update field for product not avail
                                update_post_meta( $product->ID, AWS_METAKEY_LAST_UPDATE, time() );
                                if ( ! update_post_meta( $product->ID, 'product_not_avail', '1' ) )
                                    continue;

                                switch ( get_option( 'amazon_notification' ) ) {
                                    case 'email':
                                        at_aws_set_product_notification( $product->ID );
                                        break;

                                    case 'draft':
                                        $args = array(
                                            'ID'          => $product->ID,
                                            'post_status' => 'draft'
                                        );
                                        wp_update_post( $args );

                                        break;

                                    case 'email_draft':
                                        at_aws_set_product_notification( $product->ID );
                                        $args = array(
                                            'ID'          => $product->ID,
                                            'post_status' => 'draft'
                                        );
                                        wp_update_post( $args );
                                        break;

                                    case 'remove':

                                        unset( $shops[$key] );
                                        update_post_meta( $product->ID, get_field( 'product_shops', $product->ID ), $shops );

                                        break;

                                    case 'email_remove':
                                        at_aws_set_product_notification( $product->ID );
                                        unset( $shops[$key] );
                                        update_post_meta( $product->ID, get_field( 'product_shops', $product->ID ), $shops );

                                        break;
                                }
                            }
                        }
                    }

                    update_field( 'product_shops', $shops, $product->ID );
                    update_post_meta( $product->ID, AWS_METAKEY_LAST_UPDATE, time() );

                    do_action( 'at_amazon_updated', $product->ID );

                    sleep( 2 );
                }
            } catch ( \Exception $e ) {
                sleep( 2 );
                continue;
            }
        }
    }

    at_write_api_log( 'amazon', 'system', 'end cron' );

    exit();
}


function at_aws_update_feeds()
{
    global $wpdb;
    $all_asins = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'product_shops_%_amazon_asin'" );
    $feeds     = at_amazon_feed_read();
    $i         = 0;
    if ( $feeds ) {
        foreach ( $feeds as $feed ) {
            if ( $feed->status != 0 && strtotime( $feed->last_update ) < ( time() - 86400 ) ) {
                $asins = at_aws_grab( $feed->keyword, true );
                at_write_api_log( 'amazon', 'system', 'start running feed: ' . $feed->category );
                foreach ( $asins['asins'] as $asin ) {
                    if ( ! in_array( $asin, $all_asins ) ) {
                        at_aws_import( $asin, true, unserialize( $feed->tax ) );
                        $i++;
                        sleep( 1 );
                        if ( $i > 10 ) exit();
                    }
                }
                at_amazon_feed_set_update( $feed->id );
                at_write_api_log( 'amazon', 'system', 'end running feed: ' . $feed->category );
                break;
            }
        }
    }
    exit();
}
