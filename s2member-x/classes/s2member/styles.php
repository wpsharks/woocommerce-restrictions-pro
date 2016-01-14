<?php
/**
 * Styles.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Styles.
		 *
		 * @package s2Member
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class styles extends \websharks_core_v000000_dev\styles
		{
			/**
			 * Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					// Add components & register styles (based on context).

					$styles_to_register = array(); // Initialize array of styles to register.

					if(!is_admin()) // Stand-alone/front-side styles (but only IF we are loading them).
						{
							if($this->©options->get('styles.front_side.load')) // Should these even be registered?
								{
									$this->front_side_components[] = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side';

									$styles_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side'] = array(
										'deps' => $this->©options->get('styles.front_side.load_themes'),
										'url'  => $this->©url->to_template_dir_file('client-side/styles/front-side.min.css'),
										'ver'  => $this->___instance_config->plugin_version
									);

									$this->stand_alone_components[] = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--stand-alone';

									$styles_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--stand-alone'] = array(
										'deps' => array($this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side'),
										'url'  => $this->©url->to_template_dir_file('client-side/styles/stand-alone.min.css'),
										'ver'  => $this->___instance_config->plugin_version
									);
								}
						}
					if(is_admin() && $this->©menu_page->is_plugin_page()) // For plugin menu pages.
						{
							$this->menu_page_components[] = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--menu-pages';

							$styles_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--menu-pages'] = array(
								'deps' => array($this->___instance_config->core_ns_stub_with_dashes.'--menu-pages'),
								'url'  => $this->©url->to_plugin_dir_file('/client-side/styles/menu-pages/menu-pages.min.css'),
								'ver'  => $this->___instance_config->plugin_version
							);
						}
					if($styles_to_register) $this->register($styles_to_register); // Register styles.
				}
		}
	}