<?php

class GetGroupOrderBy {

	public function get_group_orderby() {
		$getgrouporderby = get_awpcp_option( 'groupbrowseadsby' );

		if ( ! isset( $getgrouporderby ) || empty( $getgrouporderby ) ) {
			$grouporderby = '';
		} else {
			if( isset( $getgrouporderby ) && ! empty( $getgrouporderby ) ) {
				if( $getgrouporderby == 1 ) {
					$grouporderby = "ORDER BY ad_boost_time DESC";
				} elseif( $getgrouporderby == 2 ) {
					$grouporderby = "ORDER BY ad_title ASC, ad_boost_time DESC";
				} elseif( $getgrouporderby == 3 ) {
					$grouporderby = "ORDER BY ad_is_paid DESC, ad_startdate DESC, ad_title ASC, ad_boost_time DESC";
				} elseif( $getgrouporderby == 4 ) {
					$grouporderby = "ORDER BY ad_is_paid DESC, ad_title ASC, ad_boost_time DESC";
				} elseif( $getgrouporderby == 5 ) {
					$grouporderby = "ORDER BY ad_views DESC, ad_title ASC, ad_boost_time DESC";
				} elseif( $getgrouporderby == 6 ) {
					$grouporderby = "ORDER BY ad_views DESC, ad_boost_time DESC";
				}
			}
		}
		return $grouporderby;
	}

}