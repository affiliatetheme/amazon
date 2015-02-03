<?php
require_once ABSPATH.'/wp-load.php';
$nonce = $_POST['_wpnonce'];

//echo '<pre>'; print_r($_POST); echo '</pre>'; exit();

if ( ! wp_verify_nonce( $nonce, 'endcore_amazon_import_wpnonce' ) ) {
	die('Security Check failed');
} else {
	$asin = $_POST['asin'];
	$title = $_POST['title'];
	$price = floatval($_POST['price']);
	$rating = floatval($_POST['rating']);
	$rating_cnt = intval($_POST['rating_cnt']);
	$taxs = $_POST['tax'];
	$images = $_POST['image'];
	
	if($asin && $title && $price) {
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
				if($rating_cnt) update_post_meta($produkt_id, 'sterne_bewertung_cnt', $rating_count);
				
				//taxonomie
				if($taxs) {
					foreach($taxs as $key => $value) {
						wp_set_object_terms($produkt_id, $value, $key, true);
					}
				}
				
				if($images) {
					foreach($images as $image) {
						$filename = trim(normalizeFilename($image['filename']));
						$alt = $image['alt'];
						$url = $image['url'];
						$title = get_the_title($produkt->ID);
						if($image['thumb'] == true) {
							$thumb = true;
						} else {
							$thumb = false;
						}
						
						if($image['exclude'] != "true") {
							$att_id = attach_external_image($url, $produkt_id, $thumb, $filename, array('post_title' => $alt));
							update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
						}
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
	}
}

echo json_encode($output);
exit();
?>