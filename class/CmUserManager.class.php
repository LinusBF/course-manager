<?php

/**
 * CmUserManager.class.php
 *
 * This class handles all user and token related functions
 *
 * Created by PhpStorm.
 * User: Linus
 * Date: 2017-02-04
 * Time: 15:29
 * @category  CourseManager
 * @package   CourseManager
 * @author    Linus Bein Fahlander <linus.webdevelopment@gmail.com>
 * @copyright 2016-2018 Linus Bein Fahlander
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $Id$
 * @link      Coming soon
 */
class CmUserManager {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		do_action('cm_user_manager_init', $this);
	}


	/**
	 * Adds the user manager DB tables
	 */
	public function install_UM(){
		global $wpdb;
		include_once ABSPATH.'wp-admin/includes/upgrade.php';

		$sCharsetCollate = CourseManager::getCharset();
		$sCmCourseTableName = $wpdb->prefix.'cm_courses';
		$sCmPartTableName = $wpdb->prefix.'cm_parts';

		//Checking if cm_users table exists
		$sCmUserManagerTable = $wpdb->prefix.'cm_users';

		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".$sCmUserManagerTable."'"
		);

		if ($sNameInDb != $sCmUserManagerTable) {
			//Creating cm_users table
			dbDelta(
				"CREATE TABLE ".$sCmUserManagerTable." (
        			ID INT NOT NULL auto_increment,
					user_token VARCHAR(32) NOT NULL,
					email TEXT NOT NULL,
					created_at DATE NOT NULL,
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
			);
		}

		//Checking if cm_users table exists
		$sCmUserEntitlementTable = $wpdb->prefix.'cm_user_entitlements';

		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".$sCmUserEntitlementTable."'"
		);

		if ($sNameInDb != $sCmUserEntitlementTable) {
			//Creating cm_users table
			dbDelta(
				"CREATE TABLE ".$sCmUserEntitlementTable." (
					ID INT NOT NULL AUTO_INCREMENT,
        			user_id INT NOT NULL,
					course_id INT,
					purchase_date DATE NOT NULL,
					FOREIGN KEY (user_id) REFERENCES ".$sCmUserManagerTable."(ID) ON DELETE CASCADE,
					FOREIGN KEY (course_id) REFERENCES ".$sCmCourseTableName."(ID) ON DELETE SET NULL,
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
			);
		}

		//Checking if cm_user_meta table exists
		$sCmUserMetaTable = $wpdb->prefix.'cm_user_meta';

		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".$sCmUserMetaTable."'"
		);

		if ($sNameInDb != $sCmUserMetaTable) {
			//Creating cm_user_meta table
			dbDelta(
				"CREATE TABLE ".$sCmUserMetaTable." (
        			meta_id INT NOT NULL AUTO_INCREMENT,
        			user_id INT NOT NULL,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT DEFAULT NULL,
					FOREIGN KEY (user_id) REFERENCES ".$sCmUserManagerTable."(ID) ON DELETE CASCADE,
					PRIMARY KEY (meta_id)
				) $sCharsetCollate;"
			);
		}

		//Checking if cm_user_answers table exists
		$sCmUserAnswersTable = $wpdb->prefix.'cm_user_answers';

		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".$sCmUserAnswersTable."'"
		);

		if ($sNameInDb != $sCmUserAnswersTable) {
			//Creating cm_user_answers table
			dbDelta(
				"CREATE TABLE ".$sCmUserAnswersTable." (
					ID INT NOT NULL AUTO_INCREMENT,
        			cm_part_id INT,
        			user_id INT NOT NULL,
        			questions LONGTEXT DEFAULT NULL,
					answers LONGTEXT DEFAULT NULL,
					answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
					FOREIGN KEY (cm_part_id) REFERENCES ".$sCmPartTableName."(ID) ON DELETE SET NULL,
					FOREIGN KEY (user_id) REFERENCES ".$sCmUserManagerTable."(ID) ON DELETE CASCADE,
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
			);
		}

		//Create user account page
		$aPostData = $this->_getUserPageArray($this->_getUserPageId());

		wp_insert_post($aPostData);

	}

	/**
	 * Drops the user manager DB tables
	 */
	public function uninstall_UM(){
		global $wpdb;

		$sCmUserManagerTable = $wpdb->prefix.'cm_users';
		$sCmUserEntitlementTable = $wpdb->prefix.'cm_user_entitlements';
		$sCmUserMetaTable = $wpdb->prefix.'cm_user_meta';
		$sCmUserAnswersTable = $wpdb->prefix.'cm_user_answers';

		$wpdb->query(
			"
             DROP TABLE IF EXISTS ".$sCmUserAnswersTable.",
             ".$sCmUserMetaTable.",
             ".$sCmUserEntitlementTable.",
             ".$sCmUserManagerTable."
             "
		);

		wp_delete_post($this->_getUserPageId());
	}


	/**
	 * Returns a list of meta keys that every user should hav in the DB when registered
	 *
	 * @return array
	 */
	private static function getDefaultMetaKeys(){
		$aUserMeta = array(
			"first_name",
			"last_name",
			"phone"
		);

		return $aUserMeta;
	}


	private static function _getAllUsers(){
		global $wpdb;

		$sSQL = "SELECT * FROM ".DB_CM_USERS;
		$aUsers = $wpdb->get_results($sSQL);

		return $aUsers;
	}


	/**
	 * @return int - ID of the page, 0 if not found
	 */
	protected function _getUserPageId(){
		global $wpdb;

		$sSQL = "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_type = %s
			AND post_excerpt = %s
		";

		$sStorePageId = $wpdb->get_row($wpdb->prepare($sSQL, "page", "cm_user_page"));

		if (isset($sStorePageId)){
			return intval($sStorePageId->ID);
		} else {
			return 0;
		}
	}


	private static function _getUserToken( $iUserId ) {
		$aUser = self::getUserById($iUserId);
		return $aUser['user_token'];
	}


	/**
	 * @param int $iUserPageId
	 *
	 * @return array
	 */
	protected function _getUserPageArray($iUserPageId){
		$aPostData = array(
			'ID' => $iUserPageId,
			'post_excerpt' => 'cm_user_page',
			'post_type' => 'page',
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'post_title' => wp_strip_all_tags(TXT_CM_USER_PAGE_TITLE),
			'post_name' => TXT_CM_USER_PAGE_NAME,
			'post_content' => "",
		);

		return $aPostData;
	}


	public static function getUserPageURL(){
		$store = get_posts(array('name' => TXT_CM_USER_PAGE_NAME, 'post_type' => 'page'))[0];
		return get_permalink($store->ID);
	}


	/**
	 * @param string $sEmail - The users email
	 * @param array $aUserMeta - An array containing user meta data
	 *
	 * @return bool
	 */
	public static function registerUser($sEmail, $aUserMeta = array()){

		$aUsers = self::_getAllUsers();

		foreach ($aUsers as $oUser){
			//If user is already registered, return false
			if($oUser->email === $sEmail){
				return -1;
			}
		}

		//Add default meta values if not present
		foreach (self::getDefaultMetaKeys() as $defaultMetaKey){
			!in_array( $defaultMetaKey, array_keys($aUserMeta) ) ? $aUserMeta[$defaultMetaKey] = null : null;
		}
		//Generate unique user token
		$sToken = substr(md5(microtime().$sEmail), 0, 16);


		//Add the user to the DB and update the meta information for that user
		global $wpdb;

		$sSQL = "INSERT INTO ".DB_CM_USERS."(user_token, email, created_at) VALUES(%s, %s, CURRENT_DATE())";
		$sQuery = $wpdb->prepare($sSQL, $sToken, $sEmail);

		if($wpdb->query($sQuery) === false){
			return false;
		} else{
			$iUserId = $wpdb->insert_id;
		}

		self::updateUserMeta($iUserId, $aUserMeta);

		return $iUserId;
	}


	public static function subscribeUserToCourse($iUserId, $iCourseId, $blSubscribed = false){
		$sUserEmail = self::getUserById($iUserId)["email"];
		$mMailchimpResult = CmMailController::addUserToCourseGroup($iCourseId, $sUserEmail, $blSubscribed);
		if($blSubscribed){
			CmUserManager::updateUserMeta($iUserId, array("subscribed" => "true"));
		}
		$oMandrill = new CmMandrillController();
		$mMandrillResult = $oMandrill->sendTokenToUser($iUserId, $iCourseId, self::_getUserToken($iUserId));
		return array("mailchimp_result" => $mMailchimpResult, "mandrill_result" => $mMandrillResult);
	}


	/* DEPRECATED - Using subscribeUserToCourse for now. Might activate to make use of separate newsletter list.
	public static function subscribeUserToMailList($iUserId){

		CmUserManager::updateUserMeta($iUserId, array("subscribed" => "true"));

		return true;
	}*/


	/**
	 * @param int $iUserId
	 * @param array $aUserMeta - An array containing user meta data
	 *
	 * @return bool
	 */
	public static function updateUserMeta($iUserId, $aUserMeta){
		global $wpdb;

		//Get used meta keys
		$sMetaKeyCheckSQL = "SELECT meta_key FROM ".DB_CM_USER_META." WHERE user_id = %d";
		$sMetaKeyCheckQuery = $wpdb->prepare($sMetaKeyCheckSQL, $iUserId);
		$aActiveMetaKeys = $wpdb->get_col($sMetaKeyCheckQuery);

		$blQuerySuccess = true;
		//Go through all the meta keys and update/set their value
		foreach ($aUserMeta as $sMetaKey => $sMetaValue){
			if(in_array($sMetaKey, $aActiveMetaKeys)){
				$sSQL = "UPDATE ".DB_CM_USER_META." SET meta_value = %s WHERE meta_key = %s AND user_id = %d";
			} else{
				$sSQL = "INSERT INTO ".DB_CM_USER_META."(meta_value, meta_key, user_id) VALUES(%s, %s, %d)";
			}

			$sQuery = $wpdb->prepare($sSQL, $sMetaValue === null ? null : (string) $sMetaValue, $sMetaKey, (int) $iUserId);
			if($wpdb->query($sQuery) === false){
				$blQuerySuccess = false;
			}
		}

		return $blQuerySuccess;
	}


	public static function setCookie($sToken){
		setcookie("cm_token", "", (time() -  (60 * 60)), "/");
		setcookie("cm_token", $sToken, time() +  (180 * 24 * 60 * 60), "/");
		$_COOKIE['cm_token'] = $sToken;
	}


	public static function unsetTokenCookie(){
		setcookie("cm_token", "", time() -  (60 * 60), "/");
	}


	public static function updateSessionFromCookie(){
		if(isset($_COOKIE['cm_token'])){
			$aUser = CmUserManager::getUserByToken($_COOKIE['cm_token']);

			if($aUser !== false){
				$_SESSION['course_user'] = array(
					"id" => $aUser['ID'],
					"email" => $aUser['email'],
					"token" => $aUser['user_token']
				);
			} else{
				return false;
			}

			return true;
		}

		return false;
	}


	public static function resetUserSession(){
		if(isset($_SESSION['course_user'])){
			unset($_SESSION['course_user']);
		}

		if(isset($_COOKIE['cm_token'])){
			$aUser = CmUserManager::getUserByToken($_COOKIE['cm_token']);

			if($aUser !== false){
				$_SESSION['course_user'] = array(
					"id" => $aUser['ID'],
					"email" => $aUser['email'],
					"token" => $aUser['user_token']
				);
			} else{
				return false;
			}

			return true;
		}

		return false;
	}


	public static function getUserByToken($sToken){
		global $wpdb;

		$sSQL = "SELECT * FROM ".DB_CM_USERS." WHERE user_token = %s";
		$sQuery = $wpdb->prepare($sSQL, $sToken);
		$aUser = $wpdb->get_row($sQuery, ARRAY_A);
		return ($aUser !== null ? $aUser : false);
	}


	public static function getUserByEmail($sEmail){
		global $wpdb;

		$sSQL = "SELECT * FROM ".DB_CM_USERS." WHERE email = %s";
		$sQuery = $wpdb->prepare($sSQL, $sEmail);
		$aUser = $wpdb->get_row($sQuery, ARRAY_A);
		return ($aUser !== null ? $aUser : false);
	}


	public static function getUserById($iId){
		global $wpdb;

		$sSQL = "SELECT * FROM ".DB_CM_USERS." WHERE ID = %d";
		$sQuery = $wpdb->prepare($sSQL, $iId);
		$aUser = $wpdb->get_row($sQuery, ARRAY_A);
		return ($aUser !== null ? $aUser : false);
	}


	public static function acquireCourse($iUserId, $iCourseId){
		if(CmUserManager::checkAccess($iUserId, $iCourseId)){
			return false;
		}

		global $wpdb;

		$sSQL = "INSERT INTO ".DB_CM_USER_ENTITLEMENTS." (user_id, course_id, purchase_date) VALUES(%d, %d, CURRENT_DATE())";

		$sQuery = $wpdb->prepare($sSQL, $iUserId, $iCourseId);

		return $wpdb->query($sQuery);
	}


	public static function getFreeCourse(){
		$aRequestResponse = array(
			"already_purchased" => false,
			"purchase_status" => false,
			"status_message" => "POST variables not set!",
			"status_code" => -1
		);

		if(isset($_POST['cm_action']) && $_POST['cm_action'] === "get_course" && isset($_GET['my_courses'])) {
			$CmUserId = CmUserManager::registerUser( $_POST['email'] );

			if ( $CmUserId === - 1 && $CmUserId !== false ) {
				$CmUser = CmUserManager::getUserByEmail( $_POST['email'] );
				if($CmUser !== false){
					if ( CmUserManager::checkAccess( $CmUser['ID'], $_POST['course_id'] ) ) {
						$aRequestResponse['already_purchased'] = true;
					} else {
						$CmUserId = $CmUser['ID'];
					}
				} else{
					$aRequestResponse['status_message'] = "Could not fetch CM-user with email ".$_POST['email'];
					$aRequestResponse['status_code']    = 4;

					return $aRequestResponse;
				}
			} elseif ( $CmUserId === false ) {
				$aRequestResponse['status_message'] = "Could not create CM-user!";
				$aRequestResponse['status_code']    = 4;

				return $aRequestResponse;
			}

			if ( ! $aRequestResponse['already_purchased'] ) {
				if ( CmUserManager::acquireCourse( $CmUserId, $_POST['course_id'] ) ) {
					$aRequestResponse['purchase_status'] = true;
					$aRequestResponse['status_message']  = "The course is yours!";
					$aRequestResponse['status_code']     = 1;
					if ( isset( $_POST['subscribe'] ) && $_POST['subscribe'] === "on" ) {
						CmUserManager::subscribeUserToCourse( $CmUserId, $_POST['course_id'], true);
					} else{
						CmUserManager::subscribeUserToCourse( $CmUserId, $_POST['course_id']);
					}
				} else {
					$aRequestResponse['purchase_status'] = false;
					$aRequestResponse['status_message']  = "Acquisition of course failed";
					$aRequestResponse['status_code']     = 0;
				}
			} else {
				$aRequestResponse['purchase_status'] = false;
				$aRequestResponse['status_message']  = "User already owns product";
				$aRequestResponse['status_code']     = 2;
			}

			//Set current user in session to email used for purchase
			$CmUser = CmUserManager::getUserById( $CmUserId );
			CmUserManager::resetUserSession();
			CmUserManager::setCookie( $CmUser['user_token'] );
			CmUserManager::resetUserSession();
		}

		return $aRequestResponse;
	}


	/**
	 *
	 * @param $iUserId - int
	 *
	 * @param bool $blFullObject
	 *
	 * @return CmCourse[] $aCourses
	 */
	public static function getPurchasedCourses($iUserId, $blFullObject = false){
		global $wpdb;

		$sSQL = "SELECT course_id FROM ".DB_CM_USER_ENTITLEMENTS." WHERE user_id = %d ORDER BY purchase_date DESC";
		$sQuery = $wpdb->prepare($sSQL, $iUserId);
		$aCourseIds = $wpdb->get_col($sQuery);
		$aCourseIds = array_unique($aCourseIds);

		$aCourses = array();

		foreach ($aCourseIds as $iId){
			array_push($aCourses, CmCourse::getCourseByID(intval($iId), $blFullObject));
		}

		return $aCourses;
	}

	public static function checkAccess($iUserId, $iCourseId) {
		global $wpdb;

		$sSQL = "SELECT purchase_date FROM ".DB_CM_USER_ENTITLEMENTS." WHERE user_id = %d AND course_id = %d ORDER BY purchase_date DESC";
		$sDate = $wpdb->get_var($wpdb->prepare($sSQL, $iUserId, $iCourseId));

		//Customer has not purchased the course #TODO - Handle different than expired course
		if ($sDate === null){
			return false;
		}

		$sSQL = "SELECT span FROM ".DB_CM_COURSES." WHERE ID = %d";
		$iSpan = $wpdb->get_var($wpdb->prepare($sSQL, $iCourseId));

		//Customer has had access to the course for longer than the courses span.
		if(strtotime("+".$iSpan." days", strtotime($sDate)) < strtotime(date(get_option('date_format')))){
			return false;
		}

		return true;
	}

	public static function getAllPartsAndAnswers($iUserId){
		$aCourses = CmUserManager::getPurchasedCourses($iUserId, true);
		$aAnswers = array();

		foreach ($aCourses as $iIC => $oCourse){
			$aCourseParts = $oCourse->getCourseParts();
			$aCpAnswers = array();

			foreach ($aCourseParts as $iICP => $oCoursePart){
				$aParts = $oCoursePart->getParts();
				$aPAnswers = array();

				foreach ($aParts as $iIP => $oPart){
					if($oPart->getType() == "question"){
						$aAnswer = CmUserManager::getAnswers($iUserId, $oPart->getPartID());
						array_push($aPAnswers, array("index" => $iIP, "part" => $oPart, "answers" => $aAnswer));
					}
				}

				if (count($aPAnswers) > 0){
					array_push($aCpAnswers, array("index" => $iICP, "course-part" => $oCoursePart, "answers" => $aPAnswers));
				}
			}

			if (count($aCpAnswers) > 0){
				array_push($aAnswers, array("index" => $iIC, "course" => $oCourse, "answers" => $aCpAnswers));
			}
		}

		return $aAnswers;
	}

	public static function getAnswers($iUserId, $iPartId){
		global $wpdb;

		$sSQL = "SELECT questions, answers FROM ".DB_CM_USER_ANSWERS." WHERE user_id = %d AND cm_part_id = %d ORDER BY answered_at DESC";
		$sQnA = $wpdb->get_row($wpdb->prepare($sSQL, $iUserId, $iPartId));

		//No answers to the question
		if ($sQnA === null){
			return false;
		}

		$aQs = CmPart::parse_quest(stripslashes($sQnA->questions));
		$aAs = CmPart::parse_quest(stripslashes(htmlspecialchars($sQnA->answers)));

		$aQnA = array(
			"Q" => $aQs,
			"A" => $aAs
		);

		return $aQnA;
	}

	public static function answerQuestions($iUserId, $iPartId, $aAnswers){
		$oPart = CmPart::getPartByID($iPartId);
		if($oPart->getType() !== "question") {return false;}
		$sQs = $oPart->getRawContent();
		$sAs = CmPart::parse_quest($aAnswers);

		global $wpdb;

		$blResult = $wpdb->replace(
			DB_CM_USER_ANSWERS,
			array(
				"user_id" => $iUserId,
				"cm_part_id" => $iPartId,
				"questions" => $sQs,
				"answers" => $sAs
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
			)
		);

		return ($blResult);
	}

}