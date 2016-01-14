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
		class general_options extends menu_page
		{
			/**
			 * @var string Heading/title for this menu page.
			 */
			public $heading_title = 'General Options';

			/**
			 * @var string Sub-heading/description for this menu page.
			 */
			public $sub_heading_description = 'General s2Member® options/preferences.';

			/**
			 * @var boolean Does this menu page update options?
			 */
			public $updates_options = TRUE;

			/**
			 * Displays HTML markup producing content panels for this menu page.
			 */
			public function display_content_panels()
				{
					$this->add_content_panel($this->©menu_pages__panels__deactivation_safeguards($this));

					$this->display_content_panels_in_order();
				}
		}
	}