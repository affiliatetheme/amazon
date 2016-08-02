<?php		
if(!class_exists('AffiliateTheme_Amazon_Dashboard_Init'))
{
	class AffiliateTheme_Amazon_Dashboard_Init	{
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			global $wpdb;

			// actions
			add_action('admin_menu', array(&$this, 'add_submenu_page'), 999);
			add_action('admin_init', array(&$this, 'settings'));
			add_action('admin_enqueue_scripts', array(&$this, 'menu_scripts'), 999);

            // vars
            define('AWS_PATH', plugin_dir_path( __FILE__ ) );
            define('AWS_COUNTRY', get_option('amazon_country'));
            define('AWS_API_KEY', get_option('amazon_public_key'));
            define('AWS_API_SECRET_KEY', get_option('amazon_secret_key'));
            define('AWS_ASSOCIATE_TAG', get_option('amazon_partner_id'));
            define('AWS_PRICE', 'default');
            define('AWS_METAKEY_ID', 'amazon_asin');
            define('AWS_METAKEY_LAST_UPDATE', 'last_product_price_check');
            define('AWS_CRON_HASH', md5(get_option('amazon_public_key') . get_option('amazon_secret_key')));
            define('AWS_FEED_TABLE', $wpdb->prefix . 'aws_feed');

			// helpers
			require_once(AWS_PATH . '/lib/bootstrap.php');
			require_once(AWS_PATH . '/lib/api_helper.php');
			require_once(AWS_PATH . '/lib/api_search.php');
			require_once(AWS_PATH . '/lib/api_lookup.php');
			require_once(AWS_PATH . '/lib/api_import.php');
			require_once(AWS_PATH . '/lib/api_update.php');
			require_once(AWS_PATH . '/lib/api_grab.php');
		}
		
		/*
		 * SETTINGS
		 */
		public function settings()
		{
			register_setting('endcore_api_amazon_options', 'amazon_public_key');
			register_setting('endcore_api_amazon_options', 'amazon_secret_key');
			register_setting('endcore_api_amazon_options', 'amazon_partner_id');
			register_setting('endcore_api_amazon_options', 'amazon_country');
			register_setting('endcore_api_amazon_options', 'amazon_notification');
            register_setting('endcore_api_amazon_options', 'amazon_post_status');
            register_setting('endcore_api_amazon_options', 'amazon_import_description');
			register_setting('endcore_api_amazon_options', 'amazon_images_external');
			register_setting('endcore_api_amazon_options', 'amazon_images_external_size');
			register_setting('endcore_api_amazon_options', 'amazon_show_reviews');
			register_setting('endcore_api_amazon_options', 'amazon_update_ean');
			register_setting('endcore_api_amazon_options', 'amazon_update_price');
			register_setting('endcore_api_amazon_options', 'amazon_update_url');
			register_setting('endcore_api_amazon_options', 'amazon_update_external_images');
			register_setting('endcore_api_amazon_options', 'amazon_update_rating');

			register_setting('endcore_api_amazon_button_options', 'amazon_buy_short_button');
			register_setting('endcore_api_amazon_button_options', 'amazon_buy_button');
			register_setting('endcore_api_amazon_button_options', 'amazon_not_avail_button');
		}
		
		/*
		 * SCRIPTS
		 */
		public function menu_scripts($page)
		{
			if('import_page_endcore_api_amazon' != $page) { 
				return; 
			}

			wp_enqueue_script('at-select2', plugin_dir_url(__FILE__) . 'view/js/select2.min.js', '1.0', true);
			wp_enqueue_style('at-select2', plugin_dir_url(__FILE__) . 'view/css/select2.min.css');
			wp_enqueue_script('at-amazon-functions', plugin_dir_url(__FILE__) . 'view/js/ama_functions.js', '1.1', true);
		}

		/**
		 * menu content
		 */
		public function menu_dashboard() {
			$plugin_options = 'endcore_api_amazon_options';
			$plugin_button_options = 'endcore_api_amazon_button_options';
			
			require_once(AWS_PATH . '/view/panel.php');
		} 
			
			
		/**
		 * add a menu
		 */		
		public function add_submenu_page()	{
			add_submenu_page('endcore_api_dashboard', 'Amazon', 'Amazon', apply_filters('at_set_import_dashboard_capabilities', 'administrator'), 'endcore_api_amazon', array(&$this, 'menu_dashboard'));
		}
    } 
} 