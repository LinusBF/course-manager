<?php
/**
 * CmCoursePart.class.php
 * 
 * The CmCoursePart class file.
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
 * The Course Part class.
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
class CmCoursePart
{
	protected $_iCourseIndex = null;
	protected $_iCoursePartID = null;
	protected $_aCmParts = array();
	protected $_iCourseID = null;
	protected $_sCoursePartName = null;
	protected $_iNrOfParts = null;

	
	/**
	*  
	* Constructor
	* 
	**/
	protected function __construct()
	{

		do_action('cm_coursePart_init', $this);
	}


	/**
     * Constructor empty.
     *
     * @return CmCoursePart instance
     */
    public static function create()
    {
    	$instance = new self();
    	return $instance;
    }


	/**
	 * Constructor with params
	 *
	 * @param $iIndex
	 * @param $iCourseID
	 * @param $sCoursePartName
	 *
	 * @return CmCoursePart instance
	 */
	public static function createWParams($iIndex, $iCourseID, $sCoursePartName)
	{
		$instance = new self();
		$instance->setCourseIndex($iIndex);
		$instance->setCourseID($iCourseID);
		$instance->setCoursePartName($sCoursePartName);
		return $instance;
	}


	/**
     * Constructor with CmParts and a Course index.
     *
     * @param array - $aParts - array of CmParts | int - $iIndex - This index in a Course
     *
     * @return CmCoursePart instance
     */
    public static function createWPartsAndIndex($aParts, $iIndex)
    {
    	$instance = new self();
    	$instance->setParts($aParts);
    	$instance->setCourseIndex($iIndex);
    	return $instance;
    }


    /**
     * Returns the number of Parts that this CoursePart have.
     *
     * @return int
     */
    public function getNrParts()
    {
    	if (!isset($this->_iNrOfParts)) {
    		$this->_iNrOfParts = count($this->_aCmParts);
    	}

    	return (int) $this->_iNrOfParts;
    }


    /**
     * Set the number of Parts this CoursePart should have.
     *
     * @param int $iNrOfCP | The number of Parts that the CoursePart will have
     *
     * @return null
     */
    public function setNrParts($iNrOfP)
    {
    	$this->_iNrOfParts = (int) $iNrOfP;
    }


    /**
     * Returns the Parts that this CoursePart have, ordered by index.
     *
     * @return CmPart[]
     */
    public function getParts()
    {
    	$aParts = $this->_aCmParts;
		usort($aParts, function ($a, $b){
			if ($a->getIndex() == $b->getIndex()){
				return 0;
			}
			return ($a->getIndex() < $b->getIndex()) ? -1 : 1;
		});

		return $aParts;

    }


    /**
     * Set the number of Parts this CoursePart should have.
     *
     * @param array $aCmParts | The Parts that the CoursePart will have
     *
     * @return null
     */
    public function setParts($aCmParts)
    {
    	if(is_array($aCmParts)) {
			$this->_aCmParts = $aCmParts;
			$this->setNrParts(count($aCmParts));
		}
    }


	/**
	 * Returns the CourseParts CourseID.
	 *
	 * @return int - '-1' if index does not exist
	 */
	public function getCourseID()
	{
		if (isset($this->_iCourseID)) {
			return (int) $this->_iCourseID;
		}

		return -1;
	}


	/**
	 * Sets the CourseParts CourseID.
	 *
	 * @param int $iCourseID
	 * @return Null
	 */
	public function setCourseID($iCourseID)
	{
		$this->_iCourseID = (int) $iCourseID;
	}


	/**
     * Returns the CourseIndex.
     *
     * @return int - '-1' if index does not exist
     */
    public function getCourseIndex()
    {
    	if (isset($this->_iCourseIndex)) {
    		return (int) $this->_iCourseIndex;
    	}

    	return -1;
    }


	/**
	 * Sets the CourseIndex.
	 *
	 * @param int $index
	 * @return Null
	 */
	public function setCourseIndex($index)
	{
		$this->_iCourseIndex = (int) $index;
	}


    /**
     * Get this CmCourseParts ID.
     *
     * @return int
     */
    public function getCoursePartID()
    {
    	if (!isset($this->_iCoursePartID)) {
    		return -1;
    	}

    	return (int) $this->_iCoursePartID;
    }


    /**
     * Returns the CoursePart name.
     *
     * @return string
     */
    public function getCoursePartName()
    {

    	return stripslashes(htmlspecialchars($this->_sCoursePartName, ENT_QUOTES, 'UTF-8'));
    }


    /**
     * Sets the CoursePart name.
     *
     * @param string $sName | The name of the course
	 *
	 * @return Null
     */
    public function setCoursePartName($sName)
    {
    	$this->_sCoursePartName = $sName;

    }


    /**
     * Sets the CoursePartID to null so it can be saved with a new ID.
     *
     * @return null
     */
    public function resetCoursePartID()
    {
    	$this->_iCoursePartID = null;
    }


    /**
     * Returns the name of the DB table for courseparts.
     *
     * @return string
     */
    protected function _getDbTableName()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_course_parts';
    }


    /**
     * Returns the name of the DB table for parts.
     *
     * @return string
     */
    protected function _getDbTableNamePart()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_parts';
    }


	/**
	 * Returns the name of the DB table for courses.
	 *
	 * @return string
	 */
	protected function _getDbTableNameCourse()
	{
		global $wpdb;
		return $wpdb->prefix.'cm_courses';
	}


    /**
     * Saves the course.
     *
     * @param boolean $blCheckRecursive | Should save() save it's Parts too?
     *
     * @return boolean | TRUE if successfully saved - FALSE if something went wrong
     */
    public function save($blCheckRecursive = false)
    {
    	//TODO - Add check for PostID and save that separately maybe?

    	$blVarSet = $this->_areVarsSetForDB();

    	if ($blVarSet) {
    		$blSaveCheck = $this->_saveToDB();
    		if(!$blSaveCheck['result']){
    			return false;
    		}

    		if ($blCheckRecursive) {
    			$aSavedParts = array();

				if ($this->getNrParts() > 0) {
					#Correcting index for Parts
					$iCurrentPartIndex = 0;
					foreach ($this->getParts() as $oPart) {
						$oPart->setIndex($iCurrentPartIndex);
						if ($oPart->save()) {
							array_push($aSavedParts, $oPart->getPartID());
						}
						$iCurrentPartIndex++;
					}

					if (count($aSavedParts) < $this->getNrParts()) {
						return false;
					}
				}
    		}

		    return (isset($blSaveCheck['insertId']) ? $blSaveCheck['insertId'] : $blSaveCheck['result']);

    	} else{
			echo "Vars are not set for DB!";
    		return false;
    	}
    }


    /**
     * Saves the course to the database.
     *
     * @return array | [result] - TRUE if successfully saved to DB - FALSE if something went wrong | [reason] string with error msg
     */
    protected function _saveToDB()
    {
    	global $wpdb;

    	$sName = $this->_sCoursePartName;
    	$icID = $this->_iCourseID;
		$iCIndex = $this->_iCourseIndex;

	    if(!isset($this->_iCoursePartID)){

		    $sSQL = "INSERT INTO ".$this->_getDbTableName()."(courseID,name,courseIndex)
		    VALUES(%d,%s,%d)";

		    $sQuery = $wpdb->prepare($sSQL,$icID,$sName,$iCIndex);
	    } else{

		  	$sSQL = "UPDATE ".$this->_getDbTableName()."
		   	SET courseID = %d, name = %s, courseIndex = %d
		    WHERE ID = %d";

		    $sQuery = $wpdb->prepare($sSQL,$icID,$sName,$iCIndex,$this->getCoursePartID());
	    }



	    if ($wpdb->query($sQuery) !== false) {
	    	if(!isset($this->_iCoursePartID)){
			    $iInsertId = $wpdb->insert_id;
			} else{
				return array('result' => true);
			}

		    if(isset($iInsertId)){
			    return array('result' => true, 'insertId' => $iInsertId);
		    } else{
			    return array('result' => true);
		    }
	    } else{
	    	return array('result' => false);
	    }
    }


    /**
     * Return the object representing the CoursePart in the db with the same ID.
     *
     * @param int $iID
     *
     * @return CmCoursePart
     */
    public static function getCoursePartByID($iID,$blGetParts = false)
    {
    	$instance = new self();
    	global $wpdb;

    	$sSQL = "SELECT courseID,name,courseIndex FROM ".$instance->_getDbTableName()." WHERE ID = %d";

    	$oCoursePart = $wpdb->get_row($wpdb->prepare($sSQL,$iID));

    	if (isset($oCoursePart)) {

	    	$instance->setCoursePartName($oCoursePart->name);
	    	$instance->setCourseID(intval($oCoursePart->courseID));
	    	$instance->setCourseIndex(intval($oCoursePart->courseIndex));
	    	$instance->_iCoursePartID = intval($iID);

	    	if ($blGetParts) {
	    		$sSQLParts = "SELECT ID FROM ".$instance->_getDbTableNamePart()." 
	    		WHERE coursePartID = %d";

	    		$aPartIDs = $wpdb->get_col($wpdb->prepare($sSQLParts,$instance->_iCoursePartID));

	    		$aParts = array();

	    		foreach ($aPartIDs as $iP_ID) {
	    			array_push($aParts, CmPart::getPartByID($iP_ID));
	    		}

	    		$instance->setParts($aParts);
	    	}

	    	return $instance;

	    } else {
			return false;
		}
    }


	/**
	 * Delete this CmCoursePart.
	 *
	 * @return boolean - True if successfully deleted
	 */
    public function deleteCoursePart(){
    	global $wpdb;

		$sSQL = "DELETE FROM ".$this->_getDbTableName()." WHERE ID = %d";

		$sQuery = $wpdb->prepare($sSQL,$this->getCoursePartID());

		if ($wpdb->query($sQuery) !== false) {
			return true;
		} else{
			return false;
		}
	}


	/**
	 * Returns the parent CmCourse to this CmCoursePart.
	 *
	 * @return CmCourse
	 */
    public function getParentCourse(){
    	$oParentCourse = CmCourse::getCourseByID($this->getCourseID(), true);

		return $oParentCourse;
	}


    /**
     * Checks all the varibles required to save to DB.
     *
     * @return boolean | TRUE if all varibles are set - FALSE if not
     */
    protected function _areVarsSetForDB()
    {
    	$blVarSet = false;

    	if ((isset($this->_sCoursePartName) && strlen($this->_sCoursePartName) > 0)
    		&& (isset($this->_iCourseID) && $this->_iCourseID > 0)
    		&& (isset($this->_iCourseIndex) && $this->_iCourseIndex >= 0))
    	{
    		$blVarSet = true;
    	}

    	return $blVarSet;
    }


    /**
     * Returns the CmPart object at the index passed.
     *
     * @param int $iIndex
     *
     * @return CmPart
     */
    protected function _getPartByIndex($iIndex)
    {
    	foreach ($this->_aCmParts as $part) {
    		if ($part->getIndex() == $iIndex) {
    			return $part;
    		}
    	}

    	return false;
    }


	/**
	 * Prints the <select> element for selecting the CoursePart index
	 *
	 * @return Null
	 */
	/* DEPRECATED
    public function printIndexOptions(){
		$oParentCourse = $this->getParentCourse();
		$iNrOfCPs = $oParentCourse->getNrCourseParts();

		if($this->getCourseIndex() >= $iNrOfCPs){
			$iNrOfCPs = $this->getCourseIndex();
		}
	}
	*/


    /**
     * Prints the <li> element representing this CoursePart | For tpl/courseForm.php
     */
    public function printListItemRep()
    {
		$sNamePrefix = "cm_CP_".$this->getCourseIndex();

    	?>
    	<li id='<?php echo "cm_coursePart_".$this->getCourseIndex(); ?>' class='cm_coursePart'>
			<!-- HIDDEN INPUT -->
			<!-- Hidden input for JS | Used by JS to keep track of changes-->
			<input type='hidden' name='cm_CP_ID' value='<?php echo $this->getCoursePartID(); ?>'>
			<input type='hidden' name='cm_CP_index' value='<?php echo $this->getCourseIndex(); ?>'>
			<input type="hidden" name="cm_CP_nr_of_parts" value="<?php echo $this->getNrParts(); ?>">
			<!-- Hidden input for POST | Is modified by the JS to save the changes in POST -->
			<input type='hidden' name='<?php echo $sNamePrefix; ?>_ID' value='<?php echo $this->getCoursePartID(); ?>'>
			<input type="hidden" name="<?php echo $sNamePrefix; ?>_nr_of_parts" value="<?php echo $this->getNrParts(); ?>">
			<input type="hidden" name="<?php echo $sNamePrefix."_del"; ?>" value="0">
			<!-- END OF HIDDEN INPUT -->
			<div class="cm_coursePart_header">
				<p class='cm_coursePart_name'><?php echo $this->getCoursePartName() ?></p>
				<img class='cm_coursePart_del' src="<?php echo CM_URLPATH."gfx/cm_delete.png"; ?>"
				onmouseover="this.src='<?php echo CM_URLPATH."gfx/cm_delete_hover.png"; ?>'"
				onmouseout="this.src='<?php echo CM_URLPATH."gfx/cm_delete.png"; ?>'" />
			</div>
			<ul class='cm_coursePart_collapsed' style="display: none">
				<li>
					<label class="cm_coursePart_name"
					for="<?php echo $sNamePrefix."_name"; ?>">
						<?php echo TXT_CM_EDIT_PART_NAME.": "; ?>
					</label>
					<input class="cm_CP_name regular-text" name="<?php echo $sNamePrefix."_name"; ?>"
					type="text" value="<?php echo $this->getCoursePartName(); ?>">
				</li>
				<li>
					<label class="cm_coursePart_index"
						   for="<?php echo $sNamePrefix."_index"; ?>">
						<?php echo TXT_CM_EDIT_PART_INDEX.": "; ?>
					</label>
					<input class="cm_CP_index" name="<?php echo $sNamePrefix."_index"; ?>" type="number"
						   min="1" max="<?php echo $this->getParentCourse()->getNrCourseParts(); ?>"
						   value="<?php echo ($this->getCourseIndex() + 1); ?>">
				</li>
				<li>
					<label>
						<?php echo TXT_CM_EDIT_PARTS.": "; ?>
					</label>
					<button id='cmCP_btn' class='button-secondary' type='button'
							data-nonce = "<?php echo wp_create_nonce("cm_add_new_part"); ?>">
						<?php echo TXT_CM_ADD_NEW_PART; ?>
					</button>
				</li>
				<?php
					for ($i=0; $i < $this->getNrParts(); $i++) {
						$oCmPart = $this->_getPartByIndex($i);
						if (isset($oCmPart) && $oCmPart !== false){
							$oCmPart->printListItemRep();
						}
					}
				?>
			</ul>
    	</li>
    	<?php
    }


    /**
     * for toString
     */
    protected function _partsToString()
    {
    	$sStringToRet = "&emsp;Parts:<br>";

    	for ($i=0; $i < $this->getNrParts(); $i++) { 
    		$sStringToRet .= "&emsp;".strval($this->_getPartByIndex($i))."<br>";
    	}
    		
    	return $sStringToRet;
    }


	/**
	 *
	 */
	public function toJSON() {
		$aJSONCoursePart = array(
			"ID"          => $this->getCoursePartID(),
			"name"        => $this->getCoursePartName(),
			"index"       => $this->getCourseIndex(),
		);

		$aParts = array();
		foreach ($this->getParts() as $oPart){
			array_push($aParts, json_decode($oPart->toJSON()));
		}
		$aJSONCoursePart["parts"] = $aParts;

		return json_encode($aJSONCoursePart);
	}


    /**
     * toString
     */
    public function __toString()
    {
    	$sStringToRet = "";

    	$sStringToRet .= $this->_sCoursePartName."<br>";
    	$sStringToRet .= $this->_partsToString();

    	return $sStringToRet;
    }


    /**
     * Function Description.
     *
     * @param datatype $value
     *
     * @return datatype
     */
    public function FuncTemp2($value='')
    {
    	# code...
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