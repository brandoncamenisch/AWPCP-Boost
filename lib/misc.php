<?php

class BoostMisc {

	public function return_ad_edit_page() {
		if ( function_exists( 'awpcp_get_page_id_by_ref' ) ){
			$url = "http" . ( ($_SERVER['SERVER_PORT'] == 443 ) ? "s://" : "://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = preg_replace('/\?.*/', '', $url);
			$editadpageid = awpcp_get_page_id_by_ref('edit-ad-page-name');
			$url_editpage = get_permalink( $editadpageid );
			if ( $url_editpage === $url ) {
				return true;
			}
		}
	}

	public function query_ads_owned_by_user() {
		global $wpdb;
		$id = get_current_user_id();
		$userads = $wpdb->get_results(
			"SELECT *
			FROM " . AWPCP_TABLE_ADS . "
			WHERE user_id = '$id'"
		);
		return $userads;
	}

	public function return_array_of_non_boostable_ads() {
		$ads  = self::query_ads_owned_by_user();
		$time = current_time( 'timestamp' );
		$arr = array();
		#Compare boost time to current time
		foreach ( $ads as $ad ) {
			$adTime =  strtotime( $ad->ad_boost_time ) ;
			if ( $adTime >= $time ) {
				#Build and return the array accordingly
				$arr[$ad->ad_key] = date('D-d', $ad->ad_boost_time);
			}
		}
		return $arr;
	}

	public function return_array_of_boostable_ads() {
		$ads  = self::query_ads_owned_by_user();
		$time = current_time( 'timestamp' );
		$arr = array();
		#Compare boost time to current time
		foreach ( $ads as $ad ) {
			$adTime =  strtotime( $ad->ad_boost_time ) ;
			if ( $adTime <= $time ) {
				#Build and return the array accordingly
				$arr[$ad->ad_key] = $ad->ad_boost_time;
			}
		}
		return $arr;
	}

	public function current_boost_time() {
		$opt          = get_option( 'awpcp-options' );
		$boostedTime  = date('Y-m-d H:i:s', strtotime( $opt['boost_time'] ) );
		#2013-12-27 15:35:29
		return $boostedTime;
	}

	public function display_countdown() {
		$opt  = get_option( 'awpcp-options' );
		$opt  = $opt['boost_display_inactive_countdown'];
		return $opt;
	}


	public function boost_button_form_enabled() {
		$opt  = get_option( 'awpcp-options' );
		$img  = ( $opt['boost_active_boost_btn_image'] ? '<img src="' . $opt['boost_active_boost_btn_image'] . '">' : '' );
		$msg  = $opt['boost_active_boost_btn_message'];

		$out  = "<form action=\"#\" class=\"boostform\" method=\"POST\">
							" . wp_nonce_field( -1 ,'boosted_nonce' ) . "
							<fieldset>
								<input type=\"hidden\" name=\"boostedTime\" value=\"\" />
								<button type=\"submit\" class=\"boostbutton\">$img $msg</button>
							</fieldset>
						</form>";
		return $out;
	}

	public function boost_button_form_disabled() {
		$opt  = get_option( 'awpcp-options' );
		$img  = ( $opt['boost_inactive_boost_btn_image'] ? '<img src="' . $opt['boost_inactive_boost_btn_image'] . '">' : '' );
		$msg  = $opt['boost_inactive_boost_btn_message'];
		$out  = "<form action=\"#\" class=\"boostform\" method=\"POST\">
							<fieldset class=\"boostbutton\">
								<button type=\"submit\" class=\"boostbutton disabled\" disabled>$img $msg</button>
							</fieldset>
						</form>";
		return $out;
	}

	#Since the AWPCP is extremely unextendable we are updating the actual ad id so that the results are consistently boosted to the top if this causes issues which it very well may then a better solution must be sought out.
	public function update_boosted_time_values() {
		if ( isset( $_POST['boosted_nonce'] ) || wp_verify_nonce( $_POST['boosted_nonce'], -1 ) && isset( $_POST['boostedTime'] ) ) {
			global $wpdb;
			$id           = get_current_user_id();
			$boostTime    = self::current_boost_time();
			$query        = $_POST['boostedTime'];
			$perma        = get_permalink();
			#Update the table accordingly
			$wpdb->query(
				"UPDATE {$wpdb->prefix}awpcp_ads
				SET ad_boost_time='$boostTime'
				WHERE ad_key='$query'
				AND user_id='$id'"
			);
			wp_redirect( $perma );
			exit;
		}
	}

	public function update_ad_timestamp() {
		if ( isset( $_REQUEST['ad_id'] ) ) {
			global $wpdb;
			$id           = get_current_user_id();
			$boostTime    = self::current_boost_time();
			$query        = $_REQUEST['ad_id'];
			$perma        = get_permalink();
			#Update the table accordingly
			$wpdb->query(
				"UPDATE {$wpdb->prefix}awpcp_ads
				SET ad_boost_time='$boostTime'
				WHERE ad_id='$query'"
			);
		}
	}

}