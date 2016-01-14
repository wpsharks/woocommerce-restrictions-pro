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

<div class="<?php echo esc_attr($this->front_side_wrapper_classes('profile-summary', 'wrapper')); ?>" style="width:<?php echo esc_attr($this->data->attr['width']); ?> !important; margin:<?php echo esc_attr($this->data->attr['margin']); ?> !important;">
	<div class="<?php echo esc_attr($this->front_side_container_classes_plus((($this->data->attr['widgetize']) ? $this->ui_widget_classes('', $this::array_n) : array()))); ?>" style="font-size:<?php echo esc_attr($this->data->attr['font_size']); ?> !important; font-family:<?php echo esc_attr($this->data->attr['font_family']); ?> !important; padding:<?php echo esc_attr($this->data->attr['padding']); ?> !important;">

		<a name="profile_summary" class="anchor"></a>

		<?php if($this->data->attr['display_name_heading']): ?>
			<h2 title="<?php echo esc_attr($this->©user->display_name); ?>" class="text-clip"><?php echo esc_html($this->©user->display_name); ?></h2>
		<?php endif; ?>

		<?php echo $this->responses(); /* Errors, successes, and other messages. */ ?>

		<?php if($this->data->attr['display_avatar']): ?>
			<div class="avatar float-right">
				<a href="http://www.gravatar.com/" target="_blank"><?php echo get_avatar($this->©user->email, (integer)$this->data->attr['avatar_size']); ?></a>
			</div>
		<?php endif; ?>

		<?php if($this->data->content): ?>
			<div class="shortcode-content">
				<?php echo $this->data->content; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>

		<div class="clear"></div>

		<?php if($this->data->attr['display_links']): ?>
			<hr />

			<div class="float-half-left">
				« <a href="<?php echo esc_attr($this->©url->to_wp_logout($this->data->attr['logout_redirect_to_url'])); ?>"><?php echo $this->translate('Logout'); ?></a>
			</div>
			<div class="float-half-right">
				<a href="<?php echo esc_attr($this->©systematic->url('account')); ?>"><?php echo $this->translate('My Account'); ?></a> »
			</div>

			<div class="clear"></div>
		<?php endif; ?>

	</div>
</div>