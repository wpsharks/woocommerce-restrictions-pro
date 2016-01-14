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
		class donations extends panel
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

					$this->heading_title = $this->i18n('Donations Welcome');

					$form_fields = $this->©form_fields(); // Object instance.

					$this->content_body = // Donation panel (form field selection).

						'<div>'.
						sprintf($this->i18n(
							        '<p style="margin-top:0;">If you\'re running the free version of s2Member®, please consider making a donation to help support the continued development of this software.</p>'.
							        '<p style="margin-bottom:0;">Or, instead of making a donation, you could purchase <a href="%1$s" target="_blank" rel="xlink">s2Member® Pro</a>.</p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/pro/'))).
						'</div>'.

						'<form'.
						' method="GET"'.
						' target="_blank"'.
						' class="donate ui-form"'.
						' action="'.esc_attr($this->©url->to_plugin_site_uri('/r/donate/')).'"'.
						'>'.

						$form_fields->construct_field_markup(
							$form_fields->¤value(NULL),
							array(
							     'required' => TRUE,
							     'type'     => 'select',
							     'name'     => 'amount',
							     'options'  => array(
								     array(
									     'label' => $this->i18n('— Choose Donation Amount —'),
									     'value' => ''
								     ),
								     array(
									     'label' => $this->i18n('$5.00 USD'),
									     'value' => '5.00'
								     ),
								     array(
									     'label' => $this->i18n('$10.00 USD'),
									     'value' => '10.00'
								     ),
								     array(
									     'label' => $this->i18n('$15.00 USD'),
									     'value' => '15.00'
								     ),
								     array(
									     'label' => $this->i18n('$20.00 USD'),
									     'value' => '20.00'
								     ),
								     array(
									     'label' => $this->i18n('$25.00 USD'),
									     'value' => '25.00'
								     ),
								     array(
									     'label' => $this->i18n('$50.00 USD'),
									     'value' => '50.00'
								     ),
								     array(
									     'label' => $this->i18n('$75.00 USD'),
									     'value' => '75.00'
								     ),
								     array(
									     'label' => $this->i18n('$100.00 USD'),
									     'value' => '100.00'
								     ),
								     array(
									     'label' => $this->i18n('$150.00 USD'),
									     'value' => '150.00'
								     ),
								     array(
									     'label' => $this->i18n('$250.00 USD'),
									     'value' => '250.00'
								     ),
								     array(
									     'label' => $this->i18n('$500.00 USD'),
									     'value' => '500.00'
								     ),
								     array(
									     'label' => $this->i18n('$1000.00 USD'),
									     'value' => '1000.00'
								     ),
								     array(
									     'label' => $this->i18n('$2000.00 USD'),
									     'value' => '2000.00'
								     )
							     )
							)
						).
						$form_fields->construct_field_markup(
							$form_fields->¤value($this->i18n('Continue to PayPal®')),
							array(
							     'type' => 'submit',
							     'name' => 'donate'
							)
						).
						'</form>';
				}
		}
	}