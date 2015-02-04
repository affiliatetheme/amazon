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

$lookup = new Lookup();
$lookup->setItemId($_GET['asin']);
$lookup->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'Offers', 'OfferFull', 'Images', 'Reviews'));

/* @var $formattedResponse Amazon\SingleResultSet */
$formattedResponse = $apaiIO->runOperation($lookup);

if ($formattedResponse->hasItem()) {
	$item = $formattedResponse->getItem();
	
	$asin = $item->ASIN;
	$title = $item->Title;
	$price = $item->getAmountForAvailability();
	$images = $item->getAllImages()->getLargeImages();
	$average_rating = $item->getAverageRating();
	$average_rating_rounded = round($average_rating / .5) * .5;
	if($item->getTotalReviews()): $rating_cnt = $item->getTotalReviews(); else : $rating_cnt = 0; endif;
    $total_reviews = $item->getTotalReviews();
	
	/* DEBUG
	echo '<pre>';
	print_r($item);
	echo '</pre>';
	*/
		
	if("modal" === $_GET['func']) {
		// single import			
		$html .= '<div class="wrap">';
			$html .= '<h1>Produkt bearbeiten & Importieren</h1>';
			$html .= '<form action="" id="import-product">';
				$html .= '<div class="form-group"><label>ASIN</label> <input type="text" id="asin" name="asin" class="form-control" value="'.$asin.'" readonly/></div>';
				$html .= '<div class="form-group"><label>Titel</label> <input type="text" id="title" name="title" class="form-control" value="'.$title.'"/></div>';
				$html .= '<div class="form-group"><label>Preis</label> <input type="text" id="price" name="price" class="form-control" value="'.$price.'" readonly/> EUR</div>';
				$html .= '<div class="form-group"><label>Bewertung</label>'.get_product_rating_list($average_rating_rounded).' ('.$rating_cnt.' Bewertungen)</div>';
				$html .= get_products_multiselect_tax_form();
                if($item->hasImages()) {
                    $images = $item->getAllImages()->getLargeImages();

                    $html .= '<div class="form-group"><h3>Bilder</h3>';
                    $i = 1;
                    foreach ($images as $image) {
                        $image_info = explode('/', $image);
                        $image_info = array_pop($image_info);
                        $image_info = pathinfo($image_info);
                        $image_filename = $image_info['filename'];
                        $image_ext = $image_info['extension'];

                        $html .= '<div class="image" data-item="' . $i . '">';
                        $html .= '<div class="row">';
                        $html .= '<div class="col-xs-3"><img src="' . $image . '" class="img-responsive"/></div>';
                        $html .= '<div class="col-xs-9">';
                        $html .= '<div class="form-group small"><label>Bildname</label> <input type="text" name="image[' . $i . '][filename]" data-url="' . $image . '" id="image[' . $i . '][filename]" value="' . $image_filename . '" /> .' . $image_ext . '</div>';
                        $html .= '<div class="form-group small"><label>ALT-Tag</label> <input type="text" name="image[' . $i . '][alt]" id="image[' . $i . '][alt]" value="" /></div>';
                        $html .= '<div class="row">';
							$html .= '<div class="col-xs-6">';
                       			$html .= '<div class="form-group small"><label>Artikelbild</label> <input type="checkbox" name="image[' . $i . '][thumb]" value="true" class="unique"/></div>';
							$html .= '</div><div class="col-xs-6">';
								$html .= '<div class="form-group small"><label>Nicht importieren</label> <input type="checkbox" name="image[' . $i . '][exclude]" value="true" class="disable-this"/></div>';
                       		$html .= '</div>';
						$html .= '<div class="clearfix"></div></div>';
					    $html .= '<input type="hidden" name="image[' . $i . '][url]" value="' . $image . '"/>';
                        $html .= '</div>';
                        $html .= '<div class="clearfix"></div>';
                        $html .= '</div>';
                        $html .= '</div>';

                        $i++;
                    }
                }
				$html .= '</div>';
				$html .= '<div class="form-group">';
					$html .= '<input type="hidden" name="_wpnonce" value="'.wp_create_nonce("endcore_amazon_import_wpnonce").'" /><input type="hidden" name="rating_cnt" id="rating_cnt" value="'.$rating_cnt.'" /><input type="hidden" name="action" value="amazon_api_import" /><input type="hidden" name="mass" value="false" />';
					$html .= '<button type="submit" id="import" name="import" class="single-import-product button button-primary">Importieren</button>';
				$html .= '</div>';
			$html .= '</form>';
		$html .= '</div>';
		echo $html;

	} else if("quick-import" === $_GET['func']) {
		if(!$check = get_posts('post_type=produkt&posts_per_page=1&meta_key=amazon_produkt_id&meta_value='.$asin)) {
			$args = array(
				'post_title' => $title,
				'post_status' => 'publish',
				'post_type' => 'produkt',
			);
						
			$produkt_id = wp_insert_post($args);
			if($produkt_id) {
				//customfields
				update_post_meta($produkt_id, 'amazon_produkt_id', $asin);
				update_post_meta($produkt_id, 'preis', $price);
				update_post_meta($produkt_id, 'link', 'http://www.amazon.de/dp/'.$asin.'/');
				update_post_meta($produkt_id, 'produkt_verfuegbarkeit', '1');
				update_post_meta($produkt_id, 'last_amazon_check', time());
				if($rating) update_post_meta($produkt_id, 'sterne_bewertung', $rating);
				
				//taxonomie
				if($taxs) {
					foreach($taxs as $key => $value) {
						wp_set_object_terms($produkt_id, $value, $key, true);
					}
				}
				
				if($images) {
					$i=0;
					foreach($images as $image) {
						$title = get_the_title($produkt->ID);
						
						if($i == 0) {
							$thumb = true;	
						} else {
							$thumb = false;
						}
						$att_id = attach_external_image($image, $produkt_id, $thumb, $asin.'-'.$i, array('post_title' => $title));

						$i++;
					}
				}
			}
			
			
			$output['rmessage']['success'] = 'true';
			$output['rmessage']['post_id'] = $produkt_id;
		} else {
			$output['rmessage']['success'] = 'false';
			$output['rmessage']['reason'] = 'Dieses Produkt existiert bereits.';
			$output['rmessage']['post_id'] = $check[0]->ID;
		}

		echo json_encode($output);
	}
}

exit();