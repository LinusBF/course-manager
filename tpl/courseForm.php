<?php
/**
 * courseForm.php
 * 
 * The html form for editing and creating courses
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
 * Prints the html form for editing and creating courses
 *
 * @param int $iCourseID
 *
 * @return null
 */
function getCourseForm($iCourseID = null)
{
	if (isset($iCourseID)) {
		$oCourse = CmCourse::getCourseByID($iCourseID,true);
	}

	?>
	<form id = "cm_course_form" method='post' action='<?php
	$aUri = explode("?", $_SERVER["REQUEST_URI"]);
	echo reset($aUri) . "?page=cm_courses";
	?>'>
		<input type='hidden' name='action' value='<?php echo (isset($oCourse) ? 'edit' : 'create'); ?>'>
		<input type='hidden' name='_wpnonce' value='<?php echo wp_create_nonce('cm_create_edit_course'); ?>'>
		<input type="hidden" name="cm_nr_of_courseparts" value="<?php echo (isset($oCourse) ? $oCourse->getNrCourseParts() : 0) ?>">
		<?php 
			if (isset($oCourse)) {
				echo "<input type='hidden' name='course' value='".absint($iCourseID)."'>";
			}
		?>
		<table id='cm_edit_course' class='form-table'>
			<tbody>
				<tr>
					<th scope='row'>
						<label class="cm_table_label" for='cm_coursename'><?php echo TXT_CM_EDIT_COURSENAME; ?></label>
					</th>
					<td>
						<input name='cm_coursename' type='text' id='cm_edit_course_name' class='regular-text'
						value='<?php echo (isset($oCourse) ? $oCourse->getCourseName() : ''); ?>'>
					</td>
				</tr>
				<tr>
					<th scope='row'>
						<label class="cm_table_label" for='cm_coursespan'><?php echo TXT_CM_EDIT_COURSESPAN; ?></label>
					</th>
					<td>
						<input name='cm_coursespan' type='number' id='cm_edit_course_span'
						value='<?php echo (isset($oCourse) ? $oCourse->getCourseSpan() : ''); ?>'>
					</td>
				</tr>
				<?php
				if (isset($oCourse)) {

					?>
					<tr>
						<th scope='row'>
							<label class="cm_table_label"><?php echo TXT_CM_EDIT_COURSEPARTS; ?></label>
							<button id='cm_btn' class='button-secondary' type='button'
									data-nonce="<?php echo wp_create_nonce("cm_add_new_course"); ?>">
								<?php echo TXT_CM_ADD_NEW; ?>
							</button>
						</th>
					</tr>
					<?php
				} else{
					?>
					<tr>
						<th class="cm_th" id="cm_save_b4_add_con" scope='row'>
							<label class="cm_table_label" id="cm_save_b4_add"><?php echo TXT_CM_EDIT_SAVE_BEFORE_NEW_PARTS; ?></label>
						</th>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>

		<div id='cm_coursePart_con' class='widefat'>
			<?php
				if (isset($oCourse)) {
					$oCourse->printListRep();
				}
			?>
		</div>

		<div class='submit'>
			<input type='submit' name='submit' id='cm_submit' class='button button-primary' value='<?php echo TXT_CM_EDIT_SAVE?>'>
		</div>
	</form>
	<?php
	//echo CM_URLPATH.'tpl/editCourse.php';
	/* DEBUGGING
	$oDebugCourse = CmCourse::getCourseByID(1, true);
	var_dump($oDebugCourse);
	*/
}


/**
 * Takes all the POST data from the Form and saves the data to the database
 *
 * @return null
 */
function saveCourseChanges(){

	if (isset($_POST['course'])){
		$oNewCourse = CmCourse::getCourseByID($_POST['course'], true);
	} else{
		$oNewCourse = CmCourse::create();
	}

	if(!$oNewCourse->setCourseName($_POST['cm_coursename'])){
		return false;
	}
	$oNewCourse->setCourseSpan((int) $_POST['cm_coursespan']);

	if (!isset($_POST['course'])){
		$iCId = $oNewCourse->save(false);
		$oNewCourse = CmCourse::getCourseByID($iCId, true);
	} else{
		$iCId = $_POST['course'];
	}

	/**Go through every CoursePart and it's respective Parts and save to $oNewCourse*/
	$iCPIndex = 0;
	$aNewCParts = [];

	/** Looping through every CoursePart */
	while(isset($_POST['cm_CP_'.$iCPIndex.'_ID'])){

		$sCPNamePrefix = 'cm_CP_'.$iCPIndex;
		$iCPId = (int) $_POST[$sCPNamePrefix.'_ID'];
		$iDelCP = (int) $_POST[$sCPNamePrefix.'_del'];

		if ($iCPId > 0){
			$oNewCoursePart = CmCoursePart::getCoursePartByID($iCPId, true);
			if($iDelCP === 1){
				$oNewCoursePart->deleteCoursePart();
				$iCPIndex++;
				continue;
			}
		}else {
			if($iDelCP === 1){
				$iCPIndex++;
				continue;
			}else {
				$oNewCoursePart = CmCoursePart::create();
				$oNewCoursePart->setCourseID($iCId);
			}
		}

		$oNewCoursePart->setCoursePartName($_POST[$sCPNamePrefix."_name"]);
		$oNewCoursePart->setCourseIndex(((int) $_POST[$sCPNamePrefix."_index"] - 1));

		if ($iCPId < 0){
			$iCPId = $oNewCoursePart->save(false);
			$oNewCoursePart = CmCoursePart::getCoursePartByID($iCPId, true);
		}

		$iPIndex = 0;
		$aNewParts = [];

		/** Looping through every Part */
		while(isset($_POST['cm_P_'.$iCPIndex."_".$iPIndex.'_ID'])){

			$sPNamePrefix = 'cm_P_'.$iCPIndex."_".$iPIndex;
			$iPId = (int) $_POST[$sPNamePrefix.'_ID'];
			$iDelP = (int) $_POST[$sPNamePrefix.'_del'];

			if ($iPId > 0){
				$oNewPart = CmPart::getPartByID($iPId);
				if($iDelP === 1){
					$oNewPart->deletePart();
					$iPIndex++;
					continue;
				}
			} else{
				if($iDelP === 1){
					$iPIndex++;
					continue;
				}else {
					$oNewPart = CmPart::create();
				}
			}

			$oNewPart->setCoursePartID($iCPId);
			$oNewPart->setTitle($_POST[$sPNamePrefix."_name"]);
			$oNewPart->setType($_POST[$sPNamePrefix."_type"]);
			$sNewContent = handle_part_content($_POST[$sPNamePrefix."_type"], $sPNamePrefix);
			if ($sNewContent !== false) {
				$oNewPart->setContent($sNewContent);
			}
			$oNewPart->setIndex(((int)$_POST[$sPNamePrefix."_index"] - 1));

			array_push($aNewParts, $oNewPart);
			$iPIndex++;
		}

		$oNewCoursePart->setParts($aNewParts);

		array_push($aNewCParts, $oNewCoursePart);
		$iCPIndex++;
	}

	if (count($aNewCParts) > 0){
		$oNewCourse->setCourseParts($aNewCParts);
	}

	if($result = $oNewCourse->save(true)){
		return true;
	} else{
		return false;
	}

}

/**
 * Handles the different types of Part content and returns a clean string to be stored in the DB
 *
 * @param string $sType
 * @param string $sPartPrefix
 *
 * @return string - The content to be stored in the database
 */
function handle_part_content($sType, $sPartPrefix){

	//Handle simple cases (text, image link, video link and download link)
	if($sType == "text"){
		$sContent = stripslashes($_POST[$sPartPrefix."_content_simple"]);
		return $sContent;
	}
	elseif ($sType == "image" || $sType == "video" || $sType == "download"){
		$sContent = $_POST[$sPartPrefix."_content_simple"];
		return $sContent;
	}
	//Handle questions
	elseif($sType == "question"){
		$i = 0;
		$aQuests = [];
		while(isset($_POST[$sPartPrefix."_content_quest_".$i])){
			array_push($aQuests, $_POST[$sPartPrefix."_content_quest_".$i]);
			$i++;
		}

		$sContent = CmPart::parse_quest($aQuests);

		return $sContent;
	}

	return false;
}
?>