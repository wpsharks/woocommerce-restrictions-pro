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
		class email_updates extends panel
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

					$this->heading_title = $this->i18n('Updates Via Email');

					$form_fields = $this->©form_fields(); // Object instance.

					$this->content_body = // For updates via email (powered by MailChimp®).

						'<form'.
						' method="POST"'.
						' target="_blank"'.
						' class="email-updates ui-form"'.
						' action="http://websharks-inc.us1.list-manage1.com/subscribe/post?u=8f347da54d66b5298d13237d9&amp;id=19e9d213bc"'.
						'>'.

						'<div>'.
						'<img src="'.esc_attr($this->©url->to_plugin_dir_file('/client-side/images/email-64x64.png')).'" style="width:64px; height:64px; float:right; margin:0 0 0 10px;" alt="" />'.
						$this->i18n(
							'<p style="margin:0;">Receive important email updates from s2Member®. We send emails regarding new releases of this software; and also when we publish new articles in the s2Member® knowledge base.</p>'
						).
						'</div>'.

						$form_fields->construct_field_markup(
							$form_fields->value($this->©user->first_name),
							array(
							     'required' => TRUE,
							     'type'     => 'text',
							     'name'     => 'FNAME',
							     'label'    => $this->i18n('First Name')
							)
						).

						$form_fields->construct_field_markup(
							$form_fields->value($this->©user->last_name),
							array(
							     'required' => TRUE,
							     'type'     => 'text',
							     'name'     => 'LNAME',
							     'label'    => $this->i18n('Last Name')
							)
						).

						$form_fields->construct_field_markup(
							$form_fields->value($this->©user->email),
							array(
							     'required' => TRUE,
							     'type'     => 'email',
							     'name'     => 'EMAIL',
							     'label'    => $this->i18n('Email Address')
							)
						).

						$form_fields->construct_field_markup(
							$form_fields->¤value($this->i18n('Subscribe')),
							array(
							     'type' => 'submit',
							     'name' => 'subscribe'
							)
						).

						((!is_ssl() && !$this->©env->is_localhost()) ?
							'<div style="text-align:center;">'.
							'<script type="text/javascript" src="http://websharks-inc.us1.list-manage.com/subscriber-count?b=21&u=8c67d547-edf6-41c5-807d-2d2d0e6cffd1&id=19e9d213bc"></script>'.
							'</div>'
							: '').

						'<div style="text-align:center;">'.
						sprintf($this->i18n(
							        '<p style="margin:0;"><a href="%1$s" target="_blank" rel="xlink">we DO respect your privacy</a></p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/privacy/'))).
						'</div>'.

						'</form>';
				}
		}
	}