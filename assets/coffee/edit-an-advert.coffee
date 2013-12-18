jQuery(document).ready ($) ->
	"use strict"

	console.log boost_object.boost_arr

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
		else
			$(this).append boost_object.boost_buttom_form_disabled