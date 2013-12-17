<?php
/*
Plugin Name: AWPCP Seatdropper Boost
Plugin URI: http://www.brandoncamenisch.com
Description: Adds a boost button to "Another Wordpress Classifieds Plugin (AWPCP)"
Author: Brandon Camenisch
Author URI: http://www.brandoncamenisch.com
Version: 1.0.0
Text Domain: sd-boost
License: GPLv3
*/

namespace BC {

	new Boost;

	class Boost {

		function __construct() {
			#Actions
			add_action( 'init', array( $this, 'initializer' ) );
			add_action( 'init', 'BoostMisc::return_ad_edit_page' );
			#@TODO: modify this hook
			add_action( 'wp_loaded', 'BoostMisc::query_ads_owned_by_user' );

			add_action( 'awpcp_register_settings','BoostSettingsPanel::register_boost_settings' );
			add_action( 'awpcp_register_settings','BoostSettingsPanel::register_order_by_settings' );

			#Hooks
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			#Filters
			########TESTS########
			add_action( 'init', array( $this, 'tester' ) );
			########TESTS########
		}
		
		public function tester() {
		}

		public function initializer() {
			if ( ! defined( 'ABSPATH' ) && class_exists( 'AWPCP' ) ) {
				exit;
			} else {
				#define constants
				define( 'SD_BOOST_PATH', plugin_dir_path( __FILE__ ) );
				define( 'SD_BOOST_URL', plugin_dir_url( __FILE__ ) );
				define( 'SD_BOOST_NAME', plugin_basename(  __FILE__ ) );
				#Requires
				require_once SD_BOOST_PATH . 'lib/classes/class.boost-settings-panel.php';
				require_once SD_BOOST_PATH . 'lib/misc.php';

				#Shortcodes
				add_shortcode( 'AWPCPCLASSIFIEDSUIBOOSTED', 'BoostProcess::boost_shortcode' );
			}
		}

		public function activation() {
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			#add a column if it doesn't exist
			$wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS ." ADD ad_boost_time INT");
		}

		public function deactivation() {
		}

	}

}