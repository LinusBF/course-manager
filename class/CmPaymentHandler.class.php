<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-01-05
 * Time: 15:58
 */

class CmPaymentHandler {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		do_action('cm_payment_manager_init', $this);
	}

	private static function _isApiKeySet(){
		$oCM = new CourseManager();
		$aKeys = $oCM->getOptions()['stripe'];

		return (!($aKeys['secret_key'] === -1) && !($aKeys['publishable_key'] === -1)&& !($aKeys['webhook_secret'] === -1));
	}

	private static function _getApiKeys(){
		if (!self::_isApiKeySet())
			return false;

		$oCM = new CourseManager();
		return $oCM->getOptions()['stripe'];
	}

	public static function stripeActive(){
		return self::_isApiKeySet();
	}

	public static function createCustomer($sToken, $sEmail){
		if(!self::stripeActive()){
			return false;
		}
		$oCM = new CourseManager();
		//TODO - Add failure handling
		\Stripe\Stripe::setApiKey(self::_getApiKeys()['secret_key']);

		$customer = \Stripe\Customer::create(array(
			'email' => $sEmail,
			'source'  => $sToken
		));

		return $customer;
	}

	public static function chargeCustomer($iCustomerId, $iCourseId){
		if(!self::stripeActive()){
			return false;
		}

		$oCM = new CourseManager();
		$oCourse = CmCourse::getCourseByID($iCourseId);
		$aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());
		$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
		$iPrice = floor($iPrice) * 100;

		//TODO - Add failure handling
		\Stripe\Stripe::setApiKey(self::_getApiKeys()['secret_key']);

		$charge = \Stripe\Charge::create(array(
			'customer' => $iCustomerId,
			'amount'   => $iPrice,
			'currency' => $oCM->getOptions()['currency']
		));

		return $charge;
	}

	public static function getUserFromStripSessionId($sSessionId) {
        \Stripe\Stripe::setApiKey(self::_getApiKeys()['secret_key']);
        $stripe = new \Stripe\StripeClient(self::_getApiKeys()['secret_key']);
	    $session = $stripe->checkout->sessions->retrieve($sSessionId, []);
        $oCustomer = \Stripe\Customer::retrieve($session->customer);
        return CmUserManager::getUserByEmail($oCustomer->email);
    }

	public static function handlePurchaseRequest(){
		$aRequestResponse = array(
			"already_purchased" => false,
			"purchase_status" => false,
			"status_message" => "POST variables not set!",
			"status_code" => -1
		);


		if(!self::stripeActive()){
			$aRequestResponse['status_message'] = "API KEY NOT SET!";
			return $aRequestResponse;
		}

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

				try {
					$charge = CmPaymentHandler::chargeCustomer( $customer->id, $_POST['course_id'] );
				} catch ( \Stripe\Error\Card $e ) {
					wp_redirect( CmCourseStoreHandler::getLandingPageURL( $_POST['course_id'] ) . "?card_declined=true" );
				} catch ( \Stripe\Error\Authentication $e){
					wp_redirect( CmCourseStoreHandler::getLandingPageURL( $_POST['course_id'] ) . "?api_key_fail=true" );
				}

				if ( isset($charge) && $charge->status === "succeeded" && CmUserManager::acquireCourse( $CmUserId, $_POST['course_id'] ) ) {
					if ( isset( $_POST['subscribe'] ) && $_POST['subscribe'] === "on" ) {
						CmUserManager::subscribeUserToCourse($CmUserId, $_POST['course_id'], true);
					} else{
						CmUserManager::subscribeUserToCourse($CmUserId, $_POST['course_id']);
					}

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

	public static function createStripeSession($iCourseId, $blSubscribe = false) {
        $aSessionCreation = array(
            "already_purchased" => false,
            "status_message" => "POST variables not set!",
            "status_code" => -1
        );
        if(!self::stripeActive()){
            $aSessionCreation['status_message'] = "API KEY NOT SET!";
            return $aSessionCreation;
        }

        \Stripe\Stripe::setApiKey(self::_getApiKeys()['secret_key']);

        $aStripeParams = [
            'payment_method_types' => ['card'],
            'line_items' => [],
            'mode' => 'payment',
            'metadata' => ['course_id' => $iCourseId, 'subscription' => $blSubscribe],
            'success_url' => CmCourseStoreHandler::getStoreURL().'?stripe_session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => CmCourseStoreHandler::getLandingPageURL($iCourseId).'?stripe_cancel=true',
        ];
        if(isset($_SESSION['course_user'])){
            if(CmUserManager::checkAccess($_SESSION['course_user']->id, $_POST['course_id'])){
                $aSessionCreation['already_purchased'] = true;
                $aSessionCreation['status_message'] = "User already owns product";
                $aSessionCreation['status_code'] = 2;
                return $aSessionCreation;
            }
            $aStripeParams['customer_email'] = $_SESSION['course_user']->email;
        }

        $oCM = new CourseManager();
        $oCourse = CmCourse::getCourseByID($iCourseId);
        $aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());
        $iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
        $iPrice = floor($iPrice) * 100;
        array_push($aStripeParams['line_items'], [
            'price_data' => [
                'currency' => $oCM->getOptions()['currency'],
                'product_data' => [
                    'name' => $oCourse->getCourseName()
                ],
                'unit_amount' => $iPrice,
            ],
            'quantity' => 1,
        ]);

        try {
            $session = \Stripe\Checkout\Session::create($aStripeParams);
            error_log(wp_json_encode($session));
            $aSessionCreation['session'] = $session;
            $aSessionCreation['already_purchased'] = false;
            $aSessionCreation['status_message'] = "Session created";
            $aSessionCreation['status_code'] = 1;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $aSessionCreation['status_message'] = 'Stripe API failed';
            $aSessionCreation['status_code'] = 0;
            $aSessionCreation['error_message'] = $e;
        }

        return $aSessionCreation;
    }

	private static function processCheckout($session) {
        error_log(wp_json_encode($session));
        $courseId = $session->metadata['course_id'];
        $subscription = $session->metadata['subscription'] === "true";
        $oCourse = CmCourse::getCourseByID($courseId);
        $oCustomer = \Stripe\Customer::retrieve($session->customer);

        $CmUserId = CmUserManager::registerUser($oCustomer->email);
        if($CmUserId === -1 && $CmUserId !== false){
            $CmUser = CmUserManager::getUserByEmail($oCustomer->email);
            if (CmUserManager::checkAccess($CmUser['ID'], $courseId)){
                //Send warning to admin about double charging
                $oMandrill = new CmMandrillController();
                $oMandrill->sendRefundWarningToOwner($oCustomer->email, $oCourse->getCourseName());
                return true;
            } else{
                $CmUserId = $CmUser['ID'];
            }
        } elseif($CmUserId === false){
            //Send warning to admin about failed purchase
            $oMandrill = new CmMandrillController();
            $oMandrill->sendPurchaseWarningToOwner($oCustomer->email, $oCourse->getCourseName());

            return false;
        }

        if(CmUserManager::acquireCourse($CmUserId, $courseId) !== false) {
            CmUserManager::subscribeUserToCourse($CmUserId, $courseId, $subscription);
        } else {
            //Send warning to admin about failed purchase
            $oMandrill = new CmMandrillController();
            $oMandrill->sendPurchaseWarningToOwner($oCustomer->email, $oCourse->getCourseName());
            return false;
        }
        return true;
    }

	public static function handleStripeHook(WP_REST_Request $request) {
        if(!self::stripeActive()){
            return new WP_Error( 'stripe_not_active', 'Stripe has not been activated!', array( 'status' => 400 ) );
        }

        \Stripe\Stripe::setApiKey(self::_getApiKeys()['secret_key']);
        $endpoint_secret = self::_getApiKeys()['webhook_secret'];

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            return new WP_Error( 'payload_error', 'Invalid payload', array( 'status' => 400 ) );
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return new WP_Error( 'signature_error', 'Invalid signature', array( 'status' => 400 ) );
        }

        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            self::processCheckout($session);
        }

        return array('message' => 'success');
    }
}
