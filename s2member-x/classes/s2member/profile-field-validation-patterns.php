<?php
/**
 * Profile Field Validation Patterns.
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
		 * Profile Field Validation Patterns.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_field_validation_patterns extends framework
		{
			/**
			 * Gets a specific profile field validation pattern.
			 *
			 * @param integer|string $id_or_name The ID (or name) of a profile field validation pattern.
			 *
			 * @return null|object A profile field validation pattern object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$validation_patterns = $this->get_all();

					if(is_integer($id_or_name) && isset($validation_patterns['by_id'][$id_or_name]))
						return $validation_patterns['by_id'][$id_or_name];

					if(is_string($id_or_name) && isset($validation_patterns['by_name'][$id_or_name]))
						return $validation_patterns['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets a specific profile field validation pattern (integrates w/ {@link form_fields}).
			 *
			 * @param integer|string $id_or_name The ID (or name) of a profile field validation pattern.
			 *
			 * @param string         $regex_flavor Optional. Defaults to ``fw_constants::regex_js``.
			 *    Or, this can also be set to ``fw_constants::regex_php`` for a PHP version of the regex pattern.
			 *
			 * @return array A profile field validation pattern array (integrates w/ {@link form_fields}), else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function for_form_field($id_or_name, $regex_flavor = self::regex_js)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'string:!empty', func_get_args());

					if(($validation_pattern = $this->get($id_or_name)))
						return array(
							'name'         => $validation_pattern->name,
							'description'  => $validation_pattern->description,
							'regex'        => (($regex_flavor === $this::regex_php) ? $validation_pattern->regex_php : $validation_pattern->regex_js),
							'minimum'      => $validation_pattern->minimum,
							'maximum'      => $validation_pattern->maximum,
							'min_max_type' => $validation_pattern->min_max_type
						);
					return array(); // Default return value.
				}

			/**
			 * Gets all profile field validation patterns.
			 *
			 * @return array All profile field validation patterns.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_validation_patterns = array();

					$query =
						"SELECT".
						" `profile_field_validation_patterns`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_validation_patterns'))."` AS `profile_field_validation_patterns`".

						" WHERE".
						" `profile_field_validation_patterns`.`name` IS NOT NULL".
						" AND `profile_field_validation_patterns`.`name` != ''".

						" AND `profile_field_validation_patterns`.`label` IS NOT NULL".
						" AND `profile_field_validation_patterns`.`label` != ''".

						" AND `profile_field_validation_patterns`.`description` IS NOT NULL".
						" AND `profile_field_validation_patterns`.`description` != ''".

						" AND `profile_field_validation_patterns`.`regex_php` IS NOT NULL".
						" AND `profile_field_validation_patterns`.`regex_php` != ''".

						" AND `profile_field_validation_patterns`.`regex_js` IS NOT NULL".
						" AND `profile_field_validation_patterns`.`regex_js` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_validation_patterns['by_id'][$_result->ID]     = $_result;
									$profile_field_validation_patterns['by_name'][$_result->name] =& $profile_field_validation_patterns['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_validation_patterns);
				}
		}
	}