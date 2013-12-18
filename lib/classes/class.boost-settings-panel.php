<?php

class BoostSettingsPanel {

	public function register_boost_settings() {
		global $awpcp;
		$api = $awpcp->settings;

		$key = $api->add_section( 'general-settings', __( 'Boost Settings', 'AWPCP' ), 'seatdropper-boost', 10, array( $awpcp->settings, 'section' ) );

		$api->add_setting( $key, 'display_edit_an_advert_loop', __( 'Display in the "Edit an Advert" loop', 'AWPCP' ), 'checkbox', 1, '' );
		$api->add_setting( $key, 'display_edit_an_advert_single', __( 'Display in the "Edit an Advert" single', 'AWPCP' ), 'checkbox', 1, '' );
		$api->add_setting( $key, 'boosted_time', __( 'Boosted time', 'AWPCP' ), 'textfield', 1, 'Example: <strong>+1 week 2 days 4 hours 2 seconds</strong> or <strong>+3 hours</strong>' );

		$api->add_setting($key, 'tester', 'Yo mamma', 'radio', 1, '', array('options' => $radio_options));

	}

}