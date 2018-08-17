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
define('TXT_CM_STORE_SETTINGS', __('Store settings','course-manager'));
define('TXT_CM_ACTIVE', __('Active','course-manager'));
define('TXT_CM_NOT_ACTIVE', __('Not active','course-manager'));
define('TXT_CM_ACTIVATE', __('Activate','course-manager'));
define('TXT_CM_DEACTIVATE', __('Deactivate','course-manager'));
define('TXT_CM_CHOOSE', __('Choose','course-manager'));
define('TXT_CM_ID', __('ID','course-manager'));


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
define('TXT_CM_ADMIN_COURSE_DELETE_CONFIRM', __('Are you sure you want to delete this course and all its content?', 'course-manager'));
define('TXT_CM_ACTIVATE_FAILURE', __('Course could not be activated. Have you generated pages for it and set the store settings for it?','course-manager'));


// --- Settings Menu --- \\
define('TXT_CM_ADMIN_SETTINGS_NOT_SET', __('Not Set','course-manager'));
define('TXT_CM_CHIMP_TABLE_LISTS_TITLE', __('Name','course-manager'));
define('TXT_CM_CHIMP_TABLE_LISTS_DESC', __('Select a list to connect your costumers to','course-manager'));
define('TXT_CM_CHIMP_TABLE_GROUPS_DESC', __('Select a MailChimp group that the customers that want offers are put in','course-manager'));
define('TXT_CM_CHIMP_TABLE_TEMPLATE_DESC', __('Select a template that the purchase email will use','course-manager'));
define('TXT_CM_CHIMP_INCORRECT_KEY', __('The current API key does not work','course-manager'));


// --- Tags Menu --- \\


// --- Create/Edit Course --- \\
define('TXT_CM_EDIT_TITLE', __('You\'re editing ','course-manager'));
define('TXT_CM_CREATE_TITLE', __('You\'re creating a new course','course-manager'));
define('TXT_CM_EDIT_COURSENAME', __('Course name (52 characters)','course-manager'));
define('TXT_CM_EDIT_COURSEDESC', __('Course description<br>(265 characters)','course-manager'));
define('TXT_CM_EDIT_COURSESTOREDESC', __('Course description<br>(140 characters)','course-manager'));
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
define('TXT_CM_EDIT_PART_CONTENT_IMAGE', __('Image','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_DOWNLOAD', __('Download Link','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_QUESTIONS', __('Questions','course-manager'));
define('TXT_CM_EDIT_PART_CONTENT_QUESTION', __('Question','course-manager'));
define('TXT_CM_EDIT_SAVE_SUCCESS', __('The Course have successfully been saved','course-manager'));
define('TXT_CM_EDIT_SAVE_FAILURE', __('Something went wrong. Your Course updates/creation have not been saved','course-manager'));
define('TXT_CM_GENERATE_PAGES_SUCCESS', __('The course pages have successfully been generated','course-manager'));
define('TXT_CM_GENERATE_PAGES_FAILURE', __('ERROR: The course page could not be generated. Is the course active?','course-manager'));
define('TXT_CM_EDIT_NEW_COURSE_PART_TITLE', __('New course part','course-manager'));
define('TXT_CM_EDIT_NEW_PART_TITLE', __('New part','course-manager'));
define('TXT_CM_EDIT_NEW_PART_CONTENT', __('New part content','course-manager'));
define('TXT_CM_EDIT_CHANGE_TYPE_GENERAL_CONTENT', __('link_to_item','course-manager'));
define('TXT_CM_EDIT_CHANGE_TYPE_QUEST_CONTENT', __('{Question 1 text},','course-manager'));
define('TXT_CM_EDIT_ADD_NEW_QUESTION', __('Add New Question','course-manager'));
define('TXT_CM_EDIT_SAVE_BEFORE_NEW_PARTS', __('You need to save the course first to add new course parts','course-manager'));


// --- Course Pages --- \\
define('TXT_CM_PAGE_TYPE_NOT_SUPPORTED', __('Content type not supported yet','course-manager'));
define('TXT_CM_PAGE_SAVE_ANSWERS', __('Save answers','course-manager'));
define('TXT_CM_PAGE_PREV_PART', __('Previous','course-manager'));
define('TXT_CM_PAGE_NEXT_PART', __('Next','course-manager'));


// --- Admin Store --- \\
define('TXT_CM_STORE_IN_STORE_TITLE', __('Set what courses should appear in the store', 'course-manager'));
define('TXT_CM_STORE_NO_ACTIVE', __('You have no active courses, activate them in the Courses menu.', 'course-manager'));
define('TXT_CM_STORE_ACTIVATE_STORE', __('Activate the store', 'course-manager'));
define('TXT_CM_STORE_DEACTIVATE_STORE', __('Deactivate the store', 'course-manager'));
define('TXT_CM_STORE_PAGE_TITLE', __('Store', 'course-manager'));
define('TXT_CM_STORE_PAGE_NAME', __('course-store', 'course-manager'));
define('TXT_CM_STORE_ACTIVATED', __('You have activated the store, check your menus to make sure that it has been added where you like.', 'course-manager'));
define('TXT_CM_STORE_DEACTIVATED', __('You have deactivated the store.', 'course-manager'));
define('TXT_CM_STORE_CHANGED_COURSES_IN_STORE', __('Courses in the store have been updated!', 'course-manager'));
define('TXT_CM_STORE_SAVE_CHANGES', __('Save Changes', 'course-manager'));
define('TXT_CM_STORE_SAVE_SUCCESS', __('The store options for the course have successfully been saved','course-manager'));
define('TXT_CM_STORE_SAVE_FAILURE', __('Something went wrong. Your updates/creation have not been saved','course-manager'));
define('TXT_CM_STORE_ADD_IMAGE', __('Choose Image','course-manager'));
define('TXT_CM_STORE_SELECT_IMAGE', __('Select an image to use','course-manager'));
define('TXT_CM_STORE_COURSE_IMAGE', __('Course store image','course-manager'));
define('TXT_CM_STORE_COURSE_DISCOUNT', __('Course discount','course-manager'));
define('TXT_CM_STORE_BUY', __('Buy for','course-manager'));
define('TXT_CM_STORE_LEARN_MORE', __('Buy','course-manager'));
define('TXT_CM_STORE_FREE_LEARN_MORE', __('Start for free!','course-manager'));
define('TXT_CM_STORE_FREE', __('Free!','course-manager'));
define('TXT_CM_STORE_FREE_EMAIL', __('Free <br> Enter your email to start!','course-manager'));
define('TXT_CM_STORE_MORE_INFO', __('More info','course-manager'));
define('TXT_CM_STORE_SELECT_LADNING_PAGE', __('Choose a landing page','course-manager'));
define('TXT_CM_STORE_TABLE_PAGE_TITLE', __('Page Title','course-manager'));
define('TXT_CM_STORE_TABLE_CHOICE', __('Choice','course-manager'));
define('TXT_CM_STORE_ENTER_TOKEN', __('Use token','course-manager'));
define('TXT_CM_STORE_TOKEN_TITLE', __('Enter your token','course-manager'));
define('TXT_CM_STORE_GO_TO_YOUR_COURSES', __('Go to your courses','course-manager'));
define('TXT_CM_STORE_GO_TO_STORE', __('Go to the store','course-manager'));
define('TXT_CM_STORE_GO_TO_ANSWERS', __('Your answers','course-manager'));
define('TXT_CM_STORE_GO_TO_COURSE', __('Go to course','course-manager'));
define('TXT_CM_STORE_SWITCH_TOKEN', __('Use another token?','course-manager'));
define('TXT_CM_STORE_CHECKOUT_DESCRIPTION', __('Buy course','course-manager'));
define('TXT_CM_STORE_CHECKOUT_BUTTON_TEXT', __('Pay with card','course-manager'));
define('TXT_CM_STORE_CHECKOUT_SUCCESS', __('Thank you for your purchase, click on the course to start it!','course-manager'));
define('TXT_CM_STORE_CHECKOUT_CARD_DECLINED', __('Your card was declined, please try with another one or contact support.','course-manager'));
define('TXT_CM_STORE_NO_COURSES', __('There are currently no courses available :(','course-manager'));
define('TXT_CM_STORE_EXPIRED_COURSE', __('Ended','course-manager'));


// --- Landing Pages --- \\
define('TXT_CM_LANDING_PAGE_MODAL_HEADER', __('Get Course','course-manager'));
define('TXT_CM_LANDING_PAGE_EMAIL', __('Enter email','course-manager'));
define('TXT_CM_LANDING_PAGE_SEND_PROMOTIONS', __('Send me offers for other courses','course-manager'));


// --- User Page --- \\
define('TXT_CM_USER_PAGE_NAME', __('course-account', 'course-manager'));
define('TXT_CM_USER_PAGE_TITLE', __('Account', 'course-manager'));
define('TXT_CM_USER_PAGE_BACK_TO_COURSE', __('To question', 'course-manager'));
define('TXT_CM_USER_Q_NOT_ANSWERED', __('You haven\'t answered these questions yet', 'course-manager'));

// --- Mailchimp --- \\
define('TXT_CM_MC_SUBJECT', __('Thank you for your purchase!', 'course-manager'));
define('TXT_CM_MC_FROM_NAME', __('Course Manager', 'course-manager'));

?>