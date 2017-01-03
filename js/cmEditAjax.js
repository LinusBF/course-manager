jQuery(document).ready(function($) {

	function getCoursePartPrefix(cParent) {
		var coursePartIndex = cParent.children("input[name='cm_CP_index']").attr('value');

		return "cm_CP_" + coursePartIndex;
	}

	function getPartPrefix(cParent, pParent) {
		var coursePartIndex = cParent.children("input[name='cm_CP_index']").attr('value');
		var partIndex = pParent.children("input[name='cm_P_index']").attr('value');

		return "cm_P_" + coursePartIndex + "_" + partIndex;
	}

	//Add new CoursePart
	$("#cm_edit_course").on('click', "#cm_btn", function(e) {
		e.preventDefault();

		var index = $("input[name = 'cm_nr_of_courseparts']").attr('value');
		var nonce = $(e.target).attr( 'data-nonce' );
		var course_id = $("input[name = 'course']").attr('value');

		var data = {
			'action': 'cm_new_course_part',
			'cm_coursePart_index': index,
			'cm_coursePart_course_id': course_id,
			'cm_coursePart_nonce' : nonce
		};

		jQuery.post(new_course.ajaxurl, data, function (response) {
			if(response !== "Failure") {
				var cm_CP_con = $('.cm_coursePartList');

				cm_CP_con.append(response);
				$("input[name = 'cm_nr_of_courseparts']").attr('value', parseInt(index) + 1);
				$(".cm_CP_index").each(function () {
					$(this).attr('max', parseInt(index) + 1);
				});

				jQuery(document.body).trigger('post-load');
			}
		});
	});

	//Add new Part
	$(".cm_coursePartList").on('click', "#cmCP_btn", function(e) {
		e.preventDefault();
		var cPElem = $(e.target).parents(".cm_coursePart");
		var cPElemId = cPElem.attr('id');
		var cPIndex = cPElemId.substring(cPElemId.length - 1, cPElemId.length);
		var cPID = cPElem.find("input[name = 'cm_CP_" + cPIndex + "_ID']").attr('value');
		var index = $("input[name = 'cm_CP_" + cPIndex + "_nr_of_parts']").attr('value');
		var nonce = $(e.target).attr( 'data-nonce' );

		var data = {
			'action': 'cm_new_part',
			'cm_part_index': index,
			'cm_part_nonce' : nonce,
			'cm_part_CP_ID' : cPID,
			'cm_part_CP_Index' : cPIndex
		};


		jQuery.post(new_course.ajaxurl, data, function (response) {
			if(response !== "Failure") {
				var cmPCon = cPElem.find(".cm_coursePart_collapsed");

				cmPCon.append(response);
				$("input[name = 'cm_CP_" + cPIndex + "_nr_of_parts']").attr('value', parseInt(index) + 1);
				cPElem.find(".cm_P_index").each(function () {
					$(this).attr('max', parseInt(index) + 1);
				});

				jQuery(document.body).trigger('post-load');
			}
		});
	});

	//Handle CmPart type switch
	$(".cm_coursePartList").on('change', ".cm_part_type_select", function (e) {
		var cParent = $(e.target).parents(".cm_coursePart");
		var pParent = $(e.target).parents(".cm_part");
		var partPrefix = getPartPrefix(cParent, pParent);
		var coursePartIndex = cParent.children("input[name='cm_CP_index']").attr('value');
		var coursePartID = cParent.children("input[name='cm_CP_ID']").attr('value');
		var partIndex = pParent.children("input[name='cm_P_index']").attr('value');
		var partID = pParent.children("input[name='cm_P_ID']").attr('value');
		var partOriginalType = pParent.children("input[name='cm_P_type']").attr('value');
		var newPartType = $(e.target).val();
		var getOldContent = 0;

		if(partOriginalType == newPartType){
			getOldContent = 1;
		}

		var data = {
			'action': 'cm_change_part_type',
			'cm_part_index': partIndex,
			'cm_part_CP_ID' : coursePartID,
			'cm_part_CP_Index' : coursePartIndex,
			'cm_part_ID' : partID,
			'cm_part_old_content' : getOldContent,
			'cm_part_type' : newPartType,
			'cm_part_prefix' : partPrefix
		};

		jQuery.post(new_course.ajaxurl, data, function (response) {
			if(response !== "Failure") {
				var cmPCon = pParent.find(".cm_part_content_container");

				cmPCon.html(response);

				jQuery(document.body).trigger('post-load');
			}
		});
	});

	//Handle add new question
	$(".cm_coursePartList").on('click', "#cmP_quest_btn", function (e) {
		e.preventDefault();

		var questList = $(e.target).next(".cm_part_content_questions");
		var newQuestIndex = questList.children().length;
		var coursePartIndex = $(e.target).parents(".cm_coursePart").children("input[name='cm_CP_index']").attr('value');
		var partIndex = $(e.target).parents(".cm_part").children("input[name='cm_P_index']").attr('value');
		var partPrefix = "cm_P_" + coursePartIndex + "_" + partIndex;

		var data = {
			'action': 'cm_add_question',
			'cm_part_question_index' : newQuestIndex,
			'cm_part_prefix' : partPrefix
		};

		jQuery.post(new_course.ajaxurl, data, function (response) {
			if(response !== "Failure") {

				questList.append(response);

				jQuery(document.body).trigger('post-load');
			}
		});

	});
});