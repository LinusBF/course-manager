<?php
$oCM = new CourseManager();

$aSettings = $oCM->getOptions();

$blStore = $aSettings['store_active'];
$aCoursesInStore = $aSettings['courses_in_store'];
$aAllActiveCourses = CmCourse::getAllActiveCourses();
?>
<h1><?php echo TXT_CM_MENU_STORE; ?></h1>


<?php
($blStore ? $sButtonText = TXT_CM_STORE_DEACTIVATE_STORE : $sButtonText = TXT_CM_STORE_ACTIVATE_STORE);

$sActivateStoreNonce = wp_create_nonce('cm_change_store_state');
echo sprintf('<a class="button button-primary alignright" href="?page=%s&action=%s&_wpnonce=%s">'.$sButtonText.'</a>',
	esc_attr($_REQUEST['page']), 'store_state', $sActivateStoreNonce);

?>

<h2><?php echo TXT_CM_STORE_IN_STORE_TITLE; ?></h2>

<?php if(count($aAllActiveCourses)): ?>
<form method="POST" action="?page=se">
	<table class="form-table">
		<tbody>
			<?php foreach ($aAllActiveCourses as $cmCourse): ?>
				<tr>
					<th scope="row"><label><?php echo $cmCourse->getCourseName(); ?></label></th>
					<td>
						<?php
							$sAttrName = "cm_course_".$cmCourse->getCourseID()."_in_store";
							echo "<input type='checkbox' name='$sAttrName' "
							     .(in_array($cmCourse->getCourseID(), $aCoursesInStore) ? 'checked' : '')
							     .">";
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>
<?php else: ?>
<p><?php echo TXT_CM_STORE_NO_ACTIVE; ?></p>

<?php endif; ?>