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
			########TESTS########
			########TESTS########
			#Actions
			add_action( 'init', array( $this, 'initializer' ) );
			add_action( 'awpcp_register_settings','BoostSettingsPanel::register_settings' );

			#Hooks
			#Filters
		}

		public function initializer() {
			if ( ! defined( 'ABSPATH' ) ) {
				exit;
			} else {
				#define constants
				define( 'SD_BOOST_PATH', plugin_dir_path( __FILE__ ) );
				define( 'SD_BOOST_URL', plugin_dir_url( __FILE__ ) );
				define( 'SD_BOOST_NAME', plugin_basename(  __FILE__ ) );
				#Requires
				require_once SD_BOOST_PATH . 'lib/classes/class.boost-settings-panel.php';
			}
		}

	}

}