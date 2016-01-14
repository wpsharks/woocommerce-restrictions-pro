<?php
/**
 * The `[s2_profile_summary]` Shortcode.
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
		 * The `[s2_profile_summary]` Shortcode.
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
		class profile_summary extends \websharks_core_v000000_dev\shortcodes\shortcode
		{
			/**
			 * Gets default shortcode attributes.
			 *
			 * @return array Default shortcode attributes.
			 */
			public function attr_defaults()
				{
					return array(
						'theme'                  => '',
						'width'                  => '100%',
						'margin'                 => '0',
						'padding'                => '10px',
						'font_size'              => '100%',
						'font_family'            => 'inherit',
						'widgetize'              => '1',

						'display_name_heading'   => '1',
						'display_avatar'         => '1',
						'display_links'          => '1',

						'avatar_size'            => '64',
						'logout_redirect_to_url' => ''
						// One of: `%%previous%%` or `%%home%%`.
						// Or, a specific URL that is typed in manually (MUST be an on-site URL).
						// The default behavior does NOT redirect. We land on a stand-alone login page w/ a logged-out message.
						// However, we ALWAYS look for a `redirect_to` request arg (which takes precedence over anything else).
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
						'display_name_heading',
						'display_avatar',
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

					if(!$this->©user->is_logged_in())
						return ''; // No need to display this.

					$attr                           = $this->normalize_attr_strings($attr);
					$attr['logout_redirect_to_url'] = $this->©systematic->logout_redirect($attr['logout_redirect_to_url']);
					$content                        = do_shortcode($this->©string->trim_content((string)$content));

					return $this->©template('shortcodes/profile-summary.php', get_defined_vars(), $attr['theme'])->content;
				}
		}
	}