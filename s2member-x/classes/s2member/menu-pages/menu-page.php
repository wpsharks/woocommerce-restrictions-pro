<?php
/**
 * Menu Page (Base Class).
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
		 * Menu Page (Base Class).
		 *
		 * @package s2Member\Menu_Pages
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @note Dynamic properties/methods are defined explicitly here.
		 *    This way IDEs jive with ``__get()`` and ``__call()``.
		 *
		 * @method \s2member\menu_pages\panels\deactivation_safeguards ©menu_pages__panels__deactivation_safeguards()
		 * @method \s2member\menu_pages\panels\donations ©menu_pages__panels__donations()
		 * @method \s2member\menu_pages\panels\email_updates ©menu_pages__panels__email_updates()
		 * @method \s2member\menu_pages\panels\latest_news ©menu_pages__panels__latest_news()
		 * @method \s2member\menu_pages\panels\panel ©menu_pages__panels__panel()
		 * @method \s2member\menu_pages\panels\pro_upgrade ©menu_pages__panels__pro_upgrade()
		 * @method \s2member\menu_pages\panels\s2member_pages ©menu_pages__panels__s2member_pages()
		 * @method \s2member\menu_pages\panels\support_forums ©menu_pages__panels__support_forums()
		 * @method \s2member\menu_pages\panels\update_framework ©menu_pages__panels__update_framework()
		 * @method \s2member\menu_pages\panels\update_sync_pro ©menu_pages__panels__update_sync_pro()
		 * @method \s2member\menu_pages\panels\video_tutorials ©menu_pages__panels__video_tutorials()
		 */
		class menu_page extends \websharks_core_v000000_dev\menu_pages\menu_page
		{
			/**
			 * Displays HTML markup producing sidebar panels for this menu page.
			 *
			 * @return null Nothing.
			 */
			public function display_sidebar_panels()
				{
					if(!$this->©plugin->has_pro())
						$this->add_sidebar_panel($this->©menu_pages__panels__pro_upgrade($this), TRUE);

					$this->add_sidebar_panel($this->©menu_pages__panels__email_updates($this), TRUE);
					$this->add_sidebar_panel($this->©menu_pages__panels__latest_news($this));
					$this->add_sidebar_panel($this->©menu_pages__panels__support_forums($this));
					$this->add_sidebar_panel($this->©menu_pages__panels__video_tutorials($this));
					$this->add_sidebar_panel($this->©menu_pages__panels__donations($this));

					$this->display_sidebar_panels_in_order();
				}
		}
	}