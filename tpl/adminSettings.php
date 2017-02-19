<?php
	$oCM = new CourseManager();

	$aSettings = $oCM->getOptions();

	$blStore = $aSettings['store_active'];
?>
<h1><?php echo TXT_CM_SETTINGS; ?></h1>

<form method="POST" action="?page=se">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label><?php echo "Setting Label"; ?></label></th>
				<td>
					<input class="regular-text" name="setting1" type="text" placeholder="Setting placeholder">
				</td>
			</tr>
		</tbody>
	</table>
</form>
