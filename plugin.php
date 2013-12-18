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
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );

			add_action( 'init', 'BoostMisc::return_ad_edit_page' );
			add_action( 'awpcp_register_settings', 'BoostSettingsPanel::register_boost_settings' );
			add_action( 'awpcp_register_settings', 'BoostSettingsPanel::register_order_by_settings' );
			add_action( 'wp_ajax_nopriv_update_boosted_time_values', 'BoostMisc::update_boosted_time_values' );
			add_action( 'wp_head', 'BoostMisc::update_boosted_time_values' );
			#Kill AJAX
			add_action( 'wp_ajax_kill_ajax', array($this, 'kill_ajax' ) );

			#Hooks
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			#Filters

			########TESTS########
			add_action( 'init', array( $this, 'tester' ) );
			########TESTS########
		}

		public function tester() {}

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
			}
		}

		public function scripts_and_styles() {
			if ( \BoostMisc::return_ad_edit_page() ) {
				$arr =
					array(
						'boost_buttom_form_enabled'   => \BoostMisc::boost_button_form_enabled(),
						'boost_buttom_form_disabled'  => \BoostMisc::boost_button_form_disabled(),
						'boost_arr'                   => \BoostMisc::return_array_of_boostable_ads(),
						'no_boost_arr'                => \BoostMisc::return_array_of_non_boostable_ads(),
						'display_countdown'           => \BoostMisc::display_countdown(),
					);

				wp_register_script( 'edit-an-advert-script', SD_BOOST_URL . 'assets/js/edit-an-advert.js', 'jquery', NULL, true );
				wp_localize_script( 'edit-an-advert-script', 'boost_object', $arr );
				wp_enqueue_script( 'edit-an-advert-script' );

				wp_register_style( 'edit-an-advert-style', SD_BOOST_URL . 'assets/css/edit-an-advert.css' );
				wp_enqueue_style( 'edit-an-advert-style' );

			}
		}

		#On activation we want to create a table on the AWPCP _awpcp_ads table this table will keep track of the time the ad was last boosted.
		public function activation() {
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			#add a column if it doesn't exist
			$wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS ." ADD ad_boost_time INT");
		}

		public function deactivation() {}

		public function kill_ajax() {
			die;
		}

	}
}