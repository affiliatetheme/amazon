<?php		
if(!class_exists('AffiliateTheme_Amazon_Dashboard_Init'))
{
	class AffiliateTheme_Amazon_Dashboard_Init	{
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_submenu_page'), 999);
			add_action('admin_init', array(&$this, 'settings'));
			add_action('admin_enqueue_scripts', array(&$this, 'menu_scripts'), 999);

			require_once(sprintf("%s/helper.php", dirname(__FILE__)));
            require_once(sprintf("%s/update.php", dirname(__FILE__)));
			require_once(sprintf("%s/feed.php", dirname(__FILE__)));
		
			//search
			add_action( 'wp_ajax_amazon_api_search', array(&$this, 'amazon_api_search') );
			//add_action( 'wp_ajax_nopriv_amazon_api_search', array(&$this, 'amazon_api_search') );
			
			//lookup
			add_action( 'wp_ajax_amazon_api_lookup', array(&$this, 'amazon_api_lookup') );
			//add_action( 'wp_ajax_nopriv_amazon_api_lookup', array(&$this, 'amazon_api_lookup') );
			
			//import
			add_action( 'wp_ajax_amazon_api_import', array(&$this, 'amazon_api_import') );
			//add_action( 'wp_ajax_nopriv_amazon_api_import', array(&$this, 'amazon_api_import') );

            //grab
			add_action( 'wp_ajax_amazon_api_grab', array(&$this, 'amazon_api_grab') );
			//add_action( 'wp_ajax_nopriv_amazon_api_grab', array(&$this, 'amazon_api_grab') );

		}
		
		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init() {
			
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
			if('import_page_endcore_api_amazon' != $page) return;

			wp_enqueue_script('at-select2', plugin_dir_url( __FILE__ ).'view/js/select2.min.js', '1.0', true);
			wp_enqueue_style('at-select2', plugin_dir_url( __FILE__ ).'view/css/select2.min.css');
			wp_enqueue_script('at-amazon-functions', plugin_dir_url( __FILE__ ).'view/js/ama_functions.js', '1.0', true);
		}

		/**
		 * menu content
		 */
		public function menu_dashboard() {
			$plugin_options = 'endcore_api_amazon_options';
			$plugin_button_options = 'endcore_api_amazon_button_options';
			
			require_once(sprintf("%s/view/panel.php", dirname(__FILE__)));
		} 
			
			
		/**
		 * add a menu
		 */		
		public function add_submenu_page()	{
			add_submenu_page('endcore_api_dashboard', 'Amazon', 'Amazon', apply_filters('at_set_import_dashboard_capabilities', 'administrator'), 'endcore_api_amazon', array(&$this, 'menu_dashboard'));
		} 
		
		/**
		 * ajax functions
		 */	
		public function amazon_api_search() {
			require_once(sprintf("%s/search.php", dirname(__FILE__)));
		}
		public function amazon_api_lookup() {
			require_once(sprintf("%s/lookup.php", dirname(__FILE__)));
		}
		public function amazon_api_import() {
			require_once(sprintf("%s/import.php", dirname(__FILE__)));
		}
        public function amazon_api_grab(){
            require_once(sprintf("%s/grab.php", dirname(__FILE__)));
        }
    } 
} 