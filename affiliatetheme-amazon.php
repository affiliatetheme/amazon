<?php
/**
 * Plugin Name: AffiliateTheme - Amazon Schnittstelle
 * Plugin URI: http://www.endcore.com
 * Description: Dieses Plugin erweitert das Affiliatetheme um eine Amazon Schnittstelle
 * Version: 0.0.6
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

            require_once(sprintf("%s/wp-updates-plugin.php", dirname(__FILE__)));
            new WPUpdatesPluginUpdater_1189( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

            register_activation_hook( __FILE__, array(&$this, 'activate'));
            register_deactivation_hook( __FILE__, array(&$this, 'deactivate'));
		} 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
            /*
             * Amazon als Shop anlegen
             */
            if(post_type_exists('shop')) {
                global $wpdb;

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
