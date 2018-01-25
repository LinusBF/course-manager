<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-01-20
 * Time: 15:43
 */

function cm_answer_question(){

	if(isset($_POST['cm_part_id']) && isset($_POST['cm_answers']) && wp_verify_nonce($_POST['cm_question_nonce'], 'cm_answer_question')) {
		CmUserManager::answerQuestion($_SESSION['course_user']['id'], $_POST['cm_part_id'], $_POST['cm_answers']);

		echo json_encode("Success");

	} else{
		echo json_encode("Failure");
	}
	wp_die();
}


function cm_get_answers(){

	if(isset($_POST['cm_part_id']) && wp_verify_nonce($_POST['cm_answers_nonce'], 'cm_answers')) {
		$aAnswers = CmUserManager::getAnswers($_SESSION['course_user']['id'], $_POST['cm_part_id'])['A'];
		echo json_encode($aAnswers);

	} else{
		echo json_encode("Failure");
	}
	wp_die();
}

function cm_get_part_id(){
	// "cm_part_index_ancestry" structure: CourseId;CoursePartIndex;PartIndex;
	if(isset($_POST['cm_part_index_ancestry']) && wp_verify_nonce($_POST['cm_part_ancestry_nonce'], 'cm_part_ancestry')) {
		list($iCourseId, $iCoursePartIndex, $iPartIndex) = explode(";", $_POST['cm_part_index_ancestry']);

		$oCourse = CmCourse::getCourseByID($iCourseId, true);
		$oPart = $oCourse->getCourseParts()[$iCoursePartIndex]->getParts()[$iPartIndex];

		echo json_encode($oPart->getPartID());

	} else{
		echo json_encode("Failure");
	}
	wp_die();
}

?>