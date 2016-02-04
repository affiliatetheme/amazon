<?php
/**
 * Plugin Name: AffiliateTheme - Amazon Schnittstelle
 * Plugin URI: http://www.endcore.com
 * Description: Dieses Plugin erweitert das AffiliateTheme um eine Amazon Schnittstelle
 * Version: 1.1.0
 * Author: endcore Medienagentur
 * Author URI: http://endcore.com
 * License: GPL2
 */ 
if(!class_exists('AffiliateTheme_Amazon')) {
	class AffiliateTheme_Amazon {
		/**affiliatetheme-amazon
		 * Construct the plugin object
		 */
		public function __construct()
		{
			require_once(sprintf("%s/class.dashboard.init.php", dirname(__FILE__)));
			$affiliatetheme_amazon_dashboard = new AffiliateTheme_Amazon_Dashboard_Init();

            register_activation_hook( __FILE__, array(&$this, 'activate'));
            register_deactivation_hook( __FILE__, array(&$this, 'deactivate'));
		} 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
            global $wpdb;

            /*
             * Amazon als Shop anlegen
             */
            if(post_type_exists('shop')) {
                if(false == (get_amazon_shop_id())) {
                    $args = array(
                        'post_status'           => 'publish',
                        'post_type'             => 'shop',
                        'post_title'            => 'Amazon'
                    );
                    $shop_id = wp_insert_post($args);

                    if($shop_id) {
                        add_post_meta($shop_id, 'unique_identifier', 'amazon');
                    }
                }
            }

            /*
            * Installiere Tabelle
            */
            if($wpdb->get_var("show tables like '" . AWS_FEED_TABLE . "'") != AWS_FEED_TABLE)
            {
                $sql = "CREATE TABLE " . AWS_FEED_TABLE . " (
                    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    keyword text,
                    category text,
                    last_message text,
                    last_update timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    status int(1) DEFAULT '1',
                    tax text,
                    images int(1) DEFAULT '1',
                    description int(1) DEFAULT '0',
                    post_status text
                );";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
		} 

		/**
		 * Deactivate the plugin
		 */     
		public static function deactivate()
		{
            wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', array('hash' => AWS_CRON_HASH));
		} 
	} 
} 

if(class_exists('AffiliateTheme_Amazon'))
{
	$affiliatetheme_amazon = new AffiliateTheme_Amazon();
}

/**
 *  Plugin Updater
 */
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data(__FILE__);
$plugin_version = $plugin_data['Version'];

define( 'AT_AMAZON_STORE_URL', 'http://affiliatetheme.io' );
define( 'AT_AMAZON_ITEM_NAME', 'Amazon Schnittstelle' );
define( 'AT_AMAZON_ITEM_ID', 19554 );
define( 'AT_AMAZON_VERSION', $plugin_version);

if( !class_exists( 'AT_Amazon_Plugin_Updater' ) ) {
    include( dirname( __FILE__ ) . '/updater/AT_Amazon_Plugin_Updater.php' );
}

add_action( 'admin_init', 'at_amazon_plugin_updater', 0 );
function at_amazon_plugin_updater() {
    $license_key = '5a282e7e5109995cbae9d582936f6d7b';
    $updater = new AT_Amazon_Plugin_Updater( AT_AMAZON_STORE_URL, __FILE__, array(
            'version' 	=> AT_AMAZON_VERSION,
            'license' 	=> $license_key,
            'item_name' => AT_AMAZON_ITEM_NAME,
            'author' 	=> 'endcore Medienagentur'
        )
    );
}