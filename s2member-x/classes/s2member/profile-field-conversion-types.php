<?php
/**
 * Profile Field Conversion Types.
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
		 * Profile Field Conversion Types.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_field_conversion_types extends framework
		{
			/**
			 * Gets a profile field conversion type ID.
			 *
			 * @param string $type Type of profile field conversion (i.e. the profile field conversion's name).
			 *
			 * @return integer The profile field conversion type ID, else `0` if profile field conversion type does not exist.
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
			 * Gets a profile field conversion type.
			 *
			 * @param integer $id A profile field conversion type ID.
			 *
			 * @return string The profile field conversion type string (e.g. by name); else an empty string on failure.
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
			 * Gets a specific profile field conversion type.
			 *
			 * @param integer|string $id_or_type The ID of a profile field conversion type (or the type itself).
			 *
			 * @return null|object An profile field conversion type object, else NULL if unavailable.
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
			 * Gets all profile field conversion types.
			 *
			 * @return array All profile field conversion types.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_conversion_types = array();

					$query =
						"SELECT".
						" `profile_field_conversion_types`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_conversion_types'))."` AS `profile_field_conversion_types`".

						" WHERE `profile_field_conversion_types`.`type` IS NOT NULL".
						" AND `profile_field_conversion_types`.`type` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_conversion_types['by_id'][$_result->ID]     = $_result;
									$profile_field_conversion_types['by_type'][$_result->type] =& $profile_field_conversion_types['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_conversion_types);
				}
		}
	}