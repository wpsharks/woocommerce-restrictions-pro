<?php
/**
 * Profile Field Validations.
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
		 * Profile Field Validations.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_field_validations extends framework
		{
			/**
			 * Gets a specific profile field validation.
			 *
			 * @param integer $id The ID of a profile field validation.
			 *
			 * @return null|object A profile field validation object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function get($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$validations = $this->get_all();

					if(isset($validations['by_id'][$id]))
						return $validations['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets profile field validations for a specific profile field ID.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @return array An array of profile field validation objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function for_($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$validations = $this->get_all();

					if(is_integer($profile_field_id_or_name) && isset($validations['by_profile_field_id'][$profile_field_id_or_name]))
						return $validations['by_profile_field_id'][$profile_field_id_or_name];

					if(is_string($profile_field_id_or_name) && isset($validations['by_profile_field_name'][$profile_field_id_or_name]))
						return $validations['by_profile_field_name'][$profile_field_id_or_name];

					return array(); // Default return value.
				}

			/**
			 * Gets all profile field validations.
			 *
			 * @return array All profile field validations.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_validations = array();

					$query =
						"SELECT".
						" `profile_fields`.`name` AS `profile_field_name`,".
						" `profile_field_validations`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_validation_patterns'))."` AS `profile_field_validation_patterns`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_validations'))."` AS `profile_field_validations`".

						" WHERE".
						" `profile_field_validations`.`profile_field_id` = `profile_fields`.`ID`".
						" AND `profile_field_validations`.`profile_field_validation_pattern_id` = `profile_field_validation_patterns`.`ID`".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_field_validations`.`profile_field_id` IS NOT NULL".
						" AND `profile_field_validations`.`profile_field_id` > '0'".

						" AND `profile_field_validations`.`profile_field_validation_pattern_id` IS NOT NULL".
						" AND `profile_field_validations`.`profile_field_validation_pattern_id` > '0'";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_validations['by_id'][$_result->ID]                                               = $_result;
									$profile_field_validations['by_profile_field_id'][$_result->profile_field_id][$_result->ID]     =& $profile_field_validations['by_id'][$_result->ID];
									$profile_field_validations['by_profile_field_name'][$_result->profile_field_name][$_result->ID] =& $profile_field_validations['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_validations);
				}
		}
	}