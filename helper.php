<?php
/*
 * Add Amazon to "Affiliate Portal" Dropdown-List
 */
add_filter( 'at_add_product_portal', 'at_add_amazon_as_portal', 10, 2 );
function at_add_amazon_as_portal( $choices ) {
	$choices['amazon'] = 'Amazon';
	return $choices;
}

/*
 * Overwrite Product Button Text
 */
add_filter('at_get_product_button_text', 'at_overwrite_product_button_text', 10, 2);
function at_overwrite_product_button_text($product_portal, $short) {
	if('amazon' == $product_portal) {
		/*
		 * @TODO: Aus Plugin Settings auslesen!
		 * @TODO: Wenn Produkt nicht Verfügbar ist, anpassen!
		 * @TODO: Amazon Icon?
		 */
		if(true == $short)
			return __('Jetzt kaufen', 'affilaitetheme');
		
		return __('Jetzt bei Amazon bestellen','affiliatetheme');
	} 	
}

/*
 * Add Amazon Status Column
 */
add_filter('manage_edit-product_columns', 'at_add_new_amazon_columns');
function at_add_new_amazon_columns($columns) {
	$columns['amazon_status'] = __('Amazon Status', 'affiliatetheme');
	
	return $columns;
}
add_action('manage_product_posts_custom_column', 'at_manage_amazon_columns', 10, 2);
function at_manage_amazon_columns($column, $post_id) {
	switch ($column) {
		case 'amazon_status':
			if('amazon' == get_field('product_portal', $post_id)) {
				if('0' == get_field('product_amazon_avail'))
					echo '<span class="badge badge-not-avail">'.__('Nicht Verfügbar', 'affilaitetheme').'</span>';
				else 
					echo '<span class="badge badge-avail">'.__('Verfügbar', 'affilaitetheme').'</span>';
			} else {
				echo '-';
			}
			break;
	}
}

/*
 * GET A SELECT LIST FOR EACH TAXONOMY
 * @return string html code (<select>)
 */
function get_products_multiselect_tax_form() {
	$taxonomy_names = get_object_taxonomies( 'produkt' );
	
	foreach($taxonomy_names as $tax) {
		if(!is_wp_error($terms = get_terms($tax, 'hide_empty=0'))) {
			$taxonomy = get_taxonomy($tax);
			$output .= '
			<div class="form-group">
				<label>'.$taxonomy->labels->name.'</label>
				
				<select fieldname="'.$taxonomy->rewrite['slug'].'" name="tax['.$tax.'][]" multiple>';
					foreach($terms as $term) {
						$output .= '<option value="'.$term->slug.'">'.$term->name.'</option>';
					}
				$output .= '</select>
				<label></label><input type="text" name="tax['.$tax.'][]" class="form-control" placeholder="Neuen Term in \''.$taxonomy->labels->name.'\' anlegen." style="margin-left:4px;margin-top:10px;"/>
			</div>
			';
		}
	}
	
	return $output;
} 

/*
 * IMPORT EXTERNAL IMAGES INTO WORDPRESS MEDIA
 * @param string $url
 * @param int $post_id
 * @param string $thumb
 * @param string $filename
 * @param array $post_data
 * @return int post_id of attachment
 */
function attach_external_image( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
    if ( !$url || !$post_id ) return new WP_Error('missing', "Need a valid URL and post ID...");
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    // Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
    $tmp = download_url( $url );

    // If error storing temporarily, unlink
    if ( is_wp_error( $tmp ) ) {
        @unlink($file_array['tmp_name']);   // clean up
        $file_array['tmp_name'] = '';
        return $tmp; // output wp_error
    }

    preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);    // fix file filename for query strings
    $url_filename = basename($matches[0]);                                                  // extract filename from url for title
    $url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)

    // override filename if given, reconstruct server path
    if ( !empty( $filename ) ) {
        $filename = sanitize_file_name($filename);
        $tmppath = pathinfo( $tmp );                                                        // extract path parts
        $new = $tmppath['dirname'] . "/". $filename . "." . $tmppath['extension'];          // build new path
        rename($tmp, $new);                                                                 // renames temp file on server
        $tmp = $new;                                                                        // push new filename (in path) to be used in file array later
    }

    // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
    $file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

    if ( !empty( $filename ) ) {
        $file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
    } else {
        $file_array['name'] = $url_filename;                                                // just use original URL filename
    }

    // set additional wp_posts columns
    if ( empty( $post_data['post_title'] ) ) {
        $post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
    }

    // make sure gets tied to parent
    if ( empty( $post_data['post_parent'] ) ) {
        $post_data['post_parent'] = $post_id;
    }

    // do the validation and storage stuff
    $att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

    // If error storing permanently, unlink
    if ( is_wp_error($att_id) ) {
        @unlink($file_array['tmp_name']);   // clean up
        return $att_id; // output wp_error
    }

    // set as post thumbnail if desired
    if ($thumb) {
        set_post_thumbnail($post_id, $att_id);
    }

    return $att_id;
}

/*
 * normalize String (replace Umlauts)
 * @param string $string
 * @return string replaced string
 */
function normalizeFilename($string)
{
	$string = str_replace("ä", "ae", $string);
	$string = str_replace("ü", "ue", $string);
	$string = str_replace("ö", "oe", $string);
	$string = str_replace("Ä", "Ae", $string);
	$string = str_replace("Ü", "Ue", $string);
	$string = str_replace("Ö", "Oe", $string);
	$string = str_replace("ß", "ss", $string);
	$string = str_replace("´", "", $string);
	$string = str_replace("?", "_", $string);
	$string = str_replace("<", "_", $string);
	$string = str_replace(">", "_", $string);
	$string = str_replace(" ", "_", $string);
	$string = str_replace("%", "_", $string);
	return $string;
}

/*
 * GET A SELECT WITH RATING VALUES (PRE SELECTED IF RATING IS GIVEN)
 * @param float $rating
 * @return string html output (<select>)
 */
function get_product_rating_list($rating = 0) {
		
	$output = '<select name="rating" id="rating" class="form-control">';
	
	for($i=0; $i<5.5; $i+= 0.5) {
		if($rating == $i) {
			$output .= '<option value="'.$i.'" selected>'.$i.' Sterne</option>';
		} else {
			$output .= '<option value="'.$i.'">'.$i.' Sterne</option>';
		}
	}		
		
	$output .= '</select>';
	
	return $output;
}

/*
 * SEND NOTIFICATION MAIL IF PRODUCT IS NOT AVAILABLE
 * @param int $produt_id
 */
function send_amazon_notifictaion_mail($produkt_id) {
	$to = get_option('admin_email');
	$sitename = get_bloginfo('name');
	function set_html_content_type() {
		return 'text/html';
	}
	
	add_filter( 'wp_mail_content_type', 'set_html_content_type' );
	$body = 'Das Produkt <a href="'.get_permalink($produkt_id).'">'.get_the_title($produkt_id).'</a> ist aktuell nicht mehr bei Amazon verfügbar.';
	wp_mail($to, $sitename.': Produkt nicht verfügbar', $body);
	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}

/*
 * REGISTER CRON SEHDULE FOR 10 MINUTES
 * @param array $shedules
 */
function cron_amazon_api_update_recurrence( $schedules ) {
	$schedules['10min'] = array(
		'display' => __( 'Every 10 Minutes', 'textdomain' ),
		'interval' => 600,
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'cron_amazon_api_update_recurrence' );

/*
 * SHEDULE EVENT FOR PRICE UPDATE
 */
if(get_option('amazon_public_key') != "" &&  get_option('amazon_secret_key') != "") {
	if( !wp_next_scheduled( 'endcore_amazon_api_update' )) {
		wp_schedule_event(time(), '10min', 'endcore_amazon_api_update');
	}
} else {
	wp_clear_scheduled_hook('endcore_amazon_api_update');
}

/*
 * PLUGIN UPDATE

$api_url = 'http://backend.c01313.de/api/';
$plugin_slug = basename(dirname(__FILE__));

add_filter('pre_set_site_transient_update_plugins', 'endcore_amazon_check_update');
function endcore_amazon_check_update($checked_data) {
	global $api_url, $wp_version;
	$plugin_slug = basename(dirname(__FILE__));
	
	//Comment out these two lines during testing.
	//if (empty($checked_data->checked))
		//return $checked_data;

	
	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	
	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);
		
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
		
	return $checked_data;
}

add_filter('plugins_api', 'endcore_amazon_api_call', 10, 3);
function endcore_amazon_api_call($def, $action, $args) {
	global $api_url, $wp_version;
	$plugin_slug = basename(dirname(__FILE__));
		
	if (!isset($args->slug) || ($args->slug != $plugin_slug))
		return false;
	
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;
	
	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	
	$request = wp_remote_post($api_url, $request_string);
		
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	
	return $res;
} */
