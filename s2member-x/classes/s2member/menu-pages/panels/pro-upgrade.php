<?php
/**
 * Menu Page Panel.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Menu_Pages\Panels
 * @since 120318
 */
namespace s2member\menu_pages\panels
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Menu Page Panel.
		 *
		 * @package s2Member\Menu_Pages\Panels
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class pro_upgrade extends panel
		{
			/**
			 * Constructor.
			 *
			 * @param object|array                   $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 *
			 * @param \s2member\menu_pages\menu_page $menu_page A menu page class instance.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function __construct($___instance_config, $menu_page)
				{
					parent::__construct($___instance_config, $menu_page);

					$this->heading_title = $this->i18n('s2Member® Pro Upgrade');

					$this->content_body =

						'<div style="text-align:center;">'.
						sprintf($this->i18n(
							        '<p style="margin:0;">s2Member® Pro is a recommended upgrade. <a href="%1$s" target="_blank" rel="xlink">Click here</a> to learn more.</p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/pro/'))).
						'</div>'.

						'<div style="text-align:center; margin-top:5px;">'.
						'<p style="margin:0;"><a href="'.esc_attr($this->©url->to_plugin_site_uri('/pro/')).'" target="_blank"><img src="'.esc_attr($this->©url->to_plugin_dir_file('/client-side/images/pro-addon-160x80.png')).'" style="width:160px; height:80px;" alt="" /></a></p>'.
						'</div>'.

						'<div style="text-align:center; margin-top:5px;">'.
						sprintf($this->i18n(
							        '<p style="margin:0;"><strong>See also:</strong> <a href="%1$s" target="_blank" rel="xlink">s2Member® Testimonials</a></p>'.
							        '<p style="margin:0;"><a href="%2$s" target="_blank" rel="xlink">s2Member® Prices / Licensing Options</a></p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/testimonials/')), esc_attr($this->©url->to_plugin_site_uri('/prices/'))).
						'</div>';
				}
		}
	}