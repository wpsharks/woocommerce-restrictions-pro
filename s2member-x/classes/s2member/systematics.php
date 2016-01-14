<?php
/**
 * Systematics.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Systematics
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Systematics.
		 *
		 * @package s2Member\Systematics
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class systematics extends framework
		{
			/**
			 * Gets all post IDs leading to systematics.
			 *
			 * @return array Array of all systematic post IDs.
			 */
			public function post_ids()
				{
					$post_ids = array(
						'register' => (integer)$this->©options->get('systematics.register_post_id'),
						'login'    => (integer)$this->©options->get('systematics.login_post_id'),
						'account'  => (integer)$this->©options->get('systematics.account_post_id')
					);
					return $this->apply_filters('post_ids', $post_ids, get_defined_vars());
				}

			/**
			 * Gets post IDs leading to public systematics.
			 *
			 * @return array Array of public systematic post IDs.
			 */
			public function post_ids_public()
				{
					$post_ids        = $this->post_ids();
					$post_ids_public = $post_ids;
					unset($post_ids_public['account']);

					return $this->apply_filters('post_ids_public', $post_ids_public, get_defined_vars());
				}

			/**
			 * Gets post IDs leading to private systematics.
			 *
			 * @return array Array of private systematic post IDs.
			 */
			public function post_ids_private()
				{
					$post_ids         = $this->post_ids();
					$post_ids_private = array($post_ids['account']);

					return $this->apply_filters('post_ids_private', $post_ids_private, get_defined_vars());
				}

			/**
			 * Gets all URLs leading to systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of all systematic URLs.
			 *
			 * @throws exception If unable to parse a host/URI into a regex frag.
			 */
			public function urls($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					cache_checks: // Target point for cache checks.

					$db_cache_key = $this->method(__FUNCTION__);

					if($regex_patterns && is_array($urls = $this->©db_cache->get($db_cache_key.'regex_patterns')))
						goto finale; // Jump to finale (we've got regex patterns already).

					if(is_array($urls = $this->©db_cache->get($db_cache_key)))
						if($regex_patterns) // Do we need regex patterns?
							goto regex_patterns; // Need patterns.
						else goto finale; // Jump to finale.

					urls: // Target point for URLs.

					$urls = array(); // Initialize array.

					foreach($this->post_ids() as $_key => $_post_id)
						{
							if($_post_id && ($_permalink = $this->©url->to_wp_permalink_id($_post_id, 'http')))
								$urls[$_key] = $_permalink;
							else if($_key === 'register')
								$urls[$_key] = $this->©url->to_wp_register('', 'http');
							else if($_key === 'login')
								$urls[$_key] = $this->©url->to_wp_login('', 'http');
							else if($_key === 'account')
								$urls[$_key] = $this->©url->to_wp_permalink_uri('/account/', 'http');
							else $urls[$_key] = $this->©url->to_wp_home_uri('', 'http');
						}
					unset($_key, $_post_id, $_permalink); // Housekeeping.

					$urls['wp_network_home_index'] = $this->©url->to_wp_network_home_uri('/index.php', 'http');
					$urls['wp_network_home']       = $this->©url->to_wp_network_home_uri('', 'http');
					$urls['wp_home_index']         = $this->©url->to_wp_home_uri('/index.php', 'http');
					$urls['wp_home']               = $this->©url->to_wp_home_uri('', 'http');

					$urls['wp_network_site_index'] = $this->©url->to_wp_network_site_uri('/index.php', 'http');
					$urls['wp_network_site']       = $this->©url->to_wp_network_site_uri('', 'http');
					$urls['wp_site_index']         = $this->©url->to_wp_site_uri('/index.php', 'http');
					$urls['wp_site']               = $this->©url->to_wp_site_uri('', 'http');

					$urls['wp_login']         = $this->©url->to_wp_login('', 'http');
					$urls['wp_lost_password'] = $this->©url->to_wp_lost_password('', 'http');
					$urls['wp_register']      = $this->©url->to_wp_register('', 'http');
					$urls['wp_signup']        = $this->©url->to_wp_signup('http');
					$urls['wp_activate']      = $this->©url->to_wp_activate('http');
					$urls['wp_app']           = $this->©url->to_wp_app('http');
					$urls['wp_cron']          = $this->©url->to_wp_cron('http');
					$urls['wp_links_opml']    = $this->©url->to_wp_links_opml('http');
					$urls['wp_mail']          = $this->©url->to_wp_mail('http');
					$urls['wp_trackback']     = $this->©url->to_wp_trackback('http');
					$urls['wp_xmlrpc']        = $this->©url->to_wp_xmlrpc('http');

					if($this->©env->is_bp_installed())
						{
							$urls['bp_register'] = $this->©url->to_bp_register('http');
							$urls['bp_activate'] = $this->©url->to_bp_activate('http');
						}
					$this->©db_cache->update($db_cache_key, $urls);

					regex_patterns: // Target point for regex patterns.

					if(!$regex_patterns) goto finale; // Skip to finale in this scenario.

					$_exact_match_keys   = array('wp_network_home_index', 'wp_network_home', 'wp_home_index', 'wp_home',
					                             'wp_network_site_index', 'wp_network_site', 'wp_site_index', 'wp_site');
					$_o_exact_match_urls = $this->©array->compile_key_elements($urls, $_exact_match_keys, TRUE);

					foreach($urls as $_key => &$_url) // Build regex patterns.
						{
							$_o_url = $_url; // Original URL (before conversion to regex pattern).
							$_url   = preg_replace('/^'.$this->©url->regex_frag_scheme.'/', '', $_url);
							$_url   = preg_replace_callback('/^(?P<host>'.$this->©url->regex_frag_host.')(?P<uri>.*)/', function ($m)
								{
									return '(?i:'.preg_quote($m['host'], '/').')'.preg_quote($m['uri'], '/');
								}, $_url, -1, $_replacements);

							if($_replacements !== count($urls))
								throw $this->©exception(
									$this->method(__FUNCTION__).'#regex_failure', get_defined_vars(),
									$this->i18n('Failed to parse at least one URL host/URI into a regex frag.')
								);
							$_url = '/^'.$this->©url->regex_frag_scheme.$_url;

							if(substr($_url, -1) === '/')
								$_url .= '?'; // Optional trailing slash.
							else $_url = preg_replace('/(\\\\\/)(\\\\?|#)/', '${1}?'.'${2}', $_url);

							if(in_array($_key, $_exact_match_keys, TRUE) || in_array($_o_url, $_o_exact_match_urls, TRUE))
								$_url .= '$/'; // Require exact matches in these cases.
							else $_url .= '(?:[?&#]|$)/'; // Else we DO consider query/fragment.
						}
					unset($_exact_match_keys, $_o_exact_match_urls, $_key, $_url, $_replacements); // Housekeeping.

					$this->©db_cache->update($db_cache_key.'regex_patterns', $urls);

					finale: // Target point for grand finale.

					if(!$regex_patterns) // Set scheme to match the current scheme.
						// This also deals with ``force_ssl_login()``.
						{
							$force_ssl_login_keys = array(
								'login',
								'wp_login',
								'register',
								'wp_register',
								'wp_signup',
								'bp_register',
								'wp_lost_password'
							);
							$force_ssl_login      = (force_ssl_login() || force_ssl_admin());
							// TODO Update handling of force_ssl_login which only affects the actual POST process.

							foreach($urls as $_key => &$_url)
								{
									if($force_ssl_login && in_array($_key, $force_ssl_login_keys, TRUE))
										$_url = $this->©url->set_scheme($_url, 'https');
									else $_url = $this->©url->set_scheme($_url);
								}
							unset($_key, $_url); // Housekeeping.
						}
					return $this->apply_filters('urls', $urls, get_defined_vars());
				}

			/**
			 * Gets a specific systematic URL, by key name.
			 *
			 * @param string $key Systematic key (i.e. `register`, `login`, `account`, etc.).
			 *
			 * @param string $redirect_to Optional. Defaults to an empty string.
			 *    If this is provided, a `redirect_to` argument will be added to the URL.
			 *
			 * @return string A systematic URL for the ``$key``, else an exception is thrown.
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$key`` is empty.
			 */
			public function url($key, $redirect_to = '')
				{
					$this->check_arg_types('string:!empty', 'string', func_get_args());

					if(($urls = $this->urls()) && !empty($urls[$key]))
						{
							if($redirect_to) // Add `redirect_to` argument value.
								return add_query_arg(urlencode_deep(compact('redirect_to')), $urls[$key]);
							return $urls[$key];
						}
					throw $this->©exception(
						$this->method(__FUNCTION__).'#missing_systematic_url', get_defined_vars(),
						sprintf($this->i18n('Missing systematic URL for key: `%1$s`.'), $key)
					);
				}

			/**
			 * Gets URLs leading to public systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of public systematic URLs.
			 */
			public function urls_public($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					$urls        = $this->urls($regex_patterns);
					$urls_public = $urls; // Only ONE of these is private.
					unset($urls_public['account']);

					return $this->apply_filters('urls_public', $urls_public, get_defined_vars());
				}

			/**
			 * Gets URLs leading to private systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of private systematic URLs.
			 */
			public function urls_private($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					$urls         = $this->urls($regex_patterns);
					$urls_private = array($urls['account']);

					return $this->apply_filters('urls_private', $urls_private, get_defined_vars());
				}

			/**
			 * Gets all URIs leading to systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of all systematic URIs.
			 *
			 * @throws exception If unable to parse a URI (e.g. we encounter an invalid URL).
			 */
			public function uris($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					cache_checks: // Target point for cache checks.

					$db_cache_key = $this->method(__FUNCTION__);

					if($regex_patterns && is_array($uris = $this->©db_cache->get($db_cache_key.'regex_patterns')))
						goto finale; // Jump to finale (we've got regex patterns already).

					if(is_array($uris = $this->©db_cache->get($db_cache_key)))
						if($regex_patterns) // Do we need regex patterns?
							goto regex_patterns; // Need patterns.
						else goto finale; // Jump to finale.

					uris: // Target point for URIs.

					$uris = array(); // Initialize array.

					foreach($this->urls() as $_key => $_url)
						$uris[$_key] = $this->©url->must_parse_uri($_url);
					unset($_key, $_url); // Housekeeping.

					$this->©db_cache->update($db_cache_key, $uris);

					regex_patterns: // Target point for regex patterns.

					if(!$regex_patterns) goto finale; // Skip to finale in this scenario.

					$_exact_match_keys   = array('wp_network_home_index', 'wp_network_home', 'wp_home_index', 'wp_home',
					                             'wp_network_site_index', 'wp_network_site', 'wp_site_index', 'wp_site');
					$_o_exact_match_uris = $this->©array->compile_key_elements($uris, $_exact_match_keys, TRUE);

					foreach($uris as $_key => &$_uri) // Build regex patterns.
						{
							$_o_uri = $_uri; // Original URI (before conversion to regex pattern).
							$_uri   = '/^'.$_uri; // Opening delimiter/line for this pattern.

							if(substr($_uri, -1) === '/')
								$_uri .= '?'; // Optional trailing slash.
							else $_uri = preg_replace('/(\\\\\/)(\\\\?|#)/', '${1}?'.'${2}', $_uri);

							if(in_array($_key, $_exact_match_keys, TRUE) || in_array($_o_uri, $_o_exact_match_uris, TRUE))
								$_uri .= '$/'; // Require exact matches in these cases.
							else $_uri .= '(?:[?#]|$)/'; // Else we DO consider query/fragment.
						}
					unset($_exact_match_keys, $_o_exact_match_uris, $_key, $_uri, $_o_uri); // Housekeeping.

					$this->©db_cache->update($db_cache_key.'regex_patterns', $uris);

					finale: // Target point for grand finale.

					return $this->apply_filters('uris', $uris, get_defined_vars());
				}

			/**
			 * Gets a specific systematic URI, by key name.
			 *
			 * @param string $key Systematic key name (i.e. `register`, `login`, `account`, etc.).
			 *
			 * @param string $redirect_to Optional. Defaults to an empty string.
			 *    If this is provided, a `redirect_to` argument will be added to the URI.
			 *
			 * @return string A systematic URI for the ``$key``, else an exception is thrown.
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$key`` is empty.
			 */
			public function uri($key, $redirect_to = '')
				{
					$this->check_arg_types('string:!empty', 'string', func_get_args());

					if(($uris = $this->uris()) && !empty($uris[$key]))
						{
							if($redirect_to) // Add `redirect_to` argument value?
								return add_query_arg(urlencode_deep(compact('redirect_to')), $uris[$key]);
							return $uris[$key];
						}
					throw $this->©exception(
						$this->method(__FUNCTION__).'#missing_systematic_uri', get_defined_vars(),
						sprintf($this->i18n('Missing systematic URI for key: `%1$s`.'), $key)
					);
				}

			/**
			 * Gets URIs leading to public systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of public systematic URIs.
			 */
			public function uris_public($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					$uris        = $this->uris($regex_patterns);
					$uris_public = $uris; // Only ONE of these is private.
					unset($uris_public['account']);

					return $this->apply_filters('uris_public', $uris_public, get_defined_vars());
				}

			/**
			 * Gets URIs leading to private systematics.
			 *
			 * @param boolean $regex_patterns Optional. Defaults to a FALSE value.
			 *    If TRUE, the array is filled with regex patterns.
			 *
			 * @return array Array of private systematic URIs.
			 */
			public function uris_private($regex_patterns = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					$uris         = $this->uris($regex_patterns);
					$uris_private = array($uris['account']);

					return $this->apply_filters('uris_private', $uris_private, get_defined_vars());
				}

			/**
			 * Resets other login redirect filters.
			 *
			 * @attaches-to WordPress® action `wp_login`.
			 * @hook-priority Default is fine.
			 */
			public function setup_login_redirect_filters()
				{
					remove_all_filters('login_redirect');
					add_filter('login_redirect', array($this, 'login_redirect'), 10, 3);
				}

			/**
			 * Determines proper login redirection URL (based on several factors).
			 *
			 * @attaches-to WordPress® filter `login_redirect`.
			 * @filter-priority Default is fine.
			 *
			 * @note This function can also be called upon directly.
			 *    For instance, this is used by the `[s2_login]` shortcode.
			 *
			 * @param string                  $redirect_to Optional. Existing redirection URL (if there is one).
			 *    This is passed in by the WordPress® `login_redirect` filter.
			 *
			 * @param string                  $redirect_to_flag Optional. A redirection URL specified by the ``$_REQUEST``?
			 *    This is passed in by the WordPress® `login_redirect` filter (and we ignore it in that case, in favor of our own detection here).
			 *    Or, this can also be set to ``fw_constants::direct_call`` to indicate a direct call (as opposed to a filter being processed).
			 *    This parameter defaults to a value of ``fw_constants::direct_call``.
			 *
			 * @param null|\WP_Error|\WP_User $wp_user Optional. A WordPress® user object instance.
			 *    In some cases this might also be a WordPress® error object instance.
			 *    This is passed in by the WordPress® `login_redirect` filter.
			 *
			 * @return string Final redirection URL (as determined by this routine; normally an empty string for direct calls).
			 *    For most direct calls, this will simply return an empty string; allowing final filter processing to handle redirection.
			 */
			public function login_redirect($redirect_to = '', $redirect_to_flag = self::direct_call, $wp_user = NULL)
				{
					$this->check_arg_types('string', 'string', array('null', '\\WP_Error', '\\WP_User'), func_get_args());

					$is_filter      = ($redirect_to_flag !== $this::direct_call) ? TRUE : FALSE;
					$is_direct_call = ($redirect_to_flag === $this::direct_call) ? TRUE : FALSE;

					if(($_r_redirect_to = (string)$this->©vars->_REQUEST('redirect_to')))
						$redirect_to = $_r_redirect_to;

					else if($redirect_to === '%%previous%%' && is_array($r2a = $this->©vars->_REQUEST($this->___instance_config->plugin_var_ns.'_r2a'))
					        && $this->©string->is_not_empty($r2a['uri']) // Previous access URI denied by restrictions?
					) $redirect_to = $this->©url->to_abs_uri($r2a['uri']); // Special case handler.

					else if($redirect_to === '%%previous%%')
						$redirect_to = $this->©url->current();

					else if($redirect_to === '%%home%%')
						$redirect_to = $this->©url->to_wp_home_uri();

					if(!$redirect_to || rtrim($redirect_to, '/') === 'wp-admin' || preg_match('/\/wp-admin[\/?#]*$/', $redirect_to))
						$redirect_to = ''; // A default ``$redirect_to`` value.

					$is_redirecting                = ($redirect_to) ? TRUE : FALSE;
					$redirect_to_uri               = ($is_redirecting) ? (string)$this->©url->parse_uri($redirect_to) : '';
					$is_redirecting_to_current_uri = ($is_redirecting && $redirect_to_uri === $this->©url->current_uri());

					if($is_redirecting && $this->©string->in_regex_patterns($redirect_to_uri, $this->uris(TRUE)))
						$redirect_to = $this->url('account'); // The only systematic we allow.

					else if($is_redirecting_to_current_uri && $wp_user instanceof \WP_User && $this->©passtag_restrictions->check($wp_user->ID))
						$redirect_to = $this->url('account'); // Because user will NOT have access (even after logging in).

					else if($is_redirecting && !$is_redirecting_to_current_uri && $wp_user instanceof \WP_User && $this->©passtag_restrictions->check_uri($redirect_to_uri, $wp_user->ID))
						$redirect_to = $this->url('account'); // Because user will NOT have access (even after logging in).

					if($is_filter && !$redirect_to) // Default case handler (for filters).
						{
							if($wp_user instanceof \WP_User && (is_super_admin($wp_user->ID) || $wp_user->has_cap('administrator')))
								$redirect_to = $this->©url->to_wp_admin_uri(); // WordPress® Dashboard in this case.

							else // Defaults to the `account` page.
								$redirect_to = $this->url('account');
						}
					return $this->apply_filters('login_redirect', $redirect_to, get_defined_vars());
				}

			/**
			 * Determines proper logout redirection URL (based on several factors).
			 *
			 * @param string $redirect_to Optional. Existing redirection URL (if there is one).
			 *
			 * @return string Final redirection URL (as determined by this routine; normally an empty string).
			 *    This will normally return an empty string; allowing logouts to land back on the login page w/ a logged-out message.
			 */
			public function logout_redirect($redirect_to = '')
				{
					$this->check_arg_types('string', func_get_args());

					if(($_r_redirect_to = (string)$this->©vars->_REQUEST('redirect_to')))
						$redirect_to = $_r_redirect_to;

					if($redirect_to === '%%previous%%')
						$redirect_to = $this->©url->current();

					else if($redirect_to === '%%home%%')
						// This WILL be caught by the systematic routine below.
						// However, since it still defaults to the home page, that's fine.
						$redirect_to = $this->©url->to_wp_home_uri(); // The only systematic we allow.

					$is_redirecting                = ($redirect_to) ? TRUE : FALSE;
					$redirect_to_uri               = ($is_redirecting) ? (string)$this->©url->parse_uri($redirect_to) : '';
					$is_redirecting_to_current_uri = ($is_redirecting && $redirect_to_uri === $this->©url->current_uri());

					if($is_redirecting && $this->©string->in_regex_patterns($redirect_to_uri, $this->uris(TRUE)))
						$redirect_to = $this->©url->to_wp_home_uri(); // The only systematic we allow.

					else if($is_redirecting_to_current_uri && $this->©passtag_restrictions->check(-1))
						$redirect_to = $this->©url->to_wp_home_uri(); // Because user would NOT have access after logging out.

					else if($is_redirecting && !$is_redirecting_to_current_uri && $this->©passtag_restrictions->check_uri($redirect_to_uri, -1))
						$redirect_to = $this->©url->to_wp_home_uri(); // Because user would NOT have access after logging out.

					return $this->apply_filters('logout_redirect', $redirect_to, get_defined_vars());
				}
		}
	}