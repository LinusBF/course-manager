/**
 * Created by Linus on 2017-03-11.
 */
jQuery(document).ready(function ($) {
	// Uploading files
	var file_frame;
	var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
	var set_to_post_id = passed_options.post_id; // Set this
	jQuery('.cm_coursePartList').on('click', '#upload_image_button', function (event) {
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if (file_frame) {
			/* DEPRECATED AS IT MADE EVENT.TARGET RETURN THE FIRST ELEMENT THAT WAS OPENED. - Linus
			// Set the post ID to what we want
			file_frame.uploader.uploader.param('post_id', set_to_post_id);
			// Open frame
			file_frame.open();
			return;*/
		} else {
			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: passed_options.title,
			button: {
				text: passed_options.text,
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on('select', function () {
			// We set multiple to false so only get one image from the uploader
			var attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			$(event.target).parent().find('#image-preview').attr('src', attachment.url).css('width', 'auto');
			$(event.target).parent().find('#image_attachment_id').val(attachment.id);
			// Restore the main post ID
			wp.media.model.settings.post.id = wp_media_post_id;
		});
		// Finally, open the modal
		file_frame.open();
	});
	// Restore the main ID when the add media button is pressed
	jQuery('.cm_coursePartList').on('click', 'a.add_media', function () {
		wp.media.model.settings.post.id = wp_media_post_id;
	});
});