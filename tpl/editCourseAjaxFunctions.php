<?php
/**
 *
 */
function cm_add_new_coursePart(){

	if(isset($_POST['cm_coursePart_index']) && wp_verify_nonce($_POST['cm_coursePart_nonce'], 'cm_add_new_course')) {

		$newCoursePart = CmCoursePart::create();
		$newCoursePart->setCoursePartName(TXT_CM_EDIT_NEW_COURSE_PART_TITLE);
		$newCoursePart->setCourseIndex(intval($_POST['cm_coursePart_index']));
		$newCoursePart->setCourseID(intval($_POST['cm_coursePart_course_id']));
		$newCoursePart->printListItemRep();

	} else{
		echo wp_json_encode("Failure");
	}
	wp_die();
}

function cm_add_new_part(){

	if(isset($_POST['cm_part_index']) && wp_verify_nonce($_POST['cm_part_nonce'], 'cm_add_new_part')) {

		$newPart = CmPart::create();
		$newPart->setTitle(TXT_CM_EDIT_NEW_PART_TITLE);
		$newPart->setCoursePartID(intval($_POST['cm_part_CP_ID']));
		$newPart->setIndex(intval($_POST['cm_part_index']));
		$newPart->setType("text");
		$newPart->setCoursePartIndex(intval($_POST['cm_part_CP_Index']));
		$newPart->setContent("<h2>".TXT_CM_EDIT_NEW_PART_CONTENT."</h2>");
		$newPart->printListItemRep();

	} else{
		echo wp_json_encode("Failure");
	}
	wp_die();
}

function cm_change_part_type(){

	if(isset($_POST['cm_part_index'])) {

		$newPart = CmPart::create();
		$newPart->setTitle(TXT_CM_EDIT_NEW_PART_TITLE);
		$newPart->setCoursePartID(intval($_POST['cm_part_CP_ID']));
		$newPart->setIndex(intval($_POST['cm_part_index']));
		$newPart->setType($_POST['cm_part_type']);
		$newPart->setCoursePartIndex(intval($_POST['cm_part_CP_Index']));

		if(intval($_POST['cm_part_old_content']) == 1){
			$originalPart = CmPart::getPartByID($_POST['cm_part_ID']);
			if($originalPart !== false){
				$originalContent = $originalPart->getRawContent();
				$newPart->setContent($originalContent);
			} else{
				if($_POST['cm_part_type'] == "question"){
					$newPart->setContent(TXT_CM_EDIT_CHANGE_TYPE_QUEST_CONTENT);
				} elseif ($_POST['cm_part_type'] == "text"){
					$newPart->setContent("<h2>".TXT_CM_EDIT_NEW_PART_CONTENT."</h2>");
				} elseif ($_POST['cm_part_type'] == "image"){
					$newPart->setContent("0");
				} else{
					$newPart->setContent(TXT_CM_EDIT_CHANGE_TYPE_GENERAL_CONTENT);
				}
			}

		} else{
			if($_POST['cm_part_type'] == "question"){
				$newPart->setContent(TXT_CM_EDIT_CHANGE_TYPE_QUEST_CONTENT);
			} elseif ($_POST['cm_part_type'] == "text"){
				$newPart->setContent("<h2>".TXT_CM_EDIT_NEW_PART_CONTENT."</h2>");
			} elseif ($_POST['cm_part_type'] == "image"){
				$newPart->setContent("0");
			} else{
				$newPart->setContent(TXT_CM_EDIT_CHANGE_TYPE_GENERAL_CONTENT);
			}
		}

		$newPart->print_content($_POST['cm_part_prefix']);

	} else{
		echo wp_json_encode("Failure");
	}
	wp_die();
}

function cm_add_question(){
	if (isset($_POST['cm_part_prefix']) && isset($_POST['cm_part_question_index'])){
		?>
		<li class="cm_part_content_question_container">
						<label class="cm_part_content_label" for = "<?php echo $_POST['cm_part_prefix']."_content_quest_".$_POST['cm_part_question_index']; ?>">
							<?php echo TXT_CM_EDIT_PART_CONTENT_QUESTION." ".($_POST['cm_part_question_index'] + 1).":"; ?>
		</label>
		<input id = "cm_part_content_quest" class="cm_part_content_input" name = "<?php echo $_POST['cm_part_prefix']."_content_quest_".$_POST['cm_part_question_index']; ?>"
			   type = "text" value = "" />
		<img class='cm_part_content_quest_del' src="<?php echo CM_URLPATH."gfx/cm_delete_quest.png"; ?>"
			 onmouseover="this.src='<?php echo CM_URLPATH."gfx/cm_delete_quest_hover.png"; ?>'"
			 onmouseout="this.src='<?php echo CM_URLPATH."gfx/cm_delete_quest.png"; ?>'" />
		</li>
		<?php
	} else{
		echo wp_json_encode("Failure");
	}
	wp_die();
}
?>