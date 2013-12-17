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
		#var_dump($userads);
		return $userads;
		#@TODO: send to json
	}

}