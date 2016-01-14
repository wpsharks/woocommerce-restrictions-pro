<?php
/**
 * Menu Page.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Menu_Pages
 * @since 120318
 */
namespace s2member\menu_pages
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Menu Page.
		 *
		 * @package s2Member\Menu_Pages
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class update_sync extends menu_page
		{
			/**
			 * @var string Heading/title for this menu page.
			 */
			public $heading_title = 'Update/Synchronize';

			/**
			 * @var string Sub-heading/description for this menu page.
			 */
			public $sub_heading_description = 'Keeps your copy of this plugin up-to-date.';

			/**
			 * Displays HTML markup producing content panels for this menu page.
			 */
			public function display_content_panels()
				{
					$this->add_content_panel($this->©menu_pages__panels__update_framework($this), TRUE);
					$this->add_content_panel($this->©menu_pages__panels__update_sync_pro($this), TRUE);

					$this->display_content_panels_in_order();
				}

			/**
			 * Updates s2Member® Framework.
			 *
			 * @param string $username Optional. Plugin site username. Defaults to an empty string.
			 *    This is ONLY required, if the underlying plugin site requires it.
			 *
			 * @param string $password Optional. Plugin site password. Defaults to an empty string.
			 *    This is ONLY required, if the underlying plugin site requires it.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function ®update_framework($username = '', $password = '')
				{
					$this->check_arg_types('string', 'string', func_get_args());

					if(!$this->©errors->exist_in($response = $this->©url->to_plugin_update_via_wp($username, $password)))
						$url = $response; // We got the update URL.
					else $errors = $response;

					$this->©action->set_call_data_for($this->dynamic_call(__FUNCTION__), get_defined_vars());

					if(!empty($url)) // We got the update URL. Perform the update now.
						wp_redirect($url).exit();
				}

			/**
			 * Updates (and synchronizes) s2Member® Pro.
			 *
			 * @param string $username Optional. Plugin site username. Defaults to an empty string.
			 *    This is ONLY required, if the underlying plugin site requires it.
			 *
			 * @param string $password Optional. Plugin site password. Defaults to an empty string.
			 *    This is ONLY required, if the underlying plugin site requires it.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function ®update_sync_pro($username = '', $password = '')
				{
					$this->check_arg_types('string', 'string', func_get_args());

					if(!$this->©errors->exist_in($response = $this->©url->to_plugin_pro_update_via_wp($username, $password)))
						$url = $response; // We got the update URL.
					else $errors = $response;

					$this->©action->set_call_data_for($this->dynamic_call(__FUNCTION__), get_defined_vars());

					if(!empty($url)) // We got the update URL. Perform the update now.
						wp_redirect($url).exit();
				}
		}
	}