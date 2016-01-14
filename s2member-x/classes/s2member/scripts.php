<?php
/**
 * Scripts.
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
		 * Scripts.
		 *
		 * @package s2Member
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class scripts extends \websharks_core_v000000_dev\scripts
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

					// Add components & register scripts (based on context).

					$scripts_to_register = array(); // Initialize array of scripts to register.

					if(!is_admin()) // Stand-alone/front-side scripts (but only IF we are loading them).
						{
							if($this->©options->get('scripts.front_side.load')) // Should these even be registered?
								{
									$this->front_side_components[] = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side';

									$scripts_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side'] = array(
										'deps' => array($this->___instance_config->core_ns_stub_with_dashes),
										'url'  => $this->©url->to_template_dir_file('client-side/scripts/front-side.min.js'),
										'ver'  => $this->___instance_config->plugin_version,
										'data' => $this->build_front_side_inline_data()
									);
									$this->stand_alone_components[]                                                                 = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--stand-alone';

									$scripts_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--stand-alone'] = array(
										'deps' => array($this->___instance_config->plugin_root_ns_stub_with_dashes.'--front-side'),
										'url'  => $this->©url->to_template_dir_file('client-side/scripts/stand-alone.min.js'),
										'ver'  => $this->___instance_config->plugin_version,
										'data' => $this->build_stand_alone_inline_data()
									);
								}
						}
					if(is_admin() && $this->©menu_page->is_plugin_page()) // For plugin menu pages.
						{
							$this->menu_page_components[] = $this->___instance_config->plugin_root_ns_stub_with_dashes.'--menu-pages';

							$scripts_to_register[$this->___instance_config->plugin_root_ns_stub_with_dashes.'--menu-pages'] = array(
								'deps' => array($this->___instance_config->core_ns_stub_with_dashes.'--menu-pages'),
								'url'  => $this->©url->to_plugin_dir_file('/client-side/scripts/menu-pages/menu-pages.min.js'),
								'ver'  => $this->___instance_config->plugin_version,
								'data' => '' // Already added by core.
							);
						}
					if($scripts_to_register) $this->register($scripts_to_register); // Register scripts.
				}

			/**
			 * Builds additional verifiers for core inline data.
			 *
			 * @return string Additional verifiers for core inline data.
			 */
			public function build_additional_verifiers_for_core_inline_data()
				{
					if(isset($this->cache[__FUNCTION__])) return $this->cache[__FUNCTION__];

					if(is_admin() && ($current_menu_page_class = $current_menu_page_slug = $this->©menu_pages->is_plugin_page()))
						{
							$this->©no_cache->constants(); // Private callers are NOT cacheable.
							$data = $this->©action->ajax_verifier_property_for_call('©menu_pages.®x', $this::private_type).',';
						}
					return ($this->cache[__FUNCTION__] = ((isset($data)) ? $data : ''));
				}
		}
	}