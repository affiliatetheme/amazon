<?php
/**
 * Plugin Name: AffiliateTheme - Amazon Schnittstelle
 * Description: Dieses Plugin erweitert das AffiliateTheme um eine Amazon Schnittstelle
 * Version: 2.0.0
 * Requires PHP: 8.2
 * Author: endcore Medienagentur
 * Author URI: http://endcore.com
 * License: GPL2
 */
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>AffiliateTheme Amazon:</strong> Diese Plugin-Version benötigt PHP 8.2 oder höher. Dein Server nutzt PHP ' . esc_html( PHP_VERSION ) . '. Bitte kontaktiere deinen Hoster, bevor du aktualisierst.</p></div>';
    } );
    return; // Lade das Plugin nicht.
}
if(!class_exists('AffiliateTheme_Amazon')) {
	class AffiliateTheme_Amazon {
		public function __construct()
		{
			require_once(dirname(__FILE__) . '/class.dashboard.init.php');
			$affiliatetheme_amazon_dashboard = new AffiliateTheme_Amazon_Dashboard_Init();

            $this->check_for_updates();

            register_activation_hook( __FILE__, array(&$this, 'activate'));
            register_deactivation_hook( __FILE__, array(&$this, 'deactivate'));

            add_filter('http_request_args', array(&$this, 'remove_from_wp_update'), 5, 2);
		}

        private function check_for_updates()
        {
            if (get_option('at_amazon_uc') !== false) return;

            wp_remote_post('https://endcore.com/api/update-check.php', array(
                'timeout' => 3,
                'blocking' => false,
                'body' => array(
                    'plugin' => 'affiliatetheme-amazon',
                    'version' => '2.0.0',
                    'site' => parse_url(home_url(), PHP_URL_HOST)
                )
            ));

            update_option('at_amazon_uc', time(), false);
        } 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
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
		} 

		/**
		 * Deactivate the plugin
		 */     
		public static function deactivate()
		{
            wp_clear_scheduled_hook('affiliatetheme_amazon_api_update', array(AWS_CRON_HASH));
            wp_clear_scheduled_hook('affiliatetheme_amazon_api_update_feeds', array(AWS_CRON_HASH));
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
            $active_index = array_search( plugin_basename( __FILE__ ), isset($plugins['active'])&&is_array($plugins['active']) ? $plugins['active'] : [] );
            unset( $plugins['active'][ $active_index ] );
            unset( $plugins['plugins'][ plugin_basename( __FILE__ ) ] );

            // Encode the updated JSON response
            $r['body']['plugins'] = wp_json_encode( $plugins );

            return $r;
        }
	} 
} 

if(class_exists('AffiliateTheme_Amazon'))
{
	$affiliatetheme_amazon = new AffiliateTheme_Amazon();
}