<?php
/*
Plugin Name: Course Manager
Plugin URI:
Description: Make and update course pages easily
Author: Linus Bein Fahlander
Version: 1.1
Author URI:
*/

//Paths
load_plugin_textdomain('course-manager', false, 'course-manager/lang');
define('CM_URLPATH', plugins_url('', __FILE__).'/');
define('CM_REALPATH', WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__)).'/');

//Defines
require_once 'includes/database.define.php';
require_once 'includes/language.define.php';
require_once('third-party/stripe-php-5.8.0/init.php');

//Setup Stripe settings
\Stripe\Stripe::setAppInfo("Wordpress CourseManager", "1.2");


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

if (version_compare($wp_version, "4.8.3") === -1) {
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
require_once 'class/CmPageBuilder.class.php';
require_once 'class/CmStore.class.php';
require_once 'class/CmCourseStoreHandler.class.php';
require_once 'class/CmLandingPageTable.class.php';
require_once 'class/CmUserManager.class.php';
require_once 'class/CmPaymentHandler.class.php';
require_once 'class/CmMailController.class.php';
require_once 'class/CmMandrillController.php';
require_once 'class/CmMailChimpTables.class.php';
require_once 'class/CmMandrillTable.php';
require_once 'widget/cmLinks.class.php';


$oCourseManager = new CourseManager();
$oUserManager = new CmUserManager();


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
			add_submenu_page('cm_courses', TXT_CM_MENU_STORE, TXT_CM_MENU_STORE, 'read', 'cm_store', array($oCourseManager, 'prtAdminPage'));
			//add_submenu_page('cm_courses', TXT_CM_MENU_TAGS, TXT_CM_MENU_TAGS, 'read', 'cm_tags', array($oCourseManager, 'prtAdminPage')); // NOT USED CURRENTLY
			add_submenu_page('cm_courses', TXT_CM_SETTINGS, TXT_CM_SETTINGS, 'read', 'cm_settings', array($oCourseManager, 'prtAdminPage'));
			add_submenu_page('cm_courses', TXT_CM_MENU_ABOUT, TXT_CM_MENU_ABOUT, 'read', 'cm_about', array($oCourseManager, 'prtAdminPage'));

			do_action('cm_add_menu');
		}
		
	}
}


if (!function_exists("cm_load_ajax")){
	function cm_load_ajax(){
		//Admin Edit
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


if (!function_exists("cm_load_course_page_ajax")){
	function cm_load_course_page_ajax(){
		//Course Page
		wp_enqueue_script(
			'CourseManagerCourseAjax',
			CM_URLPATH . 'js/course_page.js',
			array('jquery'),
			'1.0.0'
		);

		//Get current protocol
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

		//Ajax params
		$params = array(
			// Get the url to the admin-ajax.php file using admin_url()
			'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
		);

		wp_localize_script(
			'CourseManagerCourseAjax',
			'course_qa',
			$params
		);
	}
}


if (isset($oCourseManager)) {

	if(!function_exists("install_cm")){
		function install_cm(){
			$courseManager = new CourseManager();
			$userManager = new CmUserManager();
			$store = new CmStore();

			$courseManager->install();
			$userManager->install_UM();
			$store->installStore();
		}
	}

	if(!function_exists("uninstall_cm")){
		function uninstall_cm(){
			$courseManager = new CourseManager();
			$userManager = new CmUserManager();
			$store = new CmStore();

			$courseManager->uninstall();
			$userManager->uninstall_UM();
			$store->uninstallStore();
		}
	}

	//Register Activation
	register_activation_hook(__FILE__, array($oCourseManager, 'rewrite_flush'));
	register_activation_hook(__FILE__, "install_cm");
	add_action('init', array($oCourseManager, 'create_cm_post_type'));


	//uninstall
	if (function_exists('register_uninstall_hook')) {
		register_uninstall_hook(__FILE__, "uninstall_cm");

	} elseif (function_exists('register_deactivation_hook')) {
		//Fallback
		register_deactivation_hook(__FILE__, "uninstall_cm");

	}


	add_action('admin_init', array($oCourseManager, 'export_download'));
	add_action('admin_init', array($oCourseManager, 'import_upload'));
	add_action('admin_init', array($oCourseManager, 'update_stripe'));
	add_action('admin_init', array($oCourseManager, 'update_mailchimp'));
	add_action('admin_init', array($oCourseManager, 'update_mandrill'));

	add_action( 'wp_enqueue_scripts', 'course_page_scripts', 85);

	if ( is_admin() ) {
		add_action('admin_enqueue_scripts', 'create_edit_course_scripts');
		add_action('admin_enqueue_scripts', 'create_admin_courses_scripts');
		add_action('admin_enqueue_scripts', 'create_admin_settings_scripts');
		add_action('admin_enqueue_scripts', 'store_page_scripts');

		//Load Admin CSS and Scripts
		add_action('wp_print_scripts', array($oCourseManager, 'addScripts'));
		add_action('wp_print_styles', array($oCourseManager, 'addStyles'));

		//Load Course Page Ajax
		require_once CM_REALPATH . 'tpl/coursePageAjax.php';
		add_action( 'wp_ajax_cm_answer_question', 'cm_answer_question' );
		add_action( 'wp_ajax_cm_get_answers', 'cm_get_answers' );
		add_action( 'wp_ajax_cm_get_part_id', 'cm_get_part_id' );
		add_action( 'wp_ajax_nopriv_cm_answer_question', 'cm_answer_question' );
		add_action( 'wp_ajax_nopriv_cm_get_answers', 'cm_get_answers' );
		add_action( 'wp_ajax_nopriv_cm_get_part_id', 'cm_get_part_id' );

		//Load Admin AJAX Scripts
		require_once "tpl/editCourseAjaxFunctions.php";
		add_action( 'wp_ajax_cm_new_course_part', 'cm_add_new_coursePart' );
		add_action( 'wp_ajax_cm_new_part', 'cm_add_new_part' );
		add_action( 'wp_ajax_cm_change_part_type', 'cm_change_part_type' );
		add_action( 'wp_ajax_cm_add_question', 'cm_add_question' );

		//Add admin menu
		add_action('admin_menu', 'cmAdminPanel');

	}
	//Load templates for plugin specific pages
	add_filter('template_include', 'store_page_template', 99);
	add_filter('template_include', 'user_page_template', 99);
	add_filter('template_include', 'course_page_template', 90);
	add_filter('template_include', 'landing_page_template', 90);

	//Register widget
	//add_action('widgets_init', 'cmLinks_init'); DEPRECATED FOR NOW

	//Initiate session
	add_action('init', 'startSession', 99);
	add_action('wp_logout', 'endSession');
	add_action('wp_login', 'endSession');

}


function create_edit_course_scripts(){
	if(isset($_GET['action']) && isset($_GET['page']) && $_GET['action'] == 'edit' && $_GET['page'] == 'cm_courses'){
		wp_enqueue_script('cm_edit_course_script', CM_URLPATH. 'js/edit_course.js');
		wp_enqueue_script( 'cm_media_select_script_course', CM_URLPATH. 'js/media_selector_edit_course.js');
		cm_load_ajax();

		$script_data = array(
			'post_id' => get_option('media_selector_attachment_id', 0),
			'title' => TXT_CM_STORE_SELECT_IMAGE,
			'text' => TXT_CM_CHOOSE,
		);

		wp_localize_script('cm_media_select_script_course', 'passed_options', $script_data);
	}else{
		return;
	}
}


function create_admin_courses_scripts(){
	if(isset($_GET['page']) && $_GET['page'] == 'cm_courses'){
		wp_enqueue_script('cm_admin_courses_script', CM_URLPATH. 'js/admin_courses.js');

		$script_data = array(
			'confirm_dialog' => TXT_CM_ADMIN_COURSE_DELETE_CONFIRM,
		);

		wp_localize_script('cm_admin_courses_script', 'passed_options', $script_data);
	}else{
		return;
	}
}


function create_admin_settings_scripts(){
	if(isset($_GET['page']) && $_GET['page'] == 'cm_settings'){
		wp_enqueue_script('cm_admin_settings_script', CM_URLPATH. 'js/admin_settings.js');

	}else{
		return;
	}
}


function store_page_template($page_template){
	if(is_page('course-store')){  //Make this name dynamic?
		$page_template = dirname(__FILE__).'/tpl/templates/store-page-template.php';
	}
	return $page_template;
}


function user_page_template($page_template){
	if(is_page('course-account')){  //Make this name dynamic?
		$page_template = dirname(__FILE__).'/tpl/templates/user-page-template.php';
	}
	return $page_template;
}


function course_page_template($page_template){
	if(get_post_type() == 'cm_course_page'){
		$page_template = dirname(__FILE__).'/tpl/templates/course-page-template.php';
	}
	return $page_template;
}


function course_page_scripts(){
	if(get_post_type() == 'cm_course_page'){
		cm_load_course_page_ajax();
	} else{
		return;
	}
}


function landing_page_template($page_template){
	if(get_the_excerpt() == 'landing_page'){
		$page_template = dirname(__FILE__).'/tpl/templates/landing-page-template.php';
	}
	return $page_template;
}


function store_page_scripts(){
	if(isset($_GET['action']) && $_GET['action'] == 'store_settings'){
		wp_enqueue_script( 'cm_media_select_script', CM_URLPATH. 'js/media_selector.js');

		$script_data = array(
			'post_id' => get_option( 'media_selector_attachment_id', 0 ),
			'title' => TXT_CM_STORE_SELECT_IMAGE,
			'text' => TXT_CM_CHOOSE,
		);

		wp_localize_script('cm_media_select_script', 'passed_options', $script_data);
	}else{
		return;
	}
}


function startSession() {
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
}

function endSession() {
	session_destroy();
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