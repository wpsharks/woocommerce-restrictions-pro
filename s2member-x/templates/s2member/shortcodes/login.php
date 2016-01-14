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
 * @var $this framework Current plugin framework instance (extended by templates class).
 * @var $this \websharks_core_v000000_dev\templates Template instance (extends framework).
 * @var $this framework|\websharks_core_v000000_dev\templates
 */
if(!defined('WPINC'))
	exit('Do NOT access this file directly: '.basename(__FILE__));
?>

<div class="<?php echo esc_attr($this->front_side_wrapper_classes()); ?>" style="width:<?php echo esc_attr($this->data->attr['width']); ?> !important; margin:<?php echo esc_attr($this->data->attr['margin']); ?> !important;">
	<div class="<?php echo esc_attr($this->front_side_container_classes_plus((($this->data->attr['widgetize']) ? $this->ui_widget_classes('', $this::array_n) : array()))); ?>" style="font-size:<?php echo esc_attr($this->data->attr['font_size']); ?> !important; font-family:<?php echo esc_attr($this->data->attr['font_family']); ?> !important; padding:<?php echo esc_attr($this->data->attr['padding']); ?> !important;">

		<a name="login" class="anchor"></a>

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

		<form method="post" action="<?php echo esc_attr($this->©systematic->url('login')); ?>#login" class="login ui-form">

			<?php $call = '©user_utils.®login'; ?>
			<?php $form_fields = $this->©form_fields(array('for_call' => $call)); ?>

			<?php echo $this->©action->hidden_inputs_for_call($call, $this::public_type); ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr($this->data->attr['login_redirect_to_url']); ?>" />
			<input type="hidden" name="via_shortcode" value="1" />

			<?php echo
			$form_fields->construct_field_markup(
				$form_fields->value($this->data->username),
				array(
				     'required'     => TRUE,
				     'autocomplete' => TRUE,
				     'type'         => 'text',
				     'name'         => $this->©action->input_name_for_call_arg(1),
				     'label'        => $this->data->attr['username_label']
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
				     'label'        => $this->data->attr['password_label']
				)
			);
			?>

			<?php if($this->data->attr['display_remember_me']): ?>
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
							     'label'      => $this->translate('<strong>Yes</strong>, remember me.')
						     ),
						     array(
							     'value' => '0',
							     'label' => $this->translate('No (one session only).')
						     )
					     ),
					     'extra_details' => (($this->data->attr['display_remember_me_details'])
						     ? $this->translate('If you choose NO, closing your browser logs you out (e.g. you\'re logged in for one browser session only).')
						     : '')
					)
				);
				?>
			<?php else: ?>
				<input type="hidden" name="<?php echo esc_attr($this->©action->input_name_for_call_arg(3)); ?>" value="1" />
			<?php endif; ?>

			<input type="hidden" name="<?php echo esc_attr($this->©action->input_name_for_call_arg(4)); ?>" value="1" />

			<hr />

			<?php echo
			$form_fields->construct_field_markup(
				$form_fields->¤value($this->data->attr['submit_label']),
				array(
				     'type' => 'submit',
				     'name' => 'login'
				)
			);
			?>

		</form>

		<div class="clear"></div>

		<?php if($this->data->attr['display_links']): ?>
			<div class="marginize"></div>

			<p class="float-half-left">
				« <a href="<?php echo esc_attr($this->©url->to_wp_lost_password()); ?>"><?php echo $this->translate('Lost Password?'); ?></a>
			</p>

			<p class="float-half-right">
				<a href="<?php echo esc_attr($this->data->attr['register_url']); ?>"><?php echo esc_html($this->data->attr['register_text']); ?></a> »
			</p>

			<div class="clear"></div>
		<?php endif; ?>

	</div>
</div>