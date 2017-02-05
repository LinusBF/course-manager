<?php
/**
 * database.define.php
 * 
 * The database varibles defines file.
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

global $wpdb;

define('DB_CM_COURSES', $wpdb->prefix . 'cm_courses');
define('DB_CM_COURSE_PARTS', $wpdb->prefix . 'cm_course_parts');
define('DB_CM_PARTS', $wpdb->prefix . 'cm_parts');
define('DB_CM_TAGS', $wpdb->prefix . 'cm_tags');
define('DB_CM_REL_TAG_COURSE', $wpdb->prefix . 'cm_rel_tag_course');
define('DB_CM_USERS', $wpdb->prefix . 'cm_users');
define('DB_CM_USER_ENTITLEMENTS', $wpdb->prefix . 'cm_user_entitlements');
define('DB_CM_USER_ANSWERS', $wpdb->prefix . 'cm_user_answers');
define('DB_CM_USER_META', $wpdb->prefix . 'cm_user_meta');