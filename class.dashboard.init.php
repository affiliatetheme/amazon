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
            add_action('init',  array(&$this, 'load_textdomain'));

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
            require_once(AWS_PATH . '/lib/api_acf_helper.php');
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
            register_setting('endcore_api_amazon_options', 'amazon_update_run_cronjob');
			register_setting('endcore_api_amazon_options', 'amazon_update_ean');
			register_setting('endcore_api_amazon_options', 'amazon_update_price');
			register_setting('endcore_api_amazon_options', 'amazon_update_url');
			register_setting('endcore_api_amazon_options', 'amazon_update_external_images');
			register_setting('endcore_api_amazon_options', 'amazon_update_rating');
            register_setting('endcore_api_amazon_options', 'amazon_product_skip_interval');

			register_setting('endcore_api_amazon_button_options', 'amazon_buy_short_button');
			register_setting('endcore_api_amazon_button_options', 'amazon_buy_button');
			register_setting('endcore_api_amazon_button_options', 'amazon_not_avail_button');

			register_setting('endcore_api_amazon_error_handling_options', 'amazon_error_handling_replace_thumbnails');
		}
		
		/*
		 * SCRIPTS
		 */
		public function menu_scripts($page)
		{
			if('import_page_endcore_api_amazon' != $page) { 
				return; 
			}

			wp_enqueue_style('at-select2', plugin_dir_url(__FILE__) . 'view/css/select2.min.css');
			wp_enqueue_script('at-select2', plugin_dir_url(__FILE__) . 'view/js/select2.min.js', '', '1.0', false);
			wp_enqueue_script('at-amazon-functions', plugin_dir_url(__FILE__) . 'view/js/ama_functions.js', '', '1.6.0', false);

			wp_localize_script('at-amazon-functions', 'amazon_vars',
                array(
					'connection' => __('Verbindungsaufbau...', 'affiliatetheme-amazon'),
					'connection_ok' => __('Verbindung erfolgreich hergestellt.', 'affiliatetheme-amazon'),
					'connection_error' => __('Eine Verbindung zu Amazon konnte nicht hergestellt werden. Bitte prüfe deinen Public Key, Secret Key und deine Partner ID.', 'affiliatetheme-amazon'),
					'no_image' => __('Kein Bild vorhanden', 'affiliatetheme-amazon'),
					'external_product' => __('externes Produkt!', 'affiliatetheme-amazon'),
					'import' => __('Importieren', 'affiliatetheme-amazon'),
					'edit' => __('Editieren', 'affiliatetheme-amazon'),
					'no_products_found' => __('Es wurden keine Produkte gefunden. Bitte definiere deine Suche neu.', 'affiliatetheme-amazon'),
					'edit_product' => __('Produkt bearbeiten', 'affiliatetheme-amazon'),
					'import_count' => __('Importiere Produkt <span class="current">1</span> von', 'affiliatetheme-amazon'),
					'feed_success' => __('erfolgreich hinzugefügt.', 'affiliatetheme-amazon'),
					'feed_fail' => __('konnte nicht hinzugefügt werden.', 'affiliatetheme-amazon'),
					'feed_delete_success' => __('Eintrag erfolgreich gelöscht.', 'affiliatetheme-amazon'),
					'feed_delete_fail' => __('Eintrag konnte nicht gelöscht werden.', 'affiliatetheme-amazon'),
					'feed_update_success' => __('Eintrag erfolgreich aktualisiert.', 'affiliatetheme-amazon'),
					'feed_update_fail' => __('Eintrag konnte nicht aktualisiert werden.', 'affiliatetheme-amazon'),
					'adblocker_hint' => __('Bitte deaktiviere deinen Adblocker um alle Funktionen der API zu nutzen!', 'affiliatetheme-amazon'),
                    'uvp' => __('UVP', 'affiliatetheme-amazon')
				)
			);
		}

		/**
		 * menu content
		 */
		public function menu_dashboard() {
			$plugin_options = 'endcore_api_amazon_options';
			$plugin_button_options = 'endcore_api_amazon_button_options';
			$plugin_error_handling_options = 'endcore_api_amazon_error_handling_options';

			require_once(AWS_PATH . '/view/panel.php');
		} 
			
			
		/**
		 * add a menu
		 */		
		public function add_submenu_page()	{
			add_submenu_page('endcore_api_dashboard', 'Amazon', 'Amazon', apply_filters('at_set_import_dashboard_capabilities', 'administrator'), 'endcore_api_amazon', array(&$this, 'menu_dashboard'));
		}

		/**
         * load textdomain
         */
		public function load_textdomain() {
            load_plugin_textdomain('affiliatetheme-amazon', false, dirname(plugin_basename( __FILE__ )) . '/languages');
        }
    } 
} 