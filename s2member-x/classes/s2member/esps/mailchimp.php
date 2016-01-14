<?php
/**
 * MailChimp®.
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
		 * MailChimp®.
		 *
		 * @package s2Member\ESPs
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class mailchimp extends \s2member\esps\esp
		{
			/**
			 * @var string The site owner's API key.
			 * @by-constructor Set dynamically by class constructor.
			 */
			public $api_key = '';

			/**
			 * @var string MailChimp® data center.
			 * @by-constructor Set dynamically by class constructor.
			 */
			public $api_dc = '';

			/**
			 * @var string MailChimp® API endpoint URL.
			 * @by-constructor Set dynamically by class constructor.
			 */
			public $api_url = '';

			/**
			 * @var string Integrated API version.
			 */
			public $api_version = '1.3';

			/**
			 * @var array Permissible errors codes; by API method name.
			 * @note This enhances error handling, in a few predictable scenarios.
			 * @see http://apidocs.mailchimp.com/api/1.3/exceptions.field.php
			 */
			public $permissible_error_codes = array(
				'listSubscribe'   => array('214', '230'),
				'listUnsubscribe' => array('215', '231', '232', '233')
			);

			/**
			 * Checks if the MailChimp® has been implemented by the site owner.
			 *
			 * @return boolean TRUE if MailChimp® has been implemented by the site owner; else FALSE.
			 */
			public function is_implemented()
				{
					return ($this->api_key) ? TRUE : FALSE;
				}

			/**
			 * Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					$this->api_key = (string)$this->get_meta_value('api_key');

					if($this->api_key && ($_strpos = strpos($this->api_key, '-')) !== FALSE)
						$this->api_dc = (string)substr($this->api_key, $_strpos + 1);
					unset($_strpos); // Housekeeping.

					if(!$this->api_dc) $this->api_dc = 'us1';

					$this->api_url = 'https://'.$this->api_dc.'.api.mailchimp.com/'.$this->api_version.'/';
				}

			/**
			 * Calls upon the MailChimp® API, and returns a response.
			 *
			 * @param string $method The API method we want to call upon.
			 *
			 * @param array  $vars Optional vars submitted with the API call (when applicable).
			 *
			 * @return array|boolean|\websharks_core_v000000_dev\errors This returns an array of data the MailChimp® API provides in its response.
			 *    Or, this may return boolean TRUE, if there were no errors, and there was no response data.
			 *    Else, this will return an `errors` object if the API call fails, for any reason.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/
			 * @see http://apidocs.mailchimp.com/api/1.3/exceptions.field.php
			 *
			 * @assertion-via-other-methods This is tested indirectly.
			 */
			public function api_response($method, $vars = array())
				{
					$this->check_arg_types('string:!empty', 'array', func_get_args());

					if(!$this->is_implemented())
						return $this->©error(
							$this->method(__FUNCTION__), get_defined_vars(),
							sprintf($this->i18n('MailChimp® API call: `%1$s`).'), $method).
							' '.$this->i18n('MailChimp® NOT yet implemented by site owner.')
						);
					$url = add_query_arg(urlencode_deep(array('method' => $method)), $this->api_url);

					$vars = $this->apply_filters($method.'__vars', // Merge & filter.
					                             array_merge($vars, array('apikey' => $this->api_key, 'output' => 'php')),
					                             get_defined_vars());

					$response = $this->©url->remote($url, $vars, // Do remote API call now.
					                                array('timeout'      => 20, 'redirection' => 0,
					                                      'return_array' => TRUE, 'return_errors' => TRUE)
					);
					if(!is_array($response)) // Connection failure?
						return $this->©error($this->method(__FUNCTION__), get_defined_vars(),
						                     sprintf($this->i18n('MailChimp® API call: `%1$s`. Connection failure.'), $method)
						);
					$mailchimp = array(); // Initialize MailChimp® response array.

					if($response['body']) // Do we have a response body?
						{
							if(!is_array($mailchimp = maybe_unserialize($response['body'])))
								$mailchimp = $this->©vars->parse_query($response['body']);
							$mailchimp = $this->©string->ify_deep($mailchimp);
						}
					if($this->©string->is_not_empty($response['headers']['x-mailchimp-api-error-code']))
						$error_code = $response['headers']['x-mailchimp-api-error-code'];
					else if($response['code'] >= 400) $error_code = (string)$response['code'];

					if(!empty($error_code)) // Handle errors (get error message).
						{
							if(isset($mailchimp['error']) && $this->©string->is_not_empty($mailchimp['error']))
								$error_message = $mailchimp['error'];

							else if($response['message']) // HTTP message?
								$error_message = $response['message'];

							else $error_message = $this->i18n('Check error code.');

							if($this->©array->is_not_empty($this->permissible_error_codes[$method])
							   && in_array($error_code, $this->permissible_error_codes[$method], TRUE)
							) $error_code = $error_message = NULL; // Nullify.

							if($error_code) // Error is NOT permissible.
								return $this->©error($this->method(__FUNCTION__), get_defined_vars(),
								                     sprintf($this->i18n('MailChimp® API call: `%1$s`.'), $method).
								                     ' '.sprintf($this->i18n('Error code: `%1$s`.'), $error_code).
								                     ' '.sprintf($this->i18n('Message: `%1$s`.'), $error_message)
								);
						}
					$this->©success($this->method(__FUNCTION__), get_defined_vars(),
					                sprintf($this->i18n('MailChimp® API call: `%1$s`.'), $method).
					                ' '.$this->i18n('Status: `success`.')
					);
					if(empty($mailchimp)) return TRUE; // Assume success.

					return $mailchimp; // API response data.
				}

			/**
			 * Subscribes an email address.
			 *
			 * @param string|array                          $segment MailChimp® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param boolean                               $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the subscribe action should NOT send a welcome email, or ask the user to confirm.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listsubscribe.func.php
			 *
			 * @return boolean TRUE if the address was subscribed, else FALSE.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', $this->object->©user(1)) === TRUE
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
					$merge_vars = $this->map_merge_vars($segment, $user);

					if($segment['grouping'] && $segment['groups'])
						$merge_vars = array_merge(
							$merge_vars, array(
								'GROUPINGS' => array(
									array(
										'name'   => $segment['grouping'],
										'groups' => implode(',', str_replace(',', '\\,', $segment['groups']))
									)
								)
							)
						);
					$vars = array_merge(
						array(
							'id'                => $segment['id'],
							'email_address'     => $user->email,
							'merge_vars'        => $merge_vars,
							'update_existing'   => TRUE,
							'replace_interests' => TRUE
						), $other_vars
					);
					if($silently) // Silently?
						$vars = array_merge(array(
							                    'double_optin' => FALSE,
							                    'send_welcome' => FALSE
						                    ), $vars);
					$api_response = $this->api_response('listSubscribe', $vars);

					if($this->©errors->exist_in($api_response))
						return FALSE;

					return TRUE;
				}

			/**
			 * Updates existing subscriber data.
			 *
			 * @param null|string|array                     $segment MailChimp® segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an update for ALL subscribed segments.
			 *    In other words, a NULL value indicates that an update should occur for each of the user's currently subscribed segments.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param string                                $prev_email Optional. Defaults to an empty string.
			 *    If the email address is changing, please pass this in.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listupdatemember.func.php
			 *
			 * @return integer The number of updates that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', $this->object->©user(1)) === TRUE
			 */
			public function update($segment = NULL, $user = NULL, $prev_email = '', $other_vars = array())
				{
					$this->check_arg_types(array('null', 'string:!empty', 'array:!empty'),
					                       $this->©user_utils->which_types(), 'string', 'array', func_get_args());

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
									if(!($segments = $this->user_subscribed_segments($user, array('email_address' => $prev_email))))
										return 0; // There are none (stop here).
								}
							else if(!($segments = $this->user_subscribed_segments($user)))
								return 0; // There are none (nothing more we can do).
						}
					else $segments = array($segment); // A specific segment in this case.

					foreach($segments as $_segment) // Update each of these ``$segments`` now.
						{
							$_segment = $this->parse_segment($_segment); // Parse each individually.

							if($prev_email) // The email address is changing too (we'll assume that it is in this case).
								{
									if(!($_info = $this->user_segment_info($_segment, $user, array('email_address' => $prev_email))) || $_info['status'] !== 'subscribed')
										continue; // They do NOT exist; or they're NOT `subscribed` under this previous email address.
								}
							else if(!($_info = $this->user_segment_info($_segment, $user)) || $_info['status'] !== 'subscribed')
								continue; // They do NOT exist; or they are NOT in a `subscribed` state.

							$_merge_vars = $this->map_merge_vars($_segment, $user);
							$_merge_vars = array_merge($_merge_vars, array('NEW-EMAIL' => $user->email));

							$_vars         = array_merge(
								array(
									'id'                => $_segment['id'],
									'email_address'     => $_info['id'],
									'merge_vars'        => $_merge_vars,
									'replace_interests' => FALSE
								), $other_vars
							);
							$_api_response = $this->api_response('listUpdateMember', $_vars);

							if(!$this->©errors->exist_in($_api_response))
								$updates++; // Updated successfully.
						}
					unset($_segment, $_info, $_merge_vars, $_vars, $_api_response);

					return $updates; // Total updates.
				}

			/**
			 * Unsubscribes an email address.
			 *
			 * @param null|string|array                     $segment MailChimp® segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an unsubscribe from ALL subscribed segments.
			 *    In other words, a NULL value indicates that an unsubscribe should occur for each of the user's currently subscribed segments.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param boolean                               $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the unsubscribe action should NOT send a goodbye email, or ask the user to confirm.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listunsubscribe.func.php
			 *
			 * @return integer The number of unsubscribes that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', $this->object->©user(1)) === TRUE
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

							$_vars = array_merge(
								array(
									'id'            => $_segment['id'],
									'email_address' => $user->email,
								), $other_vars
							);
							if($silently) $_vars = array_merge(array(
								                                   'send_goodbye' => FALSE,
								                                   'send_notify'  => FALSE
							                                   ), $_vars);

							$_api_response = $this->api_response('listUnsubscribe', $_vars);

							if(!$this->©errors->exist_in($_api_response))
								// If they do NOT exist, it will also count as an unsubscribe.
								$unsubscribes++; // Unsubscribed successfully.
						}
					unset($_segment, $_vars, $_api_response); // Housekeeping.

					return $unsubscribes; // Total unsubscribes.
				}

			/**
			 * Info for an email address, on a particular segment.
			 *
			 * @param string|array                          $segment MailChimp® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listmemberinfo.func.php
			 *
			 * @return array|NULL An array of info (NOT empty), else NULL if unavailable.
			 *    Note, MailChimp® returns a data array for pending/unsubscribed/cleaned members too.
			 *    So they don't necessarily NEED to be a confirmed subscriber (e.g. they just need to exist in some way).
			 *    The return array will include a `status` index, indicating the current status.
			 *
			 * @note The return array from this method, like all MailChimp® API response data,
			 *    will have been stringified by method ``api_response()``. So don't expect any integers/floats.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', $this->object->©user(1)) is-type 'array'
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
							'id'            => $segment['id'],
							'email_address' => $user->email
						), $other_vars
					);
					$api_response = $this->api_response('listMemberInfo', $vars);

					if($this->©errors->exist_in($api_response) || !is_array($api_response)
					   || !$this->©string->is_not_empty($api_response['success'])
					   || !$this->©array->is_not_empty($api_response['data'])
					) return NULL; // Unable to find any info.

					foreach($api_response['data'] as $_entry)
						if(strcasecmp($_entry['email'], (string)$vars['email_address']) === 0)
							return $_entry; // Info for ``$vars['email_address']``.
					unset($_entry); // Housekeeping.

					return NULL; // Failure.
				}

			/**
			 * Subscribed segments for an email address.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @param array                                 $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listsforemail.func.php
			 *
			 * @return array|NULL An array of subscribed segments (NOT empty), else NULL if unavailable.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ($this->object->©user(1)) is-type 'array'
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
							'email_address' => $user->email
						), $other_vars
					);
					$api_response = $this->api_response('listsForEmail', $vars);

					if($this->©errors->exist_in($api_response) || !$this->©array->is_not_empty($api_response))
						return NULL; // NOT subscribed to any segments.

					return $api_response;
				}

			/**
			 * Moves an email address from one segment to another.
			 *
			 * @param null|string|array                     $old_segment Old MailChimp® segment specs.
			 *    NULL, a string, or an already parsed array of segment specs (e.g. to specify a specific segment).
			 *    A NULL value indicates a transition for ALL subscribed segments. In other words, a NULL value indicates that a transition to ``$new_segment``,
			 *       should occur for each of the user's currently subscribed segments.
			 *
			 * @param string|array                          $new_segment New MailChimp® segment specs.
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
			 *
			 *    • In any of these scenarios, an actual "transition" always occur silently.
			 *       In other words, when/if they DO exist on an ``$old_segment`` (we're simply moving them silently — in all cases).
			 *
			 *    • In any of these scenarios, if the user IS currently subscribed, we will NOT subscribe them to the ``$new_segment``,
			 *       unless we can successfully unsubscribe them from an ``$old_segment`` (or, if they do NOT exist at all, in the case of `FALSE|TRUE`).
			 *
			 * @param array                                 $other_vars_unsubscribe Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual MailChimp® API call, for the underlying unsubscribe action in this routine.
			 *
			 * @param array                                 $other_vars_subscribe Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual MailChimp® API call, for the underlying subscribe action in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listunsubscribe.func.php
			 * @see http://apidocs.mailchimp.com/api/1.3/listsubscribe.func.php
			 *
			 * @return integer The number of transitions that occurred; else `0` by default.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$old_segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$new_segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', '0ca05e58cc', $this->object->©user(1)) === TRUE
			 */
			public function transition($old_segment, $new_segment, $user = NULL, $silently = NULL, $other_vars_unsubscribe = array(), $other_vars_subscribe = array())
				{
					$this->check_arg_types(array('null', 'string:!empty', 'array:!empty'), array('string:!empty', 'array:!empty'),
					                       $this->©user_utils->which_types(), array('null', 'boolean'), 'array', 'array', func_get_args());

					$transitions = $unsubscribe_failures = 0;
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
									if(!$transitions && !$unsubscribe_failures && !is_null($silently))
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
									if(!$this->unsubscribe($_old_segment, $user, TRUE, $other_vars_unsubscribe))
										$unsubscribe_failures++; // Unable to unsubscribe (flag failure).

									else if($transitions > 0 || $this->subscribe($new_segment, $user, TRUE, $other_vars_subscribe))
										$transitions++; // Transitioned successfully.
								}
						}
					unset($_old_segment, $_info); // Just a little housekeeping here.

					if(!$transitions && !$unsubscribe_failures && !is_null($silently))
						if($this->subscribe($new_segment, $user, $silently, $other_vars_subscribe))
							$transitions++; // A new subscriber.

					return $transitions; // Total transitions.
				}

			/**
			 * Merge vars for a particular segment.
			 *
			 * @param string|array $segment MailChimp® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param array        $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the underlying MailChimp® API call in this routine.
			 *
			 * @see http://apidocs.mailchimp.com/api/1.3/listmemberinfo.func.php
			 *
			 * @return array|NULL An array of merge vars (NOT empty), else NULL on failure.
			 *    MailChimp® returns an array containing at least the email address.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc') is-type 'array'
			 */
			public function merge_vars($segment, $other_vars = array())
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), 'array', func_get_args());

					$segment      = $this->parse_segment($segment);
					$db_cache_key = $this->method(__FUNCTION__).$segment['id'];

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$vars         = array_merge(array('id' => $segment['id']), $other_vars);
					$api_response = $this->api_response('listMergeVars', $vars);

					if($this->©errors->exist_in($api_response) || !is_array($api_response))
						return NULL; // Unable to acquire merge vars.

					$merge_vars = array(); // Initialize.

					foreach($api_response as $_merge_var)
						$merge_vars[$_merge_var['tag']] = $_merge_var;
					unset($_merge_var); // Housekeeping.

					return $this->©db_cache->update($db_cache_key, $merge_vars);
				}

			/**
			 * Maps merge vars, based on detection; and also based on site owner configuration.
			 *
			 * @param string|array                          $segment MailChimp® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @return array An array of merge vars, else an empty array if nothing can be populated.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 *
			 * @assert $this->object->api_key = '875b340c1d1cc0b3e2d03b876a1a16c6-us1';
			 *    ('0ca05e58cc', $this->object->©user(1)) is-type 'array'
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
					$merge_vars = array(
						'OPTIN_IP'   => $user->ip,
						'OPTIN_TIME' => date('Y-m-d H:i:s', time())
					);
					if(($segment_vars = $this->map_segment_vars($segment, $user)))
						$merge_vars = array_merge($merge_vars, $segment_vars);

					else // We'll assume they are using default MailChimp® merge vars.
						$merge_vars = array_merge($merge_vars, array('MERGE1' => $user->first_name, 'MERGE2' => $user->last_name));

					foreach($merge_vars as &$_value)
						$_value = (string)substr($_value, 0, 255);

					return $this->apply_filters(__FUNCTION__, $merge_vars, get_defined_vars());
				}

			/**
			 * Parses MailChimp® segment specs.
			 *
			 * @param string|array $segment MailChimp® segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 * @return array Array with these important elements:
			 *    • (string)`type` — Always `'type' => 'list'` for MailChimp®.
			 *    • (string)`value` — The original string representation of ``$segment``.
			 *          Ex: `0ca05e58cc::My Interests::fishing|computers`.
			 *    • (string)`id` — MailChimp® list ID, as determined by MailChimp®.
			 *    • (string)`name` — Alias for ID. MailChimp® list ID.
			 *    • (string)`grouping` — Possible title for a set of MailChimp® interest groups.
			 *    • (array)`groups` — Possible array of MailChimp® interest groups.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (in either a string or array format).
			 * @throws \s2member\exception If ``$segment`` is an array with missing segment specs.
			 *
			 * @assert ('0ca05e58cc') === array('type' => 'list', 'value' => '0ca05e58cc', 'id' => '0ca05e58cc', 'name' => '0ca05e58cc', 'grouping' => '', 'groups' => array())
			 * @assert ('0ca05e58cc::My Interests') === array('type' => 'list', 'value' => '0ca05e58cc::My Interests', 'id' => '0ca05e58cc', 'name' => '0ca05e58cc', 'grouping' => 'My Interests', 'groups' => array())
			 * @assert ('0ca05e58cc::My Interests::fishing') === array('type' => 'list', 'value' => '0ca05e58cc::My Interests::fishing', 'id' => '0ca05e58cc', 'name' => '0ca05e58cc', 'grouping' => 'My Interests', 'groups' => array('fishing'))
			 * @assert ('0ca05e58cc::My Interests::fishing|computers') === array('type' => 'list', 'value' => '0ca05e58cc::My Interests::fishing|computers', 'id' => '0ca05e58cc', 'name' => '0ca05e58cc', 'grouping' => 'My Interests', 'groups' => array('fishing', 'computers'))
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

					$type  = 'list'; // Always `list` for MailChimp®.
					$value = $segment; // The original string representation.
					$id    = $name = $splits[0]; // `type`, `value`, `id`, `name` are NEVER empty.

					$grouping = $this->©string->is_not_empty_or($splits[1], '');
					$groups   = preg_split('/\|/', $this->©string->is_not_empty_or($splits[2], ''), NULL, PREG_SPLIT_NO_EMPTY);

					return ($segment = array('type'     => $type, 'value' => $value, 'id' => $id, 'name' => $name,
					                         'grouping' => $grouping, 'groups' => $groups));
				}
		}
	}