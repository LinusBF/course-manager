jQuery(document).ready(function($){

	function getCoursePartDeleteStatus(element){
		if($(element.target).attr("class") != "cm_coursePart"){
			var parentCoursePart = $(element.target).parents(".cm_coursePart");
			var coursePartIndex = parentCoursePart.children("input[name='cm_CP_index']").attr("value");

			return parentCoursePart.children("input[name='cm_CP_" + coursePartIndex + "_del']").attr("value");
		} else{
			var coursePartIndex = $(element.target).children("input[name='cm_CP_index']").attr("value");

			return $(element.target).children("input[name='cm_CP_" + coursePartIndex + "_del']").attr("value");
		}
	}

	function getPartDeleteStatus(element){
		var parentCoursePart = $(element.target).parents(".cm_coursePart");
		var coursePartIndex = parentCoursePart.children("input[name='cm_CP_index']").attr("value");

		if($(element.target).attr("class") != "cm_part"){
			var parentPart = $(element.target).parents(".cm_part");
			var partIndex = parentPart.children("input[name='cm_P_index']").attr("value");

			return parentPart.children("input[name='cm_P_" + coursePartIndex + "_" + partIndex + "_del']").attr("value");
		} else{
			var partIndex = $(element.target).children("input[name='cm_P_index']").attr("value");

			return $(element.target).children("input[name='cm_P_" + coursePartIndex + "_" + partIndex + "_del']").attr("value");
		}
	}

	//Slide functions for course editing page
	$(".cm_coursePartList").on('click', ".cm_coursePart", function(e) {
		if($(e.target).attr("class") == ".cm_coursePart"){
			if(getCoursePartDeleteStatus(e) != "1"){
				$(e.target).children("ul").slideToggle();
			}
		}
	});

	$(".cm_coursePartList").on('click', ".cm_coursePart_header", function(e) {
		if(getCoursePartDeleteStatus(e) != "1"){
			$(e.target).parent().children("ul").slideToggle();
		}
	});

	$(".cm_coursePartList").on('click', ".cm_coursePart_name", function(e) {
		if(getCoursePartDeleteStatus(e) != "1"){
			$(e.target).parent().parent().children("ul").slideToggle();
		}
	});

	$(".cm_coursePartList").on('click', ".cm_part", function(e) {
		if(getPartDeleteStatus(e) != "1"){
			$(e.target).children("ul").slideToggle();
		}
	});

	$(".cm_coursePartList").on('click', ".cm_part_header", function(e) {
		if(getPartDeleteStatus(e) != "1"){
			$(e.target).parent().children("ul").slideToggle();
		}
	});

	$(".cm_coursePartList").on('click', ".cm_part_name", function(e) {
		if(getPartDeleteStatus(e) != "1"){
			$(e.target).parent().parent().children("ul").slideToggle();
		}
	});

	//On CoursePart index change, switch all element ids, names etc of the affected CoursePart and its Parts
	$(".cm_coursePartList").on('change', ".cm_CP_index", function (e) {
		var parent = $(e.target).parents(".cm_coursePart");
		var newIndex = $(e.target).val() - 1;
		var oldIndex = parent.children("input[name='cm_CP_index']").attr('value');
		parent.attr('id', parent.attr('id').substring(0, parent.attr('id').length - 1) + newIndex);
		parent.children("input[name='cm_CP_index']").attr('value', newIndex);

		var tags = ['input', 'select', 'textarea'];
		//CoursePart
		tags.forEach(function (item) {
			parent.find(item + "[name^='cm_CP_" + oldIndex + "']").each(function () {
				var oldName = $(this).attr('name');
				var newName = oldName.substring(0, 6) + newIndex + oldName.substring(7);
				$(this).attr('name', newName);
			});
		});
		parent.find("label[for^='cm_CP_" + oldIndex + "']").each(function () {
			var oldName = $(this).attr('for');
			var newName = oldName.substring(0, 6) + newIndex + oldName.substring(7);
			$(this).attr('for', newName);
		});

		//Parts
		tags.forEach(function (item) {
			parent.find(item + "[name^='cm_P_" + oldIndex + "']").each(function () {
				var oldName = $(this).attr('name');
				var newName = oldName.substring(0, 5) + newIndex + oldName.substring(6);
				$(this).attr('name', newName);
			});
		});
		parent.find("label[for^='cm_P_" + oldIndex + "']").each(function () {
			var oldName = $(this).attr('for');
			var newName = oldName.substring(0, 5) + newIndex + oldName.substring(6);
			$(this).attr('for', newName);
		});
	});

	//On Part index change, switch all element ids, names etc of the affected elements
	$(".cm_coursePartList").on('change', ".cm_P_index", function (e) {
		var parent = $(e.target).parents(".cm_part");
		var coursePartParent = $(e.target).parents(".cm_coursePart");
		var coursePartIndex = coursePartParent.children("input[name='cm_CP_index']").attr('value');
		var newIndex = $(e.target).val() - 1;
		var oldIndex = parent.children("input[name='cm_P_index']").attr('value');
		parent.attr('id', parent.attr('id').substring(0, parent.attr('id').length - 1) + newIndex);
		parent.children("input[name='cm_P_index']").attr('value', newIndex);

		var tags = ['input', 'select', 'textarea'];

		tags.forEach(function (item) {
			parent.find(item + "[name^='cm_P_" + coursePartIndex + "_" + oldIndex + "']").each(function () {
				var oldName = $(this).attr('name');
				var newName = oldName.substring(0, 7) + newIndex + oldName.substring(8);
				$(this).attr('name', newName);
			});
		});
		parent.find("label[for^='cm_P_" + coursePartIndex + "_" + oldIndex + "']").each(function () {
			var oldName = $(this).attr('for');
			var newName = oldName.substring(0, 7) + newIndex + oldName.substring(8);
			$(this).attr('for', newName);
		});
	});

	//Handle delete CoursePart
	$(".cm_coursePartList").on('click', ".cm_coursePart_del", function (e) {
		var imgEl = $(e.target);
		var imgSrc = imgEl.attr('src');
		var imgSrcHover = imgEl.attr('onmouseover');
		var parentCoursePart = $(e.target).parents(".cm_coursePart");
		var coursePartIndex = parentCoursePart.children("input[name='cm_CP_index']").attr("value");
		var delEl = parentCoursePart.children("input[name='cm_CP_" + coursePartIndex + "_del']");
		var delStatus = delEl.attr("value");

		var needleString = "gfx/cm_";
		var newImgSrc = "";
		var newImgSrcHover = "";
		var newDelStatus = 0;

		if(delStatus == "0") {
			newImgSrc = imgSrc.substring(0, imgSrc.indexOf(needleString) + needleString.length) + "cancel_delete.png";
			newImgSrcHover = imgSrcHover.substring(0, imgSrcHover.indexOf(needleString) + needleString.length) + "cancel_delete_hover.png";
			newDelStatus = "1";
		} else{
			newImgSrc = imgSrc.substring(0, imgSrc.indexOf(needleString) + needleString.length) + "delete.png";
			newImgSrcHover = imgSrcHover.substring(0, imgSrcHover.indexOf(needleString) + needleString.length) + "delete_hover.png";
			newDelStatus = "0";
		}

		imgEl.attr("src", newImgSrc);
		imgEl.attr("onmouseout", "this.src='" + newImgSrc + "'");
		imgEl.attr("onmouseover", newImgSrcHover + "'");
		delEl.attr("value", newDelStatus);

		if(newDelStatus == "1"){
			parentCoursePart.children("ul").slideUp();
			parentCoursePart.css("background", "#A9A9A9");
			parentCoursePart.find(".cm_coursePart_name").css("text-decoration", "line-through");
		} else{
			parentCoursePart.css("background", "#fafafa");
			parentCoursePart.find(".cm_coursePart_name").css("text-decoration", "none");
		}

	});

	//Handle delete Part
	$(".cm_coursePartList").on('click', ".cm_part_del", function (e) {
		var imgEl = $(e.target);
		var imgSrc = imgEl.attr('src');
		var imgSrcHover = imgEl.attr('onmouseover');
		var parentCoursePart = $(e.target).parents(".cm_coursePart");
		var coursePartIndex = parentCoursePart.children("input[name='cm_CP_index']").attr("value");
		var parentPart = $(e.target).parents(".cm_part");
		var partIndex = parentPart.children("input[name='cm_P_index']").attr("value");
		var delEl = parentPart.children("input[name='cm_P_" + coursePartIndex + "_" + partIndex + "_del']");
		var delStatus = delEl.attr("value");

		var needleString = "gfx/cm_";
		var newImgSrc = "";
		var newImgSrcHover = "";
		var newDelStatus = 0;

		if(delStatus == "0") {
			newImgSrc = imgSrc.substring(0, imgSrc.indexOf(needleString) + needleString.length) + "cancel_delete.png";
			newImgSrcHover = imgSrcHover.substring(0, imgSrcHover.indexOf(needleString) + needleString.length) + "cancel_delete_hover.png";
			newDelStatus = "1";
		} else{
			newImgSrc = imgSrc.substring(0, imgSrc.indexOf(needleString) + needleString.length) + "delete.png";
			newImgSrcHover = imgSrcHover.substring(0, imgSrcHover.indexOf(needleString) + needleString.length) + "delete_hover.png";
			newDelStatus = "0";
		}

		imgEl.attr("src", newImgSrc);
		imgEl.attr("onmouseout", "this.src='" + newImgSrc + "'");
		imgEl.attr("onmouseover", newImgSrcHover + "'");
		delEl.attr("value", newDelStatus);

		if(newDelStatus == "1"){
			parentPart.children("ul").slideUp();
			parentPart.css("background", "#A9A9A9");
			parentPart.find(".cm_part_name").css("text-decoration", "line-through");
		} else{
			parentPart.css("background", "#fafafa");
			parentPart.find(".cm_part_name").css("text-decoration", "none");
		}

	});


	//Handle delete question
	$(".cm_coursePartList").on('click', ".cm_part_content_quest_del", function (e) {
		$(e.target).parents(".cm_part_content_question_container").remove();
	});

});