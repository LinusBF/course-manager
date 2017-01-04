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
		//Check for already created pages for the course, and call @updateCoursePages if so

		//Go through all CmCourseParts and generate wp_posts with its CmParts
		$aCourseParts = $oCourse->getCourseParts();
		$iCourseId = $oCourse->getCourseID();
		$sCourseName = $oCourse->getCourseName();

		$aPageIds = array();

		foreach ($aCourseParts as $oCoursePart){
			array_push($aPageIds, $this->_genCoursePage($iCourseId, $sCourseName, $oCoursePart));
		}

		return $aPageIds;
	}


	/**
	 * @param CmCourse $oCourse
	 */
	public function updateCoursePages($oCourse)
	{
		//Go through all pages created for the CmCourse, update them and add new pages for new CmCourseParts in the CmCourse
	}


	/**
	 * @param int $iCourseId
	 * @param string $sCourseName
	 * @param CmCoursePart $oCoursePart
	 * @param int $iPageId Function will update page instead of creating if not 0
	 *
	 * @return int|WP_Error
	 */
	protected function _genCoursePage($iCourseId, $sCourseName, $oCoursePart, $iPageId = 0){
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

		$aPostData = $this->_getPostDataArray($sPageTitle, $sPageContent, $iCourseId, $iCpId, $sPageName, $iPageId);

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
			return "";

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
				'course_id' => $iCourseId,
				'course_part_id' => $iCoursePartId
			)
		);

		return $aPostData;
	}

}