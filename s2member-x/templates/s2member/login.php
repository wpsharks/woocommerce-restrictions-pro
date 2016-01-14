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
	<title><?php echo $this->translate('Account Login'); ?> | <?php echo esc_html(get_bloginfo('name')); ?></title>
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

				<a name="login" class="anchor"></a>

				<h1><?php echo $this->translate('Account Login'); ?></h1>

				<?php echo $this->responses(); /* Errors, successes, and other messages. */ ?>

				<form method="post" action="<?php echo esc_attr($this->©systematic->url('login')); ?>#login" class="login ui-form">

					<?php $call = '©user_utils.®login'; ?>
					<?php $form_fields = $this->©form_fields(array('for_call' => $call)); ?>

					<?php echo $this->©action->hidden_inputs_for_call($call, $this::public_type); ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr((string)$this->©vars->_REQUEST('redirect_to')); ?>" />

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->value($this->data->username),
						array(
						     'required'     => TRUE,
						     'autocomplete' => TRUE,
						     'type'         => 'text',
						     'name'         => $this->©action->input_name_for_call_arg(1),
						     'label'        => $this->translate('Username')
						)
					);
					?>

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->value($this->data->password),
						array(
						     'required'     => TRUE,
						     'autocomplete' => TRUE,
						     'type'         => 'password',
						     'name'         => $this->©action->input_name_for_call_arg(2),
						     'label'        => $this->translate('Password (caSe sensitive)')
						)
					);
					?>

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->value($this->data->remember),
						array(
						     'required'      => TRUE,
						     'type'          => 'radios',
						     'name'          => $this->©action->input_name_for_call_arg(3),
						     'label'         => $this->translate('Remember You?'),
						     'options'       => array(
							     array(
								     'value'      => '1',
								     'is_default' => TRUE,
								     'label'      => $this->translate('<strong>Yes</strong>, remember me on this computer.')
							     ),
							     array(
								     'value' => '0',
								     'label' => $this->translate('No (one session only)')
							     )
						     ),
						     'extra_details' => $this->translate('If you choose NO, closing your browser logs you out (e.g. you\'re logged in for one browser session only).')
						)
					);
					?>

					<input type="hidden" name="<?php echo esc_attr($this->©action->input_name_for_call_arg(4)); ?>" value="1" />

					<hr />

					<?php echo
					$form_fields->construct_field_markup(
						$form_fields->¤value($this->translate('Log In')),
						array(
						     'type' => 'submit',
						     'name' => 'login'
						)
					);
					?>

				</form>

				<div class="clear marginize"></div>

				<p class="float-half-left">
					« <a href="<?php echo esc_attr($this->©url->to_wp_lost_password()); ?>"><?php echo $this->translate('Lost Password?'); ?></a>
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