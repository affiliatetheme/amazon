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
            register_setting('endcore_api_amazon_options', 'amazon_import_description');
		}
		
		/*
		 * SCRIPTS
		 */
		public function menu_scripts($page)
		{
			if('import_page_endcore_api_amazon' != $page) return;
			
			wp_enqueue_script('endcore_api_amazon_functions', plugin_dir_url( __FILE__ ).'view/js/ama_functions.js', '1.0', true);
		}

		/**
		 * menu content
		 */
		public function menu_dashboard() {
			$plugin_options = 'endcore_api_amazon_options';
			
			require_once(sprintf("%s/view/panel.php", dirname(__FILE__)));
		} 
			
			
		/**
		 * add a menu
		 */		
		public function add_submenu_page()	{
			add_submenu_page('endcore_api_dashboard', 'Amazon', 'Amazon', 'administrator', 'endcore_api_amazon', array(&$this, 'menu_dashboard'));
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