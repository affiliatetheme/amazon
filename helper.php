<?php
/**
 * helper
 * Copyright 2015 - endcore
 */
require_once dirname(__FILE__) . '/config.php';

/*
 * Lade Sprachdateien
 */
add_action('plugins_loaded', 'at_aws_load_textdomain');
function at_aws_load_textdomain() {
    load_plugin_textdomain( 'affiliatetheme-amazon', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

/*
 * Hilfsfunktionen für Arrays
 */
function amazon_array_insert(&$array, $position, $insert) {
    if(!is_array($array))
        return;

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
 * Amazon SearchIndex
 */
function at_aws_search_index_list($html = true, $first_all = true, $current = array()) {
    $items = array(
        'Apparel' => 'Apparel',
        'Automotive' => 'Automotive',
        'Baby' => 'Baby',
        'Blended' => 'Blended',
        'Beauty' => 'Beauty',
        'Books' => 'Bücher',
        'Classical' => 'Classical',
        'DVD' => 'DVD',
        'Electronics' => 'Elektronik',
        'ForeignBooks' => 'Foreign Books',
        'Grocery' => 'Grocery',
        'HealthPersonalCare' => 'Health Personal Care',
        'HomeGarden' => 'HomeGarden',
        'Jewelry' => 'Juwelen',
        'KindleStore' => 'Kindle Store',
        'Kitchen' => 'Küche',
        'Lighting' => 'Beleuchtung',
        'Luggage' => 'Luggage',
        'Magazines' => 'Magazine',
        'Marketplace' => 'Marketplace',
        'MP3Downloads' => 'MP3 Downloads',
        'MobileApps' => 'Mobileapps',
        'Music' => 'Musik',
        'MusicalInstruments' => 'Musikinstrumente',
        'MusicTracks' => 'Lieder',
        'OfficeProducts' => 'Büro Produkte',
        'OutdoorLiving' => 'Outdoor living',
        'Outlet' => 'Outlet',
        'PCHardware' => 'PC Hardware',
        'Photo' => 'Foto',
        'Software' => 'Software',
        'SoftwareVideoGames' => 'Software Videospiele',
        'SportingGoods' => 'Sporting goods',
        'Tools' => 'Werkzeuge',
        'Toys' => 'Spielzeuge',
        'VHS' => 'VHS',
        'Video' => 'Videos',
        'VideoGames' => 'Videospiele',
        'Watches' => 'Uhren',
    );

    if($first_all == true) {
        $item = array(
            'All' => 'Alle Kategorien'
        );

        $items = array_merge($item, $items);
    }

    $output = '';

    if($html == true) {
        $output .= '<select name="category" id="category" class="form-control">';

        foreach($items as $k => $v) {
            $selected = ($k == $current ? 'selected' : '');
            $output .= '<option value="' . $k  . '" ' . $selected . '>' . $v . '</option>';
        }

        $output .= '</select>';
    } else {
        $output = $items;
    }

    return $output;
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
add_filter('at_get_amazon_product_button_text', 'at_overwrite_amazon_product_button_text', 10, 4);
function at_overwrite_amazon_product_button_text($var = '', $product_portal = '', $product_shop = '', $short = false) {
    global $post;

    if('amazon' == $product_portal) {
		/*
		 * @TODO: Amazon Icon?
		 */

        $buy_short_button = (get_option('amazon_buy_short_button') ? get_option('amazon_buy_short_button') : __('Kaufen', 'affiliatetheme-amazon'));
        $buy_button = (get_option('amazon_buy_button') ? get_option('amazon_buy_button') : __('Jetzt bei Amazon kaufen', 'affiliatetheme-amazon'));
        $not_avail_button = (get_option('amazon_not_avail_button') ? get_option('amazon_not_avail_button') : __('Nicht Verfügbar', 'affiliatetheme-amazon'));

        if('1' == get_post_meta($post->ID, 'product_not_avail', true))
            return __($not_avail_button, 'affiliatetheme-amazon');

		if(true == $short)
			return __($buy_short_button, 'affiliatetheme-amazon');
		
		return __($buy_button,'affiliatetheme-amazon');
	} 	
}

/*
 * Add Amazon Status Column
 * @TODO: Überarbeiten!
 */
//add_filter('manage_edit-product_columns', 'at_add_new_amazon_columns');
function at_add_new_amazon_columns($columns) {
	$columns['amazon_status'] = __('Amazon Status', 'affiliatetheme-amazon');
	
	return $columns;
}
//add_action('manage_product_posts_custom_column', 'at_manage_amazon_columns', 10, 2);
function at_manage_amazon_columns($column, $post_id) {
	switch ($column) {
		case 'amazon_status':
			if('amazon' == get_field('product_portal', $post_id)) {
				if('0' == get_field('product_amazon_avail'))
					echo '<span class="badge badge-not-avail">'.__('Nicht Verfügbar', 'affiliatetheme-amazon').'</span>';
				else 
					echo '<span class="badge badge-avail">'.__('Verfügbar', 'affiliatetheme-amazon').'</span>';
			} else {
				echo '-';
			}
			break;
	}
}

/*
 * Add Amazon Fields
 */
add_filter( 'at_add_product_fields', 'at_add_amazon_field_portal_id', 10, 2 );
function at_add_amazon_field_portal_id( $fields ) {
	$new_field[] =  array (
		'key' => 'field_553b75842c246bc',
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

    amazon_array_insert($fields['fields'][4]['sub_fields'], 7, $new_field);
	return $fields;
}

/*
 * Tabs für Kundenmeinungen (Amazon Link!)
 */
add_filter( 'at_product_tabs_nav', 'at_add_amazon_product_tabs_nav', 10, 2 );
function at_add_amazon_product_tabs_nav($content, $post_id) {
    if('1' != get_option('amazon_show_reviews')) {
        return false;
    }

    $partner_tag = get_option('amazon_partner_id');
    $product_shops = get_field('product_shops', $post_id);
    $shop_id = getRepeaterRowID($product_shops, 'portal', 'amazon', false);
    $asin = $product_shops[$shop_id]['amazon_asin'];
    $link = $product_shops[$shop_id]['link'];
    $url = 'http://www.amazon.de/product-reviews/' . $asin . '/?tag=' . $partner_tag;

    // Überpüfe den Amazon Marktplatz
    if($link) {
        preg_match_all("/\\.[a-z]{2,3}(\\.[a-z]{2,3})?/m", $link, $amazon_tld);
        if($amazon_tld) {
            if($tld = $amazon_tld[0][1]) {
                $url = 'http://www.amazon' . $tld . '/product-reviews/' . $asin . '/?tag=' . $partner_tag;
            }
        }
     }

    if(!$asin) {
        return false;
    }

    $content .= '<li><a href="' . $url . '" rel="nofollow" target="_blank">' . __('Kundenrezensionen', 'affiliatetheme-amazon') . '</a></li>';

    echo $content;
}

/*
 * Liefert die ID des Amazon Shops
 */
function get_amazon_shop_id() {
    global $wpdb;

    if($shop_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'unique_identifier' AND meta_value = 'amazon' LIMIT 0,1")) {
        return $shop_id;
    }

    return false;
}

/*
 * Notification Mail Option
 */
function set_product_notification($post_id) {
    $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());

    if(!is_array($products))
        return;

    $products[] = $post_id;
    $products = array_unique($products);

    update_option('at_amazon_notification_items', $products);
}

function remove_product_notification($post_id) {
    $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());

    if(!is_array($products))
        return;

    if(($key = array_search($post_id, $products)) !== false) {
        unset($products[$key]);
    }

    update_option('at_amazon_notification_items', $products);
}

/*
 * Send Notification Mail
 *
 */
if(get_option('amazon_notification') == "email" ||  get_option('amazon_notification') == "email_draft") {
    if( !wp_next_scheduled( 'affiliatetheme_send_amazon_notification_mail')) {
        wp_schedule_event(time(), 'daily', 'affiliatetheme_send_amazon_notification_mail');
    }
} else {
    wp_clear_scheduled_hook('affiliatetheme_send_amazon_notification_mail');
}
add_action('wp_ajax_at_send_amazon_notification_mail', 'at_send_amazon_notification_mail');
add_action('affiliatetheme_send_amazon_notification_mail', 'at_send_amazon_notification_mail');
function at_send_amazon_notification_mail($produkt_id) {
    $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());
    $to = get_option('admin_email');
    $sitename = get_bloginfo('name');

    if(!is_array($products) || empty($products))
        return;

    if($products) {
        $product_table = '';
        foreach($products as $item) {
            if(!get_post_status($item)) {
                remove_product_notification($item);
                continue;
            }

            $product_table .= '
                <tr>
                    <td style="padding: 5px; border-top: 1px solid #eee;">' . $item . '</td>
                    <td style="padding: 5px; border-top: 1px solid #eee;"><a href="' . get_permalink($item). '" target="_blank">' . get_the_title($item). '</a></td>
                    <td style="padding: 5px; border-top: 1px solid #eee;">' . get_product_last_update($item) . '</td>
                </tr>
            ';
        }

        if(!$product_table)
            exit;

        $body = file_get_contents(__DIR__ . '/view/email.html');
        $body = str_replace('%%BLOGNAME%%', $sitename, $body);
        $body = str_replace('%%BLOGURL%%', '<a href="' . home_url() . '" target="_blank">' . home_url('') . '</a>', $body);
        $body = str_replace('%%PRODUCTS%%', $product_table, $body);
        $body = str_replace('%%AMAZON_API_SETTINGS_URL%%', admin_url("admin.php?page=endcore_api_amazon"), $body);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $sitename . ': Nicht verfügbare Produkte', $body, $headers);
    }
}

/*
 * Feed: Read
 */
function at_amazon_feed_read() {
    global $wpdb;

    $feed = $wpdb->get_results (
        "
        SELECT * FROM " . AWS_FEED_TABLE . "
        "
    );

    if($feed)
        return $feed;

    return;
}

/*
 * Feed: Add Keyword
 */
function at_amazon_feed_write($keyword, $category) {
    if(!$keyword || !$category)
        return;

    global $wpdb;

    $status = $wpdb->insert(
        AWS_FEED_TABLE,
        array(
            'keyword'       => $keyword,
            'category'      => $category,
            'last_message'  => sprintf(__('hinzugefügt: %s', 'affiliatetheme-amazon'), date('d.m.Y G:i:s')),
            'post_status'   => 'publish'
        ),
        array(
            '%s',
            '%s',
            '%s'
        )
    );

    return $status;
}

add_action('wp_ajax_at_amazon_feed_write_ajax', 'at_amazon_feed_write_ajax');
function at_amazon_feed_write_ajax() {
    $keyword = (isset($_POST['keyword']) ? $_POST['keyword'] : '');
    $category = (isset($_POST['category']) ? $_POST['category'] : '');

    if(!$keyword || !$category) {
        echo json_encode(array('status' => 'error'));
        exit;
    }

    at_amazon_feed_write($keyword, $category);

    echo json_encode(array('status' => 'ok'));
    exit;
}

/*
 * Feed: Status Label
 */
function at_amazon_feed_status_label($status) {
    switch($status) {
        case '1':
            return __('aktiv', 'affiliatetheme-amazon');

        case '0':
            return __('inaktiv', 'affiliatetheme-amazon');
    }

    return;
}

/*
 * Feed: Change Status
 */
function at_amazon_feed_change_status($id, $status) {
    if($id == 'undefined')
        return;

    global $wpdb;

    $status = $wpdb->update(
        AWS_FEED_TABLE,
        array(
            'status'    => $status,
        ),
        array( 'id' => $id ),
        array(
            '%d'	// value2
        ),
        array( '%d' )
    );

    return $status;
}

add_action('wp_ajax_at_amazon_feed_change_status_ajax', 'at_amazon_feed_change_status_ajax');
function at_amazon_feed_change_status_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');
    $status = (isset($_POST['status']) ? $_POST['status'] : '');

    if(at_amazon_feed_change_status($id, $status)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}

/*
 * Feed: Change Settings
 */
function at_amazon_feed_change_settings($id, $data) {
    if($id == 'undefined')
        return;

    global $wpdb;

    $status = $wpdb->update(
        AWS_FEED_TABLE,
        $data,
        array( 'id' => $id )
    );
    
    return $status;
}

add_action('wp_ajax_at_amazon_feed_change_settings_ajax', 'at_amazon_feed_change_settings_ajax');
function at_amazon_feed_change_settings_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');

    $data = $_POST;

    unset($data['id']);
    unset($data['action']);

    if(isset($data['tax'])) {
        $tax_data = serialize($data['tax']);
        $data['tax'] = $tax_data;
    }

    if(at_amazon_feed_change_settings($id, $data)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}

/*
 * Feed: Delete Keyword
 */
function at_amazon_feed_delete($id) {
    if($id == 'undefined' || !$id)
        return;

    global $wpdb;

    $status = $wpdb->delete(AWS_FEED_TABLE, array('id' => $id));

    return $status;
}

add_action('wp_ajax_at_amazon_feed_delete_ajax', 'at_amazon_feed_delete_ajax');
function at_amazon_feed_delete_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');

    if(at_amazon_feed_delete($id)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}

