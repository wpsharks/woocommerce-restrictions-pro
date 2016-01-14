<?php
/**
 * The `[s2_profile_update]` Shortcode.
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
		 * The `[s2_profile_update]` Shortcode.
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
		class profile_update extends \websharks_core_v000000_dev\shortcodes\shortcode
		{
			/**
			 * Gets default shortcode attributes.
			 *
			 * @return array Default shortcode attributes.
			 */
			public function attr_defaults()
				{
					return array(
						'theme'                    => '',
						'width'                    => '100%',
						'margin'                   => '0',
						'padding'                  => '10px',
						'font_size'                => '100%',
						'font_family'              => 'inherit',
						'widgetize'                => '1',

						'display_heading'          => '1',
						'display_account_basics'   => '1',
						'display_online_details'   => '1',
						'display_profile_fields'   => '1',
						'display_details_about_me' => '1',
						'display_account_security' => '1',

						'display_username'         => '1',
						'display_email'            => '1',
						'display_first_name'       => '1',
						'display_last_name'        => '1',
						'display_display_name'     => '1',

						'display_url'              => '1',
						'display_aim'              => '1',
						'display_yim'              => '1',
						'display_jabber'           => '1',

						'display_description'      => '1',

						'display_password'         => '1',

						'heading'                  => $this->translate('My Profile'),
						'account_basics_heading'   => $this->translate('Account Basics'),
						'online_details_heading'   => $this->translate('Online Details'),
						'profile_fields_heading'   => $this->translate('Additional Details'),
						'details_about_me_heading' => $this->translate('About Me / Personal Bio'),
						'account_security_heading' => $this->translate('Account Security'),

						'username_label'           => $this->translate('Username (cannot be changed)'),
						'email_label'              => $this->translate('Email Address'),
						'first_name_label'         => $this->translate('First Name'),
						'last_name_label'          => $this->translate('Last Name'),
						'display_name_label'       => $this->translate('Display Name (public)'),
						'url_label'                => $this->translate('Website URL'),
						'aim_label'                => $this->translate('AOL® (AIM) Username'),
						'yim_label'                => $this->translate('Yahoo® Messenger ID'),
						'jabber_label'             => $this->translate('Jabber™ (or Google® Talk) Username'),
						'about_me_label'           => $this->translate('About Me (Biographical Info)'),
						'password_label'           => $this->translate('New Password? (if so, type it twice to confirm please)'),
						'submit_label'             => $this->translate('Update Profile'),

						'require_first_name'       => '0',
						'require_last_name'        => '0',
						'require_display_name'     => '0',
						'require_url'              => '0',
						'require_aim'              => '0',
						'require_yim'              => '0',
						'require_jabber'           => '0',
						'require_description'      => '0'
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
						'display_account_basics', 'display_online_details', 'display_profile_fields', 'display_details_about_me', 'display_account_security',
						'display_username', 'display_email', 'display_first_name', 'display_last_name', 'display_display_name',
						'display_url', 'display_aim', 'display_yim', 'display_jabber',
						'display_description',
						'display_password',

						'require_first_name', 'require_last_name', 'require_display_name',
						'require_url', 'require_aim', 'require_yim', 'require_jabber',
						'require_description'
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

					extract((array)$this->©action->get_call_data_for('©users.®profile_update'),
					        EXTR_PREFIX_SAME, 'xps'); // Prefix collisions.

					if(!$this->©user->is_logged_in())
						return ''; // No need to display this.

					$attr    = $this->normalize_attr_strings($attr);
					$content = do_shortcode($this->©string->trim_content((string)$content));

					$optional_requirements = array();

					foreach($attr as $_attr_key => $_attr_value)
						if(is_string($_attr_key) && strpos($_attr_key, 'require_') === 0 && $_attr_value)
							$optional_requirements[] = $this->©string->replace_once('require_', '', $_attr_key);
					unset($_attr_key, $_attr_value); // Housekeeping.

					$optional_requirements = $this->©encryption->encrypt(serialize($optional_requirements));

					return $this->©template('shortcodes/profile-update.php', get_defined_vars(), $attr['theme'])->content;
				}
		}
	}