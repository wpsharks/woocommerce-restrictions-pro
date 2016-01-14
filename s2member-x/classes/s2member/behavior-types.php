<?php
/**
 * Behavior Types.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Behaviors
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Behavior Types.
		 *
		 * @package s2Member\Behaviors
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class behavior_types extends framework
		{
			/**
			 * Gets a behavior type ID.
			 *
			 * @param string $type Type of behavior (i.e. the behavior's name).
			 *
			 * @return integer The behavior type ID, else `0` if behavior type does not exist.
			 *    A value of `0` also indicates there is NO behavior (e.g. it IS a real behavior type ID).
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
			 * Gets a behavior type ID.
			 *
			 * @param integer $id Behavior type ID. This can be empty (e.g. `0`).
			 *    Since there is a type `0`, we DO allow integer `0` in this parameter.
			 *
			 * @return string The behavior type string (e.g. by name); else an empty string on failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function type($id)
				{
					$this->check_arg_types('integer', func_get_args());

					if(isset($this->cache[__FUNCTION__][$id]))
						return $this->cache[__FUNCTION__][$id];

					$this->cache[__FUNCTION__][$id] = '';

					if(($get = $this->get($id)))
						$this->cache[__FUNCTION__][$id] = $get->type;

					return $this->cache[__FUNCTION__][$id];
				}

			/**
			 * Gets a specific behavior type.
			 *
			 * @param integer|string $id_or_type The ID of a behavior type (or the type itself).
			 *    Since there is a type `0`, we DO allow integer `0` in this parameter.
			 *
			 * @return null|object A behavior type object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_type`` is empty (but we DO allow integer `0` here).
			 */
			public function get($id_or_type)
				{
					$this->check_arg_types(array('integer', 'string:!empty'), func_get_args());

					$types = $this->get_all();

					if(is_integer($id_or_type) && isset($types['by_id'][$id_or_type]))
						return $types['by_id'][$id_or_type];

					if(is_string($id_or_type) && isset($types['by_type'][$id_or_type]))
						return $types['by_type'][$id_or_type];

					return NULL; // Default return value.
				}

			/**
			 * Gets all behavior types.
			 *
			 * @return array All behavior types.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$behavior_types = array();

					$query =
						"SELECT".
						" `behavior_types`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('behavior_types'))."` AS `behavior_types`".

						" WHERE `behavior_types`.`type` IS NOT NULL".
						" AND `behavior_types`.`type` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$behavior_types['by_id'][$_result->ID]     = $_result;
									$behavior_types['by_type'][$_result->type] =& $behavior_types['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $behavior_types);
				}
		}
	}