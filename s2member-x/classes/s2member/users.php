<?php
/**
 * s2Member® Users.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Users
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * s2Member® Users.
		 *
		 * @package s2Member\Users
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__], 1)
		 *
		 * @property \s2member\events         $©events
		 * @property \s2member\events         $©event
		 * @method \s2member\events ©events()
		 * @method \s2member\events ©event()
		 *
		 * @property \s2member\exception      $©exception
		 * @method \s2member\exception ©exception()
		 *
		 * @property \s2member\passtags       $©passtags
		 * @property \s2member\passtags       $©passtag
		 * @method \s2member\passtags ©passtags()
		 * @method \s2member\passtags ©passtag()
		 *
		 * @property \s2member\profile_fields $©profile_fields
		 * @property \s2member\profile_fields $©profile_field
		 * @method \s2member\profile_fields ©profile_fields()
		 * @method \s2member\profile_fields ©profile_field()
		 *
		 * @property \s2member\user_utils     $©user_utils
		 * @method \s2member\user_utils ©user_utils()
		 */
		class users extends \websharks_core_v000000_dev\users
		{
			/**
			 * Populate user's custom {@link $data} keys?
			 *
			 * @var boolean Populate user's custom {@link $data} keys?
			 * @note This should NOT be enabled unless absolutely necessary.
			 */
			protected $populate_data = FALSE; // Only as needed :-)

			/**
			 * Populates user object properties.
			 *
			 * @return null Nothing. Simply populates user object properties.
			 *
			 * @param array $default_properties Optional. Any default and/or custom properties.
			 *    If empty, this defaults to {@link $args} value for `default_properties`.
			 */
			public function populate($default_properties = array())
				{
					$this->check_arg_types('array', func_get_args());

					parent::populate($default_properties); // Like any other :-)
					if($this->populate_data && $this->has_id()) $this->populate_data(TRUE);
					if($this->has_id()) return; // If user has an ID we're done.

					populate_basic_data_iaap: // Populate this user if at all possible.

					if($this->has_args_by_value()) // Can use arguments from constructor (e.g. by/value)?
						if(($data = $this->©user_utils->get_basic_data_iaap_by($this->args['by'], $this->args['value'], FALSE)))
							goto populate_basic_data; // Jump down below (we DO have basic data).

					if($this->has_session_access_keys()) // Can try session access keys?
						if(($data = $this->©user_utils->get_basic_data_iaap_by('access_key', $this->session_access_keys())))
							goto populate_basic_data; // Jump down below (we DO have basic data).

					if($this->has_session_data() && ($data = $this->session_data($this::object_p, TRUE)))
						goto populate_basic_data; // Jump down below (we DO have basic data).

					return; // FAIL: nothing we can do in this case.

					populate_basic_data: // Success! Populate basic data properties :-)

					if(!$this->ip) $this->ip = $data->ip;
					$this->email        = $data->email;
					$this->first_name   = $data->first_name;
					$this->last_name    = $data->last_name;
					$this->full_name    = $data->full_name;
					$this->display_name = $data->display_name;
					if($this->populate_data) $this->populate_data(TRUE);
				}

			/**
			 * Populates user's custom {@link $data} keys.
			 *
			 * @param boolean $___on_populate Internal use only. See {@link populate()}.
			 *
			 *    Direct calls should NOT result in unnecessary/repeated routines.
			 *    We only populate this user if it has NOT been done already.
			 *    ~ OR, if called by {@link populate()}; e.g. refreshing.
			 *
			 * @note This should NOT be called unless necessary.
			 * @note See also {@link $populate_data}; protected property.
			 */
			public function populate_data($___on_populate = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					if(isset($this->cache[__FUNCTION__]) && !$___on_populate)
						return; // Populated data already :-)

					$this->data['access_keys']   = $this->access_keys();
					$this->data['passtag_ids']   = $this->passtag_ids();
					$this->data['passtag__ids']  = $this->passtag__ids();
					$this->data['passtag_names'] = $this->passtag_names();

					if(($profile_fields = $this->profile_fields()))
						{
							foreach($profile_fields['by_profile_field_name'] as $_name => $_data)
								$this->data['profile_field'][$_name] = $_data['value'];
							$this->data['profile_fields'] = $profile_fields['by_profile_field_name'];
							unset($_name, $_data); // Housekeeping.
						}
					else $this->data['profile_field'] = $this->data['profile_fields'] = array();

					$this->cache[__FUNCTION__] = $this->populate_data = TRUE;
				}

			/**
			 * Starts a session for this user (if they are the current user).
			 *
			 * @return null Nothing. Simply starts a session for this user (if they are the current user).
			 */
			public function session_start()
				{
					if(!$this->is_current()) return;

					$session_data = array('ip' => $this->ip);

					if($this->has_id()) // ID/username?
						$session_data = array_merge($session_data,
						                            array(
							                            'ID'       => $this->ID,
							                            'username' => $this->username
						                            ));
					if($this->is_populated()) // Other?
						$session_data = array_merge($session_data,
						                            array(
							                            'email'        => $this->email,
							                            'first_name'   => $this->first_name,
							                            'last_name'    => $this->last_name,
							                            'full_name'    => $this->full_name,
							                            'display_name' => $this->display_name
						                            ));
					$session_data['access_keys'] = array(); // Initialize access keys.
					$access_key                  = $this->©vars->_REQUEST($this->___instance_config->plugin_var_ns.'_access_key');
					$access_keys                 = $this->©vars->_REQUEST($this->___instance_config->plugin_var_ns.'_access_keys');

					if($this->©string->is_not_empty($access_key)) $session_data['access_keys'][] = $access_key;
					if($this->©string->is_not_empty($access_keys) && is_array($access_keys = maybe_unserialize($access_keys)))
						$session_data['access_keys'] = array_merge($session_data['access_keys'], $access_keys);

					$this->update_session_data($session_data);
				}

			/**
			 * Ends session for this user (if they are the current user).
			 *
			 * @return null Nothing. Simply ends a session for this user (if they are the current user).
			 */
			public function session_end()
				{
					if(!$this->is_current()) return;

					$this->©cookie->delete('session_data');
				}

			/**
			 * Does this user have session data?
			 *
			 * @return boolean TRUE if this user has session data, else FALSE.
			 */
			public function has_session_data()
				{
					return ($this->session_data()) ? TRUE : FALSE;
				}

			/**
			 * Gets session data for this user (if they are the current user).
			 *
			 * @param string  $return Type of return value. Defaults to ``fw_constants::array_a`` (an associative array).
			 *    This can be set to ``fw_constants::object_p``, to instead return an object with data properties (if possible).
			 *    If this is set to ``fw_constants::object_p``, and there is no data; this returns NULL by default.
			 *
			 * @param boolean $email_required Optional. This defaults to a FALSE value.
			 *    If this is TRUE, we only return data if we find an email address in the session.
			 *
			 * @return null|array|object An associative array, or an object with properties; else NULL.
			 *    By default, this returns an array of session data, else an empty array if there is no session data.
			 *    If ``$return`` is set to ``fw_constants::object_p``, and there is no data; this returns NULL by default.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function session_data($return = self::array_a, $email_required = FALSE)
				{
					$this->check_arg_types('string:!empty', 'boolean', func_get_args());

					if(isset($this->cache[__FUNCTION__]))
						goto finale; // Skip to finale.

					$this->cache[__FUNCTION__] = array(); // Initialize.

					if(!$this->is_current()) goto finale; // NOT possible (or empty)?
					if(!($session_data = $this->©cookie->get('session_data'))) goto finale;
					if(!is_array($session_data = maybe_unserialize($session_data))) goto finale;

					foreach($session_data as $_key => &$_value) // Sanitize.
						{
							if(!array_key_exists($_key, $this->©user_utils->session_data_defaults, TRUE))
								unset($session_data[$_key]); // Invalid key (MUST exist in defaults).

							if(gettype($_value) !== gettype($this->©user_utils->session_data_defaults[$_key]))
								unset($session_data[$_key]); // Invalid type (MUST match default).

							if(!isset($session_data[$_key])) continue; // Unset above?
							if(!is_array($_value)) continue; // Nothing more to do here?

							$_value = $this->©array->to_one_dimension($_value);
							$_value = $this->©string->ify_deep($_value);
							$_value = $this->©array->remove_0b_strings_deep($_value);

							if($_key === 'access_keys') foreach($_value as $__key => &$__value)
								if(strlen($__value) !== 64) unset($_value[$__key]);
							unset($__key, $__value); // Housekeeping.
						}
					unset($_key, $_value); // Just a little housekeeping now.

					$session_data              = array_merge($this->©user_utils->session_data_defaults, $session_data);
					$this->cache[__FUNCTION__] = $session_data; // Standardized now (includes all default keys too).

					finale: // Target point. Data returned here (based on preference).

					if(!$this->cache[__FUNCTION__] // This could definitely be empty (MUST check).
					   || ($email_required && !$this->©string->is_not_empty($this->cache[__FUNCTION__]['email']))
					) return ($return === $this::object_p) ? NULL : array();

					$this->cache[__FUNCTION__.$this::object_p] = (object)$this->cache[__FUNCTION__];

					if($return === $this::object_p) // Object properties?
						return $this->cache[__FUNCTION__.$this::object_p];

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Updates session data for this user (if they ARE the current user).
			 *
			 * @param array $values An associative array of key/value pairs to update.
			 *
			 * @return boolean TRUE if session data was updated (for current user); else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If called after headers have already been sent!
			 */
			public function update_session_data($values)
				{
					$this->check_arg_types('array', func_get_args());

					if(!$this->is_current()) return FALSE; // Not possible.

					if(!($session_data = $this->session_data())) // Use defaults if empty.
						$session_data = $this->©user_utils->session_data_defaults;

					foreach($values as $_key => $_value) // Sanitize incoming data before update.
						{
							if(!array_key_exists($_key, $this->©user_utils->session_data_defaults))
								continue; // This key does NOT exist in session data defaults.

							if(!is_null($this->©user_utils->session_data_defaults[$_key])
							   && gettype($_value) !== gettype($this->©user_utils->session_data_defaults[$_key])
							) continue; // MUST match default data type (unless NULL).

							if($_key !== 'access_keys') // Only access keys are special :-)
								{
									$session_data[$_key] = $_value; // Keep value.
									continue; // We're done here.
								}
							foreach($_value as $_access_key) // We also validate the length here.
								if($this->©string->is_not_empty($_access_key) && strlen($_access_key) === 64)
									array_unshift($session_data[$_key], $_access_key);
							unset($_access_key); // A little housekeeping.

							$session_data[$_key] = array_unique($session_data[$_key]);

							// Maximum access keys allowed in a session is `10` (by default; but this can be changed).
							// With `10` access keys + other session data, the average encrypted cookie size is about `1650` bytes.
							// Maximum cookie size per domain (for all cookies combined) is around `4000` bytes.

							$session_data[$_key] = array_slice($session_data[$_key], 0, // Default maximum is `10`.
							                                   (integer)$this->©option->get('users.sessions.max_access_keys'));
						}
					unset($_key, $_value); // Just a little housekeeping here.

					$this->©cookie->set('session_data', serialize($session_data), // All cookies are encrypted also!
					                    strtotime('+'.$this->©options->get('users.sessions.cookie_expiration_offset_time')) - time());

					$this->refresh(array('session_data', 'session_access_keys', 'access_keys'));

					return TRUE; // Updated session.
				}

			/**
			 * Does this user have session access keys?
			 *
			 * @return boolean TRUE if this user has session access keys, else FALSE.
			 */
			public function has_session_access_keys()
				{
					return ($this->session_access_keys()) ? TRUE : FALSE;
				}

			/**
			 * Gets session access keys for this user (if they are the current user).
			 *
			 * @note This also handles/tracks session access keys (but ONLY if this IS the current user).
			 *    Sessions are handled with cookies. This method should be called upon by `init()`, at WordPress® hook priority `1`.
			 *
			 * @return array Array of all session access keys, else an empty array if there are none.
			 */
			public function session_access_keys()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					if(!$this->is_current()) // Not possible.
						return $this->cache[__FUNCTION__];

					if(($session_data = $this->session_data($this::object_p)))
						$this->cache[__FUNCTION__] = $session_data->access_keys;

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Does this user have access keys?
			 *
			 * @return boolean TRUE if this user has access keys, else FALSE.
			 */
			public function has_access_keys()
				{
					return ($this->access_keys()) ? TRUE : FALSE;
				}

			/**
			 * Gets all access keys for this user (including session access keys).
			 *
			 * @return array Array of all access keys, else an empty array if there are none.
			 */
			public function access_keys()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = $this->session_access_keys();

					if($this->has_id()) // Ideally; but this is NOT always the case.
						{
							$query = // Get all access keys associated with this user ID.
								"SELECT".
								" `user_passtags`.`access_key`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."'".
								" AND `user_passtags`.`user_id` IS NOT NULL".
								" AND `user_passtags`.`user_id` > '0'".

								" AND `user_passtags`.`access_key` IS NOT NULL".
								" AND `user_passtags`.`access_key` != ''";

							if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
								$this->cache[__FUNCTION__] = array_merge($this->cache[__FUNCTION__], $result_access_keys);
						}
					if($this->has_args_by_value())
						$this->cache[__FUNCTION__] = array_merge(
							$this->cache[__FUNCTION__], // Merge any other keys found by args.
							$this->©user_utils->get_access_keys_iaap_by($this->args['by'], $this->args['value'], FALSE)
						);
					if($this->©array->is_not_empty($this->data[__FUNCTION__]))
						$this->cache[__FUNCTION__] = array_merge($this->cache[__FUNCTION__], $this->data[__FUNCTION__]);

					$this->cache[__FUNCTION__] = array_unique($this->cache[__FUNCTION__]);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets a user profile field object, for a specific profile field.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a user profile field.
			 *
			 * @return object|null A user profile field object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function profile_field($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!$this->has_id()) return NULL; // No ID. No profile fields.

					$profile_fields = $this->profile_fields(); // All profile fields.

					if(is_integer($profile_field_id_or_name) && isset($profile_fields['by_profile_field_id'][$profile_field_id_or_name]['properties']))
						{
							$profile_field = $this->©array->first($profile_fields['by_profile_field_id'][$profile_field_id_or_name]['properties']);

							$profile_field = clone $profile_field; // Clone (so we do NOT affect the underlying object properties).
							unset($profile_field->label, $profile_field->value); // Exclude value-specific properties.

							if($profile_fields['by_profile_field_id'][$profile_field_id_or_name]['has_array_value'])
								$profile_field->has_array_value = 1;
							else $profile_field->has_array_value = 0;

							return $profile_field; // Object properties.
						}
					else if(is_string($profile_field_id_or_name) && isset($profile_fields['by_profile_field_name'][$profile_field_id_or_name]['properties']))
						{
							$profile_field = $this->©array->first($profile_fields['by_profile_field_name'][$profile_field_id_or_name]['properties']);

							$profile_field = clone $profile_field; // Clone (so we do NOT affect the underlying object properties).
							unset($profile_field->label, $profile_field->value); // Exclude value-specific properties.

							if($profile_fields['by_profile_field_name'][$profile_field_id_or_name]['has_array_value'])
								$profile_field->has_array_value = 1;
							else $profile_field->has_array_value = 0;

							return $profile_field; // Object properties.
						}
					return NULL; // Default return value.
				}

			/**
			 * Gets a user profile field value, for a specific profile field.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a user profile field.
			 *
			 * @return string|array|null A string value (if NOT an array value). Or, an array of values (if it IS in an array value).
			 *    Otherwise, this returns a NULL value if the field is currently unavailable (i.e. has no value at all).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function profile_field_value($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!$this->has_id()) return NULL; // No ID. No profile fields.

					$profile_field = $this->profile_field($profile_field_id_or_name);

					if($profile_field && $profile_field->has_array_value)
						return $this->profile_field_values($profile_field->profile_field_id);

					if($profile_field && !$profile_field->has_array_value)
						return $this->©array->first($this->profile_field_values($profile_field->profile_field_id));

					return NULL; // Default return value.
				}

			/**
			 * Gets an array of user profile field values, for a specific profile field.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a user profile field.
			 *
			 * @return array An array of user profile field values, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function profile_field_values($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!$this->has_id()) return array(); // No ID. No profile fields.

					$profile_fields = $this->profile_fields();

					if(is_integer($profile_field_id_or_name) && isset($profile_fields['by_profile_field_id'][$profile_field_id_or_name]['values']))
						return $profile_fields['by_profile_field_id'][$profile_field_id_or_name]['values'];

					if(is_string($profile_field_id_or_name) && isset($profile_fields['by_profile_field_name'][$profile_field_id_or_name]['values']))
						return $profile_fields['by_profile_field_name'][$profile_field_id_or_name]['values'];

					return array(); // Default return value.
				}

			/**
			 * Gets an array of user profile field value labels, for a specific profile field.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a user profile field.
			 *
			 * @return array An array of user profile field value labels, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function profile_field_value_labels($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!$this->has_id()) return array(); // No ID. No profile fields.

					$profile_fields = $this->profile_fields();

					if(is_integer($profile_field_id_or_name) && isset($profile_fields['by_profile_field_id'][$profile_field_id_or_name]['value_labels']))
						return $profile_fields['by_profile_field_id'][$profile_field_id_or_name]['value_labels'];

					if(is_string($profile_field_id_or_name) && isset($profile_fields['by_profile_field_name'][$profile_field_id_or_name]['value_labels']))
						return $profile_fields['by_profile_field_name'][$profile_field_id_or_name]['value_labels'];

					return array(); // Default return value.
				}

			/**
			 * Gets all profile fields.
			 *
			 * @return array Array of profile fields for this user.
			 */
			public function profile_fields()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					if(!$this->has_id()) // No ID. No profile fields.
						return $this->cache[__FUNCTION__];

					$query =
						"SELECT".
						" `profile_fields`.`name` AS `name`,".
						" `profile_field_types`.`type` AS `type`,".
						" `user_profile_fields`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_profile_fields'))."` AS `user_profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_types'))."` AS `profile_field_types`".

						" WHERE".
						" `user_profile_fields`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."'".
						" AND `user_profile_fields`.`profile_field_id` = `profile_fields`.`ID`".
						" AND `profile_fields`.`profile_field_type_id` = `profile_field_types`.`ID`".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_field_types`.`type` IS NOT NULL".
						" AND `profile_field_types`.`type` != ''".

						" AND `user_profile_fields`.`profile_field_id` IS NOT NULL".
						" AND `user_profile_fields`.`profile_field_id` > '0'".

						" AND `profile_fields`.`profile_field_type_id` IS NOT NULL".
						" AND `profile_fields`.`profile_field_type_id` > '0'".

						" ORDER BY `user_profile_fields`.`time` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$this->cache[__FUNCTION__]['by_id'][$_result->ID]                                                           = $_result;
									$this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id]['value']                      = $_result->value;
									$this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id]['values'][$_result->ID]       = $_result->value;
									$this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id]['value_labels'][$_result->ID] = $_result->label;
									$this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id]['has_array_value']            = ($_result->in_array) ? 1 : 0;
									$this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id]['properties'][$_result->ID]   =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_profile_field_name'][$_result->name]                                         =& $this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id];
									$this->cache[__FUNCTION__]['by_profile_field_type'][$_result->type]                                         =& $this->cache[__FUNCTION__]['by_profile_field_id'][$_result->profile_field_id];
								}
							unset($_result); // Housekeeping.
						}
					return $this->cache[__FUNCTION__];
				}

			/**
			 * Updates additional profile fields implemented by site owners.
			 *
			 * @param array $profile_field_values An associative array of profile fields (by code).
			 *
			 * @return boolean|\websharks_core_v000000_dev\errors TRUE on success; else an errors object on failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function update_profile_fields($profile_field_values)
				{
					$this->check_arg_types('array', func_get_args());

					if(!$this->has_id()) return TRUE; // No ID. No profile fields.

					$validations = $this->©user_utils->validate_profile_fields($this, -1, $profile_field_values, $this::context_profile_updates,
					                                                           array('check_passtag_restrictions' => FALSE, 'enforce_required_fields' => FALSE));
					if($this->©errors->exist_in($validations)) return $validations; // Validation errors.

					foreach($this->©profile_fields->for_user_profile_update_form_fields($this, -1, FALSE) as $_name_code => $_field)
						{
							$_value = (isset($profile_field_values[$_name_code])) ? $profile_field_values[$_name_code] : NULL;
							if(!isset($_value)) continue; // No value; nothing to update in this case.
							if(is_array($_value)) unset($_value['___update'], $_value['___file_info']);

							$this->delete_profile_field_values($_name_code); // First delete all existing values.

							$_query = // Prepares database table insertions.
								"INSERT INTO `".$this->©string->esc_sql($this->©db_tables->get('user_profile_fields'))."`".
								"(".$this->©db_utils->comma_tickify(array('user_id', 'profile_field_id', 'label', 'value', 'in_array', 'time')).")";

							$_values = array(); // Initialize.
							$_time   = time(); // Current UTC time.

							if(is_array($_value) && in_array($_field['type'], $this->©form_fields->types_with_options, TRUE))
								{
									foreach($_value as $_selected_option_value) // Iterate each value.
										foreach($_field['options'] as $_option) // Iterate each option value (``$_option['value']`` MUST match up).
											if($_option['value'] === $_selected_option_value) // Matches this string option value.
												{
													$_values[] = "(".$this->©db_utils->comma_quotify(array($this->ID, $_field['ID'], $this->©string->is_not_empty_or($_option['label'], NULL), $_option['value'], 1, $_time), TRUE).")";
													break; // Only one option value for each match (e.g. we do NOT insert multiple option value matches here).
												}
									unset($_selected_option_value, $_option); // Housekeeping.
								}
							else if(is_array($_value)) // Any other array of values.
								{
									foreach($_value as $__value)
										$_values[] = "(".$this->©db_utils->comma_quotify(array($this->ID, $_field['ID'], NULL, $this->©profile_field_conversions->process($_field['ID'], $__value), 1, $_time), TRUE).")";
									unset($__value); // Housekeeping.
								}
							else if(is_string($_value) && in_array($_field['type'], $this->©form_fields->types_with_options, TRUE))
								{
									foreach($_field['options'] as $_option) // Iterate each option value (``$_option['value']`` MUST match up).
										if($_option['value'] === $_value) // Matches this string option value.
											{
												$_values[] = "(".$this->©db_utils->comma_quotify(array($this->ID, $_field['ID'], $this->©string->is_not_empty_or($_option['label'], NULL), $_option['value'], 0, $_time), TRUE).")";
												break; // Only one option value for each match (e.g. we do NOT insert multiple option value matches here).
											}
									unset($_option); // Housekeeping.
								}
							else if(is_string($_value)) // Any other string value.
								$_values[] = "(".$this->©db_utils->comma_quotify(array($this->ID, $_field['ID'], NULL, $this->©profile_field_conversions->process($_field['ID'], $_value), 0, $_time), TRUE).")";

							if($_values) // Insert/update (but only if we DO have values).
								{
									$_query .= " VALUES".implode(',', $_values);

									if(!(integer)$this->©db->query($_query)) // Database update failure.
										throw $this->©exception($this->method(__FUNCTION__).'#update_failure', array_merge(get_defined_vars(), array('user' => $this)),
										                        $this->i18n('Database insertion failure. Unable to update profile.'));
								}
							unset($_query, $_value, $_values, $_time); // Housekeeping.
						}
					unset($_name_code, $_field, $_query, $_value, $_values, $_time); // Housekeeping.

					$this->refresh('profile_fields'); // Delete/refresh profile fields cache.

					return TRUE; // Updated profile fields.
				}

			/**
			 * Deletes a user profile field (by deleting all values associated with it).
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a user profile field.
			 *
			 * @return integer The number of database rows deleted from `user_profile_fields` table.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function delete_profile_field_values($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!$this->has_id()) return 0; // No ID. No profile fields.

					if(!($user_profile_field_ids = array_keys($this->profile_field_values($profile_field_id_or_name))))
						return 0; // There's nothing to delete.

					$deletions = // Deletes all profile field values (i.e. user profile fields).
						(integer)$this->©db->query(
							"DELETE".
							" `user_profile_fields`".

							" FROM".
							" `".$this->©string->esc_sql($this->©db_tables->get('user_profile_fields'))."` AS `user_profile_fields`".

							" WHERE".
							" `user_profile_fields`.`ID` IN(".$this->©db_utils->comma_quotify($user_profile_field_ids).")"
						);
					$this->refresh('profile_fields'); // Delete/refresh the profile fields cache.

					return $deletions; // Total number of deletions.
				}

			/**
			 * Refreshes all passtag caches.
			 *
			 * @return null Nothing. Simply refreshes passtag caches.
			 */
			public function refresh_passtags()
				{
					$this->refresh( // We refresh ALL of these keys.
					// Keys related to (or derived from) user passtags.
						array('passtag',
						      'passtags',
						      'passtag_ids',
						      'passtag__ids',
						      'passtags_for',
						      'active_passtags',
						      'inactive_passtags',
						      'deleted_passtags',
						      'accessible_passtags',
						      'inaccessible_passtags',
						      'inaccessible_expired_passtags',
						      'inaccessible_used_passtags',
						      'inaccessible_iped_passtags',
						      'passtag_is_within_time_starts_stops',
						      'passtag_is_within_uses_limit',
						      'passtag_is_within_ips_limit',
						      // These are derived from passtags.
						      'access_keys', // Unique passtag identifiers.
						      'transaction_ids', 'transactions', 'last_transaction')
					);
				}

			/**
			 * Gets a specific user passtag.
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @return null|object A user passtag object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function passtag($id_or_access_key)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$passtags = $this->passtags(TRUE);

					if(is_integer($id_or_access_key) && isset($passtags['by_id'][$id_or_access_key]))
						return $passtags['by_id'][$id_or_access_key];

					if(is_string($id_or_access_key) && isset($passtags['by_access_key'][$id_or_access_key]))
						return $passtags['by_access_key'][$id_or_access_key];

					return NULL; // Default return value.
				}

			/**
			 * Gets specific user passtags (based on search criteria).
			 *
			 * @param array|string $args Array of search criteria; or {@link fw_constants::all}.
			 *
			 *   • If this is a string w/ value {@link fw_constants:all}; we return ALL user passtags.
			 *
			 *   • If this is an array, and ANY array element is an object with `ID` & `passtag_id` properties;
			 *       we simply pass ``$args`` through as an existing set of user passtags (e.g. existing results).
			 *
			 *   • An array of SEARCH CRITERIA may include any combination of the following array keys.
			 *
			 *       • `id_or_access_key`(null|integer|string|array) By user passtag ID(s) or access key(s).
			 *       • `passtag_id_or_name`(null|integer|string|array) By passtag ID(s) or passtag name(s).
			 *       • `order_session_id`(null|integer|array) By order session ID(s).
			 *       • `transaction_id`(null|integer|array) By transaction ID(s).
			 *
			 *       • ↑ Any of these may contain {@link fw_constants::all} to indicate ALL of something.
			 *
			 *       • `+descendants`(boolean) Plus ALL passtag descendants underlying user passtags?
			 *          EXAMPLE: If search results include user passtag ID #5, which points to an underlying passtag A;
			 *          do you ALSO want to include (automatically) ALL user passtags pointing to passtag A descendants?
			 *          ~ TIP: Best to use this w/ a `passtag_id_or_name` search query.
			 *
			 *   • NOTE: This returns an empty array if SEARCH CRITERIA contains all NULL key values.
			 *       You MUST give this function something to search with; else pass {@link fw_constants::all}.
			 *
			 *   • NOTE: An empty array (e.g. no criteria; or no user passtags) throws an exception.
			 *
			 * @return array Array of user passtag objects w/ matching criteria.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args`` is empty for some reason.
			 */
			public function passtags_for($args)
				{
					$this->check_arg_types(array('array:!empty', 'string:!empty'), func_get_args());

					if(is_string($args)) if($args === $this::all) return $this->passtags();
					else throw $this->©exception( // Invalid string. Must have {@link fw_constants::all}.
						$this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						sprintf($this->i18n('Invalid string parameter value: `%1$s`.'), $args));

					foreach($args as $_possible_user_passtag) // Existing results (e.g. user passtags)?
						if(is_object($_possible_user_passtag) && isset($_possible_user_passtag->ID, $_possible_user_passtag->passtag_id))
							return $args; // An existing result set (simply pass these through).
					unset($_possible_user_passtag); // Housekeeping.

					$default_args = array( // NULL = no filter.
					                       'id_or_access_key' => NULL, 'passtag_id_or_name' => NULL,
					                       'order_session_id' => NULL, 'transaction_id' => NULL, '+descendants' => FALSE
					);
					$args         = $this->check_extension_arg_types( // Validates each argument value.
						array('null', 'integer:!empty', 'string:!empty', 'array:!empty'), // User passtag reference(s).
						array('null', 'integer:!empty', 'string:!empty', 'array:!empty'), // Underlying passtag reference(s).
						array('null', 'integer:!empty', 'string:!empty', 'array:!empty'), // Order session reference(s).
						array('null', 'integer:!empty', 'string:!empty', 'array:!empty'), // Transaction reference(s).
						'boolean', $default_args, $args); // MUST have at least one key to search by (see below).

					if(!isset($args['id_or_access_key']) && !isset($args['passtag_id_or_name']))
						if(!isset($args['order_session_id']) && !isset($args['transaction_id']))
							return array(); // No criteria; no passtags.

					foreach($args as $_key => &$_arg) // Convert most to arrays.
						if(isset($_arg) && $_key !== '+descendants') $_arg = (array)$_arg;
					unset($_key, $_arg); // Housekeeping.

					$user_passtags = $passtag_ids = array(); // Initialize user passtag objects & passtag IDs.

					foreach($this->passtags() as $_user_passtag) // Collect those w/ matching criteria.
						{
							if(isset($args['id_or_access_key'])) // Filter by ID or access key?
								if(!in_array($this::all, $args['id_or_access_key'], TRUE)) // All?
									if(!in_array($_user_passtag->ID, $args['id_or_access_key'], TRUE))
										if(!in_array($_user_passtag->access_key, $args['id_or_access_key'], TRUE))
											continue; // Continue searching user passtags.

							if(isset($args['passtag_id_or_name'])) // Filter by passtag ID or name?
								if(!in_array($this::all, $args['passtag_id_or_name'], TRUE)) // All passtags?
									if(!in_array($_user_passtag->passtag_id, $args['passtag_id_or_name'], TRUE))
										if(!in_array($_user_passtag->name, $args['passtag_id_or_name'], TRUE))
											continue; // Continue searching user passtags.

							if(isset($args['order_session_id'])) // Filter by order session ID?
								if(!in_array($this::all, $args['order_session_id'], TRUE)) // All order sessions?
									if(!in_array($_user_passtag->order_session_id, $args['order_session_id'], TRUE))
										continue; // Continue searching user passtags.

							if(isset($args['transaction_id'])) // Filter by transaction ID?
								if(!in_array($this::all, $args['transaction_id'], TRUE)) // All transactions?
									if(!in_array($_user_passtag->transaction_id, $args['transaction_id'], TRUE))
										continue; // Continue searching user passtags.

							// If we get here it's a match & should be included in the results.

							$user_passtags[$_user_passtag->ID]       = $_user_passtag;
							$passtag_ids[$_user_passtag->passtag_id] = $_user_passtag->passtag_id;
						}
					unset($_user_passtag); // Housekeeping.

					if($args['+descendants'] && $user_passtags && $passtag_ids)
						{
							$_descendants = $this->©passtags->descendants_of($passtag_ids);

							foreach($this->passtags() as $_user_passtag)
								if(in_array($_user_passtag->passtag_id, $_descendants, TRUE))
									{
										$user_passtags[$_user_passtag->ID]       = $_user_passtag;
										$passtag_ids[$_user_passtag->passtag_id] = $_user_passtag->passtag_id;
									}
							unset($_descendants, $_user_passtag); // Housekeeping.
						}
					return $user_passtags; // Array of user passtag objects (e.g. search results).
				}

			/**
			 * Gets user passtags, in chronological order.
			 *
			 * @param boolean $all Optional. Defaults to a FALSE value.
			 *    If TRUE, we return ALL user passtag objects/references (e.g. `by_id`, `by_access_key`, etc).
			 *    By default, we simply return an array of all user passtag objects (by ID).
			 *
			 * @return array Array of user passtags for this user, in chronological order.
			 */
			public function passtags($all = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					if(isset($this->cache[__FUNCTION__])) goto finale;

					$this->cache[__FUNCTION__] = array(); // Initialize.

					if($this->has_id()) // We can search by user ID (when possible).
						$where['user'][] = // Ideally this user will have an ID we can search by.
							"(`user_passtags`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."'".
							" AND `user_passtags`.`user_id` IS NOT NULL".
							" AND `user_passtags`.`user_id` > '0')";

					if($this->has_access_keys()) // Also check access keys (always).
						$where['user'][] = // Even if they have an ID. We ALWAYS check access keys (if possible).
							"(`user_passtags`.`access_key` IN(".$this->©db_utils->comma_quotify($this->access_keys()).")".
							" AND `user_passtags`.`access_key` IS NOT NULL".
							" AND `user_passtags`.`access_key` != '')";

					if(empty($where['user'])) return $this->cache[__FUNCTION__]; // Not possible.

					$where['user'] = '('.implode(' OR ', $where['user']).')';

					$query = // Including parent properties.
						"SELECT".
						" `passtags`.`name` AS `name`,".
						" `passtags`.`label` AS `label`,".
						" `passtags`.`description` AS `description`,".
						" `passtags`.`shareable_limit` AS `shareable_limit`,".

						" `passtags`.`uses_limit` AS `uses_limit`,".
						" `passtags`.`uses_limit_term` AS `uses_limit_term`,".
						" `passtags`.`uses_limit_recurs` AS `uses_limit_recurs`,".
						" `passtags`.`uses_limit_rolls_over` AS `uses_limit_rolls_over`,".
						" `passtags`.`uses_limit_unique` AS `uses_limit_unique`,".

						" `passtags`.`ips_limit` AS `ips_limit`,".
						" `passtags`.`ips_limit_term` AS `ips_limit_term`,".
						" `passtags`.`ips_limit_recurs` AS `ips_limit_recurs`,".
						" `passtags`.`ips_limit_rolls_over` AS `ips_limit_rolls_over`,".

						" `user_passtags`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtags'))."` AS `passtags`".

						" WHERE".
						" ".$where['user']. // By ID (or access keys).
						" AND `user_passtags`.`passtag_id` = `passtags`.`ID`".

						" AND `passtags`.`name` IS NOT NULL".
						" AND `passtags`.`name` != ''".

						" AND `user_passtags`.`passtag_id` IS NOT NULL".
						" AND `user_passtags`.`passtag_id` > '0'".

						" ORDER BY". // Original time.
						" `user_passtags`.`time_created` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$_result->descendants = maybe_unserialize($_result->descendants);

									$this->cache[__FUNCTION__]['by_id'][$_result->ID]                                                                                                      = $_result;
									$this->cache[__FUNCTION__]['by_access_key'][$_result->access_key]                                                                                      =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_passtag_id'][$_result->passtag_id][$_result->ID]                                                                        =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_passtag_name'][$_result->name][$_result->ID]                                                                            =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_order_session_id'][(integer)$_result->order_session_id][$_result->ID]                                                   =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_transaction_id'][(integer)$_result->transaction_id][$_result->ID]                                                       =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
									$this->cache[__FUNCTION__]['by_order_session_id_transaction_id'][(integer)$_result->order_session_id][(integer)$_result->transaction_id][$_result->ID] =& $this->cache[__FUNCTION__]['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					finale: // Target point. Finale for passtag return value.

					if($all) return $this->cache[__FUNCTION__]; // ALL passtag references?

					return (isset($this->cache[__FUNCTION__]['by_id'])) ? $this->cache[__FUNCTION__]['by_id'] : array();
				}

			/**
			 * All user passtag IDs.
			 *
			 * @return array All user passtag IDs.
			 */
			public function passtag_ids()
				{
					$passtags = $this->passtags(TRUE);

					if(isset($passtags['by_id']))
						return array_keys($passtags['by_id']);

					return array();
				}

			/**
			 * All user passtag (passtag) IDs.
			 *
			 * @return array All user passtag (passtag) IDs.
			 */
			public function passtag__ids()
				{
					$passtags = $this->passtags(TRUE);

					if(isset($passtags['by_passtag_id']))
						return array_keys($passtags['by_passtag_id']);

					return array();
				}

			/**
			 * All user passtag names.
			 *
			 * @return array All user passtag names.
			 */
			public function passtag_names()
				{
					$passtags = $this->passtags(TRUE);

					if(isset($passtags['by_passtag_name']))
						return array_keys($passtags['by_passtag_name']);

					return array();
				}

			/**
			 * Gets active passtags, in chronological order.
			 *
			 * @return array Array of active user passtags for this user, in chronological order.
			 */
			public function active_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if($_passtag->status === 'active')
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inactive passtags, in chronological order.
			 *
			 * @return array Array of inactive user passtags for this user, in chronological order.
			 */
			public function inactive_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if($_passtag->status === 'inactive')
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inactive/deleted (NOT active) passtags, in chronological order.
			 *
			 * @return array Array of inactive/deleted user passtags for this user, in chronological order.
			 */
			public function passtags_not_active()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if($_passtag->status !== 'active')
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets deleted passtags, in chronological order.
			 *
			 * @return array Array of deleted user passtags for this user, in chronological order.
			 */
			public function deleted_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if($_passtag->status === 'deleted')
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets accessible passtags, in chronological order.
			 *
			 * @return array Array of accessible user passtags for this user, in chronological order.
			 */
			public function accessible_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->active_passtags() as $_passtag)
						if($this->passtag_is_within_time_starts_stops($_passtag)
						   && $this->passtag_is_within_uses_limit($_passtag)
						   && $this->passtag_is_within_ips_limit($_passtag)
						) $this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inaccessible passtags, in chronological order.
			 *
			 * @return array Array of inaccessible user passtags for this user, in chronological order.
			 */
			public function inaccessible_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if($_passtag->status !== 'deleted' // Ignore.
						   && ($_passtag->status === 'inactive' // Inactive?
						       || !$this->passtag_is_within_time_starts_stops($_passtag)
						       || !$this->passtag_is_within_uses_limit($_passtag)
						       || !$this->passtag_is_within_ips_limit($_passtag))
						) $this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inaccessible/expired passtags, in chronological order.
			 *
			 * @return array Array of inaccessible/expired user passtags for this user; in chronological order.
			 */
			public function inaccessible_expired_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$time = time(); // The current time.

					$this->cache[__FUNCTION__] = array();

					foreach($this->inaccessible_passtags() as $_passtag)
						if($_passtag->time_starts <= $time // Time started already?
						   && !$this->passtag_is_within_time_starts_stops($_passtag)
						) $this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inaccessible/used passtags, in chronological order.
			 *
			 * @return array Array of inaccessible/used user passtags for this user, in chronological order.
			 */
			public function inaccessible_used_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->inaccessible_passtags() as $_passtag)
						if(!$this->passtag_is_within_uses_limit($_passtag))
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets inaccessible/iped passtags, in chronological order.
			 *
			 * @return array Array of inaccessible/iped user passtags for this user, in chronological order.
			 */
			public function inaccessible_iped_passtags()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->inaccessible_passtags() as $_passtag)
						if(!$this->passtag_is_within_ips_limit($_passtag))
							$this->cache[__FUNCTION__][$_passtag->ID] = $_passtag;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Checks if a passtag is within time starts/stops.
			 *
			 * @param object $passtag A user passtag object, with properties returned by ``$this->passtags()``.
			 *
			 * @return boolean TRUE if the passtag is within time starts/stops; else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function passtag_is_within_time_starts_stops($passtag)
				{
					$this->check_arg_types('object', func_get_args());

					if(isset($this->cache[__FUNCTION__][$passtag->ID]))
						return $this->cache[__FUNCTION__][$passtag->ID];

					$time = time(); // Current time (used in comparisons below).

					$this->cache[__FUNCTION__][$passtag->ID] = FALSE;

					if(isset($passtag->eot_time_stops)) $time_stops = $passtag->eot_time_stops;
					else // We use the `time_stops` property (default behavior).
						$time_stops = $passtag->time_stops;

					if($passtag->time_starts <= $time && ($time_stops < 0 || $time_stops > $time))
						$this->cache[__FUNCTION__][$passtag->ID] = TRUE;

					return $this->cache[__FUNCTION__][$passtag->ID];
				}

			/**
			 * Checks if passtag uses are within a possible uses limitation.
			 *
			 * @param object $passtag A user passtag object, with properties returned by {@link passtags()}.
			 *
			 * @param string $return Optional. Defaults to {@link fw_constants::boolean} (recommended).
			 *    However, to get more information set this to {@link fw_constants::array_a}.
			 *
			 * @return boolean TRUE if passtag uses are within a possible uses limitation.
			 *    Or, an associative array. See ``$return`` parameter for further details.
			 *
			 * @note The number of uses found by this method, is compared to ``$passtag->uses_limit``.
			 *
			 *    • In cases where there IS a uses limit, but there is NO term limitation,
			 *       the uses limit should be set as a total number of uses allowed (in a lifetime).
			 *
			 *    • In cases where there is NO uses limit at all, this method will always return TRUE (as it should).
			 *
			 * @note This method will automatically exclude log entries where the `counts` column is <= `0`.
			 *    In other words, we only count uses that actually matter (e.g. uses that actually count).
			 *
			 * @note If ``$passtag->uses_limit_unique`` is enabled (on by default); we only count unique uses.
			 *    In other words, we do NOT count repeat uses of the same underlying content (e.g. accessing a post twice, counts only ONE time).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function passtag_is_within_uses_limit($passtag, $return = self::boolean)
				{
					$this->check_arg_types('object', 'string:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$return][$passtag->ID]))
						return $this->cache[__FUNCTION__][$return][$passtag->ID];

					$this->cache[__FUNCTION__][$this::boolean][$passtag->ID] = FALSE; // Default value.
					$this->cache[__FUNCTION__][$this::array_a][$passtag->ID] = // Plus default array values.
						array('boolean'          => FALSE, 'thus_far' => PHP_INT_MAX, 'max' => 0, 'limit' => $passtag->uses_limit,
						      'limit_term'       => $passtag->uses_limit_term, 'limit_recurs' => $passtag->uses_limit_recurs,
						      'limit_rolls_over' => $passtag->uses_limit_rolls_over, 'limit_unique' => $passtag->uses_limit_unique);

					if($passtag->uses_limit < 0) // There is no uses limit?
						{
							$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = TRUE;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = TRUE;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = 0;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = PHP_INT_MAX;
						}
					else if($passtag->uses_limit_term <= 0) // No term?
						{
							$query =
								"SELECT".
								" `user_passtag_log`.`ID`".

								" FROM".
								" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

								" WHERE".
								" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

								" AND `user_passtag_log`.`counts` IS NOT NULL".
								" AND `user_passtag_log`.`counts` > '0'".

								(($passtag->uses_limit_unique) ? " GROUP BY `user_passtag_log`.`identifier`" : '');

							$uses_thus_far = $this->©db_utils->calc_found_rows($query);

							$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($uses_thus_far < $passtag->uses_limit);
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($uses_thus_far < $passtag->uses_limit);
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $uses_thus_far;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->uses_limit;
						}
					else // There IS a term limitation for this passtag. ~ Run time calculations.
						{
							if($passtag->uses_limit_recurs < 0) // Yes, recurs indefinitely.
								{
									$time = time(); // Current time.

									$time_from                     = $time - $passtag->uses_limit_term;
									$time_to                       = $time_from + $passtag->uses_limit_term;
									$max_time_to                   = PHP_INT_MAX; // Indefinite.
									$uses_limit_terms_have_expired = FALSE; // Indefinite.

									$_terms_completed_thus_far = 0;
									$_term_from                = $passtag->time_starts;
									$_term_to                  = $_term_from + $passtag->uses_limit_term;

									while($time > $_term_to)
										{
											$_term_from = $_term_from + $passtag->uses_limit_term;
											$_term_to   = $_term_from + $passtag->uses_limit_term;
											$_terms_completed_thus_far++;
										}
									$max_allowed_uses_thus_far = ($passtag->uses_limit * $_terms_completed_thus_far) + $passtag->uses_limit;

									unset($_term_from, $_term_to, $_terms_completed_thus_far);
								}
							else if($passtag->uses_limit_recurs > 0) // Yes, recurs X times.
								{
									$time = time(); // Current time.

									$time_from                     = $passtag->time_starts;
									$time_to                       = $time_from + $passtag->uses_limit_term;
									$max_time_to                   = $time_from + ($passtag->uses_limit_term * $passtag->uses_limit_recurs);
									$uses_limit_terms_have_expired = ($time >= $max_time_to);

									while($time > $time_to)
										{
											$time_to   = $time_from + $passtag->uses_limit_term;
											$time_from = $time_from + $passtag->uses_limit_term;
											// This MAY go beyond the number of allowed recurrences (which IS the correct behavior).
											// However, because of this... we should always check if ``$uses_limit_terms_have_expired``.
											// Also watch out for this same issue, when calculating ``$max_allowed_uses_thus_far``.
										}
									$_terms_completed_thus_far = 0;
									$_term_from                = $passtag->time_starts;
									$_term_to                  = $_term_from + $passtag->uses_limit_term;

									while($time > $_term_to)
										{
											$_term_from = $_term_from + $passtag->uses_limit_term;
											$_term_to   = $_term_from + $passtag->uses_limit_term;
											$_terms_completed_thus_far++;
										}
									$max_allowed_uses_thus_far = ($passtag->uses_limit * $_terms_completed_thus_far) + $passtag->uses_limit;
									$max_allowed_uses_thus_far = min( // Should NEVER exceed the maximum number of allowed recurrences.
										$max_allowed_uses_thus_far, $passtag->uses_limit * $passtag->uses_limit_recurs
									);
									unset($_term_from, $_term_to, $_terms_completed_thus_far);
								}
							else // No, the uses limit does NOT recur. ONE term only.
								{
									$time = time(); // Current time.

									$time_from                     = $passtag->time_starts;
									$time_to                       = $time_from + $passtag->uses_limit_term;
									$max_time_to                   = $time_from + $passtag->uses_limit_term;
									$uses_limit_terms_have_expired = ($time >= $max_time_to);

									$max_allowed_uses_thus_far = $passtag->uses_limit; // Only one term, so this is easy.
								}
							if($uses_limit_terms_have_expired) // First check if ``$uses_limit_terms_have_expired``.
								{
									if($passtag->uses_limit_rolls_over < 0) // A value of `-1` (or lower).
										{
											$query =
												"SELECT".
												" `user_passtag_log`.`ID`".

												" FROM".
												" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

												" WHERE".
												" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

												" AND `user_passtag_log`.`counts` IS NOT NULL".
												" AND `user_passtag_log`.`counts` > '0'".

												(($passtag->uses_limit_unique) ? " GROUP BY `user_passtag_log`.`identifier`" : '');

											$uses_thus_far = $this->©db_utils->calc_found_rows($query);

											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($uses_thus_far < $max_allowed_uses_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($uses_thus_far < $max_allowed_uses_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $uses_thus_far;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_uses_thus_far;
										}
									else // They do NOT rollover into terms following even the last term.
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = PHP_INT_MAX;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_uses_thus_far;
										}
								}
							else // Let's query ``$time_from`` through ``$time_to``.
								{
									$query =
										"SELECT".
										" `user_passtag_log`.`ID`".

										" FROM".
										" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

										" WHERE".
										" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".
										" AND (`user_passtag_log`.`time` BETWEEN '".$this->©string->esc_sql((string)$time_from)."' AND '".$this->©string->esc_sql((string)$time_to)."')".

										" AND `user_passtag_log`.`counts` IS NOT NULL".
										" AND `user_passtag_log`.`counts` > '0'".

										(($passtag->uses_limit_unique) ? " GROUP BY `user_passtag_log`.`identifier`" : '');

									$uses_in_term = $this->©db_utils->calc_found_rows($query);

									if($uses_in_term < $passtag->uses_limit)
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = TRUE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = TRUE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $uses_in_term;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->uses_limit;
										}
									else if($passtag->uses_limit_rolls_over <> 0)
										{
											$query =
												"SELECT".
												" `user_passtag_log`.`ID`".

												" FROM".
												" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

												" WHERE".
												" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

												" AND `user_passtag_log`.`counts` IS NOT NULL".
												" AND `user_passtag_log`.`counts` > '0'".

												(($passtag->uses_limit_unique) ? " GROUP BY `user_passtag_log`.`identifier`" : '');

											$uses_thus_far = $this->©db_utils->calc_found_rows($query);

											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($uses_thus_far < $max_allowed_uses_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($uses_thus_far < $max_allowed_uses_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $uses_thus_far;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_uses_thus_far;
										}
									else // They exceeded the limit in this term; and limit does NOT rollover.
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $uses_in_term;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->uses_limit;
										}
								}
						}
					return $this->cache[__FUNCTION__][$return][$passtag->ID];
				}

			/**
			 * Checks if passtag IPs are within a possible IPs limitation.
			 *
			 * @param object $passtag A user passtag object, with properties returned by {@link passtags()}.
			 *
			 * @param string $return Optional. Defaults to {@link fw_constants::boolean} (recommended).
			 *    However, to get more information set this to {@link fw_constants::array_a}.
			 *
			 * @return boolean|array TRUE if passtag IPs are within a possible IPs limitation.
			 *    Or, an associative array. See ``$return`` parameter for further details.
			 *
			 * @note The number of IPs found by this method, is compared to ``$passtag->ips_limit``.
			 *
			 *    • In cases where there IS an IPs limit, but there is NO term limitation,
			 *       the IPs limit should be set as a total number of IPs allowed (in a lifetime).
			 *
			 *    • In cases where there is NO IPs limit at all, this method will always return TRUE (as it should).
			 *
			 * @note This method will automatically exclude log entries where the `counts` column is <= `0`.
			 *    In other words, we only count IPs that actually matter (e.g. IPs that actually count).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function passtag_is_within_ips_limit($passtag, $return = self::boolean)
				{
					$this->check_arg_types('object', 'string:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$return][$passtag->ID]))
						return $this->cache[__FUNCTION__][$return][$passtag->ID];

					$this->cache[__FUNCTION__][$this::boolean][$passtag->ID] = FALSE; // Default value.
					$this->cache[__FUNCTION__][$this::array_a][$passtag->ID] = // Plus default array values.
						array('boolean'          => FALSE, 'thus_far' => PHP_INT_MAX, 'max' => 0, 'limit' => $passtag->ips_limit,
						      'limit_term'       => $passtag->ips_limit_term, 'limit_recurs' => $passtag->ips_limit_recurs,
						      'limit_rolls_over' => $passtag->ips_limit_rolls_over);

					if($passtag->ips_limit < 0) // There is no IPs limit?
						{
							$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = TRUE;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = TRUE;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = 0;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = PHP_INT_MAX;
						}
					else if($passtag->ips_limit_term <= 0) // No term?
						{
							$query =
								"SELECT".
								" DISTINCT `user_passtag_log`.`ip`".

								" FROM".
								" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

								" WHERE".
								" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

								" AND `user_passtag_log`.`counts` IS NOT NULL".
								" AND `user_passtag_log`.`counts` > '0'";

							$ips_thus_far = $this->©db_utils->calc_found_rows($query);

							$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($ips_thus_far < $passtag->ips_limit);
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($ips_thus_far < $passtag->ips_limit);
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $ips_thus_far;
							$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->ips_limit;
						}
					else // There IS a term limitation for this passtag. ~ Run time calculations.
						{
							if($passtag->ips_limit_recurs < 0) // Yes, recurs indefinitely.
								{
									$time = time(); // Current time.

									$time_from                    = $time - $passtag->ips_limit_term;
									$time_to                      = $time_from + $passtag->ips_limit_term;
									$max_time_to                  = PHP_INT_MAX; // Indefinite.
									$ips_limit_terms_have_expired = FALSE; // Indefinite.

									$_terms_completed_thus_far = 0;
									$_term_from                = $passtag->time_starts;
									$_term_to                  = $_term_from + $passtag->ips_limit_term;

									while($time > $_term_to)
										{
											$_term_from = $_term_from + $passtag->ips_limit_term;
											$_term_to   = $_term_from + $passtag->ips_limit_term;
											$_terms_completed_thus_far++;
										}
									$max_allowed_ips_thus_far = ($passtag->ips_limit * $_terms_completed_thus_far) + $passtag->ips_limit;

									unset($_term_from, $_term_to, $_terms_completed_thus_far);
								}
							else if($passtag->ips_limit_recurs > 0) // Yes, recurs X times.
								{
									$time = time(); // Current time.

									$time_from                    = $passtag->time_starts;
									$time_to                      = $time_from + $passtag->ips_limit_term;
									$max_time_to                  = $time_from + ($passtag->ips_limit_term * $passtag->ips_limit_recurs);
									$ips_limit_terms_have_expired = ($time >= $max_time_to);

									while($time > $time_to)
										{
											$time_to   = $time_from + $passtag->ips_limit_term;
											$time_from = $time_from + $passtag->ips_limit_term;
											// This MAY go beyond the number of allowed recurrences (which IS the correct behavior).
											// However, because of this... we should always check if ``$ips_limit_terms_have_expired``.
											// Also watch out for this same issue, when calculating ``$max_allowed_ips_thus_far``.
										}
									$_terms_completed_thus_far = 0;
									$_term_from                = $passtag->time_starts;
									$_term_to                  = $_term_from + $passtag->ips_limit_term;

									while($time > $_term_to)
										{
											$_term_from = $_term_from + $passtag->ips_limit_term;
											$_term_to   = $_term_from + $passtag->ips_limit_term;
											$_terms_completed_thus_far++;
										}
									$max_allowed_ips_thus_far = ($passtag->ips_limit * $_terms_completed_thus_far) + $passtag->ips_limit;
									$max_allowed_ips_thus_far = min( // Should NEVER exceed the maximum number of allowed recurrences.
										$max_allowed_ips_thus_far, $passtag->ips_limit * $passtag->ips_limit_recurs
									);
									unset($_term_from, $_term_to, $_terms_completed_thus_far);
								}
							else // No, the IPs limit does NOT recur. ONE term only.
								{
									$time = time(); // Current time.

									$time_from                    = $passtag->time_starts;
									$time_to                      = $time_from + $passtag->ips_limit_term;
									$max_time_to                  = $time_from + $passtag->ips_limit_term;
									$ips_limit_terms_have_expired = ($time >= $max_time_to);

									$max_allowed_ips_thus_far = $passtag->ips_limit; // Only one term, so this is easy.
								}
							if($ips_limit_terms_have_expired) // First check if ``$ips_limit_terms_have_expired``.
								{
									if($passtag->ips_limit_rolls_over < 0) // A value of `-1` (or lower).
										{
											$query =
												"SELECT".
												" DISTINCT `user_passtag_log`.`ip`".

												" FROM".
												" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

												" WHERE".
												" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

												" AND `user_passtag_log`.`counts` IS NOT NULL".
												" AND `user_passtag_log`.`counts` > '0'";

											$ips_thus_far = $this->©db_utils->calc_found_rows($query);

											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($ips_thus_far < $max_allowed_ips_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($ips_thus_far < $max_allowed_ips_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $ips_thus_far;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_ips_thus_far;
										}
									else // They do NOT rollover into terms following even the last term.
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = PHP_INT_MAX;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_ips_thus_far;
										}
								}
							else // Let's query ``$time_from`` through ``$time_to``.
								{
									$query =
										"SELECT".
										" DISTINCT `user_passtag_log`.`ip`".

										" FROM".
										" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

										" WHERE".
										" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".
										" AND (`user_passtag_log`.`time` BETWEEN '".$this->©string->esc_sql((string)$time_from)."' AND '".$this->©string->esc_sql((string)$time_to)."')".

										" AND `user_passtag_log`.`counts` IS NOT NULL".
										" AND `user_passtag_log`.`counts` > '0'";

									$ips_in_term = $this->©db_utils->calc_found_rows($query);

									if($ips_in_term < $passtag->ips_limit)
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = TRUE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = TRUE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $ips_in_term;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->ips_limit;
										}
									else if($passtag->ips_limit_rolls_over <> 0)
										{
											$query =
												"SELECT".
												" DISTINCT `user_passtag_log`.`ip`".

												" FROM".
												" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".

												" WHERE".
												" `user_passtag_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$passtag->ID)."'".

												" AND `user_passtag_log`.`counts` IS NOT NULL".
												" AND `user_passtag_log`.`counts` > '0'";

											$ips_thus_far = $this->©db_utils->calc_found_rows($query);

											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = ($ips_thus_far < $max_allowed_ips_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = ($ips_thus_far < $max_allowed_ips_thus_far);
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $ips_thus_far;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $max_allowed_ips_thus_far;
										}
									else // They exceeded the limit in this term; and limit does NOT rollover.
										{
											$this->cache[__FUNCTION__][$this::boolean][$passtag->ID]             = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['boolean']  = FALSE;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['thus_far'] = $ips_in_term;
											$this->cache[__FUNCTION__][$this::array_a][$passtag->ID]['max']      = $passtag->ips_limit;
										}
								}
						}
					return $this->cache[__FUNCTION__][$return][$passtag->ID];
				}

			/**
			 * Logs accessible passtag uses.
			 *
			 * @note This routine needs to remain HIGHLY OPTIMIZED to prevent slow response times.
			 *    This method is responsible for logging ALL uses of content made available through passtags.
			 *
			 * @param array   $passtag_ids_granting_access Array (NOT empty); of all passtag IDs granting access to ``$identifier``.
			 *    Note, this array should contain passtag `ID` values from the `passtags` table, and NOT the `user_passtag_id`.
			 *    This array is NOT easy to accurately formulate. It requires a deep analysis of all restriction checks.
			 *    See {@link restrictions::check_type()} for an example of how to build this array.
			 *
			 * @param string  $identifier Content identifier, such as a post type/ID or file name.
			 *    Any content accessed via passtags should be assigned a unique identifier so it is possible to
			 *    exclude repeat uses of the same content when we inspect logs.
			 *
			 * @param boolean $counts Optional. This defaults to a TRUE value.
			 *    If FALSE, we will still log the identifier, but it will NOT count against any limits.
			 *
			 * @note By convention, an ``$identifier`` is prefixed with a content type (e.g. a restriction type);
			 *    followed by a pipe `|` symbol before finally identifying itself at the end of the string.
			 *    Examples: `type::post|post::123`, `uri::/hello-world/?this=that`.
			 *    See {@link restrictions} class for more examples.
			 *
			 * @note The max length for ``$identifier`` is `255` chars. If an ``$identifier`` is longer than `255` chars;
			 *    we convert that ``$identifier`` automatically to an MD5 hash instead; to maintain the integrity of our logs.
			 *    For instance, this can happen when logging URIs that are abnormally long (e.g. they contain a long query string).
			 *    ~ However, if an ``$identifier`` is converted to an MD5 hash, we DO attempt to preserve any prefix it may have.
			 *
			 * @return integer The number of new log entries created by this routine.
			 *
			 * @note This routine INTENTIONALLY does NOT refresh any user data upon creating new log entries.
			 *    Doing so would slow down important routines; and this is really NOT important enough to warrant an
			 *    object cache refresh each & every time time content is accessed in one way or another.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$passtag_ids_granting_access`` or ``$identifier`` are empty.
			 */
			public function log_accessible_passtag_uses($passtag_ids_granting_access, $identifier, $counts = TRUE)
				{
					$this->check_arg_types('array:!empty', 'string:!empty', 'boolean', func_get_args());

					$counts                      = (integer)$counts;
					$log_entry_values            = array(); // Initialize.
					$time                        = time(); // Current time.
					$passtag_ids_granting_access = array_unique($passtag_ids_granting_access);

					if(strlen($identifier) > 255) // See notes above regarding identifiers.
						{
							if(!($_prefix = (string)strstr($identifier, '|', TRUE)))
								$_prefix = 'md5'; // Default prefix.
							$identifier = $_prefix.'|md5::'.md5($identifier);
							unset($_prefix); // Housekeeping.
						}
					foreach($this->accessible_passtags() as $_user_passtag)
						if(in_array($_user_passtag->passtag_id, $passtag_ids_granting_access, TRUE))
							{
								$log_entry_values[] = "(".$this->©db_utils->comma_quotify(array($_user_passtag->ID, $identifier, $time, $this->ip, $counts)).")";
								break; // Only ONE "use". If the user maxes this accessible user passtag out; it will become inaccessible.
								// As each user passtag is maxed out, it drops from the list of accessible passtags automatically.
								// IMPORTANT TO NOTE: accessible passtags are iterated here in chronological order.
								// Thus, the oldest ones are used up first; which IS the intended behavior.
							}
					unset($_user_passtag); // Housekeeping.

					if(!$log_entry_values) return 0; // No log entries.

					$query = // Prepares `meta` table name/value insertions.
						"INSERT INTO `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."`".
						"(".$this->©db_utils->comma_tickify(array('user_passtag_id', 'identifier', 'time', 'ip', 'counts')).")";

					$query .= " VALUES".implode(',', $log_entry_values);

					return (integer)$this->©db->query($query);
				}

			/**
			 * Alias for ``$this->has_passtags()``.
			 *
			 * @return boolean TRUE if the user HAS the passtag(s).
			 */
			public function has_passtag()
				{
					return call_user_func_array(array($this, 'has_passtags'), func_get_args());
				}

			/**
			 * Does this user HAVE the passtag(s)?
			 *
			 * @param null|string|integer|array|object $passtags Optional. Defaults to a NULL value.
			 *
			 *    • A NULL value indicates that we don't care which passtag(s) they HAVE,
			 *       so long as they HAVE at least one passtag (of ANY kind).
			 *
			 *    • A string indicates that we want to check if the user HAS a particular passtag, by `name`.
			 *       This is matched against the `name` field in the `passtags` table.
			 *
			 *    • An integer indicates that we want to check if the user HAS a particular passtag, by passtag `ID`.
			 *       Note, this is the actual passtag `ID` in the `passtags` table, and NOT the `user_passtag_id`.
			 *
			 *    • An array/object may contain any combination of the above, including `null|string|integer|array|object` values.
			 *       The values are scanned deeply, and if the user HAS ALL of the passtags, this method returns TRUE.
			 *       This can be modified by passing the ``$logic`` parameter as `fw_constants::any_logic`.
			 *
			 * @note We do NOT need to query passtag ancestors here, because ALL passtags are given to a user during checkout.
			 *    In other words, we're already looking at ALL passtags the user HAS. Pulling ancestors would be counter-intuitive.
			 *    If you want to know if a user HAS passtag `b`; which comes with passtag `a`; you simply look for passtag `b`.
			 *
			 * @params-variable-length This function accepts a variable-length list of passtag arguments.
			 *    Please be sure ``$logic`` is ALWAYS the last parameter, when/if specified (see details below).
			 *    If ``$between`` is passed, make it the last argument; but only if ``$logic`` is NOT passed in also.
			 *    If they are both passed in, please keep ``$logic`` as the very last parameter at ALL times.
			 *
			 * @param null|string                      $between Optional. Between a specific date range?
			 *    Example: `#between::TIMESTAMP::TIMESTAMP`. Either TIMESTAMP can be `0` to exclude that check.
			 *    These TIMESTAMPS represent a `from` - `to` date range; in that order.
			 *
			 *    • This method compares `from` with the `time_starts` property for the user passtag.
			 *    • This method compares `to` with the `time_stops` property (and/or `eot_time_stops`; if set).
			 *
			 * @param string                           $logic Optional, should be passed with a PHP constant.
			 *    Defaults to constant `fw_constants::all_logic`. By default, the user MUST satisfy ALL passtag requirements.
			 *    If this is set to `fw_constants::any_logic`, we return TRUE if the user passes ANY of the passtag requirements.
			 *
			 * @return boolean TRUE if the user has the passtag(s).
			 *
			 * @throws exception If ``$between`` is passed incorrectly.
			 * @throws exception If ``$logic`` is passed with an incorrect value.
			 */
			public function has_passtags($passtags = NULL, $between = NULL, $logic = self::all_logic)
				{
					if(!($user_passtags = $this->active_passtags()))
						return FALSE; // NO passtags? Save some time here.

					if(!($args = func_get_args()))
						goto check_passtags;

					$logic       = $this::all_logic;
					$logic_types = array($this::all_logic, $this::any_logic);
					$between     = NULL; // Establish defaults.

					if(count($args) > 1 // Possible ``$logic`` in this case?
					   && in_array($this->©array->last($args), $logic_types, TRUE)
					) $logic = array_pop($args); // A ``$logic`` type.

					if(count($args) > 1 // Possible ``$between`` range?
					   && is_string($between = $this->©array->last($args)) && strpos($between, '#between::') === 0
					) $between = array_pop($args); // Date range.

					$passtags = (count($args) > 1) ? $args : $args[0]; // Multiple args remaining?

					if($between && (!($between = explode('::', $between, 3)) || count($between) !== 3))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_between_range', array_merge(get_defined_vars(), array('user' => $this)),
							$this->i18n('Invalid `$between` date range (please use `#between::from::to` with UTC timestamps).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($between))
						);
					if($between) $between = array('from' => (integer)$between[1], 'to' => (integer)$between[2]);

					check_passtags: // Target point. Start analyzing passtags.

					if(!isset($passtags)) return TRUE; // TRUE, at least ONE passtag.

					if(is_array($passtags) || is_object($passtags)) // Checks array/object values.
						{
							if(!$passtags || (is_object($passtags) && !$this->©object->is_not_ass_empty($passtags)))
								return FALSE; // Catch empty arrays/objects. Only NULL indicates *any* passtag.

							switch($logic) // Handle this based on logic type.
							{
								case $this::all_logic: // Satisfy all?

										foreach($passtags as $_passtags)
											if(!$this->has_passtags($_passtags, $logic))
												return FALSE; // Fail; MUST satisfy all :-)
										unset($_passtags); // Housekeeping.

										return TRUE; // Passed on all of these.

								case $this::any_logic: // Satisfy any?

										foreach($passtags as $_passtags)
											if($this->has_passtags($_passtags, $logic))
												return TRUE; // Success; satisfy ANY :-)
										unset($_passtags); // Housekeeping.

										return FALSE; // Failed on all of these.

								default: // Default case handler.
									throw $this->©exception( // This should NOT happen.
										$this->method(__FUNCTION__).'#invalid_logic_type', array_merge(get_defined_vars(), array('user' => $this)),
										$this->i18n('Invalid `$logic` type (please use a core constant for this value).').
										' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($logic))
									);
							}
						}
					if($this->©integer->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag)
								if($_user_passtag->passtag_id === ($passtag_id = $passtags))
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					if($this->©string->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag) // NOT case sensitive.
								if(strcasecmp($_user_passtag->name, ($passtag_name = $passtags)) === 0)
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					return FALSE; // Default value.
				}

			/**
			 * Alias for ``$this->has_passtags(..., fw_constants::any_logic)``.
			 *
			 * @return boolean TRUE if the user HAS any of the passtags.
			 */
			public function has_any_passtag()
				{
					$args = func_get_args();

					if(isset($args[0])) // Only if we DO have arguments.
						// Note: if we have NO arguments, it's already an `any` check.
						$args[] = $this::any_logic;

					return call_user_func_array(array($this, 'has_passtags'), $args);
				}

			/**
			 * Alias for ``$this->did_have_passtags()``.
			 *
			 * @return boolean TRUE if the user HAD the passtag(s).
			 */
			public function had_passtag()
				{
					return call_user_func_array(array($this, 'did_have_passtags'), func_get_args());
				}

			/**
			 * Alias for ``$this->did_have_passtags()``.
			 *
			 * @return boolean TRUE if the user HAD the passtag(s).
			 */
			public function had_passtags()
				{
					return call_user_func_array(array($this, 'did_have_passtags'), func_get_args());
				}

			/**
			 * Alias for ``$this->did_have_passtags()``.
			 *
			 * @return boolean TRUE if the user HAD the passtag(s).
			 */
			public function did_have_passtag()
				{
					return call_user_func_array(array($this, 'did_have_passtags'), func_get_args());
				}

			/**
			 * Has this user ever HAD the passtag(s)?
			 *
			 * @param null|string|integer|array|object $passtags Optional. Defaults to a NULL value.
			 *
			 *    • A NULL value indicates that we don't care which passtag(s) they HAD,
			 *       so long as they HAD at least one passtag (of ANY kind).
			 *
			 *    • A string indicates that we want to check if the user HAD a particular passtag, by `name`.
			 *       This is matched against the `name` field in the `passtags` table.
			 *
			 *    • An integer indicates that we want to check if the user HAD a particular passtag, by passtag `ID`.
			 *       Note, this is the actual passtag `ID` in the `passtags` table, and NOT the `user_passtag_id`.
			 *
			 *    • An array/object may contain any combination of the above, including `null|string|integer|array|object` values.
			 *       The values are scanned deeply, and if the user HAD ALL of the passtags, this method returns TRUE.
			 *       This can be modified by passing the ``$logic`` parameter as `fw_constants::any_logic`.
			 *
			 * @note We do NOT need to query passtag ancestors here, because ALL passtags are given to a user during checkout.
			 *    In other words, we're already looking at ALL passtags the user HAD. Pulling ancestors would be counter-intuitive.
			 *    If you want to know if a user HAD passtag `b`; which came with passtag `a`; you simply look for passtag `b`.
			 *
			 * @params-variable-length This function accepts a variable-length list of passtag arguments.
			 *    Please be sure ``$logic`` is ALWAYS the last parameter, when/if specified (see details below).
			 *    If ``$between`` is passed, make it the last argument; but only if ``$logic`` is NOT passed in also.
			 *    If they are both passed in, please keep ``$logic`` as the very last parameter at ALL times.
			 *
			 * @param null|string                      $between Optional. Between a specific date range?
			 *    Example: `#between::TIMESTAMP::TIMESTAMP`. Either TIMESTAMP can be `0` to exclude that check.
			 *    These TIMESTAMPS represent a `from` - `to` date range; in that order.
			 *
			 *    • This method compares `from` with the `time_created` property for the user passtag.
			 *    • This method compares `to` with the `time_stops` property (and/or `eot_time_stops`; if set).
			 *
			 * @param string                           $logic Optional, should be passed with a PHP constant.
			 *    Defaults to constant `fw_constants::all_logic`. By default, the user MUST satisfy ALL passtag requirements.
			 *    If this is set to `fw_constants::any_logic`, we return TRUE if the user passes ANY of the passtag requirements.
			 *
			 * @return boolean TRUE if the user HAD the passtag(s).
			 *
			 * @throws exception If ``$between`` is passed incorrectly.
			 * @throws exception If ``$logic`` is passed with an incorrect value.
			 */
			public function did_have_passtags($passtags = NULL, $between = NULL, $logic = self::all_logic)
				{
					if(!($user_passtags = $this->passtags_not_active()))
						return FALSE; // NO passtags? Save some time here.

					if(!($args = func_get_args()))
						goto check_passtags;

					$logic       = $this::all_logic;
					$logic_types = array($this::all_logic, $this::any_logic);
					$between     = NULL; // Establish defaults.

					if(count($args) > 1 // Possible ``$logic`` in this case?
					   && in_array($this->©array->last($args), $logic_types, TRUE)
					) $logic = array_pop($args); // A ``$logic`` type.

					if(count($args) > 1 // Possible ``$between`` range?
					   && is_string($between = $this->©array->last($args)) && strpos($between, '#between::') === 0
					) $between = array_pop($args); // Date range.

					$passtags = (count($args) > 1) ? $args : $args[0]; // Multiple args remaining?

					if($between && (!($between = explode('::', $between, 3)) || count($between) !== 3))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_between_range', array_merge(get_defined_vars(), array('user' => $this)),
							$this->i18n('Invalid `$between` date range (please use `#between::from::to` with UTC timestamps).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($between))
						);
					if($between) $between = array('from' => (integer)$between[1], 'to' => (integer)$between[2]);

					check_passtags: // Target point. Start analyzing passtags.

					if(!isset($passtags)) return TRUE; // TRUE, at least ONE passtag.

					if(is_array($passtags) || is_object($passtags)) // Checks array/object values.
						{
							if(!$passtags || (is_object($passtags) && !$this->©object->is_not_ass_empty($passtags)))
								return FALSE; // Catch empty arrays/objects. Only NULL indicates *any* passtag.

							switch($logic) // Handle this based on logic type.
							{
								case $this::all_logic: // Satisfy all?

										foreach($passtags as $_passtags)
											if(!$this->did_have_passtags($_passtags, $logic))
												return FALSE; // Fail; MUST satisfy all :-)
										unset($_passtags); // Housekeeping.

										return TRUE; // Passed on all of these.

								case $this::any_logic: // Satisfy any?

										foreach($passtags as $_passtags)
											if($this->did_have_passtags($_passtags, $logic))
												return TRUE; // Success; satisfy ANY :-)
										unset($_passtags); // Housekeeping.

										return FALSE; // Failed on all of these.

								default: // Default case handler.
									throw $this->©exception( // This should NOT happen.
										$this->method(__FUNCTION__).'#invalid_logic_type', array_merge(get_defined_vars(), array('user' => $this)),
										$this->i18n('Invalid `$logic` type (please use a core constant for this value).').
										' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($logic))
									);
							}
						}
					if($this->©integer->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag)
								if($_user_passtag->passtag_id === ($passtag_id = $passtags))
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_created >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					if($this->©string->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag) // NOT case sensitive.
								if(strcasecmp($_user_passtag->name, ($passtag_name = $passtags)) === 0)
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_created >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					return FALSE; // Default value.
				}

			/**
			 * Alias for ``$this->did_have_passtags(..., fw_constants::any_logic)``.
			 *
			 * @return boolean TRUE if the user HAD any of the passtags.
			 */
			public function did_have_any_passtag()
				{
					$args = func_get_args();

					if(isset($args[0])) // Only if we DO have arguments.
						// Note: if we have NO arguments, it's already an `any` check.
						$args[] = $this::any_logic;

					return call_user_func_array(array($this, 'did_have_passtags'), $args);
				}

			/**
			 * Alias for ``$this->did_have_passtags(..., fw_constants::any_logic)``.
			 *
			 * @return boolean TRUE if the user HAD any of the passtags.
			 */
			public function had_any_passtag()
				{
					$args = func_get_args();

					if(isset($args[0])) // Only if we DO have arguments.
						// Note: if we have NO arguments, it's already an `any` check.
						$args[] = $this::any_logic;

					return call_user_func_array(array($this, 'did_have_passtags'), $args);
				}

			/**
			 * Alias for ``$this->can_access_passtags()``.
			 *
			 * @return boolean TRUE if the user CAN ACCESS the passtag(s).
			 */
			public function can_passtag()
				{
					return call_user_func_array(array($this, 'can_access_passtags'), func_get_args());
				}

			/**
			 * Alias for ``$this->can_access_passtags()``.
			 *
			 * @return boolean TRUE if the user CAN ACCESS the passtag(s).
			 */
			public function can_passtags()
				{
					return call_user_func_array(array($this, 'can_access_passtags'), func_get_args());
				}

			/**
			 * Alias for ``$this->can_access_passtags()``.
			 *
			 * @return boolean TRUE if the user CAN ACCESS the passtag(s).
			 */
			public function can_access_passtag()
				{
					return call_user_func_array(array($this, 'can_access_passtags'), func_get_args());
				}

			/**
			 * CAN this user ACCESS the passtag(s)?
			 *
			 * @param null|string|integer|array|object $passtags Optional. Defaults to a NULL value.
			 *
			 *    • A NULL value indicates that we don't care which passtag(s) they CAN ACCESS,
			 *       so long as they CAN ACCESS at least one passtag (of ANY kind).
			 *
			 *    • A string indicates that we want to check if the user CAN ACCESS a particular passtag, by `name`.
			 *       This is matched against the `name` field in the `passtags` table.
			 *
			 *    • An integer indicates that we want to check if the user CAN ACCESS a particular passtag, by passtag `ID`.
			 *       Note, this is the actual passtag `ID` in the `passtags` table, and NOT the `user_passtag_id`.
			 *
			 *    • An array/object may contain any combination of the above, including `null|string|integer|array|object` values.
			 *       The values are scanned deeply, and if the user CAN ACCESS ALL of the passtags, this method returns TRUE.
			 *       This can be modified to an `OR/any` check, by passing the ``$logic`` parameter as `fw_constants::any_logic`.
			 *
			 * @note We do NOT need to query passtag ancestors here, because ALL passtags are given to a user during checkout.
			 *    In other words, we're already looking at ALL passtags the user CAN ACCESS. Pulling ancestors would be counter-intuitive.
			 *    Every passtag that comes with a parent; IS checked here. So there is no need to query ancestors/descendants.
			 *    If you want to know if a user CAN ACCESS passtag `b`; which comes with passtag `a`; you simply look for passtag `b`.
			 *
			 * @params-variable-length This function accepts a variable-length list of passtag arguments.
			 *    Please be sure ``$logic`` is ALWAYS the last parameter, when/if specified (see details below).
			 *    If ``$between`` is passed, make it the last argument; but only if ``$logic`` is NOT passed in also.
			 *    If they are both passed in, please keep ``$logic`` as the very last parameter at ALL times.
			 *
			 * @param null|string                      $between Optional. Between a specific date range?
			 *    Example: `#between::TIMESTAMP::TIMESTAMP`. Either TIMESTAMP can be `0` to exclude that check.
			 *    These TIMESTAMPS represent a `from` - `to` date range; in that order.
			 *
			 *    • This method compares `from` with the `time_starts` property for the user passtag.
			 *    • This method compares `to` with the `time_stops` property (and/or `eot_time_stops`; if set).
			 *
			 * @param string                           $logic Optional, should be passed with a PHP constant.
			 *    Defaults to constant `fw_constants::all_logic`. By default, the user MUST satisfy ALL passtag requirements.
			 *    If this is set to `fw_constants::any_logic`, we return TRUE if the user passes ANY of the passtag requirements.
			 *
			 * @return boolean TRUE if the user CAN ACCESS the passtag(s).
			 *
			 * @throws exception If ``$between`` is passed incorrectly.
			 * @throws exception If ``$logic`` is passed with an incorrect value.
			 */
			public function can_access_passtags($passtags = NULL, $between = NULL, $logic = self::all_logic)
				{
					if($this->is_super_admin()) return TRUE; // Always.

					if(!($user_passtags = $this->accessible_passtags()))
						return FALSE; // NO passtags? Save some time here.

					if(!($args = func_get_args()))
						goto check_passtags;

					$logic       = $this::all_logic;
					$logic_types = array($this::all_logic, $this::any_logic);
					$between     = NULL; // Establish defaults.

					if(count($args) > 1 // Possible ``$logic`` in this case?
					   && in_array($this->©array->last($args), $logic_types, TRUE)
					) $logic = array_pop($args); // A ``$logic`` type.

					if(count($args) > 1 // Possible ``$between`` range?
					   && is_string($between = $this->©array->last($args)) && strpos($between, '#between::') === 0
					) $between = array_pop($args); // Date range.

					$passtags = (count($args) > 1) ? $args : $args[0]; // Multiple args remaining?

					if($between && (!($between = explode('::', $between, 3)) || count($between) !== 3))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_between_range', array_merge(get_defined_vars(), array('user' => $this)),
							$this->i18n('Invalid `$between` date range (please use `#between::from::to` with UTC timestamps).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($between))
						);
					if($between) $between = array('from' => (integer)$between[1], 'to' => (integer)$between[2]);

					check_passtags: // Target point. Start analyzing passtags.

					if(!isset($passtags)) return TRUE; // TRUE, at least ONE passtag.

					if(is_array($passtags) || is_object($passtags)) // Checks array/object values.
						{
							if(!$passtags || (is_object($passtags) && !$this->©object->is_not_ass_empty($passtags)))
								return FALSE; // Catch empty arrays/objects. Only NULL indicates *any* passtag.

							switch($logic) // Handle this based on logic type.
							{
								case $this::all_logic: // Satisfy all?

										foreach($passtags as $_passtags)
											if(!$this->can_access_passtags($_passtags, $logic))
												return FALSE; // Fail; MUST satisfy all :-)
										unset($_passtags); // Housekeeping.

										return TRUE; // Passed on all of these.

								case $this::any_logic: // Satisfy any?

										foreach($passtags as $_passtags)
											if($this->can_access_passtags($_passtags, $logic))
												return TRUE; // Success; satisfy ANY :-)
										unset($_passtags); // Housekeeping.

										return FALSE; // Failed on all of these.

								default: // Default case handler.
									throw $this->©exception( // This should NOT happen.
										$this->method(__FUNCTION__).'#invalid_logic_type', array_merge(get_defined_vars(), array('user' => $this)),
										$this->i18n('Invalid `$logic` type (please use a core constant for this value).').
										' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($logic))
									);
							}
						}
					if($this->©integer->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag)
								if($_user_passtag->passtag_id === ($passtag_id = $passtags))
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					if($this->©string->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag) // NOT case sensitive.
								if(strcasecmp($_user_passtag->name, ($passtag_name = $passtags)) === 0)
									{
										if(!$between) return TRUE;

										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					return FALSE; // Default value.
				}

			/**
			 * Alias for ``$this->can_access_passtags(..., fw_constants::any_logic)``.
			 *
			 * @return boolean TRUE if the user CAN ACCESS any of the passtags.
			 */
			public function can_access_any_passtag()
				{
					$args = func_get_args();

					if(isset($args[0])) // Only if we DO have arguments.
						// Note: if we have NO arguments, it's already an `any` check.
						$args[] = $this::any_logic;

					return call_user_func_array(array($this, 'can_access_passtags'), $args);
				}

			/**
			 * Alias for {@link will_access_passtags()}.
			 *
			 * @return array {@inheritdoc will_access_passtags()}
			 */
			public function will_access_passtag() // Arguments are NOT listed here.
				{
					return call_user_func_array(array($this, 'will_access_passtags'), func_get_args());
				}

			/**
			 * WILL this user ACCESS the passtag(s)?
			 *
			 * @param null|string|integer|array|object $passtags Optional. Defaults to a NULL value.
			 *
			 *    • A NULL value indicates that we don't care which passtag(s) they WILL ACCESS,
			 *       so long as they WILL ACCESS at least one passtag (of ANY kind).
			 *
			 *    • A string indicates that we want to check if the user WILL ACCESS a particular passtag, by `name`.
			 *       This is matched against the `name` field in the `passtags` table.
			 *
			 *    • An integer indicates that we want to check if the user WILL ACCESS a particular passtag, by passtag `ID`.
			 *       Note, this is the actual passtag `ID` in the `passtags` table, and NOT the `user_passtag_id`.
			 *
			 *    • An array/object may contain any combination of the above, including `null|string|integer|array|object` values.
			 *       The values are scanned deeply, and if the user WILL ACCESS ALL of the passtags, this method returns TRUE.
			 *       This can be modified to an `OR/any` check, by passing the ``$logic`` parameter as `fw_constants::any_logic`.
			 *
			 * @note We do NOT need to query passtag ancestors here, because ALL passtags are given to a user during checkout.
			 *    In other words, we're already looking at ALL passtags the user WILL ACCESS. Pulling ancestors would be counter-intuitive.
			 *    Every passtag that comes with a parent; IS checked here. So there is no need to query ancestors/descendants.
			 *    If you want to know if a user WILL ACCESS passtag `b`; which comes with passtag `a`; you simply look for passtag `b`.
			 *
			 * @params-variable-length This function accepts a variable-length list of passtag arguments.
			 *    Please be sure ``$logic`` is ALWAYS the last parameter, when/if specified (see details below).
			 *    If ``$between`` is passed, make it the last argument; but only if ``$logic`` is NOT passed in also.
			 *    If they are both passed in, please keep ``$logic`` as the very last parameter at ALL times.
			 *
			 * @param null|string                      $between Optional. Between a specific date range?
			 *    Example: `#between::TIMESTAMP::TIMESTAMP`. Either TIMESTAMP can be `0` to exclude that check.
			 *    These TIMESTAMPS represent a `from` - `to` date range; in that order.
			 *
			 *    • This method compares `from` with the `time_starts` property for the user passtag.
			 *       NOTE: This method will NOT return TRUE unless `time_starts` is in the future.
			 *          We're checking if the user WILL ACCESS at some point in the future.
			 *    • This method compares `to` with the `time_stops` property (and/or `eot_time_stops`; if set).
			 *
			 * @param string                           $logic Optional, should be passed with a PHP constant.
			 *    Defaults to constant `fw_constants::all_logic`. By default, the user MUST satisfy ALL passtag requirements.
			 *    If this is set to `fw_constants::any_logic`, we return TRUE if the user passes ANY of the passtag requirements.
			 *
			 * @return boolean TRUE if the user WILL ACCESS the passtag(s); e.g. at a future time.
			 *    Obviously we can't predict the future, but we can determine if the user already HAS a passtag
			 *    which they are currently unable to access because it has a future `time_starts` value.
			 *
			 * @throws exception If ``$between`` is passed incorrectly.
			 * @throws exception If ``$logic`` is passed with an incorrect value.
			 */
			public function will_access_passtags($passtags = NULL, $between = NULL, $logic = self::all_logic)
				{
					if($this->is_super_admin()) return TRUE; // Always.

					if(!($user_passtags = $this->active_passtags()))
						return FALSE; // NO passtags? Save some time here.

					if(!($args = func_get_args()))
						goto check_passtags;

					$logic       = $this::all_logic;
					$logic_types = array($this::all_logic, $this::any_logic);
					$between     = NULL; // Establish defaults.

					if(count($args) > 1 // Possible ``$logic`` in this case?
					   && in_array($this->©array->last($args), $logic_types, TRUE)
					) $logic = array_pop($args); // A ``$logic`` type.

					if(count($args) > 1 // Possible ``$between`` range?
					   && is_string($between = $this->©array->last($args)) && strpos($between, '#between::') === 0
					) $between = array_pop($args); // Date range.

					$passtags = (count($args) > 1) ? $args : $args[0]; // Multiple args remaining?

					if($between && (!($between = explode('::', $between, 3)) || count($between) !== 3))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_between_range', array_merge(get_defined_vars(), array('user' => $this)),
							$this->i18n('Invalid `$between` date range (please use `#between::from::to` with UTC timestamps).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($between))
						);
					if($between) $between = array('from' => (integer)$between[1], 'to' => (integer)$between[2]);

					check_passtags: // Target point. Start analyzing passtags.

					if(!isset($passtags)) return TRUE; // TRUE, at least ONE passtag.

					if(is_array($passtags) || is_object($passtags)) // Checks array/object values.
						{
							if(!$passtags || (is_object($passtags) && !$this->©object->is_not_ass_empty($passtags)))
								return FALSE; // Catch empty arrays/objects. Only NULL indicates *any* passtag.

							switch($logic) // Handle this based on logic type.
							{
								case $this::all_logic: // Satisfy all?

										foreach($passtags as $_passtags)
											if(!$this->will_access_passtags($_passtags, $logic))
												return FALSE; // Fail; MUST satisfy all :-)
										unset($_passtags); // Housekeeping.

										return TRUE; // Passed on all of these.

								case $this::any_logic: // Satisfy any?

										foreach($passtags as $_passtags)
											if($this->will_access_passtags($_passtags, $logic))
												return TRUE; // Success; satisfy ANY :-)
										unset($_passtags); // Housekeeping.

										return FALSE; // Failed on all of these.

								default: // Default case handler.
									throw $this->©exception( // This should NOT happen.
										$this->method(__FUNCTION__).'#invalid_logic_type', array_merge(get_defined_vars(), array('user' => $this)),
										$this->i18n('Invalid `$logic` type (please use a core constant for this value).').
										' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($logic))
									);
							}
						}
					if($this->©integer->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag)
								if($_user_passtag->passtag_id === ($passtag_id = $passtags))
									{
										if(!$between) // NOT looking at a date range.
											// However, we always DO check future access.
											{
												if($_user_passtag->time_starts > time())
													return TRUE; // In the future.
												continue; // Keep looking.
											}
										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										   && $_user_passtag->time_starts > time() // In the future?
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					if($this->©string->is_not_empty($passtags))
						{
							foreach($user_passtags as $_user_passtag) // NOT case sensitive.
								if(strcasecmp($_user_passtag->name, ($passtag_name = $passtags)) === 0)
									{
										if(!$between) // NOT looking at a date range.
											// However, we always DO check future access.
											{
												if($_user_passtag->time_starts > time())
													return TRUE; // In the future.
												continue; // Keep looking.
											}
										if(isset($_user_passtag->eot_time_stops))
											$_time_stops = $_user_passtag->eot_time_stops;
										else $_time_stops = $_user_passtag->time_stops;

										if((!$between['from'] || $_user_passtag->time_starts >= $between['from'])
										   && (!$between['to'] || $_time_stops < 0 || $_time_stops <= $between['to'])
										   && $_user_passtag->time_starts > time() // In the future?
										) return TRUE; // Passes on all of this.
									}
							unset($_user_passtag, $_time_stops); // Housekeeping.
						}
					return FALSE; // Default value.
				}

			/**
			 * Alias for ``$this->will_access_passtags(..., fw_constants::any_logic)``.
			 *
			 * @return boolean TRUE if the user WILL ACCESS any of the passtags.
			 */
			public function will_access_any_passtag()
				{
					$args = func_get_args();

					if(isset($args[0])) // Only if we DO have arguments.
						// Note: if we have NO arguments, it's already an `any` check.
						$args[] = $this::any_logic;

					return call_user_func_array(array($this, 'will_access_passtags'), $args);
				}

			/**
			 * Alias for {@link add_passtags()}.
			 *
			 * @return array {@inheritdoc add_passtags()}
			 */
			public function add_passtag() // Arguments are NOT listed here.
				{
					return call_user_func_array(array($this, 'add_passtags'), func_get_args());
				}

			/**
			 * Alternative to {@link add_passtags()}; this excludes DESCENDANTS (e.g. `_x`).
			 *
			 * @return array {@inheritdoc add_passtags()}
			 */
			public function add_passtag_x($passtag_ids_or_names, $order_session_id = NULL, $transaction_id = NULL, $allow_primary_dupes = FALSE)
				{
					return call_user_func(array($this, 'add_passtags'), // This excludes DESCENDANTS (e.g. `_x`).
					                      $passtag_ids_or_names, $order_session_id, $transaction_id, $allow_primary_dupes, TRUE);
				}

			/**
			 * Adds new passtag(s); plus all DESCENDANTS automatically.
			 *
			 * @param integer|string|array $passtag_ids_or_names Passtag ID(s) and/or name(s).
			 *    Can be defined as an array of passtag IDs and/or names (to add multiple passtags).
			 *    However, please NOTE that multiple passtags are added w/ a common order session and/or transaction ID.
			 *    If you need to add multiple passtags with different order session and/or transaction IDs; make separate calls.
			 *
			 * @param null|integer         $order_session_id An order session ID (if applicable).
			 *
			 * @param null|integer         $transaction_id A transaction ID (if applicable).
			 *
			 * @param boolean              $allow_primary_dupes Optional. Defaults to a FALSE value (recommended in most cases).
			 *    During checkout it IS possible to purchase a quantity greater than ONE however (enable this flag).
			 *
			 * @param boolean              $exclude_descendants Optional. Defaults to a FALSE value (recommended).
			 *    If TRUE, NO descendants are added; only ``$passtag_ids_or_names`` (and NO others whatsoever).
			 *
			 * @return array A TWO dimensional array of new user passtags added by this routine.
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$passtag_ids_or_names`` is empty.
			 */
			public function add_passtags($passtag_ids_or_names, $order_session_id = NULL, $transaction_id = NULL,
			                             $allow_primary_dupes = FALSE, $exclude_descendants = FALSE)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty', 'array:!empty'), array('null', 'integer:!empty'),
					                       array('null', 'integer:!empty'), 'boolean', 'boolean', func_get_args());

					$new_user_passtags             = array(); // Initialize array of new user passtags added by this routine.
					$processed_primary_passtag_ids = array(); // Used to avoid primary dupes in the routine below.
					$passtag_ids_or_names          = (array)$passtag_ids_or_names; // Force array.

					foreach(array_unique($passtag_ids_or_names) as $_passtag_id_or_name)
						{
							if(!($_passtag = $this->©passtag->get($_passtag_id_or_name)))
								throw $this->©exception( // Should NOT happen.
									$this->method(__FUNCTION__).'#missing_passtag', array_merge(get_defined_vars(), array('user' => $this)),
									sprintf($this->i18n('Invalid. Missing passtag: `%1$s`.'), $_passtag_id_or_name)
								);
							if(!$allow_primary_dupes && isset($processed_primary_passtag_ids[$_passtag->ID])) continue;
							$processed_primary_passtag_ids[$_passtag->ID] = $_passtag->ID; // Flag array w/ passtag's ID.

							if(!isset($_passtag_descendants[$_passtag->ID])) // Only if we DON'T know already.
								if($exclude_descendants) $_passtag_descendants[$_passtag->ID] = array(); // Force NO descendants.
								else $_passtag_descendants[$_passtag->ID] = $this->©passtags->descendants_of($_passtag->ID, FALSE, -1, FALSE);

							// NOTE: It is always possible to have DUPLICATE descendants; when/if two or more passtags share the same children.
							// The call above to `descendants_of` allows for this. The user should get all of the passtags (even DUPLICATE descendants).
							// In this way, a user may get five of passtag "C" (as one example); and this IS the intended behavior :-)

							foreach(array_merge((array)$_passtag->ID, $_passtag_descendants[$_passtag->ID]) as $_passtag_id)
								{
									if(!($_passtag = $this->©passtag->get($_passtag_id)))
										throw $this->©exception( // Should NOT happen.
											$this->method(__FUNCTION__).'#missing_passtag', array_merge(get_defined_vars(), array('user' => $this)),
											sprintf($this->i18n('Invalid. Missing passtag ID: `%1$s`.'), $_passtag_id)
										);
									$_columns = array( // A new database row.
									                   'access_key'       => $this->©encryption->keygen(),
									                   'passtag_id'       => $_passtag->ID, // For this passtag.
									                   'user_id'          => (($this->has_id()) ? $this->ID : NULL),
									                   'order_session_id' => $order_session_id, 'transaction_id' => $transaction_id,
									                   'time_created'     => time(), 'time_starts' => time() + $_passtag->time_starts_offset,
									                   'time_stops'       => (($_passtag->time_stops_offset >= 0) ? time() + $_passtag->time_stops_offset : -1),
									                   'eot_time_stops'   => NULL, // Be sure we end up with this property for events below.
									);
									if(!$this->©db->insert($this->©db_tables->get('user_passtags'), $_columns) || !($_user_passtag_id = $this->©db->insert_id))
										throw $this->©exception($this->method(__FUNCTION__).'#insertion_failure', array_merge(get_defined_vars(), array('user' => $this)),
										                        sprintf($this->i18n('Database insertion failure on passtag ID: `%1$s`.'), $_passtag->ID));

									$_user_passtag = (object)($_columns + array('ID' => $_user_passtag_id) + (array)$_passtag); // ID + inheritance.

									$new_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
								}
							unset($_passtag_id, $_passtag, $_columns, $_user_passtag_id, $_user_passtag); // Housekeeping.
						}
					unset($_passtag_id_or_name, $_passtag, $_passtag_descendants); // Housekeeping.

					if(!headers_sent() && $this->is_current()) // If possible, update session access keys.
						$this->update_session_data(array('access_keys' => array_keys($new_user_passtags)));

					$this->refresh_passtags(); // Refresh user passtags (before events).

					if($new_user_passtags) // Trigger events?
						foreach($this->©array->values($new_user_passtags, 2, TRUE) as $_user_passtag)
							$this->©event->trigger('user_gets_passtag',
							                       array('user'          => $this, // This user.
							                             'user_passtags' => $_user_passtag), // User passtag.
							                       get_defined_vars()); // Plus all defined variables.
					unset($_user_passtag); // Housekeeping.

					return $new_user_passtags; // New user passtag IDs.
				}

			/**
			 * Alternative to {@link add_passtags()}; this excludes DESCENDANTS (e.g. `_x`).
			 *
			 * @return array {@inheritdoc add_passtags()}
			 */
			public function add_passtags_x($passtag_ids_or_names, $order_session_id = NULL, $transaction_id = NULL, $allow_primary_dupes = FALSE)
				{
					return call_user_func(array($this, 'add_passtags'), // This excludes DESCENDANTS (e.g. `_x`).
					                      $passtag_ids_or_names, $order_session_id, $transaction_id, $allow_primary_dupes, TRUE);
				}

			/**
			 * Removes/deactivates a passtag (by passtag ID or name); plus all DESCENDANTS automatically.
			 *
			 * @note Use with CAUTION :-) This will remove/deactivate ALL user passtags with the underlying passtag ID (or name).
			 *    In addition, this will remove ALL user passtags having an underlying passtag which is a descendant of
			 *    the passtag ID or name you are removing/deactivating (e.g. all DESCENDANTS automatically).
			 *
			 * @param integer|string $passtag_id_or_name Passtag ID or name (required).
			 *
			 * @param null|integer   $eot_time_stops Optional. Defaults to NULL (indicating current time).
			 *    If defined, user passtag deactivations will EOT at this time; NOT current time.
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @note To exclude descendants pass ``$args['+descendants'] = FALSE``.
			 *    See also: {@link passtags_for()} for further details.
			 *
			 * @return array {@inheritdoc deactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$passtag_id_or_name`` is empty.
			 */
			public function remove_passtag($passtag_id_or_name, $eot_time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'),
					                       array('null', 'integer:!empty'), 'array', func_get_args());

					if($passtag_id_or_name === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge(array('+descendants' => TRUE), $args, array('passtag_id_or_name' => $passtag_id_or_name));

					return call_user_func_array(array($this, 'deactivate_passtags'), array($args, $eot_time_stops));
				}

			/**
			 * Removes/deactivates a passtag (by passtag ID or name); excluding DESCENDANTS (e.g. `_x`).
			 *
			 * @note Use with CAUTION :-) This will remove/deactivate ALL user passtags with the underlying passtag ID (or name).
			 *
			 * @param integer|string $passtag_id_or_name Passtag ID or name (required).
			 *
			 * @param null|integer   $eot_time_stops Optional. Defaults to NULL (indicating current time).
			 *    If defined, user passtag deactivations will EOT at this time; NOT current time.
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc deactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$passtag_id_or_name`` is empty.
			 */
			public function remove_passtag_x($passtag_id_or_name, $eot_time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'),
					                       array('null', 'integer:!empty'), 'array', func_get_args());

					if($passtag_id_or_name === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('passtag_id_or_name' => $passtag_id_or_name, '+descendants' => FALSE));

					return call_user_func_array(array($this, 'deactivate_passtags'), array($args, $eot_time_stops));
				}

			/**
			 * Removes/deactivates passtag ID(s) and/or name(s); plus all DESCENDANTS automatically.
			 *
			 * @note Use with CAUTION :-) This will remove/deactivate ALL user passtags with the underlying passtag IDs (or names).
			 *    In addition, this will remove ALL user passtags having an underlying passtag which are a descendant of
			 *    the passtag IDs or names you are removing/deactivating (e.g. all DESCENDANTS automatically).
			 *
			 * @param integer|string|array $passtag_ids_or_names Passtag ID(s) and/or name(s). Defaults to {@link fw_constants::all}.
			 *    Can be defined as an array of passtag IDs and/or names to remove/deactivate multiple passtags.
			 *
			 * @param null|integer         $eot_time_stops Optional. Defaults to NULL (indicating current time).
			 *    If defined, user passtag deactivations will EOT at this time; NOT current time.
			 *
			 * @param array                $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @note To exclude descendants pass ``$args['+descendants'] = FALSE``.
			 *    See also: {@link passtags_for()} for further details.
			 *
			 * @return array {@inheritdoc deactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$passtag_ids_or_names`` is empty.
			 */
			public function remove_passtags($passtag_ids_or_names = self::all, $eot_time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty', 'array:!empty'),
					                       array('null', 'integer:!empty'), 'array', func_get_args());

					$args = array_merge(array('+descendants' => TRUE), $args, array('passtag_id_or_name' => $passtag_ids_or_names));

					return call_user_func_array(array($this, 'deactivate_passtags'), array($args, $eot_time_stops));
				}

			/**
			 * Removes/deactivates passtag ID(s) and/or name(s); excluding DESCENDANTS (e.g. `_x`).
			 *
			 * @note Use with CAUTION :-) This will remove/deactivate ALL user passtags with the underlying passtag IDs (or names).
			 *
			 * @param integer|string|array $passtag_ids_or_names Passtag ID(s) and/or name(s). Defaults to {@link fw_constants::all}.
			 *    Can be defined as an array of passtag IDs and/or names to remove/deactivate multiple passtags.
			 *
			 * @param null|integer         $eot_time_stops Optional. Defaults to NULL (indicating current time).
			 *    If defined, user passtag deactivations will EOT at this time; NOT current time.
			 *
			 * @param array                $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc deactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$passtag_ids_or_names`` is empty.
			 */
			public function remove_passtags_x($passtag_ids_or_names = self::all, $eot_time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty', 'array:!empty'),
					                       array('null', 'integer:!empty'), 'array', func_get_args());

					$args = array_merge($args, array('passtag_id_or_name' => $passtag_ids_or_names, '+descendants' => FALSE));

					return call_user_func_array(array($this, 'deactivate_passtags'), array($args, $eot_time_stops));
				}

			/**
			 * Deactivates a specific user passtag (by ID or access key).
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @param null|integer   $eot_time_stops Optional. Defaults to NULL (indicating current time).
			 *    If defined, the user passtag deactivation will EOT at this time; NOT current time.
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc deactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function deactivate_passtag($id_or_access_key, $eot_time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), array('null', 'integer:!empty'), 'array', func_get_args());

					if($id_or_access_key === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('id_or_access_key' => $id_or_access_key));

					return call_user_func_array(array($this, 'deactivate_passtags'), array($args, $eot_time_stops));
				}

			/**
			 * Deactivates specific user passtags (based on search criteria).
			 *
			 * @note User passtags are NOT actually deleted/removed by this routine (ever).
			 *    Instead, we simply deactivate access by forcing `eot_time_stops` to a specific time; and status to `inactive`.
			 *    In this way, a user passtag is NOT deleted; only made `inactive` (e.g. deactivated).
			 *
			 * @note If ``$eot_time_stops`` is greater than one which already exists (that is, when/if an EOT time already exists);
			 *    it will NOT override the existing value under ANY circumstance. The only way to override an existing value
			 *    is to set an EOT time that comes before (e.g. earlier) than one which already exists.
			 *
			 * @note If there is currently no EOT time; a user passtag can be deactivated at a future EOT time,
			 *    by passing ``$eot_time_stops`` w/ a future time. In this case, the user will NOT lose the passtag now;
			 *    they will lose it in the future (e.g. deactivation occurs later); so we only set the `eot_time_stops` property.
			 *    We do NOT change status. A future CRON job will deactivate the user passtag at the correct time.
			 *
			 * @note A user passtag CANNOT be deactivated if it's NOT currently `active` (e.g. it MUST be in an `active` state).
			 *    A user passtag is ONLY deactivated when/if the current status is `active`. Otherwise, the user passtag is either already
			 *    `inactive` or `deleted`. In addition, this routine will ONLY deactivate user passtags when ``$eot_time_stops`` is less than
			 *    or equal to the current time. Else, a future CRON job will deactivate the user passtag at the correct time.
			 *
			 * @param string|array $args Optional. Defaults to {@link fw_constants::all}.
			 *    Search criteria may include any combination of the following.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @param null|integer $eot_time_stops Optional. Defaults to NULL (current time).
			 *    If defined, all user passtag deactivations will EOT at this time instead of current time.
			 *
			 * @return array A TWO dimensional array of user passtags deactivated by this routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function deactivate_passtags($args = self::all, $eot_time_stops = NULL)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), array('null', 'integer:!empty'), func_get_args());

					$deactivated_user_passtags = array(); // Initialize array of user passtags deactivated by this routine.
					$time                      = time(); // Current time (for default EOT & comparisons below).
					if(!isset($eot_time_stops)) $eot_time_stops = $time; // Use default EOT time?

					foreach($this->passtags_for($args) as $_user_passtag) // User passtags.
						{
							check_and_update_eot_time: // Update EOT time?

							if(isset($_user_passtag->eot_time_stops))
								if($eot_time_stops >= $_user_passtag->eot_time_stops)
									goto check_and_deactivate_status; // MUST be earlier.

							if($this->©db->update($this->©db_tables->get('user_passtags'), // Update EOT time.
							                      array('eot_time_stops' => $eot_time_stops), array('ID' => $_user_passtag->ID))
							) throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
							                          sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							$_user_passtag->eot_time_stops = $eot_time_stops; // Update property (we have a new EOT time).

							# The first part is done. Now, should this user passtag be deactivated right now?

							check_and_deactivate_status: // Deactivate now?

							if($_user_passtag->status !== 'active' || $eot_time_stops > $time) continue; // Nope.

							if($this->©db->update($this->©db_tables->get('user_passtags'), // Update status.
							                      array('status' => 'inactive'), array('ID' => $_user_passtag->ID))
							) throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
							                          sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							$_user_passtag->status = 'inactive'; // Update property (we have a new status).

							$deactivated_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
						}
					unset($_user_passtag); // Housekeeping.

					$this->refresh_passtags(); // Refresh user passtags (before events).

					if($deactivated_user_passtags) // Trigger events?
						foreach($this->©array->values($deactivated_user_passtags, 2, TRUE) as $_user_passtag)
							$this->©event->trigger('user_loses_passtag',
							                       array('user'          => $this, // This user.
							                             'user_passtags' => $_user_passtag), // User passtag.
							                       get_defined_vars()); // Plus all defined variables.
					unset($_user_passtag); // Housekeeping.

					return $deactivated_user_passtags;
				}

			/**
			 * Reactivates a specific user passtag (by ID or access key).
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc reactivate_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function reactivate_passtag($id_or_access_key, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array', func_get_args());

					if($id_or_access_key === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('id_or_access_key' => $id_or_access_key));

					return call_user_func_array(array($this, 'reactivate_passtags'), array($args));
				}

			/**
			 * Reactivates specific user passtags (based on search criteria).
			 *
			 * @note We reactivate a user passtag by forcing it's status to `active` & `eot_time_stops` to a NULL value.
			 *    Restoration may or may NOT actually restore access. The user passtag may also need to be renewed for more time.
			 *    In other words, this does NOT renew the `time_stops` value; it simply restores an `active` status.
			 *
			 * @note An active passtag w/o an EOT time CANNOT be reactivated again (e.g. it MUST be restorable).
			 *
			 * @param string|array $args Optional. Defaults to {@link fw_constants::all}.
			 *    Search criteria may include any combination of the following.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array A TWO dimensional array of user passtags reactivated by this routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function reactivate_passtags($args = self::all)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), func_get_args());

					$reactivated_user_passtags = array(); // Initialize array of user passtags reactivated by this routine.

					foreach($this->passtags_for($args) as $_user_passtag) // User passtags (based on search criteria).
						{
							if($_user_passtag->status === 'active' && !isset($_user_passtag->eot_time_stops))
								continue; // User passtag is ALREADY `active`; and there is no EOT time.

							if(!$this->©db->update($this->©db_tables->get('user_passtags'),
							                       array('eot_time_stops' => NULL, 'status' => 'active'), array('ID' => $_user_passtag->ID))
							) throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
							                          sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							$_user_passtag->eot_time_stops = NULL; // Update object property.
							$_user_passtag->status         = 'active'; // Update property.

							$reactivated_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
						}
					unset($_user_passtag); // Housekeeping.

					$this->refresh_passtags(); // Refresh user passtags (before events).

					if($reactivated_user_passtags) // Trigger events?
						foreach($this->©array->values($reactivated_user_passtags, 2, TRUE) as $_user_passtag)
							$this->©event->trigger('user_regains_passtag',
							                       array('user'          => $this, // This user.
							                             'user_passtags' => $_user_passtag), // User passtag.
							                       get_defined_vars()); // Plus all defined variables.
					unset($_user_passtag); // Housekeeping.

					return $reactivated_user_passtags;
				}

			/**
			 * Updates a specific user passtag (by ID or access key).
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @param array          $columns An array of columns to update in the `user_passtags` table.
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc update_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function update_passtag($id_or_access_key, $columns, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array', func_get_args());

					if($id_or_access_key === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('id_or_access_key' => $id_or_access_key));

					return call_user_func_array(array($this, 'update_passtags'), array($args, $columns));
				}

			/**
			 * Updates specific user passtags (based on search criteria).
			 *
			 * @note This method allows us to modify user passtags w/o triggering any events.
			 *    For instance; if user passtags need to be removed w/o triggering an event; use this.
			 *    However, please note that `time_stops`, `eot_time_stops` may result in CRON-driven event processes;
			 *    when/if an expiration occurs naturally; based on this column value.
			 *
			 * @param string|array $args Required. There is NO default value here.
			 *    Search criteria may include any combination of the following.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @param array        $columns An array of columns to update.
			 *    CAUTION: The same columns/values will be updated for all matching user passtags.
			 *    If you need to update each w/ different values; please use {@link update_passtag}.
			 *
			 * @return array A TWO dimensional array of user passtags updated by this routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$columns`` is empty for any reason.
			 */
			public function update_passtags($args, $columns)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), 'array:!empty', func_get_args());

					$updated_user_passtags = array(); // Initialize array of user passtags updated by this routine.

					unset($columns['ID'], $columns['access_key']); // These CANNOT be modified in any way.
					if(!$columns) // Empty after reserved key removals? This should NOT happen (but just in case).
						throw $this->©exception($this->method(__FUNCTION__).'#empty_columns', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('Empty columns after reserved key removals.')
						);
					foreach($this->passtags_for($args) as $_user_passtag) // User passtags (based on search criteria).
						{
							if(!$this->©db->update($this->©db_tables->get('user_passtags'), $columns, array('ID' => $_user_passtag->ID)))
								throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
								                        sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							$_user_passtag = (object)array_merge((array)$_user_passtag, $columns); // Merge w/ columns updated here.
							$_user_passtag = $this->©db_utils->typify_results_deep($_user_passtag); // Typify; as DB results.

							$updated_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
						}
					unset($_user_passtag); // Just a little housekeeping now.

					$this->refresh_passtags(); // Refresh user passtags.

					return $updated_user_passtags;
				}

			/**
			 * Deletes a specific user passtag (by ID or access key).
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc delete_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function delete_passtag($id_or_access_key, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array', func_get_args());

					if($id_or_access_key === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('id_or_access_key' => $id_or_access_key));

					return call_user_func_array(array($this, 'delete_passtags'), array($args));
				}

			/**
			 * Deletes specific user passtags (based on search criteria).
			 *
			 * @note User passtags are NOT actually deleted/removed by this routine (ever).
			 *    Instead, we simply set the status for a user passtag to `deleted`; and it remains in the DB.
			 *    In this way, a user passtag is NEVER deleted; only given a `deleted` status.
			 *
			 * @note A user passtag CANNOT be deleted more than ONE time. If it already has a status of `deleted`,
			 *    it will NOT be deleted again. In addition, user passtags are only "lost" as a result of this routine,
			 *    IF the previous status was `active` (e.g. if status goes from `active` to `deleted` here).
			 *
			 * @param string|array $args Optional. Defaults to {@link fw_constants::all}.
			 *    Search criteria may include any combination of the following.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array A TWO dimensional array of user passtags deleted by this routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function delete_passtags($args = self::all)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), func_get_args());

					$lost_user_passtags    = array(); // Initialize array of user passtags lost by this routine.
					$deleted_user_passtags = array(); // Initialize array of user passtags deleted by this routine.

					foreach($this->passtags_for($args) as $_user_passtag) // User passtags (based on search criteria).
						{
							if($_user_passtag->status === 'deleted') continue; // Deleted already?

							if($this->©db->update($this->©db_tables->get('user_passtags'), // Delete.
							                      array('status' => 'deleted'), array('ID' => $_user_passtag->ID))
							) throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
							                          sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							if($_user_passtag->status === 'active') // Was active?
								$lost_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;

							$_user_passtag->status = 'deleted'; // Update object property.

							$deleted_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
						}
					unset($_user_passtag); // Housekeeping.

					$this->refresh_passtags(); // Refresh user passtags (before events).

					if($lost_user_passtags) // Trigger events?
						foreach($this->©array->values($lost_user_passtags, 2, TRUE) as $_user_passtag)
							$this->©event->trigger('user_loses_passtag',
							                       array('user'          => $this, // This user.
							                             'user_passtags' => $_user_passtag), // User passtag.
							                       get_defined_vars()); // Plus all defined variables.
					unset($_user_passtag); // Housekeeping.

					return $deleted_user_passtags;
				}

			/**
			 * Renews a specific user passtag (by ID or access key).
			 *
			 * @param integer|string $id_or_access_key A user passtag ID (or access key).
			 *
			 * @param integer        $time_stops Optional. Defaults to a NULL value (repeat previous term).
			 *    If defined, the user passtag will be given this specific `time_stops` value.
			 *
			 * @param array          $args Optional. Any additional search criteria.
			 *    Search criteria may include any combination of the following array keys.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @return array {@inheritdoc renew_passtags()}
			 *
			 * @throws exception If invalid types passed through arguments list.
			 * @throws exception If ``$id_or_access_key`` is empty.
			 */
			public function renew_passtag($id_or_access_key, $time_stops = NULL, $args = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'integer', 'array', func_get_args());

					if($id_or_access_key === $this::all) // Disallow {@link fw_constants::all}. Function name implies ONE only.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_string', array_merge(get_defined_vars(), array('user' => $this)),
						                        $this->i18n('The all constant is NOT allowed here.')
						);
					$args = array_merge($args, array('id_or_access_key' => $id_or_access_key));

					return call_user_func_array(array($this, 'renew_passtags'), array($args, $time_stops));
				}

			/**
			 * Renews specific user passtags (based on search criteria).
			 *
			 * @note Renewing a user passtag can potentially trigger TWO different events. Because, before anything else;
			 *    we ensure that all user passtags are `active` (e.g. we reactivate all). If some user passtags were previously
			 *    `inactive` or `deleted`, event type `user_regains_passtag` will be triggered on those before we renew them.
			 *    See {@link reactivate_passtags()} for further details on this behavior.
			 *
			 * @note It is NOT possible to renew a user passtag which currently has a `time_stops` value less than or equal to `0`.
			 *    User passtags that do NOT expire CANNOT (by definition) be renewed; because there IS nothing to renew.
			 *
			 * @note If ``$time_stops`` is NOT defined (e.g. we are simply repeating previous term); but it is NOT possible
			 *    to accurately determine the previous term length (e.g. the user passtag has invalid `time_starts`, `time_stops`);
			 *    those user passtags will NOT be renewed at all. They are simply skipped by this routine.
			 *
			 * @param string|array $args Optional. Defaults to {@link fw_constants::all}.
			 *    Search criteria may include any combination of the following.
			 *       See: {@link passtags_for()} for further details on this.
			 *
			 * @param integer      $time_stops Optional. Defaults to a NULL value (repeat previous term).
			 *    If this is defined, all matching user passtags will be given this specific `time_stops` value.
			 *
			 * @return array A TWO dimensional array of user passtags renewed by this routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$time_stops`` is passed with a time less than or equal to now.
			 *    A renewal (by definition) indicates there should be MORE time; e.g. it should NOT expire now.
			 */
			public function renew_passtags($args = self::all, $time_stops = NULL)
				{
					$this->check_arg_types(array('string:!empty', 'array:!empty'), 'integer', func_get_args());

					$this->reactivate_passtags($args); // Make sure all of these user passtags are active.
					$time                  = time(); // Current time (for default `time_stops` & comparisons below).
					$renewed_user_passtags = array(); // Initialize array of user passtags renewed by this routine.

					if(isset($time_stops) && $time_stops <= $time)
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_time', array_merge(get_defined_vars(), array('user' => $this)),
							sprintf($this->i18n('Invalid renewal time (e.g. `time_stops`): `%1$s`.'), $time_stops).
							' '.$this->i18n('A renewal (by definition) indicates there should be MORE time.')
						);
					foreach($this->passtags_for($args) as $_user_passtag) // User passtags (based on search criteria).
						{
							if($_user_passtag->time_stops <= 0) continue; // Nothing to renew (does NOT stop).

							if(!isset($time_stops) && ($_user_passtag->time_stops <= $_user_passtag->time_starts))
								continue; // No specific `time_stops` value & unable to accurately determine previous term length.
							// It's VERY important that we bypass this, because we do NOT want any value that is less than or equal to `0`.
							// That would grant lifetime access by mistake; anything <= 0 is considered infinite (NOT good).

							if(!isset($time_stops)) // If NOT defined; we simply repeat previous term length.
								$_time_stops = $time + ($_user_passtag->time_stops - $_user_passtag->time_starts);
							else $_time_stops = $time_stops; // A specific time in the future.

							if($this->©db->update($this->©db_tables->get('user_passtags'), // Renew.
							                      array('time_stops' => $_time_stops), array('ID' => $_user_passtag->ID))
							) throw $this->©exception($this->method(__FUNCTION__).'#udpate_failure', array_merge(get_defined_vars(), array('user' => $this)),
							                          sprintf($this->i18n('DB update failure on user passtag ID: `%1$s`.'), $_user_passtag->ID));

							$_user_passtag->time_stops = $_time_stops; // Update object property.

							$renewed_user_passtags[$_user_passtag->access_key][$_user_passtag->ID] = $_user_passtag;
						}
					unset($_user_passtag, $_time_stops); // Housekeeping.

					$this->refresh_passtags(); // Refresh user passtags (before events).

					if($renewed_user_passtags) // Trigger events?
						foreach($this->©array->values($renewed_user_passtags, 2, TRUE) as $_user_passtag)
							$this->©event->trigger('user_renews_passtag',
							                       array('user'          => $this, // This user.
							                             'user_passtags' => $_user_passtag), // User passtag.
							                       get_defined_vars()); // Plus all defined variables.
					unset($_user_passtag); // Housekeeping.

					return $renewed_user_passtags;
				}

			/**
			 * Gets the number of times this user has logged in.
			 *
			 * @return integer Number of times this user has logged in,
			 *    else `0` if they've NEVER logged in before.
			 */
			public function login_count()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = 0;

					if(!$this->has_id()) // No ID. No logins.
						return $this->cache[__FUNCTION__];

					$query = // Total number of logins.
						"SELECT".
						" `user_login_log`.`ID`".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_login_log'))."` AS `user_login_log`".

						" WHERE".
						" (`user_login_log`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."' OR `user_login_log`.`username` = '".$this->©string->esc_sql($this->username)."')".
						" AND `user_login_log`.`event_type_id` = '".$this->©string->esc_sql((string)$this->©event_type->id('user_login_success'))."'";

					$this->cache[__FUNCTION__] = $this->©db_utils->calc_found_rows($query);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets the number of times this user has failed to log in.
			 *
			 * @return integer Number of times this user has failed to log in,
			 *    else `0` if they've NEVER failed to log in.
			 */
			public function failed_login_count()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = 0;

					if(!$this->has_id()) // No ID. No logins.
						return $this->cache[__FUNCTION__];

					$query =
						"SELECT".
						" `user_login_log`.`ID`".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_login_log'))."` AS `user_login_log`".

						" WHERE".
						" (`user_login_log`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."' OR `user_login_log`.`username` = '".$this->©string->esc_sql($this->username)."')".
						" AND `user_login_log`.`event_type_id` = '".$this->©string->esc_sql((string)$this->©event_type->id('user_login_failure'))."'";

					$this->cache[__FUNCTION__] = $this->©db_utils->calc_found_rows($query);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets user's last login time.
			 *
			 * @return integer Last login time, else `0` if they've NEVER logged in before.
			 */
			public function last_login_time()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = 0;

					if(!$this->has_id()) // No ID. No logins.
						return $this->cache[__FUNCTION__];

					$query = // Max login time (e.g. last login time).
						"SELECT".
						" MAX(`user_login_log`.`time`)".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_login_log'))."` AS `user_login_log`".

						" WHERE".
						" (`user_login_log`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."' OR `user_login_log`.`username` = '".$this->©string->esc_sql($this->username)."')".
						" AND `user_login_log`.`event_type_id` = '".$this->©string->esc_sql((string)$this->©event_type->id('user_login_success'))."'";

					$this->cache[__FUNCTION__] = (integer)$this->©db->get_var($query);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets user's last failed login time.
			 *
			 * @return integer Last failed login time, else `0` if they've NEVER failed to log in.
			 */
			public function last_failed_login_time()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = 0; // Default value.

					if(!$this->has_id()) // No ID. No logins.
						return $this->cache[__FUNCTION__];

					$query = // Max failed login time (e.g. last failed login time).
						"SELECT".
						" MAX(`user_login_log`.`time`)".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_login_log'))."` AS `user_login_log`".

						" WHERE".
						" (`user_login_log`.`user_id` = '".$this->©string->esc_sql((string)$this->ID)."' OR `user_login_log`.`username` = '".$this->©string->esc_sql($this->username)."')".
						" AND `user_login_log`.`event_type_id` = '".$this->©string->esc_sql((string)$this->©event_type->id('user_login_failure'))."'";

					$this->cache[__FUNCTION__] = (integer)$this->©db->get_var($query);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets user's transaction IDs.
			 *
			 * @return array User's transaction IDs.
			 */
			public function transaction_ids()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					foreach($this->passtags() as $_passtag)
						if(isset($_passtag->transaction_id))
							$this->cache[__FUNCTION__][$_passtag->transaction_id] = $_passtag->transaction_id;
					unset($_passtag); // Housekeeping.

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets user's transactions, in chronological order.
			 *
			 * @return array User's transactions, in chronological order.
			 */
			public function transactions()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = array();

					if(!($transaction_ids = $this->transaction_ids()))
						return $this->cache[__FUNCTION__];

					$query = // Query based on transaction IDs associated w/ passtags.
						"SELECT".
						" `transactions`.`ID` AS `ID`,".
						" `transactions`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('transactions'))."` AS `transactions`".

						" WHERE".
						" `transactions`.`ID` IN(".$this->©db_utils->comma_quotify($transaction_ids).")".
						" AND `transactions`.`ID` IS NOT NULL".
						" AND `transactions`.`ID` > '0'".

						" AND".
						"  (".
						"     (`transactions`.`subscr_id` IS NOT NULL AND `transactions`.`subscr_id` != '')".
						"     OR (`transactions`.`txn_id` IS NOT NULL AND `transactions`.`txn_id` != '')".
						"  )".

						" ORDER BY".
						" `transactions`.`time` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT_K)))
						$this->cache[__FUNCTION__] = $this->©db_utils->typify_results_deep($results);

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Gets user's last transaction.
			 *
			 * @return null|object The user's last transaction object, else NULL if they have no transactions.
			 */
			public function last_transaction()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = NULL;

					if(($transactions = $this->transactions()))
						$this->cache[__FUNCTION__] = $this->©array->last($transactions);

					return $this->cache[__FUNCTION__];
				}
		}
	}