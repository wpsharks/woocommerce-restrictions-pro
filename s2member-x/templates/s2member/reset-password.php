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
<!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8" />
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo $this->translate('Reset Password'); ?> | <?php echo esc_html(get_bloginfo('name')); ?></title>
	<?php echo $this->stand_alone_styles().$this->stand_alone_scripts(); ?>
</head>
<body>

<div class="<?php echo esc_attr($this->stand_alone_wrapper_classes()); ?>">
	<div class="<?php echo esc_attr($this->stand_alone_container_classes()); ?>">

		<div class="header wrapper">
			<div class="header container">

				<?php echo $this->stand_alone_header(); ?>

			</div>
		</div>

		<div class="content wrapper">
			<div class="content container <?php echo esc_attr($this->ui_widget_classes()); ?>">

				<a name="reset_password" class="anchor"></a>

				<h1><?php echo $this->translate('Reset Password'); ?></h1>

				<?php echo $this->responses(); /* Errors, successes, and other messages. */ ?>

				<form method="post" action="<?php echo esc_attr($this->©url->current()); ?>#reset_password" class="reset-password ui-form">

					<?php $call = '©user_utils.®reset_password'; ?>
					<?php $form_fields = $this->©form_fields(array('for_call' => $call)); ?>

					<?php echo $this->©action->hidden_inputs_for_call($call, $this::public_type); ?>
					<input type="hidden" name="<?php echo esc_attr($this->©action->input_name_for_call_arg(1)); ?>" value="<?php echo esc_attr($this->data->activation_key); ?>" />

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->value($this->data->password),
						array(
						     'required' => TRUE,
						     'type'     => 'password',
						     'confirm'  => TRUE, // Ask to confirm.
						     'name'     => $this->©action->input_name_for_call_arg(2),
						     'label'    => $this->translate('Password (twice to confirm)')
						)
					);
					?>

					<hr />

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->¤value($this->translate('Reset Password')),
						array(
						     'type' => 'submit',
						     'name' => 'reset'
						)
					);
					?>

				</form>

				<div class="clear marginize"></div>

				<p class="float-half-left">
					« <a href="<?php echo esc_attr($this->©systematic->url('login')); ?>"><?php echo $this->translate('Account Login'); ?></a>
				</p>

				<p class="float-half-right">
					<a href="<?php echo esc_attr($this->©url->to_wp_home_uri()); ?>"><?php echo esc_html(get_bloginfo('name')); ?></a> »
				</p>

				<div class="clear"></div>

			</div>
		</div>

		<div class="footer wrapper">
			<div class="footer container">

				<?php echo $this->stand_alone_footer(); ?>

			</div>
		</div>

	</div>
</div>

</body>
</html>