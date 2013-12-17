<?php

class BoostEditAd {

	public function remove_edit_ad_shortcode( $content) {
		#Remove the default classifieds shortcode;
		remove_shortcode("AWPCPEDITAD");
		$content = str_replace( "AWPCPEDITAD", "BOOSTEDAWPCPEDITAD", $content);
		return $content;
	}


	public function awpcpui_editformscreen() {
		global $editpostform_content;
		if ( ! isset( $editpostform_content ) || empty( $editpostform_content ) ) {
			$editpostform_content = awpcpui_process_editad();
		}
		return $adpostform_content;
	}

	public function awpcpui_process_editad() {
		global $hasextrafieldsmodule;

		wp_enqueue_script('awpcp-page-place-ad');

		$action='';
		$output = '';

		// if Ad Management panel is enabled use  that to edit Ads
		if (get_awpcp_option('enable-user-panel') == 1) {
			$panel_url = admin_url('admin.php?page=awpcp-panel');
			$output = __('Please go to the Ad Management panel to edit your Ads.', 'AWPCP');
			$output.= ' <a href="' . $panel_url . '">' . __('Click here', 'AWPCP') . '</a>.';
			return $output;
		}


		if (!isset($awpcppagename) || empty($awpcppagename)) {
			$awpcppage = get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
		}

		if (isset($_REQUEST['a']) && !empty($_REQUEST['a'])) {
			$action=$_REQUEST['a'];
		}

		if ($action == 'editad') {
			$output .= load_ad_edit_form($action,$awpcppagename,$usereditemail,$adaccesskey,$message);
		}
		elseif ($action == 'doadedit1')
		{
			$adaccesskey=clean_field($_REQUEST['adaccesskey']);
			$editemail=clean_field($_REQUEST['editemail']);
			//$awpcppagename=clean_field($_REQUEST['awpcppagename']);
			$output .= editadstep1($adaccesskey,$editemail,$awpcppagename);
		}
		elseif ($action == 'resendaccesskey')
		{
			$editemail = awpcp_request_param('editemail');
			$awpcppagename = awpcp_request_param('awpcppagename');
			$output .= resendadaccesskeyform($editemail,$awpcppagename);

		} elseif (in_array($action, array('dp', 'enable-picture', 'disable-picture'))) {
			if (isset($_REQUEST['k']) && !empty($_REQUEST['k'])) {
				$keyids=$_REQUEST['k'];
				$keyidelements = explode("_", $keyids);
				$picid=$keyidelements[0];
				$adid=$keyidelements[1];
				$adtermid=$keyidelements[2];
				$adkey=$keyidelements[3];
				$editemail=$keyidelements[4];
			}

			$is_admin = awpcp_current_user_is_admin();
			$admin_must_approve = get_awpcp_option('imagesapprove');

			if ($action == 'dp') {
				$output .= deletepic($picid, $adid, $adtermid, $adkey, $editemail);
			} else if (in_array($action, array('enable-picture', 'disable-picture'))) {
				$image = AWPCP_Image::find_by_id($picid);
				if (is_object($image) && ($is_admin || !$admin_must_approve)) {
					if ($action == 'enable-picture') {
						$image->disabled = false;
						$image->save();
					} else if ($action == 'disable-picture') {
						$image->disabled = true;
						$image->save();
					}
				}
				$output .= editimages($adtermid, $adid, $adkey, get_adposteremail($adid));
			}

		} elseif ($action == 'dopost1') {
			$errors = array();
			$output .= awpcp_place_ad_save_details_step(array(), $errors, true);

		} elseif ($action == 'awpcpuploadfiles') {
			$adid='';$adtermid='';$adkey='';$adpaymethod='';$nextstep='';$adaction='';

			if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);}
			if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adtermid=clean_field($_REQUEST['adtermid']);}
			if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$adkey=clean_field($_REQUEST['adkey']);}
			if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);}
			if (isset($_REQUEST['nextstep']) && !empty($_REQUEST['nextstep'])){$nextstep=clean_field($_REQUEST['nextstep']);}
			if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){$adaction=clean_field($_REQUEST['adaction']);}
			// $output .= handleimagesupload($adid,$adtermid,$nextstep,$adpaymethod,$adaction,$adkey);

			$form_errors = array();
			$success = awpcp_handle_uploaded_images($adid, $form_errors);

			if (!empty($form_errors)) {
				 $output .= display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuerror);
			} else {
				$output = awpcp_place_ad_finish($adid, true);
			}
		}
		elseif ($action == 'adpostfinish')
		{
			if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction']))
			{
				$adaction=$_REQUEST['adaction'];
			}
			if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
			{
				$theadid=$_REQUEST['adid'];
			}
			if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
			{
				$theadkey=$_REQUEST['adkey'];
			}

			if ($adaction == 'editad')
			{
				$output .= showad($theadid,$omitmenu='');
			}
			else
			{

				$awpcpshowadsample=1;
				$awpcpsubmissionresultmessage ='';
				$message='';

				$awpcpsubmissionresultmessage =ad_success_email($theadid,$txn_id='',$theadkey,$message,$gateway='', false);

				$output .= "<div id=\"classiwrapper\">";
				$output .= '<p class="ad_status_msg">';
				$output .= $awpcpsubmissionresultmessage;
				$output .= "</p>";
				$output .= awpcp_menu_items();
				if ($awpcpshowadsample == 1)
				{
					$output .= "<h2>";
					$output .= __("Your Ad is posted","AWPCP");
					$output .= "</h2>";
					$output .= showad($theadid,$omitmenu=1);
				}
				$output .= "</div>";
			}
		}
		elseif ($action == 'deletead')
		{
			if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
			{
				$adid=$_REQUEST['adid'];
			}
			if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
			{
				$adkey=$_REQUEST['adkey'];
			}
			if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail']))
			{
				$editemail=$_REQUEST['editemail'];
			}

			$output .= deletead($adid,$adkey,$editemail);
		}
		else
		{
			$output .= load_ad_edit_form($action='editad',$awpcppagename,$editemail='',$adaccesskey='',$message='');
		}
		return $output;
	}

}
