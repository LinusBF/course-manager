<?php
	$oCM = new CourseManager();

	$aSettings = $oCM->getOptions();

?>
<div class="wrap">
	<h1><?php echo TXT_CM_SETTINGS; ?></h1>

	<!-- STRIPE -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php _e( 'Stripe Settings' ); ?></span></h2>
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

	<!-- MAILCHIMP -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php _e( 'MailChimp Settings' ); ?></span></h2>
			<div class="inside">
				<p><?php _e( 'Enter your API key to enable email lists with MailChimp.' ); ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="mailchimp_settings" /></p>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="cm_mail_chimp_key"><?php _e('API key') ?></label></th>
							<td>
								<input name="cm_mail_chimp_key" type="text" id="mailchimp_key"
								       value="<?php echo ($aSettings['mail_chimp']['api_key'] !== -1 ? $aSettings['mail_chimp']['api_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
					</table>
					<?php if($aSettings['mail_chimp']['api_key'] !== -1 && !CmMailController::checkApiKey()): ?>
						<div class="notice inline notice-warning notice-alt">
							<p><?php echo TXT_CM_CHIMP_INCORRECT_KEY; ?></p>
						</div>
					<?php endif;?>
					<p>
						<?php wp_nonce_field( 'cm_mailchimp_nonce', 'cm_mailchimp_nonce' ); ?>
						<?php submit_button( __( 'Update key' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
			<?php if($aSettings['mail_chimp']['api_key'] !== -1 && CmMailController::checkApiKey()): ?>
			<div id="cm_mc_lists" class="cm_mc_list inside">
				<h3><span><?php _e('Lists') ?></span></h3>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="mailchimp_list_settings" /></p>
					<?php
						$oListTable = new MailChimpTable();
						$oListTable->print_table();
					?>
					<p>
						<?php wp_nonce_field( 'cm_mailchimp_list_nonce', 'cm_mailchimp_list_nonce' ); ?>
						<?php submit_button( __( 'Select list' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
			<?php endif; ?>
			<?php if($aSettings['mail_chimp']['api_key'] !== -1 && $aSettings['mail_chimp']['list_id'] !== -1): ?>
				<div id="cm_mc_templates" class="cm_mc_list inside">
					<h3><span><?php _e('Email Template') ?></span></h3>
					<form method="post">
						<p><input type="hidden" name="cm_action" value="mailchimp_template_settings" /></p>
						<?php
						$oListTable = new MailChimpTable("template");
						$oListTable->print_table();
						?>
						<p>
							<?php wp_nonce_field( 'cm_mailchimp_template_nonce', 'cm_mailchimp_template_nonce' ); ?>
							<?php submit_button( __( 'Select template' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			<?php endif; ?>
			<?php if($aSettings['mail_chimp']['list_id'] !== -1): ?>
				<div id="cm_mc_groups" class="cm_mc_list inside">
					<h3><span><?php _e('Groups (Optional)') ?></span></h3>
					<form method="post">
						<p><input type="hidden" name="cm_action" value="mailchimp_group_settings" /></p>
						<?php
							$oListTable = new MailChimpTable("group");
							$oListTable->print_table();
						?>
						<p>
							<?php wp_nonce_field( 'cm_mailchimp_group_nonce', 'cm_mailchimp_group_nonce' ); ?>
							<?php submit_button( __( 'Select group' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			<?php endif; ?>
		</div>
	</div>

	<!-- EXPORT/IMPORT -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php _e( 'Export Settings' ); ?></span></h2>
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
			<h2 class="cm_setting_title"><span><?php _e( 'Import Settings' ); ?></span></h2>
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


</div>