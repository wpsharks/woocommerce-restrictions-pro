<?php
/**
 * Menu Page Utilities.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Menu_Pages
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Menu Page Utilities.
		 *
		 * @package s2Member\Menu_Pages
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class menu_pages extends \websharks_core_v000000_dev\menu_pages
		{
			/**
			 * Handles WordPress® `admin_menu` hook.
			 *
			 * @attaches-to WordPress® `admin_menu` hook.
			 * @hook-priority Default is fine.
			 *
			 * @return null Nothing.
			 */
			public function admin_menu()
				{
					parent::admin_menu();

					$doc_title      = $this->___instance_config->plugin_name;
					$main_menu_slug = $this->___instance_config->plugin_root_ns_stub;

					$menu_pages = array(

						$main_menu_slug       => array(
							'doc_title'    => $doc_title,
							'menu_title'   => $this->___instance_config->plugin_name,
							'cap_required' => $this->©caps->map('manage_'.$this->___instance_config->plugin_root_ns, 'menu_page__'.$main_menu_slug),
							'displayer'    => array($this, '©menu_pages__'.$main_menu_slug.'.display'),
							'icon'         => $this->©url->to_plugin_dir_file('/client-side/images/favicon-16x16.png')
						),

						'___'.$main_menu_slug => array(
							'doc_title'    => $doc_title,
							'menu_title'   => $this->i18n('Config. Wizard'),
							'cap_required' => $this->©caps->map('manage_'.$this->___instance_config->plugin_root_ns, 'menu_page__'.$main_menu_slug),
							'displayer'    => array($this, '©menu_pages__'.$main_menu_slug.'.display'),
							'is_under'     => $main_menu_slug
						),

						'general_options'     => array(
							'doc_title'    => $doc_title,
							'menu_title'   => $this->i18n('General Options'),
							'cap_required' => $this->©caps->map('manage_'.$this->___instance_config->plugin_root_ns, 'menu_page__general_options'),
							'displayer'    => array($this, '©menu_pages__general_options.display'),
							'is_under'     => $main_menu_slug
						),

						'update_sync'         => array(
							'doc_title'    => $doc_title,
							'menu_title'   => $this->i18n('Update/Sync'),
							'cap_required' => $this->©caps->map('manage_'.$this->___instance_config->plugin_root_ns, 'menu_page__update_sync'),
							'displayer'    => array($this, '©menu_pages__update_sync.display'),
							'is_under'     => $main_menu_slug
						)
					);
					$this->add($menu_pages); // Add each of these menu pages to WordPress®.
				}

			/**
			 * Handles WordPress® `network_admin_menu` hook.
			 *
			 * @attaches-to WordPress® `network_admin_menu` hook.
			 * @hook-priority Default is fine.
			 *
			 * @return null Nothing.
			 */
			public function network_admin_menu()
				{
					parent::network_admin_menu();
				}
		}
	}