<?php
/**
 * Event Behaviors.
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
		 * Event Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_behaviors extends framework
		{
			/**
			 * Gets a specific event behavior.
			 *
			 * @param integer $id The ID of an event behavior.
			 *
			 * @return null|object An event behavior object, else NULL.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function get($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$behaviors = $this->get_all();

					if(isset($behaviors['by_id'][$id]))
						return $behaviors['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets all behaviors for a specific event handler (by ID or name).
			 *
			 * @param integer|string $event_handler_id_or_name The ID (or name) of an event handler.
			 *
			 * @return array An array of event behaviors; else an empty array on failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_handler_id_or_name`` is empty.
			 */
			public function for_($event_handler_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$behaviors = $this->get_all();

					if(!($handler = $this->©event_handler->get($event_handler_id_or_name))) // Missing?
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `$1%s`.'), $event_handler_id_or_name));

					if(isset($behaviors['by_event_handler_id'][$handler->ID]))
						return $behaviors['by_event_handler_id'][$handler->ID];

					return array(); // Default return value.
				}

			/**
			 * Gets all event behaviors.
			 *
			 * @return array All event behaviors.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$event_behaviors = array();

					$query =
						"SELECT".
						" `behavior_types`.`type` AS `behavior_type`,".
						" `event_behaviors`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('behavior_types'))."` AS `behavior_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_behaviors'))."` AS `event_behaviors`".

						" WHERE ".
						" `event_behaviors`.`behavior_type_id` = `behavior_types`.`ID`".
						" AND `event_behaviors`.`behavior_type_id` IS NOT NULL".
						" AND `event_behaviors`.`behavior_type_id` >= '0'".

						" AND `behavior_types`.`type` IS NOT NULL".
						" AND `behavior_types`.`type` != ''".

						" AND `event_behaviors`.`event_handler_id` IS NOT NULL".
						" AND `event_behaviors`.`event_handler_id` > '0'".

						" ORDER BY `event_behaviors`.`order` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							$_default_behavior_type_id = $this->©behavior_type->id('default');

							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->behavior_type_id === $_default_behavior_type_id)
										{
											$_result->behavior_type    = 'none'; // No behavior.
											$_result->behavior_type_id = 0; // There is no behavior.
										}
									$event_behaviors['by_id'][$_result->ID]                                           = $_result;
									$event_behaviors['by_behavior_type_id'][$_result->behavior_type_id][$_result->ID] =& $event_behaviors['by_id'][$_result->ID];
									$event_behaviors['by_behavior_type'][$_result->behavior_type][$_result->ID]       =& $event_behaviors['by_id'][$_result->ID];
									$event_behaviors['by_event_handler_id'][$_result->event_handler_id][$_result->ID] =& $event_behaviors['by_id'][$_result->ID];
								}
							unset($_default_behavior_type_id, $_result); // Just a little housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $event_behaviors);
				}
		}
	}