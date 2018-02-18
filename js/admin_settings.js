/**
 * Created by Linus on 2018-02-17.
 */

jQuery(document).ready(function($) {

	$('.cm_mc_list').each(function (index) {
		$(this).children('.next-page').each(function (index) {
			console.log($(this));
		})
	});


	$("#mailchimp_key").on("change", function (event) {

		$("#cm_mc_groups").slideUp("default", function () {
			$(this).remove();

			$("#cm_mc_lists").slideUp("default", function () {
				$(this).remove();
			});
		});
	});

	$(".cm_mc_list_rb").on("change", function (event) {

		$("#cm_mc_groups").slideUp("default", function () {
			$(this).remove();
		});
	});
});
