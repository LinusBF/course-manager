<?php
/**
 * CmCourse.class.php
 * 
 * The CmCourse class file.
 * 
 * PHP versions 7
 * 
 * @category  CourseManager
 * @package   CourseManager
 * @author    Linus Bein Fahlander <linus.webdevelopment@gmail.com>
 * @copyright 2016-2016 Linus Bein Fahlander
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $Id$
 * @link      Coming soon
 */

/**
 * The Course class.
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
class CmCourse
{
	protected $_sCourseName = null;
	protected $_sCourseDescription = null;
	protected $_iCoursePrice = null;
	protected $_iCourseSpan = null; //In days
	protected $_blCourseActive = false;
	protected $_iCourseID = null;
	protected $_aCourseParts = array();
	protected $_iNrOfCourseParts = null;
	protected $_aUsedCourseNames = array();
	protected $_aCourseTags = array();


	/**
	*  
	* Constructor
	* 
	**/
	protected function __construct()
	{

		do_action('cm_course_init', $this);
	}


	/**
     * Constructor empty.
     *
     * @return CmCourse instance
     */
    public static function create()
    {
    	$instance = new self();
    	return $instance;
    }


	/**
     * Constructor with CourseParts.
     *
     * @param CmCoursePart[] $aCourseParts
     *
     * @return CmCourse instance
     */
    public static function createWCourseParts($aCourseParts)
    {
    	$instance = new self();
    	$instance->_aCourseParts = $aCourseParts;
    	return $instance;
    }


	/**
	 * Constructor with all parameters to make a new Course in the db.
	 *
	 * @param $sName
	 * @param $sDesc
	 * @param $iPrice
	 * @param $blActive
	 * @param $iSpan
	 * @param CmCoursePart[] $aCourseParts
	 *
	 * @return bool|CmCourse instance
	 */
    public static function createCompleteCourse($sName, $sDesc, $iPrice, $blActive, $iSpan, $aCourseParts)
    {
    	$instance = new self();
    	if (!$instance->checkCourseName($sName)) {
    		return false;
    	}
    	$instance->setCourseName($sName);
		$instance->setCourseDescription($sDesc);
		$instance->setCoursePrice($iPrice);
    	$instance->setCourseStatus($blActive);
    	$instance->setCourseSpan($iSpan);
    	$instance->setCourseParts($aCourseParts);
    	$instance->setNrCourseParts(count($aCourseParts));
    	return $instance;
    }


    /**
     * Gets all the used Course names
     *
     * @return string[]
     */
    public function getUsedNames()
    {
    	global $wpdb;

    	$sSQL = "SELECT name FROM ".$this->_getDbTableName();

    	$aResults = $wpdb->get_col($sSQL);

    	return $aResults;
    }


	/**
	 * Returns the description of the Course
	 *
	 * @return int
	 */
	public function getCourseDescription()
	{
		return htmlspecialchars($this->_sCourseDescription, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Sets the description of the Course
	 *
	 * @param string $sDesc
	 *
	 * @return null
	 */
	public function setCourseDescription($sDesc)
	{
		$this->_sCourseDescription = htmlspecialchars($sDesc, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Returns the price of the Course
	 *
	 * @return int
	 */
	public function getCoursePrice()
	{
		return $this->_iCoursePrice;
	}


	/**
	 * Sets the price of the Course
	 *
	 * @param int $iPrice
	 *
	 * @return null
	 */
	public function setCoursePrice($iPrice)
	{
		$this->_iCoursePrice = intval($iPrice);
	}


    /**
     * Returns the span of the Course
     *
     * @return int
     */
    public function getCourseSpan()
    {
    	return $this->_iCourseSpan;
    }


    /**
     * Sets the span of the Course
     *
     * @param int $iDays
     *
     * @return null
     */
    public function setCourseSpan($iDays)
    {
    	$this->_iCourseSpan = $iDays;
    }


	/**
     * Returns the number of CourseParts that this Course have.
     *
     * @return int
     */
    public function getNrCourseParts()
    {
    	return count($this->getCourseParts());
    }


    /**
     * Set the number of CourseParts this Course should have.
     *
     * @param int $iNrOfCP | The number of Course parts that the course will have
     *
     * @return null
     */
    public function setNrCourseParts($iNrOfCP)
    {
    	$this->_iNrOfCourseParts = $iNrOfCP;
    }


    /**
     * Returns the CourseParts that this Course have.
     *
     * @return CmCoursePart[]
     */
    public function getCourseParts()
    {
		$aCourseParts = $this->_aCourseParts;

		usort($aCourseParts, function ($a, $b){
			if ($a->getCourseIndex() == $b->getCourseIndex()){
				return 0;
			}
			return ($a->getCourseIndex() < $b->getCourseIndex()) ? -1 : 1;
		});

		return $aCourseParts;
    }


    /**
     * Set the CourseParts that this Course have.
     *
     * @param CmCoursePart[] $aCParts | The Course parts that the course will have
     *
     * @return null
     */
    public function setCourseParts($aCParts)
    {
    	$this->_aCourseParts = $aCParts;
		$this->setNrCourseParts(count($aCParts));
    }


    /**
     * Returns the Course name.
     *
     * @return string
     */
    public function getCourseName()
    {
    	return htmlspecialchars($this->_sCourseName, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns the Course name sanitized.
     *
     * @return string
     */
    public function getCourseURLName()
    {
    	return sanitize_title(htmlspecialchars($this->_sCourseName, ENT_QUOTES, 'UTF-8'));
    }


    /**
     * Sets the Course name.
     *
     * @param string $sName | The name of the course
     *
     * @return boolean | TRUE if the name isn't used - FALSE if the name is in use
     */
    public function setCourseName($sName)
    {
    	$iID = null;

    	if (isset($this->_iCourseID)) {
    		$iID = $this->_iCourseID;
    	}

    	if($this->checkCourseName($sName,$iID)){
    		$this->_sCourseName = htmlspecialchars($sName, ENT_QUOTES, 'UTF-8');
    		return true;
    	} else{
    		return false;
    	}
    }


    /**
     * Returns the Course Tags.
     *
     * @return array
     */
    public function getCourseTags()
    {
    	return $this->_aCourseTags;
    }


    /**
     * Returns the Course Tags on a readable string.
     *
     * @return string
     */
    public function getCourseTagList()
    {
    	$sTags = "";

    	foreach ($this->_aCourseTags as $key => $sTagName) {
    		if($key == count($this->_aCourseTags) - 1){
    			$sTags .= "\"".$sTagName."\"";
    		} else{
    			$sTags .= "\"".$sTagName."\", ";
    		}
    	}

    	return $sTags;
    }


    /**
     * Returns the ID of the course
     *
     * @return int
     */
    public function getCourseID()
    {
    	return $this->_iCourseID;
    }


    /**
     * Returns the status of the course
     *
     * @return boolean
     */
    public function getCourseStatus()
    {
    	return $this->_blCourseActive;
    }


    /**
     * Returns the status of the course
     *
     * @param boolean $blStatus
     *
     * @return null
     */
    public function setCourseStatus($blStatus)
    {
    	$this->_blCourseActive = $blStatus;
    }


    /**
     * Sets the CourseID to null so it can be saved with a new ID.
     *
     * @return null
     */
    public function resetCourseID()
    {
    	$this->_iCourseID = null;
    }


    /**
     * Returns the name of the DB table for courses.
     *
     * @return string
     */
    protected function _getDbTableName()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_courses';
    }


    /**
     * Returns the name of the DB table for courses.
     *
     * @return string
     */
    protected function _getDbTableNameCoursePart()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_course_parts';
    }


    /**
     * Returns the name of the Tags table for courses.
     *
     * @return string
     */
    protected function _getDbTableNameTags()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_tags';
    }


    /**
     * Returns the name of the rel_tag_course table for courses.
     *
     * @return string
     */
    protected function _getDbTableNameTagRel()
    {
    	global $wpdb;
    	return $wpdb->prefix.'cm_rel_tag_course';
    }


	/**
     * Saves the course.
     *
     * @param boolean $blCheckRecursive | Should save() save it's CourseParts too?
     *
     * @return boolean | TRUE if successfully saved - FALSE if something went wrong
     */
    public function save($blCheckRecursive = false)
    {
    	$blVarSet = $this->_areVarsSetForDB();

    	if ($blVarSet) {
    		$blSaveCheck = $this->_saveToDB();

    		if(!$blSaveCheck['result']){
    			return false;
    		}

    		if ($blCheckRecursive) {
    			if (count($this->getCourseParts()) > 0) {

					$aSavedCmCourseParts = array();
					#Correcting index for Parts
					$iCurrentCoursePartIndex = 0;
					foreach ($this->getCourseParts() as $oCoursePart) {
						$oCoursePart->setCourseIndex($iCurrentCoursePartIndex);
						if ($oCoursePart->save(true)) {
							array_push($aSavedCmCourseParts, $oCoursePart->getCoursePartID());
						}
						$iCurrentCoursePartIndex++;
					}

					if (count($aSavedCmCourseParts) < $this->getNrCourseParts()) {
						return false;
					}

				}

				return true;

    		} else{
    			return (isset($blSaveCheck['insertId']) ? $blSaveCheck['insertId'] : true);
			}

    	} else{
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

    	$sName = $this->_sCourseName;
    	if ($this->checkCourseName($sName) || isset($this->_iCourseID)) {
    	
		    $iActive = 0;
		    if ($this->_blCourseActive) {
		    	$iActive = 1;
		    }
		    $sDesc = $this->_sCourseDescription;
			$iPrice = $this->_iCoursePrice;
		    $iSpan = $this->_iCourseSpan;

	    	if(!isset($this->_iCourseID)){

		    	$sSQL = "INSERT INTO ".$this->_getDbTableName()."(name,description,price,active,span)
		    	VALUES(%s,%s,%d,%d,%d)";

	    	} else{

	    		if($this->checkCourseName($sName,$this->_iCourseID)){

		    		$sSQL = "UPDATE ".$this->_getDbTableName()."
		    		SET name = %s, description = %s, price = %d, active = %d, span = %d
			    	WHERE ID = ".$this->_iCourseID;

			    } else{
			    	return array('result' => false, 'reason' => TXT_CM_ERROR_NAME_CHECK);
			    }
	    	}

		    $sQuery = $wpdb->prepare($sSQL,$sName,$sDesc,$iPrice,$iActive,$iSpan);

		    if ($wpdb->query($sQuery) !== false) {

				if(!isset($this->_iCourseID)){
					$iInsertId = $wpdb->insert_id;

				}

		    	if (isset($this->_aCourseTags)) {
		    		$this->_saveTagRelToDb();
		    	}

				if(isset($iInsertId)){
					return array('result' => true, 'insertId' => $iInsertId);
				} else{
					return array('result' => true);
				}

		    } else{
		    	return array('result' => false, 'reason' => TXT_CM_ERROR_DB_QUERY);
		    }
		} else{
			return array('result' => false, 'reason' => TXT_CM_ERROR_NAME_CHECK);
		}
    }


    /**
     * Saves a tag to the database.
     *
     * @param string $sName
     *
     * @return int ID of the inserted tag
     */
    public static function saveTagToDB($sName)
    {
    	global $wpdb;
    	$instance = new self();

    	$sSQL = "INSERT INTO ".$instance->_getDbTableNameTags()."(name) 
    	VALUES(%s)";

    	$wpdb->query($wpdb->prepare($sSQL,$sName));

    	return $wpdb->insert_id;
    }


    /**
     * Saves the courses relations to tags to the database.
     *
     * @return null
     */
    protected function _saveTagRelToDb()
    {
    	if (isset($this->_aCourseTags) && isset($this->_iCourseID)) {

    		global $wpdb;

    		foreach ($this->_aCourseTags as $CourseTag) {
    			$iTagID = $this->_getTagIdByName($CourseTag);

    			if (!isset($iTagID)) {
    				$iTagID = $this->saveTagToDB($CourseTag);
    			}

    			$sSQL = "INSERT INTO ".$this->_getDbTableNameTagRel()."(courseID,tagID) 
    			VALUES(%d,%d)";

    			$wpdb->query($wpdb->prepare($sSQL,$this->_iCourseID,$iTagID));
    		}

    		
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

    	if ((isset($this->_sCourseName) && strlen($this->_sCourseName) > 0)
    		&& (isset($this->_iCourseSpan) && $this->_iCourseSpan != 0)
    		&& isset($this->_blCourseActive) && isset($this->_iCoursePrice))
    	{
    		$blVarSet = true;
    	}

    	return $blVarSet;
    }


    /**
     * Removes a Course from the DB
     *
     * @param int $iID
     */
    public static function deleteCourse($iID)
    {
    	global $wpdb;
    	$instance = new self();

    	$wpdb->delete($instance->_getDbTableName(), array('ID' => $iID), array('%d'));
    }


	/**
	 * Checks if the course can be activated
	 *
	 * @param int $iId
	 *
	 * @return bool
	 */
	public static function checkActivate( $iId ) {
		if (CmCourse::getCourseByID($iId)->getCourseStatus()){
			return true;
		}

		$oStoreHandler = new CmCourseStoreHandler();
		$oPageBuilder = new CmPageBuilder();

		$aPageIds = $oPageBuilder->getCoursePageIds($iId);

		return (bool) $oStoreHandler->getStoreOptionsForCourse($iId)['settings_modified'] && !in_array(0, $aPageIds);
    }


	/**
	 * Changes the status of a Course in the DB
	 *
	 * @param int $iID
	 *
	 * @return int
	 */
    public static function changeStatus($iID)
    {
    	global $wpdb;
    	$oCourse = CmCourse::getCourseByID($iID);

    	if ($oCourse->_blCourseActive) {
    		$iStatus = 0;
    	} else{
    		$iStatus = 1;
    	}

    	$wpdb->update($oCourse->_getDbTableName(), array('active' => $iStatus), array('ID' => $iID), array('%d'), array('%d'));

    	return $iStatus;
    }


	/**
	 * Return the object representing the course in the db with the same ID.
	 *
	 * @param int $iID
	 *
	 * @param bool $blGetCourseParts
	 * @return CmCourse|bool
	 */
    public static function getCourseByID($iID,$blGetCourseParts = false)
    {
    	$instance = new self();
    	global $wpdb;

    	$sSQL = "SELECT name,description,price,active,span FROM ".$instance->_getDbTableName()." WHERE ID = %d";

    	$oCourse = $wpdb->get_row($wpdb->prepare($sSQL,$iID));

    	if (isset($oCourse)) {

	    	$instance->_sCourseName = stripslashes($oCourse->name);
	    	if (intval($oCourse->active) == 1) {
	    		$blActive = true;
	    	} else{
	    		$blActive = false;
	    	}
	    	$instance->setCourseDescription($oCourse->description);
			$instance->setCoursePrice($oCourse->price);
	    	$instance->setCourseStatus($blActive);
	    	$instance->setCourseSpan(intval($oCourse->span));
	    	$instance->_iCourseID = intval($iID);

	    	if ($blGetCourseParts) {
	    		$sSQLCourseParts = "SELECT ID FROM ".$instance->_getDbTableNameCoursePart()." 
	    		WHERE courseID = %d";

	    		$aCoursePartIDs = $wpdb->get_col($wpdb->prepare($sSQLCourseParts,$instance->_iCourseID));

	    		$aCParts = array();

	    		if(isset($aCoursePartIDs)){
		    		foreach ($aCoursePartIDs as $iCP_ID) {
		    			array_push($aCParts, CmCoursePart::getCoursePartByID($iCP_ID,true));
		    		}
		    	}

	    		$instance->setCourseParts($aCParts);
	    	}

	    	$sSQLTags = "SELECT name FROM ".$instance->_getDbTableNameTags()." 
	    	JOIN ".$instance->_getDbTableNameTagRel()." 
	    	ON ID = tagID WHERE courseID = %d";

	    	$aTags = $wpdb->get_col($wpdb->prepare($sSQLTags,$iID));

	    	$instance->_aCourseTags = $aTags;

	    	return $instance;
	    }

	    return false;
    }


	/**
	 * Return the object representing the course in the db with the same name.
	 *
	 * @param string $sName
	 *
	 * @param bool $blGetCourseParts
	 * @return bool|CmCourse
	 */
    public static function getCourseByName($sName,$blGetCourseParts = false)
    {
    	$instance = new self();
    	global $wpdb;

    	$sSQL = "SELECT ID FROM ".$instance->_getDbTableName()." WHERE name = %s";

    	$oCourse = $wpdb->get_row($wpdb->prepare($sSQL,$sName));

    	if (isset($oCourse)) {

	    	$instance = CmCourse::getCourseByID($oCourse->ID,$blGetCourseParts);

	    	return $instance;
	    }

	    return false;
    }


    /**
     * Orders the CourseParts after their CourseIndex vaule.
     *
     * @param CmCoursePart[] $aCourseParts
     *
     * @return null
     */
    public static function orderCourseParts($aCourseParts = null, $iCourseID = null)
    {
    	if (isset($iCourseID)) {
    		$aCourseParts = CmCourse::getCourseByID($iCourseID)->_aCourseParts;
    	}

    	$aNewCPArr = array();

    	foreach ($aCourseParts as $oCoursePart) {
    		$aNewCPArr[$oCoursePart->getCourseIndex()] = $oCoursePart;
    	}

    	return $aNewCPArr;
    }


    /**
     * Returns some courses from the DB.
     *
     * @param int $iPage, int $iPerPage, boolean $blGetCourseParts
     *
     * @return CmCourse[]
     */
    public static function getCourses($iPage = 1, $iPerPage = 10)
    {
    	global $wpdb;
    	$aCourses = array();
    	$oCmCourse = new self();

    	$sSQL = "SELECT ID FROM ".$oCmCourse->_getDbTableName();

    	if (!empty($_REQUEST['orderby'])){
		    $sSQL .= " ORDER BY " . esc_sql( $_REQUEST['orderby'] );
			$sSQL .= !empty( $_REQUEST['order'] ) ? " ". esc_sql( $_REQUEST['order'] ) : " ASC";
		}

		$sSQL .= " LIMIT ".$iPerPage." OFFSET ".($iPage - 1)*$iPerPage;

    	$aCourseIDs = $wpdb->get_col($sSQL);

    	foreach ($aCourseIDs as $iID) {

    		$instance = CmCourse::getCourseByID($iID,true);

	    	array_push($aCourses,$instance);
	    }

    	return $aCourses;
    }


    /**
     * Returns all the courses in the DB.
     *
     * @return CmCourse[]
     */
    public static function getAllCourses($blGetCourseParts = false)
    {
    	global $wpdb;
    	$aAllCourses = array();
    	$oCmCourse = new self();

    	$sSQL = "SELECT ID FROM ".$oCmCourse->_getDbTableName();

    	$aCourseIDs = $wpdb->get_col($sSQL);

    	foreach ($aCourseIDs as $iID) {

    		$instance = CmCourse::getCourseByID($iID,$blGetCourseParts);

	    	array_push($aAllCourses,$instance);
	    }

    	return $aAllCourses;
    }


	/**
	 * Returns all the courses in the DB.
	 *
	 * @return CmCourse[]
	 */
	public static function getAllActiveCourses($blGetCourseParts = false)
	{
		global $wpdb;
		$aAllCourses = array();
		$oCmCourse = new self();

		$sSQL = "SELECT ID FROM ".$oCmCourse->_getDbTableName()." WHERE active = 1";

		$aCourseIDs = $wpdb->get_col($sSQL);

		foreach ($aCourseIDs as $iID) {

			$instance = CmCourse::getCourseByID($iID,$blGetCourseParts);

			array_push($aAllCourses,$instance);
		}

		return $aAllCourses;
	}


	/**
	 * Checks to see if the given name is used for any other Course.
	 *
	 * @param string $sName
	 *
	 * @param int $iID
	 *
	 * @return bool|TRUE if the name is not used - FALSE if it is used
	 */
    public function checkCourseName($sName,$iID = null)
    {
    	if (count($this->_aUsedCourseNames) < 1) {
	    	$this->_aUsedCourseNames = $this->getUsedNames();
	    }

    	if(!isset($iID)){

	    	if (count($this->_aUsedCourseNames) < 1) {
	    		return true;
	    	} else {

	    		if(in_array($sName, $this->_aUsedCourseNames)){
	    			return false;
	    		}

	    		return true;
	    	}
    	} else{
    		if (in_array($sName, $this->_aUsedCourseNames)) {

    			$oNameID = CmCourse::getCourseByName($sName);
    			if ($iID === $oNameID->_iCourseID) {
    				return true;
    			}

    			return false;
    		} else{
    			return true;
    		}
    	}
    }


    /**
     * Returns the CoursePart that matches the passed index.
     *
     * @param int $iIndex
     *
     * @return CmCoursePart
     */
    protected function _getCoursePartByIndex($iIndex)
    {
    	foreach ($this->getCourseParts() as $coursePart) {
    		if ($coursePart->getCourseIndex() == $iIndex) {
    			return $coursePart;
    		}
    	}
    }


	/**
	 *
	 */
	public function exportToJSON(){
    	$aJSONCourse = array(
    		"ID" => $this->getCourseID(),
		    "type" => "Course",
		    "name" => $this->getCourseName(),
		    "description" => $this->getCourseDescription(),
		    "price" => $this->getCoursePrice(),
		    "span" => $this->getCourseSpan(),
	    );

		$aCourseParts = array();
		foreach ($this->getCourseParts() as $oCoursePart){
			array_push($aCourseParts, json_decode($oCoursePart->toJSON()));
		}
		$aJSONCourse["parts"] = $aCourseParts;

		return json_encode($aJSONCourse);
    }


    /**
     * Prints the <ul> element representing this Course | For tpl/courseForm.php
	 *
	 * @return Null
     */
    public function printListRep()
    {
    	?>
    	<ul class='cm_coursePartList'>
    	<?php
    		for ($i=0; $i < $this->getNrCourseParts(); $i++) {
	    		$oCoursePart = $this->_getCoursePartByIndex($i);
	    		$oCoursePart->printListItemRep();
	    	}
    	?>
    	</ul>
    	<?php
    }


    /**
     * for toString
     */
    protected function _coursePartsToString()
    {
    	$sStringToRet = "&ensp;CourseParts:<br>";

    	for ($i=0; $i < $this->getNrCourseParts(); $i++) {
    		$sStringToRet .= "&ensp;".strval($this->_getCoursePartByIndex($i))."<br>";
    	}
    		
    	return $sStringToRet;
    }


    /**
     * toString
     */
    public function __toString()
    {
    	$sStringToRet = "";

    	$sStringToRet .= $this->_sCourseName."&emsp;Tags: ".$this->getCourseTagList()."<br>";
    	if(count($this->_aCourseParts) > 0){
    		$sStringToRet .= $this->_coursePartsToString();
    	}

    	return $sStringToRet;
    }


    /**
     * Returns all the tags that this course has.
     *
     * @param string $sName
     *
     * @return int $iTagID
     */
    protected function _getTagIdByName($sName)
    {
    	global $wpdb;

    	$sSQL = "SELECT ID FROM ".$this->_getDbTableNameTags()."WHERE name = %s";

    	$iTagID = intval($wpdb->get_row($wpdb->prepare($sSQL,$sName)));

    	return $iTagID;
    }
}



?>