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


	/**
	 * @param bool $blForce - Force activation?
	 *
	 * @return bool - False if store is already active and !$blForce | True otherwise
	 */
	public function activateStore($blForce = false){
		if ($blForce){

			$this->_oCourseManager->setOption('store_active', 'true');

			$aPostData = $this->_getStorePageArray(true, $this->_getStorePageId(), $this->_getStorePageData());

			wp_insert_post($aPostData);

			return true;
		}
		else{
			if(!$this->isStoreActive()){

				$this->_oCourseManager->setOption('store_active', 'true');

				$aPostData = $this->_getStorePageArray(true, $this->_getStorePageId(), $this->_getStorePageData());

				wp_insert_post($aPostData);

				return true;

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

			return true;

		} else{
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
	 * @return int - ID of the page, 0 if not found
	 */
	protected function _getStorePageId(){
		$oStorePage = get_page_by_title(wp_strip_all_tags(TXT_CM_STORE_PAGE_TITLE));

		if (isset($oStorePage)){
			return $oStorePage->ID;
		} else {
			return 0;
		}
	}


	/**
	 * @return string
	 */
	protected function _getStorePageData(){
		return "<p>Look at all these chicken!</p>";
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