<?php 
/*
Plugin Name: CF Get to Shortcode
Plugin URI: http://crowdfavorite.com
Description: Provides admin level users with the ability to set shortcode variables based on GET variables from the URL
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

// Constants
define('CFGTS_VERSION', '1.0');
define('CFGTS_DIR', plugin_dir_path(__FILE__));
define('CFGTS_DIR_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

load_plugin_textdomain('cfgts');


## INIT FUNCTIONALITY

/**
 * INIT handler for gathering in submitted content and sending it to the processor
 *
 * @return void
 */
function cfgts_request_handler() {
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfgts_settings':
				if (!empty($_POST['cfgts']) && is_array($_POST['cfgts'])) {
					cfgts_process($_POST['cfgts']);
					wp_redirect(admin_url('options-general.php?page=cfgts&updated=true'));
					die();
				}
				wp_redirect(admin_url('options-general.php?page=cfgts&updated=error'));
				die();
				break;
		}
	}
}
add_action('init', 'cfgts_request_handler');

/**
 * INIT handler for serving the CSS & JS
 *
 * @return void
 */
function cfgts_resources() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfgts_admin_js':
				cfgts_admin_js();
				die();
				break;
			case 'cfgts_admin_css':
				cfgts_admin_css();
				die();
				break;
		}
	}
	
}
add_action('init', 'cfgts_resources', 1);


## RESOURCES FUNCTIONALITY

/**
 * JS for the options form
 *
 * @return void
 */
function cfgts_admin_js() {
	header('Content-type: text/javascript');
	do_action('cfgts-admin-js');
	?>
	;(function($) {
		$(function() {
			// Add a new shortcode for a particular section
			$(".cfgts-add").live('click', function() {
				var _this = $(this);
				var section = _this.attr('id').replace('cfgts-add-', '');
				var id = new Date().valueOf();
				var key = id.toString();
				var newhtml = $("#cfgts-new-inputs tbody").html().replace(/##SECTION##/gi, section).replace(/##ID##/gi, key);
				
				$("#cfgts-content-"+section+" tbody").append(newhtml);
				$("#cfgts-save-reminder").show();
				return false;
			});
			
			// Show global shortcode input
			$("#cfgts-add-global-shortcode").live('click', function() {
				$("#cfgts-add-global-input").show();
				$("#cfgts-add-global-button-show").hide();
				$("#cfgts-add-global-button-hide").show();
			});

			// Hide global shortcode input
			$("#cfgts-hide-global-shortcode").live('click', function() {
				$("#cfgts-add-global-input").hide();
				$("#cfgts-add-global-button-show").show();
				$("#cfgts-add-global-button-hide").hide();
			});
			
			// Add global shortcode
			$("#cfgts-add-global-input-go").live('click', function() {
				var value = $("#cfgts-add-global-input-field").val();
				
				// Check to make sure the input isn't empty, if it is alert the user of this fact
				if (value != '') {
					$(".cfgts-section-content-table").each(function() {
						var _this = $(this);
						var section_id = _this.attr('id');
						var section = section_id.replace('cfgts-content-', '');
						var id = new Date().valueOf();
						var key = id.toString();
						var newhtml = $("#cfgts-new-inputs tbody").html().replace(/##SECTION##/gi, section).replace(/##ID##/gi, key);

						$("#"+section_id+" tbody").append(newhtml);
						$("#cfgts-shortcode-"+section+"-"+key).val(value);
					});
				}
				else {
					alert('<?php _e("No value entered for Global Shortcode.  Please enter a value, then click GO.", "cfgts"); ?>');
				}
				$("#cfgts-save-reminder").show();
				return false;
			});
			
			// Add a new get section
			$("#cfgts-add-new-section").live('click', function() {
				var id = new Date().valueOf();
				var key = id.toString();
				var newhtml = $("#cfgts-get-template").html().replace(/##SECTION##/gi, key);
				
				$("#cfgts-get-items").append(newhtml);
				$("#cfgts-save-reminder").show();
				return false;
			});
			
			// Save the Get Key and Value 
			$(".cfgts-get-values-save").live('click', function() {
				var _this = $(this);
				var section = _this.attr('id').replace('cfgts-get-values-save-', '');
				var key = $("#cfgts-get-values-key-input-"+section).val();
				var value = $("#cfgts-get-values-value-input-"+section).val();
				var description = $("#cfgts-get-values-description-input-"+section).val();
				
				if (key == '') {
					alert('<?php _e("The key must be set before saving for this section.  Please enter a key and click save again.", "cfgts"); ?>');
				}
				else {
					$("#cfgts-get-values-key-"+section).html(key);
					$("#cfgts-get-values-value-"+section).html(value);
					$("#cfgts-help-text-"+section).text(description).html();

					if (value == '') {
						$("#cfgts-get-values-value-display-"+section).hide();
					}
					if (description == '') {
						$("#cfgts-help-link-"+section).hide();
					}
					$("#cfgts-get-values-"+section).show();
					$("#cfgts-get-inputs-"+section).hide();
				}
				$("#cfgts-save-reminder").show();
			});
			
			// Remove a shortcode from a get section
			$(".cfgts-remove-shortcode").live('click', function() {
				if (confirm('Are you sure you want to delete this?')) {
					var _this = $(this);
					var key = _this.attr('id').replace('cfgts-remove-shortcode-', '');
					$("#cfgts-input-"+key).remove();
					$("#cfgts-save-reminder").show();
				}
			});
			
			// Hide and shoe help text for a particular shortcode
			$(".cfgts-help-shortcode").live('click', function() {
				var _this = $(this);
				var key = _this.attr('id').replace('cfgts-help-shortcode-', '');
				$("#cfgts-input-help-"+key).toggle();
			});
			
			// Remove a get section
			$(".cfgts-remove").live('click', function() {
				if (confirm('Are you sure you want to delete this?')) {
					var _this = $(this);
					var section = _this.attr('id').replace('cfgts-remove-', '');
					$("#cfgts-"+section).remove();
					$("#cfgts-content-"+section).remove();
					$("#cfgts-save-reminder").show();
				}
			});
			
			// Show the Get edit area for each section 
			$(".cfgts-edit-link a").live('click', function() {
				var _this = $(this);
				var section = _this.attr('rel');
				
				$("#cfgts-get-values-"+section).hide();
				$("#cfgts-get-inputs-"+section).show();
				return false;
			});
			
			// Hide and show the help text for each section
			$(".cfgts-help-link a").live('click', function() {
				var _this = $(this);
				var section = _this.attr('rel');
				$("#cfgts-help-text-"+section).slideToggle();
				$("#cfgts-help-link-hide-"+section).toggle();
				return false;
			});
			
			// Hide and show the data for a particular section
			$(".cfgts-hide-section-link").live('click', function() {
				var _this = $(this);
				var section = _this.attr('rel');
				
				$("#cfgts-content-"+section).toggle();
				$("#cfgts-hide-section-link-show-"+section).toggle();
				$("#cfgts-hide-section-link-hide-"+section).toggle();
				return false;
			});
		});
	})(jQuery);
	<?php
	die();
}

/**
 * CSS for the options form
 *
 * @return void
 */
function cfgts_admin_css() {
	header('Content-type: text/css');
	do_action('cfgts-admin-css');
	?>
	.cfgts-help-link,
	.cfgts-edit-link {
		font-size:11px;
		text-decoration:none;
	}
	.cfgts-help-text,
	.cfgts-help-link-hide {
		display:none;
	}
	.cfgts-remove-section {
		text-align:center;
	}
	
	.cfgts-spacer {
		margin-bottom:10px;
	}
	#cfgts_settings textarea {
		width:700px;
		height:100px;
	}
	#cfgts-add-global-input {
		background-color:#FFFFE0;
		border: 1px solid #E6DB55;
		-moz-border-radius:3px;
		-webkit-border-radius:3px;
		-khtml-border-radius:3px;
		border-radius:3px;
		margin:0 0 0 10px;
		padding:7px;
	}
	
	.cfgts-hide {
		display:none;
	}
	
	.cfgts-section-meta {
		float:left;
	}
	.cfgts-hide-section {
		float:right;
		margin:12px;
	}
	
	.cfgts-section-meta-table {
		-moz-border-radius-bottomright:0;
		-moz-border-radius-bottomleft:0;
		-webkit-border-bottom-right-radius:0;
		-webkit-border-bottom-left-radius:0;
		-khtml-border-bottom-right-radius:0;
		-khtml-border-bottom-left-radius:0;
		border-bottom-right-radius:0;
		border-bottom-left-radius:0;
		border-bottom:0;
	}
	
	.cfgts-section-content-table {
		-moz-border-radius-topright:0;
		-moz-border-radius-topleft:0;
		-webkit-border-top-right-radius:0;
		-webkit-border-top-left-radius:0;
		-khtml-border-top-right-radius:0;
		-khtml-border-top-left-radius:0;
		border-top-right-radius:0;
		border-top-left-radius:0;
	}
	
	<?php
	die();
}

if (!empty($_GET['page']) && $_GET['page'] == 'cfgts') {
	wp_enqueue_script('jquery');
	wp_enqueue_script('cfgts-admin-js', admin_url('?cf_action=cfgts_admin_js'), array('jquery'), CFGTS_VERSION);
	wp_enqueue_style('cfgts-admin-css', admin_url('?cf_action=cfgts_admin_css'), array(), CFGTS_VERSION, 'screen');
}


## PROCESSING FUNCTIONALITY

/**
 * Function for processing posted content and saving it to the database
 *
 * @param array $post | Posted content from the options form
 * @return void
 */
function cfgts_process($post) {
	$new_options = array();
	
	if (!empty($post['default'])) {
		$default = $post['default'];
		$default_shortcodes = array();
		
		if (!empty($default) && is_array($default)) {
			foreach ($default as $key => $value) {
				if (empty($value['shortcode'])) { continue; }
				
				$shortcode = sanitize_title($value['shortcode']);
				$shortcode_value = trim(stripslashes($value['value']));
				
				if (empty($default_shortcodes[$shortcode])) {
					$default_shortcodes[$shortcode] = array(
						'shortcode' => $shortcode,
						'value' => $shortcode_value
					);
				}
			}
		}
		
		$new_options['cfgts-default']['default'] = array(
			'key' => 'cfgts-default',
			'shortcode' => $default_shortcodes
		);
		
		unset($post['default']);
	}
	
	if (!empty($post) && is_array($post)) {
		foreach ($post as $key => $values) {
			if (empty($values['get']) || !is_array($values['get']) || empty($values['get']['key'])) { continue; }
			
			// Gather the get info
			$getkey = trim(strip_tags(stripslashes($values['get']['key'])));
			$getvalue = trim(strip_tags(stripslashes($values['get']['value'])));
			$getdescription = trim(strip_tags(stripslashes($values['get']['description'])));
			
			unset($values['get']);
			$shortcodes = array();
			
			if (!empty($values) && is_array($values)) {
				foreach ($values as $valuekey => $value) {
					if (empty($value['shortcode'])) { continue; }
					
					$shortcode = trim(strip_tags(stripslashes($value['shortcode'])));
					$shortcode_value = trim(stripslashes($value['value']));
					
					if (empty($shortcodes[$shortcode])) {
						$shortcodes[$shortcode] = array(
							'shortcode' => $shortcode,
							'value' => $shortcode_value
						);
					}
				}
			}
			
			$data = array(
				'key' => $getkey,
				'value' => $getvalue,
				'description' => $getdescription,
				'shortcode' => $shortcodes
			);
			
			if (!empty($getvalue) && empty($new_options[$getkey][$getvalue])) {
				$new_options[$getkey][$getvalue] = $data;
			}
			else if (empty($getvalue) && empty($new_options[$getkey]['cfgts-empty'])) {
				$new_options[$getkey]['cfgts-empty'] = $data;
			}
		}
	}
	
	update_option('cfgts_options', $new_options);
}


## ADMIN FUNCTIONALITY

/**
 * Adding the admin menu
 *
 * @return void
 */
function cfgts_admin_menu() {
	add_options_page(
		__('CF Get to Shortcode', 'cfgts'),
		__('CF Get to Shortcode', 'cfgts'),
		10,
		'cfgts',
		'cfgts_options'
	);
}
add_action('admin_menu', 'cfgts_admin_menu');

/**
 * Admin options form for entering content
 *
 * @return void
 */
function cfgts_options() {
	$options = get_option('cfgts_options');
	
	// Get the defaults for processing later
	$defaults = $options['cfgts-default']['default'];
	unset($options['cfgts-default']);
	
	$settings_saved = ' cfgts-hide';
	$settings_error = ' cfgts-hide';
	
	if (!empty($_GET['updated'])) {
		switch ($_GET['updated']) {
			case 'true':
				$settings_saved = '';
				break;
			case 'error':
				$settings_error = '';
				break;
		}
	}
	?>
	<div class="wrap">
		<?php echo screen_icon().'<h2>CF Get to Shortcode</h2>'; ?>
		<div class="cfgts-hide updated" id="cfgts-save-reminder">
			<p>
				<strong><?php _e("Settings have been changed, click the Save Settings button below to save changes.", "cfgts"); ?></strong>	
			</p>
		</div>
		<div class="updated<?php echo esc_attr($settings_saved); ?>" id="cfgts-settings-saved">
			<p>
				<strong><?php _e('Settings saved', 'cfgts'); ?></strong>
			</p>
		</div>
		<div class="updated<?php echo esc_attr($settings_error); ?>" id="cfgts-settings-error">
			<p>
				<strong><?php _e('Error saving settings.  Please try again', 'cfgts'); ?></strong>
			</p>
		</div>
		<form id="cfgts_settings" name="cfgts_settings" action="" method="post">
			<div id="cfgts-get-items">
				<?php
				if (!empty($options) && is_array($options)) {
					foreach ($options as $key => $option) {
						if (!empty($option) && is_array($option)) {
							foreach ($option as $value => $data) {
								$section = esc_attr($key.'-'.$value);
								$value_display = '';
								if (empty($data['value'])) {
									$value_display = ' style="display:none;"';
								}
								$help_display = '';
								if (empty($data['description'])) {
									$help_display = ' style="display:none;"';
								}
								?>
								<table id="cfgts-<?php echo $section; ?>" class="widefat cfgts-section-meta-table">
									<thead>
										<tr>
											<td colspan="3">
												<span id="cfgts-get-inputs-<?php echo $section; ?>" style="display:none" class="cfgts-section-meta">
													<?php _e('Key: ', 'cfgts'); ?><input type="text" id="cfgts-get-values-key-input-<?php echo $section; ?>" name="cfgts[<?php echo $section; ?>][get][key]" value="<?php echo esc_attr($data['key']); ?>" />
													<?php _e('Value: ', 'cfgts'); ?><input type="text" id="cfgts-get-values-value-input-<?php echo $section; ?>" name="cfgts[<?php echo $section; ?>][get][value]" value="<?php echo esc_attr($data['value']); ?>" />
													<input type="button" id="cfgts-get-values-save-<?php echo $section; ?>" value="<?php _e('Save', 'cfgts'); ?>" class="button cfgts-get-values-save" />
													<br />
													<?php _e('Description (Optional):', 'cfgts'); ?><br />
													<textarea id="cfgts-get-values-description-input-<?php echo $section; ?>" name="cfgts[<?php echo $section; ?>][get][description]"><?php echo htmlentities($data['description']); ?></textarea>
												</span>
												<span id="cfgts-get-values-<?php echo $section; ?>" class="cfgts-section-meta">
													<h3>
														<?php _e('Key: ', 'cfgts'); ?>
														<span id="cfgts-get-values-key-<?php echo $section; ?>">
															<?php echo esc_attr($data['key']); ?>
														</span>
														<span id="cfgts-get-values-value-display-<?php echo $section; ?>"<?php echo $value_display; ?>>
															 | <?php _e('Value: ', 'cfgts'); ?>
															<span id="cfgts-get-values-value-<?php echo $section; ?>">
																<?php echo esc_attr($data['value']); ?>
															</span>
														</span>
														<span id="cfgts-edit-link-<?php echo $section; ?>" class="cfgts-edit-link">
															| <a href="#" rel="<?php echo $section; ?>">
																<?php _e('Edit', 'cfgts'); ?>
															</a>
														</span>
														<span id="cfgts-help-link-<?php echo $section; ?>" class="cfgts-help-link"<?php echo $help_display; ?>>
															 | <a href="#" rel="<?php echo $section; ?>">
																<span id="cfgts-help-link-hide-<?php echo $section; ?>" class="cfgts-help-link-hide">
																	<?php _e('Hide ', 'cfgts'); ?>
																</span>
																<?php _e('Description', 'cfgts'); ?>
															</a>
														</span>
													</h3>
													<div id="cfgts-help-text-<?php echo $section; ?>" class="cfgts-help-text">
														<p>
															<?php echo htmlentities($data['description']); ?>
														</p>
													</div>
												</span>
												<span id="cfgts-hide-section-<?php echo $section; ?>" class="cfgts-hide-section">
													<a href="#" rel="<?php echo $section; ?>" class="cfgts-hide-section-link"><span id="cfgts-hide-section-link-hide-<?php echo $section; ?>"><?php _e('Hide', 'cfgts'); ?></span><span id="cfgts-hide-section-link-show-<?php echo $section; ?>" style="display:none;"><?php _e('Show', 'cfgts'); ?></span><?php _e(' Data', 'cfgts'); ?></a>
												</span>
												<div class="clear"></div>
											</td>
										</tr>
									</thead>
								</table>
								<table id="cfgts-content-<?php echo $section; ?>" class="widefat cfgts-section-content-table">
									<thead>
										<tr>
											<th width="25%">
												<?php _e('Shortcode Name', 'cfgts'); ?>
											</th>
											<th width="65%">
												<?php _e('Shortcode Value', 'cfgts'); ?>
											</th>
											<th style="width:10%; text-align:center;">
												<?php _e('Remove', 'cfgts'); ?>
											</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th width="25%">
												<?php _e('Shortcode Name', 'cfgts'); ?>
											</th>
											<th width="65%">
												<?php _e('Shortcode Value', 'cfgts'); ?>
											</th>
											<th style="width:10%; text-align:center;">
												<?php _e('Remove', 'cfgts'); ?>
											</th>
										</tr>
										<tr>
											<td colspan="2">
												<input type="button" class="button cfgts-add" id="cfgts-add-<?php echo $section; ?>" value="<?php _e('Add Shortcode', 'cfgts'); ?>" />
												<input type="button" class="button cfgts-remove" id="cfgts-remove-<?php echo $section; ?>" value="<?php _e('Remove Section', 'cfgts'); ?>" />
											</td>
										</tr>
									</tfoot>
									<tbody>
										<?php
										if (!empty($data['shortcode']) && is_array($data['shortcode'])) {
											foreach ($data['shortcode'] as $key2 => $data2) {
												if (empty($data2['shortcode'])) { continue; }
												?>
												<tr id="cfgts-input-<?php echo $section; ?>-<?php echo $key; ?>">
													<td>
														<input name="cfgts[<?php echo $section; ?>][<?php echo esc_attr($key2); ?>][shortcode]" id="cfgts-shortcode-<?php echo $section; ?>-<?php echo esc_attr($key2); ?>" class="widefat" type="text" value="<?php echo esc_attr($data2['shortcode']); ?>" />
													</td>
													<td>
														<input name="cfgts[<?php echo $section; ?>][<?php echo esc_attr($key2); ?>][value]" id="cfgts-value-<?php echo $section; ?>-<?php echo esc_attr($key2); ?>" class="widefat" type="text" value="<?php echo esc_attr($data2['value']); ?>" />
													</td>
													<td class="cfgts-remove-section">
														<input name="cfgts[<?php echo $section; ?>][<?php echo esc_attr($key2); ?>][help]" id="cfgts-help-shortcode-<?php echo $section; ?>-<?php echo esc_attr($key2); ?>" type="button" class="button cfgts-help-shortcode" value="<?php _e('Help', 'cfgts'); ?>" />
														<input name="cfgts[<?php echo $section; ?>][<?php echo esc_attr($key2); ?>][remove]" id="cfgts-remove-shortcode-<?php echo $section; ?>-<?php echo esc_attr($key2); ?>" type="button" class="button cfgts-remove-shortcode" value="<?php _e('Remove', 'cfgts'); ?>" />
													</td>
												</tr>
												<tr id="cfgts-input-help-<?php echo $section; ?>-<?php echo $key2; ?>" style="display:none;">
													<td colspan="3">
														<?php _e('Shortcode: ', 'cfgts'); ?><code>[cfgts name="<?php echo esc_attr($data2['shortcode']); ?>"]</code>
													</td>
												<tr>
												<?php
											}
										}
										?>
									</tbody>
								</table>
								<div class="cfgts-spacer"></div>
								<?php
							}
						}
					}
				}
				?>
			</div>
			<div id="cfgts-get-default">
				<table id="cfgts-default" class="widefat cfgts-section-meta-table">
					<thead>
						<tr>
							<td colspan="3">
								<span id="cfgts-get-values-default" class="cfgts-section-meta">
									<h3><?php _e('Defaults', 'cfgts'); ?><span class="cfgts-help-link"> | <a href="#" rel="default"><span id="cfgts-help-link-hide-default" class="cfgts-help-link-hide"><?php _e('Hide ', 'cfgts'); ?></span><?php _e('Description', 'cfgts'); ?></a></span></h3>
									<div id="cfgts-help-text-default" class="cfgts-help-text">
										<p>
											<?php _e('Used when no GET variables are present in the URL. These will also be used if a shortcode name is not set in any other places.', 'cfgts'); ?>
										</p>
									</div>
								</span>
								<span id="cfgts-hide-section-default" class="cfgts-hide-section">
									<a href="#" rel="default" class="cfgts-hide-section-link"><span id="cfgts-hide-section-link-hide-default"><?php _e('Hide', 'cfgts'); ?></span><span id="cfgts-hide-section-link-show-default" style="display:none;"><?php _e('Show', 'cfgts'); ?></span><?php _e(' Data', 'cfgts'); ?></a>
								</span>
								<div class="clear"></div>
							</td>
						</tr>
					</thead>
				</table>
				<table id="cfgts-content-default" class="widefat cfgts-section-content-table">
					<thead>
						<tr>
							<th width="25%">
								<?php _e('Shortcode Name', 'cfgts'); ?>
							</th>
							<th width="65%">
								<?php _e('Shortcode Value', 'cfgts'); ?>
							</th>
							<th style="width:10%; text-align:center;">
								<?php _e('Remove', 'cfgts'); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th width="25%">
								<?php _e('Shortcode Name', 'cfgts'); ?>
							</th>
							<th width="65%">
								<?php _e('Shortcode Value', 'cfgts'); ?>
							</th>
							<th style="width:10%; text-align:center;">
								<?php _e('Remove', 'cfgts'); ?>
							</th>
						</tr>
						<tr>
							<td colspan="2">
								<input type="button" class="button cfgts-add" id="cfgts-add-default" value="<?php _e('Add Shortcode', 'cfgts'); ?>" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
						if (!empty($defaults) && is_array($defaults) && !empty($defaults['shortcode']) && is_array($defaults['shortcode'])) {
							foreach ($defaults['shortcode'] as $key => $data) {
								if (empty($data['shortcode'])) { continue; }
								?>
								<tr id="cfgts-input-default-<?php echo $key; ?>">
									<td>
										<input name="cfgts[default][<?php echo esc_attr($key); ?>][shortcode]" id="cfgts-shortcode-default-<?php echo esc_attr($key); ?>" class="widefat" type="text" value="<?php echo esc_attr($data['shortcode']); ?>" />
									</td>
									<td>
										<input name="cfgts[default][<?php echo esc_attr($key); ?>][value]" id="cfgts-value-default-<?php echo esc_attr($key); ?>" class="widefat" type="text" value="<?php echo esc_attr($data['value']); ?>" />
									</td>
									<td class="cfgts-remove-section">
										<input name="cfgts[default][<?php echo esc_attr($key); ?>][help]" id="cfgts-help-shortcode-default-<?php echo esc_attr($key); ?>" type="button" class="button cfgts-help-shortcode" value="<?php _e('Help', 'cfgts'); ?>" />
										<input name="cfgts[default][<?php echo esc_attr($key); ?>][remove]" id="cfgts-remove-shortcode-default-<?php echo esc_attr($key); ?>" type="button" class="button cfgts-remove-shortcode" value="<?php _e('Remove', 'cfgts'); ?>" />
									</td>
								</tr>
								<tr id="cfgts-input-help-default-<?php echo $key; ?>" style="display:none;">
									<td colspan="3">
										<?php _e('Shortcode: ', 'cfgts'); ?><code>[cfgts name="<?php echo esc_attr($data['shortcode']); ?>"]</code>
									</td>
								<tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
			<p>
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'cfgts'); ?>" />
				<input type="button" class="button" id="cfgts-add-new-section" value="<?php _e('Add New Get Option', 'cfgts'); ?>" />
				<span id="cfgts-add-global-button-show">
					<input type="button" class="button" id="cfgts-add-global-shortcode" value="<?php _e('Add Global Shortcode', 'cfgts'); ?>" />
				</span>
				<span id="cfgts-add-global-button-hide" style="display:none">
					<input type="button" class="button" id="cfgts-hide-global-shortcode" value="<?php _e('Hide Global Shortcode', 'cfgts'); ?>" />
				</span>
				<span id="cfgts-add-global-input" style="display:none;">
					<?php _e('Enter new Shortcode name here, then click GO: ', 'cfgts'); ?><input type="text" id="cfgts-add-global-input-field" name="cfgts-add-global-input-field" autocomplete="off" />
					<input type="button" class="button" id="cfgts-add-global-input-go" value="<?php _e('GO', 'cfgts'); ?>" />
				</span>
				<input type="hidden" name="cf_action" value="cfgts_settings" />
			</p>
		</form>
		<table id="cfgts-new-inputs" style="display:none">
			<tbody>
				<tr id="cfgts-input-##SECTION##-##ID##">
					<td>
						<input name="cfgts[##SECTION##][##ID##][shortcode]" id="cfgts-shortcode-##SECTION##-##ID##" class="widefat" type="text" />
					</td>
					<td>
						<input name="cfgts[##SECTION##][##ID##][value]" id="cfgts-value-##SECTION##-##ID##" class="widefat" type="text" />
					</td>
					<td class="cfgts-remove-section">
						<input name="cfgts[##SECTION##][##ID##][remove]" id="cfgts-remove-shortcode-##SECTION##-##ID##" type="button" class="button cfgts-remove-shortcode" value="<?php _e('Remove', 'cfgts'); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
		<div id="cfgts-get-template" style="display:none;">
			<table id="cfgts-##SECTION##" class="widefat cfgts-section-meta-table">
				<thead>
					<tr>
						<td colspan="2">
							<span id="cfgts-get-inputs-##SECTION##">
								<?php _e('Key: ', 'cfgts'); ?><input type="text" id="cfgts-get-values-key-input-##SECTION##" name="cfgts[##SECTION##][get][key]" value="" />
								<?php _e('Value: ', 'cfgts'); ?><input type="text" id="cfgts-get-values-value-input-##SECTION##" name="cfgts[##SECTION##][get][value]" value="" />
								<input type="button" id="cfgts-get-values-save-##SECTION##" value="<?php _e('Save', 'cfgts'); ?>" class="button cfgts-get-values-save" />
								<br />
								<?php _e('Description (Optional):', 'cfgts'); ?><br />
								<textarea id="cfgts-get-values-description-input-##SECTION##" name="cfgts[##SECTION##][get][description]"></textarea>
							</span>
							<span id="cfgts-get-values-##SECTION##" style="display:none">
								<h3>
									<?php _e('Key: ', 'cfgts'); ?><span id="cfgts-get-values-key-##SECTION##"></span><span id="cfgts-get-values-value-display-##SECTION##"> | <?php _e('Value: ', 'cfgts'); ?><span id="cfgts-get-values-value-##SECTION##"></span></span><span id="cfgts-help-link-##SECTION##" class="cfgts-help-link"> | <a href="#" rel="##SECTION##"><span id="cfgts-help-link-hide-##SECTION##" class="cfgts-help-link-hide"><?php _e('Hide ', 'cfgts'); ?></span><?php _e('Description', 'cfgts'); ?></a></span>
								</h3>
								<div id="cfgts-help-text-##SECTION##" class="cfgts-help-text">
								</div>
							</span>
						</td>
					</tr>
				</thead>
			</table>
			<table id="cfgts-content-##SECTION##" class="widefat cfgts-section-content-table">
				<thead>
					<tr>
						<th width="25%">
							<?php _e('Shortcode Name', 'cfgts'); ?>
						</th>
						<th width="65%">
							<?php _e('Shortcode Value', 'cfgts'); ?>
						</th>
						<th style="width:10%; text-align:center;">
							<?php _e('Remove', 'cfgts'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th width="25%">
							<?php _e('Shortcode Name', 'cfgts'); ?>
						</th>
						<th width="65%">
							<?php _e('Shortcode Value', 'cfgts'); ?>
						</th>
						<th style="width:10%; text-align:center;">
							<?php _e('Remove', 'cfgts'); ?>
						</th>
					</tr>
					<tr>
						<td colspan="2">
							<input type="button" class="button cfgts-add" id="cfgts-add-##SECTION##" value="<?php _e('Add Shortcode', 'cfgts'); ?>" />
						</td>
					</tr>
				</tfoot>
				<tbody>
				</tbody>
			</table>
			<div class="cfgts-spacer"></div>
		</div>
	</div>
	<?php
}


## SHORTCODE FUNCTIONALITY

/**
 * Function for displaying the shortcode using WordPress functionality
 *
 * @param array $atts | Attributes sent by WordPress
 * @return string | Shortcode content for shortcode/get vars used
 */
function cfgts_shortcode($atts) {
	$atts = extract(shortcode_atts(array('name'=>''),$atts));
	
	if (empty($name)) { return; }
	
	$options = get_option('cfgts_options');
	$defaults = $options['cfgts-default']['default'];
	unset($options['cfgts-default']);
	$gets = '';
	$keyusing = '';
	$valueusing = '';
	$optionsusing = '';
	
	if (!empty($_GET)) {
		$gets = $_GET;
	}
	
	if (!empty($gets) && is_array($gets)) {
		foreach ($gets as $key => $value) {
			if (empty($options[$key])) { continue; }
			
			$keyusing = $key;
			$valueusing = $value;
			
			if (!empty($options[$key][$value])) {
				$optionsusing = $options[$key][$value];
			}
			else {
				$optionsusing = $options[$key]['cfgts-empty'];
			}
			break;
		}
	}
	
	// Lets look for the shortcode key, and if we find it, return the value
	if (is_array($optionsusing['shortcode'])) {
		foreach ($optionsusing['shortcode'] as $key => $value) {
			if ($key != $name) { continue; }
			if (!empty($value['value'])) {
				return $value['value'];
			}
		}
	}
	
	// If we haven't already found a shortcode, lets use the default list and try to find one there
	if (is_array($defaults) && is_array($defaults['shortcode'])) {
		foreach ($defaults['shortcode'] as $key => $value) {
			if ($key != $name) { continue; }
			if (!empty($value['value'])) {
				return $value['value'];
			}
		}
	}
	
	// At this point, we didn't find anything to return back, so just leave the function
	return;
}
add_shortcode('cfgts','cfgts_shortcode');


## AUXILLARY FUNCTIONALITY

/**
 * Functions for adding plugin readme content to the CF ReadMe plugin
 *
 * @return void
 */
if (function_exists('cfreadme_enqueue')) {
	function cfgts_add_readme() {
		cfreadme_enqueue('cfgts', 'cfgts_readme');
	}
	add_action('admin_init', 'cfgts_add_readme');
	
	function cfgts_readme() {
		$file = CFGTS_DIR.'README.txt';
		if (is_file($file) && is_readable($file)) {
			$markdown = file_get_contents($file);
			$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|', '![$1]('.CFGTS_DIR.'/$2)', $markdown);
			return $markdown;
		}
		return null;
	}
}



?>
