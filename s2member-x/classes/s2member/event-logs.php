<?php
/**
 * Event Logs.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Events
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Event Logs.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_logs extends framework
		{
			/**
			 * Inserts a new log entry (`meta_vars` stored separately).
			 *
			 * @note This routine auto-formulates a list of related IDs from `meta_vars`.
			 *
			 * @param array $columns Required columns in the database table underlying this class.
			 *    See also ``$default_columns`` in this method for further details/requirements.
			 *
			 * @return integer New row ID; else `0` on any type of failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args`` are missing (or contain invalid data types).
			 */
			public function insert($columns)
				{
					$this->check_arg_types('array:!empty', func_get_args());

					$default_columns = array(
						'event_handler_id' => 0, // Required event handler ID.
						'meta_vars'        => array(), // Required (from event trigger).
						'vars'             => array(), // Required (from event trigger).

						'time'             => time(), // Time now (auto-generated).
						'process_time'     => NULL, // NULL if `processed_time` <> 0 (by force).
						'processed_time'   => 0 // Or, set this if already processed (or processing now).
						// For events logged as instances only (e.g. for tracking nth occurrences); use `-1`.
					);
					$columns         = $this->check_extension_arg_types(
						'integer:!empty', 'array:!empty', 'array', 'integer:!empty',
						array('null', 'integer:!empty'), 'integer', $default_columns, $columns, 3);

					$columns += $this->©event->meta_var_ids($columns['event_handler_id'], $columns['meta_vars']);
					unset($columns['user_passtag_ids']); // Ditch this; do NOT insert these.

					if(isset($columns['process_time'])) // Absolute value.
						$columns['process_time'] = abs($columns['process_time']);

					if($columns['processed_time']) // If so, nullify `process_time`.
						$columns = array_merge($columns, array('process_time' => NULL));

					$columns['unique_sha1'] = // SHA1 hash of all unique aspects.
						$this->©event->unique_sha1($columns['event_handler_id'], $columns['meta_vars']);

					$meta_vars = $columns['meta_vars']; // These go in our meta table.
					unset($columns['meta_vars']); // Ditch this (column does NOT actually exist).
					$columns['vars'] = serialize($columns['vars']); // Serialize these (always).

					insertion: // Target point for DB insertion of all columns.

					if(!$this->©db->insert($this->©db_tables->get('event_log'), $columns) || !($id = $this->©db->insert_id))
						throw $this->©exception($this->method(__FUNCTION__).'#insertion_failure', get_defined_vars(),
						                        $this->i18n('DB insertion failure.'));

					$this->update_meta_values($id, $meta_vars);

					return $id; // Event log ID.
				}

			/**
			 * Updates processed time on a specific log entry (by ID).
			 *
			 * @param integer $id A log entry ID.
			 *
			 * @param integer $time Optional. Defaults to NULL (indicating the current time).
			 *    If this is passed, it must NOT be empty (e.g. it MUST indicate the event has been processed in some way).
			 *    If logging an instance only (e.g. NOT processing; only tracking nth occurrences), set this to `-1`.
			 *
			 * @return integer The number of columns updated (should be `0` or `1` at all times).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty for some reason.
			 * @throws exception If ``$time`` is set (and empty).
			 */
			public function update_processed_time($id, $time = NULL)
				{
					$this->check_arg_types('integer:!empty', array('null', 'integer:!empty'), func_get_args());

					if(!isset($time)) $time = time(); // Defaults to the current time.

					return (integer)$this->©db->update($this->©db_tables->get('event_log'), array('processed_time' => $time), array('ID' => $id));
				}

			/**
			 * Checks if log contains previous unique instances (by event handler ID or name).
			 *
			 * @param integer|string $event_handler_id_or_name An event handler ID or name (NOT for a log entry).
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param null|integer   $reprocessing_id Optional. Defaults to a NULL value.
			 *    Integer indicates it's for a log entry that is being reprocessed after `offset_time`.
			 *    An integer causes this routine to ignore instances w/ this `ID` in the log.
			 *
			 * @return integer Total number of instances found in the event log.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_handler_id_or_name`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function has_prev_unique_sha1_instances($event_handler_id_or_name, $meta_vars, $reprocessing_id = NULL)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty',
					                       array('null', 'integer:!empty'), func_get_args());

					if(!($handler = $this->©event_handler->get($event_handler_id_or_name)))
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `%1$s`.'), $event_handler_id_or_name)
						);
					consider_reprocessing: // Consider the reprocessing of an existing log entry?

					if(!$reprocessing_id) goto calc_found_rows; // Do NOT exclude.

					$where['reprocessing'] = // Exclude this log entry from instance calculations.
						"`event_log`.`ID` != '".$this->©string->esc_sql((string)$reprocessing_id)."'";

					calc_found_rows: // Target point. Calculate found rows (e.g. instances).

					$unique_sha1 = $this->©event->unique_sha1($handler->ID, $meta_vars);

					$query = // MUST query DB to determine this each time.
						"SELECT".
						" `event_log`.`ID`". // Check found rows.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_log'))."` AS `event_log`".

						" WHERE".
						" `event_log`.`event_handler_id` = '".$this->©string->esc_sql((string)$handler->ID)."'".
						" AND `event_log`.`unique_sha1` = '".$this->©string->esc_sql($unique_sha1)."'".

						" AND `event_log`.`event_handler_id` IS NOT NULL".
						" AND `event_log`.`event_handler_id` > '0'".

						" AND `event_log`.`unique_sha1` IS NOT NULL".
						" AND `event_log`.`unique_sha1` != ''".

						((!empty($where['reprocessing'])) ? ' AND '.$where['reprocessing'] : '');

					return $this->©db_utils->calc_found_rows($query);
				}

			/**
			 * Checks if log contains previous occurrences of an event handler (by event handler ID or name).
			 *
			 * @param integer|string $event_handler_id_or_name An event handler ID or name (NOT for a log entry).
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param null|integer   $reprocessing_id Optional. Defaults to a NULL value.
			 *    Integer indicates it's for a log entry that is being reprocessed after `offset_time`.
			 *    An integer causes this routine to ignore occurrences w/ this `ID` in the log.
			 *
			 * @return integer Total number of occurrences found in the event log.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_handler_id_or_name`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function has_previous_occurrences($event_handler_id_or_name, $meta_vars, $reprocessing_id = NULL)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty',
					                       array('null', 'integer:!empty'), func_get_args());

					if(!($handler = $this->©event_handler->get($event_handler_id_or_name)))
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `%1$s`.'), $event_handler_id_or_name)
						);
					$ids = $this->©event->meta_var_ids($handler->ID, $meta_vars); // IDs from ``$meta_vars``.

					consider_user: // Consider the user in previous occurrences — (COMPLEX routine).

					if($handler->user_id === 0) // Zero indicates N/A. Don't care about the user.
						goto consider_passtag; // N/A; don't care about user in past occurrences.

					if(!isset($handler->user_id)) // NULL indicates any user (MUST have user).
						{
							if($ids['user_id']) $where['user'][] = // Search for this user's ID.
								"(`event_log`.`user_id` = '".$this->©string->esc_sql((string)$ids['user_id'])."'".
								" AND `event_log`.`user_id` IS NOT NULL".
								" AND `event_log`.`user_id` > '0')";

							if($ids['user_passtag_ids']) $where['user'][] = // Search for any of these user passtag IDs.
								"(`event_log`.`user_passtag_id` IN(".$this->©db_utils->comma_quotify($ids['user_passtag_ids']).")".
								" AND `event_log`.`user_passtag_id` IS NOT NULL".
								" AND `event_log`.`user_passtag_id` > '0')";

							if(empty($where['user'])) return 0; // Nothing to go by.

							$where['user'] = '('.implode(' OR ', $where['user']).')'; // Either/OR :-)

							goto consider_passtag; // We're good here.
						}
					if($handler->user_id < 0) // -1 indicates NO (MUST NOT have user).
						{
							$where['user'] = // Look for occurrences w/o a user reference.
								"((`event_log`.`user_id` IS NULL OR `event_log`.`user_id` <= '0')".
								" AND (`event_log`.`user_passtag_id` IS NULL OR `event_log`.`user_passtag_id` <= '0'))";

							goto consider_passtag; // We're good here.
						}
					// Any other value indicates a specific user (by ID).
					$where['user'] = // Search for the user ID in previous occurrences.
						"(`event_log`.`user_id` = '".$this->©string->esc_sql((string)$handler->user_id)."'".
						" AND `event_log`.`user_id` IS NOT NULL".
						" AND `event_log`.`user_id` > '0')";

					consider_passtag: // Consider the passtag in previous occurrences — (COMPLEX routine).

					if($handler->passtag_id === 0) // Zero indicates N/A. Don't care about the passtag.
						goto consider_reprocessing; // N/A; don't care about passtag in past occurrences.

					if(!isset($handler->passtag_id)) // NULL indicates any passtag (MUST have passtag).
						{
							if($ids['user_passtag_id']) // A user passtag ID w/ this event?
								if($handler->user_id !== 0) // NOT if we don't care about the user.
									$where['passtag'][] = // Search for this user passtag ID in previous occurrences.
										"(`event_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$ids['user_passtag_id'])."'".
										" AND `event_log`.`user_passtag_id` IS NOT NULL".
										" AND `event_log`.`user_passtag_id` > '0')";

							if($ids['passtag_id']) $where['passtag'][] = // Search for this passtag ID in previous occurrences.
								"(`event_log`.`passtag_id` = '".$this->©string->esc_sql((string)$ids['passtag_id'])."'".
								" AND `event_log`.`passtag_id` IS NOT NULL".
								" AND `event_log`.`passtag_id` > '0')";

							if(empty($where['passtag'])) return 0; // Nothing to go by.

							$where['passtag'] = '('.implode(' AND ', $where['passtag']).')'; // Both :-)

							goto consider_reprocessing; // We're good here.
						}
					if($handler->passtag_id < 0) // -1 indicates NO passtag (MUST NOT have passtag).
						{
							$where['passtag'] = // Look for occurrences w/o a passtag reference.
								"((`event_log`.`user_passtag_id` IS NULL OR `event_log`.`user_passtag_id` <= '0')".
								" AND (`event_log`.`passtag_id` IS NULL OR `event_log`.`passtag_id` <= '0'))";

							goto consider_reprocessing; // We're good here.
						}
					// Any other value indicates a specific passtag.
					if($ids['user_passtag_id']) // A user passtag ID w/ this event?
						if($handler->user_id !== 0) // NOT if we don't care about the user.
							$where['passtag'][] = // Search for this user passtag ID in previous occurrences.
								"(`event_log`.`user_passtag_id` = '".$this->©string->esc_sql((string)$ids['user_passtag_id'])."'".
								" AND `event_log`.`user_passtag_id` IS NOT NULL".
								" AND `event_log`.`user_passtag_id` > '0')";

					$where['passtag'][] = // Search for this passtag ID in previous occurrences.
						"(`event_log`.`passtag_id` = '".$this->©string->esc_sql((string)$handler->passtag_id)."'".
						" AND `event_log`.`passtag_id` IS NOT NULL".
						" AND `event_log`.`passtag_id` > '0')";

					$where['passtag'] = '('.implode(' AND ', $where['passtag']).')'; // Both :-)

					consider_reprocessing: // Consider the reprocessing of an existing log entry?

					if(!$reprocessing_id) goto calc_found_rows; // Nope, NOT reprocessing.

					$where['reprocessing'] = "`event_log`.`ID` != '".$this->©string->esc_sql((string)$reprocessing_id)."'";

					calc_found_rows: // Calculate found rows (e.g. occurrences).

					$query = // MUST query DB to determine this each time.
						"SELECT".
						" `event_log`.`ID`". // Check found rows.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_log'))."` AS `event_log`".

						" WHERE".
						" `event_log`.`event_handler_id` = '".$this->©string->esc_sql((string)$handler->ID)."'".
						" AND `event_log`.`event_handler_id` IS NOT NULL".
						" AND `event_log`.`event_handler_id` > '0'".

						((!empty($where['user'])) ? ' AND '.$where['user'] : '').
						((!empty($where['passtag'])) ? ' AND '.$where['passtag'] : '').
						((!empty($where['reprocessing'])) ? ' AND '.$where['reprocessing'] : '');

					return $this->©db_utils->calc_found_rows($query);
				}

			/**
			 * Gets a meta value associated with an event log entry.
			 *
			 * @param integer $id A log entry ID.
			 *
			 * @param string  $name Name of a meta value that we're seeking.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_value($id, $name)
				{
					$value = $this->©db_utils->get_meta_values('event_log_meta', 'event_log_id', $id, (string)$name);

					return $this->_unpack_meta_values($value, (string)$name);
				}

			/**
			 * Gets meta value(s) associated with an event log entry.
			 *
			 * @param integer      $id A log entry ID.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_values($id, $names = self::all)
				{
					$values = $this->©db_utils->get_meta_values('event_log_meta', 'event_log_id', $id, $names);

					return $this->_unpack_meta_values($values);
				}

			/**
			 * Inserts (or updates) a meta value associated with an event log entry.
			 *
			 * @param integer $id A log entry ID.
			 *
			 * @param string  $name The name for this meta value (e.g. it's key in the database).
			 *
			 * @param mixed   $value The value for this meta entry.
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_value($id, $name, $value)
				{
					return $this->©db_utils->update_meta_values('event_log_meta', 'event_log_id', $id, array((string)$name => $value));
				}

			/**
			 * Inserts (or updates) meta value(s) associated with an event log entry.
			 *
			 * @param integer $id A log entry ID.
			 *
			 * @param array   $values Associative array of meta values (e.g. key/value pairs).
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_values($id, $values)
				{
					return $this->©db_utils->update_meta_values('event_log_meta', 'event_log_id', $id, $values);
				}

			/**
			 * Deletes a meta value associated with an event log entry.
			 *
			 * @param integer $id A log entry ID.
			 *
			 * @param string  $name The name of a meta value (e.g. it's key in the database).
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_value($id, $name)
				{
					return $this->©db_utils->delete_meta_values('event_log_meta', 'event_log_id', $id, (string)$name);
				}

			/**
			 * Deletes meta value(s) associated with an event log entry.
			 *
			 * @param integer      $id A log entry ID.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_values($id, $names = self::all)
				{
					return $this->©db_utils->delete_meta_values('event_log_meta', 'event_log_id', $id, $names);
				}

			/**
			 * Unpacks meta values (deals w/ data types in ``$meta_vars``).
			 *
			 * @param mixed       $values See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @param null|string $single_name Optional. Defaults to a NULL value.
			 *    If this is provided, we return a single meta value only (and NOT an array of values).
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws exception See {@link db_utils::get_meta_values()}.
			 */
			protected function _unpack_meta_values($values, $single_name = NULL)
				{
					if(isset($single_name)) $values = array((string)$single_name => $values);

					foreach($values as $_name => &$_value) switch($_name) // Switch key/name.
					{
						case 'user': // Creates new object instance from stored array.

								if(!is_array($_value)) goto user_finale;
								if(!$this->©array->is_not_empty($_value['args']))
									goto user_finale; // MUST have args.

								$_default_properties = array(); // Initialize.
								foreach($_value as $_property => $_property_value)
									if(array_key_exists($_property, $this->©user_utils->basic_data_defaults, TRUE))
										$_default_properties[$_property] = $_property_value;

								if($this->©array->is_not_empty($_value['data']['access_keys']))
									$_default_properties['data']['access_keys'] = $_value['data']['access_keys'];

								$_value = $this->©user($_value['args']['user_id'], // Where the magic happens :-)
								                       $_value['args']['by'], $_value['args']['value'], $_default_properties);

								unset($_default_properties, $_property, $_property_value); // Housekeeping.

								user_finale: // Target point. Check user value & break.

								if(!($_value instanceof users)) $_value = NULL;

								break; // Break switch handler.

						case 'user_passtag': // Rebuild objects.
						case 'passtag': // Same goes for the passtag.

								if($this->©array->is_not_empty($_value))
									$_value = (object)$_value;
								else $_value = NULL; // Default value.

								break; // Break switch handler.

						case 'aggregate_user_passtags':

								if($this->©array->is_not_empty($_value))
									{
										foreach($_value as $_key => &$_user_passtag)
											{
												if($this->©array->is_not_empty($_user_passtag))
													$_user_passtag = (object)$_user_passtag;
												else unset($_value[$_key]); // Remove.
											}
										unset($_key, $_user_passtag); // Housekeeping.

										if(!$_value) $_value = NULL; // If empty.
									}
								else $_value = NULL; // Default value.

								break; // Break switch handler.

						case 'order_session_id':
						case 'transaction_id':
						case 'futuristic_time':

								if(is_numeric($_value) && (integer)$_value)
									$_value = (integer)$_value;
								else $_value = NULL;

								break; // Break switch handler.

						case 'unique_sha1':

								if(!$_value) $_value = NULL;

								break; // Break switch handler.
					}
					unset($_name, $_value); // A little housekeeping.

					return (isset($single_name)) ? $values[(string)$single_name] : $values;
				}
		}
	}