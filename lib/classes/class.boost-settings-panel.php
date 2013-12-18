<?php

class BoostSettingsPanel {

	public function register_boost_settings() {
		global $awpcp;
		$api = $awpcp->settings;

		$key = $api->add_section( 'general-settings', __( 'Boost Settings', 'AWPCP' ), 'seatdropper-boost', 10, array( $awpcp->settings, 'section' ) );

		#$api->add_setting( $key, 'boost_display_edit_an_advert_loop', __( 'Display in the "Edit an Advert" loop?', 'AWPCP' ), 'checkbox', 1, '' );
		#$api->add_setting( $key, 'boost_display_edit_an_advert_single', __( 'Display in the "Edit an Advert" single?', 'AWPCP' ), 'checkbox', 1, '' );

		$api->add_setting( $key, 'boost_time', __( 'Boosted time', 'AWPCP' ), 'textfield', '+3 days', 'Example: <strong>+1 week 2 days 4 hours 2 seconds</strong> or <strong>+3 hours</strong>' );

		$api->add_setting( $key, 'boost_active_boost_btn_image', __( 'Enter an image URL for the active boost button', 'AWPCP' ), 'textfield', 'URL', '' );

		$api->add_setting( $key, 'boost_active_boost_btn_message', __( 'Enter a message for the active boost button', 'AWPCP' ), 'textfield', 'Message', '' );

		$api->add_setting( $key, 'boost_inactive_boost_btn_image', __( 'Enter an image URL for the inactive boost button', 'AWPCP' ), 'textfield', 'URL', '' );

		$api->add_setting( $key, 'boost_inactive_boost_btn_message', __( 'Enter a message for the inactive boost button', 'AWPCP' ), 'textfield', 'Message', '' );

		$api->add_setting( $key, 'boost_display_inactive_countdown', __( 'Display the inactive boost button countdown?', 'AWPCP' ), 'checkbox', 1, '' );

	}

}