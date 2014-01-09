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
	#define constants
	define( 'SD_BOOST_PATH', plugin_dir_path( __FILE__ ) );
	define( 'SD_BOOST_URL', plugin_dir_url( __FILE__ ) );
	define( 'SD_BOOST_NAME', plugin_basename(  __FILE__ ) );

	new Boost;

	class Boost {

		function __construct() {
			if ( ! defined( 'ABSPATH' ) && is_plugin_active( 'another-wordpress-classifieds-plugin/awpcp.php' ) && class_exists( 'AWPCP' ) ) {
				exit;
			} else {
				#Actions
				add_action( 'init', array( $this, 'initializer' ) );
				add_action( 'init', array( $this, 'patchwork' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
				add_action( 'activated_plugin', array( $this, 'boost_plugin_first' ) );

				add_action( 'awpcp_register_settings', 'BoostSettingsPanel::register_boost_settings' );
				add_action( 'awpcp_register_settings', 'BoostSettingsPanel::register_order_by_settings' );
				add_action( 'wp_head', 'BoostMisc::update_boosted_time_values' );

				#Hooks
				register_activation_hook( __FILE__, array( $this, 'activation' ) );
				register_deactivation_hook( __FILE__, array( $this, 'deactivation' )  );
				#Filters

				########TESTS########
				add_action( 'wp_head', 'BoostMisc::update_ad_timestamp' );
				########TESTS########
			}
		}


		public function boost_plugin_first() {
			$path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
			if ( $plugins = get_option( 'active_plugins' ) ) {
				if ( $key = array_search( $path, $plugins ) ) {
					array_splice( $plugins, $key, 1 );
					array_unshift( $plugins, $path );
					update_option( 'active_plugins', $plugins );
				}
			}
		}


		public function initializer() {
			#Requires
			require_once SD_BOOST_PATH . 'lib/classes/class.boost-settings-panel.php';
			require_once SD_BOOST_PATH . 'lib/misc.php';
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
			$wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS ." ADD ad_boost_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
			$this->override_group_order_by( WP_PLUGIN_DIR . '/another-wordpress-classifieds-plugin/functions_awpcp.php', true );
		}


		public function deactivation() {
			$this->override_group_order_by( WP_PLUGIN_DIR . '/another-wordpress-classifieds-plugin/functions_awpcp.php', false );
		}

		#I really hate to do this!!!
		private function override_group_order_by( $file, $activation ) {
			#if files exsists then copy it's contents
			if ( file_exists( $file ) ) {
				if ( $activation ) {
					$contents = file_get_contents( $file );
					$contents = str_replace( 'ad_id DESC', 'ad_boost_time DESC, ad_id DESC', $contents );
					copy ( $file , "$file.temp" );
					file_put_contents( $file, $contents );
				} else {
					$contents = file_get_contents( "$file.temp" );
					file_put_contents( $file, $contents );
				}
			}
		}


	}
