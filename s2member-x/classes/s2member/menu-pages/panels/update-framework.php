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
		class update_framework extends panel
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

					$call        = '©menu_pages__update_sync.®update_framework';
					$form_fields = $this->©form_fields(array('for_call' => $call));
					$data        = $this->©action->get_call_data_for($call);

					$username    = $this->©string->is_not_empty_or($data->username, '');
					$password    = $this->©string->is_not_empty_or($data->password, '');
					$credentials = $this->©plugin->get_site_credentials($username, $password);

					$this->heading_title = $this->i18n('s2Member® Framework (Update)');

					$this->content_body = // Updates s2Member® Framework (to the latest version).

						$this->i18n(
							'<p>This will automatically update your copy of the s2Member® Framework to the latest available version. This update routine is powered by WordPress®. Depending on your configuration of WordPress®, you might be asked for FTP credentials before the update will begin. The s2Member® Framework (which is free), can also be updated from the plugins menu in WordPress®. Please be sure to <strong>BACKUP</strong> your entire file structure and database before updating any WordPress® component.</p>'
						).

						'<form method="POST" action="'.esc_attr($this->©menu_page->url($this->menu_page->slug, $this->slug)).'" class="update-framework ui-form">'.

						$this->©action->hidden_inputs_for_call($call, $this::private_type).

						$this->©action->get_call_responses_for($call).

						$form_fields->construct_field_markup(
							$form_fields->¤value($this->i18n('s2Member® Framework (Update)')),
							array(
							     'type'                => 'submit',
							     'name'                => 'update_framework',
							     'div_wrapper_classes' => 'form-submit update-framework'
							)
						).
						'</form>';

					$this->documentation = '';
					$this->yt_playlist   = '';
				}
		}
	}