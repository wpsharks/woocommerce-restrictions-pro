<?php
/**
 * Passtag WordPress® Caps.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Passtags
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Passtag WordPress® Caps.
		 *
		 * @package s2Member\Passtags
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class passtag_wp_caps extends framework
		{
			/**
			 * Handles WordPress® `init` hook.
			 *
			 * @attaches-to WordPress® hook `init`.
			 * @hook-priority `-2` Before EVERYTHING else.
			 */
			public function init()
				{
					add_filter('user_has_cap', array($this, 'user_has_cap'), 10, 3);
				}

			/**
			 * Handles WordPress® filter `user_has_cap`.
			 *
			 * @attaches-to WordPress® filter `user_has_cap`.
			 * @hook-priority Default is fine here.
			 *
			 * @param array $capabilities Array of all user capabilities (e.g. the ``WP_User->allcaps`` property).
			 *
			 * @param array $map Map created by WordPress® via ``map_meta_cap()`` function in WordPress®.
			 *
			 * @param array $args Capability and user ID; plus arguments passed to ``WP_User->has_cap()`` function in WordPress®.
			 *    These arguments always have two extra keys added by WordPress®. The first two arguments are the
			 *    capability and the user ID being tested. Then, key `2` begins any other function arguments.
			 *
			 * @return array Array of all user capabilities (e.g. filtered ``WP_User->allcaps`` property).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 *
			 * @note This impacts users w/o user IDs too (e.g. users that are NOT logged in).
			 *    You do NOT need to be logged into the site in order to gain access to a capability this way.
			 */
			public function user_has_cap($capabilities, $map, $args)
				{
					$this->check_arg_types('array', 'array', 'array:!empty', func_get_args());

					$cap     = $args[0]; // The capability being tested by WordPress®.
					$user_id = $args[1]; // The user ID being tested by WordPress®.

					if($user_id && empty($capabilities[$cap]) && ($passtag_wp_caps = $this->get_all()))
						if(!empty($passtag_wp_caps['by_wp_cap'][$cap]['passtag_ids']))
							{
								$user = $this->©user_utils->which($user_id);
								if($user->can_access_any_passtag($passtag_wp_caps['by_wp_cap'][$cap]['passtag_ids']))
									$capabilities[$cap] = TRUE;
							}
					return $capabilities;
				}

			/**
			 * Gets map of all passtags to WordPress® capabilities.
			 *
			 * @return array Map of all passtags to WordPress® capabilities.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$passtag_wp_caps = array();

					$query =
						"SELECT".
						" `passtag_wp_caps`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtags'))."` AS `passtags`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtag_wp_caps'))."` AS `passtag_wp_caps`".

						" WHERE".
						" `passtag_wp_caps`.`passtag_id` = `passtags`.`ID`".

						" AND `passtag_wp_caps`.`wp_cap` IS NOT NULL".
						" AND `passtag_wp_caps`.`wp_cap` != ''".

						" AND `passtag_wp_caps`.`passtag_id` IS NOT NULL".
						" AND `passtag_wp_caps`.`passtag_id` > '0'";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$passtag_wp_caps['by_id'][$_result->ID]                                          = $_result;
									$passtag_wp_caps['by_wp_cap'][$_result->wp_cap]['passtag_ids'][$_result->ID]     = $_result->passtag_id;
									$passtag_wp_caps['by_passtag_id'][$_result->passtag_id]['wp_caps'][$_result->ID] = $_result->wp_cap;
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $passtag_wp_caps);
				}
		}
	}