<?php
/**
 * AWeber®.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\ESPs
 * @since 120318
 */
namespace s2member\esps
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * AWeber®.
		 *
		 * @package s2Member\ESPs
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class aweber extends \s2member\esps\esp
		{
			/**
			 * @var string Our AWeber® application ID.
			 * @aweber Provided by AWeber® (this identifies s2Member®).
			 */
			public $app_id = '224027c2';

			/**
			 * @var string Integrated API version.
			 */
			public $api_version = '1.0';

			/**
			 * @var string AWeber® API endpoint URL.
			 */
			public $api_url = 'https://api.aweber.com/1.0';

			/**
			 * @var string Site owners will need to visit this URL,
			 *    to obtain their own API authorization code; allowing s2Member® access.
			 */
			public $authorization_url = 'https://auth.aweber.com/1.0/oauth/authorize_app/224027c2';

			/**
			 * @var string API endpoint URL; where we can request a new access token.
			 */
			public $access_token_url = 'https://auth.aweber.com/1.0/oauth/access_token';

			/**
			 * @var array OAuth signable var types.
			 * @note AWeber® only includes GET vars in the signature base string.
			 */
			public $signable_var_types = array('GET');

			/**
			 * @var array Permissible errors codes; by API collection type and `ws.op`.
			 * @note This enhances error handling, in a few predictable scenarios.
			 * @see https://labs.aweber.com/docs/troubleshooting
			 */
			public $permissible_error_codes = array(
				'subscribers' => array(
					'create' => array(400 => '/already (?:subscribed|exists|belongs)/i'),
					'move'   => array(405 => '/already (?:subscribed|exists|belongs)/i')
				));

			/**
			 * @var \websharks_core_v000000_dev\oauth_v1 An OAuth instance.
			 * @by-constructor Set dynamically by class constructor (if possible).
			 */
			public $oauth; // Defaults to a NULL value.

			/**
			 * Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					$meta        = $this->©string->ify_deep($this->get_meta_values(array('api_authorization_code', 'api_access_code')));
					$this->oauth = $this->©oauth_v1($this->access_token_url, $meta['api_authorization_code'], $meta['api_access_code'], $this->signable_var_types);

					if($this->oauth->has_new_access_code())
						$this->update_meta_value('api_access_code', $this->oauth->new_access_code);
				}

			/**
			 * Checks if AWeber® has been implemented by the site owner.
			 *
			 * @return boolean TRUE if AWeber® has been implemented by the site owner; else FALSE.
			 */
			public function is_implemented()
				{
					return ($this->oauth->has_authorization_access_codes()) ? TRUE : FALSE;
				}

			/**
			 * Calls upon the AWeber® API (gets response).
			 *
			 * @param string $method The request method (i.e. `GET`, `POST`, etc).
			 * @param string $url The request URL (the endpoint URL for this API request).
			 * @param array  $vars Array of all request vars.
			 *
			 * @return array|boolean|\websharks_core_v000000_dev\errors This returns an array of data the AWeber® API provides in its response.
			 *    Or, this may return boolean TRUE, if there were no errors, and there was no response data.
			 *    Else, this will return an `errors` object if the API call fails, for any reason.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$method`` or ``$url`` are empty.
			 *
			 * @see https://labs.aweber.com/docs
			 */
			public function api_response($method, $url, $vars = array())
				{
					$this->check_arg_types('string:!empty', 'string:!empty', 'array', func_get_args());

					if(!$this->is_implemented())
						return $this->©error(
							$this->method(__FUNCTION__), get_defined_vars(),
							sprintf($this->i18n('AWeber® API call: `%1$s » %2$s`).'), $method, $url).
							' '.$this->i18n('AWeber® NOT yet implemented by site owner.')
						);
					$method  = strtoupper($method);
					$headers = array();
					$body    = NULL;

					$collection_type = $this->collection_type($url);
					$collection_type = $this->©string->is_not_empty_or($collection_type, 'n/a');
					$ws_op           = (!empty($vars['ws.op']) && is_string($vars['ws.op'])) ? $vars['ws.op'] : 'n/a';
					$vars            = $this->apply_filters($method.'__'.$collection_type.'__'.$ws_op.'__vars', $vars, get_defined_vars());

					if($method === 'GET' && $vars) // Query string.
						$url = add_query_arg(rawurlencode_deep($vars), $url);

					else if($method === 'POST' && $vars) // POST vars.
						{
							$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
							$body                    = $this->©vars->build_raw_query($vars);
						}
					else if(in_array($method, array('PATCH', 'PUT'), TRUE) && $vars)
						{
							$headers['Content-Type'] = 'application/json';
							$body                    = json_encode($vars);
						}
					$oauth_signature          = $this->oauth->sign($method, $url, $vars);
					$headers['Authorization'] = $oauth_signature['header'];

					$response = $this->©url->remote($url, NULL, // Do remote API call now.
					                                array('timeout'      => 20, 'redirection' => 0,
					                                      'return_array' => TRUE, 'return_errors' => TRUE,
					                                      'method'       => $method, 'headers' => $headers, 'body' => $body)
					);
					if(!is_array($response)) // Connection failure?
						return $this->©error($this->method(__FUNCTION__), get_defined_vars(),
						                     sprintf($this->i18n('AWeber® API call: `%1$s » %2$s`).'), $method, $url).
						                     ' '.$this->i18n('Connection failure.')
						);
					$aweber = array(); // Initialize AWeber® response array.

					if($response['body']) // Detect response body type.
						// There does NOT seem to be any way of forcing AWeber® to a particular output type.
						// The docs indicate it's always JSON, but there's no way to enforce JSON; so we'll leave this here.
						{
							if(!is_array($aweber = json_decode($response['body'], TRUE)))
								if(!is_array($aweber = maybe_unserialize($response['body'])))
									$aweber = $this->©vars->parse_query($response['body']);
							$aweber = $this->©string->ify_deep($aweber);
						}
					if(isset($aweber['error']['status'])
					   && $this->©string->is_not_empty($aweber['error']['status'])
					) $error_code = $aweber['error']['status'];

					else if($response['code'] >= 400) $error_code = (string)$response['code'];

					if(!empty($error_code)) // Handle errors (get error message).
						{
							if(isset($aweber['error']['message']) && $this->©string->is_not_empty($aweber['error']['message']))
								$error_message = $aweber['error']['message'];

							else if($response['message']) // HTTP message?
								$error_message = $response['message'];

							else $error_message = $this->i18n('Check error code.');

							if($this->©array->is_not_empty($this->permissible_error_codes[$collection_type][$ws_op])
							   && in_array((int)$error_code, array_keys($this->permissible_error_codes[$collection_type][$ws_op]), TRUE)
							   && $this->©string->in_regex_patterns($error_message, $this->permissible_error_codes[$collection_type][$ws_op][$error_code])
							) $error_code = $error_message = NULL; // Nullify.

							if($error_code) // Error is NOT permissible.
								return $this->©error($this->method(__FUNCTION__), get_defined_vars(),
								                     sprintf($this->i18n('AWeber® API call: `%1$s » %2$s`).'), $method, $url).
								                     ' '.sprintf($this->i18n('Error code: `%1$s`.'), $error_code).
								                     ' '.sprintf($this->i18n('Message: `%1$s`.'), $error_message)
								);
						}
					$this->©success($this->method(__FUNCTION__), get_defined_vars(),
					                sprintf($this->i18n('AWeber® API call: `%1$s » %2$s`).'), $method, $url).
					                ' '.$this->i18n('Status: `success`.')
					);
					if(empty($aweber)) return TRUE; // Assume success.

					return $aweber; // API response data.
				}

			/**
			 * Gets the AWeber® account ID associated with this API integration.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#accounts
			 *
			 * @return string The AWeber® account ID associated with this API integration.
			 *    Else, an empty string on failure.
			 */
			public function account_id()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_string($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$api_response = $this->api_response('GET', $this->api_url.'/accounts');

					if($this->©errors->exist_in($api_response) || !is_array($api_response)
					   || !$this->©string->is_not_empty($api_response['entries'][0]['id'])
					) return ''; // Default value (empty string).

					return $this->©db_cache->update($db_cache_key, $api_response['entries'][0]['id']);
				}

			/**
			 * Gets the segments associated with this AWeber® account ID.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#lists
			 *
			 * @return array The segments associated with this AWeber® account ID.
			 *    Else, an empty array on failure.
			 */
			public function segments()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$api_response = $this->api_response('GET', $this->api_url.'/accounts/'.$this->account_id().'/lists');

					if($this->©errors->exist_in($api_response) || !is_array($api_response)
					   || !$this->©array->is_not_empty($api_response['entries'])
					) return array(); // Defaults to an empty array.

					$segments = array(); // Initialize.

					foreach($api_response['entries'] as $_entry)
						$segments[$_entry['name']] = $_entry['id'];
					unset($_entry); // Housekeeping.

					return $this->©db_cache->update($db_cache_key, $segments);
				}

			/**
			 * Gets a segment ID, for a particular segment (by name).
			 *
			 * @param string $segment_name AWeber® segment name.
			 *
			 * @return string A segment ID, else an empty string on failure.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment_name`` is empty.
			 */
			public function segment_id($segment_name)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					$segments = $this->segments();

					if($this->©string->is_not_empty($segments[$segment_name]))
						return $segments[$segment_name];

					return ''; // Empty string default value.
				}

			/**
			 * Gets a segment name, for a particular segment (by ID).
			 *
			 * @param string $segment_id AWeber® segment ID.
			 *
			 * @return string A segment name, else an empty string on failure.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment_id`` is empty.
			 */
			public function segment_name($segment_id)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					$segments = $this->segments();

					if(($segment_name = array_search($segment_id, $segments, TRUE)))
						return $segment_name;

					return ''; // Empty string default value.
				}

			/**
			 * Parses the collection type from an API request URL.
			 *
			 * @param string $url An AWeber® API request URL (i.e. an endpoint URL).
			 *
			 * @return string The collection type; else an empty string on detection failure.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function collection_type($url)
				{
					$this->check_arg_types('string', func_get_args());

					foreach(array_reverse(explode('/', (string)$this->©url->parse($url, PHP_URL_PATH))) as $dir)
						if($dir && !is_numeric($dir[0]))
							return $dir;

					return ''; // Default return value.
				}

			/**
			 * Subscribes an email address.
			 *
			 * @param string|array                          $segment AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param boolean                               $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the subscribe action should NOT send a welcome email, or ask the user to confirm.
			 *    IMPORTANT: AWeber® does NOT support this feature. So a TRUE value here, is simply ignored.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#subscribers
			 *
			 * @return boolean TRUE if the address was subscribed, else FALSE.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function subscribe($segment, $user = NULL, $silently = FALSE, $other_vars = array())
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), $this->©user_utils->which_types(), 'boolean', 'array', func_get_args());

					$segment = $this->parse_segment($segment);
					$user    = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					$ad_tracking = $this->©url->current_host();
					$ad_tracking = $this->apply_filters('ad_tracking', $ad_tracking, get_defined_vars());
					$merge_vars  = $this->map_merge_vars($segment, $user);

					$vars = array_merge(
						array(
							'ws.op'         => 'create',
							'ip_address'    => $user->ip,
							'email'         => $user->email,
							'name'          => (string)substr($user->full_name, 0, 50),
							'ad_tracking'   => (string)substr($ad_tracking, 0, 20),
							'custom_fields' => $merge_vars
						), $other_vars
					);
					if(empty($vars['custom_fields'])) // Remove if empty.
						unset($vars['custom_fields']); // AWeber® chokes on empty custom fields.

					$api_response = $this->api_response(
						'POST', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$segment['id'].'/subscribers', $vars
					);
					if($this->©errors->exist_in($api_response))
						return FALSE;

					return TRUE;
				}

			/**
			 * Updates existing subscriber data.
			 *
			 * @param null|string|array                     $segment AWeber® segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an update for ALL subscribed segments.
			 *    In other words, a NULL value indicates that an update should occur for each of the user's currently subscribed segments.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param string                                $prev_email Optional. Defaults to an empty string.
			 *    If the email address is changing, please pass this in.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#subscribers
			 *
			 * @return integer The number of updates that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function update($segment = NULL, $user = NULL, $prev_email = '', $other_vars = array())
				{
					$this->check_arg_types(array('null', 'string:!empty', 'array:!empty'), $this->©user_utils->which_types(), 'string', 'array', func_get_args());

					$updates = 0; // Initialize.
					$user    = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					if(is_null($segment)) // All subscribed segments?
						{
							if($prev_email) // The email address is changing too (we assume it is).
								{
									if(!($segments = $this->user_subscribed_segments($user, array('email' => $prev_email))))
										return 0; // There are none (stop here).
								}
							else if(!($segments = $this->user_subscribed_segments($user)))
								return 0; // There are none (nothing more we can do).
						}
					else $segments = array($segment); // A specific segment in this case.

					foreach($segments as $_segment) // Update each of these ``$segments`` now.
						{
							$_segment = $this->parse_segment($_segment); // Parse individually.

							if($prev_email) // The email address is changing too (we'll assume that it is in this case).
								{
									if(!($_info = $this->user_segment_info($_segment, $user, array('email' => $prev_email))) || $_info['status'] !== 'subscribed')
										return FALSE; // They do NOT exist; or they're NOT `subscribed` under this previous email address.
								}
							else if(!($_info = $this->user_segment_info($_segment, $user)) || $_info['status'] !== 'subscribed')
								return FALSE; // They do NOT exist; or they are NOT in a `subscribed` state.

							$_merge_vars = $this->map_merge_vars($_segment, $user);

							$_vars = array_merge(
								array(
									'email'         => $user->email,
									'name'          => (string)substr($user->full_name, 0, 50),
									'custom_fields' => $_merge_vars
								), $other_vars
							);
							if(empty($_vars['custom_fields'])) // Remove if empty.
								unset($_vars['custom_fields']); // AWeber® chokes on empty custom fields.

							$_api_response = $this->api_response(
								'PATCH', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$_segment['id'].'/subscribers/'.$_info['id'], $_vars
							);
							if(!$this->©errors->exist_in($_api_response))
								$updates++; // Updated successfully.
						}
					unset($_segment, $_info, $_merge_vars, $_vars, $_api_response);

					return $updates; // Total updates.
				}

			/**
			 * Unsubscribes an email address.
			 *
			 * @param null|string|array                     $segment AWeber® segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an unsubscribe from ALL subscribed segments.
			 *    In other words, a NULL value indicates that an unsubscribe should occur for each of the user's currently subscribed segments.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param boolean                               $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the unsubscribe action should NOT send a goodbye email, or ask the user to confirm.
			 *    IMPORTANT: AWeber® does NOT support this feature. So a TRUE value here, is simply ignored.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#subscribers
			 *
			 * @return integer The number of unsubscribes that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function unsubscribe($segment = NULL, $user = NULL, $silently = FALSE, $other_vars = array())
				{
					$this->check_arg_types(array('null', 'string:!empty', 'array:!empty'),
					                       $this->©user_utils->which_types(), 'boolean', 'array', func_get_args());

					$unsubscribes = 0; // Initialize.
					$user         = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					if(is_null($segment)) // All subscribed segments?
						{
							if(!($segments = $this->user_subscribed_segments($user)))
								return 0; // There are none (nothing more we can do).
						}
					else $segments = array($segment); // A specific segment in this case.

					foreach($segments as $_segment) // Unsubscribe from each of these ``$segments`` now.
						{
							$_segment = $this->parse_segment($_segment); // Parse each individually.

							if(!($_info = $this->user_segment_info($_segment, $user)) || $_info['status'] !== 'subscribed')
								{
									$unsubscribes++; // This counts as an unsubscribe too (they do NOT exist).
									continue; // They do NOT exist, or they're NOT currently in a `subscribed` state.
								}
							$_api_response = $this->api_response(
								'DELETE', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$_segment['id'].'/subscribers/'.$_info['id'], $other_vars
							);
							if(!$this->©errors->exist_in($_api_response))
								$unsubscribes++; // Unsubscribed successfully.
						}
					unset($_segment, $_info, $_api_response); // Housekeeping.

					return $unsubscribes; // Total unsubscribes.
				}

			/**
			 * Info for an email address, on a particular segment.
			 *
			 * @param string|array                          $segment AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#subscribers
			 *
			 * @return array|NULL An array of info (NOT empty), else NULL if unavailable.
			 *    Note, AWeber® returns a data array for unconfirmed/unsubscribed members too.
			 *    So they don't necessarily NEED to be a confirmed subscriber (e.g. they just need to exist in some way).
			 *    The return array will include a `status` index, indicating the current status of the ``$user``.
			 *
			 * @note The return array from this method, like all AWeber® API response data,
			 *    will have been stringified by method ``api_response()``. So don't expect any integers/floats.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function user_segment_info($segment, $user = NULL, $other_vars = array())
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), $this->©user_utils->which_types(), 'array', func_get_args());

					$segment = $this->parse_segment($segment);
					$user    = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					$vars         = array_merge(
						array(
							'ws.op' => 'find',
							'email' => $user->email
						), $other_vars
					);
					$api_response = $this->api_response(
						'GET', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$segment['id'].'/subscribers', $vars
					);
					if($this->©errors->exist_in($api_response)
					   || !is_array($api_response) || !$this->©array->is_not_empty($api_response['entries'])
					) return NULL; // Unable to find any info at all here.

					foreach($api_response['entries'] as $_entry)
						if(strcasecmp($_entry['email'], (string)$vars['email']) === 0)
							return $_entry; // Info for ``$vars['email']``.
					unset($_entry); // Housekeeping.

					return NULL; // Failure.
				}

			/**
			 * Subscribed segments for an email address.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listsforemail.func.php
			 * @see https://labs.aweber.com/docs/code_samples/subs/find_all
			 *
			 * @return array|NULL An array of subscribed segments (NOT empty), else NULL if unavailable.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function user_subscribed_segments($user = NULL, $other_vars = array())
				{
					$this->check_arg_types($this->©user_utils->which_types(), 'array', func_get_args());

					$user = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					$vars         = array_merge(
						array(
							'ws.op' => 'findSubscribers',
							'email' => $user->email
						), $other_vars
					);
					$api_response = $this->api_response(
						'GET', $this->api_url.'/accounts/'.$this->account_id(), $vars
					);
					if($this->©errors->exist_in($api_response)
					   || !is_array($api_response) || !$this->©array->is_not_empty($api_response['entries'])
					) return NULL; // They're NOT subscribed to any segments.

					foreach($api_response['entries'] as $_entry)
						if($_entry['status'] === 'subscribed' && strcasecmp($_entry['email'], (string)$vars['email']) === 0)
							if(preg_match('/\/lists\/(?P<id>.+?)\//', $_entry['self_link'], $_m))
								if(($_segment_name = $this->segment_name($_m['id'])))
									$segments[] = $_segment_name;
					unset($_entry, $_m, $_segment_name);

					return (!empty($segments)) ? $segments : NULL;
				}

			/**
			 * Moves an email address from one segment to another.
			 *
			 * @param null|string|array                     $old_segment Old AWeber® segment specs.
			 *    NULL, a string, or an already parsed array of segment specs (e.g. to specify a specific segment).
			 *    A NULL value indicates a transition for ALL subscribed segments. In other words, a NULL value indicates that a transition to ``$new_segment``,
			 *       should occur for each of the user's currently subscribed segments.
			 *
			 * @param string|array                          $new_segment New AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param boolean|null                          $silently Defaults to a NULL value.
			 *
			 *    • NULL — Transition only (silent, but the user MUST exist on at least one ``$old_segment``).
			 *       If this is NULL, the user MUST exist on at least one ``$old_segment``, before we allow a transition.
			 *       When they exist on an ``$old_segment`` (we're simply moving them); and that always occurs silently.
			 *
			 *    • FALSE — Transition, else subscribe (subscribe is NOT handled silently).
			 *       If this is FALSE, the user does NOT need to exist before a transition occurs.
			 *       That is, if they do NOT exist on any ``$old_segment``, they'll be subscribed to the ``$new_segment`` (but NOT silently).
			 *       We say "(FALSE)NOT silent", because a confirmation email WILL be sent, if they do NOT exist on any ``$old_segment``.
			 *       We treat them as a brand new subscriber (w/ a confirmation email, if they do NOT exist on any ``$old_segment``).
			 *
			 *    • TRUE — Transition, else subscribe (subscribe is silent, please use with caution).
			 *       If this is TRUE (the same applies); the user does NOT need to exist before a transition occurs.
			 *       However, if they do NOT exist on any ``$old_segment``, they'll be subscribed to the ``$new_segment`` (silently).
			 *       We say "(TRUE)silent", because a confirmation email will NOT be sent, even if they do NOT exist on any ``$old_segment``.
			 *       We treat them as a brand new subscriber (but w/o a confirmation email, if they do NOT exist on any ``$old_segment``).
			 *       IMPORTANT: AWeber® does NOT support this feature. So a TRUE value here, is treated like a FALSE value.
			 *
			 *    • In any of these scenarios, an actual "transition" always occur silently.
			 *       In other words, when/if they DO exist on an ``$old_segment`` (we're simply moving them silently — in all cases).
			 *
			 *    • In any of these scenarios, if the user IS currently subscribed, we will NOT subscribe them to the ``$new_segment``,
			 *       unless we can successfully unsubscribe them from an ``$old_segment`` (or, if they do NOT exist at all, in the case of `FALSE|TRUE`).
			 *
			 * @param array                                 $other_vars_move Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual AWeber® API call, for the underlying unsubscribe (or `move`) action in this routine.
			 *
			 * @param array                                 $other_vars_subscribe Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual AWeber® API call, for the underlying subscribe action in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#subscriber
			 *
			 * @return integer The number of transitions that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$old_segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$new_segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function transition($old_segment, $new_segment, $user = NULL, $silently = NULL, $other_vars_move = array(), $other_vars_subscribe = array())
				{
					$this->check_arg_types(array('null', 'string:!empty', 'array:!empty'), array('string:!empty', 'array:!empty'),
					                       $this->©user_utils->which_types(), array('null', 'boolean'), 'array', 'array', func_get_args());

					$transitions = $move_failures = 0;
					$new_segment = $this->parse_segment($new_segment);
					$user        = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					if(is_null($old_segment)) // All subscribed segments?
						{
							$old_segments = $this->user_subscribed_segments($user);

							if(!$old_segments) // There are none. Do they need to exist?
								{
									if(!$transitions && !$move_failures && !is_null($silently))
										if($this->subscribe($new_segment, $user, $silently, $other_vars_subscribe))
											$transitions++; // A new subscriber.
									return $transitions; // Total number of transitions.
								}
						}
					else $old_segments = array($old_segment); // A specific segment in this case.

					foreach($old_segments as $_old_segment) // Transition each of these ``$old_segments`` now.
						{
							$_old_segment = $this->parse_segment($_old_segment); // Parse each individually.

							if(($_info = $this->user_segment_info($_old_segment, $user)) && $_info['status'] === 'subscribed')
								{
									$_vars         = array_merge(
										array(
											'ws.op'     => 'move',
											'list_link' => $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$new_segment['id']
										), $other_vars_move
									);
									$_api_response = $this->api_response(
										'POST', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$_old_segment['id'].'/subscribers/'.$_info['id'], $_vars
									);
									if($this->©errors->exist_in($_api_response))
										$move_failures++; // An unexpected error (flag as a move failure).
									else $transitions++; // Transitioned successfully.
								}
						}
					unset($_old_segment, $_info, $_vars, $_api_response); // Just a little housekeeping.

					if(!$transitions && !$move_failures && !is_null($silently))
						if($this->subscribe($new_segment, $user, $silently, $other_vars_subscribe))
							$transitions++; // A new subscriber.

					return $transitions; // Total transitions.
				}

			/**
			 * Merge vars for a particular segment.
			 *
			 * @param string|array $segment AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param array        $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying AWeber® API call in this routine.
			 *
			 * @see https://labs.aweber.com/docs/reference/1.0#custom_fields
			 *
			 * @return array|NULL An array of merge vars (perhaps empty), else NULL on failure.
			 *    This may return an empty array, in cases where there are NO custom fields.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 */
			public function merge_vars($segment, $other_vars = array())
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), 'array', func_get_args());

					$segment      = $this->parse_segment($segment);
					$db_cache_key = $this->method(__FUNCTION__).$segment['id'];

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$api_response = $this->api_response(
						'GET', $this->api_url.'/accounts/'.$this->account_id().'/lists/'.$segment['id'].'/custom_fields', $other_vars
					);
					if($this->©errors->exist_in($api_response) || !is_array($api_response)
					   || !$this->©array->is($api_response['entries'])
					) return NULL; // Missing entries.

					$merge_vars = array(); // Initialize.

					foreach($api_response['entries'] as $_merge_var)
						$merge_vars[$_merge_var['name']] = $_merge_var;
					unset($_merge_var); // Housekeeping.

					return $this->©db_cache->update($db_cache_key, $merge_vars);
				}

			/**
			 * Maps merge vars, based on detection; and also based on site owner configuration.
			 *
			 * @param string|array                          $segment AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @return array An array of merge vars, else an empty array if nothing can be populated.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function map_merge_vars($segment, $user = NULL)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), $this->©user_utils->which_types(), func_get_args());

					$segment = $this->parse_segment($segment);
					$user    = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					$merge_vars = array(); // Initialize.

					if(($segment_vars = $this->map_segment_vars($segment, $user)))
						$merge_vars = array_merge($merge_vars, $segment_vars);

					foreach($merge_vars as &$_value)
						$_value = (string)substr($_value, 0, 100);

					return $this->apply_filters(__FUNCTION__, $merge_vars, get_defined_vars());
				}

			/**
			 * Parses AWeber® segment specs.
			 *
			 * @param string|array $segment AWeber® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @return array Array with these important elements:
			 *    • (string)`type` — Always `'type' => 'list'` for AWeber®.
			 *    • (string)`value` — The original string representation of ``$segment``.
			 *       Ex: `0ca05e58cc::My Interests::fishing|computers`.
			 *    • (string)`id` — AWeber® list ID, as determined by AWeber®.
			 *       If we're unable to obtain an ID; defaults to `name`.
			 *    • (string)`name` — AWeber® list name, as determined by AWeber®.
			 *    • (string)`grouping` — Possible title for a set of AWeber® interest groups.
			 *    • (array)`groups` — Possible array of AWeber® interest groups.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (in either a string or array format).
			 * @throws \s2member\exception If ``$segment`` is an array with missing segment specs.
			 */
			public function parse_segment($segment)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), func_get_args());

					if(is_array($segment)) // The segment is already parsed in this case.
						{
							if(count($segment) === 6 // We're simply validating it now.
							   && $this->©strings->are_not_empty($segment['type'], $segment['value'], $segment['id'], $segment['name'])
							   && $this->©string->is($segment['grouping']) && $this->©array->is($segment['groups'])
							) return $segment; // No problems here.

							throw $this->©exception(
								$this->method(__FUNCTION__).'#invalid_segment_array', get_defined_vars(),
								$this->i18n('Invalid `$segment` array (missing one or more keys).').
								' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($segment))
							);
						} // Else we need to parse this string.
					$splits = preg_split('/\:\:/', $segment, 3, PREG_SPLIT_NO_EMPTY);

					$type  = 'list'; // Always `list` for AWeber®.
					$value = $segment; // The original string representation.
					$id    = $name = $splits[0]; // `type`, `value`, `id`, `name` are NEVER empty.

					if(($segment_id = $this->segment_id($name)))
						$id = $segment_id;

					$grouping = $this->©string->is_not_empty_or($splits[1], '');
					$groups   = preg_split('/\|/', $this->©string->is_not_empty_or($splits[2], ''), NULL, PREG_SPLIT_NO_EMPTY);

					return ($segment = array('type'     => $type, 'value' => $value, 'id' => $id, 'name' => $name,
					                         'grouping' => $grouping, 'groups' => $groups));
				}
		}
	}