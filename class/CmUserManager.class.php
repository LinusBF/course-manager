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
 * @copyright 2016-2016 Linus Bein Fahlander
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


	/**
	 * @param string $sEmail - The users email
	 * @param array $aUserMeta - An array containing user meta data
	 *
	 * @return bool
	 */
	public static function registerUser($sEmail, $aUserMeta = array()){

		//Add default meta values if not present
		foreach (self::getDefaultMetaKeys() as $defaultMetaKey){
			!in_array( $defaultMetaKey, array_keys($aUserMeta) ) ? $aUserMeta[$defaultMetaKey] = null : null;
		}
		//Generate unique user token
		$sToken = substr(md5(microtime()), 0, 16);


		//Add the user to the DB and update the meta information for that user
		global $wpdb;

		$sSQL = "INSERT INTO ".DB_CM_USERS."(user_token, email) VALUES(%s, %s)";
		$sQuery = $wpdb->prepare($sSQL, $sToken, $sEmail);

		if($wpdb->query($sQuery) === false){
			return false;
		} else{
			$iUserId = $wpdb->insert_id;
		}

		return self::updateUserMeta($iUserId, $aUserMeta) ? true : false;
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
        			user_id INT NOT NULL,
					course_id INT,
					purchase_date DATE NOT NULL,
					FOREIGN KEY (user_id) REFERENCES ".$sCmUserManagerTable."(ID) ON DELETE CASCADE,
					FOREIGN KEY (course_id) REFERENCES ".$sCmCourseTableName."(ID) ON DELETE SET NULL,
					PRIMARY KEY (user_id)
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

}