<?php
defined( 'ABSPATH' ) or die( 'Cheating, huh?' );
global $fc_meta, $wpdb, $fc_forms_table,$fc_addons,$fc_addons_directory, $fc_triggers;
$form_id = intval($_GET['id']);
$form_name = $wpdb->get_var( "SELECT name FROM $fc_forms_table WHERE id = '$form_id'" );
$counter = $wpdb->get_var( "SELECT counter FROM $fc_forms_table WHERE id = '$form_id'" );
if (is_rtl())
{
	?>
	<script>
		window.isRTL = true;
	</script>
	<?php
}
else
{
	?>
	<script>
		window.isRTL = false;
	</script>
	<?php
}

$backgrounds = array();
$base = plugins_url( '../assets/images/backgrounds/', __FILE__ );
$backgrounds[] = array('White','#ffffff','#ffffff');
$backgrounds[] = array('None','transparent','url('.$base.'transparent.png)');
$backgrounds[] = array('Birds','url('.$base.'birds.jpg)','url('.$base.'thumb-birds.jpg)');
$backgrounds[] = array('Speck','url('.$base.'brillant.png)','url('.$base.'thumb-brillant.png)');
$backgrounds[] = array('Fibre','url('.$base.'fibre.png)','url('.$base.'thumb-fibre.png)');
$backgrounds[] = array('Noise','url('.$base.'grey-noise.png)','url('.$base.'thumb-grey-noise.png)');
$backgrounds[] = array('Lined','url('.$base.'lined.png)','url('.$base.'thumb-lined.png)');
$backgrounds[] = array('Linen','url('.$base.'white-linen.png)','url('.$base.'thumb-white-linen.png)');
$backgrounds[] = array('Coarse','url('.$base.'coarse.png)','url('.$base.'thumb-coarse.png)');
$backgrounds[] = array('Jeans','url('.$base.'jeans.png)','url('.$base.'jeans.png)');

?>
<input type='hidden' id='form_id' value='<?php echo $form_id; ?>'>
<div ng-app='FormCraft' style=''>
	<div class='formcraft-css form-builder-cover hide-form' ng-controller='FormController' ng-init='AngularInit()'>

	<div class='fields-list-right fields-list-sortable state-{{Builder.show_fields}}'>
			<div class='show-fields button' ng-click='Builder.show_fields = true'>
				<img src='<?php echo plugins_url( '../assets/images/add-fields.png', __FILE__ ); ?>'/>
			</div>
			<img ng-click='Builder.show_fields = false' src='<?php echo plugins_url( '../assets/images/hide-fields.png', __FILE__ ); ?>'/>
			<div class='button' ng-click='addFormElement("heading")'><?php _e('Heading','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("oneLineText")'><?php _e('One Line Input','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("email")'><?php _e('Email Input','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("textarea")'><?php _e('Textarea','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("checkbox")'><?php _e('Checkbox','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("dropdown")'><?php _e('Dropdown','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("datepicker")'><?php _e('Datepicker','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("customText")'><?php _e('Custom Text','formcraft') ?></div>
			<div class='button' ng-click='addFormElement("submit")'><?php _e('Submit','formcraft') ?></div>
			<?php
			do_action('formcraft_after_fields');
			?>			
			<div class='adv-fields fake-hover'>
				<div class='button'><i class='icon-angle-left'></i> <?php _e('More Fields','formcraft') ?></div>
				<ul class='fields-list fields-list-sortable'>
					<li><div data-target='#captcha_modal' data-toggle='fc_modal' class='button'><?php _e('Captcha','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("password")'><?php _e('Password','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("fileupload")'><?php _e('File Upload','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("slider")'><?php _e('Slider','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("timepicker")'><?php _e('Timepicker','formcraft') ?></div></li>
					<li ng-repeat='field in addField.others'><div class='button' ng-click='addFormElement(field.name)'>{{field.name}}</div></li>
				</ul>
			</div>
			<div class='adv-fields fake-hover' style='z-index: 100'>
				<div class='button'><i class='icon-angle-left'></i> <?php _e('Survey','formcraft') ?></div>
				<ul class='fields-list fields-list-sortable'>
					<li><div class='button' ng-click='addFormElement("thumb")'><?php _e('Thumb Rating','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("star")'><?php _e('Star Rating','formcraft') ?></div></li>
					<li><div class='button' ng-click='addFormElement("matrix")'><?php _e('Choice Matrix','formcraft') ?></div></li>
				</ul>
			</div>						
			<div class='adv-fields fake-hover fields-nos-{{addField.payments.length}}'>
				<div class='button'><i class='icon-angle-left'></i> Payments</div>
				<ul class='fields-list fields-list-sortable'>
					<li ng-repeat='field in addField.payments'><div class='button' ng-click='addFormElement(field.name)'>{{field.name}}</div></li>
				</ul>
			</div>			
		</div>


		<div class='option-box  state-{{Options.show_logic}}' id='form_logic_box'>
			<div id='logic_tabs' class='nav-content-slide'>
				<div class='active'>
					<div id='add-logic-heads'>
						<div style='margin-left: 3.5%; width: 45%'><?php _e('Conditions','formcraft'); ?></div>
						<div style='margin-left: 7%; width: 39%'><?php _e('Consequences','formcraft'); ?></div>
					</div>
					<div class='add-logic-area' ng-repeat='logic in Builder.Config.Logic'>
						<div style='width: 3.5%;line-height:28px;vertical-align:top;color:#48e'>
							if
						</div>
						<div style='width: 45%' class='group actions-nos-{{Builder.Config.Logic[$index][0].length}}'>
							<div ng-repeat='action in Builder.Config.Logic[$index][0]' class='show-{{Builder.Config.Logic[$parent.$index][0].length}}'>
								<div style='width: 30%'>
									<select id='select_fix_{{$parent.$index}}_{{$index}}' ng-model='action[0]'>
										<option value=''>(<?php _e('field','formcraft'); ?>)</option>
										<optgroup ng-repeat='page in Builder.FormElements' label='{{Builder.Config.page_names[$index]}}'>
											<option ng-repeat='element in page' value='{{element.elementDefaults.identifier}}'>{{element.elementDefaults.main_label}}</option>
										</optgroup>
									</select>
								</div>
								<div style='width: 30%'>
									<select ng-model='action[1]'>
										<option value=''>(<?php _e('trigger','formcraft'); ?>)</option>
										<option value='equal_to'><?php _e('is equal to','formcraft'); ?></option>
										<option value='not_equal_to'><?php _e('is not equal to','formcraft'); ?></option>
										<option value='contains'><?php _e('contains','formcraft'); ?></option>
										<option value='contains_not'><?php _e('doest not contain','formcraft'); ?></option>
										<option value='greater_than'><?php _e('is greater than','formcraft'); ?></option>
										<option value='less_than'><?php _e('is less than','formcraft'); ?></option>
									</select>
								</div>
								<div style='width: 33%'>
									<input type='text' ng-model='action[2]' placeholder='...'>
								</div>
								<div ng-click='removeLogicAction($parent.$index, $index)' class='remove-action' style='width: 7%; border-right-width: 0px'>
									×
								</div>
								<div class='and-or'>
									<select ng-model='Builder.Config.Logic[$parent.$index][2]'>
										<option value='and'>And</option>
										<option value='or'>Or</option>
									</select>
								</div>
							</div>
							<div ng-click='addLogicAction($index)' class='add-group'><?php _e('add row','formcraft'); ?></div>
						</div>
						<div style='width: 7%;line-height:28px;vertical-align:top;color:#48e'>
							then
						</div>
						<div style='width: 39%' class='group'>
							<div ng-repeat='result in Builder.Config.Logic[$index][1]'>
								<div style='width: 40%; border-right: 0px' class='set-value-{{result[0]}}'>
									<select ng-model='result[0]'>
										<option value=''><?php _e('(action)','formcraft'); ?></option>
										<option value='show_fields'><?php _e('show fields','formcraft'); ?></option>
										<option value='hide_fields'><?php _e('hide fields','formcraft'); ?></option>
										<option value='email_to'><?php _e('send email to','formcraft'); ?></option>
										<option value='redirect_to'><?php _e('redirect to','formcraft'); ?></option>
										<option value='trigger_integration'><?php _e('trigger integration','formcraft'); ?></option>
										<option value='set_value'><?php _e('set value of','formcraft'); ?></option>
									</select>
									<select class='set-value-field' id='select_fix_{{$parent.$index}}' ng-model='result[4]'>
										<option value=''>(<?php _e('field','formcraft'); ?>)</option>
										<optgroup ng-repeat='page in Builder.FormElements' label='{{Builder.Config.page_names[$index]}}'>
											<option ng-repeat='element in page' value='{{element.elementDefaults.identifier}}'>{{element.elementDefaults.main_label}}</option>
										</optgroup>
									</select>									
								</div>
								<div class='result-type-{{result[0]}}' style='width: 52%; border-left: 1px solid #d4d4d4'>
									<input type='text' class='select-fields-logic' select-fields ng-model='result[1]' placeholder='(add fields)'/>
									<input type='text' class='type-in-logic' ng-model='result[2]' placeholder='...'>
									<?php
									if ( isset($fc_triggers) && count($fc_triggers)>0 )
									{
										echo "<select ng-model='result[3]'>";
										echo "<option value=''>".__('(select)','formcraft')."</option>";
										foreach ($fc_triggers as $key => $value) {
											echo "<option value='$value'>$value</option>";
										}
										echo "</select>";
									}
									?>
								</div>
								<div ng-click='removeLogicResult($parent.$index, $index)'class='remove-action' style='width: 8%; border-right: 0px'>
									×
								</div>
							</div>
							<div ng-click='addLogicResult($index)' class='add-group'><?php _e('add row','formcraft'); ?></div>
						</div>
						<div style='margin-left: 1%; width: 4.5%' class='remove-logic' ng-click='removeLogic($index)'>×</div>
					</div>
					<div id='add-logic-cover'>
						<button class='button' ng-click='addLogic()'><?php _e('Add New Logic','formcraft'); ?></button>
						<a style='margin: 10px auto;font-size: 12px' class='trigger-help' data-post-id='9'><?php _e('how to use Conditional Logic', 'formcraft'); ?></a>
					</div>
				</div>
			</div>
		</div>


		<div class='option-box  state-{{Options.show_addons}}' id='form_addon_box'>
			<nav class='nav-tabs-slide' data-content='#addon_tabs'>
				<span style='width: 50%' class='active'><?php _e('Installed','formcraft'); ?></span>
				<span style='width: 50%'><?php _e('Add New','formcraft'); ?></span>
			</nav>
			<div id='addon_tabs' class='nav-content-slide'>
				<div class='active'>
					<?php
					if ( is_array($fc_addons) && count($fc_addons)>0 )
					{
						foreach ($fc_addons as $key => $addon) {
							$extra_class = isset($_GET['f3_activated']) && $_GET['f3_activated']==$addon['plugin_id'] ? 'fc_highlight' : '';
							?>
							<div class='addon addon-id-<?php echo $addon['plugin_id'].' '; echo $extra_class; ?>' <?php if ( !empty($addon['controller']) ) { ?> ng-controller='<?php echo $addon['controller']; ?>' <?php } ?> data-name='<?php echo $addon['title']; ?>'>
								<div class='addon-head ac-toggle' ng-click='Init()'>
									<div class='addon-logo-cover'>
										<img class='addon-logo' src='<?php echo $addon['logo']; ?>' alt='<?php echo $addon['title']; ?>'/>
									</div>
									<span class='addon-title'><?php echo $addon['title']; ?></span>
									<span class='toggle-angle'><i class='icon-angle-down'></i><i class='icon-angle-up'></i></span>
								</div>
								<div class='addon-content ac-inner'>
									<div>
										<?php
										$addon['content_fn']();
										?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					else
					{
						?>
						<div class='no-addons'><?php _e('No Installed Addons','formcraft') ?></div>
						<?php
					}
					?>
				</div>
				<div class='new-addons'>
				</div>			
			</div>
		</div>
		<div class='option-box state-{{Options.show_options}}' id='form_options_box'>
			<nav class='nav-tabs-slide' data-content='#options_tabs'>
				<span style='width: 20%' class='active'><?php _e('General','formcraft'); ?></span>
				<span style='width: 17%'><?php _e('Email','formcraft'); ?></span>
				<span style='width: 17%'><?php _e('Embed','formcraft'); ?></span>
				<span style='width: 27%'><?php _e('Custom Text','formcraft'); ?></span>
				<span style='width: 19%'><?php _e('Extras','formcraft'); ?></span>
			</nav>
			<div id='options_tabs' class='nav-content-slide'>
				<div class='active'>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.form_disable'>
						<h3><?php _e('Disable form','formcraft'); ?></h3>
						<span ng-slide-toggle='Builder.Config.form_disable'><?php _e('Show this message when disabled: ','formcraft'); ?>
							<textarea style='width: 100%' ng-model='Builder.Config.form_disable_message'>
							</textarea>
						</span>
					</label>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.disable_form_link'>
						<h3><?php _e('Disable form page','formcraft'); ?></h3>
						<span><?php _e('You can share this link directly to allow people to fill the form','formcraft'); ?>
							<textarea onclick='select()' style='width: 100%' rows='1' class='copy-code' readonly><?php echo get_site_url().'/form-view/'.$form_id; ?></textarea>
						</span>
					</label>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.disable_multiple'>
						<h3><?php _e('Disable multiple submissions from same device','formcraft'); ?></h3>
						<span ng-slide-toggle='Builder.Config.disable_multiple'><?php _e('Show this message when disabled: ','formcraft'); ?>
							<textarea style='width: 100%' ng-model='Builder.Config.disable_multiple_message'>
							</textarea>
						</span>
					</label>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.disable_after'>
						<h3><?php _e('Disable form when it reaches','formcraft'); ?><input type='text' ng-model='Builder.Config.disable_after_nos' style='width:40px'/><?php _e('submissions','formcraft'); ?></h3>
						<span><?php _e('Current submission counter: ') ?><?php echo $counter; ?></span>
					</label>					
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.spin_effect'>
						<h3><?php _e('Enable spin effect on math numbers','formcraft'); ?></h3>
					</label>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.save_progress'>
						<h3><?php _e('Enable auto-save form progress','formcraft'); ?></h3>
						<span><?php _e('Your users\' data is automatically saved as they type. If the form is not submitted, they can come back to the form later on, and will be able to continue from where they left. The data is stored for 60 days.','formcraft'); ?></span>
					</label>					
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.dont_submit_hidden'>
						<h3><?php _e('Don\'t submit values for hidden fields','formcraft'); ?></h3>
					</label>
					<label class='single-option'>
						<input update-label type='checkbox' value='true' ng-model='Builder.Config.Post_data'>
						<h3><?php _e('Send data to custom URL','formcraft'); ?></h3>
						<span><?php _e('When the form has been successfully submitted, we will also send the form data to a URL of your choice:','formcraft'); ?>
							<input type='text' placeholder='http://example.com/my_app.php' style='width: 100%; margin-bottom: 8px' ng-model='Builder.Config.webhook'>
							<strong><?php _e('Method','formcraft'); ?></strong>: &nbsp;&nbsp;<label><input type='radio' ng-model='Builder.Config.webhook_method' value='POST'>POST</label>
							<label style='margin-left: 5px'><input type='radio' ng-model='Builder.Config.webhook_method' value='GET'>GET</label>
						</span>
					</label>
				</div>			
				<div>
					<h2 class='ac-toggle'><?php _e('Email Setup','formcraft'); ?><i class='icon-angle-down'></i><i class='icon-angle-up'></i></h2>
					<div class='ac-inner'>
						<div style='width: 100%'>
							<div class='help-link'>
								<a class='trigger-help' data-post-id='33'><?php _e('what do I fill in here?', 'formcraft'); ?></a>
							</div>
							<div class='email-method-check'>
								<label style='border-radius: 2px 0 0 0; width: 50%; border-right: 0px'>
									<input update-label value='php' type='radio' ng-model='Builder.Config.notifications._method'/>
									<?php _e('WP Mail','formcraft'); ?>
								</label>
								<label style='border-radius: 0 2px 0 0; width: 50%; border-left-color:#ddd'>
									<input update-label value='smtp' type='radio' ng-model='Builder.Config.notifications._method'/>
									<?php _e('SMTP Method','formcraft'); ?>
								</label>
							</div>
							<div class='email-method-list'>
								<div ng-class='["email-method", "show-"+Builder.Config.notifications._method]'>
									<div class='input-group'>
										<label>
											<input type='text' placeholder='Sender Name' ng-model='Builder.Config.notifications.general_sender_name'/>
										</label>
										<label>
											<input type='text' placeholder='Sender Email' ng-model='Builder.Config.notifications.general_sender_email'/>
										</label>
									</div>
								</div>
								<div ng-class='["email-method", "hide-"+Builder.Config.notifications._method]'>
									<div class='input-group'>
										<label>
											<input type='text' placeholder='Sender Name' ng-model='Builder.Config.notifications.general_sender_name'/>
										</label>
										<label>
											<input type='text' placeholder='Sender Email' ng-model='Builder.Config.notifications.general_sender_email'/>
										</label>
										<label>
											<input type='text' placeholder='Username' ng-model='Builder.Config.notifications.smtp_sender_username'/>
										</label>
										<label>
											<input type='password' placeholder='Password' ng-model='Builder.Config.notifications.smtp_sender_password'/>
										</label>
										<label>
											<input type='text' placeholder='SMTP Host' ng-model='Builder.Config.notifications.smtp_sender_host'/>
										</label>
										<div class='half'>
											<label>
												<input type='text' placeholder='Port' ng-model='Builder.Config.notifications.smtp_sender_port'/>
											</label>
											<label style='margin-left:-1px'>
												<select ng-model='Builder.Config.notifications.smtp_sender_security'>
													<option value='none'>None</option>
													<option value='ssl'>SSL</option>
													<option value='tls'>TLS</option>
												</select>
											</label>
										</div>
									</div>
								</div>
								<form ng-submit='testEmail()' class='test-email'>
									<i data-placement='left' data-html='true' class='icon-help tooltip-icon' data-toggle='tooltip' title='<?php _e('You can send a test email to check if the email settings are working properly.<br>Add an email here, and press Enter','formcraft'); ?>'></i>
									<input placeholder='Send Test E-Mail' type='text' ng-model='Builder.TestEmails'/>
									<span><?php _e('press Enter to send','formcraft'); ?></span>
									<div compile='TestEmailResult'></div>
								</form>
							</div>
						</div>
					</div>
					<h2 class='ac-toggle'><?php _e('Email Notifications','formcraft'); ?><i class='icon-angle-down'></i><i class='icon-angle-up'></i></h2>
					<div class='ac-inner'>
						<div>
							<label style='width: 100%; margin-top: 15px'>
								<span class='sub-label' style='background:rgb(244, 244, 244)'><?php _e('Send Email To','formcraft'); ?></span>								
								<input type="text" placeholder="dan@example.com, joe@example.com" ng-model='Builder.Config.notifications.recipients' style='width: 100%; padding-right: 35px'>
								<span class='tooltip-cover'><i data-placement='left' data-html='true' class='icon-help-circled tooltip-icon' data-toggle='tooltip' title='<?php _e('When the form is submitted, an email will be sent to these addresses.<br>You can add multiple emails, separated by a comma.','formcraft'); ?>'></i></span>
							</label>
						</div>						
						<label style='width: 100%; margin-top: 30px'>
							<span class='sub-label' style='background:rgb(244, 244, 244)'><?php _e('Email Subject','formcraft'); ?></span>
							<input type="text" placeholder="New Form Entry - [Form Name]" ng-model='Builder.Config.notifications.email_subject' style='width: 100%; padding-right: 35px'>
							<span class='tooltip-cover'><i data-placement='left' data-html='true' class='icon-help-circled tooltip-icon' data-toggle='tooltip' title='<?php _e('You can add form values to subject, using the labels of fields, example: <br><strong>[Your Name]</strong>','formcraft'); ?>'></i></span>
						</label>
						<div class='tooltip-wide'>
							<div style='position: relative; width: 100%;'>
								<label style='display: block; width: 140px; margin-top: 30px'><span class='sub-label' style='background:rgb(244, 244, 244)'><?php _e('Email Body','formcraft'); ?></span></label>
								<text-angular class='textangular' ng-model="Builder.Config.notifications.email_body"></text-angular>
							</div>
							<a class='trigger-help' data-post-id='186'><?php _e('read more about customising email content', 'formcraft'); ?></a>
							<label style='margin-top: 5px;'>
								<input type='checkbox' ng-model='Builder.Config.notifications.attach_images'>
								<?php _e('Attach file uploads to emails','formcraft'); ?>
							</label>
							<label style='margin-top: 5px;'>
								<input type='checkbox' ng-model='Builder.Config.notifications.form_layout'>
								<?php _e('Use form\'s multi-column layout in email body','formcraft'); ?>
							</label>
						</div>
					</div>
					<div>
						<h2 data-toggle='fc_modal' data-target='#autoresponder_modal' class='ac-toggle'><?php _e('Email Autoresponders','formcraft'); ?><i class='icon-popup'></i></h2>
					</div>
				</div>
				<div>
					<div class='single-option'>
						<div class='step'>1</div>
						<h3><?php _e('Dedicated Form Page','formcraft'); ?></h3>
						<span><?php _e('You can share this link directly to allow people to fill the form','formcraft'); ?>
							<textarea onclick='select()' style='width: 100%' rows='1' class='copy-code' readonly><?php echo get_site_url().'/form-view/'.$form_id; ?></textarea>
						</span>
						<label style='margin-top: 6px'>
							<input update-label type='checkbox' value='true' ng-model='Builder.Config.disable_form_link'>
							<h3><?php _e('Disable This Page','formcraft'); ?></h3>
						</label>
					</div>
					<div class='single-option'>
						<div class='step'>2</div>
						<h3><?php _e('Shortcode','formcraft'); ?></h3>
						<span><?php _e('Add this shortcode to your page / post','formcraft'); ?>.
							<a data-post-id='179' class='trigger-help'><?php _e('Read more','formcraft'); ?></a>.
							<textarea onclick='select()' style='width: 100%' rows='1' class='copy-code' readonly>[fc id='<?php echo $_GET['id']; ?>'][/fc]</textarea>
						</span>
					</div>
					<div class='single-option' style='border: 0px; overflow: hidden; height: 142px; box-shadow: 0px -3px 2px -2px rgba(0,0,0,.8) inset; margin-top: -1px'>
						<div class='step'>3</div>
						<img class='bg-image' src='<?php echo plugins_url( '../assets/images/add-form.png', __FILE__ ); ?>'/>
						<h3 style='position: absolute; padding-left: 15px; height: 40px; line-height: 40px; background: rgba(255,255,255,.7); left: 0; right: 0; top: 0;box-shadow: 0px 3px 3px -3px rgba(0,0,0,.4) inset'><?php _e('Post / Page Editor','formcraft'); ?></h3>
						<a style='position: absolute; top: 0; height: 30px; line-height: 30px; left: 130px' data-post-id='66' class='trigger-help'>(<?php _e('Read more','formcraft'); ?>)</a>.
					</div>
					<div class='single-option'>
						<div class='step'>4</div>
						<h3><?php _e('PHP Function','formcraft'); ?></h3>
						<span><?php _e('You can use this in your theme files','formcraft'); ?>.
							<a data-post-id='84' class='trigger-help'><?php _e('Read more','formcraft'); ?></a>.
							<textarea onclick='select()' style='width: 100%;white-space:normal' rows='4' class='copy-code' readonly>&#x3C;?php 
								if (function_exists(&#x22;add_formcraft_form&#x22;))
								{ add_formcraft_form(&#x22;[fc id=&#x27;<?php echo $_GET['id']; ?>&#x27;][/fc]&#x22;); } 
								?&#x3E;</textarea>
							</span>
						</div>
						<div class='single-option'>
							<div class='step'>5</div>						
							<h3><?php _e('Widget','formcraft'); ?></h3>
							<span><?php _e('Go to Appearance → Widgets, and add a FormCraft widget','formcraft'); ?>.</span>
						</div>
					</div>
					<div class='custom-text-cover'>
						<textarea id='success-message' autosize ng-model='Builder.Config.Messages.success' class='underline-input' placeholder='<?php _e('We have received your message','formcraft'); ?>'></textarea>
						<input type='text' ng-model='Builder.Config.Messages.failed' class='underline-input' placeholder='<?php _e('Please correct the errors and try again','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.is_required' class='underline-input' placeholder='<?php _e('Required field','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.is_invalid' class='underline-input' placeholder='<?php _e('Invalid value','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.min_char' class='underline-input' placeholder='<?php _e('Min [x] characters required','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.max_char' class='underline-input' placeholder='<?php _e('Max [x] characters allowed','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.min_files' class='underline-input' placeholder='<?php _e('Min [x] file(s) required','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.max_files' class='underline-input' placeholder='<?php _e('Max [x] file(s) allowed','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_email' class='underline-input' placeholder='<?php _e('Invalid Email Address','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_alphabets' class='underline-input' placeholder='<?php _e('Only alphabets allowed','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_numbers' class='underline-input' placeholder='<?php _e('Only numbers allowed','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_alphanumeric' class='underline-input' placeholder='<?php _e('Only alphabets and numbers allowed','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_url' class='underline-input' placeholder='<?php _e('Invalid URL','formcraft'); ?>'/>
						<input type='text' ng-model='Builder.Config.Messages.allow_regexp' class='underline-input' placeholder='<?php _e('Invalid Expression','formcraft'); ?>'/>						
					</div>
					<div>
						<label class='single-option'>
							<h3>
								<?php _e('Form Name','formcraft'); ?>: <input style='width: 225px; text-align: left; font-weight: normal; text-transform: none; padding: 6px 11px' type='text' value='<?php echo $form_name ?>' ng-model='Builder.Config.form_name'/>
							</h3>
						</label>
						<label class='single-option'>
							<h3>
								<?php _e('Redirect on Submit','formcraft'); ?>: <input style='width: 200px; text-align: left; font-weight: normal; text-transform: none; padding: 6px 11px' placeholder='http://example.com/thank-you' type='text' value='<?php echo $form_name ?>' ng-model='Builder.Config.redirect_main'/>
							</h3>
						</label>
						<label class='single-option'>
							<h3><?php _e('Redirect delay: ','formcraft'); ?><input type='text' ng-model='Builder.Config.Redirect_delay_seconds' style='width:40px'/><?php _e('seconds after form submit','formcraft'); ?></h3>
						</label>
						<label class='single-option'>
							<h3><?php _e('Thousand separator: ','formcraft'); ?>
								<select ng-model='Builder.Config.thousand_separator'  style='width:70px'>
									<option value=''><?php _e('None','formcraft'); ?></option>
									<option value=' '>space ( )</option>
									<option value=','>comma (,)</option>
									<option value='.'>period (.)</option>
								</select>
							</h3>
						</label>
						<label class='single-option'>
							<h3><?php _e('Decimal separator: ','formcraft'); ?>
								<select ng-model='Builder.Config.decimal_separator' style='width:70px'>
									<option value='.'>period (.)</option>
									<option value=','>comma (,)</option>
								</select>
							</h3>
						</label>
						<div class='single-option'>
							<a style='margin: 4px 0 3px 0; padding: 10px 15px' target='_blank' href='<?php echo get_site_url(); ?>?formcraft3_export_form=<?php echo $form_id; ?>' class='button'><?php _e('Export Form File','formcraft'); ?></a>
							<p class='description' style='margin-top: 8px'>
								<?php _e('You can import this form template on any other WordPress site with the plugin installed.','formcraft'); ?>
							</p>
						</div>
					</div>
				</div>
			</div>		
			<div id='form_styling_box' class='option-box  state-{{Options.show_styling}}'>
				<nav class='nav-tabs-slide' data-content='#styling_tabs'>
					<span style='width: 25%' class='active'><?php _e('General','formcraft'); ?></span>
					<span style='width: 40%'><?php _e('Color Scheme','formcraft'); ?></span>
					<span style='width: 35%'><?php _e('Background','formcraft'); ?></span>
				</nav>
				<div id='styling_tabs' class='nav-content-slide'>
					<div class='active'>
						<div class='group'>
							<h2>
								<?php _e('Form','formcraft'); ?>
							</h2>
							<div class='hide-checkbox'>
								<label class='standalone' style='margin-right: 5px'>
									<input update-label type='checkbox' ng-model='Builder.form_frame' ng-true-value='"hidden"' ng-false-value='"visible"'><?php _e('Remove Form Frame','formcraft'); ?>
								</label>
								<label class='standalone'>
									<input update-label type='checkbox' ng-model='Builder.form_asterisk'><?php _e('Hide','formcraft'); ?> <span style='color: red; font-weight: bold'>*</span>
								</label>
								<br>
								<label class='standalone' style='margin-top: 9px'>
									<input update-label type='checkbox' ng-model='Builder.hide_icons'><?php _e('Hide Field Icons','formcraft'); ?>
								</label>
							</div>
						</div>
						<div class='group'>
							<h2>
								<?php _e('Font','formcraft'); ?>
							</h2>
							<select class='standalone' ng-model='Builder.Config.font_family' style='width: 182px; opacity: 1'>
								<option value="inherit">Default Font</option>
								<optgroup label='<?php _e('General Fonts','formcraft'); ?>'>
									<option value="Helvetica, Arial, sans-serif">Helvetica / Arial</option>
									<option value="'Trebuchet MS', Helvetica, Arial, sans-serif">Trebuchet MS</option>
									<option value="'Courier New', Courier, monospace">Courier New</option>
									<option value="'Georgia', sans-serif">Georgia</option>
									<option value="'Times New Roman', sans-serif">Times New Roman</option>
								</optgroup>
								<optgroup label='<?php _e('Google Fonts','formcraft'); ?>'>
									<option value="Source Sans Pro">Source Sans Pro</option>
									<option value="Ubuntu">Ubuntu</option>
									<option value="Merriweather">Merriweather</option>
									<option value="Roboto">Roboto</option>
									<option value="Raleway">Raleway</option>
									<option value="Lato">Lato</option>
									<option value="Oswald">Oswald</option>
									<option value="Lora">Lora</option>
									<option value="Bitter">Bitter</option>
									<option value="Cabin">Cabin</option>
									<option value="Playfair Display">Playfair Display</option>
									<option value="Courgette">Courgette</option>
								</optgroup>
							</select><br>
							<button style='margin-right: 5px; margin-top: 9px' class='button font-btn' ng-click='Builder.font_size = Builder.font_size + 5'>+ Font Size</button>
							<button style='margin-top: 9px' class='button font-btn' ng-click='Builder.font_size = Builder.font_size - 5'>- Font Size</button>
							<p class='font-show'>{{Builder.font_size}}%</p>
						</div>
						<div class='group'>
							<h2>
								<?php _e('Field Layout','formcraft'); ?>
							</h2>
							<div class='grid-allow'>
								<label style='width: 30.6%; margin-right: 4%'>
									<span style='left: 11%; width: 17.5%; bottom: 20px; height: 12px; background: #bbb'></span>
									<span style='left: 7%; width: 21.5%; bottom: 9px; height: 9px; background: #ccc'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; left: 31%; bottom: 9px; right: 7%'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='inline' ng-model='Builder.label_style'>
								</label>
								<label style='width: 30.6%; margin-right: 4%'>
									<span style='z-index: 1; left: 11%; width: 30px; bottom: 14px; height: 13px; background: #bbb'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='placeholder' ng-model='Builder.label_style'>
								</label>
								<label style='width: 30.6%'>
									<span style='left: 7.6%; width: 20px; bottom: 45px; height: 10px; background: #bbb'></span>
									<span style='left: 7.6%; width: 35px; bottom: 35px; height: 8px; background: #ccc'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='block' ng-model='Builder.label_style'>
								</label>
								<label style='width: 30.6%; margin-right: 4%; margin-top: 4%'>
									<span style='z-index: 1; left: 11%; width: 30px; bottom: 14px; height: 13px; background: #bbb'></span>
									<span style='border-bottom: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='floating' ng-model='Builder.label_style'>
								</label>
							</div>
						</div>
						<div class='group'>
							<h2>
								<?php _e('Field Alignment','formcraft'); ?>
							</h2>
							<div class='grid-allow'>
								<label style='width: 30.6%; margin-right: 4%'>
									<span style='left: 7.6%; width: 20px; bottom: 45px; height: 10px; background: #bbb'></span>
									<span style='left: 7.6%; width: 35px; bottom: 35px; height: 8px; background: #ccc'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='left' ng-model='Builder.form_internal_alignment'>
								</label>
								<label style='width: 30.6%; margin-right: 4%'>
									<span style='left: 40.5%; width: 20px; bottom: 45px; height: 10px; background: #bbb'></span>
									<span style='left: 33%; width: 35px; bottom: 35px; height: 8px; background: #ccc'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='center' ng-model='Builder.form_internal_alignment'>
								</label>
								<label style='width: 30.6%'>
									<span style='right: 7.6%; width: 20px; bottom: 45px; height: 10px; background: #bbb'></span>
									<span style='right: 7.6%; width: 35px; bottom: 35px; height: 8px; background: #ccc'></span>
									<span style='background: white; border: 1px solid #ccc; height: 23px; width: 86%; left: 7%; bottom: 9px'></span>
									<input type='radio' fc-placeholder-update name='fs_label' update-label value='right' ng-model='Builder.form_internal_alignment'>
								</label>
							</div>
						</div>
						<div class='group'>
							<h2>
								<?php _e('Your Logo','formcraft'); ?>
							</h2>
							<div>
								<input style='width: 100%' type='text' placeholder='<?php _e('Image URL','formcraft'); ?>' ng-model='Builder.Config.form_logo_url'/>
							</div>
						</div>					
						<div class='group'>
							<h2>
								<?php _e('Custom CSS','formcraft'); ?>
							</h2>
							<textarea autosize id='custom-css-textarea' ng-model='Builder.Config.Custom_CSS' rows='7' style='width: 100%; min-height: 145px; margin-top: 5px'>
							</textarea>
							<a class='trigger-help' data-post-id='253'><?php _e('Guide to using Custom CSS', 'formcraft'); ?></a>
						</div>
					</div>
					<div>
						<div class='group'>
							<h2>
								<?php _e('Choose A Color Scheme','formcraft'); ?>
							</h2>
							<div class='color-schemes colors'>
								<label style='background: #4488ee; border-color: <?php echo fc_adjustBrightness('#4488ee', -30); ?>'>
									<input type='radio' update-label value='#4488ee' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #a9a9a9; border-color: <?php echo fc_adjustBrightness('#a9a9a9', -30); ?>'>
									<input type='radio' update-label value='#a9a9a9' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #e9967a; border-color: <?php echo fc_adjustBrightness('#e9967a', -30); ?>'>
									<input type='radio' update-label value='#e9967a' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #3cb371; border-color: <?php echo fc_adjustBrightness('#3cb371', -30); ?>'>
									<input type='radio' update-label value='#3cb371' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #8FBC8F; border-color: <?php echo fc_adjustBrightness('#8FBC8F', -30); ?>'>
									<input type='radio' update-label value='#8FBC8F' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #F08080; border-color: <?php echo fc_adjustBrightness('#F08080', -30); ?>'>
									<input type='radio' update-label value='#F08080' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #778899; border-color: <?php echo fc_adjustBrightness('#778899', -30); ?>'>
									<input type='radio' update-label value='#778899' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #FF6347; border-color: <?php echo fc_adjustBrightness('#FF6347', -30); ?>'>
									<input type='radio' update-label value='#FF6347' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #4682B4; border-color: <?php echo fc_adjustBrightness('#4682B4', -30); ?>'>
									<input type='radio' update-label value='#4682B4' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #5F9EA0; border-color: <?php echo fc_adjustBrightness('#5F9EA0', -30); ?>'>
									<input type='radio' update-label value='#5F9EA0' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #deb887; border-color: <?php echo fc_adjustBrightness('#deb887', -30); ?>'>
									<input type='radio' update-label value='#deb887' ng-model='Color_scheme' name='radio_cs'>
								</label>
								<label style='background: #ff69b4; border-color: <?php echo fc_adjustBrightness('#ff69b4', -30); ?>'>
									<input type='radio' update-label value='#ff69b4' ng-model='Color_scheme' name='radio_cs'>
								</label>
							</div>
						</div>
						<div class='group custom-color'>
							<h2>
								<?php _e('Or Build A Custom One','formcraft'); ?>
							</h2>
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Base Color','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.color_scheme_button'>
							</div>
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Button Font Color','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.color_scheme_font'>
							</div>
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Pagination Button Color','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.color_scheme_step'>
							</div>						
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Checkbox Background','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.color_scheme_checkbox'>
							</div>
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Field Background Color','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.color_field_background'>
							</div>							
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('General Font Color','formcraft'); ?>
								</span>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.Config.font_color'>
							</div>
							<div>
								<span style='font-size: 12px; display: inline-block; width: 150px'>
									<?php _e('Field Font Color','formcraft'); ?>
								</span>
								<input type="text" value="#777" angular-color class="color-picker" ng-model='Builder.Config.field_font_color'>
							</div>
						</div>
					</div>
					<div>
						<div class='group'>
							<h2><?php _e('Choose A Form Background','formcraft'); ?></h2>
							<div class='color-schemes image-schemes hide-checkbox'>
								<?php
								foreach ($backgrounds as $key => $value) {
									?>
									<label ng-click='clearCustom()' title='<?php echo $value[0]; ?>' style='background: <?php echo $value[2]; ?>'>
										<span><?php echo $value[0]; ?></span>
										<input type='radio' name='radio_bs' update-label value='<?php echo $value[1]; ?>' ng-model='Builder.form_background'>
									</label>
									<?php
								}
								?>
								<input type="text" value="#fff" angular-color class="color-picker" ng-model='Builder.form_background'>
							</div>
						</div>
						<div class='group'>
							<h2><?php _e('Custom Image Background','formcraft'); ?></h2>
						</div>
						<div>
							<input style='width: 100%; margin: 5px 0' type='text' placeholder='Image URL' ng-model='Builder.form_background_custom_image'>
						</div>					
					</div>
				</div>
			</div>	
			<div class='options-head'>
				<div>
					<a href='admin.php?page=formcraft3_dashboard' class='button' style='width: 126px; text-align: center'><i class='icon-angle-left'></i>Dashboard</a>
					<div>
						<button id='form_options_button' ng-click='Options.show_options = !Options.show_options; Options.show_styling = false; Options.show_addons = false; Options.show_logic = false' style='width: 114px' class='button active-{{Options.show_options}}'><i class='icon-cog-alt'></i> <?php _e('Options','formcraft') ?></button>
					</div>
					<div>
						<button style='width: 108px' id='form_styling_button' ng-click='Options.show_styling = !Options.show_styling; Options.show_addons = false; Options.show_options = false; Options.show_logic = false' class='button active-{{Options.show_styling}}'><i class='icon-pencil'></i> <?php _e('Styling','formcraft') ?></button>
					</div>
					<div>
						<button id='form_addons_button' ng-click='Options.show_addons = !Options.show_addons; Options.show_styling = false; Options.show_options = false; Options.show_logic = false' style='width: 114px' class='button active-{{Options.show_addons}}'><i class='icon-puzzle'></i> <?php _e('Add-Ons','formcraft') ?></button>
					</div>
					<div>
						<button id='form_logic_button' ng-click='Options.show_logic = !Options.show_logic; Options.show_styling = false; Options.show_options = false; Options.show_addons = false' style='width: 114px' class='button active-{{Options.show_logic}}'><i class='icon-shuffle'></i> <?php _e('Logic','formcraft') ?></button>
					</div>		
					<div>
						<button style='width: 135px' id='form_save_button' ng-click='saveForm()' class='button'><span class="fc-spinner small"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></span><span class='one'><i class='icon-up-circled'></i> <?php _e('Save Form','formcraft') ?></span></button>
						<button style='display: none' id='plugin-save' ng-click='saveForm("plugin_installed")'></button>
					</div>
					<div>
						<button style='width: 115px' type='submit' ng-click='saveForm("preview")' id='form_preview_button' class='button'><i class='icon-desktop'></i> <?php _e('Preview','formcraft') ?></button>
					</div>
					<div id='help_button_cover'>
						<button data-target='#help_modal' data-toggle='fc_modal' style='width: 88px' type='submit' id='help_button' class='button'><i class='icon-help-circled'></i> <?php _e('Help','formcraft') ?></button>
					</div>
				</div>
			</div>

			<style>
				{{Builder.Config.Custom_CSS}}
			</style>
			<div class='form-cover-builder hide-form hide-form-options'>
				<span class='fc-spinner fc-spinner-form small'><div class='bounce1'></div><div class='bounce2'></div><div class='bounce3'></div></span>
				<div id='form-cover-html' class='nos-{{Builder.FormElements.length}}{{Builder.FormElements[0].length}}' style='width: {{Builder.form_width_nos}}'>
					<div class='fc-pagination-cover fc-pagination-{{Builder.FormElements.length}}'>
						<div class='fc-pagination' style='width: {{Builder.form_width}}'>
							<div class='pagination-trigger' data-index='{{$index}}' ng-repeat='page in Builder.FormElements'>
								<span class='page-number'><span>{{$index+1}}</span></span>
								<span class='page-name'>{{Builder.Config.page_names[$index]}}</span>
								<!--RFH--><input type='text' ng-model='Builder.Config.page_names[$index]'><!--RTH-->
							</div>
						</div>
					</div>
					<!--RFH-->
					<div class='no-fields' ng-click='Options.show_fields = true'><?php _e('+ Add a Field','formcraft'); ?></div>
					<div style='width: {{Builder.form_width}}' class='form_width_change'><span><?php _e('Width','formcraft') ?></span><input ng-model='Builder.form_width' type='text'/>
					</div>
					<!--RTH-->
					<style scoped='scoped'>
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .submit-cover .submit-button,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .fileupload-cover .button-file,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover .button,
						#ui-datepicker-div.fc-datepicker .ui-datepicker-header,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .stripe-amount-show
						{
							background: {{Builder.Config.color_scheme_button}};
							color: {{Builder.Config.color_scheme_font}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .slider-cover .ui-slider-range
						{
							background: {{Builder.Config.color_scheme_button}};
						}
						#ui-datepicker-div.fc-datepicker .ui-datepicker-header,
						.formcraft-css .fc-form .field-cover>div.full hr
						{
							border-color: {{Builder.Config.color_scheme_button_dark}};
						}
						#ui-datepicker-div.fc-datepicker .ui-datepicker-prev:hover,
						#ui-datepicker-div.fc-datepicker .ui-datepicker-next:hover,
						#ui-datepicker-div.fc-datepicker select.ui-datepicker-month:hover,
						#ui-datepicker-div.fc-datepicker select.ui-datepicker-year:hover
						{
							background-color: {{Builder.Config.color_scheme_button_dark}};
						}
						.formcraft-css .fc-pagination>div.active .page-number,
						.formcraft-css .form-cover-builder .fc-pagination>div:first-child .page-number
						{
							background-color: {{Builder.Config.color_scheme_step}};
							border-color: {{Builder.Config.color_scheme_step_dark}};
							color: {{Builder.Config.color_scheme_font}};
						}
						#ui-datepicker-div.fc-datepicker table.ui-datepicker-calendar th,
						#ui-datepicker-div.fc-datepicker table.ui-datepicker-calendar td.ui-datepicker-today a,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .star-cover label,
						html .formcraft-css .fc-form.label-floating .form-element .field-cover.has-focus>span,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .customText-cover a,
						.formcraft-css .fc-form .form-element .slider-cover .range-max,
						.formcraft-css .fc-form .form-element .slider-cover .range-min,
						.formcraft-css .prev-next>div span:hover
						{
							color: {{Builder.Config.color_scheme_button}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .customText-cover a:hover
						{
							color: {{Builder.Config.color_scheme_button_dark}};
						}
						html .formcraft-css .fc-form.label-floating .form-element .field-cover > span
						{
							color: {{Builder.Config.font_color}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover input[type="text"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover input[type="email"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover input[type="password"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover input[type="tel"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover textarea,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover select,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>.label-floating .form-element .field-cover .time-fields-cover
						{
							border-bottom-color: {{Builder.Config.font_color}};
							color: {{Builder.Config.field_font_color}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover input[type="text"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover input[type="password"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover input[type="email"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover input[type="tel"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover select,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover textarea
						{
							background-color: {{Builder.Config.color_field_background}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .star-cover label .star			
						{
							text-shadow: 0px 1px 0px {{Builder.Config.color_scheme_button_dark}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .slider-cover .ui-slider-range
						{
							box-shadow: 0px 1px 1px {{Builder.Config.color_scheme_button_dark}} inset;
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .fileupload-cover .button-file,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .stripe-amount-show
						{
							border-color: {{Builder.Config.color_scheme_button_dark}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .stripe-cover.field-cover div.stripe-amount-show::before
						{
							border-top-color: {{Builder.Config.color_scheme_button_dark}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .stripe-cover.field-cover div.stripe-amount-show::after
						{
							border-right-color: {{Builder.Config.color_scheme_button}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .stripe-amount-show,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .submit-cover .submit-button .text,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .field-cover .button
						{
							text-shadow: 1px 0px 3px {{Builder.Config.color_scheme_button_dark}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="checkbox"]:checked,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="radio"]:checked
						{
							border-color: {{Builder.Config.color_scheme_checkbox}};
							background-color: {{Builder.Config.color_scheme_checkbox}};						
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="checkbox"]:hover,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="radio"]:hover,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="checkbox"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> input[type="radio"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> label:hover>input[type="checkbox"],
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> label:hover>input[type="radio"]
						{
							border-color: {{Builder.Config.color_scheme_checkbox}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html input[type="password"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html input[type="email"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html input[type="tel"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html input[type="text"]:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html textarea:focus,
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?> .form-element .form-element-html select:focus
						{
							border-color: {{Builder.Config.color_scheme_button}};
						}
						.formcraft-css .fc-form.fc-form-<?php echo $form_id; ?>
						{
							font-family: {{Builder.Config.font_family}};
						}
						@media (max-width : 480px) {
							html .dedicated-page,
							html .dedicated-page .formcraft-css .fc-pagination > div.active
							{
								background: {{Builder.form_background}};
							}
						}
					</style>
					<div class='form-cover'>
						<form data-thousand='{{Builder.Config.thousand_separator}}' data-decimal='{{Builder.Config.decimal_separator}}' data-delay='{{Builder.Config.Redirect_delay_seconds}}' data-id='<?php echo $form_id; ?>' class='fc-form fc-form-<?php echo $form_id; ?> label-{{Builder.label_style}} align-{{Builder.form_align}} fc-temp-class frame-{{Builder.form_frame}} spin-{{Builder.spin_effect}} save-form-{{Builder.Config.save_progress}} dont-submit-hidden-{{Builder.Config.dont_submit_hidden}} remove-asterisk-{{Builder.form_asterisk}} icons-hide-{{Builder.hide_icons}} field-alignment-{{Builder.form_internal_alignment}}' style='width: {{Builder.form_width}}; color: {{Builder.Config.font_color}}; font-size: {{Builder.font_size}}%; background: {{Builder.form_background}}'>
							<div class='form-page form-page-{{$index}}' ng-repeat='page in Builder.FormElements' data-index='{{$index}}'>
								<!--RFH-->
								<div class='delete-page' ng-click='removeFormPage($index)' title='<?php _e('Delete Page','formcraft'); ?>'>
									<i class='icon-cancel-circled'></i>
								</div>
								<!--RTH-->
								<div ng-init='builderInit()' ui-sortable="sortableOptions[$index]" ng-model='page' class='form-page-content'>
									<div ng-class-odd="'odd'" data-identifier='{{element.elementDefaults.identifier}}' ng-class='["form-element", "form-element-"+element.elementDefaults.identifier, "options-"+element.show_options, "index-"+element.show_options, "form-element-"+$index, "default-"+element.elementDefaults.hidden_default, "form-element-type-"+element.type, "is-required-"+element.elementDefaults.required]' ng-class-even="'even'" ng-repeat='element in page track by element.identifier' data-index='{{$index}}' style='width: {{element.elementDefaults.field_width}}'>
										<div ng-click='toggleOptions($parent.$index,$index)' watch-show-options='{{element.show_options}}' class='form-element-html' compile='element.element'>
										</div>
										<!--RFH-->
										<div class='form-options animate-{{element.show_options_animate}} state-{{element.show_options}}'>
											<div class='sub-options'>
												<div title='Field ID'>{{element.elementDefaults.identifier}}</div>
												<span title='Delete Field' ng-click='removeFormElement($parent.$index, $index)' class='delete'>
													<i class='icon-trash-empty'></i>
												</span>
												<span title='Duplicate Field' ng-click='duplicateFormElement($parent.$index, $index)' class='duplicate'>
													<i class='icon-docs-1'></i>
												</span>
											</div>
											<div class='options-main' compile='element.elementOptions'></div>
										</div>
										<!--RTH-->
									</div>
								</div>
							</div>
						</form>
						<div class='prev-next prev-next-{{Builder.FormElements.length}}' style='width: {{Builder.form_width}}; color: {{Builder.Config.font_color}}; font-size: {{Builder.font_size}}%; background: {{Builder.form_background}}'>
							<div><input type='text' ng-model='Builder.prevText'/><span class='inactive page-prev'><i class='icon-angle-left'></i>{{Builder.prevText}}</span></div>
							<div><input type='text' ng-model='Builder.nextText'/><span class='page-next'>{{Builder.nextText}}<i class='icon-angle-right'></i></span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="fc_modal fc_fade" id="general_modal">
				<div class="fc_modal-dialog">
					<div class="fc_modal-content">
						<div class="fc_modal-header">
							<button class='fc_close' type="button" class="close" data-dismiss="fc_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="fc_modal-title"><?php _e('Form Options','formcraft'); ?></h4>
						</div>
					</div>
				</div>
			</div>
			<div class="fc_modal fc_fade" id="captcha_modal">
				<?php
				include 'general/no-captcha.php';
				?>
			</div>
			<div class="fc_modal fc_fade" id="help_modal">
				<div class="fc_modal-dialog" style="width: 700px">
					<div class="fc_modal-content">
						<div class="fc_modal-body">
							<div id='help-menu'>
								<form id='help-search'>
									<input type='text' placeholder='<?php _e('Search','formcraft'); ?>'>
								</form>
								<ul>
								</ul>
							</div>
							<div id='help-content'>
								<div class='loader'>
									<div class="fc-spinner small">
										<div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div>
									</div>
								</div>
								<div id='help-content-content'></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="fc_modal fc_fade" id="autoresponder_modal" style="padding-top: 5px">
				<div class="fc_modal-dialog" style='width: 446px'>
					<div class="fc_modal-content">
						<div class="fc_modal-header" style='border-radius: 5px 5px 0 0'>
							<button class='fc_close' type="button" class="close" data-dismiss="fc_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h3><?php _e('Email Autoresponder','formcraft'); ?></h3>
						</div>
						<div class='fc_modal-body' style='padding: 25px; background: rgb(244, 244, 244); border-radius: 0 0 5px 5px'>
							<p>
								<?php _e('Send an auto-reply to users who complete the form. You can set the Sender Name, Sender Email details below. You can also customize the Subject and Email Body.','formcraft') ?>
							</p>
							<label>
								<span class='sub-label'><?php _e('Sender Name','formcraft'); ?></span><input type='text' style='width: 90%' ng-model="Builder.Config.autoresponder.email_sender_name" placeholder='<?php _e('John Smith','formcraft') ?>'/>
							</label>
							<label>
								<span class='sub-label'><?php _e('Sender Email','formcraft'); ?></span><input type='text' style='width: 100%' ng-model="Builder.Config.autoresponder.email_sender_email" placeholder='<?php _e('john@gmail.com','formcraft') ?>'/>
							</label>
							<label style='width: 100%; margin-bottom: 10px'>
								<span class='sub-label'><?php _e('Email Subject','formcraft'); ?></span><input type='text' style='width: 100%' ng-model="Builder.Config.autoresponder.email_subject" placeholder='<?php _e('Thank you for your submission','formcraft') ?>'/>
							</label>
							<label style='bottom: -29px; display: block'>
								<span class='sub-label'><?php _e('Email Content','formcraft'); ?></span>
							</label>
							<text-angular style='margin-top: 20px' class='textangular' ng-model="Builder.Config.autoresponder.email_body"></text-angular>
							<a class='trigger-help' data-post-id='186'><?php _e('read more about customising email content', 'formcraft'); ?></a>						
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<?php

	?>