<?php
/**
 * adminCourses.php
 * 
 * Displays all the created courses
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


//check for WP_List_Table
if (!class_exists("WP_List_Table")) {
	require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

/**
* 
*/
class CourseList extends WP_List_Table
{
	
	function __construct()
	{
		parent::__construct([
			'singular' => TXT_CM_ADMIN_COURSE_SINGULAR,
			'plural' => TXT_CM_ADMIN_COURSE_PLURAL,
			'ajax' => false
			]);
	}


	/**
	 * Returns an array with CmCourse Objects
	 *
	 * @param int $iPage
	 * @param int $iPerPage
	 *
	 * @return array
	 */
	public static function getCourses($iPage = 1,$iPerPage = 10)
	{
		return CmCourse::getCourses($iPage,$iPerPage);
	}


	/**
	* Deletes a Course from the DB
	*
	* @param int $iID
	*/
	public static function deleteCourse($iID)
	{
		return CmCourse::deleteCourse($iID);
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
		return CmCourse::changeStatus($iID);
	}


	public static function record_count()
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."cm_courses";

		return $wpdb->get_var($sql);
	}


	/** Returns what should be displayed when there are no Courses */
	public function no_items()
	{
		echo TXT_CM_ADMIN_COURSE_NO_COURSES;
	}

	/**
	 * Returns the format and value for the span column
	 *
	 * @param CmCourse $oCourse
	 *
	 * @return array
	 */
	public function column_name($oCourse){
		$sDeleteNonce = wp_create_nonce('cm_delete_course');
		$sEditNonce = wp_create_nonce('cm_edit_course');
		$sGeneratePagesNonce = wp_create_nonce('cm_gen_pages');
		$sStoreSettingsNonce = wp_create_nonce('cm_store_set');

		$sTitle = sprintf("<strong><a href='?page=%s&action=%s&course=%s&_wpnonce=%s'>",esc_attr($_REQUEST['page']),
				'edit', absint($oCourse->getCourseID()),$sEditNonce).$oCourse->getCourseName()."</a></strong>";

		$aActions = [
			"delete" => sprintf('<a class="cm_delete_course" href="?page=%s&action=%s&course=%s&_wpnonce=%s">'.TXT_CM_DELETE.'</a>',
				esc_attr($_REQUEST['page']), 'delete', absint($oCourse->getCourseID()), $sDeleteNonce),
			"gen_pages" => sprintf('<a href="?page=%s&action=%s&course=%s&_wpnonce=%s">'.TXT_CM_GENERATE_PAGES.'</a>',
				esc_attr($_REQUEST['page']), 'gen_pages', absint($oCourse->getCourseID()), $sGeneratePagesNonce),
			"store_settings" => sprintf('<a href="?page=%s&action=%s&course=%s&_wpnonce=%s">'.TXT_CM_STORE_SETTINGS.'</a>',
				esc_attr($_REQUEST['page']), 'store_settings', absint($oCourse->getCourseID()), $sStoreSettingsNonce)
		];

		return $sTitle.$this->row_actions($aActions);
	}

	/**
     * Returns the format and value for the span column
     *
     * @param CmCourse $oCourse
     *
     * @return string
     */
	public function column_span($oCourse){

		$sTitle = $oCourse->getCourseSpan()." ".TXT_CM_ADMIN_COURSE_DAYS;

		return $sTitle;
	}


	/**
     * Returns the format and value for the parts column
     *
     * @param CmCourse $oCourse
     *
     * @return string
     */
	public function column_courseParts($oCourse){

		$sTitle = $oCourse->getNrCourseParts();

		return $sTitle;
	}


	/**
     * Returns the format and value for the status column
     *
     * @param CmCourse $oCourse
     *
     * @return string
     */
	public function column_status($oCourse){

		$blCheck = $oCourse->getCourseStatus();

		$sStatusNonce = wp_create_nonce('cm_change_course_status');

		if($blCheck) {
			$sTitle = TXT_CM_ACTIVE;
			$sChangeLink = TXT_CM_DEACTIVATE;
		} else{
			$sTitle = TXT_CM_NOT_ACTIVE;
			$sChangeLink = TXT_CM_ACTIVATE;
		}

		$aActions = [
			"status" => sprintf('<a href="?page=%s&action=%s&course=%s&_wpnonce=%s">'.$sChangeLink.'</a>',
				esc_attr($_REQUEST['page']), 'status', absint($oCourse->getCourseID()), $sStatusNonce)
		];

		return $sTitle.$this->row_actions($aActions);
	}


	/**
     * Returns the format and value for the tags column
     *
     * @param CmCourse $oCourse
     *
     * @return string
     */
	public function column_tags($oCourse){

		$sTitle = $oCourse->getCourseTagList();

		return $sTitle;
	}


	/**
     * Returns the default format and value for a column if no specific functions is defined
     *
     * @param CmCourse $oCourse, string $column_name
     *
     * @return string
     */
	public function column_default($oCourse, $column_name){
		return print_r($oCourse, true);
	}


	/**
     * Returns the format and value for the checkbox column
     *
     * @param CmCourse $oCourse
     *
     * @return string
     */
	public function column_cb($oCourse){
		return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $oCourse->getCourseID());
	}


	public function get_columns(){
		$aColumns = [
			'cb' => '<input type="checkbox" />',
			'name' => TXT_CM_ADMIN_COURSE_NAME,
			'span' => TXT_CM_ADMIN_COURSE_SPAN,
			'courseParts' => TXT_CM_ADMIN_COURSE_PARTS,
			'status' => TXT_CM_ADMIN_COURSE_STATUS,
			'tags' => TXT_CM_ADMIN_COURSE_TAGS
		];

		return $aColumns;
	}


	public function get_sortable_columns()
	{
		$aSortable_columns = array(
			"name" => array('name', true),
		);

		return $aSortable_columns;
	}


	public function get_bulk_actions()
	{
		$aActions = array(
			"bulk-delete" => TXT_CM_DELETE,
		);

		return $aActions;
	}


	public function prepare_items()
	{
		$aColumns = $this->get_columns();
		$aHidden = array();
		$aSortable = $this->get_sortable_columns();

		$this->_column_headers = array($aColumns,$aHidden,$aSortable);

		$this->process_bulk_action();

		$iPerPage = $this->get_items_per_page("courses_per_page", 10);
		$iCurrPage = $this->get_pagenum();
		$iTotalItems = self::record_count();

		$this->set_pagination_args([
			'total_items' => $iTotalItems,
			'per_page' => $iPerPage
		]);

		$this->items = self::getCourses($iCurrPage, $iPerPage);
	}


	public function process_bulk_action()
	{
		if ($this->current_action() === 'delete') {
			$sNonce = esc_attr($_REQUEST['_wpnonce']);

			if (!wp_verify_nonce($sNonce,'cm_delete_course')) {
				die('Stop messing with it...');
			} else{
				self::deleteCourse(absint($_GET['course']));
				wp_redirect(add_query_arg(array(
					"page" => "cm_courses"
				), explode('?', $_SERVER['REQUEST_URI'], 2)[0]));
			}
		}

		if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
			|| (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete'))
		{
			$aDeleteIDs = esc_sql($_POST['bulk-delete']);

			foreach ($aDeleteIDs as $iID) {
				self::deleteCourse($iID);
			}

			wp_redirect(add_query_arg(array(
				"page" => "cm_courses"
			), explode('?', $_SERVER['REQUEST_URI'], 2)[0]));
		}
	}


	/**
	 * Prints the table with all the courses in the database
	 *
	 * @param null $sDialog
	 *
	 * @return null
	 */
	public function printCourseList($sDialog = null)
	{
		?><div class="wrap">
			<h1><?php echo TXT_CM_MENU_COURSES;
			$sNewCourseNonce = wp_create_nonce('cm_new_course');
			echo sprintf('<a class="add-new-h2" href="?page=%s&action=%s&_wpnonce=%s">'.TXT_CM_ADD_NEW.'</a>',
				esc_attr($_REQUEST['page']), 'create', $sNewCourseNonce);
			?>
			</h1>
			<?php if(isset($sDialog)){
				echo "<h3 class='cm_result cm_result_success'>".$sDialog."</h3>";
			}?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->prepare_items();
								$this->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div><?php
	}
}

if (isset($_GET['action']) && ($_GET['action'] === 'edit' || $_GET['action'] === 'create')) { //Include the form for creating/editing a course
	$sNonce = $_GET['_wpnonce'];

	if (wp_verify_nonce($sNonce,'cm_edit_course') || wp_verify_nonce($sNonce,'cm_new_course')) {
		require 'editCourse.php';
	} else{
		die('Stop messing with it...');
	}
} else if(isset($_POST['action']) && ($_POST['action'] === 'edit' || $_POST['action'] === 'create')){ //Save the course data
	$sNonce = $_POST['_wpnonce'];

	if (wp_verify_nonce($sNonce,'cm_create_edit_course')) {
		require_once 'courseForm.php';

		$blSaveCheck = saveCourseChanges();

		$oCourseList = new CourseList();

		if ($blSaveCheck){
			$oCourseList->printCourseList(TXT_CM_EDIT_SAVE_SUCCESS);
		} else{
			$oCourseList->printCourseList(TXT_CM_EDIT_SAVE_FAILURE);
		}



	} else{
		die('Stop messing with it...');
	}
} else if(isset($_GET['action']) && $_GET['action'] === 'gen_pages' && isset($_GET['course'])){ //Generate pages for the course
	if (wp_verify_nonce($_GET['_wpnonce'],'cm_gen_pages')) {
		$oPageBuilder = new CmPageBuilder();
		$oCourse = CmCourse::getCourseByID($_GET['course'], true);
		$blCreateCheck = $oPageBuilder->createCoursePages($oCourse);

		$oCourseList = new CourseList();

		if ($blCreateCheck !== false){
			$oCourseList->printCourseList(TXT_CM_GENERATE_PAGES_SUCCESS);
		} else{
			$oCourseList->printCourseList(TXT_CM_GENERATE_PAGES_FAILURE);
		}

	} else{
		die('Stop messing with it...');
	}
} else if(isset($_GET['action']) && $_GET['action'] === 'store_settings' && isset($_GET['course'])){ //Generate pages for the course
	if (wp_verify_nonce($_GET['_wpnonce'],'cm_store_set')) {
		require "storeSettings.php";

		genStoreSettingsForm($_GET['course']);

	} else{
		die('Stop messing with it...');
	}
} else if(isset($_POST['action']) && $_POST['action'] === 'set_settings'){ //Save the store course options
	$sNonce = $_POST['_wpnonce_cm'];

	if (wp_verify_nonce($sNonce,'cm_store_settings_set')) {
		require_once 'storeSettings.php';

		$blSaveCheck = saveOptions();
		$oCourseList = new CourseList();

		if ($blSaveCheck){
			$oCourseList->printCourseList(TXT_CM_STORE_SAVE_SUCCESS);
		} else{
			$oCourseList->printCourseList(TXT_CM_STORE_SAVE_FAILURE);
		}

	} else{
		die('Stop messing with it...');
	}
}  else if(isset($_GET['action']) && $_GET['action'] === 'status'){ //Change the active state of the course
	$sNonce = $_REQUEST['_wpnonce'];

	if (!wp_verify_nonce($sNonce,'cm_change_course_status')) {
		die('Stop messing with it...');
	} else{

		$oCourseList = new CourseList();

		if(CmCourse::checkActivate(absint( $_GET['course'] ))) {
			$oCourseList::changeStatus( absint( $_GET['course'] ) );
			$oPageBuilder = new CmPageBuilder();
			$oPageBuilder->updateCoursePagesStatus( $_GET['course'] );
			wp_redirect(add_query_arg(array(
				"page" => "cm_courses"
			), explode('?', $_SERVER['REQUEST_URI'], 2)[0]));
		} else{
			$oCourseList->printCourseList(TXT_CM_ACTIVATE_FAILURE);
		}

	}

	if (wp_verify_nonce($sNonce,'cm_store_settings_set')) {
		require_once 'storeSettings.php';

		$blSaveCheck = saveOptions();
		$oCourseList = new CourseList();

		if ($blSaveCheck){
			$oCourseList->printCourseList(TXT_CM_STORE_SAVE_SUCCESS);
		} else{
			$oCourseList->printCourseList(TXT_CM_STORE_SAVE_FAILURE);
		}

	} else{
		die('Stop messing with it...');
	}
} else{

	$oCourseList = new CourseList();
	$oCourseList->printCourseList();
}
?>
