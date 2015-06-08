<?php
/**
 * helper
 * Copyright 2015 - endcore
 */
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
function at_overwrite_product_button_text($var = '', $product_portal = '', $short = false) {
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
 * Add Affilinet Fields
 */
add_filter( 'at_add_product_fields', 'at_add_amazon_field_portal_id', 10, 2 );
function at_add_amazon_field_portal_id( $fields ) {
	$new_field[] =  array (
		'key' => 'field_553b842c246bc',
		'label' => 'Amazon ASIN',
		'name' => 'product_amazon_asin',
		'type' => 'text',
		'instructions' => '',
		'required' => 0,
		'conditional_logic' => array (
			array (
				array (
					'field' => 'field_553b83de246bb',
					'operator' => '==',
					'value' => 'amazon',
				),
			),
		),
		'wrapper' => array (
			'width' => 50,
			'class' => '',
			'id' => '',
		),
		'default_value' => '',
		'placeholder' => '',
		'prepend' => '',
		'append' => '',
		'maxlength' => '',
		'readonly' => 0,
		'disabled' => 0,
	);

	array_insert($fields['fields'], 6, $new_field);
	return $fields;
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
