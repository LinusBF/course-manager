<?php

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2017-03-05
 * Time: 13:33
 */
class CmStore {

	protected $_oCourseManager = null;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		do_action('cm_store_init', $this);

		$this->_oCourseManager = new CourseManager();

	}


	public function installStore() {
		global $wpdb;
		include_once ABSPATH.'wp-admin/includes/upgrade.php';

		$sCharsetCollate = CourseManager::getCharset();

		//Checking if cm_store_meta table exists
		$sNameInDb = $wpdb->get_var(
			"SHOW TABLES LIKE '".DB_CM_STORE_META."'"
		);

		if ($sNameInDb != DB_CM_STORE_META) {
			//Creating cm_store_meta table
			dbDelta(
				"CREATE TABLE ".DB_CM_STORE_META." (
        			meta_id INT NOT NULL AUTO_INCREMENT,
        			course_id INT NOT NULL,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT DEFAULT NULL,
					FOREIGN KEY (course_id) REFERENCES ".DB_CM_COURSES."(ID) ON DELETE CASCADE,
					PRIMARY KEY (meta_id)
				) $sCharsetCollate;"
			);
		}
	}


	public function uninstallStore() {
		global $wpdb;

		$wpdb->query(
			"
             DROP TABLE IF EXISTS ".DB_CM_STORE_META."
             "
		);
	}


	/**
	 * @return boolean
	 */
	public function isStoreActive(){
		$aOptions = $this->_oCourseManager->updateOptionsFromDb();

		return $aOptions['store_active'];
	}


	/**
	 * @return CmCourse[]
	 */
	public function getCoursesForStore(){
		$aCourses = CmCourse::getAllActiveCourses();
		$aCourseIdsForStore = $this->_oCourseManager->getOptions()['courses_in_store'];

		$aStoreCourses = array();

		foreach($aCourses as $oCourse){
			if(in_array($oCourse->getCourseID(), $aCourseIdsForStore)){
				array_push($aStoreCourses, $oCourse);
			}
		}

		return $aStoreCourses;

	}


	public function storeActivationCheck(){
		return (CmMailController::mailChimpActive() && CmPaymentHandler::stripeActive());
	}


	/**
	 * @param bool $blForce - Force activation?
	 *
	 * @return bool - False if store is already active and !$blForce | True otherwise
	 */
	public function activateStore($blForce = false){
		if ($blForce){
			if($this->storeActivationCheck()) {
				$this->_oCourseManager->setOption( 'store_active', true );

				$aPostData = $this->_getStorePageArray( true, $this->_getStorePageId(), $this->_getStorePageData() );

				return wp_insert_post($aPostData) > 0;
			}
			else{
				return false;
			}
		}
		else{
			if(!$this->isStoreActive() && $this->storeActivationCheck()){

				$this->_oCourseManager->setOption('store_active', true);

				$aPostData = $this->_getStorePageArray(true, $this->_getStorePageId(), $this->_getStorePageData());

				return wp_insert_post($aPostData) > 0;

			} else{
				return false;
			}
		}
	}


	/**
	 *  Deletes the store page
	 */
	public function deactivateStore() {

		$iPageId = $this->_getStorePageId();
		if ($iPageId > 0){
			wp_delete_post($iPageId, true);

			$this->_oCourseManager->setOption('store_active', false);

			return true;

		} else{
			$this->_oCourseManager->setOption('store_active', false);
			return false;
		}
	}


	/**
	 * @param $iCourseId
	 *
	 * @return bool
	 */
	public function addCourseToStore($iCourseId){
		$aOptions = $this->_oCourseManager->getOptions();

		if (!in_array($iCourseId, $aOptions['courses_in_store'])){
			array_push($aOptions['courses_in_store'], $iCourseId);

			$this->_oCourseManager->setOption('courses_in_store', $aOptions['courses_in_store']);
		}

		return true;
	}


	public function removeCourseFromStore($iCourseId){
		$aOptions = $this->_oCourseManager->getOptions();

		if (in_array($iCourseId, $aOptions['courses_in_store'])){
			$aOptions['courses_in_store'] = array_diff($aOptions['courses_in_store'], array($iCourseId));

			$this->_oCourseManager->setOption('courses_in_store',$aOptions['courses_in_store']);
		}

		return true;
	}


	/**
	 *  Returns the store data in JSON-format
	 */
	public function exportToJSON(){
		return json_encode($this->_getAllStoreData());
	}


	/**
	 *  Returns an array containing all of the rows in the store_meta table
	 */
	private function _getAllStoreData(){
		global $wpdb;

		$sSQL = "SELECT * FROM ".DB_CM_STORE_META;

		$aResults = $wpdb->get_results($sSQL);

		return $aResults;
	}


	/**
	 * @return int - ID of the page, 0 if not found
	 */
	protected function _getStorePageId(){
		global $wpdb;

		$sSQL = "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_type = %s
			AND post_excerpt = %s
		";

		$sStorePageId = $wpdb->get_row($wpdb->prepare($sSQL, "page", "cm_store"));

		if (isset($sStorePageId)){
			return intval($sStorePageId->ID);
		} else {
			return 0;
		}
	}


	/**
	 * @return string
	 */
	protected function _getStorePageData(){
		return "";
	}


	/**
	 * @param bool $blStoreActive
	 * @param int $iStorePageId
	 * @param string $sStoreContent
	 *
	 * @return array
	 */
	protected function _getStorePageArray($blStoreActive, $iStorePageId, $sStoreContent){
		$aPostData = array(
			'ID' => $iStorePageId,
			'post_excerpt' => 'cm_store',
			'post_type' => 'page',
			'post_status' => ($blStoreActive ? 'publish' : 'draft'),
			'comment_status' => 'closed',
			'post_title' => wp_strip_all_tags(TXT_CM_STORE_PAGE_TITLE),
			'post_name' => TXT_CM_STORE_PAGE_NAME,
			'post_content' => $sStoreContent,
		);

		return $aPostData;
	}

}