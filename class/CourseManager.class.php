<?php
/**
 * CourseManager.class.php
 * 
 * The CourseManager class file.
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
 * The CourseManager class.
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
class CourseManager
{

	protected $_sCmVersion = "1.0";
    protected $_sCmDBVersion = "1.0";
    protected $_blActive = false;
    protected $_sAdminOptionsName = "cmAdminOptions";
    protected $_aAdminOptions = null;
    protected $_aWPOptions = null;

	/**
     * Constructor.
     */
    public function __construct()
    {
    	$this->_blActive = true;
    	do_action('cm_init', $this);
    }


    /**
     * Returns the database charset.
     * 
     * @return string
     */
    protected function _getCharset()
    {
        global $wpdb;
        $sCharsetCollate = '';

        $sMySlqVersion = $wpdb->get_var("SELECT VERSION() as mysql_version");
        
        if (version_compare($sMySlqVersion, '4.1.0', '>=')) {
            if (!empty($wpdb->charset)) {
                $sCharsetCollate = "DEFAULT CHARACTER SET $wpdb->charset";
            }
            
            if (!empty($wpdb->collate)) {
                $sCharsetCollate.= " COLLATE $wpdb->collate";
            }
        }
        
        return $sCharsetCollate;
    }


    /**
     * Returns the current user.
     *
     * @return WP_User
     */
    public function getCurrentUser()
    {
        if (!function_exists('get_userdata')) {
            include_once ABSPATH.'wp-includes/pluggable.php';
        }

        //Force user information
        return wp_get_current_user();
    }


    /**
     * Get and updates settings
     * 
     * @return array
     */
    public function getOptions()
    {
        if ($this->_aAdminOptions === null) {
            $aCmAdminOptions = array(
                'edit_access_role' => 'administrator'
            );
            
            $aCmOptions = $this->getWpOption($this->_sAdminOptionsName);
            
            if (!empty($aCmOptions)) {
                foreach ($aCmOptions as $sKey => $mOption) {
                    $aCmAdminOptions[$sKey] = $mOption;
                }
            }
            
            update_option($this->_sAdminOptionsName, $aCmAdminOptions);
            $this->_aAdminOptions = $aCmAdminOptions;
        }

        return $this->_aAdminOptions;
    }


    /**
     * Returns a option value
     *
     * @param string $sOption
     *
     * @return array
     */
    public function getWPOption($sOption)
    {
        if (!isset($this->_aWpOptions[$sOption])) {
            $this->_aWpOptions[$sOption] = get_option($sOption);
        }

        return $this->_aWpOptions[$sOption];
    }


    /**
     * Returns the role of the user with this ID.
     * 
     * @return array
     */
    protected function _getUserRole($iUserID)
    {
        global $wpdb;

        $oData = get_userdata($iUserID);

        if (!empty($oData->user_level) && !isset($oData->user_level)) {
            $oData->user_level = null;
        }
        
        if (isset($oData->{$wpdb->prefix . "capabilities"})) {
            $aCapabilities = $oData->{$wpdb->prefix . "capabilities"};
        } else {
            $aCapabilities = array();
        }
        
        $aRoles = (is_array($aCapabilities) && count($aCapabilities) > 0) ? array_keys($aCapabilities) : array('norole');
        
        return $aRoles;

    }


    /**
     * Get role is associative array.
     * 
     * @return array
     */
    public function getDefaultRoles()
    {
        $aRoles = array(
            'norole' => 0,
            'subscriber' => 1,
            'contributor' => 2,
            'author' => 3,
            'editor' => 4,
            'administrator' => 5
        );
        
        return $aRoles;
    }


    /**
     * Installs course manager.
     * 
     * @return null;
     */
    public function install(){
    	$this->_installCm();
    }

    /**
     * Creates the tables that are needed in the database.
     * 
     * @return null;
     */
    protected function _installCm()
    {
    	global $wpdb;
        include_once ABSPATH.'wp-admin/includes/upgrade.php';

        $sCharsetCollate = $this->_getCharset();

        //Cecking if cm_courses table exists
        $sCmCourseTableName = $wpdb->prefix.'cm_courses';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmCourseTableName."'"
        );

        if ($sNameInDb != $sCmCourseTableName) {
        	//Creating cm_courses table
        	dbDelta(
        		"CREATE TABLE ".$sCmCourseTableName." (
        			ID int NOT NULL auto_increment,
					name VARCHAR(100) NOT NULL UNIQUE,
					active BOOLEAN NOT NULL DEFAULT FALSE,
					span int NOT NULL COMMENT 'Days',
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
        	);
        }

        //Cecking if cm_course_parts table exists
        $sCmCoursePartTableName = $wpdb->prefix.'cm_course_parts';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmCoursePartTableName."'"
        );

        if ($sNameInDb != $sCmCoursePartTableName) {

        	$sDbPostTable = $wpdb->prefix."posts";

            $aWpDbPostDataType = $wpdb->get_col("SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '".$sDbPostTable."' AND COLUMN_NAME = 'ID'");

        	//Creating cm_course_parts table
        	dbDelta(
        		"CREATE TABLE ".$sCmCoursePartTableName." (
        			ID int NOT NULL auto_increment,
                    courseID int NOT NULL,
                    postID ".$aWpDbPostDataType[0].",
                    name VARCHAR(100) NOT NULL,
                    courseIndex int NOT NULL,
                    PRIMARY KEY (ID),
                    FOREIGN KEY (courseID) REFERENCES ".$sCmCourseTableName."(ID) ON DELETE CASCADE,
                    FOREIGN KEY (postID) REFERENCES ".$sDbPostTable."(ID)
				) $sCharsetCollate;"
        	);
        }

        //Cecking if cm_parts table exists
        $sCmPartTableName = $wpdb->prefix.'cm_parts';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmPartTableName."'"
        );

        if ($sNameInDb != $sCmPartTableName) {
        	//Creating cm_parts table
        	dbDelta(
        		"CREATE TABLE ".$sCmPartTableName." (
        			ID int NOT NULL auto_increment,
                    coursePartID int NOT NULL,
                    title VARCHAR(100),
                    content LONGTEXT,
                    type VARCHAR(65),
                    partIndex int NOT NULL,
                    PRIMARY KEY (ID),
                    FOREIGN KEY (coursePartID) REFERENCES ".$sCmCoursePartTableName."(ID) ON DELETE CASCADE
				) $sCharsetCollate;"
        	);
        }

        //Cecking if cm_tags table exists
        $sCmTagTableName = $wpdb->prefix.'cm_tags';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmTagTableName."'"
        );

        if ($sNameInDb != $sCmTagTableName) {
        	//Creating cm_tags table
        	dbDelta(
        		"CREATE TABLE ".$sCmTagTableName." (
        			ID int NOT NULL auto_increment,
					name VARCHAR(100) NOT NULL,
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
        	);
        }

        //Cecking if cm_rel_tag_course table exists
        $sCmTagRelTableName = $wpdb->prefix.'cm_rel_tag_course';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmTagRelTableName."'"
        );

        if ($sNameInDb != $sCmTagRelTableName) {
        	//Creating cm_rel_tag_course table
        	dbDelta(
        		"CREATE TABLE ".$sCmTagRelTableName." (
        			courseID int,
					tagID int,
					FOREIGN KEY (courseID) REFERENCES ".$sCmCourseTableName."(ID) ON DELETE CASCADE,
					FOREIGN KEY (tagID) REFERENCES ".$sCmTagTableName."(ID) ON DELETE CASCADE,
					PRIMARY KEY (courseID, tagID)
				) $sCharsetCollate;"
        	);
        }

        add_option("cm_db_version", $this->_sCmDBVersion);
    }


    /**
     * Uninstalls course manager.
     * 
     * @return null;
     */
    public function uninstall(){
        $this->_uninstallCm();
    }


    /**
     * Removes the Course Manager tables in the database.
     * 
     * @return null;
     */
    protected function _uninstallCm()
    {
        global $wpdb;

        $sCmCourseTableName = $wpdb->prefix.'cm_courses';
        $sCmCoursePartTableName = $wpdb->prefix.'cm_course_parts';
        $sCmPartTableName = $wpdb->prefix.'cm_parts';
        $sCmTagTableName = $wpdb->prefix.'cm_tags';
        $sCmTagRelTableName = $wpdb->prefix.'cm_rel_tag_course';

        $wpdb->query(
            "
             DROP TABLE IF EXISTS ".$sCmPartTableName.",
             ".$sCmCoursePartTableName.",
             ".$sCmTagRelTableName.",
             ".$sCmTagTableName.",
             ".$sCmCourseTableName."
             "
        );
        
        delete_option($this->_sAdminOptionsName);
        //delete_option('cm_version');
        delete_option('cm_db_version');
    }


    /**
     * Does all the required things to deactivate Course Manager.
     *
     * @return null
     */
    public function deactivate()
    {
        #TODO
    }


    /**
     * Checks to see if the user has access to the Course Manager Adminpanel
     * 
     * @return boolean
     */
    public function checkAdminPageAccess()
    {
    	$oCurrentUser = $this->getCurrentUser();
    	$aCmOptions = $this->getOptions();

    	$aUserRoles = $this->_getUserRole($oCurrentUser->ID);
    	$aUserRoleKeys = array_keys($aUserRoles);
    	$aRoles = $this->getDefaultRoles();
    	$iRoleLevel = 0;

    	foreach ($aUserRoles as $sUserRole) {
    		if (isset($aRoles[$sUserRole]) && $aRoles[$sUserRole] > $iRoleLevel) {
    			$iRoleLevel = $aRoles[$sUserRole];
    		}
    	}

    	if ($iRoleLevel >= $aRoles[$aCmOptions['edit_access_role']]
    		|| isset($aUserRoleKeys['administrator'])
    		|| is_super_admin($oCurrentUser->ID))
    	{
    		return true;
    	}

    	return false;

    }


    /*
     * Functions for the admin panel content.
     */
    
    /**
     * The function for the wp_print_styles action.
     * 
     * @return null
     */
    public function addStyles()
    {
        wp_enqueue_style(
            'CourseManagerEditCourse', 
            CM_URLPATH . "css/cmEditCourse.css",
            array() ,
            '1.0',
            'screen'
        );
        
        wp_enqueue_style(
            'CourseManagerGeneral', 
            CM_URLPATH . "css/cmGeneral.css",
            array() ,
            '1.0',
            'screen'
        );
    }

    
    /**
     * The function for the wp_print_scripts action.
     * 
     * @return null
     */
    public function addScripts()
    {
        wp_enqueue_script(
            'CourseManagerScripts',
            CM_URLPATH . 'js/functions.js', 
            array('jquery'),
            '1.0.0'
        );
    }


    /**
     * Prints the admin page.
     * 
     * @return null
     */
    public function prtAdminPage()
    {
        if (isset($_GET['page'])) {
            $sAdminPage = $_GET['page'];

            if ($sAdminPage == 'cm_courses') {

                include CM_REALPATH."tpl/adminCourses.php";
            } elseif ($sAdminPage == 'cm_tags') {
                include CM_REALPATH."tpl/adminTags.php";
            } elseif ($sAdminPage == 'cm_settings') {
                include CM_REALPATH."tpl/adminSettings.php";
            } elseif ($sAdminPage == 'cm_about') {
                include CM_REALPATH."tpl/about.php";
            }
        }
    }


    /**
     * Creates a course, adds it to the database and generates all the requiered pages for the course.
     *
     * @param CmCourse $course
     *
     * @return boolean | TRUE - Successfully made course | FALSE - Failed to make course
     */
    public function createCourse($course)
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