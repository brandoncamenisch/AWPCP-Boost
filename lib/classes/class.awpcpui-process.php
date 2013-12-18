<?php
class BoostProcess {

	public function remove_edit_ad_shortcode( $content) {
		#Remove the default classifieds shortcode;
		remove_shortcode("AWPCPCLASSIFIEDSUI");
		$content = str_replace( "AWPCPCLASSIFIEDSUI", "BOOSTEDAWPCPCLASSIFIEDSUI", $content);
		return $content;
	}


	public function boost_shortcode() {
		global $classicontent;
		if ( ! isset( $awpcppagename ) || empty( $awpcppagename ) ) {
			$awpcppage      = get_currentpagename();
			$awpcppagename  = sanitize_title( $awpcppage, $post_ID = '' );
		}
		if ( ! isset( $classicontent ) || empty( $classicontent ) ) {
			$classicontent = self::boost_awpcpui_process( $awpcppagename );
		}
		return $classicontent;
	}

	public function boost_awpcpui_process($awpcppagename) {
		global $hasrssmodule, $hasregionsmodule, $awpcp_plugin_url;

		$output = '';
		$action = '';

		$awpcppage = get_currentpagename();
		if ( ! isset( $awpcppagename ) || empty( $awpcppagename ) ) {
			$awpcppagename = sanitize_title( $awpcppage, $post_ID = '' );
		}

		if ( isset( $_REQUEST['a'] ) && ! empty( $_REQUEST['a'] ) ) {
			$action = $_REQUEST['a'];
		}

		if ( ( $action == 'setregion' ) || '' != get_query_var( 'regionid' ) ) {
			if ( $hasregionsmodule ==  1 ) {
				if ( isset( $_REQUEST['regionid'] ) && ! empty( $_REQUEST['regionid'] ) ) {
					$region_id = $_REQUEST['regionid'];
				} else {
					$region_id = get_query_var( 'regionid' );
				}

				if ( method_exists( 'AWPCP_Region_Control_Module', 'set_location' ) ) {
					$region = awpcp_region_control_get_entry( array( 'id' => $region_id ) );
					$regions = AWPCP_Region_Control_Module::instance();
					$regions->set_location( $region );
				}
			}

		} elseif ( $action == 'unsetregion' ) {
			if ( isset($_SESSION['theactiveregionid'] ) ) {
				unset( $_SESSION['theactiveregionid'] );
			}
		}

		$categoriesviewpagename = sanitize_title( get_awpcp_option( 'view-categories-page-name' ) );
		$browsestat       = '';
		$browsestat       = get_query_var( 'cid' );
		$layout           = get_query_var( 'layout' );
		$isadmin          = checkifisadmin();
		$isclassifiedpage = checkifclassifiedpage($awpcppage);

		if ( ( $isclassifiedpage == false ) && ( $isadmin == 1 ) ) {
			$output .= __( "Hi admin, you need to go to your dashboard and setup your classifieds.", "AWPCP" );
		} elseif ( ( $isclassifiedpage == false ) && ( $isadmin != 1 ) ) {
			$output .= __( "You currently have no classifieds","AWPCP" );
		} elseif ( $browsestat == $categoriesviewpagename ) {
			$output .= awpcp_display_the_classifieds_page_body( $awpcppagename );
		} elseif ( $layout == 2 ) {
			$output .= awpcp_display_the_classifieds_page_body( $awpcppagename );
		} else {
			$output .= self::boost_awpcp_load_classifieds($awpcppagename);
		}

		return $output;
	}

	public function boost_awpcp_load_classifieds( $awpcppagename ) {
		$output = '';
		if ( get_awpcp_option( 'main_page_display' ) == 1 ) {
			$grouporderby = BoostMisc::get_group_orderby();

			$output .= load_ad_search_form();
			$output .= awpcp_display_ads( $where = '', $byl = 1, $hidepager = '', $grouporderby, $adorcat = 'ad' );
		} else {
			$output .= awpcp_display_the_classifieds_page_body( $awpcppagename );
		}
		return $output;
	}



}