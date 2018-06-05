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
	 * @return bool|int[] - false if the course is active | IDs of the pages created
	 */
	public function createCoursePages($oCourse)
	{
		if ($oCourse->getCourseStatus()){
			return false;
		}

		//Go through all CmCourseParts and generate wp_posts with its CmParts
		$aCourseParts = $oCourse->getCourseParts();
		$iCourseId = $oCourse->getCourseID();
		$sCourseName = $oCourse->getCourseName();
		$blCourseStatus = $oCourse->getCourseStatus();

		$aPageIds = array();
		$aCoursePartIds = array();

		foreach ($aCourseParts as $iIndex => $oCoursePart){
			array_push($aCoursePartIds, $oCoursePart->getCoursePartID());
			$iPostId = $this->_checkCoursePartPost($oCoursePart->getCoursePartID());

			//Data for the links to the previous and next course part
			$aSurroundingPartsIds = array(
				'prev' => ($iIndex === 0 ? null: $iIndex - 1),
				'next' => ($iIndex === count($aCourseParts) - 1 ? null: $iIndex + 1),
			);

			array_push($aPageIds, $this->_genCoursePage($iCourseId, $sCourseName, $oCoursePart, $aCourseParts, $aSurroundingPartsIds, $blCourseStatus, $iPostId));
		}

		//Cleanup deleted CmCourseParts
		$this->_coursePagesCleanup($iCourseId, $aCoursePartIds);

		return $aPageIds;
	}


	/**
	 * @param int $iCourseId
	 * @param string $sCourseName
	 * @param CmCoursePart $oCoursePart
	 * @param CmCoursePart[] $aCourseParts
	 * @param $aSurroundingPartsIds
	 * @param bool $blCourseStatus
	 * @param int $iPostID Function will update page instead of creating if not 0
	 *
	 * @return int|WP_Error
	 */
	protected function _genCoursePage($iCourseId, $sCourseName, $oCoursePart, $aCourseParts, $aSurroundingPartsIds, $blCourseStatus, $iPostID = 0){
		$iCpIndex = $oCoursePart->getCourseIndex();
		$iCpId = $oCoursePart->getCoursePartID();
		$sCpTitle = $oCoursePart->getCoursePartName();
		$sPageTitle = $sCourseName." - ".$sCpTitle;
		$sPageElementId = "cm_course_".$iCourseId."_".$iCpIndex;
		$sPageName = $sCourseName."-".$sCpTitle;  // Add index if client thinks that s/he will use the same title within a Course

		$sPageContent = "<div id='$sPageElementId' class='cm_page_wrap'>";

		$sPageContent .= "<section class='row'>";
		$sPageContent .= "<div class='cm_page_title_container col-sm-12'>
							<p class='cm_title_course'>$sCourseName</p>
							<h1 class='cm_title_part'>$sCpTitle</h1>
						  </div>";
		$sPageContent .= "</section>";

		$sPageContent .= "<section class='row'>";
		$sPageContent .= "<div class='cm_parts_wrap col-sm-8'>
							<div class='cm_parts_wrap_inner'>";

		foreach ($oCoursePart->getParts() as $oPart){
			$sDivId = "cm_part_divider_".$oPart->getIndex();

			$sPageContent .= "<section class='row'>
								<div id='$sDivId' class='cm_part_divider'>";
			$sPageContent .= $this->_handlePartContent($oPart);
			$sPageContent .= "</div>
							</section>";
		}

		$sPageContent .= "</div>
						</div>";
		$sPageContent .= $this->_getCourseNavBar($sCourseName, $aCourseParts, $aSurroundingPartsIds);
		$sPageContent .= "</section>";
		$sPageContent .= "</div>";

		$aPostData = $this->_getPostDataArray($sPageTitle, $sPageContent, $iCourseId, $iCpId, $sPageName, $blCourseStatus, $iPostID);

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

		$sPostHeader = "";
		$sPostFooter = "";

		if (isset($sTitle) && $sTitle != ""){
			$sPostHeader .= "<h3 class='cm_part_title'>$sTitle</h3>";
		}

		$sPostHeader .= "<div class='cm_part_content'>";
		$sPostFooter .= "</div>";

		if ($sType == "text"){
			return $sPostHeader."<div id='$sPartAttrId' class='cm_page_text'>$sContent</div>".$sPostFooter;

		} elseif ($sType == "image"){
			return $sPostHeader."<img id='$sPartAttrId' class='cm_page_image' src='".wp_get_attachment_url($sContent)."' />".$sPostFooter;

		} elseif ($sType == "video"){
			//Handle youtube link

			if(strpos($sContent, "v=") !== false){
				$sVideoId = explode("v=", $sContent)[1];
			}else{
				$sVideoId = $sContent;
			}
			//Return iFrame element
			return $sPostHeader."<iframe width='560' height='315' src='https://www.youtube.com/embed/$sVideoId?rel=0' frameborder='0' allowfullscreen></iframe>".$sPostFooter;

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

			$sHtmlString .= "</ul>";
			$sHtmlString .= "<div class='cm_answer_button_container'>
								<a class='w3-btn cm_btn cm_answer_questions' href='#'>".TXT_CM_PAGE_SAVE_ANSWERS."</a><img class='cm_answer_loading cm_hidden' src='".CM_URLPATH."gfx/cm_loading.gif"."'>
							</div>"; //TODO - Expand save button

			return $sPostHeader.$sHtmlString.$sPostFooter;

		} elseif ($sType == "download"){
			$sFileTypeClass = "cm_dl_".substr($sContent, strrpos($sContent, ".") + 1);

			return $sPostHeader."<a id='$sPartAttrId' class='cm_page_dl $sFileTypeClass' target='_blank' href='$sContent'>$sTitle</a>".$sPostFooter;

		}

		return TXT_CM_PAGE_TYPE_NOT_SUPPORTED;
	}


	/**
	 * @param string $sCourseTitle
	 * @param CmCoursePart[] $aCourseParts
	 * @param array $aSurIds - Surrounding coursePart ids
	 *
	 * @return string
	 */
	protected function _getCourseNavBar($sCourseTitle, $aCourseParts, $aSurIds){
		$sNavContent = "<div class='cm_nav_bar col-sm-4' style='z-index: 0;'>
						<div class='cm_nav_container'>";

		$sNavContent .= "<section class='row'>";
		$sNavContent .= "<div class='cm_nav_title col-sm-12'><p>$sCourseTitle</p></div>";
		$sNavContent .= "</section>";

		$aLinkInfo = $this->getCoursePartLinks($sCourseTitle, $aCourseParts);

		$sNavContent .= "<section class='row'>";

		if( isset($aSurIds['prev']) || isset($aSurIds['next'])) {
			$sNavContent .= "<div class='cm_course_links col-sm-12'>";

			if ( isset( $aSurIds['prev'] ) ) {
				$sNavContent .= "<a id='cm_prev_part_link' class='cm_part_nav_btn sf-button sf-button-rounded accent btn btn-secondary' 
									href='" . $aLinkInfo[$aSurIds['prev']]['link'] . "'>"
				               . TXT_CM_PAGE_PREV_PART . "</a>";
			}

			if ( isset( $aSurIds['next'] ) ) {
				$sNavContent .= "<a id='cm_next_part_link' class='cm_part_nav_btn sf-button sf-button-rounded accent btn btn-secondary' 
									href='" . $aLinkInfo[$aSurIds['next']]['link'] . "'>"
				               . TXT_CM_PAGE_NEXT_PART . "</a>";
			}

			$sNavContent .= "</div>";
		}

		$sNavContent .= "</section>";
		$sNavContent .= "<section class='cm_nav_parts row'>";

		foreach ($aLinkInfo as $aLink){
			$sNavContent .= "<div class='cm_nav_part col-sm-12'>";
			$sIsCurrent = ((!isset($aSurIds['prev']) && $aLink['index'] === 0) || $aLink['index'] === $aSurIds['prev'] + 1 ? " cm_current" : "");
			$sLinkElement = "<a id='$sIsCurrent' class='cm_nav_part_link' href='".$aLink['link']."'>".$aLink['title']."</a>";
			$sNavContent .= $sLinkElement;
			$sNavContent .= "</div>";
		}

		$sNavContent .= "</section>";

		$sNavContent .= "</div>
					</div>";

		return $sNavContent;
	}


	/**
	 * @param string $sCoursePartTitle - The title of the page
	 * @param string $sCoursePartContent - The content that should be on the page
	 * @param int $iCourseId
	 * @param int $iCoursePartId
	 * @param string $sPageName
	 * @param bool $blCourseStatus
	 * @param int $iPostID - If not 0 it will update an already created page
	 * @return array containing all params to use with wp_insert_post()
	 */
	protected function _getPostDataArray($sCoursePartTitle, $sCoursePartContent, $iCourseId, $iCoursePartId, $sPageName, $blCourseStatus, $iPostID = 0)
	{
		$aPostData = array(
			'ID' => $iPostID,
			'post_excerpt' => $iCourseId.','.$iCoursePartId,
			'post_type' => 'cm_course_page',
			'post_status' => ($blCourseStatus ? 'publish' : 'draft'),
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
		$sTablePostMeta = $wpdb->prefix."postmeta";

		$sSQL = "SELECT $sTablePosts.ID FROM $sTablePosts 
				JOIN $sTablePostMeta ON $sTablePosts.ID = $sTablePostMeta.post_id 
				WHERE $sTablePostMeta.meta_key = 'cm_course_part_id' AND $sTablePostMeta.meta_value = %d";

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

		$sTablePostMeta = $wpdb->prefix."postmeta";

		$sSQL = "SELECT post_id FROM $sTablePostMeta AS meta WHERE meta.meta_key = 'cm_course_id' AND meta.meta_value = %d";

		$sQuery = $wpdb->prepare($sSQL, $iCourseId);

		$aResponse = $wpdb->get_col($sQuery);

		if(isset($aResponse)){
			foreach ($aResponse as $iPostId){
				$sGetPartSQL = "SELECT meta_value FROM $sTablePostMeta AS meta WHERE meta.meta_key = 'cm_course_part_id' AND meta.post_id = %d";
				$sGetPartQuery = $wpdb->prepare($sGetPartSQL, $iPostId);
				$iPartId = (int) $wpdb->get_row($sGetPartQuery)->meta_value;

				if (!in_array($iPartId, $aCoursePartIds)){
					wp_delete_post($iPostId, true);
				}
			}

			return true;

		} else{
			return false;
		}
	}


	/**
	 * @param int $iCourseId
	 * @return array Contains the ids of all the pages for the course
	 */
	public function getCoursePageIds($iCourseId){
		$oCourse = CmCourse::getCourseByID($iCourseId, true);
		$aCourseParts = $oCourse->getCourseParts();

		$aPageIds = array();
		foreach ($aCourseParts as $oCoursePart){
			$iPageId = $this->_checkCoursePartPost($oCoursePart->getCoursePartID());
			array_push($aPageIds, $iPageId);
		}

		return $aPageIds;
	}


	/**
	 * @param int $iCourseId
	 * @return bool True if all pages were updated, False if not
	 */
	public function updateCoursePagesStatus($iCourseId){
		$oCourse = CmCourse::getCourseByID($iCourseId, true);
		if($oCourse->getCourseStatus()){
			$blPostStatus = "publish";
		} else{
			$blPostStatus = 'draft';
		}

		$blUpdateSuccess = true;
		foreach ($this->getCoursePageIds($iCourseId) as $iPageId){
			if ($iPageId > 0){
				$aPost = array('ID' => $iPageId, 'post_status' => $blPostStatus);
				if(wp_update_post($aPost) < 1){
					$blUpdateSuccess = false;
				}
			}
		}

		return $blUpdateSuccess;
	}


	/**
	 * @param int $iCourseId
	 *
	 * @return bool|string
	 */
	public static function getCourseFirstPageName( $iCourseId ) {
		global $wpdb;

		$oFirstPart = CmCourse::getCourseByID($iCourseId, true)->getCourseParts()[0];

		$iPartId = $oFirstPart->getCoursePartID();

		$sQuery = $wpdb->prepare("SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key = 'cm_course_part_id' AND meta_value = %d", $iPartId);

		$iPostId = $wpdb->get_row($sQuery);

		if (isset($iPostId)){
			$sNameQuery = $wpdb->prepare("SELECT post_name FROM ".$wpdb->posts." WHERE ID = %d", (int) $iPostId->post_id);
			$sPageName = $wpdb->get_row($sNameQuery);

			if (isset($sPageName)){
				return $sPageName->post_name;
			}
		}

		return false;
	}


	/**
	 * @param $iCoursePartId
	 *
	 * @return bool|string
	 *
	 */
	public static function getCoursePageName( $iCoursePartId ) {
		global $wpdb;

		$sQuery = $wpdb->prepare("SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key = 'cm_course_part_id' AND meta_value = %d", $iCoursePartId);

		$iPostId = $wpdb->get_row($sQuery);

		if (isset($iPostId)){
			$sNameQuery = $wpdb->prepare("SELECT post_name FROM ".$wpdb->posts." WHERE ID = %d", (int) $iPostId->post_id);
			$sPageName = $wpdb->get_row($sNameQuery);

			if (isset($sPageName)){
				return $sPageName->post_name;
			}
		}

		return false;
	}

	/**
	 * @param string $sCourseName
	 * @param CmCoursePart[] $aCourseParts
	 *
	 * @return array
	 */
	private function getCoursePartLinks($sCourseName, $aCourseParts){
		$aLinks = array();
		foreach ($aCourseParts as $iIndex => $oCoursePart){
			array_push($aLinks, array(
				'id' => $oCoursePart->getCoursePartID(),
				'index' => $oCoursePart->getCourseIndex(),
				'title' => $oCoursePart->getCoursePartName(),
				'link' => CmPageBuilder::getCoursePartUrlName($sCourseName, $oCoursePart)
			));
		}

		return $aLinks;
	}


	/**
	 * @param string $sCourseName
	 * @param CmCoursePart $oCoursePart
	 *
	 * @return string
	 */
	public static function getCoursePartUrlName($sCourseName, $oCoursePart){
		$aUri = explode("wp-admin", $_SERVER["REQUEST_URI"]);
		$sLinkBase = sanitize_title($sCourseName . '-' . $oCoursePart->getCoursePartName());
		return reset($aUri) . 'courses/' . $sLinkBase;
	}

}