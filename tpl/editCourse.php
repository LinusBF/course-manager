<?php
/**
 * editCourse.php
 * 
 * Edit a existing course or make a new one
 * 
 * PHP versions 5
 * 
 * @category  CourseManager
 * @package   CourseManager
 * @author    Linus Bein Fahlander <linus.webdevelopment@gmail.com>
 * @copyright 2016-2016 Linus Bein Fahlander
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $Id$
 * @link      Coming soon
 */

?>
<div class="wrap">
	<h1>
		<?php
		if (isset($_GET['course'])) {
			echo TXT_CM_EDIT_TITLE . " " . CmCourse::getCourseByID($_GET['course'])->getCourseName();
		} else {
			echo TXT_CM_CREATE_TITLE;
		}
		?>
	</h1>
	<?php
		require_once 'courseForm.php';
		(isset($_GET['course']) ? getCourseForm($_GET['course']) : getCourseForm());
	?>
</div>
<?php



?>