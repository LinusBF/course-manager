<?php
	$oCM = new CourseManager();

	$aSettings = $oCM->getOptions();

?>
<div class="wrap">
	<h1><?php echo TXT_CM_SETTINGS; ?></h1>

	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Export Settings' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.' ); ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="export_settings" /></p>
					<p>
						<?php wp_nonce_field( 'cm_export_nonce', 'cm_export_nonce' ); ?>
						<?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Import Settings' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.' ); ?></p>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="file" name="import_file"/>
					</p>
					<p>
						<input type="hidden" name="cm_action" value="import_settings" />
						<?php wp_nonce_field( 'cm_import_nonce', 'cm_import_nonce' ); ?>
						<?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .metabox-holder -->

	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Stripe Settings' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Enter your public and secret stripe keys to enable payment.' ); ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="stripe_settings" /></p>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="stripe_public"><?php _e('Public key') ?></label></th>
							<td>
								<input name="cm_stripe_public" type="text" id="stripe_public"
								       value="<?php echo ($aSettings['stripe']['publishable_key'] !== -1 ? $aSettings['stripe']['publishable_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="stripe_secret"><?php _e('Secret key') ?></label></th>
							<td>
								<input name="cm_stripe_secret" type="text" id="stripe_secret"
								       value="<?php echo ($aSettings['stripe']['secret_key'] !== -1 ? $aSettings['stripe']['secret_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
					</table>
					<p>
						<?php wp_nonce_field( 'cm_stripe_nonce', 'cm_stripe_nonce' ); ?>
						<?php submit_button( __( 'Update keys' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div>
	</div>
</div>