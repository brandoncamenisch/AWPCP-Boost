// Generated by CoffeeScript 1.6.3
(function(){jQuery(document).ready(function(e){"use strict";var t,n;t=function(){return e("[class^=displayaditem]").each(function(){var t,n,r,i,s;t=e(this).find("> div > a").attr("href");s=[];n=void 0;i=t.split("?")[1];if(i!==undefined){i=i.split("&");r=0;while(r<i.length){n=i[r].split("=");s.push(n[1]);s[n[0]]=n[1];r++}}if(s.adaccesskey in boost_object.boost_arr){e(this).append(boost_object.boost_buttom_form_enabled);return e(this).find("form fieldset input[name='boostedTime']").val(s.adaccesskey)}e(this).append(boost_object.boost_buttom_form_disabled);if(boost_object.display_countdown==="1")return e(this).find("form.boostform fieldset button").append(" "+boost_object.no_boost_arr[s.adaccesskey])})};n=function(){var t;t=e("#adpostform").find('input[name="adkey"]').attr("value");if(t in boost_object.boost_arr){e(boost_object.boost_buttom_form_enabled).insertBefore("#adpostform");return e("form.boostform").attr("id","boostform").find("fieldset input[name='boostedTime']").val(t)}e(boost_object.boost_buttom_form_disabled).insertBefore("#adpostform");return e("form.boostform").attr("id","boostform")};e("#adpostform").length&&n();if(e("[class^=displayaditem]").length)return t()})}).call(this);