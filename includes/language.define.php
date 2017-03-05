<?php
/**
 * language.define.php
 * 
 * Defines needed for the language
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

// --- General Use --- \\
define('TXT_CM_PLUGIN_NAME', __('Course Manager','course-manager'));
define('TXT_CM_YES', __('Yes','course-manager'));
define('TXT_CM_NO', __('No','course-manager'));
define('TXT_CM_CANCEL', __('Cancel','course-manager'));
define('TXT_CM_SAVE', __('Save','course-manager'));
define('TXT_CM_SETTINGS', __('Settings','course-manager'));
define('TXT_CM_ADD_NEW', __('Add New','course-manager'));
define('TXT_CM_DELETE', __('Delete','course-manager'));
define('TXT_CM_GENERATE_PAGES', __('Generate pages','course-manager'));
define('TXT_CM_ACTIVE', __('Active','course-manager'));
define('TXT_CM_NOT_ACTIVE', __('Not active','course-manager'));
define('TXT_CM_ACTIVATE', __('Activate','course-manager'));
define('TXT_CM_DEACTIVATE', __('Deactivate','course-manager'));

// --- Errors --- \\
define('TXT_CM_PHP_VERSION_TO_LOW', __('Your PHP version is too low, please update it before using Course Manager','course-manager'));
define('TXT_CM_WORDPRESS_VERSION_TO_LOW', __('Your Wordpress version is too low, please it update before using Course Manager','course-manager'));
define('TXT_CM_ERROR_NAME_CHECK', __('The name for the course is already in use, please edit that one instead.','course-manager'));
define('TXT_CM_ERROR_DB_QUERY', __('The database query failed, please try again. If the problem persists please contact the site administrator.','course-manager'));

// --- Menu --- \\
define('TXT_CM_MENU_COURSES', __('Courses','course-manager'));
define('TXT_CM_MENU_TAGS', __('Tags','course-manager'));
define('TXT_CM_MENU_ABOUT', __('About','course-manager'));
define('TXT_CM_MENU_STORE', __('Store','course-manager'));

// --- Courses Menu --- \\
define('TXT_CM_ADMIN_COURSE_SINGULAR', __('Course','course-manager'));
define('TXT_CM_ADMIN_COURSE_PLURAL', __('Courses','course-manager'));
define('TXT_CM_ADMIN_COURSE_NO_COURSES', __('You have no courses yet','course-manager'));
define('TXT_CM_ADMIN_COURSE_DAYS', __('days','course-manager'));
define('TXT_CM_ADMIN_COURSE_NAME', __('Course Name','course-manager'));
define('TXT_CM_ADMIN_COURSE_SPAN', __('Span','course-manager'));
define('TXT_CM_ADMIN_COURSE_PARTS', __('Parts','course-manager'));
define('TXT_CM_ADMIN_COURSE_STATUS', __('Status','course-manager'));
define('TXT_CM_ADMIN_COURSE_TAGS', __('Tags','course-manager'));

// --- Tags Menu --- \\


// --- Create/Edit Course --- \\
define('TXT_CM_EDIT_TITLE', __('You\'re editing ','course-manager'));
define('TXT_CM_CREATE_TITLE', __('You\'re creating a new course','course-manager'));
define('TXT_CM_EDIT_COURSENAME', __('Course name','course-manager'));
define('TXT_CM_EDIT_COURSEDESC', __('Course description','course-manager'));
define('TXT_CM_EDIT_COURSEPRICE', __('Course price (kr)','course-manager'));
define('TXT_CM_EDIT_COURSESPAN', __('Course span (days)','course-manager'));
define('TXT_CM_EDIT_SAVE', __('Save Course','course-manager'));
define('TXT_CM_EDIT_COURSEPARTS', __('Course parts','course-manager'));
define('TXT_CM_EDIT_TYPE', __('Type','course-manager'));
define('TXT_CM_EDIT_PART_NAME', __('Name','course-manager'));
define('TXT_CM_EDIT_PART_INDEX', __('Index','course-manager'));
define('TXT_CM_ADD_NEW_PART', __('Add New Part','course-manager'));
define('TXT_CM_EDIT_PARTS', __('Parts','course-manager'));
define('TXT_CM_EDIT_PART_TITLE', __('Title','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_VIDEO', __('Video Link','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_IMAGE', __('Image Link','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_DOWNLOAD', __('Download Link','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_QUESTIONS', __('Questions','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_QUESTION', __('Question','course-manager'));
define('TXT_CM_EDIT_SAVE_SUCCESS', __('The Course have successfully been saved','course-manager'));
define('TXT_CM_EDIT_SAVE_FAILURE', __('Something went wrong. Your Course updates/creation have not been saved','course-manager'));
define('TXT_CM_GENERATE_PAGES_SUCCESS', __('The course pages have successfully been generated','course-manager'));
define('TXT_CM_GENERATE_PAGES_FAILURE', __('ERROR: Some course pages were not generated correctly!','course-manager'));
define('TXT_CM_EDIT_NEW_COURSE_PART_TITLE', __('New course part','course-manager'));
define('TXT_CM_EDIT_NEW_PART_TITLE', __('New part','course-manager'));
define('TXT_CM_EDIT_NEW_PART_CONTENT', __('New part content','course-manager'));
define('TXT_CM_EDIT_CHANGE_TYPE_GENERAL_CONTENT', __('link_to_item','course-manager'));
define('TXT_CM_EDIT_CHANGE_TYPE_QUEST_CONTENT', __('{Question 1 text},','course-manager'));
define('TXT_CM_EDIT_ADD_NEW_QUESTION', __('Add New Question','course-manager'));
define('TXT_CM_EDIT_SAVE_BEFORE_NEW_PARTS', __('You need to save the course first to add new course parts','course-manager'));


// --- Course Pages --- \\
define('TXT_CM_PAGE_TYPE_NOT_SUPPORTED', __('Content type not supported yet','course-manager'));


// --- Admin Store --- \\
define('TXT_CM_STORE_IN_STORE_TITLE', __('Set what courses should appear in the store', 'course-manager'));
define('TXT_CM_STORE_NO_ACTIVE', __('You have no active courses, activate them in the Courses menu.', 'course-manager'));
define('TXT_CM_STORE_ACTIVATE_STORE', __('Activate the store', 'course-manager'));
define('TXT_CM_STORE_DEACTIVATE_STORE', __('Deactivate the store', 'course-manager'));
define('TXT_CM_STORE_PAGE_TITLE', __('Store', 'course-manager'));
define('TXT_CM_STORE_PAGE_NAME', __('course-store', 'course-manager'));
define('TXT_CM_STORE_ACTIVATED', __('You have activated the store, check your menus to make sure that it has been added where you like.', 'course-manager'));
define('TXT_CM_STORE_DEACTIVATED', __('You have deactivated the store.', 'course-manager'));


?>