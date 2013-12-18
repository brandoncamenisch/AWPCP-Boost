<?php
class DisplayAds {
	public function awpcp_display_ads($where, $byl, $hidepager, $grouporderby, $adorcat, $before_content='', $editing=false) {
		global $wpdb;
		global $awpcp_imagesurl, $awpcp_plugin_path;
		global $hasregionsmodule, $hasextrafieldsmodule;

		$output = '';
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage);
		$quers=setup_url_structure($awpcppagename);
		$permastruc=get_option('permalink_structure');

		$showadspagename=sanitize_title(get_awpcp_option('show-ads-page-name'));
		$browseadspagename = sanitize_title(get_awpcp_option('browse-ads-page-name'));
		$browsecatspagename=sanitize_title(get_awpcp_option('browse-categories-page-name'));

		$awpcp_browsecats_pageid=awpcp_get_page_id_by_ref('browse-categories-page-name');
		$awpcpwppostpageid=awpcp_get_page_id_by_ref('main-page-name');
		$browseadspageid=awpcp_get_page_id_by_ref('browse-ads-page-name');

		$displayadthumbwidth = get_awpcp_option('displayadthumbwidth');

		$url_browsecats='';
		__("*** NOTE:  The next two strings are for currency formatting:  1,000.00 where comma is used for currency place holders and the period for decimal separation.  Change the next two strings for your preferred price formatting.  (this string is just a note)***","AWPCP");
		$currencySep = __(",", "AWPCP");
		$decimalPlace = __(".","AWPCP");

		// filters to provide alternative method of storing custom layouts (e.g. can be outside of this plugin's directory)
		if ( has_action('awpcp_browse_ads_template_action') || has_filter('awpcp_browse_ads_template_filter') ) {
			do_action('awpcp_browse_ads_template_action');
			$output = apply_filters('awpcp_browse_ads_template_filter');
			return;

		} else if (file_exists("$awpcp_plugin_path/awpcp_display_ads_my_layout.php") &&
				   get_awpcp_option('activatemylayoutdisplayads'))
		{
			include("$awpcp_plugin_path/awpcp_display_ads_my_layout.php");

		} else {
			$output .= "<div id=\"classiwrapper\">";

			$isadmin = checkifisadmin();
			$uiwelcome=stripslashes_deep(get_awpcp_option('uiwelcome'));

			$output .= "<div class=\"uiwelcome\">$uiwelcome</div>";
			$output .= awpcp_menu_items();

			if ($hasregionsmodule ==  1) {
				if (isset($_SESSION['theactiveregionid'])) {
					$theactiveregionid=$_SESSION['theactiveregionid'];
					$theactiveregionname = addslashes(get_theawpcpregionname($theactiveregionid));
				}

				// Do not show Region Control form when showing Search Ads page
				// search result. Changing the current location will redirect the user
				// to the form instead of a filterd version of the form and that's confusing
				global $post;
				// this is a poor test to see if we are in Search Ads page
				if ($post->post_name == sanitize_title(get_awpcp_option('search-ads-page-name')) &&
					isset($_POST['a']) && $_POST['a'] == 'dosearch') {
					// do nothing
				} else {
					$output .= awpcp_region_control_selector();
				}
			}

			$output .= $before_content;

			$tbl_ads = $wpdb->prefix . "awpcp_ads";
			$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

			$from="$tbl_ads";

			if (!isset($where) || empty($where)) {
				$where="disabled =0";
			} else {
				$where="$where";
			}

			// this overrides Search Ads region form fields
			if ($hasregionsmodule == 1 && isset($theactiveregionname) && !empty($theactiveregionname)) {
		        if (function_exists('awpcp_regions_api')) {
		        	$regions = awpcp_regions_api();
		        	$where.= ' AND ' . $regions->sql_where($theactiveregionid);
		        } else {
					$where.=" AND (ad_city ='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname')";
				}
			}

			// disablependingads is shown to the user with the label:
			// "Enable pending Ads that are pending payment"
			// if the value is 1 we should allow pending payment Ads
			// if value is 0 (unchecked) we shouldn't allow pending payment Ads
			// TODO: change the name of this setting to something that makes sense
			if (get_awpcp_option('disablependingads') == 0 &&  get_awpcp_option('freepay') == 1) {
				$where .= " AND (payment_status != 'Pending' AND payment_status != 'Unpaid') ";
			}/* else {
				// never allow Unpaid Ads
				$where .= " AND payment_status != 'Unpaid' ";
			}*/

			$ads_exist = ads_exist();
			if (!$ads_exist) {
				$showcategories="<p style=\"padding:10px\">";
				$showcategories.=__("There are currently no ads in the system","AWPCP");
				$showcategories.="</p>";
				$pager1='';
				$pager2='';

			} else {
				$awpcp_image_display_list=array();

				if ($adorcat == 'cat') {
					$tpname = get_permalink($awpcp_browsecats_pageid);
				} else {
					$tpname = get_permalink($browseadspageid);
				}

				$awpcpmyresults=get_awpcp_option('adresultsperpage');
				if (!isset($awpcpmyresults) || empty($awpcpmyresults)) {
					$awpcpmyresults=10;
				}

				$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
				$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=$awpcpmyresults);

				if (!isset($hidepager) || empty($hidepager) ) {
					//Unset the page and action here...these do the wrong thing on display ad
					unset($_GET['page_id']);
					unset($_POST['page_id']);
					//unset($params['page_id']);
					$pager1=create_pager($from,$where,$offset,$results,$tpname);
					$pager2=create_pager($from,$where,$offset,$results,$tpname);
				} else {
					$pager1='';
					$pager2='';
				}

				if (isset($grouporderby) && !empty($grouporderby)) {
				    if (function_exists('awpcp_featured_ads')) {
						$grouporderby = str_replace('ORDER BY','', strtoupper($grouporderby));
						$grouporder = 'ORDER BY is_featured_ad DESC, '.$grouporderby;
				    } else {
						$grouporder = $grouporderby;
				    }

				} else {
				    if (function_exists('awpcp_featured_ads')) {
						$grouporder = "ORDER BY is_featured_ad DESC, ad_postdate DESC, ad_title ASC";
				    } else {
						$grouporder="ORDER BY ad_postdate DESC, ad_title ASC";
				    }
				}

				$query = "SELECT ad_id, ad_category_id, ad_title, ad_contact_name, ";
				$query.= "ad_contact_phone, ad_city, ad_state, ad_country, ";
				$query.= "ad_details, ad_postdate, ad_enddate, ad_views, ad_fee_paid, ";
				$query.= "IF(ad_fee_paid > 0, 1, 0) as ad_is_paid, ad_item_price, flagged, ";
				$query.= "ad_key, ad_contact_email ";
				$query.= "FROM $from WHERE $where $grouporder LIMIT $offset,$results";

				$items=array();
				$res = awpcp_query($query, __LINE__);

				while ($rsrow=mysql_fetch_row($res)) {
					// Change:  Allow flagged ads to show
					// if ($rsrow[15]) continue;

					if (is_array($rsrow)) {
						for ($i=0; $i < count($rsrow); $i++) {
							$rsrow[$i] = stripslashes($rsrow[$i]);
						}
					}

					$ad_id=$rsrow[0];
					$awpcppage=get_currentpagename();
					$awpcppagename = sanitize_title($awpcppage, $post_ID='');

					$modtitle = awpcp_esc_attr($rsrow[2]);
					$modcontactname = stripslashes($rsrow[3]);
					$tcname = get_adcatname($rsrow[1]);
					// $modcatname=cleanstring($tcname);
					// $modcatname=add_dashes($modcatname);
					$category_id=$rsrow[1];
					$category_name=get_adcatname($category_id);
					$addetailssummary = stripslashes_deep(wp_trim_words($rsrow[8], 20, ''));
					$awpcpadcity=get_adcityvalue($ad_id);
					$awpcpadstate=get_adstatevalue($ad_id);
					$awpcpadcountry=get_adcountryvalue($ad_id);
					$awpcpadcountyvillage=get_adcountyvillagevalue($ad_id);

					if ($editing)
						$url_actionad=sprintf('?a=doadedit1&adaccesskey=%s&editemail=%s', $rsrow[16], $rsrow[17]);
					else
						$url_actionad=url_showad($ad_id);
					if (isset($permastruc) && !empty($permastruc)) {
						// $url_browsecats = "$quers/$browsecatspagename/$category_id/";
						$base_url = trim(get_permalink($awpcp_browsecats_pageid), '/');
						$url_browsecats = sprintf("%s/%s/", $base_url, $category_id);
					} else {
						// $url_browsecats = "$quers/?page_id=$awpcp_browsecats_pageid&amp;a=browsecat&amp;category_id=$category_id";
						$base_url = trim(get_permalink($awpcp_browsecats_pageid), '/');
						$params = array('a' => 'browsecat', 'category_id' => $category_id);
						$url_browsecats = add_query_arg($params, $base_url);
					}

					$ad_title="<a href=\"$url_actionad\">".$rsrow[2]."</a>";
					$categorylink="<a href=\"$url_browsecats\">$category_name</a><br/>";

					$awpcpcity=$rsrow[5];
					$awpcpstate=$rsrow[6];
					$awpcpcountry=$rsrow[7];

					if (isset($awpcpcity) && !empty($awpcpcity)) {
						$awpcp_city_display = "$awpcpcity<br/>";
					} else {
						$awpcp_city_display = "";
					}

					if (isset($awpcpstate) && !empty($awpcpstate)) {
						$awpcp_state_display = "$awpcpstate<br/>";
					} else {
						$awpcp_state_display = "";
					}

					if ( isset($awpcpcountry) && !empty($awpcpcountry) ) {
						$awpcp_country_display="$awpcpcountry<br/>";
					} else {
						$awpcp_country_display='';
					}

					$awpcp_image_display="<a href=\"$url_actionad\">";
					if (get_awpcp_option('imagesallowdisallow')) {
						$totalimagesuploaded=get_total_imagesuploaded($ad_id);
						if ($totalimagesuploaded >=1) {
							$image = awpcp_get_ad_primary_image($ad_id);
							if (!is_null($image)) {
								$awpcp_image_name_srccode="<img src=\"". awpcp_get_image_url($image, 'thumbnail') . "\" border=\"0\" style=\"float:left;margin-right:25px;\" width=\"$displayadthumbwidth\" alt=\"$modtitle\"/>";
							} else {
								$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" style=\"float:left;margin-right:25px;\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
							}
						} else {
							$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
						}
					} else {
						$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
					}

					$awpcp_image_display.="$awpcp_image_name_srccode</a>";

					if ( get_awpcp_option('displayadviews') ) {
						$awpcp_display_adviews=__("<p class='details'>Total views","AWPCP");
						$awpcp_display_adviews.=": $rsrow[11]</p>";
					} else {
						$awpcp_display_adviews='';
					}

					if (get_awpcp_option('displaypricefield')) {
						if (isset($rsrow[14]) && !empty($rsrow[14])) {
							$awpcptheprice=$rsrow[14];
							$itempricereconverted=($awpcptheprice/100);
							$itempricereconverted=number_format($itempricereconverted, 2, $decimalPlace, $currencySep);
							if ($itempricereconverted >=1 ) {
								$awpcpthecurrencysymbol=awpcp_get_currency_code();
								$awpcp_display_price=__("<p class='details'>Price","AWPCP");
								$awpcp_display_price.=": $awpcpthecurrencysymbol $itempricereconverted</p>";
							} else {
								$awpcp_display_price='';
							}
						} else {
							$awpcp_display_price='';
						}
					} else {
						$awpcp_display_price='';
					}

					$awpcpextrafields='';
					if ($hasextrafieldsmodule == 1) {
						$awpcpextrafields=display_x_fields_data($ad_id, false);
					}

					$awpcpdateformat=__("m/d/Y","AWPCP");
					$awpcpadpostdate=date($awpcpdateformat, strtotime($rsrow[9]))."<br/>";

					$imgblockwidth="$displayadthumbwidth";
					$imgblockwidth.="px";

					$ad_title=stripslashes_deep($ad_title);
					$addetailssummary=stripslashes_deep($addetailssummary);
					$awpcpdisplaylayoutcode=get_awpcp_option('displayadlayoutcode');

					if ( isset($awpcpdisplaylayoutcode) && !empty($awpcpdisplaylayoutcode)) {
						//$awpcpdisplaylayoutcode=str_replace("\$awpcpdisplayaditems","${awpcpdisplayaditems}",$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$imgblockwidth",$imgblockwidth,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcp_image_name_srccode",$awpcp_image_display,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$addetailssummary",$addetailssummary,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$ad_title",$ad_title,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcpadpostdate",$awpcpadpostdate,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcp_state_display",$awpcp_state_display,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcp_display_adviews",$awpcp_display_adviews,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcp_city_display",$awpcp_city_display,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcp_display_price",$awpcp_display_price,$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$awpcpextrafields","$awpcpextrafields",$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$ad_categoryname","$tcname",$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$ad_contactname","$modcontactname",$awpcpdisplaylayoutcode);
						$awpcpdisplaylayoutcode=str_replace("\$url_showad","$url_actionad",$awpcpdisplaylayoutcode);

						if (function_exists('awpcp_featured_ads')) {
						    $awpcpdisplaylayoutcode = awpcp_featured_ad_class($ad_id, $awpcpdisplaylayoutcode);
						}

						$items[]="$awpcpdisplaylayoutcode";

					} else {
						$items[]="
								<div class=\"\$awpcpdisplayaditems awpcp_featured_ad_wrapper\">
								<div style=\"width:$imgblockwidth;padding:5px;float:left;margin-right:20px;\">$awpcp_image_name_srccode</div>
								<div style=\"width:50%;padding:5px;float:left;\"><h4>$ad_title </h4> " . $addetailssummary . "...</div>
								<div style=\"padding:5px;float:left;\"> $awpcpadpostdate $awpcp_city_display $awpcp_state_display $awpcp_display_adviews $awpcp_display_price $awpcpextrafields</div>
								<span class=\"fixfloat\">$tweetbtn $sharebtn $flagad</span>
								</div>
								<div class=\"fixfloat\"></div>
								";
					}

					$opentable="";
					$closetable="";

					$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
					$showcategories="$theitems";
				}

				if (!isset($ad_id) || empty($ad_id) || $ad_id == 0) {
					$showcategories="<p style=\"padding:20px;\">";
					$showcategories.=__("There were no ads found","AWPCP");
					$showcategories.="</p>";
					$pager1='';
					$pager2='';
				}
			}

			if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id'])) {
				$show_category_id=$_REQUEST['category_id'];
			} else {
				$show_category_id='';
			}

			if (!isset($url_browsecatselect) || empty($url_browsecatselect)) {
				$url_browsecatselect = get_permalink($awpcp_browsecats_pageid);
			}

			if ($ads_exist) {
				$output .= "<div class=\"fixfloat\"></div><div class=\"pager\">$pager1</div>";
				if (!$editing) {
					$output .= "<div class=\"changecategoryselect\"><form method=\"post\" action=\"$url_browsecatselect\"><select style='float:left' name=\"category_id\"><option value=\"-1\">";
					$output .= __("Select Category","AWPCP");
					$output .= "</option>";
					$allcategories=get_categorynameidall($show_category_id='');
					$output .= "$allcategories";
					$output .= "</select><input type=\"hidden\" name=\"a\" value=\"browsecat\" />&nbsp;<input class=\"button\" type=\"submit\" value=\"";
					$output .= __("Change Category","AWPCP");
					$output .= "\" /></form></div>";
				}
				$output .= "<div id='awpcpcatname' class=\"fixfloat\">";

				$category_id = (int) awpcp_request_param('category_id', -1);
				$category_id = $category_id === -1 ? (int) get_query_var('cid') : $category_id;
				if ($category_id > 0) {
					$output .= "<h3>" . __("Category: ", "AWPCP") . get_adcatname($category_id) . "</h3>";
				}

				$output .= "</div>";
			}

			$output .= apply_filters('awpcp-display-ads-before-list', '');
			$output .= "$showcategories";

			if ($ads_exist) {
				$output .= "&nbsp;<div class=\"pager\">$pager2</div>";
			}

			if ($byl) {
				if (field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign'))) {
					$output .= "<p><font style=\"font-size:smaller\">";
					$output .= __("Powered by ","AWPCP");
					$output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";

				} elseif (field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign'))) {
					// ...

				} else {
					// $output .= "<p><font style=\"font-size:smaller\">";
					// $output .= __("Powered by ","AWPCP");
					// $output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
				}
			}

			$output .= "</div>";

		}
		return $output;
	}
}