<?php
/**
 * Event Types.
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
		 * Event Types.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_types extends framework
		{
			/**
			 * @var array Disabled event types at runtime (by ID).
			 * @see events::trigger()
			 */
			protected $disabled = array(); // Disable NO event types (default).

			/**
			 * Checks if an event type is disabled at runtime.
			 *
			 * @param integer|string $id_or_type The ID of an event type (or the type itself).
			 *
			 * @return boolean TRUE if the event type is disabled at runtime.
			 *    This will return FALSE if you attempt to check an invalid event type.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_type`` is empty.
			 */
			public function is_disabled($id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!($type = $this->get($id_or_type))) return FALSE; // Unknown type.

					return isset($this->disabled[$type->ID]);
				}

			/**
			 * Checks if an event type is enabled (e.g. is NOT disabled).
			 *
			 * @param integer|string $id_or_type The ID of an event type (or the type itself).
			 *
			 * @return boolean TRUE if the event type is enabled (e.g. is NOT disabled).
			 *    This will also return FALSE if you attempt to check an invalid event type.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_type`` is empty.
			 */
			public function is_enabled($id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					if(!($type = $this->get($id_or_type))) return FALSE; // Unknown type.

					return !isset($this->disabled[$type->ID]);
				}

			/**
			 * Disables certain event types at runtime.
			 *
			 * @param string|array $types Optional. Defaults to {@link fw_constants::all}.
			 *    An array should include each type of event (type or ID) you want to disable.
			 *    Or, use {@link fw_constants::all} to disable ALL event types.
			 *
			 * @return array Returns the current array of DISABLED event types.
			 *
			 * @note This does NOT override anything that is ALREADY disabled (e.g. enables nothing).
			 *    This simply adds new disabled event types to any that may already exist.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$types`` is NOT an array; and NOT {@link fw_constants::all}.
			 */
			public function disable($types = self::all)
				{
					$this->check_arg_types(array('array', 'string:!empty'), func_get_args());

					if(!is_array($types) && $types !== $this::all)
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_types', get_defined_vars(),
						                        sprintf($this->i18n('Invalid types: `$1%s`.'), $types)
						);
					if(($event_types = $this->get_all())) foreach($event_types['by_id'] as $_type)
						{
							if($types === $this::all // Disabling ALL types?
							   || in_array($_type->ID, $types, TRUE) // By type ID?
							   || in_array($_type->type, $types, TRUE) // By type name?
							) // We use type IDs as keys to prevent duplicates.
								$this->disabled[$_type->ID] = $_type->ID;
						}
					unset($_type); // Housekeeping.

					return $this->disabled;
				}

			/**
			 * Enables certain event types at runtime.
			 *
			 * @param string|array $types Optional. Defaults to {@link fw_constants::all}.
			 *    An array should include each type of event (type or ID) you want to enable.
			 *    Or, use {@link fw_constants::all} to enable ALL event types.
			 *
			 * @return array Returns the current array of ENABLED event types.
			 *
			 * @note This does NOT override anything that is ALREADY enabled (e.g. disables nothing).
			 *    Simply removes event types from those which are disabled for some reason.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$types`` is NOT an array; and is NOT {@link fw_constants::all}.
			 */
			public function enable($types = self::all)
				{
					$this->check_arg_types(array('array', 'string:!empty'), func_get_args());

					if(!is_array($types) && $types !== $this::all)
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_types', get_defined_vars(),
						                        sprintf($this->i18n('Invalid types: `$1%s`.'), $types)
						);
					$enabled = array(); // Initialize array of enabled event types.
					if(($event_types = $this->get_all())) foreach($event_types['by_id'] as $_type)
						{
							if($types === $this::all // Enabling ALL types?
							   || in_array($_type->ID, $types, TRUE) // By type ID?
							   || in_array($_type->type, $types, TRUE) // By type name?
							) // We use type IDs as keys to make this simple.
								unset($this->disabled[$_type->ID]);

							if(!isset($this->disabled[$_type->ID]))
								$enabled[$_type->ID] = $_type->ID;
						}
					unset($_type); // Housekeeping.

					return $enabled;
				}

			/**
			 * Gets an event type ID.
			 *
			 * @param string $type Type of event (i.e. the event's name).
			 *
			 * @return integer The event type ID, else `0` if event type does not exist.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$type`` is empty.
			 */
			public function id($type)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$type]))
						return $this->cache[__FUNCTION__][$type];

					$this->cache[__FUNCTION__][$type] = 0;

					if(($get = $this->get($type)))
						$this->cache[__FUNCTION__][$type] = $get->ID;

					return $this->cache[__FUNCTION__][$type];
				}

			/**
			 * Gets an event type.
			 *
			 * @param integer $id Event type ID.
			 *
			 * @return string The event type string (e.g. by name); else an empty string on failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function type($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$id]))
						return $this->cache[__FUNCTION__][$id];

					$this->cache[__FUNCTION__][$id] = '';

					if(($get = $this->get($id)))
						$this->cache[__FUNCTION__][$id] = $get->type;

					return $this->cache[__FUNCTION__][$id];
				}

			/**
			 * Gets a specific event type.
			 *
			 * @param integer|string $id_or_type The ID of an event type (or the type itself).
			 *
			 * @return null|object An event type object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_type`` is empty.
			 */
			public function get($id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$types = $this->get_all();

					if(is_integer($id_or_type) && isset($types['by_id'][$id_or_type]))
						return $types['by_id'][$id_or_type];

					if(is_string($id_or_type) && isset($types['by_type'][$id_or_type]))
						return $types['by_type'][$id_or_type];

					return NULL; // Default return value.
				}

			/**
			 * Gets all event types.
			 *
			 * @return array All event types.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$event_types = array();

					$query =
						"SELECT".
						" `event_types`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_types'))."` AS `event_types`".

						" WHERE `event_types`.`type` IS NOT NULL".
						" AND `event_types`.`type` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$event_types['by_id'][$_result->ID]     = $_result;
									$event_types['by_type'][$_result->type] =& $event_types['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $event_types);
				}
		}
	}