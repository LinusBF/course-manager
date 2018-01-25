/**
 * Created by Linus on 2017-11-19.
 */

//Ajax call for answering questions
jQuery(document).ready(function($) {

	function get_part_id(course_id, course_part_index, part_index, callback) {
		const ancestry = course_id + ";" + course_part_index + ";" + part_index + ";";
		const nonce = $("#cm_ancestry_nonce").val();

		let data = {
			'action': 'cm_get_part_id',
			'cm_part_index_ancestry': ancestry,
			'cm_part_ancestry_nonce': nonce
		};

		jQuery.post(course_qa.ajaxurl, data, function (response) {
			if(response !== "Failure") {
				callback(response);
			}
		});
	}

	function load_answers() {
		const courseId = $('.cm_page_wrap').attr('id').split("_")[2]; // Assuming form cm_course_X_Y
		const indexes = $(e.target).parent().siblings('.cm_page_quest').attr('id').split("_");  // Assuming form cm_CP_Y_P_Z
		const nonce = $("#cm_answers_nonce").val();


		get_part_id(courseId, indexes[2], indexes[4], function(part_id){
			let data = {
				'action': 'cm_get_answers',
				'cm_part_id': part_id,
				'cm_answers_nonce': nonce
			};

			jQuery.post(course_qa.ajaxurl, data, function (response) {
				if(response !== "Failure") {
					//TODO - Update DOM

					jQuery(document.body).trigger('post-load');
				}
			});
		});

	}

	$(".cm_answer_questions").on('click', function(e) {
		e.preventDefault();
		const courseId = $('.cm_page_wrap').attr('id').split("_")[2]; // Assuming form cm_course_X_Y
		const indexes = $(e.target).parent().siblings('.cm_page_quest').attr('id').split("_");  // Assuming form cm_CP_Y_P_Z

		get_part_id(courseId, indexes[2], indexes[4], function(part_id){

			const nonce = $("#cm_question_nonce").val();

			const answers = [];
			//TODO - Add answers

			let data = {
				'action': 'cm_answer_question',
				'cm_part_id': part_id,
				'cm_answers': answers,
				'cm_answers_nonce': nonce
			};

			jQuery.post(course_qa.ajaxurl, data, function (response) {
				if(response !== "Failure") {
					load_answers();
				} else{
					//TODO - If answers can't be set, let the user know.
				}
			});
		});
	});
});
