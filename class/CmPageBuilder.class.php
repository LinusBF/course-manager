<?php
/**
 * CmPageBuilder.class.php
 *
 * The CmPageBuilder class file.
 *
 * PHP versions 7
 *
 * @category  CourseManager
 * @package   CourseManager
 * @author    Linus Bein Fahlander <linus.webdevelopment@gmail.com>
 * @copyright 2016-2016 Linus Bein Fahlander
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: 0.1
 * @link      Coming soon
 */

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2017-01-03
 * Time: 12:47
 */


class CmPageBuilder
{


	/**
	 * Constructor
	 */
	public function __construct()
	{
		do_action('cm_page_builder_init', $this);
	}


	/**
	 * @param CmCourse $oCourse
	 *
	 * @return int[] IDs of the pages created
	 */
	public function createCoursePages($oCourse)
	{
		//Go through all CmCourseParts and generate wp_posts with its CmParts
		$aCourseParts = $oCourse->getCourseParts();
		$iCourseId = $oCourse->getCourseID();
		$sCourseName = $oCourse->getCourseName();

		$aPageIds = array();
		$aCoursePartIds = array();

		foreach ($aCourseParts as $oCoursePart){
			array_push($aCoursePartIds, $oCoursePart->getCoursePartID());
			$iPostId = $this->_checkCoursePartPost($oCoursePart->getCoursePartID());

			array_push($aPageIds, $this->_genCoursePage($iCourseId, $sCourseName, $oCoursePart, $iPostId));
		}

		//Cleanup deleted CmCourseParts
		$this->_coursePagesCleanup($iCourseId, $aCoursePartIds);

		return $aPageIds;
	}


	/**
	 * @param int $iCourseId
	 * @param string $sCourseName
	 * @param CmCoursePart $oCoursePart
	 * @param int $iPostID Function will update page instead of creating if not 0
	 *
	 * @return int|WP_Error
	 */
	protected function _genCoursePage($iCourseId, $sCourseName, $oCoursePart, $iPostID = 0){
		$iCpIndex = $oCoursePart->getCourseIndex();
		$iCpId = $oCoursePart->getCoursePartID();
		$sCpTitle = $oCoursePart->getCoursePartName();
		$sPageTitle = $sCourseName." - ".$sCpTitle;
		$sPageElementId = "cm_course_".$iCourseId."_".$iCpIndex;
		$sPageName = $sCourseName."-".$iCpIndex."-".$sCpTitle;

		$sPageContent = "<div id='$sPageElementId' class='cm_page_wrap'>";

		foreach ($oCoursePart->getParts() as $oPart){
			$sDivId = "cm_part_divider_".$oPart->getIndex();

			$sPageContent .= "<div id='$sDivId' class='cm_part_divider'>";
			$sPageContent .= $this->_handlePartContent($oPart);
			$sPageContent .= "</div>";
		}

		$sPageContent .= "</div>";

		$aPostData = $this->_getPostDataArray($sPageTitle, $sPageContent, $iCourseId, $iCpId, $sPageName, $iPostID);

		$iGeneratedPageId = wp_insert_post($aPostData);

		return $iGeneratedPageId;
	}


	/**
	 * @param CmPart $oPart
	 * @return string The html string representing the parts content that is to be added to the page
	 */
	protected function _handlePartContent($oPart)
	{
		$sType = $oPart->getType();
		$sContent = $oPart->getContent();
		$sTitle = $oPart->getTitle();
		$iIndex = $oPart->getIndex();
		$iCPIndex = $oPart->getCoursePartIndex();

		$sPartAttrId = "cm_CP_".$iCPIndex."_P_".$iIndex;

		//TODO - Add titles

		if ($sType == "text"){
			return "<p id='$sPartAttrId' class='cm_page_text'>$sContent</p>";

		} elseif ($sType == "image"){
			return "<img id='$sPartAttrId' class='cm_page_image' src='$sContent' />";

		} elseif ($sType == "video"){
			//TODO
			//Handle youtube link
			$sVideoId = explode("v=", $sContent)[1];
			//Return iFrame element
			return "<iframe width='560' height='315' src='https://www.youtube.com/embed/$sVideoId' frameborder='0' allowfullscreen></iframe>";

		} elseif ($sType == "question"){
			if (!is_array($sContent)){
				$aContent = CmPart::parse_quest($sContent);
			} else{
				$aContent = $sContent;
			}

			$sHtmlString = "<ul id='$sPartAttrId' class='cm_page_quest'>";

			foreach ($aContent as $iIndex => $sQuestion){
				$sHtmlString .= "<li class='cm_page_quest_item'>
									<label class='cm_page_quest_lbl' for='".$sPartAttrId."_q_".$iIndex."'>$sQuestion</label>
									<input class='cm_page_quest_input' type='text' name='".$sPartAttrId."_q_".$iIndex."' />
								</li>";
			}

			//TODO - Add save button
			$sHtmlString .= "</ul>";

			return $sHtmlString;

		} elseif ($sType == "download"){
			$sFileTypeClass = "cm_dl_".substr($sContent, strrpos($sContent, ".") + 1);

			return "<a id='$sPartAttrId' class='cm_page_dl $sFileTypeClass' href='$sContent' download>$sTitle</a>";

		}

		return TXT_CM_PAGE_TYPE_NOT_SUPPORTED;
	}


	/**
	 * @param string $sCoursePartTitle - The title of the page
	 * @param string $sCoursePartContent - The content that should be on the page
	 * @param int $iCourseId
	 * @param int $iCoursePartId
	 * @param string $sPageName
	 * @param int $iPostID - If not 0 it will update an already created page
	 * @return array containing all params to use with wp_insert_post()
	 */
	protected function _getPostDataArray($sCoursePartTitle, $sCoursePartContent, $iCourseId, $iCoursePartId, $sPageName, $iPostID = 0)
	{
		$aPostData = array(
			'ID' => $iPostID,
			'post_excerpt' => 'cm_course',
			'post_type' => 'page',
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'post_title' => wp_strip_all_tags($sCoursePartTitle),
			'post_name' => $sPageName,
			'post_content' => $sCoursePartContent,
			'meta_input'   => array(
				'cm_course_id' => $iCourseId,
				'cm_course_part_id' => $iCoursePartId
			)
		);

		return $aPostData;
	}


	/**
	 * @param int $iCoursePartID
	 * @return int 0 if no post was found, ID of the post if found
	 */
	protected function _checkCoursePartPost($iCoursePartID)
	{
		global $wpdb;

		$sTablePosts = $wpdb->prefix."posts";
		$sTablePostmeta = $wpdb->prefix."postmeta";

		$sSQL = "SELECT $sTablePosts.ID FROM $sTablePosts 
				JOIN $sTablePostmeta ON $sTablePosts.ID = $sTablePostmeta.post_id 
				WHERE $sTablePostmeta.meta_key = 'cm_course_part_id' AND $sTablePostmeta.meta_value = %d";

		$sQuery = $wpdb->prepare($sSQL, $iCoursePartID);

		$oResponse = $wpdb->get_row($sQuery);

		if(isset($oResponse)){
			return intval($oResponse->ID);
		} else{
			return 0;
		}
	}


	/**
	 * @param int $iCourseId
	 * @param int[] $aCoursePartIds
	 * @return bool
	 */
	protected function _coursePagesCleanup($iCourseId, $aCoursePartIds)
	{
		global $wpdb;

		$sTablePostmeta = $wpdb->prefix."postmeta";

		$sSQL = "SELECT DISTINCT meta1.meta_value FROM $sTablePostmeta AS meta1 
				JOIN $sTablePostmeta AS meta2 ON meta2.meta_key = 'cm_course_id' AND meta2.meta_value = %d
				WHERE meta1.meta_key = 'cm_course_part_id'";

		$sQuery = $wpdb->prepare($sSQL, $iCourseId);

		$aResponse = $wpdb->get_col($sQuery);

		if(isset($aResponse)){
			foreach ($aResponse as $iPartId){
				if (!in_array($iPartId, $aCoursePartIds)){
					$sDelSQL = "SELECT meta.post_id FROM $sTablePostmeta AS meta WHERE meta.meta_key = 'cm_course_part_id' AND meta.meta_value = %d";

					$sQuery = $wpdb->prepare($sDelSQL, $iPartId);
					$iPostId = $wpdb->get_row($sQuery)->post_id;

					wp_delete_post($iPostId, true);
				}
			}

			return true;

		} else{
			return false;
		}
	}

}