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
		class deactivation_safeguards extends panel
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

					$this->heading_title = $this->i18n('Deactivation Safeguards');

					$form_fields = $this->menu_page->option_fields;

					$this->content_body  =

						$form_fields->construct_field_markup(
							$form_fields->¤value($this->©options->get('installer.deactivation.uninstalls')),
							array(
							     'type'          => 'select',
							     'name'          => '[installer.deactivation.uninstalls]',
							     'label'         => $this->i18n('Preserve all data/options/tables upon deactivation of this plugin?'),
							     'details'       => $this->i18n('Otherwise known as Deactivation Safeguards (we recommend setting this to YES).'),
							     'extra_details' => $this->i18n('Recommendation: <code>YES</code> (preserve all data/options/tables).'),
							     'options'       => array(
								     array(
									     'value' => '0',
									     'label' => 'Yes (preserve all data/options/tables)'
								     ),
								     array(
									     'value' => '1',
									     'label' => 'No (deactivation completely uninstalls s2Member®)'
								     )
							     )
							)
						);
					$this->documentation = '';
					$this->yt_playlist   = '';
				}
		}
	}