<?php
/**
 * Event Behaviors (Base Class).
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
		 * Event Behaviors (Base Class).
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		abstract class event_behaviors_base extends framework
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = ''; // MUST be set by extenders.

			/**
			 * Event Behaviors Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 *
			 * @throws exception If ``$this->type`` is empty.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					if(!$this->©string->is_not_empty($this->type))
						throw $this->©exception( // This should NOT happen!
							$this->method(__FUNCTION__).'#type_missing', get_defined_vars(),
							$this->i18n('Invalid class extender. Missing event behavior type.')
						);
				}

			/**
			 * Processes all behaviors of this type (for an event behavior ID).
			 *
			 * @param integer|string $event_behavior_id A specific event behavior ID.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_behavior_id`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function process_all($event_behavior_id, $meta_vars, $vars)
				{
					$this->check_arg_types('integer:!empty', 'array:!empty', 'array', func_get_args());

					foreach($this->for_($event_behavior_id) as $_behavior)
						if($_behavior->status !== 'deleted') $this->process($_behavior->ID, $meta_vars, $vars);
					unset($_behavior); // Housekeeping.
				}

			/**
			 * Processes a specific behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of a behavior.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			abstract public function process($id_or_name, $meta_vars, $vars);

			/**
			 * Behaviors of this type; for an event behavior ID.
			 *
			 * @param integer $event_behavior_id An event behavior ID.
			 *
			 * @return array An array of behavior objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_behavior_id`` is empty.
			 */
			public function for_($event_behavior_id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$behaviors = $this->get_all();

					if(isset($behaviors['by_event_behavior_id'][$event_behavior_id]))
						return $behaviors['by_event_behavior_id'][$event_behavior_id];

					return array(); // Default return value.
				}

			/**
			 * Gets a specific behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of a behavior.
			 *
			 * @return null|object A behavior object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$behaviors = $this->get_all();

					if(is_integer($id_or_name) && isset($behaviors['by_id'][$id_or_name]))
						return $behaviors['by_id'][$id_or_name];

					if(is_string($id_or_name) && isset($behaviors['by_name'][$id_or_name]))
						return $behaviors['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets all behaviors.
			 *
			 * @return array All behaviors.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$behaviors = array(); // Initialize.

					$query =
						"SELECT".
						" `event_behaviors`.`event_handler_id` AS `event_handler_id`,".
						" `event_'.$this->type.'_behaviors`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_behaviors'))."` AS `event_behaviors`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_'.$this->type.'_behaviors'))."` AS `event_'.$this->type.'_behaviors`".

						" WHERE".
						" `event_'.$this->type.'_behaviors`.`event_behavior_id` = `event_behaviors`.`ID`". // For behavior (by ID).
						" AND `event_'.$this->type.'_behaviors`.`event_behavior_id` IS NOT NULL".
						" AND `event_'.$this->type.'_behaviors`.`event_behavior_id` > '0'".

						" AND `event_behaviors`.`event_handler_id` IS NOT NULL".
						" AND `event_behaviors`.`event_handler_id` > '0'".

						" AND `event_'.$this->type.'_behaviors`.`name` IS NOT NULL".
						" AND `event_'.$this->type.'_behaviors`.`name` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$behaviors['by_id'][$_result->ID]                                             = $_result;
									$behaviors['by_name'][$_result->name]                                         =& $behaviors['by_id'][$_result->ID];
									$behaviors['by_event_behavior_id'][$_result->event_behavior_id][$_result->ID] =& $behaviors['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $behaviors);
				}
		}
	}