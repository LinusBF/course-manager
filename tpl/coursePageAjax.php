<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-01-20
 * Time: 15:43
 */

function cm_answer_question(){

	if(isset($_POST['cm_part_id']) && isset($_POST['cm_answers']) && wp_verify_nonce($_POST['cm_question_nonce'], 'cm_answer_question')) {
		$blResult = CmUserManager::answerQuestions($_SESSION['course_user']['id'], $_POST['cm_part_id'], $_POST['cm_answers']);

		if($blResult){
			echo wp_json_encode(array("status" => "Success", "data" => $blResult));
		} else{
			echo wp_json_encode(array("status" => "Failure", "data" => null, 'msg' => $blResult));
		}

	} else{
		echo wp_json_encode(array("status" => "Failure", "data" => null, "msg" => "POST params not set or incorrect"));
	}
	wp_die();
}


function cm_get_answers(){

	if(isset($_POST['cm_part_id']) && wp_verify_nonce($_POST['cm_answers_nonce'], 'cm_answers')) {
		$aAnswers = CmUserManager::getAnswers($_SESSION['course_user']['id'], $_POST['cm_part_id']);
		if($aAnswers !== false) {
			echo wp_json_encode( array( "status" => "Success", "data" => $aAnswers ) );
		} else{
			echo wp_json_encode(array("status" => "Failure", "data" => null, "msg" => "The user hasn't answered"));
		}

	} else{
		echo wp_json_encode(array("status" => "Failure", "data" => null, "msg" => "POST params not set or incorrect"));
	}
	wp_die();
}

function cm_get_part_id(){
	// "cm_part_index_ancestry" structure: CourseId;CoursePartIndex;PartIndex;
	if(isset($_POST['cm_part_index_ancestry']) && wp_verify_nonce($_POST['cm_part_ancestry_nonce'], 'cm_part_ancestry')) {
		list($iCourseId, $iCoursePartIndex, $iPartIndex) = explode(";", $_POST['cm_part_index_ancestry']);

		$oCourse = CmCourse::getCourseByID($iCourseId, true);
		$oPart = $oCourse->getCourseParts()[$iCoursePartIndex]->getParts()[$iPartIndex];

		echo wp_json_encode(array("status" => "Success", "data" => $oPart->getPartID()));

	} else{
		echo wp_json_encode(array("status" => "Failure", "data" => null, "msg" => "POST params not set or incorrect"));
	}
	wp_die();
}

?>