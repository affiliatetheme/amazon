<?php
/**
 * Plugin Name: Affiliatetheme.de - Amazon Schnitstelle
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Dieses Plugin erweitert das Affiliatetheme um eine Amazon Schnittstelle
 * Version: 0.1
 * Author: endcore Medienagentur
 * Author URI: http://endcore.com
 * License: GPL2
 */
 
if(!class_exists('AffiliateTheme_Amazon')) {
	class AffiliateTheme_Amazon {
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			require_once(sprintf("%s/class.dashboard.init.php", dirname(__FILE__)));
			$affiliatetheme_amazon_dashboard = new AffiliateTheme_Amazon_Dashboard_Init();
		} 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			
		
		} 

		/**
		 * Deactivate the plugin
		 */     
		public static function deactivate()
		{
		    // Do nothing
		} 
	} 
} 

if(class_exists('AffiliateTheme_Amazon'))
{
	$affiliatetheme_amazon = new AffiliateTheme_Amazon();
}