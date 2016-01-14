<?php
/**
 * ESPs.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Email_Service_Providers
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * ESPs.
		 *
		 * @package s2Member\Email_Service_Providers
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class esps extends framework
		{
			/**
			 * Gets an ESP ID (by name).
			 *
			 * @param string $name An ESP (by name).
			 *
			 * @return integer The ESP ID, else `0` if ESP does not exist.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$name`` is empty.
			 */
			public function id($name)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$name]))
						return $this->cache[__FUNCTION__][$name];

					$this->cache[__FUNCTION__][$name] = 0;

					if(($esp = $this->get($name)))
						$this->cache[__FUNCTION__][$name] = $esp->ID;

					return $this->cache[__FUNCTION__][$name];
				}

			/**
			 * Gets an ESP name (by ID).
			 *
			 * @param integer $id An ESP (by ID).
			 *
			 * @return string The ESP name; else an empty string.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function name($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					if(isset($this->cache[__FUNCTION__][$id]))
						return $this->cache[__FUNCTION__][$id];

					$this->cache[__FUNCTION__][$id] = '';

					if(($esp = $this->get($id)))
						$this->cache[__FUNCTION__][$id] = $esp->name;

					return $this->cache[__FUNCTION__][$id];
				}

			/**
			 * Gets a specific ESP (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an ESP.
			 *
			 * @return null|object An ESP object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$esps = $this->get_all(); // Gets all ESPs from the database.

					if(is_integer($id_or_name) && isset($esps['by_id'][$id_or_name]))
						return $esps['by_id'][$id_or_name];

					if(is_string($id_or_name) && isset($esps['by_name'][$id_or_name]))
						return $esps['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets all ESPs.
			 *
			 * @return array An array of all ESPs.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$esps = array();

					$query =
						"SELECT".
						" `esps`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('esps'))."` AS `esps`".

						" WHERE".
						" `esps`.`name` IS NOT NULL".
						" AND `esps`.`name` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$esps['by_id'][$_result->ID]     = $_result;
									$esps['by_name'][$_result->name] =& $esps[$_result->ID];
								}
							unset($_result);
						}
					return $this->©db_cache->update($db_cache_key, $esps);
				}
		}
	}