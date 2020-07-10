<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2020-07-10
 * Time: 14:19
 */

function cm_create_stripe_session(){
	if(isset($_POST['cm_course_id'])) {
        $aSessionResponse = CmPaymentHandler::createStripeSession($_POST['cm_course_id'], isset($_POST['cm_subscribe']) && $_POST['cm_subscribe']);
        if($aSessionResponse['status_code'] == 1) {
            echo wp_json_encode(array("status" => "Success", "data" => $aSessionResponse['session']->id));
        } else {
            echo wp_json_encode(array("status" => "Failure", "data" => null, 'msg' => $aSessionResponse['status_message']));
        }

	} else{
		echo wp_json_encode(array("status" => "Failure", "data" => null, "msg" => "POST params not set or incorrect"));
	}
	wp_die();
}

?>