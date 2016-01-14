<?php
/**
 * Profile Field Permissions.
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
		 * Profile Field Permissions.
		 *
		 * @package s2Member\Profile_Fields
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class profile_field_permissions extends framework
		{
			/**
			 * Gets a specific profile field permission.
			 *
			 * @param integer $id The ID of a profile field permission.
			 *
			 * @return null|object A profile field permission object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function get($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$permissions = $this->get_all();

					if(isset($permissions['by_id'][$id]))
						return $permissions['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets profile field permissions for a specific profile field ID.
			 *
			 * @param integer|string $profile_field_id_or_name The ID (or name) of a profile field.
			 *
			 * @return array An array of profile field permission objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id_or_name`` is empty.
			 */
			public function for_($profile_field_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$permissions = $this->get_all();

					if(is_integer($profile_field_id_or_name) && isset($permissions['by_profile_field_id'][$profile_field_id_or_name]))
						return $permissions['by_profile_field_id'][$profile_field_id_or_name];

					if(is_string($profile_field_id_or_name) && isset($permissions['by_profile_field_name'][$profile_field_id_or_name]))
						return $permissions['by_profile_field_name'][$profile_field_id_or_name];

					return array(); // Default return value.
				}

			/**
			 * Gets all profile field permissions.
			 *
			 * @return array All profile field permissions.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$profile_field_permissions = array();

					$query =
						"SELECT".
						" `profile_fields`.`name` AS `profile_field_name`,".
						" `profile_field_permissions`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_fields'))."` AS `profile_fields`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('profile_field_permissions'))."` AS `profile_field_permissions`".

						" WHERE".
						" `profile_field_permissions`.`profile_field_id` = `profile_fields`.`ID`".

						" AND `profile_fields`.`name` IS NOT NULL".
						" AND `profile_fields`.`name` != ''".

						" AND `profile_field_permissions`.`profile_field_id` IS NOT NULL".
						" AND `profile_field_permissions`.`profile_field_id` > '0'";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$profile_field_permissions['by_id'][$_result->ID]                                               = $_result;
									$profile_field_permissions['by_profile_field_id'][$_result->profile_field_id][$_result->ID]     =& $profile_field_permissions['by_id'][$_result->ID];
									$profile_field_permissions['by_profile_field_name'][$_result->profile_field_name][$_result->ID] =& $profile_field_permissions['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $profile_field_permissions);
				}
		}
	}