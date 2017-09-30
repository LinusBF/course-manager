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
});