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
		class s2member extends menu_page
		{
			/**
			 * @var string Heading/title for this menu page.
			 */
			public $heading_title = 'Configuration Wizard';

			/**
			 * @var string Sub-heading/description for this menu page.
			 */
			public $sub_heading_description = 'Configuration wizard &amp; quick-start guide, for s2Member® site owners.';

			/**
			 * Displays HTML markup producing content panels for this menu page.
			 */
			public function display_content_panels()
				{
					$this->add_content_panel($this->©menu_pages__panels__s2member_pages($this));

					$this->display_content_panels_in_order();
				}

			/**
			 * Creates login page.
			 *
			 * @return integer Page ID, else 0 if creation fails.
			 */
			public function create_login_page()
				{
					$post = array(

						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',

						'post_type'      => 'page',
						'post_name'      => 'login',
						'post_title'     => 'Log In',

						'post_content'   => '[s2_user_login /]'

					);
					return wp_insert_post($post);
				}

			/**
			 * Creates signup page.
			 *
			 * @return integer Page ID, else 0 if creation fails.
			 */
			public function create_signup_page()
				{
					$post = array(

						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',

						'post_type'      => 'page',
						'post_name'      => 'signup',
						'post_title'     => 'Signup',

						'post_content'   => '[s2_user_signup /]'

					);
					return wp_insert_post($post);
				}

			/**
			 * Creates account page.
			 *
			 * @return integer Page ID, else 0 if creation fails.
			 */
			public function create_account_page()
				{
					$post = array(

						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',

						'post_type'      => 'page',
						'post_name'      => 'account',
						'post_title'     => 'My Account',

						'post_content'   => '[s2_user_profile /]'

					);
					return wp_insert_post($post);
				}
		}
	}