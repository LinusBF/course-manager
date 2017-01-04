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
	 */
	public function createCoursePages($oCourse)
	{
		//Check for already created pages for the course, and call @updateCoursePages if so

		//Go through all CmCourseParts and generate wp_posts with its CmParts

		//TEST
		$aCourseParts = $oCourse->getCourseParts();
		$sCourseName = $oCourse->getCourseName();

		echo "<h2>$sCourseName</h2><br>";

		foreach ($aCourseParts as $oCoursePart){
			$sCpTitle = $oCoursePart->getCoursePartName();
			echo "<div><h5>$sCpTitle</h5><br>";

			foreach ($oCoursePart->getParts() as $oPart){
				echo $this->_handlePartContent($oPart);
			}

			echo "</div>";
		}
	}


	/**
	 * @param CmCourse $oCourse
	 */
	public function updateCoursePages($oCourse)
	{
		//Go through all pages created for the CmCourse, update them and add new pages for new CmCourseParts in the CmCourse
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
	 * @param int $iPostID - If not 0 it will update an already created page
	 * @return array containing all params to use with wp_insert_post()
	 */
	protected function _getPostDataArray($sCoursePartTitle, $sCoursePartContent, $iPostID = 0)
	{
		$aPostData = array(
			'ID' => $iPostID,
			'post_excerpt' => 'cm_course',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'post_title' => $sCoursePartTitle,
			'post_content' => $sCoursePartContent,
		);

		return $aPostData;
	}

}