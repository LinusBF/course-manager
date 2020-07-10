<?php
	$oCM = new CourseManager();

	$aSettings = $oCM->getOptions();

?>
<div class="wrap">
	<h1><?php echo TXT_CM_SETTINGS; ?></h1>

	<!-- STRIPE -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php echo TXT_CM_SETTINGS_STRIPE_SETTINGS; ?></span></h2>
			<div class="inside">
				<p><?php echo TXT_CM_SETTINGS_STRIPE_DESCRIPTION; ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="stripe_settings" /></p>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="stripe_public"><?php echo TXT_CM_SETTINGS_STRIPE_PUBLIC; ?></label></th>
							<td>
								<input name="cm_stripe_public" type="text" id="stripe_public"
								       value="<?php echo ($aSettings['stripe']['publishable_key'] !== -1 ? $aSettings['stripe']['publishable_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="stripe_secret"><?php echo TXT_CM_SETTINGS_STRIPE_SECRET; ?></label></th>
							<td>
								<input name="cm_stripe_secret" type="text" id="stripe_secret"
								       value="<?php echo ($aSettings['stripe']['secret_key'] !== -1 ? $aSettings['stripe']['secret_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="stripe_webhook"><?php echo TXT_CM_SETTINGS_STRIPE_WEBHOOK; ?></label></th>
							<td>
								<input name="cm_stripe_webhook" type="text" id="stripe_webhook"
                       value="<?php echo ($aSettings['stripe']['webhook_secret'] !== -1 ? $aSettings['stripe']['webhook_secret'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
                       class="regular-text" />
							</td>
						</tr>
					</table>
					<p>
						<?php wp_nonce_field( 'cm_stripe_nonce', 'cm_stripe_nonce' ); ?>
						<?php submit_button( TXT_CM_SETTINGS_STRIPE_BUTTON, 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div>
	</div>

	<!-- MAILCHIMP -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php echo TXT_CM_SETTINGS_MAILCHIMP_SETTINGS; ?></span></h2>
			<div class="inside">
				<p><?php echo TXT_CM_SETTINGS_MAILCHIMP_DESCRIPTION; ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="mailchimp_settings" /></p>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="cm_mail_chimp_key"><?php echo TXT_CM_SETTINGS_MAILCHIMP_KEY; ?></label></th>
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
						<?php submit_button( TXT_CM_SETTINGS_MAILCHIMP_KEY_BUTTON, 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
			<?php if($aSettings['mail_chimp']['api_key'] !== -1 && CmMailController::checkApiKey()): ?>
			<div id="cm_mc_lists" class="cm_mc_list inside">
				<h3><span><?php echo TXT_CM_SETTINGS_MAILCHIMP_LISTS; ?></span></h3>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="mailchimp_list_settings" /></p>
					<?php
						$oListTable = new MailChimpTable();
						$oListTable->print_table();
					?>
					<p>
						<?php wp_nonce_field( 'cm_mailchimp_list_nonce', 'cm_mailchimp_list_nonce' ); ?>
						<?php submit_button( TXT_CM_SETTINGS_MAILCHIMP_LIST_BUTTON, 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
			<?php endif; ?>
		</div>
	</div>

	<!-- MANDRILL -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php echo TXT_CM_SETTINGS_MANDRILL_SETTINGS; ?></span></h2>
			<div class="inside">
				<p><?php echo TXT_CM_SETTINGS_MANDRILL_DESCRIPTION; ?></p>
				<form method="post">
					<p><input type="hidden" name="cm_action" value="mandrill_settings" /></p>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="cm_mandrill_key"><?php echo TXT_CM_SETTINGS_MANDRILL_KEY; ?></label></th>
							<td>
								<input name="cm_mandrill_key" type="text" id="mandrill_key"
								       value="<?php echo ($aSettings['mandrill']['api_key'] !== -1 ? $aSettings['mandrill']['api_key'] : TXT_CM_ADMIN_SETTINGS_NOT_SET) ?>"
								       class="regular-text" />
							</td>
						</tr>
					</table>
					<?php /*if($aSettings['mail_chimp']['api_key'] !== -1 && !CmMailController::checkApiKey()): */?><!--
						<div class="notice inline notice-warning notice-alt">
							<p><?php /*echo TXT_CM_CHIMP_INCORRECT_KEY; */?></p>
						</div>
					--><?php /*endif;*/?>
					<p>
						<?php wp_nonce_field( 'cm_mandrill_nonce', 'cm_mandrill_nonce' ); ?>
						<?php submit_button( TXT_CM_SETTINGS_MANDRILL_KEY_BUTTON, 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
			<?php if($aSettings['mandrill']['api_key'] !== -1): ?>
				<div id="cm_md_templates" class="cm_md_list inside">
					<h3><span><?php echo TXT_CM_SETTINGS_MANDRILL_TEMPLATE; ?></span></h3>
					<form method="post">
						<p><input type="hidden" name="cm_action" value="mandrill_template_settings" /></p>
						<?php
						$oListTable = new MandrillTable();
						$oListTable->print_table();
						?>
						<p>
							<?php wp_nonce_field( 'cm_mandrill_template_nonce', 'cm_mandrill_template_nonce' ); ?>
							<?php submit_button( TXT_CM_SETTINGS_MANDRILL_TEMPLATE_BUTTON, 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			<?php endif; ?>
		</div>
	</div>

	<!-- EXPORT/IMPORT -->
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="cm_setting_title"><span><?php echo TXT_CM_SETTINGS_EXPORT_TITLE; ?></span></h2>
			<div class="inside">
				<p><?php echo TXT_CM_SETTINGS_EXPORT_DESCRIPTION; ?></p>
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
			<h2 class="cm_setting_title"><span><?php echo TXT_CM_SETTINGS_IMPORT_TITLE; ?></span></h2>
			<div class="inside">
				<p><?php echo TXT_CM_SETTINGS_IMPORT_DESCRIPTION; ?></p>
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