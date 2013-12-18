<?php
class BoostMisc {

	public function return_ad_edit_page() {
		$url = "http" . ( ($_SERVER['SERVER_PORT'] == 443 ) ? "s://" : "://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$editadpageid = awpcp_get_page_id_by_ref('edit-ad-page-name');
		$url_editpage = get_permalink($editadpageid);
		if ( $url_editpage === $url ){
			return true;
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

	public function return_array_of_boostable_ads() {
		$ads  = self::query_ads_owned_by_user();
		$time = current_time( 'timestamp' );
		$arr = array();
		#Compare boost time to current time
		foreach ( $ads as $ad ) {
			if ( $ad->ad_boost_time >= $time ) {
				#Build and return the array accordingly
				$arr[$ad->ad_key] = $ad->ad_boost_time;
			}
		}
		return $arr;
	}

	public function current_boost_time() {
		$opt          = get_option( 'awpcp-options' );
		$boostedTime  = strtotime($opt['boosted_time']);
		return $boostedTime;
	}

	public function boost_button_form_enabled() {
		#? disabled
		return '<div class="boostbutton"></div>';
	}

	public function boost_button_form_disabled() {
		#? disabled
		return '<div class="boostbutton disabled"></div>';
	}

	#Since the AWPCP is extremely unextendable we are updating the actual ad id so that the results are consistently boosted to the top if this causes issues which it very well may then a better solution must be sought out.
	public function update_boosted_time_values() {
		global $wpdb;
		$id           = get_current_user_id();
		$boostTime    = self::current_boost_time();
		$query        = '5e58f66b9b7a92e5f8ccdb89ee9d5805';
		#Check the nonce
		#Get the Value of the adaccesskey
		#Update the table accordingly
		$wpdb->query(
			"UPDATE {$wpdb->prefix}awpcp_ads
			SET ad_boost_time='$boostTime', ad_id='$boostTime'
			WHERE ad_key='$query'
			AND user_id='$id'"
		);
	}

}