<?php
/**
 * Profile Field Conversions.
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
		 * Profile Field Conversions.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_field_conversions extends framework
		{
			/**
			 * Processes profile field conversions for a specific profile field/value.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @param string|array   $value Unconverted profile field value.
			 *    If the value is an array, it should contain a single dimension with all string values.
			 *    ~ This routine will always enforce a single dimension and all string values in any array ``$value``.
			 *
			 * @return string|array The ``$value`` with conversions having been processed now.
			 */
			public function process($profile_field_id_or_name, $value)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), array('string', 'array'), func_get_args());

					if(is_array($value)) $value = $this->©array->to_one_dimension($value); // Force ONE dimension only.

					foreach($this->for_($profile_field_id_or_name) as $_conversion)
						{
							switch($_conversion->type) // Conversions by type.
							{
								case 'uppercase':

										if(is_array($value))
											$value = array_map('strtoupper', $value);
										else $value = strtoupper($value);

										break; // Break switch handler.

								case 'lowercase':

										if(is_array($value))
											$value = array_map('strtolower', $value);
										else $value = strtolower($value);

										break; // Break switch handler.

								case 'ucfirst':

										if(is_array($value))
											$value = array_map('ucfirst', $value);
										else $value = ucfirst($value);

										break; // Break switch handler.

								case 'ucwords':

										if(is_array($value))
											$value = array_map('ucwords', $value);
										else $value = ucwords($value);

										break; // Break switch handler.

								default: // Default case handler (do nothing).

									break; // Break switch handler.
							}
						}
					return $value; // With conversions applied now.
				}

			/**
			 * Gets a specific profile field conversion.
			 *
			 * @param integer $id The ID of a profile field conversion.
			 *
			 * @return null|object A profile field conversion object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function get($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$conversions = $this->get_all();

					if(isset($conversions['by_id'][$id]))
						return $conversions['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets profile field conversions for a specific profile field ID.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @return array An array of profile field conversion objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function for_($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$conversions = $this->get_all();

					if(is_integer($profile_field_id_or_name) && isset($conversions['by_profile_field_id'][$profile_field_id_or_name]))
						return $conversions['by_profile_field_id'][$profile_field_id_or_name];

					if(is_string($profile_field_id_or_name) && isset($conversions['by_profile_field_name'][$profile_field_id_or_name]))
						return $conversions['by_profile_field_name'][$profile_field_id_or_name];

					return array(); // Default return value.
				}

			/**
			 * Gets all profile field conversions.
			 *
			 * @return array All profile field conversions.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_conversions = array();

					$query =
						"SELECT".
						" `profile_fields`.`name` AS `profile_field_name`,".
						" `profile_field_conversion_types`.`type` AS `type`,".
						" `profile_field_conversions`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_conversion_types'))."` AS `profile_field_conversion_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_conversions'))."` AS `profile_field_conversions`".

						" WHERE".
						" `profile_field_conversions`.`profile_field_id` = `profile_fields`.`ID`".
						" AND `profile_field_conversions`.`profile_field_conversion_type_id` = `profile_field_conversion_types`.`ID`".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_field_conversion_types`.`type` IS NOT NULL".
						" AND `profile_field_conversion_types`.`type` != ''".

						" AND `profile_field_conversions`.`profile_field_id` IS NOT NULL".
						" AND `profile_field_conversions`.`profile_field_id` > '0'".

						" AND `profile_field_conversions`.`profile_field_conversion_type_id` IS NOT NULL".
						" AND `profile_field_conversions`.`profile_field_conversion_type_id` > '0'";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_conversions['by_id'][$_result->ID]                                               = $_result;
									$profile_field_conversions['by_profile_field_id'][$_result->profile_field_id][$_result->ID]     =& $profile_field_conversions['by_id'][$_result->ID];
									$profile_field_conversions['by_profile_field_name'][$_result->profile_field_name][$_result->ID] =& $profile_field_conversions['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_conversions);
				}
		}
	}