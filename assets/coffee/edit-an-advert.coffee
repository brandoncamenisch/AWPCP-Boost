jQuery(document).ready ($) ->
	"use strict"

	editLoop = ->
		$("[class^=displayaditem]").each ->
			adQueryString = $(this).find("> div > a").attr('href')
			#Here we will split the query string parameters such as: href="?a=doadedit1&adaccesskey=4ec986e6a8d7fbd67ee5f8d7b96a0158&editemail=kinghenry@gmail.com"
			vars = []
			hash = undefined
			q = adQueryString.split("?")[1]
			unless q is `undefined`
				q = q.split("&")
				i = 0
				while i < q.length
					hash = q[i].split("=")
					vars.push hash[1]
					vars[hash[0]] = hash[1]
					i++
			#If adaccesskey is in the object then append the form accordingly
			if vars['adaccesskey'] of boost_object.boost_arr
				$(this).append boost_object.boost_buttom_form_enabled
				$(this).find("form fieldset input[name='boostedTime']").val(vars['adaccesskey'])
			else
				$(this).append boost_object.boost_buttom_form_disabled
				if boost_object.display_countdown is "1"
					$(this).find("form.boostform fieldset button").append(" " + boost_object.no_boost_arr[vars['adaccesskey']])


	editSingle = ->
		adKey = $("#adpostform").find("input[name=\"adkey\"]").attr('value')
		if adKey of boost_object.boost_arr
			$(boost_object.boost_buttom_form_enabled).insertBefore("#adpostform")
			$("form.boostform").attr("id", "boostform").find("fieldset input[name='boostedTime']").val(adKey)
		else
			$(boost_object.boost_buttom_form_disabled).insertBefore("#adpostform")
			$("form.boostform").attr("id", "boostform")


	editSingle()  if $("#adpostform").length
	editLoop()  if $("[class^=displayaditem]").length
