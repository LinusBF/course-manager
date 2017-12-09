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
 * Prints the html form for editing the store settings for the course
 *
 * @param int $iCourseId
 *
 * @return null
 */

function genStoreSettingsForm($iCourseId){

	$aOptions = CmCourseStoreHandler::getStoreOptionsForCourse($iCourseId);

	$oLandingPageTable = new LandingPageTable();

	$sExplodedUrl = explode("?", $_SERVER["REQUEST_URI"]);
	$sFormAction = reset($sExplodedUrl) . "?page=cm_courses";

	?>
	<form id = "cm_course_setting_form" method='post' action='<?php echo $sFormAction; ?>'>
		<input type='hidden' name='action' value='set_settings'>
		<input type='hidden' name='_wpnonce_cm' value='<?php echo wp_create_nonce('cm_store_settings_set'); ?>'>
		<input type='hidden' name='course' value="<?php echo $iCourseId; ?>">
		<input type="hidden" name="settings_modified" value="1">
		<input type="hidden" name="old_landing_page" value="<?php echo $aOptions['landing_page']; ?>">

		<table id='cm_edit_course' class='form-table'>
			<tbody>
				<tr>
					<th scope='row'>
						<label class="cm_table_label" for='cm_course_image'><?php echo TXT_CM_STORE_COURSE_IMAGE; ?></label>
					</th>
					<td>
						<?php

						wp_enqueue_media();

						?>
						<div class='image-preview-wrapper'>
							<img id='image-preview' src='<?php echo wp_get_attachment_url( $aOptions['store_image'] ); ?>' height='100'>
						</div>
						<input id="upload_image_button" type="button" class="button add_media" value="<?php echo TXT_CM_STORE_ADD_IMAGE; ?>" />
						<input type='hidden' name='store_image' id='image_attachment_id' value='<?php echo $aOptions['store_image']; ?>'>
					</td>
				</tr>
				<tr>
					<th scope='row'>
						<label class="cm_table_label" for='cm_course_desc'><?php echo TXT_CM_EDIT_COURSEDESC; ?></label>
					</th>
					<td>
						<div class="textarea-wrap">
							<textarea name="store_description"><?php echo $aOptions['store_description']; ?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<th scope='row'>
						<label class="cm_table_label" for='cm_course_desc'><?php echo TXT_CM_STORE_COURSE_DISCOUNT; ?></label>
					</th>
					<td>
						<div>
							<input type="number" min="0" max="100" name="current_discount" value="<?php echo $aOptions['current_discount']; ?>">%
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php
			$oLandingPageTable->print_landing_page_table();
		?>

		<div class='submit'>
			<input type='submit' name='submit' id='cm_submit' class='button button-primary' value='<?php echo TXT_CM_EDIT_SAVE?>'>
		</div>
	</form>
<?php
}

function saveOptions(){
	$oStoreHandler = new CmCourseStoreHandler();

	if (!isset($_POST['course'])){
		return false;
	}

	$aOptions = $oStoreHandler->getStoreOptionsForCourse($_POST['course']);

	foreach (array_keys($aOptions) as $sKey){
		if(isset($_POST[$sKey])){
			$aOptions[$sKey] = $_POST[$sKey];
		}
	}

	$blSetCheck = $oStoreHandler->setStoreOptions($_POST['course'], $aOptions);

	if($blSetCheck && $_POST['landing_page'] != $_POST['old_landing_page']){
		configLandingPage($_POST['old_landing_page'], false);
		configLandingPage($_POST['landing_page'], true);
	}

	return $blSetCheck;
}

function configLandingPage($iPageID, $blActive){
	global $wpdb;

	$wpdb_table = $wpdb->prefix . 'posts';
	$wpdb_data = array(
		'post_excerpt' => ($blActive ? 'landing_page' : ''),
	);
	$wpdb_where = array(
		'ID' => $iPageID,
		'post_type' => 'page',
	);

	return $wpdb->update($wpdb_table, $wpdb_data, $wpdb_where);
}

?>