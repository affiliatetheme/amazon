<?php
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
add_action('wp_ajax_at_aws_update', 'at_aws_update');
add_action('wp_ajax_nopriv_at_aws_update', 'at_aws_update');
add_action('wp_ajax_amazon_api_update', 'at_aws_update');
add_action('wp_ajax_nopriv_amazon_api_update', 'at_aws_update');
add_action('affiliatetheme_amazon_api_update', 'at_aws_update');
function at_aws_update($args = array()) {	
    global $wpdb;
	
	$hash = AWS_CRON_HASH;
    $check_hash = ($args ? $args : (isset($_GET['hash']) ? $_GET['hash'] : ''));

    if($check_hash != $hash) {
        wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', $args = array('hash' => $check_hash));
        die('Security check failed.');
    }
	
	// get products
    $products = $wpdb->get_results(
        $wpdb->prepare(
            "
                SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                LEFT JOIN {$wpdb->postmeta} a ON p.ID = a.post_id
				WHERE a.meta_key = '%s' AND (a.meta_value+3600 < UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) OR a.meta_id IS NULL) AND pm.meta_key LIKE '%s' AND  p.post_type = '%s' AND p.post_status != 'trash'
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
		
    $products = array_merge($products, $wlProducts);
	
    at_write_api_log('amazon', 'system', 'start cron');

    if ($products) {
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
                                $lookup->setResponseGroup(array('ItemAttributes', 'OfferSummary', 'Offers', 'OfferFull', 'Variations', 'SalesRank', 'Reviews', 'Images'));
                                $lookup->setAvailability('Available');
                                $formattedResponse = $apaiIO->runOperation($lookup);
                                $item = $formattedResponse->getItem();

                                if (!($item instanceof Amazon\Item)) {
                                    throw new \Exception(sprintf('Item %s not found on Amazon.', $val[AWS_METAKEY_ID]), 505);
                                }

                                if ($item->getAmountForAvailability() === '') {
                                    throw new \Exception(sprintf('Item %s not available.', $val[AWS_METAKEY_ID]), 506);
                                }

                                if ($item) {
									$title = get_the_title($product->ID);
                                    $old_ean = get_post_meta($product->ID, 'product_ean', true);
                                    $ean = $item->getEan();
                                    $old_price = ($val['price'] ? $val['price'] : '');
                                    $price = ($item->getAmountForAvailability() ? $item->getAmountForAvailability() : '');
                                    $old_link = ($val['link'] ? $val['link'] : '');
                                    $link = ($item->getUrl() ? $item->getUrl() : '');
                                    $old_salesrank = get_post_meta($product->ID, 'amazon_salesrank_' . $key, true);
                                    $salesrank = $item->getSalesRank();

                                    // update ean
                                    if ($ean && $ean != $old_ean && get_option('amazon_update_ean') != 'no') {
                                        update_post_meta($product->ID, 'product_ean', $ean);
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') updated ean from ' . $old_ean . ' to ' . $ean);
                                    }

                                    // update price
                                    if ($price != $old_price && get_option('amazon_update_price') != 'no') {
                                        $shops[$key]['price'] = $price;
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') updated price from ' . $old_price . ' to ' . $price);
                                    }

                                    // update url
                                    if ($link != $old_link && get_option('amazon_update_url') != 'no') {
                                        $shops[$key]['link'] = $link;
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') changed amazon url');
                                    }

                                    // update salesrank
                                    if ($salesrank != $old_salesrank && $salesrank != "") {
                                        update_post_meta($product->ID, 'amazon_salesrank_' . $key, $salesrank);
                                        at_write_api_log('amazon', $product->ID, '(' . $key . ') changed amazon salesrank from ' . $old_salesrank . ' to ' . $salesrank);
                                    }

                                    // update external images
                                    if (get_option('amazon_update_external_images') == 'yes') {
                                        if(get_option('amazon_images_external') == '1') {
                                            $amazon_images = $item->getExternalImages();
											
                                            if ($amazon_images) {
                                                $i = 1;
                                                foreach ($amazon_images as $image) {
                                                    $images[$i]['filename'] = sanitize_title(get_the_title($product->ID) . '-' . $i);
                                                    $images[$i]['alt'] = get_the_title($product->ID) . ' - ' . $i;
                                                    $images[$i]['url'] = $image;

                                                    if ($i == 1) {
                                                        $images[$i]['thumb'] = 'true';
                                                    }

                                                    $i++;
                                                }
                                            }


                                            if ($images) {
                                                $attachments = array();
                                                $_thumbnail_ext_url = get_post_meta($product->ID, '_thumbnail_ext_url', TRUE );;

                                                foreach ($images as $image) {
                                                    $image_filename = substr(sanitize_title($image['filename']), 0, 30);
                                                    $image_alt = (isset($image['alt']) ? $image['alt'] : '');
                                                    $image_url = $image['url'];
                                                    $image_thumb = (isset($image['thumb']) ? $image['thumb'] : '');

                                                    // skip if image already exists as post thumbnail
                                                    if($image_url == $_thumbnail_ext_url) {
                                                        continue;
                                                    }

                                                    // load images form extern
                                                    if ("true" == $image_thumb) {
                                                        // check if thumbnail does not exist
                                                        if($_thumbnail_ext_url != $image_url) {
                                                            update_post_meta($product->ID, '_thumbnail_ext_url', $image_url);
                                                            update_post_meta($product->ID, '_thumbnail_id', 'by_url');
                                                        } else {
                                                            // check if ssl is available and image is ssl
                                                            if(is_ssl() && strpos($_thumbnail_ext_url, 'https://') !== 0) {
                                                                update_post_meta($product->ID, '_thumbnail_ext_url', $image_url);
                                                                update_post_meta($product->ID, '_thumbnail_id', 'by_url' );
                                                            }
                                                        }
                                                    } else {
                                                        $attachments[] = array(
                                                            'url' => $image_url,
                                                            'alt' => $image_alt,
                                                            'hide' => ''
                                                        );
                                                    }
                                                }
                                            }

                                            if($attachments) {
                                                $product_gallery_external = get_field('product_gallery_external', $product->ID);

                                                // set old attributes for hide
                                                $i = 0;
                                                foreach ($attachments as $attachment) {
                                                    if ($product_gallery_external) {
                                                        foreach ($product_gallery_external as $k => $v) {
                                                            // fix ssl
                                                            if(is_ssl() && strpos($product_gallery_external[$k]['url'], 'https://') !== 0) {
                                                                $product_gallery_external[$k]['url'] = str_replace('http://ecx.images-amazon.com/images/', 'https://images-na.ssl-images-amazon.com/images/', $v);
                                                            }

                                                            if ($attachment['url'] == $product_gallery_external[$k]['url']) {
                                                                // overwrite url, fix damaged images
                                                                $attachments[$i]['url'] = $product_gallery_external[$k]['url'];

                                                                if ($product_gallery_external[$k]['hide'] == '1') {
                                                                    $attachments[$i]['hide'] = '1';
                                                                }

                                                                if ($product_gallery_external[$k]['alt'] != '') {
                                                                    $attachments[$i]['alt'] = $product_gallery_external[$k]['alt'];
                                                                }
                                                            }
                                                        }
                                                    }

                                                    $i++;
                                                }

                                                update_field('field_57486088e1f0d', $attachments, $product->ID);
                                            }
                                        } else {
											// remove old external images
											update_field('field_57486088e1f0d', array(), $product->ID);
											
											$product_gallery = get_field('field_553b84fb117b1', $product->ID);
																						
                                            // no external images, check if we should add internal images
											if(!$product_gallery) {
												$amazon_images = $item->getAllImages()->getLargeImages();
												$images = array();
												
												if ($amazon_images) {
													$c = 1;
													foreach ($amazon_images as $image) {
														$images[$c]['filename'] = sanitize_title($title . '-' . $c);
														$images[$c]['alt'] = $title . ' - ' . $c;
														$images[$c]['url'] = $image;

														if ($c == 1) {
															$images[$c]['thumb'] = 'true';
														}

														$c++;
													}
												}
												
												if ($images) {
													$attachments = array();

													foreach ($images as $image) {
														$image_filename = substr(sanitize_title($image['filename']), 0, 30);
														$image_alt = (isset($image['alt']) ? $image['alt'] : '');
														$image_url = $image['url'];
														$image_thumb = (isset($image['thumb']) ? $image['thumb'] : '');
														$image_exclude = (isset($image['exclude']) ? $image['exclude'] : '');
														
														if ("true" == $image_exclude) {
															continue;
														}

														if ("true" == $image_thumb) {
															$att_id = at_attach_external_image($image_url, $product->ID, true, $image_filename, array('post_title' => $image_alt));
															update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
															update_post_meta($product->ID, '_thumbnail_id', $att_id );
														} else {
															$att_id = at_attach_external_image($image_url, $product->ID, false, $image_filename, array('post_title' => $image_alt));
															update_post_meta($att_id, '_wp_attachment_image_alt', $image_alt);
															$attachments[] = $att_id;
														}
														
													}

													if ($attachments) {
														update_field('field_553b84fb117b1', $attachments, $product->ID);
													}
												}
											} else {
												// check product thumbnail
												$_thumbnail_ext_url = get_post_meta($product->ID, '_thumbnail_ext_url', true);
												$_thumbnail_id = get_post_meta($product->ID, '_thumbnail_id', true);
												
												if($_thumbnail_id == 'by_url') {
													if($_thumbnail_ext_url != "") {
														// try to set the external image as product thumbnail
														$att_id = at_attach_external_image($_thumbnail_ext_url, $product->ID, true);
													} else {
														// no image found? fuck. just set the first image from the gallery as product thumbnail
														foreach($product_gallery as $attachment) {
															$att_id = $attachment['ID'];
															break;
														}
														
														// remove this image from gallery
														unset($product_gallery[0]);
														update_field('field_553b84fb117b1', $product_gallery, $product->ID);
													}
													
													update_post_meta($product->ID, '_thumbnail_id', $att_id );
													update_post_meta($product->ID, '_thumbnail_ext_url', '' );
												} 
											}
                                        }
                                    }

                                    //update rating
                                    if (get_option('amazon_update_rating') == 'yes' || get_option('amazon_update_rating') == '1') {
                                        $rating = $item->getAverageRating();
                                        $rating_cnt = ($item->getTotalReviews() ? $item->getTotalReviews() : '0');

                                        if ($rating && $rating > 0) {
                                            //fix rating
                                            $rating = round($rating * 2) / 2;

                                            update_post_meta($product->ID, 'product_rating', $rating);
                                            update_post_meta($product->ID, 'product_rating_cnt', $rating_cnt);
                                        }
                                    }

                                    update_post_meta($product->ID, 'product_not_avail', '0');
                                    at_aws_remove_product_notification($product->ID);

                                    if (get_option('amazon_notification') == 'draft' || get_option('amazon_notification') == 'email_draft') {
                                        wp_publish_post($product->ID);
                                    }
                                }
                            } catch(Exception $e) {
                                // produkt nicht verfügbar
                                update_post_meta($product->ID, AWS_METAKEY_LAST_UPDATE, time());

                                // action
                                if (505 === $e->getCode()) {
                                    at_write_api_log('amazon', $product->ID, 'error (no/incorrect asin?)');
                                    continue;
                                }

                                if (!update_post_meta($product->ID, 'product_not_avail', '1'))
                                    continue;

                                at_write_api_log('amazon', $product->ID, 'product not available');

                                switch (get_option('amazon_notification')) {
                                    case 'email':
                                        at_aws_set_product_notification($product->ID);
                                        break;

                                    case 'draft':
                                        $args = array(
                                            'ID' => $product->ID,
                                            'post_status' => 'draft'
                                        );
                                        wp_update_post($args);

                                        break;

                                    case 'email_draft':
                                        at_aws_set_product_notification($product->ID);
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