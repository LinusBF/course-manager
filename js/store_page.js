jQuery(document).ready(function ($) {
	$('.cm_flip_link').on('click', function (event) {
		event.preventDefault();

		var course_hash = this.getAttribute('href').substr(1);
		var element = $('#' + course_hash);
		element.addClass('target');
	});

	$('.close_course').on('click', function (event) {
		event.preventDefault();

		$(this).parents(".target").removeClass("target");
	});

	$('#token_btn').on('click', function (event) {
		var value = $("#cm_token_input").val();
		if(value != "") {
			var date = new Date();
			date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
			var expires = "; expires=" + date.toUTCString();

			document.cookie = "cm_token" + "=" + (value || "") + expires + "; path=/";
		}
	});
});