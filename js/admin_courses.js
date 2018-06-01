/**
 * Created by Linus on 2017-09-30.
 */
jQuery(document).ready(function ($) {
	jQuery('.cm_delete_course').on('click', function (event) {
		event.preventDefault();

		var confirm_dialog_text = passed_options.confirm_dialog;

		if(confirm(confirm_dialog_text)){
			window.location = $(this).attr('href');
		}
	});
});