<?php
/**
 * CmPart.class.php
 * 
 * The CmPart class file.
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
 * The Part class.
 * 
 * @category CourseManager
 * @package  CourseManager
 * @author   Linus Bein Fahlander <linus.webdevelopment@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @link     Coming soon
 */


/**
* 
*/
class CmPart
{
	protected $_iPartID = null;
	protected $_iCoursePartID = null;
	protected $_sPartTitle = null;
	protected $_sContent = null;
	protected $_sType = null;
	protected $_iPartIndex = null;
	protected $_iCoursePartIndex = null;
	
	/**
	* 
	*
	* @param 
	*
	**/
	protected function __construct()
	{
		do_action('cm_part_init', $this);
	}


	/**
     * Constructor empty.
     *
     * @return CmPart instance
     */
    public static function create()
    {
    	$instance = new self();
    	return $instance;
    }


	/**
	 * Constructor with Title, Content, Index, Type and a CoursePartID.
	 *
	 * @param string $sTitle
	 * @param string $sContent
	 * @param int $iIndex
	 * @param string $sType
	 * @param int $iCoursePartID
	 *
	 * @return CmPart instance
	 */
    public static function createWParams($sTitle, $sContent, $iIndex, $sType, $iCoursePartID)
    {
    	$instance = new self();
    	$instance->setTitle($sTitle);
    	$instance->setContent($sContent);
    	$instance->setIndex($iIndex);
    	$instance->setType($sType);
    	$instance->setCoursePartID($iCoursePartID);
    	return $instance;
    }


    // ---- GETTERS AND SETTERS ---- \\

    /**
     * Returns the title of the CmPart.
     *
     * @return string
     */
    public function getTitle()
    {
    	return htmlspecialchars($this->_sPartTitle, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Sets the title of the CmPart.
     *
     * @param string $sTitle
     *
     * @return null
     */
    public function setTitle($sTitle)
    {
    	$this->_sPartTitle = htmlspecialchars($sTitle, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Returns the content of the CmPart.
     *
     * @return string
     */
    public function getContent()
    {
    	return $this->_sContent;
    }


    /**
     * Sets the content of the CmPart.
     *
     * @param string $sContent
     *
     * @return null
     */
    public function setContent($sContent)
    {
    	$this->_sContent = $sContent;
    }


	/**
     * Returns the CourseIndex.
     *
     * @return int - '-1' if index does not exist
     */
    public function getIndex()
    {
    	if (isset($this->_iPartIndex)) {
    		return (int) $this->_iPartIndex;
    	}

    	return -1;
    }


    /**
     * Sets the index of the CmPart.
     *
     * @param int $iIndex
     *
     * @return null
     */
    public function setIndex($iIndex)
    {
    	$this->_iPartIndex = (int) $iIndex;
    }


    /**
     * Returns the type of the CmPart.
     *
     * @return string
     */
    public function getType()
    {
    	return htmlspecialchars($this->_sType, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Sets the type of the CmPart.
     *
     * @param string $sType
     *
     * @return null
     */
    public function setType($sType)
    {
    	$this->_sType = htmlspecialchars($sType, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Returns the CoursePartID of the CmPart.
     *
     * @return int
     */
    public function getCoursePartID()
    {
    	return (int) $this->_iCoursePartID;
    }


    /**
     * Sets the CoursePartID of the CmPart.
     *
     * @param int $iID
     *
     * @return null
     */
    public function setCoursePartID($iID)
    {
    	$this->_iCoursePartID = (int) $iID;
    }


	/**
	 * Returns the CoursePart index of the CmCourse.
	 *
	 * @return int
	 */
	public function getCoursePartIndex()
	{
		return (int) $this->_iCoursePartIndex;
	}


	/**
	 * Sets the CoursePart index of the CmCourse.
	 *
	 * @param int $iIndex
	 *
	 * @return null
	 */
	public function setCoursePartIndex($iIndex)
	{
		$this->_iCoursePartIndex = (int) $iIndex;
	}


	/**
	 * Returns the PartID of the CmPart.
	 *
	 * @return int
	 */
	public function getPartID()
	{
		if(isset($this->_iPartID)){
			return (int) $this->_iPartID;
		} else{
			return -1;
		}
	}


	/**
     * Returns the name of the DB table for parts.
     *
     * @return string
     */
    protected function _getDbTableName()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_parts';
    }


	/**
	 * Returns the name of the DB table for CourseParts.
	 *
	 * @return string
	 */
	protected function _getDbTableNameCoursePart()
	{
		global $wpdb;
		return $wpdb->prefix.'cm_course_parts';
	}


	/**
	 * Returns an array with the available types a CmPart can have
	 *
	 * @return string[]
	 */
    public function getTypes(){
    	$types = [
    		"text",
			"image",
			"video",
			"question",
			"download"
		];

		return $types;
	}


	/**
	 * Returns the parent CmCoursePart to this CmPart.
	 *
	 * @return CmCoursePart
	 */
	public function getParentCoursePart(){
		$oParentCourse = CmCoursePart::getCoursePartByID($this->getCoursePartID(), true);

		return $oParentCourse;
	}


	/**
     * Return the object representing the Part in the db with the same ID.
     *
     * @param int $iID
     *
     * @return CmPart
     */
    public static function getPartByID($iID)
    {
    	$instance = new self();
    	global $wpdb;

    	$sSQL = "SELECT coursePartID,title,content,type,partIndex FROM ".$instance->_getDbTableName()." WHERE ID = %d";

    	$oPart = $wpdb->get_row($wpdb->prepare($sSQL,$iID));

    	if (isset($oPart)) {

	    	$instance->_iPartID = intval($iID);
	    	$instance->setCoursePartID(intval($oPart->coursePartID));
	    	$instance->setTitle($oPart->title);
	    	$instance->setContent($oPart->content);
	    	$instance->setType($oPart->type);
	    	$instance->setIndex(intval($oPart->partIndex));

			$sCoursePartIndexSQL = "SELECT courseIndex FROM ".$instance->_getDbTableNameCoursePart()." WHERE ID = %d";

			$oCoursePart = $wpdb->get_row($wpdb->prepare($sCoursePartIndexSQL, $instance->getCoursePartID()));
			if(isset($oCoursePart)){
				$instance->setCoursePartIndex($oCoursePart->courseIndex);
			} else{
				$instance->setCoursePartIndex(0);
			}

	    	return $instance;
	    }

	    return false;
    }


	/**
	 * Saves the course.
	 *
	 * @return boolean | TRUE if successfully saved - FALSE if something went wrong
	 */
	public function save()
	{
		$blVarSet = $this->_areVarsSetForDB();

		if ($blVarSet) {
			$blSaveCheck = $this->_saveToDB();
			if(!$blSaveCheck){
				return false;
			}

			return true;

		} else{
			return false;
		}
	}


	/**
	 * Saves the course to the database.
	 *
	 * @return boolean | TRUE if successfully saved to DB - FALSE if something went wrong
	 */
	protected function _saveToDB()
	{
		global $wpdb;

		$iCPID = $this->getCoursePartID();
		$sTitle = $this->getTitle();
		$sContent = $this->getContent();
		$sType = $this->getType();
		$iPIndex = $this->getIndex();

		if ($sType == "question"){
			if(is_array($sContent)){
				$sContent = CmPart::parse_quest($sContent);
			}
		}

		if(!isset($this->_iPartID)){

			$sSQL = "INSERT INTO %s(coursePartID,title,content,type,partIndex)
		    VALUES(%d,%s,%s,%s,%d)";
			$sQuery = $wpdb->prepare($sSQL,$this->_getDbTableName(),$iCPID,$sTitle,$sContent,$sType,$iPIndex);
		} else{

			$sSQL = "UPDATE %s
		   	SET coursePartID = %d, title = %s, content = %s, type = %s, partIndex = %d
		    WHERE ID = %d";

			$sQuery = $wpdb->prepare($sSQL,$this->_getDbTableName(),$iCPID,$sTitle,$sContent,$sType,$iPIndex, $this->_iPartID);
		}



		if ($wpdb->query($sQuery) !== false) {
			return true;
		} else{
			return false;
		}
	}


	/**
	 * Delete this CmCoursePart.
	 *
	 * @return boolean - True if successfully deleted
	 */
	public function deletePart(){
		global $wpdb;

		$sSQL = "DELETE FROM ".$this->_getDbTableName()." WHERE ID = %d";

		$sQuery = $wpdb->prepare($sSQL,$this->getPartID());

		if ($wpdb->query($sQuery) !== false) {
			return true;
		} else{
			return false;
		}
	}


	/**
	 * Checks all the varibles required to save to DB.
	 *
	 * @return boolean | TRUE if all varibles are set - FALSE if not
	 */
	protected function _areVarsSetForDB()
	{
		$blVarSet = false;

		if ((isset($this->_iCoursePartID) && $this->_iCoursePartID > 0)
			&& (isset($this->_sContent) && strlen($this->_sContent) >= 0)
			&& (isset($this->_sType) && strlen($this->_sType) >= 0)
			&& (isset($this->_iPartIndex) && $this->_iPartIndex >= 0))
		{
			$blVarSet = true;
		}

		return $blVarSet;
	}


	/**
	 * Parses the content passed to fit the "question" type, by either taking a string and convert it to an array for easy handling
	 * or taking an array and returning a string fit for DB storage
	 *
	 * @param string || Array $content
	 *
	 * @return array if $toDB is not set - string if $toDB is set
	 *
	 */
    public static function parse_quest($content){
		if (is_array($content)) {
			$sToDatabase = "";

			foreach ($content as $que) {
				$sToDatabase .= "{" . $que . "},";
			}

			return $sToDatabase;

		} else {
			$sReg = '|\{(.*)\},|U';

			preg_match_all($sReg, $content, $aQuestsRaw, PREG_SET_ORDER);

			$aQuests = [];

			foreach ($aQuestsRaw as $aQuestRaw) {
				array_push($aQuests, $aQuestRaw[1]);
			}

			return $aQuests;
		}
	}


	/**
	 * Prints the content of the CmPart in relation to what the CmPart type is
	 *
	 * @param $sNamePrefix - The prefix used on a html elements' name attr
	 * @return Null
	 */
    public function print_content($sNamePrefix){
		if($this->getType() == "text"){
			$sEditorId = "cm".substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 5)), 0, 8);

			$aEditorSettings = array(
				'drag_drop_upload' => false,
				'media_buttons' => false,
				'default_editor' => 'quicktags',
				'textarea_name' => $sNamePrefix."_content_simple"
			);
			wp_editor($this->getContent(), $sEditorId, $aEditorSettings);
		} elseif ($this->getType() == "video"){
			?>
				<label class="cm_part_content_label"
					   for="<?php echo $sNamePrefix."_content_simple"; ?>">
					<?php echo TXT_CM_EDIT_PART_CONTENT_VIDEO.": "; ?>
				</label>
				<input id="cm_part_content_video" class="cm_part_content_input" name="<?php echo $sNamePrefix."_content_simple"; ?>"
					   type="text" value="<?php echo $this->getContent(); ?>" />
			<?php
		} elseif ($this->getType() == "image"){
			?>
			<label class="cm_part_content_label"
				   for="<?php echo $sNamePrefix."_content_simple"; ?>">
				<?php echo TXT_CM_EDIT_PART_CONTENT_IMAGE.": "; ?>
			</label>
			<div class='image-preview-wrapper cm_part_content_input'>
				<img id='image-preview' src='<?php echo wp_get_attachment_url( $this->getContent() ); ?>' height='100'>
			</div>
			<input id="upload_image_button" type="button" class="cm_part_content_input button" value="<?php echo TXT_CM_STORE_ADD_IMAGE; ?>" />
			<input type='hidden' name='<?php echo $sNamePrefix."_content_simple"; ?>' id='image_attachment_id' value='<?php echo $this->getContent(); ?>'>

			<?php
		} elseif ($this->getType() == "download"){
			?>
			<label class="cm_part_content_label"
				   for="<?php echo $sNamePrefix."_content_simple"; ?>">
				<?php echo TXT_CM_EDIT_PART_CONTENT_DOWNLOAD.": "; ?>
			</label>
			<input id="cm_part_content_download" class="cm_part_content_input" name="<?php echo $sNamePrefix."_content_simple"; ?>"
				   type="text" value="<?php echo $this->getContent(); ?>" />
			<?php
		} elseif ($this->getType() == "question"){
			?>
			<button id='cmP_quest_btn' class='button-secondary' type='button'>
				<?php echo TXT_CM_EDIT_ADD_NEW_QUESTION; ?>
			</button>
			<ul class="cm_part_content_questions">
			<?php

			$aQuestions = CmPart::parse_quest($this->getContent());

			foreach($aQuestions as $iKey => $sQue){

				?>
				<li class="cm_part_content_question_container">
				<label class="cm_part_content_label" for = "<?php echo $sNamePrefix."_content_quest_".$iKey; ?>">
					<?php echo TXT_CM_EDIT_PART_CONTENT_QUESTION." ".($iKey + 1).":"; ?>
				</label>
				<input id = "cm_part_content_quest" class="cm_part_content_input" name = "<?php echo $sNamePrefix."_content_quest_".$iKey; ?>"
				   type = "text" value = "<?php echo $sQue; ?>" />
				<img class='cm_part_content_quest_del' src="<?php echo CM_URLPATH."gfx/cm_delete_quest.png"; ?>"
					 onmouseover="this.src='<?php echo CM_URLPATH."gfx/cm_delete_quest_hover.png"; ?>'"
					 onmouseout="this.src='<?php echo CM_URLPATH."gfx/cm_delete_quest.png"; ?>'" />
				</li>
				<?php
			}
			?>
			</ul>
			<?php
		}
	}


    /**
     * Prints the <li> element representing this Part | For tpl/courseForm.php
     */
    public function printListItemRep()
    {
		$sNamePrefix = "cm_P_" . $this->getCoursePartIndex() . "_" . $this->getIndex();
    	?>
    	<li id='<?php echo "cm_part_".$this->getIndex(); ?>' class='cm_part'>
			<!-- HIDDEN INPUT -->
			<!-- Hidden input for JS | Used by JS to keep track of changes-->
			<input type="hidden" name="cm_P_ID" value="<?php echo $this->getPartID() ?>">
			<input type="hidden" name="cm_P_index" value="<?php echo $this->getIndex(); ?>">
			<input type="hidden" name="cm_P_type" value="<?php echo $this->getType(); ?>">
			<!-- Hidden input for POST | Is modified by the JS to save the changes in POST -->
			<input type="hidden" name="<?php echo $sNamePrefix."_ID"; ?>"
				   value="<?php echo $this->getPartID() ?>">
			<input type="hidden" name="<?php echo $sNamePrefix."_del"; ?>" value="0">
			<!-- END OF HIDDEN INPUT -->
			<div class="cm_part_header">
				<label class='cm_part_name'><?php echo $this->getTitle() ?></label>
				<img class='cm_part_del' src="<?php echo CM_URLPATH."gfx/cm_delete.png"; ?>"
					 onmouseover="this.src='<?php echo CM_URLPATH."gfx/cm_delete_hover.png"; ?>'"
					 onmouseout="this.src='<?php echo CM_URLPATH."gfx/cm_delete.png"; ?>'" />
				<label id="cm_part_type" class='cm_part_name'><?php echo TXT_CM_EDIT_TYPE.": ".$this->getType() ?></label>
			</div>
			<ul class='cm_part_collapsed' style="display: none">
				<li>
					<label class="cm_part_name"
						   for="<?php echo $sNamePrefix."_name"; ?>">
						<?php echo TXT_CM_EDIT_PART_TITLE.": "; ?>
					</label>
					<input class="cm_P_name regular-text" name="<?php echo $sNamePrefix."_name"; ?>"
						   type="text" value="<?php echo $this->getTitle(); ?>" />
				</li>
				<li>
					<label class="cm_part_index"
						   for="<?php echo $sNamePrefix."_index"; ?>">
						<?php echo TXT_CM_EDIT_PART_INDEX.": "; ?>
					</label>
					<input class="cm_P_index" name="<?php echo $sNamePrefix."_index"; ?>" type="number"
						   min="1" max="<?php echo ($this->getCoursePartID() != -1 ? $this->getParentCoursePart()->getNrParts(): 20); ?>"
						   value="<?php echo ($this->getIndex() + 1); ?>">
				</li>
				<li>
					<label class = "cm_part_content_label" for = "<?php echo $sNamePrefix."_type"; ?>">
						<?php echo TXT_CM_EDIT_TYPE.": "; ?>
					</label>
					<select class = "cm_part_type_select" name = "<?php echo $sNamePrefix."_type"; ?>">
						<?php
							$types = CmPart::getTypes();

							foreach ($types as $type){
								?>
								<option class = "cm_part_type_option" value = "<?php echo $type; ?>"
									<?php echo ($type == $this->getType() ? "selected" : ""); ?>
								>
									<?php echo $type; ?>
								</option>
								<?php
							}
						?>
					</select>
				</li>
				<li class="cm_part_content_container">
					<?php
						$this->print_content($sNamePrefix);
					?>
				</li>
			</ul>
    	</li>
    	<?php
    }


	/**
	 *
	 */
	public function toJSON() {
		$aJSONPart = array(
			"ID"          => $this->getPartID(),
			"title"        => $this->getTitle(),
			"content" => $this->getContent(),
			"type"       => $this->getType(),
			"index"        => $this->getIndex(),
		);

		return json_encode($aJSONPart);
	}


    /**
     * toString
     */
    public function __toString()
    {
    	$sStringToRet = "";

    	$sStringToRet .= "\"".$this->_sPartTitle."\" - Type:(".$this->_sType.")";

    	return $sStringToRet;
    }


	/**
     * Function Description.
     *
     * @param datatype $value
     *
     * @return datatype
     */
    public function FuncTemp($value='')
    {
    	# code...
    }
}



?>