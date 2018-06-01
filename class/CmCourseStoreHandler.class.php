<?php

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2017-03-10
 * Time: 18:49
 */
class CmCourseStoreHandler {

	public function __construct__() {
		do_action('cm_store_init', $this);
	}


	/**
	 * @param int $iCourseId
	 *
	 * @return array
	 */
	public static function getStoreOptionsForCourse( $iCourseId ) {
		global $wpdb;

		$sSQL = "SELECT meta_key, meta_value FROM ".DB_CM_STORE_META." WHERE course_id = %d";

		$sQuery = $wpdb->prepare($sSQL, $iCourseId);

		$aResults = $wpdb->get_results($sQuery);

		$aStoreOptions = array();
		foreach ($aResults as $oOption){
			$aStoreOptions[$oOption->meta_key] = maybe_unserialize($oOption->meta_value);
		}

		$aDefaultOptions = self::getDefaultOptions();
		foreach ($aDefaultOptions as $sKey => $aDefaultOption){
			if(!key_exists($sKey, $aStoreOptions) || $aStoreOptions[$sKey] == null){
				$aStoreOptions[$sKey] = $aDefaultOption;
			}
			else if(is_array($aDefaultOption)){
				if(is_array($aStoreOptions[$sKey])){
					foreach ($aDefaultOption as $arrKey => $arrItem){
						if(!key_exists($arrKey, $aStoreOptions[$sKey]) || $aStoreOptions[$sKey][$arrKey] == null){
							$aStoreOptions[$sKey][$arrKey] = $aDefaultOption[$arrKey];
						}
					}
				} else{
					$aStoreOptions[$sKey] = $aDefaultOption;
				}
			}
		}

		return $aStoreOptions;
	}


	/**
	 * @param int $iCourseId
	 * @param array $aNewOptions
	 * @param bool $blDeleteRest - Should the options not in the $NewOptions array be removed from the DB?
	 *
	 * @return bool
	 */
	public function setStoreOptions( $iCourseId, $aNewOptions = array(), $blDeleteRest = false ) {
		//Default options
		$aOptions = CmCourseStoreHandler::getDefaultOptions($iCourseId);

		if(!$blDeleteRest) {
			$aSetOptions = $this->getStoreOptionsForCourse( $iCourseId );

			if ( ! empty( $aSetOptions ) ) {
				foreach ( $aSetOptions as $sKey => $mOption ) {
					$aOptions[ $sKey ] = $mOption;
				}
			}
		}

		if (!empty($aNewOptions)) {
			foreach ($aNewOptions as $sKey => $mOption) {
				$aOptions[$sKey] = $mOption;
			}
		}

		return $this->_storeOptions($iCourseId, $aOptions, $blDeleteRest);
	}


	/**
	 * @param int $iCourseId
	 * @param array $aOptions
	 * @param bool $blDeleteRest - Should the options not in the $NewOptions array be removed from the DB?
	 *
	 * @return bool
	 */
	private function _storeOptions( $iCourseId, $aOptions, $blDeleteRest = false ) {

		global $wpdb;

		//Get existing setting keys
		$sExistingKeysSQL = "SELECT meta_key FROM ".DB_CM_STORE_META." WHERE course_id = %d";
		$sExistingKeysQuery = $wpdb->prepare($sExistingKeysSQL, $iCourseId);

		$aExistingKeys = $wpdb->get_col($sExistingKeysQuery);

		//Set or update the options in the DB
		foreach ($aOptions as $sKey=>$mOption){

			if (in_array($sKey, $aExistingKeys)){
				$sSQL = "UPDATE ".DB_CM_STORE_META." SET meta_value = %s WHERE meta_key = %s AND course_id = %d";
			}
			else{
				$sSQL = "INSERT INTO ".DB_CM_STORE_META."(meta_value, meta_key, course_id) VALUES(%s, %s, %d)";
			}

			$sQuery = $wpdb->prepare($sSQL, maybe_serialize($mOption), $sKey, $iCourseId);


			if($wpdb->query($sQuery) === false){
				return false;
			}


		}

		if($blDeleteRest) {
			//Delete the option keys not used anymore
			$aDefaultKeys = CmCourseStoreHandler::getDefaultOptions();
			foreach ( $aExistingKeys as $sKey ) {
				if ( ! in_array( $sKey, array_keys( $aOptions ) ) && ! in_array( $sKey, array_keys( $aDefaultKeys ) ) ) {
					$mResult = $wpdb->delete( DB_CM_STORE_META, array( 'meta_key'  => $sKey,
					                                                   'course_id' => $iCourseId
					), array( '%s', '%d' ) );

					if ( $mResult === false ) {
						return false;
					}
				}
			}
		}

		return true;

	}


	public static function getDefaultOptions($iCourseId = null) {
		return array(
			'store_image' => '',
			'store_description' => (isset($iCourseId) ? CmCourse::getCourseByID($iCourseId)->getCourseDescription() : ''),
			'current_discount' => '0',
			'settings_modified' => '0',
			'landing_page' => '0',
			'mc_group_category' => array(
				"category_id" => -1,
				"buyer_id" => -1,
				"newsletter_id" => -1
			)
		);
	}


	public static function getStoreURL(){
		$store = get_posts(array('name' => TXT_CM_STORE_PAGE_NAME, 'post_type' => 'page'))[0];
		return get_permalink($store->ID);
	}


	/**
	 * @param $iCourseID
	 *
	 * @return false|string
	 */
	public static function getLandingPageURL($iCourseID){
		global $wpdb;

		$sSQL = "SELECT meta_value FROM ".DB_CM_STORE_META." WHERE course_id = %d AND meta_key = 'landing_page'";

		$sQuery = $wpdb->prepare($sSQL, $iCourseID);
		$iPageID = $wpdb->get_var($sQuery);

		return get_permalink($iPageID);
	}

	public static function activateStripe(){
		$oCM = new CourseManager();
		$aOptions = $oCM->getOptions();

		if($aOptions['stripe']['secret_key'] === -1 || $aOptions['stripe']['publishable_key'] === -1){
			return false;
		}

		\Stripe\Stripe::setApiKey($aOptions['stripe']['secret_key']);

		return true;
	}

	public static function resetCourseMailGroups(){
		//TODO - Remove all connections to mail groups from all courses
	}

}