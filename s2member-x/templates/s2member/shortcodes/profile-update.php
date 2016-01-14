<?php
namespace s2member;
/**
 * Template.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Templates
 * @since 120318
 *
 * @note All WordPress® template tags are available for use in this template.
 *    See: {@link http://codex.wordpress.org/Template_Tags}
 *    See: {@link http://codex.wordpress.org/Conditional_Tags}
 *
 * @note The current plugin instance is available through the special keyword: ``$this``.
 * @var $this \websharks_core_v000000_dev\templates|framework Template instance (extends framework).
 */
if(!defined('WPINC'))
	exit('Do NOT access this file directly: '.basename(__FILE__));
?>

<div class="<?php echo esc_attr($this->front_side_wrapper_classes('login', 'wrapper')); ?>" style="width:<?php echo esc_attr($this->data->attr['width']); ?> !important; margin:<?php echo esc_attr($this->data->attr['margin']); ?> !important;">
	<div class="<?php echo esc_attr($this->front_side_container_classes_plus((($this->data->attr['widgetize']) ? $this->ui_widget_classes('', $this::array_n) : array()))); ?>" style="font-size:<?php echo esc_attr($this->data->attr['font_size']); ?> !important; font-family:<?php echo esc_attr($this->data->attr['font_family']); ?> !important; padding:<?php echo esc_attr($this->data->attr['padding']); ?> !important;">

		<a name="profile_update" class="anchor"></a>

		<?php if($this->data->attr['display_heading']): ?>
			<h2 title="<?php echo esc_attr($this->data->attr['heading']); ?>" class="text-clip"><?php echo $this->data->attr['heading']; ?></h2>
		<?php endif; ?>

		<?php echo $this->responses(); /* Errors, successes, and other messages. */ ?>

		<?php if($this->data->content): ?>
			<div class="shortcode-content">
				<?php echo $this->data->content; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_attr($this->©url->current()); ?>#profile_update" enctype="multipart/form-data" class="profile-update ui-form">

			<?php $call = '©users.®profile_update'; ?>
			<?php $form_fields = $this->©form_fields(array('for_call' => $call, 'name_prefix' => $this->©action->input_name_for_call_arg(1))); ?>

			<?php echo $this->©action->hidden_inputs_for_call($call, $this::private_type); ?>
			<input type="hidden" name="<?php echo esc_attr($this->©action->input_name_for_call_arg(2)); ?>" value="<?php echo esc_attr($this->data->optional_requirements); ?>" />
			<input type="hidden" name="via_shortcode" value="1" />

			<?php if($this->data->attr['display_account_basics']): ?>
				<div class="panel wrapper account-basics">
					<div class="panel container">
						<h3 title="<?php echo esc_attr($this->data->attr['account_basics_heading']); ?>" class="text-clip"><a href="#"><?php echo $this->data->attr['account_basics_heading']; ?></a></h3>
						<div>
							<?php if($this->data->attr['display_username']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->username, $this->©user->username)),
									array(
									     'disabled' => TRUE,
									     'type'     => 'text',
									     'name'     => '[username]',
									     'label'    => $this->data->attr['username_label']
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_email']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->email, $this->©user->email)),
									array(
									     'required'            => TRUE,
									     'maxlength'           => 100,
									     'autocomplete'        => TRUE,
									     'type'                => 'email',
									     'name'                => '[email]',
									     'label'               => $this->data->attr['email_label'],
									     'validation_patterns' => array($this->©profile_field_validation_pattern->for_form_field('email'))
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_first_name']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->first_name, $this->©user->first_name)),
									array(
									     'required'     => (boolean)$this->data->attr['require_first_name'],
									     'maxlength'    => 100,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[first_name]',
									     'label'        => $this->data->attr['first_name_label']
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_last_name']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->last_name, $this->©user->last_name)),
									array(
									     'required'     => (boolean)$this->data->attr['require_last_name'],
									     'maxlength'    => 100,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[last_name]',
									     'label'        => $this->data->attr['last_name_label']
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_display_name']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->display_name, $this->©user->display_name)),
									array(
									     'required'     => (boolean)$this->data->attr['require_display_name'],
									     'maxlength'    => 250,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[display_name]',
									     'label'        => $this->data->attr['display_name_label']
									)
								);
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->data->attr['display_online_details']): ?>
				<div class="panel wrapper online-details">
					<div class="panel container">
						<h3 title="<?php echo esc_attr($this->data->attr['online_details_heading']); ?>" class="text-clip"><a href="#"><?php echo $this->data->attr['online_details_heading']; ?></a></h3>
						<div>
							<?php if($this->data->attr['display_url']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->url, $this->©user->url)),
									array(
									     'required'            => (boolean)$this->data->attr['require_url'],
									     'maxlength'           => 100,
									     'autocomplete'        => TRUE,
									     'type'                => 'url',
									     'name'                => '[url]',
									     'label'               => $this->data->attr['url_label'],
									     'validation_patterns' => array($this->©profile_field_validation_pattern->for_form_field('url'))
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_aim']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->aim, $this->©user->aim)),
									array(
									     'required'     => (boolean)$this->data->attr['require_aim'],
									     'maxlength'    => 100,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[aim]',
									     'label'        => $this->data->attr['aim_label']
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_yim']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->yim, $this->©user->yim)),
									array(
									     'required'     => (boolean)$this->data->attr['require_yim'],
									     'maxlength'    => 100,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[yim]',
									     'label'        => $this->data->attr['yim_label']
									)
								);
								?>
							<?php endif; ?>

							<?php if($this->data->attr['display_jabber']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->jabber, $this->©user->jabber)),
									array(
									     'required'     => (boolean)$this->data->attr['require_jabber'],
									     'maxlength'    => 100,
									     'autocomplete' => TRUE,
									     'type'         => 'text',
									     'name'         => '[jabber]',
									     'label'        => $this->data->attr['jabber_label']
									)
								);
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->data->attr['display_profile_fields']): ?>
				<?php if(($profile_fields = $this->©profile_fields->for_user_profile_update_form_fields($this->©user, $this->©user))): ?>
					<div class="panel wrapper profile-fields">
						<div class="panel container">
							<h3 title="<?php echo esc_attr($this->data->attr['profile_fields_heading']); ?>" class="text-clip"><a href="#"><?php echo $this->data->attr['profile_fields_heading']; ?></a></h3>
							<div>
								<?php
								foreach($profile_fields as $_profile_field_name => $_form_field_config)
									{
										$_value = $this->©var->isset_or(
											$this->data->profile_fields[$_profile_field_name],
											$this->©user->profile_field_value($_profile_field_name)
										);
										echo $form_fields->construct_field_markup($_value, $_form_field_config);
									}
								unset($_profile_field_name, $_form_field_config, $_value);
								?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if($this->data->attr['display_details_about_me']): ?>
				<div class="panel wrapper details-about-me">
					<div class="panel container">
						<h3 title="<?php echo esc_attr($this->data->attr['details_about_me_heading']); ?>" class="text-clip"><a href="#"><?php echo $this->data->attr['details_about_me_heading']; ?></a></h3>
						<div>
							<?php if($this->data->attr['display_description']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->¤value($this->©string->isset_or($this->data->description, $this->©user->description)),
									array(
									     'required'  => (boolean)$this->data->attr['require_description'],
									     'maxlength' => 5000,
									     'type'      => 'textarea',
									     'name'      => '[description]',
									     'label'     => $this->data->attr['about_me_label'],
									     'details'   => $this->translate('Some HTML is allowed. Please check our list of <a href="#" onclick="jQuery(\'div.allowed-tags\').toggle(); return false;">allowed tags</a>.<div class="allowed-tags" style="display:none;"><small><code>'.allowed_tags().'</code></small></div>')
									)
								);
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->data->attr['display_account_security']): ?>
				<div class="panel wrapper account-security">
					<div class="panel container">
						<h3 title="<?php echo esc_attr($this->data->attr['account_security_heading']); ?>" class="text-clip"><a href="#"><?php echo $this->data->attr['account_security_heading']; ?></a></h3>
						<div>
							<?php if($this->data->attr['display_password']): ?>
								<?php echo
								$form_fields->construct_field_markup(
									$form_fields->value($this->data->password),
									array(
									     'confirm'             => TRUE,
									     'type'                => 'password',
									     'name'                => '[password]',
									     'label'               => $this->data->attr['password_label'],
									     'validation_patterns' => array($this->©profile_field_validation_pattern->for_form_field('password'))
									)
								);
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<hr />

			<?php echo
			$form_fields->construct_field_markup(
				$form_fields->¤value($this->data->attr['submit_label']),
				array(
				     'type' => 'submit',
				     'name' => 'update'
				)
			);
			?>
		</form>

		<div class="clear"></div>

	</div>
</div>