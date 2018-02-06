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
			response = JSON.parse(response);
			if(response['status'] === "Success") {
				callback(response["data"]);
			} else{
				console.log("Failed at getting part ID");
			}
		});
	}

	function load_answers(indexes) {
		const courseId = $('.cm_page_wrap').attr('id').split("_")[2]; // Assuming form cm_course_X_Y
		const nonce = $("#cm_answers_nonce").val();


		get_part_id(courseId, indexes['c_part'], indexes['part'], function(part_id){
			let data = {
				'action': 'cm_get_answers',
				'cm_part_id': part_id,
				'cm_answers_nonce': nonce
			};

			jQuery.post(course_qa.ajaxurl, data, function (response) {
				response = JSON.parse(response);
				if(response['status'] === "Success") {
					response['data']['A'].forEach(function (elem, index) {
						let qName = "cm_CP_" + indexes['c_part'] + "_P_" + indexes['part'] + "_q_" + index;
						let qObject = $('input[name=' + qName + ']');

						qObject.val(elem);
					});

					jQuery(document.body).trigger('post-load');
				} else{
					console.log("Failed at getting answers");
				}
			});
		});

	}

	$(".cm_answer_questions").on('click', function(e) {
		e.preventDefault();
		const courseId = $('.cm_page_wrap').attr('id').split("_")[2]; // Assuming form cm_course_X_Y
		const indexSrc = $(e.target).parent().siblings('.cm_page_quest').attr('id').split("_"); // Assuming form cm_CP_Y_P_Z
		const indexes = {
			"c_part": indexSrc[2],
			"part": indexSrc[4]
		};

		get_part_id(courseId, indexes['c_part'], indexes['part'], function(part_id){

			const nonce = $("#cm_question_nonce").val();

			const answers = [];
			$(e.target).parent().siblings('.cm_page_quest').children('.cm_page_quest_item').each(function (index) {
				answers.push($(this).children('.cm_page_quest_input').val());
			});


			let data = {
				'action': 'cm_answer_question',
				'cm_part_id': part_id,
				'cm_answers': answers,
				'cm_question_nonce': nonce
			};

			jQuery.post(course_qa.ajaxurl, data, function (response) {
				response = JSON.parse(response);
				if(response['status'] === "Success") {
					load_answers(indexes);
				} else{
					//TODO - If answers can't be set, let the user know.
					console.log("Failed at setting answers");
				}
			});
		});
	});

	$('.cm_page_quest').each(function (index) {
		const indexSrc = $(this).attr('id').split("_"); // Assuming form cm_CP_Y_P_Z
		const indexes = {
			"c_part": indexSrc[2],
			"part": indexSrc[4]
		};

		load_answers(indexes);
	});
});
