<?php
$oCM = new CourseManager();
$oStore = new CmStore();

$blStateChange = false;
$sStateChangeMessage = "";

if (isset($_GET['action']) && $_GET['action'] == "store_state"){
	if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'cm_change_store_state')){

		$blStateChange = true;

		if ($oStore->isStoreActive()){
			$oStore->deactivateStore();
			$sStateChangeMessage = TXT_CM_STORE_DEACTIVATED;
		}
		else{
			$oStore->activateStore();
			$sStateChangeMessage = TXT_CM_STORE_ACTIVATED;
		}
	}
	else{
		die("Stop messing with it...");
	}
}

if (isset($_GET['action']) && $_GET['action'] == "store_courses"){
	if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'cm_save_store_courses')){
		$iIndex = 0;

		while (isset($_POST["cm_course_".$iIndex."_id"])){

			if (isset($_POST["cm_course_".$iIndex."_in_store"]) && $_POST["cm_course_".$iIndex."_in_store"] == "yes"){
				$oStore->addCourseToStore((int) $_POST["cm_course_".$iIndex."_id"]);
			}
			else{
				$oStore->removeCourseFromStore((int) $_POST["cm_course_".$iIndex."_id"]);
			}

			$iIndex++;
		}
	}
	else{
		die("Stop messing with it...");
	}
}

$aSettings = $oCM->getOptions();

$blStore = $aSettings['store_active'];
$aCoursesInStore = $aSettings['courses_in_store'];
$aAllActiveCourses = CmCourse::getAllActiveCourses();
?>
<h1><?php echo TXT_CM_MENU_STORE; ?></h1>

<?php if ($blStateChange): ?>
	<div class="updated notice is-dismissible"><?php echo $sStateChangeMessage; ?></div>
<?php endif; ?>


<?php
($blStore ? $sButtonText = TXT_CM_STORE_DEACTIVATE_STORE : $sButtonText = TXT_CM_STORE_ACTIVATE_STORE);

$sActivateStoreNonce = wp_create_nonce('cm_change_store_state');
echo sprintf('<a class="button button-primary alignright" href="?page=%s&action=%s&_wpnonce=%s">'.$sButtonText.'</a>',
	esc_attr($_REQUEST['page']), 'store_state', $sActivateStoreNonce);

?>

<h2><?php echo TXT_CM_STORE_IN_STORE_TITLE; ?></h2>

<?php if(count($aAllActiveCourses)): ?>
<?php
	$sSaveChoicesNonce = wp_create_nonce('cm_save_store_courses');
	echo sprintf('<form method="POST" action="?page=%s&action=%s&_wpnonce=%s">', esc_attr($_REQUEST['page']), 'store_courses', $sSaveChoicesNonce);
?>
	<table class="form-table">
		<tbody>
			<?php foreach ($aAllActiveCourses as $iKey => $cmCourse): ?>
				<tr>
					<th scope="row"><label><?php echo $cmCourse->getCourseName(); ?></label></th>
					<td>
						<?php
							echo '<input type="hidden" value="'.$cmCourse->getCourseID().'" name="cm_course_'.$iKey.'_id">';

							$sAttrName = "cm_course_".$iKey."_in_store";
							echo "<input type='checkbox' name='$sAttrName' value='yes'"
							     .(in_array($cmCourse->getCourseID(), $aCoursesInStore) ? 'checked' : '')
							     .">";
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class='submit'>
		<input type='submit' name='submit' id='cm_submit' class='button button-primary' value='<?php echo TXT_CM_EDIT_SAVE?>'>
	</div>
</form>
<?php else: ?>
<p><?php echo TXT_CM_STORE_NO_ACTIVE; ?></p>

<?php endif; ?>