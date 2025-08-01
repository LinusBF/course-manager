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

	protected $_sCmVersion = "1.5";
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
    public static function getCharset()
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
	 * @param bool $blForceRefresh
	 *
	 * @return array
	 */
    public function getOptions($blForceRefresh = false)
    {
        if ($this->_aAdminOptions === null || $blForceRefresh) {
            $aCmAdminOptions = array(
                'edit_access_role' => 'administrator',
	            'store_active' => false,
	            'courses_in_store' => array(),
	            'currency' => 'sek',
	            'stripe' => array(
		            "secret_key"      => -1,
		            "publishable_key" => -1,
                    "webhook_secret" => -1
	            ),
                'mail_chimp' => array(
	                "api_key"      => -1,
	                'list_id' => -1,
	                'template_id' => -1,
	                'campaign_id' => -1,
	                'buyer_group_title' => 'Buyer',
	                'newsletter_group_title' => 'Newsletter',
                ),
                'mandrill' => array(
	                "api_key"      => -1,
	                'template_slug' => -1,
	                'domain' => 'vopviktopererad.se',
	                'subject_line' => TXT_CM_MC_SUBJECT,
	                'from_email' => 'support',
	                'from_name' => TXT_CM_MC_FROM_NAME
                ),
            );
            
            $aCmOptions = $this->getWPOption($this->_sAdminOptionsName);
            
            if (!empty($aCmOptions)) {
                foreach ($aCmOptions as $sKey => $mOption) {
                    if(!is_array($aCmAdminOptions[$sKey])){
                    	$aCmAdminOptions[$sKey] = $mOption;
                    } else{
                    	if(is_array($mOption)) {
		                    foreach ($mOption as $arrKey => $arrItem){
			                    $aCmAdminOptions[$sKey][$arrKey] = $arrItem;
		                    }
                    	}
					}
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
	 * @param bool $blCacheBust
	 *
	 * @return array
	 */
    public function getWPOption($sOption, $blCacheBust = false)
    {
        if (!isset($this->_aWPOptions[$sOption]) || $blCacheBust) {
            $this->_aWPOptions[$sOption] = get_option($sOption);
        }

        return $this->_aWPOptions[$sOption];
    }


	/**
	 * @return array
	 */
	public function updateOptionsFromDb(){
    	$aCurrentOptions = $this->getOptions();
	    $aOptionsFromDb = $this->getWPOption($this->_sAdminOptionsName, true);

	    $aUpdatedOptions = array_merge($aCurrentOptions, $aOptionsFromDb);

	    $this->_aAdminOptions = $aUpdatedOptions;

	    return $aUpdatedOptions;

    }


	/**
	 * @param string $sOptionKey
	 * @param mixed $mValue
	 * @param bool $blSetIfNonExistent
	 *
	 *
	 * @return bool
	 */
	public function setOption($sOptionKey, $mValue, $blSetIfNonExistent = false)
    {
    	$aOptions = $this->getOptions();

	    if(array_key_exists($sOptionKey, $aOptions)) {
		    $aOptions[ $sOptionKey ] = $mValue;

		    $this->_aAdminOptions[$sOptionKey] = $mValue;

		    return update_option( $this->_sAdminOptionsName, $aOptions );
	    }
	    else{
	    	if($blSetIfNonExistent){
				return $this->setNewOption($sOptionKey, $mValue);
		    }
		    else{
			    return false;
		    }
	    }
    }


	/**
	 * @param string $sOptionKey
	 * @param mixed $mNewValue
	 *
	 * @return bool
	 */
	public function setNewOption($sOptionKey, $mNewValue)
	{
		$aOptions = $this->getOptions();

		$aOptions[ $sOptionKey ] = $mNewValue;

		$this->_aAdminOptions = $aOptions;

		return update_option( $this->_sAdminOptionsName, $aOptions );
	}


	/**
	 * Returns the role of the user with this ID.
	 *
	 * @param int $iUserID
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


	public function update_stripe(){
		if( empty( $_POST['cm_action'] ) || 'stripe_settings' != $_POST['cm_action'] )
			return;
		if( ! wp_verify_nonce( $_POST['cm_stripe_nonce'], 'cm_stripe_nonce' ) )
			return;
		if( ! current_user_can( 'manage_options' ) )
			return;

		$stripe_settings = array(
			"secret_key"      => $_POST['cm_stripe_secret'],
			"publishable_key" => $_POST['cm_stripe_public'],
			"webhook_secret" => $_POST['cm_stripe_webhook'],
		);

		$this->setOption('stripe', $stripe_settings, true);

		CmCourseStoreHandler::activateStripe();
	}


	public function update_mailchimp(){
		if( empty( $_POST['cm_action'] ))
			return;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if($_POST['cm_action'] === 'mailchimp_settings') {
			if ( ! wp_verify_nonce( $_POST['cm_mailchimp_nonce'], 'cm_mailchimp_nonce' ) ) {
				return;
			}

			CmMailController::setApiKey($_POST['cm_mail_chimp_key']);
		}
		elseif($_POST['cm_action'] === 'mailchimp_list_settings') {
			if ( ! wp_verify_nonce( $_POST['cm_mailchimp_list_nonce'], 'cm_mailchimp_list_nonce' ) ) {
				return;
			}

			CmMailController::setList($_POST['cm_mc_list']);
		}
	}


	public function update_mandrill(){
		if( empty( $_POST['cm_action'] ))
			return;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if($_POST['cm_action'] === 'mandrill_settings') {
			if ( ! wp_verify_nonce( $_POST['cm_mandrill_nonce'], 'cm_mandrill_nonce' ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$mandrill_settings = array(
				"api_key" => $_POST['cm_mandrill_key'],
			);

			$this->setOption( 'mandrill', $mandrill_settings, true );
		}
		elseif($_POST['cm_action'] === 'mandrill_template_settings') {
			if ( ! wp_verify_nonce( $_POST['cm_mandrill_template_nonce'], 'cm_mandrill_template_nonce' ) ) {
				return;
			}

			CmMandrillController::setTemplateSlug($_POST['cm_md_template']);
		}
	}


	/**
	 * See "Flushing Rewrite on Activation" https://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public function rewrite_flush()
	{
		// First, we "add" the custom post type via the above written function.
		// Note: "add" is written with quotes, as CPTs don't get added to the DB,
		// They are only referenced in the post_type column with a post entry,
		// when you add a post of this CPT.
		$this->create_cm_post_type();

		// ATTENTION: This is *only* done during plugin activation hook in this example!
		// You should *NEVER EVER* do this on every page load!!
		flush_rewrite_rules();
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

        $sCharsetCollate = $this->getCharset();

        //Checking if cm_courses table exists
        $sCmCourseTableName = $wpdb->prefix.'cm_courses';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmCourseTableName."'"
        );

        if ($sNameInDb != $sCmCourseTableName) {
        	//Creating cm_courses table
        	dbDelta(
        		"CREATE TABLE ".$sCmCourseTableName." (
        			ID int NOT NULL auto_increment,
					name VARCHAR(150) NOT NULL UNIQUE,
					description TEXT,
					price int NOT NULL,
					active BOOLEAN NOT NULL DEFAULT FALSE,
					span int NOT NULL COMMENT 'Days',
					PRIMARY KEY (ID)
				) $sCharsetCollate;"
        	);
        }

        //Checking if cm_course_parts table exists
        $sCmCoursePartTableName = $wpdb->prefix.'cm_course_parts';

        $sNameInDb = $wpdb->get_var(
        	"SHOW TABLES LIKE '".$sCmCoursePartTableName."'"
        );

        if ($sNameInDb != $sCmCoursePartTableName) {

        	//Creating cm_course_parts table
        	dbDelta(
        		"CREATE TABLE ".$sCmCoursePartTableName." (
        			ID int NOT NULL auto_increment,
                    courseID int NOT NULL,
                    name VARCHAR(250) NOT NULL,
                    courseIndex int NOT NULL,
                    PRIMARY KEY (ID),
                    FOREIGN KEY (courseID) REFERENCES ".$sCmCourseTableName."(ID) ON DELETE CASCADE
				) $sCharsetCollate;"
        	);
        }

        //Checking if cm_parts table exists
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
                    title VARCHAR(250),
                    content LONGTEXT,
                    type VARCHAR(65),
                    partIndex int NOT NULL,
                    PRIMARY KEY (ID),
                    FOREIGN KEY (coursePartID) REFERENCES ".$sCmCoursePartTableName."(ID) ON DELETE CASCADE
				) $sCharsetCollate;"
        	);
        }

        /*  NOT CURRENTLY USED

        //Checking if cm_tags table exists
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

        //Checking if cm_rel_tag_course table exists
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

        */

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
	    $oStore = new CmStore();

	    $oStore->deactivateStore();
    }


    /**
     * Export current instance of Course Manager plugin state
     */
    public function export(){
        $aCourses = CmCourse::getAllCourses(true);
	    $oStore = new CmStore();
		$aPluginData = array();

	    $aCourseData = array("type" => "courses", "item" => array());
	    foreach ($aCourses as $oCourse){
	    	array_push($aCourseData['item'], json_decode($oCourse->exportToJSON()));
	    }

	    $aOptions = $this->getOptions();
	    unset($aOptions['stripe']);
	    unset($aOptions['mail_chimp']);
	    unset($aOptions['mandrill']);
	    array_push($aPluginData, $aCourseData);
	    array_push($aPluginData, array("type" => "users", "item" => json_decode(CmUserManager::exportUsersToJSON())));
	    array_push($aPluginData, array("type" => "store", "item" => json_decode($oStore->exportToJSON())));
	    array_push($aPluginData, array("type" => "options", "item" => $aOptions));
	    array_push($aPluginData, array("type" => "db_version", "item" => $this->_sCmDBVersion));
	    array_push($aPluginData, array("type" => "cm_version", "item" => $this->_sCmVersion));

	    return json_encode($aPluginData);
    }


    public function export_download(){
	    if( empty( $_POST['cm_action'] ) || 'export_settings' != $_POST['cm_action'] )
		    return;
	    if( ! wp_verify_nonce( $_POST['cm_export_nonce'], 'cm_export_nonce' ) )
		    return;
	    if( ! current_user_can( 'manage_options' ) )
		    return;

	    ignore_user_abort( true );
	    nocache_headers();
	    header( 'Content-Type: application/json; charset=utf-8' );
	    header( 'Content-Disposition: attachment; filename=course-manager-export-' . date( 'm-d-Y' ) . '.json' );
	    header( "Expires: 0" );

	    echo $this->export();
	    exit;
    }


	/**
	 * Import current instance of Course Manager plugin state. Needs to be updated to deal with bad/damaged JSON
	 *
	 * @param string $sImportData - The exported data in JSON format
	 *
	 * @return bool True if successfully imported, false if something went wrong (probably wrong JSON structure)
	 */
    public function import($sImportData){
      $aPluginData = json_decode($sImportData);
      $blImportCheck = true;

      if ($aPluginData[0]->type != "courses" || $aPluginData[1]->type != "users" || $aPluginData[2]->type!= "store"
          || $aPluginData[3]->type != "options" || $aPluginData[4]->type != "db_version" || $aPluginData[5]->type != "cm_version"){
        return false;
      }

      $aCourseIdMapping = array();
      $aCoursePartIdMapping = array();
      $aPartIdMapping = array();
      foreach ($aPluginData[0]->item as $aCourseData){
        if ($aCourseData->type != "Course")
          continue;

        $oCourse = CmCourse::create();
        $oCourse->setCourseName($aCourseData->name);
        $oCourse->setCourseDescription($aCourseData->description);
        $oCourse->setCoursePrice($aCourseData->price);
        $oCourse->setCourseSpan($aCourseData->span);
        $oCourse->setCourseStatus(false);

        $iNewCourseId = $oCourse->save();
        if($iNewCourseId === false){
          $blImportCheck = false;
          break;
        }
        $aCourseIdMapping[strval($aCourseData->ID)] = strval($iNewCourseId);

        $aCourseParts = array();

        foreach ($aCourseData->parts as $aCoursePartData){
          $aParts = array();

          $oCoursePart = CmCoursePart::createWParams($aCoursePartData->index, $iNewCourseId, $aCoursePartData->name);
          $iNewCPId = $oCoursePart->save();
          if($iNewCPId === false){
              $blImportCheck = false;
              break;
          }
          $aCoursePartIdMapping[strval($aCoursePartData->ID)] = strval($iNewCPId);

          foreach ($aCoursePartData->parts as $aPartData){
            $oPart = CmPart::createWParams($aPartData->title, $aPartData->content, $aPartData->index, $aPartData->type, $iNewCPId);
            $iNewPartId = $oPart->save();
            if($iNewPartId === false){
                $blImportCheck = false;
                break;
            }

            $aPartIdMapping[strval($aPartData->ID)] = strval($iNewPartId);
            array_push($aParts, $oPart);
          }
          if(!$blImportCheck){
              break;
          }

          $oCoursePart->setParts($aParts);
          array_push($aCourseParts, $oCoursePart);
        }

        if(!$blImportCheck){
          break;
        }

        $oCourse->setCourseParts($aCourseParts);

        if(!$oCourse->save(true)){
          $blImportCheck = false;
        }
      }

      if($blImportCheck && !CmUserManager::importFromJSON($aPluginData[1]->item, $aCourseIdMapping, $aCoursePartIdMapping, $aPartIdMapping)){
        $blImportCheck = false;
      }

      $oOldOptions = $aPluginData[3]->item;
      CourseManager::setOption('edit_access_role',$oOldOptions->edit_access_role,true);
      CourseManager::setOption('currency',$oOldOptions->currency,true);

      return $blImportCheck;
    }

    public function import_upload(){
	    if( empty( $_POST['cm_action'] ) || 'import_settings' != $_POST['cm_action'] )
		    return;
	    if( ! wp_verify_nonce( $_POST['cm_import_nonce'], 'cm_import_nonce' ) ) {
		    wp_die( __( 'Nonce failure' ) );
		    return;
	    }
	    if( ! current_user_can( 'manage_options' ) ) {
		    wp_die( __( 'User cannot manage options' ) );
		    return;
	    }
	    $extension = end( explode( '.', $_FILES['import_file']['name'] ) );
	    if( $extension != 'json' ) {
		    wp_die( __( 'Please upload a valid .json file' ) );
	    }
	    $import_file = $_FILES['import_file']['tmp_name'];
	    if( empty( $import_file ) ) {
		    wp_die( __( 'Please upload a file to import' ) );
	    }

	    $sData = file_get_contents( $import_file );

	    $this->import($sData);
    }


	/**
	 *
	 */
	function create_cm_post_type() {
		register_post_type( 'cm_course_page',
			array(
				'labels' => array(
					'name' => __( 'Course Pages', 'course-manager' ),
					'singular_name' => __( 'Course Page', 'course-manager' ),
					'add_new'            => __( 'Add New Course page', 'slide', 'course-manager' ),
					'add_new_item'       => __( 'Add New Course page', 'course-manager' ),
					'edit_item'          => __( 'Edit Course page', 'course-manager' ),
					'new_item'           => __( 'New Course page', 'course-manager' ),
					'view_item'          => __( 'View Course page', 'course-manager' ),
					'search_items'       => __( 'Search Course page', 'course-manager' ),
					'not_found'          => __( 'No course pages have been added yet', 'course-manager' ),
					'not_found_in_trash' => __( 'Nothing found in Trash', 'course-manager' ),
				),
				'public' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_icon' => 'dashicons-format-aside',
				'hierarchical' => false,
				'has_archive' => false,
				'rewrite' => array('slug' => __( 'courses', 'course-manager' )),
			)
		);
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
			'CourseManagerCoursePage',
			CM_URLPATH . "css/cmCoursePage.css",
			array() ,
			'1.0',
			'screen'
		);
        
        wp_enqueue_style(
            'CourseManagerGeneral', 
            CM_URLPATH . "css/cm_general.css",
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

            } elseif ($sAdminPage == 'cm_store') {
	            include CM_REALPATH."tpl/adminStore.php";

            } elseif ($sAdminPage == 'cm_tags') {
                include CM_REALPATH."tpl/adminTags.php";

            } elseif ($sAdminPage == 'cm_settings') {
                include CM_REALPATH."tpl/adminSettings.php";

            } elseif ($sAdminPage == 'cm_about') {
                include CM_REALPATH."tpl/about.php";

            }
        }
    }
}

?>