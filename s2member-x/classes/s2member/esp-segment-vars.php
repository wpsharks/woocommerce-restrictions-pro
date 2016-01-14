<?php
/**
 * ESP Segment Vars.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\ESPs
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * ESP Segment Vars.
		 *
		 * @package s2Member\ESPs
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class esp_segment_vars extends framework
		{
			/**
			 * Maps segment vars to details provided by this software.
			 *
			 * @param integer|string              $esp_id_or_name The ID (or name) of an ESP.
			 *
			 * @param array                       $segment Segment specs.
			 *    An already parsed array of segment specs, with these elements.
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're working with in this case.
			 *
			 * @return array An array of vars, else an empty array if nothing can be populated.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 * @throws exception If ``$esp_id_or_name`` is empty.
			 */
			public function map($esp_id_or_name, $segment, $user = NULL)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty',
					                       $this->©user_utils->which_types(), func_get_args());

					$segment_vars = $this->for_segment($esp_id_or_name, $segment);
					$user         = $this->©user_utils->which($user);

					if(!$user->is_populated())
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#email_missing', get_defined_vars(),
							$this->i18n('The `$user` is NOT populated yet (email missing).').
							' '.$this->i18n('There is no `email` address to work with.')
						);
					$map = array(); // Initialize map/array.

					foreach($segment_vars as $_segment_var)
						{
							switch($_segment_var->var)
							{
								case 'user::ID': // Basic user details.
								case 'user::email':
								case 'user::username':
								case 'user::nicename':
								case 'user::first_name':
								case 'user::last_name':
								case 'user::full_name':
								case 'user::display_name':
								case 'user::registration_time':

										$_user_property              = $this->©string->replace_once('user::', '', $_segment_var->var);
										$map[$_segment_var->esp_var] = (string)$user->$_user_property;

										unset($_user_property); // Housekeeping.

										break; // Break switch handler.

								case 'user::passtag_ids': // Passtag IDs/names.
								case 'user::passtag_names':
								case 'user::active_passtag_ids':
								case 'user::active_passtag_names':
								case 'user::inactive_passtag_ids':
								case 'user::inactive_passtag_names':
								case 'user::deleted_passtag_ids':
								case 'user::deleted_passtag_names':
								case 'user::accessible_passtag_ids':
								case 'user::accessible_passtag_names':
								case 'user::inaccessible_passtag_ids':
								case 'user::inaccessible_passtag_names':

										$_passtags   = array(); // Initialize array of passtags.
										$_need_names = (substr($_segment_var->var, -5) === 'names');

										if(preg_match('/^user\:\:(?P<type>.+?)_passtag_(?:ids|names)$/', $_segment_var->var, $_sv))
											$_user_passtags = $user->{$_sv['type'].'_passtags'}();
										else $_user_passtags = $user->passtags();

										foreach($_user_passtags as $_user_passtag)
											if($_need_names) // Need passtag names?
												$_passtags[] = $_user_passtag->name;
											else $_passtags[] = (string)$_user_passtag->passtag_id;

										$_passtags                   = array_unique($_passtags);
										$map[$_segment_var->esp_var] = implode(';', $_passtags);

										unset($_sv, $_passtags, $_need_names, $_user_passtags, $_user_passtag);

										break; // Break switch handler.

								case 'user::login_count': // Login counts.
								case 'user::failed_login_count':

										if($_segment_var->var === 'user::failed_login_count')
											$map[$_segment_var->esp_var] = (string)$user->failed_login_count();
										else $map[$_segment_var->esp_var] = (string)$user->login_count();

										break; // Break switch handler.

								case 'user::last_login_time': // Login times.
								case 'user::last_failed_login_time':

										if($_segment_var->var === 'user::last_failed_login_time')
											$map[$_segment_var->esp_var] = (string)$user->last_failed_login_time();
										else $map[$_segment_var->esp_var] = (string)$user->last_login_time();

										break; // Break switch handler.

								case 'user::last_transaction_time': // Transaction info.

										if(($_user_last_transaction = $user->last_transaction()))
											$map[$_segment_var->esp_var] = (string)$_user_last_transaction->time;
										else $map[$_segment_var->esp_var] = (string)0;

										unset($_user_last_transaction); // Housekeeping.

										break; // Break switch handler.

								case 'home::url': // Home URL info.
								case 'home::domain':

										if($_segment_var->var === 'home::domain')
											{
												if(($_wp_home_parts = $this->©url->parse($this->©url->to_wp_home_uri())))
													$map[$_segment_var->esp_var] = $_wp_home_parts['host'];
												else $map[$_segment_var->esp_var] = '';
											}
										else $map[$_segment_var->esp_var] = $this->©url->to_wp_home_uri('', 'http');

										unset($_wp_home_parts); // Housekeeping.

										break; // Break switch handler.

								default: // Handle other dynamics here.

									if(strpos($_segment_var->var, 'user::profile_field::') === 0) // Profile fields.
										{
											$_profile_field_name         = $this->©string->replace_once('user::profile_field::', '', $_segment_var->var);
											$map[$_segment_var->esp_var] = implode(';', (array)$user->profile_field_value($_profile_field_name));

											unset($_profile_field_name); // Housekeeping.
										}
									break; // Break switch handler.
							}
						}
					unset($_segment_var); // Housekeeping.

					return $this->apply_filters('map', $map, get_defined_vars());
				}

			/**
			 * Gets ESP segment vars (for a specific ESP & segment).
			 *
			 * @param integer|string $esp_id_or_name The ID (or name) of an ESP.
			 *
			 * @param array          $segment Segment specs.
			 *    An already parsed array of segment specs, with these elements.
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @return array Array of ESP segment var objects (for a specific ESP & segment).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws exception If ``$esp_id_or_name`` is empty.
			 */
			public function for_segment($esp_id_or_name, $segment)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', func_get_args());

					$vars = $this->get_all($esp_id_or_name); // For a specific ESP.

					if(!$this->©strings->are_not_empty($segment['type'], $segment['value']))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#invalid_segment_array', get_defined_vars(),
							$this->i18n('Invalid `$segment` array (missing one or more keys).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($segment))
						);
					if(isset($vars['by_segment_type_segment'][$segment['type']][$segment['value']]))
						return $vars['by_segment_type_segment'][$segment['type']][$segment['value']];

					return array(); // Default return value.
				}

			/**
			 * Gets all ESP segment vars (for a specific ESP).
			 *
			 * @param integer|string $esp_id_or_name The ID (or name) of an ESP.
			 *
			 * @return array All ESP segment vars (for a specific ESP).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$esp_id_or_name`` is empty.
			 */
			public function get_all($esp_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!($esp = $this->©esp->get($esp_id_or_name)))
						throw $this->©exception(
							$this->method(__FUNCTION__).'#unexpected_esp_id_or_name', get_defined_vars(),
							$this->i18n('Unexpected `$esp_id_or_name`. Unable to locate this ESP.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $esp_id_or_name)
						);
					$db_cache_key = $this->method(__FUNCTION__).$esp->ID;

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$esp_segment_vars = array();

					$query =
						"SELECT".
						" `esp_segment_types`.`type` AS `esp_segment_type`,".
						" `esp_segment_vars`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('esp_segment_types'))."` AS `esp_segment_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('esp_segment_vars'))."` AS `esp_segment_vars`".

						" WHERE".
						" `esp_segment_vars`.`esp_id` = '".$this->©string->esc_sql((string)$esp->ID)."'".
						" AND `esp_segment_vars`.`esp_segment_type_id` = `esp_segment_types`.`ID`".

						" AND `esp_segment_vars`.`esp_segment_type_id` IS NOT NULL".
						" AND `esp_segment_vars`.`esp_segment_type_id` > '0'".

						" AND `esp_segment_vars`.`esp_var` IS NOT NULL".
						" AND `esp_segment_vars`.`esp_var` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$esp_segment_vars['by_id'][$_result->ID]                                                                        = $_result;
									$esp_segment_vars['by_segment_type_id_segment'][$_result->esp_segment_type_id][$_result->segment][$_result->ID] =& $esp_segment_vars['by_id'][$_result->ID];
									$esp_segment_vars['by_segment_type_segment'][$_result->esp_segment_type][$_result->segment][$_result->ID]       =& $esp_segment_vars['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $esp_segment_vars);
				}
		}
	}