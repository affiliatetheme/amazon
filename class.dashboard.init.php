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
			
			//search
			add_action( 'wp_ajax_amazon_api_search', array(&$this, 'amazon_api_search') );
			//add_action( 'wp_ajax_nopriv_amazon_api_search', array(&$this, 'amazon_api_search') );
			
			//lookup
			add_action( 'wp_ajax_amazon_api_lookup', array(&$this, 'amazon_api_lookup') );
			//add_action( 'wp_ajax_nopriv_amazon_api_lookup', array(&$this, 'amazon_api_lookup') );
			
			//import
			add_action( 'wp_ajax_amazon_api_import', array(&$this, 'amazon_api_import') );
			//add_action( 'wp_ajax_nopriv_amazon_api_import', array(&$this, 'amazon_api_import') );
			
			//update
			//add_action( 'wp_ajax_amazon_api_update', array(&$this, 'amazon_api_update') );
			//add_action( 'wp_ajax_nopriv_amazon_api_update', array(&$this, 'amazon_api_update') );
			add_action( 'endcore_amazon_api_update', array(&$this, 'amazon_api_update') );			
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
			register_setting('endcore_api_amazon_options', 'amazon_benachrichtigung');
		}
		
		/*
		 * SCRIPTS
		 */
		public function menu_scripts()
		{
			wp_enqueue_script('endcore_api_amazon_functions', plugin_dir_url( __FILE__ ).'view/js/ama_functions.js', '1.0', true);
			wp_enqueue_script('endcore_api_amazon_stuff', plugin_dir_url( __FILE__ ).'view/js/stuff.js', '1.0', true);
			wp_enqueue_style('endcore_api_amazon_css',  plugin_dir_url( __FILE__ ).'view/css/style.css');
			wp_enqueue_style('font-awesome-420', '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
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
		public function amazon_api_update() {
			require_once(sprintf("%s/update.php", dirname(__FILE__)));
		}
    } 
} 

//helper
function get_products_multiselect_tax_form() {
	$taxonomy_names = get_object_taxonomies( 'produkt' );
	
	foreach($taxonomy_names as $tax) {
		if(!is_wp_error($terms = get_terms($tax, 'hide_empty=0'))) {
			$taxonomy = get_taxonomy($tax);
			$output .= '
			<div class="form-group">
				<label>'.$taxonomy->labels->name.'</label>
				
				<select fieldname="'.$taxonomy->rewrite['slug'].'" name="tax['.$tax.'][]" multiple>';
					foreach($terms as $term) {
						$output .= '<option value="'.$term->slug.'">'.$term->name.'</option>';
					}
				$output .= '</select>
				<label></label><input type="text" name="tax['.$tax.'][]" class="form-control" placeholder="Neuen Term in \''.$taxonomy->labels->name.'\' anlegen." style="margin-left:4px;margin-top:10px;"/>
			</div>
			';
		}
	}
	
	return $output;
} 

//external images to db
function attach_external_image( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
    if ( !$url || !$post_id ) return new WP_Error('missing', "Need a valid URL and post ID...");
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    // Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
    $tmp = download_url( $url );

    // If error storing temporarily, unlink
    if ( is_wp_error( $tmp ) ) {
        @unlink($file_array['tmp_name']);   // clean up
        $file_array['tmp_name'] = '';
        return $tmp; // output wp_error
    }

    preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);    // fix file filename for query strings
    $url_filename = basename($matches[0]);                                                  // extract filename from url for title
    $url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)

    // override filename if given, reconstruct server path
    if ( !empty( $filename ) ) {
        $filename = sanitize_file_name($filename);
        $tmppath = pathinfo( $tmp );                                                        // extract path parts
        $new = $tmppath['dirname'] . "/". $filename . "." . $tmppath['extension'];          // build new path
        rename($tmp, $new);                                                                 // renames temp file on server
        $tmp = $new;                                                                        // push new filename (in path) to be used in file array later
    }

    // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
    $file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

    if ( !empty( $filename ) ) {
        $file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
    } else {
        $file_array['name'] = $url_filename;                                                // just use original URL filename
    }

    // set additional wp_posts columns
    if ( empty( $post_data['post_title'] ) ) {
        $post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
    }

    // make sure gets tied to parent
    if ( empty( $post_data['post_parent'] ) ) {
        $post_data['post_parent'] = $post_id;
    }

    // do the validation and storage stuff
    $att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

    // If error storing permanently, unlink
    if ( is_wp_error($att_id) ) {
        @unlink($file_array['tmp_name']);   // clean up
        return $att_id; // output wp_error
    }

    // set as post thumbnail if desired
    if ($thumb) {
        set_post_thumbnail($post_id, $att_id);
    }

    return $att_id;
}

//normalize filename
function normalizeFilename($string)
{
	$string = str_replace("ä", "ae", $string);
	$string = str_replace("ü", "ue", $string);
	$string = str_replace("ö", "oe", $string);
	$string = str_replace("Ä", "Ae", $string);
	$string = str_replace("Ü", "Ue", $string);
	$string = str_replace("Ö", "Oe", $string);
	$string = str_replace("ß", "ss", $string);
	$string = str_replace("´", "", $string);
	$string = str_replace("?", "_", $string);
	$string = str_replace("<", "_", $string);
	$string = str_replace(">", "_", $string);
	$string = str_replace(" ", "_", $string);
	$string = str_replace("%", "_", $string);
	return $string;
}

//
function get_product_rating_list($rating = 0) {
		
	$output = '<select name="rating" id="rating" class="form-control">';
	
	for($i=0; $i<5.5; $i+= 0.5) {
		if($rating == $i) {
			$output .= '<option value="'.$i.'" selected>'.$i.' Sterne</option>';
		} else {
			$output .= '<option value="'.$i.'">'.$i.' Sterne</option>';
		}
	}		
		
	$output .= '</select>';
	
	return $output;
}

// send amazon notification mail
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
}

function cron_amazon_api_update_recurrence( $schedules ) {
	$schedules['10min'] = array(
		'display' => __( 'Every 10 Minutes', 'textdomain' ),
		'interval' => 600,
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'cron_amazon_api_update_recurrence' );

if(get_option('amazon_public_key') != "" &&  get_option('amazon_secret_key') != "") {
	if( !wp_next_scheduled( 'endcore_amazon_api_update' )) {
		wp_schedule_event(time(), '10min', 'endcore_amazon_api_update');
	}
} else {
	wp_clear_scheduled_hook('endcore_amazon_api_update');
}