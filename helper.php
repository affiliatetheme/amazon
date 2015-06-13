<?php
/**
 * helper
 * Copyright 2015 - endcore
 */
require_once dirname(__FILE__) . '/config.php';

/*
 * Hilfsfunktionen für Arrays
 */
function amazon_array_insert(&$array, $position, $insert) {
    if (is_int($position)) {
        array_splice($array, $position, 0, $insert);
    } else {
        $pos   = array_search($position, array_keys($array));
        $array = array_merge(
            array_slice($array, 0, $pos),
            $insert,
            array_slice($array, $pos)
        );
    }
}

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
		'name' => 'amazon_asin',
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
			'width' => 25,
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

	amazon_array_insert($fields['fields'][1]['sub_fields'], 5, $new_field);
	return $fields;
}

/*
 * SEND NOTIFICATION MAIL IF PRODUCT IS NOT AVAILABLE
 * @param int $produt_id
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
} */