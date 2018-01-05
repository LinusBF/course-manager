<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-01-05
 * Time: 15:58
 */

class CmPaymentHandler {
	public static function createCustomer($sToken, $sEmail){
		$oCM = new CourseManager();
		\Stripe\Stripe::setApiKey($oCM->getOptions()['stripe']['secret_key']);

		$customer = \Stripe\Customer::create(array(
			'email' => $sEmail,
			'source'  => $sToken
		));

		return $customer;
	}

	public static function chargeCustomer($iCustomerId, $iCourseId){
		$oCM = new CourseManager();
		$oCourse = CmCourse::getCourseByID($iCourseId);
		$aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());
		$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
		$iPrice = floor($iPrice) * 100;

		\Stripe\Stripe::setApiKey($oCM->getOptions()['stripe']['secret_key']);

		$charge = \Stripe\Charge::create(array(
			'customer' => $iCustomerId,
			'amount'   => $iPrice,
			'currency' => $oCM->getOptions()['currency']
		));

		return $charge;
	}

	public static function handlePurchaseRequest(){
		$aRequestResponse = array(
			"already_purchased" => false,
			"purchase_status" => false,
			"status_message" => "POST variables not set!",
			"status_code" => -1
		);

		if(isset($_POST['stripeToken']) && isset($_POST['stripeEmail']) && isset($_GET['my_courses'])){

			$CmUserId = CmUserManager::registerUser($_POST['stripeEmail']);
			if($CmUserId === -1 && $CmUserId !== false){
				$CmUser = CmUserManager::getUserByEmail($_POST['stripeEmail']);
				if (CmUserManager::checkAccess($CmUser['ID'], $_POST['course_id'])){
					$aRequestResponse['already_purchased'] = true;
				} else{
					$CmUserId = $CmUser['ID'];
				}
			} elseif($CmUserId === false){
				$aRequestResponse['status_message'] = "Could not create CM-user!";
				$aRequestResponse['status_code'] = 4;

				return $aRequestResponse;
			}

			if(!$aRequestResponse['already_purchased']) {
				try{
					$customer = CmPaymentHandler::createCustomer( $_POST['stripeToken'], $_POST['stripeEmail'] );
				} catch (\Stripe\Error\InvalidRequest $e){
					$aRequestResponse['status_message'] = "Identical Stripe token!";
					$aRequestResponse['status_code'] = 3;

					return $aRequestResponse;
				}

				if ( isset( $_POST['subscribe'] ) && $_POST['subscribe'] === "on" ) {
					CmUserManager::subscribeUser( $CmUserId );
				}

				try {
					$charge = CmPaymentHandler::chargeCustomer( $customer->id, $_POST['course_id'] );
				} catch ( \Stripe\Error\Card $e ) {
					wp_redirect( CmCourseStoreHandler::getLandingPageURL( $_POST['course_id'] ) . "?card_declined=true" );
				}

				if ( $charge->status === "succeeded" && CmUserManager::acquireCourse( $CmUserId, $_POST['course_id'] ) ) {
					$aRequestResponse['purchase_status'] = true;
					$aRequestResponse['status_message'] = "Purchase successful!";
					$aRequestResponse['status_code'] = 1;
				} else {
					$aRequestResponse['purchase_status'] = false;
					$aRequestResponse['status_message'] = "Purchase failed";
					$aRequestResponse['status_code'] = 0;
				}
			} else{
				$aRequestResponse['purchase_status'] = false;
				$aRequestResponse['status_message'] = "User already owns product";
				$aRequestResponse['status_code'] = 2;
			}

			//Set current user in session to email used for purchase
			$CmUser = CmUserManager::getUserById($CmUserId);
			CmUserManager::setCookie($CmUser['user_token']);
			CmUserManager::resetUserSession();
		}

		return $aRequestResponse;
	}
}
