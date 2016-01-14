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
	<title><?php echo $this->translate('Unsubscribe'); ?> | <?php echo esc_html(get_bloginfo('name')); ?></title>
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

				<a name="unsubscribe" class="anchor"></a>

				<h1><?php echo $this->translate('Unsubscribe'); ?></h1>

				<?php echo $this->responses(); /* Errors, successes, and other messages. */ ?>

				<div class="clear"></div>

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