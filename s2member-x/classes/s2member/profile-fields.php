<?php
/**
 * Profile Fields.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Profile_Fields
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Profile Fields.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_fields extends framework
		{
			/**
			 * Form field configurations, for profile fields that apply during profile updates.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader_writer The user (reader/writer) that we need to check permissions against here.
			 *
			 * @param boolean                     $check_passtag_restrictions Optional. Defaults to a TRUE value.
			 *    If this is FALSE, we will NOT limit profile fields to only those which apply to a specific user via passtag restrictions.
			 *
			 * @param string                      $regex_flavor Optional. Defaults to ``fw_constants::regex_js``.
			 *    Or, this can also be set to ``fw_constants::regex_php``; providing PHP regex validation patterns.
			 *
			 * @param array                       $passtag_ids Optional. An array of passtag IDs being acquired by the user.
			 *
			 * @param boolean                     $redisplay Optional. Defaults to a FALSE value.
			 *    If a user is logged-in, we will NOT redisplay public profile fields (we assume the user has already filled these in).
			 *    However, if this is set to TRUE, public profile fields will ALWAYS be displayed, even if the user is currently logged-in.
			 *    This also affects profile fields that require a passtag (where the user already has a passtag granting access).
			 *
			 * @return array An array of form field configuration arrays (integrates w/ {@link form_fields}).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$regex_flavor is empty.
			 */
			public function for_user_registration_checkout_form_fields($user, $reader_writer, $check_passtag_restrictions = TRUE, $regex_flavor = self::regex_js, $passtag_ids = array(), $redisplay = FALSE)
				{
					$this->check_arg_types($this->©user_utils->which_types(), $this->©user_utils->which_types(),
					                       'boolean', 'string:!empty', 'array', 'boolean', func_get_args());

					$user                          = $this->©user_utils->which($user);
					$reader_writer                 = $this->©user_utils->which($reader_writer);
					$form_field_configuration_keys = array_keys($this->©form_field->defaults);
					$form_fields                   = array(); // Initialize.

					foreach($this->ids_for_user_registration_checkout($user, $reader_writer, $check_passtag_restrictions, $passtag_ids, $redisplay) as $_profile_field_id)
						{
							if(!($_profile_field = $this->get($_profile_field_id)))
								throw $this->©exception(
									$this->method(__FUNCTION__).'#unexpected_profile_field_id', get_defined_vars(),
									$this->i18n('Unexpected profile field ID. Profile field is missing.').
									' '.sprintf($this->i18n('Got: `%1$s`.'), $_profile_field_id)
								);
							$form_fields[$_profile_field->name] = array();
							$_form_field_config                 =& $form_fields[$_profile_field->name];
							$_form_field_config                 = $this->get_meta_values($_profile_field->ID, $form_field_configuration_keys);
							foreach($_form_field_config as $_key => $_value) // Remove FALSE values (i.e. meta keys that do NOT exist).
								if($_value === FALSE) unset($_form_field_config[$_key]);
							unset($_key, $_value); // Housekeeping.

							$_form_field_config['ID']       = $_profile_field->ID;
							$_form_field_config['type']     = $_profile_field->type;
							$_form_field_config['label']    = $_profile_field->label;
							$_form_field_config['required'] = (boolean)$_profile_field->require;
							$_form_field_config['unique']   = (boolean)$_profile_field->unique;
							$_form_field_config['name']     = '[profile_fields]['.$_profile_field->name.']';

							if(!isset($_form_field_config['tabindex'])) // Only if NOT defined in meta values.
								$_form_field_config['tabindex'] = $_profile_field->order; // Default value.

							if(!$this->is_writable($_profile_field->ID, $user, $reader_writer, $this::context_registration))
								{
									if($_form_field_config['label']) // This additional text can be modified via filters.
										$_form_field_config['label'] .= ' '.$this->apply_filters('uneditable', $this->translate('— uneditable'), get_defined_vars());
									$_form_field_config['disabled'] = TRUE; // Read only.
								}
							unset($_form_field_config['validation_patterns'], $_form_field_config['options'], $_form_field_config['use_update_marker']);
							unset($_form_field_config['check_label'], $_form_field_config['checked_value'], $_form_field_config['checked_by_default']);
							unset($_form_field_config['default_value']);

							foreach($this->©profile_field_validations->for_($_profile_field->ID) as $_validation)
								if(($_form_field_validation_pattern = $this->©profile_field_validation_pattern->for_form_field($_validation->profile_field_validation_pattern_id, $regex_flavor)))
									$_form_field_config['validation_patterns'][$_validation->profile_field_validation_pattern_id] = $_form_field_validation_pattern;
							unset($_validation, $_form_field_validation_pattern);

							$_values      = $this->values_for($_profile_field->ID);
							$_first_value = $this->©array->first($_values);

							if(in_array($_profile_field->type, $this->©form_field->types_with_options, TRUE))
								{
									$_form_field_config['options'] = array();

									foreach($_values as $_value)
										$_form_field_config['options'][] = array(
											'label'      => (string)$_value->label,
											'value'      => $_value->value,
											'is_default' => (boolean)$_value->default
										);
									unset($_value); // Housekeeping.
								}
							else if(in_array($_profile_field->type, $this->©form_field->single_check_types, TRUE) && $_first_value)
								{
									$_form_field_config['check_label']        = (string)$_first_value->label;
									$_form_field_config['checked_value']      = $_first_value->value;
									$_form_field_config['checked_by_default'] = (boolean)$_first_value->default;
								}
							else if($_first_value && $_first_value->default)
								$_form_field_config['default_value'] = $_first_value->value;

							foreach($this->©form_field->defaults as $_key => $_default) // Type conversions.
								if(array_key_exists($_key, $_form_field_config) && !is_null($_default))
									settype($_form_field_config[$_key], gettype($_default));
							unset($_key, $_default); // Just a little housekeeping.

							$_form_field_config = $this->©form_field->standardize_field_config(NULL, $_form_field_config);

							unset($_values, $_first_value, $_form_field_config); // Housekeeping.
						}
					return $form_fields; // Form fields configurations, for all profile fields that apply.
				}

			/**
			 * Gets all profile fields for a user's registration/checkout session (in order).
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader_writer The user (reader/writer) that we need to check permissions against here.
			 *
			 * @param boolean                     $check_passtag_restrictions Optional. Defaults to a TRUE value.
			 *    If this is FALSE, we will NOT limit profile fields to only those which apply to a specific user via passtag restrictions.
			 *
			 * @param array                       $passtag_ids Optional. An array of passtag IDs being acquired by the user.
			 *
			 * @param boolean                     $redisplay Optional. Defaults to a FALSE value.
			 *    If a user is logged-in, we will NOT redisplay public profile fields (we assume the user has already filled these in).
			 *    However, if this is set to TRUE, public profile fields will ALWAYS be displayed, even if the user is currently logged-in.
			 *    This also affects profile fields that require a passtag (where the user already has a passtag granting access).
			 *
			 * @return array An array of profile field IDs (in order); which should be displayed during registration/checkout for the user.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function ids_for_user_registration_checkout($user, $reader_writer, $check_passtag_restrictions = TRUE, $passtag_ids = array(), $redisplay = FALSE)
				{
					$this->check_arg_types($this->©user_utils->which_types(), $this->©user_utils->which_types(),
					                       'boolean', 'array', 'boolean', func_get_args());

					$user          = $this->©user_utils->which($user);
					$reader_writer = $this->©user_utils->which($reader_writer);

					if($passtag_ids) // Gather all descendants of these passtag IDs too.
						$passtag_ids = array_unique(array_merge($passtag_ids, $this->©passtag->descendants_of($passtag_ids)));
					$profile_field_ids = array(); // Initialize.

					if(!($profile_fields = $this->get_all())) return array(); // No profile fields.

					foreach($profile_fields['by_id'] as $_profile_field_id => $_profile_field)
						{
							if(!$this->is_readable($_profile_field->ID, $user, $reader_writer, $this::context_registration))
								continue; // Writable should be scanned before generating the form field's markup (i.e. disabled="disabled").

							if(!$check_passtag_restrictions) $profile_field_ids[] = $_profile_field->ID;

							else if(is_array($_failures = $this->©passtag_restrictions->check_profile_field($_profile_field->ID, $user)))
								{
									// This is a profile field that comes with certain passtags (but the user does NOT yet have one of them).
									// In this case, if the user is registering for (or purchasing) a passtag granting access to this profile field,
									// we DO include the profile field (but again, ONLY if the registration/purchase includes access to it).

									foreach(array_keys($_failures) as $_type) // Looping over each ``$_type`` of failure.
										foreach($_failures[$_type] as $_group => $_data) foreach($passtag_ids as $_passtag_id)
											if(in_array($_passtag_id, $_data['passtag_ids_granting_access'], TRUE))
												{
													$profile_field_ids[] = $_profile_field->ID;
													break 3; // Continue.
												}
								}
							else if($_failures === NULL && $redisplay) // Redisplay?
								// This is a profile field that comes with certain passtags (and the user can already access one of them).
								// In this case, we ONLY display the profile field again, if ``$redisplay`` is set to a TRUE value.
								$profile_field_ids[] = $_profile_field->ID;

							else if($_failures === FALSE && ($redisplay || !$user->is_logged_in())) // Redisplay?
								// This is a profile field that comes with every account (i.e. does NOT require a passtag).
								// In this case, we ONLY display the profile field, if they are NOT logged-in.
								// Or, if ``$redisplay`` is set to a TRUE value (we display it again).
								$profile_field_ids[] = $_profile_field->ID;

							unset($_failures, $_type, $_group, $_data);
						}
					unset($_profile_field_id, $_profile_field);

					return $profile_field_ids;
				}

			/**
			 * Form field configurations, for profile fields that apply during profile updates.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader_writer The user (reader/writer) that we need to check permissions against here.
			 *
			 * @param boolean                     $check_passtag_restrictions Optional. Defaults to a TRUE value.
			 *    If this is FALSE, we will NOT limit profile fields to only those which apply to a specific user via passtag restrictions.
			 *
			 * @param string                      $regex_flavor Optional. Defaults to ``fw_constants::regex_js``.
			 *    Or, this can also be set to ``fw_constants::regex_php``; providing PHP regex validation patterns.
			 *
			 * @return array An array of form field configuration arrays (integrates w/ {@link form_fields}).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$regex_flavor is empty.
			 */
			public function for_user_profile_update_form_fields($user, $reader_writer, $check_passtag_restrictions = TRUE, $regex_flavor = self::regex_js)
				{
					$this->check_arg_types($this->©user_utils->which_types(), $this->©user_utils->which_types(), 'boolean', 'string:!empty', func_get_args());

					$user                          = $this->©user_utils->which($user);
					$reader_writer                 = $this->©user_utils->which($reader_writer);
					$form_field_configuration_keys = array_keys($this->©form_field->defaults);
					$form_fields                   = array(); // Initialize.

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot get profile fields for update).')
						);
					foreach($this->ids_for_user_profile_update($user, $reader_writer, $check_passtag_restrictions) as $_profile_field_id)
						{
							if(!($_profile_field = $this->get($_profile_field_id)))
								throw $this->©exception(
									$this->method(__FUNCTION__).'#unexpected_profile_field_id', get_defined_vars(),
									$this->i18n('Unexpected profile field ID. Profile field is missing.').
									' '.sprintf($this->i18n('Got: `%1$s`.'), $_profile_field_id)
								);
							$form_fields[$_profile_field->name] = array();
							$_form_field_config                 =& $form_fields[$_profile_field->name];
							$_form_field_config                 = $this->get_meta_values($_profile_field->ID, $form_field_configuration_keys);
							foreach($_form_field_config as $_key => $_value) // Remove FALSE values (i.e. meta keys that do NOT exist).
								if($_value === FALSE) unset($_form_field_config[$_key]);
							unset($_key, $_value); // Housekeeping.

							$_form_field_config['ID']       = $_profile_field->ID;
							$_form_field_config['type']     = $_profile_field->type;
							$_form_field_config['label']    = $_profile_field->label;
							$_form_field_config['required'] = (boolean)$_profile_field->require;
							$_form_field_config['unique']   = (boolean)$_profile_field->unique;
							$_form_field_config['name']     = '[profile_fields]['.$_profile_field->name.']';

							if(!isset($_form_field_config['tabindex'])) // Only if NOT defined in meta values.
								$_form_field_config['tabindex'] = $_profile_field->order; // Default value.

							if(!$this->is_writable($_profile_field->ID, $user, $reader_writer, $this::context_profile_updates))
								{
									if($_form_field_config['label']) // This additional text can be modified via filters.
										$_form_field_config['label'] .= ' '.$this->apply_filters('uneditable', $this->translate('— uneditable'), get_defined_vars());
									$_form_field_config['disabled'] = TRUE; // Read only.
								}
							unset($_form_field_config['validation_patterns'], $_form_field_config['options'], $_form_field_config['use_update_marker']);
							unset($_form_field_config['check_label'], $_form_field_config['checked_value'], $_form_field_config['checked_by_default']);
							unset($_form_field_config['default_value']);

							foreach($this->©profile_field_validations->for_($_profile_field->ID) as $_validation)
								if(($_form_field_validation_pattern = $this->©profile_field_validation_pattern->for_form_field($_validation->profile_field_validation_pattern_id, $regex_flavor)))
									$_form_field_config['validation_patterns'][$_validation->profile_field_validation_pattern_id] = $_form_field_validation_pattern;
							unset($_validation, $_form_field_validation_pattern);

							$_values      = $this->values_for($_profile_field->ID);
							$_first_value = $this->©array->first($_values);

							if(in_array($_profile_field->type, $this->©form_field->types_with_options, TRUE))
								{
									$_form_field_config['options'] = array();

									foreach($_values as $_value)
										$_form_field_config['options'][] = array(
											'label'      => (string)$_value->label,
											'value'      => $_value->value,
											'is_default' => (boolean)$_value->default
										);
									unset($_value); // Housekeeping.
									$_form_field_config['use_update_markers'] = TRUE;
								}
							else if(in_array($_profile_field->type, $this->©form_field->single_check_types, TRUE) && $_first_value)
								{
									$_form_field_config['check_label']        = (string)$_first_value->label;
									$_form_field_config['checked_value']      = $_first_value->value;
									$_form_field_config['checked_by_default'] = (boolean)$_first_value->default;
								}
							else if($_first_value && $_first_value->default)
								$_form_field_config['default_value'] = $_first_value->value;

							foreach($this->©form_field->defaults as $_key => $_default) // Type conversions.
								if(array_key_exists($_key, $_form_field_config) && !is_null($_default))
									settype($_form_field_config[$_key], gettype($_default));
							unset($_key, $_default); // Just a little housekeeping.

							$_form_field_config = $this->©form_field->standardize_field_config(NULL, $_form_field_config);

							unset($_values, $_first_value, $_form_field_config); // Housekeeping.
						}
					return $form_fields; // Form fields configurations, for all profile fields that apply.
				}

			/**
			 * Gets all profile fields for user profile updates (in order).
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader_writer The user (reader/writer) that we need to check permissions against here.
			 *
			 * @param boolean                     $check_passtag_restrictions Optional. Defaults to a TRUE value.
			 *    If this is FALSE, we will NOT limit profile fields to only those which apply to a specific user via passtag restrictions.
			 *
			 * @return array An array of profile field IDs (in order); which should be displayed during profile updates for the user.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function ids_for_user_profile_update($user, $reader_writer, $check_passtag_restrictions = TRUE)
				{
					$this->check_arg_types($this->©user_utils->which_types(), $this->©user_utils->which_types(), 'boolean', func_get_args());

					$user              = $this->©user_utils->which($user);
					$reader_writer     = $this->©user_utils->which($reader_writer);
					$profile_field_ids = array(); // Initialize.

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot get profile field IDs for update).')
						);
					if(!($profile_fields = $this->get_all())) return array(); // No profile fields.

					foreach($profile_fields['by_id'] as $_profile_field_id => $_profile_field)
						{
							if(!$this->is_readable($_profile_field->ID, $user, $reader_writer, $this::context_profile_updates))
								continue; // Writable should be scanned before generating the form field's markup (e.g. disabled="disabled").

							if(!$check_passtag_restrictions || !($_failures = $this->©passtag_restrictions->check_profile_field($_profile_field->ID, $user)))
								// This is a profile field that comes with certain passtags (and the user can access one of them).
								// Or, this profile field is available to all users (e.g. it does NOT require any specific passtag).
								$profile_field_ids[] = $_profile_field->ID; // Include.
						}
					unset($_profile_field_id, $_profile_field);

					return $profile_field_ids;
				}

			/**
			 * Checks profile field read permissions for a specific user (in a specific area).
			 *
			 * @param integer|string              $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader The user (reader) that we need to check permissions against here.
			 *
			 * @param string                      $context The context in which permissions are being analyzed here.
			 *    One of: `fw_constants::context_registration`, `fw_constants::context_profile_updates`, `fw_constants::context_profile_views`.
			 *
			 * @return boolean TRUE if the profile field is readable for this user (in this context), else FALSE by default.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function is_readable($profile_field_id_or_name, $user, $reader, $context)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'),
					                       $this->©user_utils->which_types(), $this->©user_utils->which_types(),
					                       'string:!empty', func_get_args());

					$user   = $this->©user_utils->which($user);
					$reader = $this->©user_utils->which($reader);
					if($reader->is_no_user()) return TRUE;

					switch($context) // Translate context into database string value.
					{
						case $this::context_registration:

								$context = 'registration';

								break; // Break switch handler.

						case $this::context_profile_updates:

								$context = 'profile_updates';

								break; // Break switch handler.

						case $this::context_profile_views:

								$context = 'profile_views';

								break; // Break switch handler.

						default: // Exception.
							throw $this->©exception(
								$this->method(__FUNCTION__).'#unexpected_context', get_defined_vars(),
								sprintf($this->i18n('Invalid/unexpected context: `%1$s`.'), $context)
							);
					}
					foreach($this->©profile_field_permissions->for_($profile_field_id_or_name) as $_permission)
						if($_permission->context === $context && $_permission->read_access)
							if(!$_permission->wp_cap || ($reader->has_id() && $reader->wp->has_cap($_permission->wp_cap)))
								return TRUE; // Read access allowed by permissions.
					unset($_permission); // Housekeeping.

					return FALSE; // Default return value.
				}

			/**
			 * Checks profile field write permissions for a specific user (in a specific area).
			 *
			 * @param integer|string              $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $writer The user (writer) that we need to check permissions against here.
			 *
			 * @param string                      $context The context in which permissions are being analyzed here.
			 *    One of: ``fw_constants::context_registration``, ``fw_constants::context_profile_updates``, ``fw_constants::context_profile_views``.
			 *
			 * @return boolean TRUE if the profile field is writable for this user (in this context), else FALSE by default.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function is_writable($profile_field_id_or_name, $user, $writer, $context)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'),
					                       $this->©user_utils->which_types(), $this->©user_utils->which_types(),
					                       'string:!empty', func_get_args());

					$user   = $this->©user_utils->which($user);
					$writer = $this->©user_utils->which($writer);
					if($writer->is_no_user()) return TRUE;

					switch($context) // Translate context into database string value.
					{
						case $this::context_registration:

								$context = 'registration';

								break; // Break switch handler.

						case $this::context_profile_updates:

								$context = 'profile_updates';

								break; // Break switch handler.

						case $this::context_profile_views:

								$context = 'profile_views';

								break; // Break switch handler.

						default: // Exception.
							throw $this->©exception(
								$this->method(__FUNCTION__).'#unexpected_context', get_defined_vars(),
								sprintf($this->i18n('Invalid/unexpected context: `%1$s`.'), $context)
							);
					}
					foreach($this->©profile_field_permissions->for_($profile_field_id_or_name) as $_permission)
						if($_permission->context === $context && $_permission->write_access)
							if(!$_permission->wp_cap || ($writer->has_id() && $writer->wp->has_cap($_permission->wp_cap)))
								return TRUE; // Write access allowed by permissions.
					unset($_permission); // Housekeeping.

					return FALSE; // Default return value.
				}

			/**
			 * Gets a specific profile field value.
			 *
			 * @param integer $id The ID of a profile field value.
			 *
			 * @return null|object A profile field value object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function value($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$values = $this->values();

					if(isset($values['by_id'][$id]))
						return $values['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets profile field values for a specific profile field ID (in order).
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @return array An array of profile field value objects (in order), else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function values_for($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$values = $this->values(); // Gets all profile field values.

					if(is_integer($profile_field_id_or_name) && isset($values['by_profile_field_id'][$profile_field_id_or_name]))
						return $values['by_profile_field_id'][$profile_field_id_or_name];

					if(is_string($profile_field_id_or_name) && isset($values['by_profile_field_name'][$profile_field_id_or_name]))
						return $values['by_profile_field_name'][$profile_field_id_or_name];

					return array(); // Default return value.
				}

			/**
			 * Gets all profile field values (in order).
			 *
			 * @return array An array of all profile field values.
			 */
			public function values()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_values = array();

					$query =
						"SELECT".
						" `profile_fields`.`name` AS `profile_field_name`,".
						" `profile_field_values`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_values'))."` AS `profile_field_values`".

						" WHERE".
						" `profile_field_values`.`profile_field_id` = `profile_fields`.`ID`".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_field_values`.`profile_field_id` IS NOT NULL".
						" AND `profile_field_values`.`profile_field_id` > '0'".

						" ORDER BY".
						" `profile_field_values`.`order` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_values['by_id'][$_result->ID]                                               = $_result;
									$profile_field_values['by_profile_field_id'][$_result->profile_field_id][$_result->ID]     =& $profile_field_values['by_id'][$_result->ID];
									$profile_field_values['by_profile_field_name'][$_result->profile_field_name][$_result->ID] =& $profile_field_values['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_values);
				}

			/**
			 * Gets a meta value associated with a particular profile field.
			 *
			 * @param integer $profile_field_id A profile field ID.
			 *
			 * @param string  $name Name of a meta value that we're seeking.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_value($profile_field_id, $name)
				{
					return $this->©db_utils->get_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, (string)$name);
				}

			/**
			 * Gets meta value(s) associated with a particular profile field.
			 *
			 * @param integer      $profile_field_id A profile field ID.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_values($profile_field_id, $names = self::all)
				{
					return $this->©db_utils->get_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, $names);
				}

			/**
			 * Inserts (or updates) a meta value associated with a particular profile field.
			 *
			 * @param integer $profile_field_id A profile field ID.
			 *
			 * @param string  $name The name for this meta value (e.g. it's key in the database).
			 *
			 * @param mixed   $value The value for this meta entry.
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_value($profile_field_id, $name, $value)
				{
					return $this->©db_utils->update_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, array((string)$name => $value));
				}

			/**
			 * Inserts (or updates) meta value(s) associated with a particular profile field.
			 *
			 * @param integer $profile_field_id A profile field ID.
			 *
			 * @param array   $values Associative array of meta values (e.g. key/value pairs).
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_values($profile_field_id, $values)
				{
					return $this->©db_utils->update_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, $values);
				}

			/**
			 * Deletes a meta value associated with a particular profile field.
			 *
			 * @param integer $profile_field_id A profile field ID.
			 *
			 * @param string  $name The name of a meta value (e.g. it's key in the database).
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_value($profile_field_id, $name)
				{
					return $this->©db_utils->delete_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, (string)$name);
				}

			/**
			 * Deletes meta value(s) associated with a particular profile field.
			 *
			 * @param integer      $profile_field_id A profile field ID.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_values($profile_field_id, $names = self::all)
				{
					return $this->©db_utils->delete_meta_values('profile_field_meta', 'profile_field_id', $profile_field_id, $names);
				}

			/**
			 * Gets a specific profile field.
			 *
			 * @param integer|string $id_or_name The ID (or name) of a profile field.
			 *
			 * @return null|object A profile field object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$profile_fields = $this->get_all();

					if(is_integer($id_or_name) && isset($profile_fields['by_id'][$id_or_name]))
						return $profile_fields['by_id'][$id_or_name];

					else if(is_string($id_or_name) && isset($profile_fields['by_name'][$id_or_name]))
						return $profile_fields['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets all profile fields of a particular type (in order).
			 *
			 * @param integer|string $type_id_or_type A specific type of profile field (type ID or type name).
			 *
			 * @return array An array of all profile fields of a particular type (in order).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$type_id_or_type`` is empty.
			 */
			public function get_type($type_id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$profile_fields = $this->get_all();

					if(is_integer($type_id_or_type) && isset($profile_fields['by_type_id'][$type_id_or_type]))
						return $profile_fields['by_type_id'][$type_id_or_type];

					if(is_string($type_id_or_type) && isset($profile_fields['by_type'][$type_id_or_type]))
						return $profile_fields['by_type'][$type_id_or_type];

					return array(); // No profile fields of this type.
				}

			/**
			 * Gets all profile fields (in order).
			 *
			 * @return array An array of all profile fields (in order).
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_fields = array();

					$query =
						"SELECT".
						" `profile_field_types`.`type` AS `type`,".
						" `profile_fields`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_types'))."` AS `profile_field_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`".

						" WHERE".
						" `profile_fields`.`profile_field_type_id` = `profile_field_types`.`ID`".

						" AND `profile_field_types`.`type` IS NOT NULL".
						" AND `profile_field_types`.`type` != ''".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_fields`.`profile_field_type_id` IS NOT NULL".
						" AND `profile_fields`.`profile_field_type_id` > '0'".

						" ORDER BY".
						" `profile_fields`.`order` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_fields['by_id'][$_result->ID]                                       = $_result;
									$profile_fields['by_name'][$_result->name][$_result->ID]                     =& $profile_fields['by_id'][$_result->ID];
									$profile_fields['by_type'][$_result->type][$_result->ID]                     =& $profile_fields['by_id'][$_result->ID];
									$profile_fields['by_type_id'][$_result->profile_field_type_id][$_result->ID] =& $profile_fields['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_fields);
				}
		}
	}