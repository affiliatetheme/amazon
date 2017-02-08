<?php
/**
 * Plugin Name: AffiliateTheme - Amazon Schnittstelle
 * Plugin URI: http://affiliatetheme.io
 * Description: Dieses Plugin erweitert das AffiliateTheme um eine Amazon Schnittstelle
 * Version: 1.3.0
 * Author: endcore Medienagentur
 * Author URI: http://endcore.com
 * License: GPL2
 */ 
if(!class_exists('AffiliateTheme_Amazon')) {
	class AffiliateTheme_Amazon {
		public function __construct()
		{
			require_once(dirname(__FILE__) . '/class.dashboard.init.php');
			$affiliatetheme_amazon_dashboard = new AffiliateTheme_Amazon_Dashboard_Init();

            require 'plugin-update-checker/plugin-update-checker.php';
            $myUpdateChecker = PucFactory::buildUpdateChecker(
                'http://update.affiliatetheme.io/affiliatetheme-amazon.json',
                __FILE__
            );

            register_activation_hook( __FILE__, array(&$this, 'activate'));
            register_deactivation_hook( __FILE__, array(&$this, 'deactivate'));

            add_filter('http_request_args', array(&$this, 'remove_from_wp_update'), 5, 2);
		} 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
            global $wpdb;

            /**
             * Amazon als Shop anlegen
             */
            if(post_type_exists('shop')) {
                if(false == (at_aws_get_amazon_shop_id())) {
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
            */
		} 

		/**
		 * Deactivate the plugin
		 */     
		public static function deactivate()
		{
            wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', array('hash' => AWS_CRON_HASH));
		}

        /**
         * Remove Plugin from wp.org update check
         */
        public function remove_from_wp_update( $r, $url )
        {
            // If it's not a plugins update request, bail.
            if ( FALSE === strpos( $url, '//api.wordpress.org/plugins/update-check' ) ) {
                return $r;
            }

            if ( empty($r['body']['plugins']) ){
                return $r;
            }

            // Decode the JSON response
            $plugins = json_decode( $r['body']['plugins'], true );

            // Remove the plugin from the check
            $active_index = array_search( plugin_basename( __FILE__ ), $plugins['active'] );
            unset( $plugins['active'][ $active_index ] );
            unset( $plugins['plugins'][ plugin_basename( __FILE__ ) ] );

            // Encode the updated JSON response
            $r['body']['plugins'] = wp_json_encode( $plugins );

            var_dump($plugins);
            exit;

            return $r;
        }
	} 
} 

if(class_exists('AffiliateTheme_Amazon'))
{
	$affiliatetheme_amazon = new AffiliateTheme_Amazon();
}