<?php
/**
 * The `[s2_login]` Shortcode.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Shortcodes
 * @since 120318
 */
namespace s2member\shortcodes
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * The `[s2_login]` Shortcode.
		 *
		 * @package s2Member\Shortcodes
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @property \s2member\systematics $©systematics
		 * @property \s2member\systematics $©systematic
		 * @method \s2member\systematics ©systematics()
		 * @method \s2member\systematics ©systematic()
		 */
		class login extends \websharks_core_v000000_dev\shortcodes\shortcode
		{
			/**
			 * Gets default shortcode attributes.
			 *
			 * @return array Default shortcode attributes.
			 */
			public function attr_defaults()
				{
					return array(
						'theme'                       => '',
						'width'                       => '100%',
						'margin'                      => '0',
						'padding'                     => '10px',
						'font_size'                   => '100%',
						'font_family'                 => 'inherit',
						'widgetize'                   => '1',

						'display_heading'             => '1',
						'display_remember_me'         => '0',
						'display_remember_me_details' => '1',
						'display_links'               => '0',

						'heading'                     => $this->translate('Account Login'),

						'username_label'              => $this->translate('Username'),
						'password_label'              => $this->translate('Password (caSe sensitive)'),
						'submit_label'                => $this->translate('Log In'),

						'login_redirect_to_url'       => '',
						// One of: `%%previous%%` or `%%home%%`.
						// Or, a specific URL that is typed in manually (MUST be an on-site URL).
						// The default behavior is one which automatically redirects to the account page.
						// However, we ALWAYS look for a `redirect_to` request arg (which takes precedence over anything else).
						'register_url'                => $this->©systematic->url('register'),
						'register_text'               => $this->translate('Register')
					);
				}

			/**
			 * Gets all shortcode attribute keys, interpreted as boolean values.
			 *
			 * @return array Boolean attribute keys.
			 */
			public function boolean_attr_keys()
				{
					return array(
						'widgetize',
						'display_heading',
						'display_remember_me', 'display_remember_me_details',
						'display_links'
					);
				}

			/**
			 * Shortcode processor.
			 *
			 * @param string|array $attr An array of all shortcode attributes (if there were any).
			 *    Or, a string w/ the entire attributes section (when WordPress® fails to parse attributes).
			 *
			 * @param null|string  $content Shortcode content (or NULL for self-closing shortcodes).
			 *
			 * @param string       $shortcode The name of the shortcode.
			 *
			 * @return string Shortcode content.
			 *
			 * @throws \websharks_core_v000000_dev\exception If invalid types are passed through arguments list.
			 * @throws \websharks_core_v000000_dev\exception If ``$shortcode`` is empty.
			 */
			public function do_shortcode($attr, $content, $shortcode)
				{
					$this->check_arg_types(array('string', 'array'), array('null', 'string'), 'string:!empty', func_get_args());

					extract((array)$this->©action->get_call_data_for('©user_utils.®login'),
					        EXTR_PREFIX_SAME, 'xps'); // Prefix collisions.

					$attr                          = $this->normalize_attr_strings($attr);
					$attr['login_redirect_to_url'] = $this->©systematic->login_redirect($attr['login_redirect_to_url']);
					$content                       = do_shortcode($this->©string->trim_content((string)$content));

					return $this->©template('shortcodes/login.php', get_defined_vars(), $attr['theme'])->content;
				}
		}
	}