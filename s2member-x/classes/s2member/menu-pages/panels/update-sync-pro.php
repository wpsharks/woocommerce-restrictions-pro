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
		class update_sync_pro extends panel
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

					$call        = '©menu_pages__update_sync.®update_sync_pro';
					$form_fields = $this->©form_fields(array('for_call' => $call));
					$data        = $this->©action->get_call_data_for($call);

					$username    = $this->©string->is_not_empty_or($data->username, '');
					$password    = $this->©string->is_not_empty_or($data->password, '');
					$credentials = $this->©plugin->get_site_credentials($username, $password);

					$this->heading_title = $this->i18n('s2Member® Pro Add-On (Update/Sync)');

					$this->content_body = // Pro update/sync form handler.

						$this->i18n(
							'<p>This will automatically update (and synchronize) your copy of the s2Member® Pro add-on, so that it matches your currently installed version of the s2Member® Framework. This update routine is powered (in part) by WordPress®. Depending on your configuration of WordPress®, you might be asked for FTP credentials before the update will begin. Please be sure to <strong>BACKUP</strong> your entire file structure and database before updating any WordPress® component.</p>'
						).

						'<form method="POST" action="'.esc_attr($this->©menu_page->url($this->menu_page->slug, $this->slug)).'" class="update-sync-pro ui-form">'.

						$this->©action->hidden_inputs_for_call($call, $this::private_type).

						$this->©action->get_call_responses_for($call).

						$form_fields->construct_field_markup(
							$form_fields->value($credentials['username']),
							array(
							     'required' => TRUE,
							     'type'     => 'text',
							     'label'    => $this->i18n('s2Member® Pro Username'),
							     'details'  => $this->i18n('The username for your customer account @ s2Member.com.'),
							     'name'     => $this->©action->input_name_for_call_arg(1)
							)
						).

						$form_fields->construct_field_markup(
							$form_fields->value($credentials['password']),
							array(
							     'required' => TRUE,
							     'type'     => 'password',
							     'label'    => $this->i18n('s2Member® Pro Password'),
							     'details'  => $this->i18n('Password is caSe sensitive (please type carefully).'),
							     'name'     => $this->©action->input_name_for_call_arg(2)
							)
						).

						$form_fields->construct_field_markup(
							$form_fields->¤value($this->i18n('s2Member® Pro Add-On (Update/Sync)')),
							array(
							     'type'                => 'submit',
							     'name'                => 'update_sync_pro',
							     'div_wrapper_classes' => 'form-submit update-sync-pro'
							)
						).
						'</form>';

					$this->documentation = 'Hello';
					$this->yt_playlist   = '769D5A5BEC7DB028';
				}
		}
	}