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

		//Checking if cm_user_data table exists
		$sCmUserMetaTable = $wpdb->prefix.'cm_user_meta';

		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".$sCmUserMetaTable."'"
		);

		if ($sNameInDb != $sCmUserMetaTable) {
			//Creating cm_user_data table
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
					FOREIGN KEY (cm_part_id) REFERENCES ".$sCmPartTableName."(ID) ON DELETE SET NULL,
					FOREIGN KEY (user_id) REFERENCES ".$sCmUserManagerTable."(ID) ON DELETE CASCADE,
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
			);
		}

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


	public static function subscribeUser($iUserId){
		//TODO - Add user email to Mailchimp

		CmUserManager::updateUserMeta($iUserId, array("subscribed" => "true"));

		return true;
	}


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
		setcookie("cm_token", "", time() -  (60 * 60), "/");
		setcookie("cm_token", $sToken, time() +  (30 * 24 * 60 * 60), "/");
	}


	public static function unsetTokenCookie(){
		setcookie("cm_token", "", time() -  (60 * 60), "/");
	}


	public static function checkForCookie(){
		if(isset($_COOKIE['cm_token']) && !isset($_SESSION['course_user'])){
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
				if ( isset( $_POST['subscribe'] ) && $_POST['subscribe'] === "on" ) {
					CmUserManager::subscribeUser( $CmUserId );
				}

				if ( CmUserManager::acquireCourse( $CmUserId, $_POST['course_id'] ) ) {
					$aRequestResponse['purchase_status'] = true;
					$aRequestResponse['status_message']  = "The course is yours!";
					$aRequestResponse['status_code']     = 1;
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
			CmUserManager::setCookie( $CmUser['user_token'] );
			CmUserManager::resetUserSession();
		}

		return $aRequestResponse;
	}

	public static function getPurchasedCourses($iUserId){
		global $wpdb;

		$sSQL = "SELECT course_id FROM ".DB_CM_USER_ENTITLEMENTS." WHERE user_id = %d";
		$sQuery = $wpdb->prepare($sSQL, $iUserId);
		$aCourseIds = $wpdb->get_col($sQuery);

		$aCourses = array();

		foreach ($aCourseIds as $iId){
			array_push($aCourses, CmCourse::getCourseByID(intval($iId)));
		}

		return $aCourses;
	}

	public static function checkAccess($iUserId, $iCourseId) {
		global $wpdb;

		$sSQL = "SELECT purchase_date FROM ".DB_CM_USER_ENTITLEMENTS." WHERE user_id = %d AND course_id = %d";
		$sDate = $wpdb->get_var($wpdb->prepare($sSQL, $iUserId, $iCourseId));

		//Customer has not purchased the course
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

}