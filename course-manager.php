<?php
/*
Plugin Name: Course Manager
Plugin URI:
Description: Make and update course pages easily
Author: Linus Bein Fahlander
Version: 0.1
Author URI:
*/

//Paths
load_plugin_textdomain('course-manager', false, 'course-manager/lang');
define('CM_URLPATH', plugins_url('', __FILE__).'/');
define('CM_REALPATH', WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__)).'/');

//Defines
require_once 'includes/database.define.php';
require_once 'includes/language.define.php';

//Check requirements
$blStop = false;

//Check php version
$sPhpVersion = phpversion();

if (version_compare($sPhpVersion, "5.0") === -1) {
    add_action(
        'admin_notices',
        create_function(
            '',
            'echo \'<div id="message" class="error"><p><strong>'.
            sprintf(TXT_CM_PHP_VERSION_TO_LOW, $sPhpVersion).
            '</strong></p></div>\';'
        )
    );
    
    $blStop = true;
}

//Check wordpress version
global $wp_version;

if (version_compare($wp_version, "3.0") === -1) {
    add_action(
        'admin_notices',
        create_function(
            '',
            'echo \'<div id="message" class="error"><p><strong>'.
            sprintf(TXT_CM_WORDPRESS_VERSION_TO_LOW, $wp_version).
            '</strong></p></div>\';'
        )
    );
    
    $blStop = true;
}

//If there is a version error, stop the plugin
if ($blStop) {
    return;
}

//Classes
require_once 'class/CourseManager.class.php';
require_once 'class/CmCourse.class.php';
require_once 'class/CmCoursePart.class.php';
require_once 'class/CmPart.class.php';
require_once 'class/CmUamLink.class.php';
require_once 'widget/cmLinks.class.php';


$oCourseManager = new CourseManager();


//START - core promote function for User Access Manager
function promoteUser(){

  $iUserId = get_current_user_id();

  if (!class_exists("UserAccessManager")) {
    return false;
  }
  else{
    $oUserAccessManager = new UserAccessManager();
    $oHandler = $oUserAccessManager->getAccessHandler();
    $oGroups = $oHandler->getUserGroupsForObject('user', $iUserId);
    $isBuyer = false;

    foreach ($oGroups as $group) {
      if ($group->getId() == 2) {
        $isBuyer = true;
      }
    }

    if(!$isBuyer){

      $oBuyerGroup = $oHandler->getUserGroups(2);
      if (is_array($oBuyerGroup)) {
        $oBuyerGroup = $oBuyerGroup[0];
      }

      $oBuyerGroup->addObject('user', $iUserId);
      $oBuyerGroup->save(false);

    }
  }	
}
//END - core promote function for User Access Manager


if(!function_exists("cmAdminPanel")){

	/**
     * Creates the menu for admin interface
     * 
     * @return null;
     */
	function cmAdminPanel()	{
		global $oCourseManager;

		if ($oCourseManager->checkAdminPageAccess()) {

      //Add styles and scripts
      add_action('admin_print_styles', array($oCourseManager, 'addStyles'));
      add_action('wp_print_scripts', array($oCourseManager, 'addScripts'));
			
			//Admin menu
			add_menu_page('Course Manager', TXT_CM_PLUGIN_NAME, 'manage_options', 'cm_courses', array($oCourseManager, 'prtAdminPage'), 'div');


			//Admin sub menus
			add_submenu_page('cm_courses', TXT_CM_MENU_COURSES, TXT_CM_MENU_COURSES, 'read', 'cm_courses', array($oCourseManager, 'prtAdminPage'));
      add_submenu_page('cm_courses', TXT_CM_MENU_TAGS, TXT_CM_MENU_TAGS, 'read', 'cm_tags', array($oCourseManager, 'prtAdminPage'));
			add_submenu_page('cm_courses', TXT_CM_SETTINGS, TXT_CM_SETTINGS, 'read', 'cm_settings', array($oCourseManager, 'prtAdminPage'));
			add_submenu_page('cm_courses', TXT_CM_MENU_ABOUT, TXT_CM_MENU_ABOUT, 'read', 'cm_about', array($oCourseManager, 'prtAdminPage'));

			do_action('cm_add_menu');

		}
		
	}
}


if (!function_exists("cm_load_ajax")){
	function cm_load_ajax(){
			wp_enqueue_script(
				'CourseManagerAjax',
				CM_URLPATH . 'js/cmEditAjax.js',
				array('jquery'),
				'1.0.0', true
			);

			//Get current protocol
			$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

			//Ajax params
			$params = array(
				// Get the url to the admin-ajax.php file using admin_url()
				'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
			);

			wp_localize_script(
				'CourseManagerAjax',
				'new_course',
				$params
			);
	}
}


if (isset($oCourseManager)) {

	//Register Activation
	register_activation_hook(__FILE__, array($oCourseManager, 'install'));

	//uninstall
	if (function_exists('register_uninstall_hook')) {
		register_uninstall_hook(__FILE__, array($oCourseManager, 'uninstall'));
	} elseif (function_exists('register_deactivation_hook')) {
		//Fallback
		register_deactivation_hook(__FILE__, array($oCourseManager, 'uninstall'));
	}

	register_deactivation_hook(__FILE__, array($oCourseManager, 'deactivate'));

	//Load CSS and Scripts
	add_action('wp_print_scripts', array($oCourseManager, 'addScripts'));
	add_action('wp_print_styles', array($oCourseManager, 'addStyles'));
	add_action('wp_print_scripts', 'cm_load_ajax');
	require_once "tpl/editCourseAjaxFunctions.php";
	add_action('wp_ajax_cm_new_course_part', 'cm_add_new_coursePart');
	add_action('wp_ajax_cm_new_part', 'cm_add_new_part');
	add_action('wp_ajax_cm_change_part_type', 'cm_change_part_type');
	add_action('wp_ajax_cm_add_question', 'cm_add_question');

	//Register Plugin
	add_action('admin_menu', 'cmAdminPanel');

	//Register widget
	add_action('widgets_init', 'cmLinks_init');
}

function wpa54064_inspect_scripts() {
	global $wp_scripts;
	foreach( $wp_scripts->queue as $handle ) :
		echo $handle . ' | ';
	endforeach;

	//Debug filter
	//var_dump(has_action("filter_name"));
}
//DEBUG Check scripts and action
//add_action( 'wp_print_scripts', 'wpa54064_inspect_scripts' );

?>