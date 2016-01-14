<?php
/**
 * Passtags.
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
		 * Passtags.
		 *
		 * @package s2Member\Passtags
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class passtags extends framework
		{
			/**
			 * Alias for {@link relations_type_of()}.
			 *
			 * @return array {@inheritdoc relations_type_of()}
			 */
			public function siblings_of() // Arguments are NOT listed here.
				{
					$args = func_get_args();
					array_unshift($args, 'siblings');
					return call_user_func_array(array($this, 'relations_type_of'), $args);
				}

			/**
			 * Alias for {@link relations_type_of()}.
			 *
			 * @return array {@inheritdoc relations_type_of()}
			 */
			public function ancestors_of() // Arguments are NOT listed here.
				{
					$args = func_get_args();
					array_unshift($args, 'ancestors');
					return call_user_func_array(array($this, 'relations_type_of'), $args);
				}

			/**
			 * Alias for {@link relations_type_of()}.
			 *
			 * @return array {@inheritdoc relations_type_of()}
			 */
			public function descendants_of() // Arguments are NOT listed here.
				{
					$args = func_get_args();
					array_unshift($args, 'descendants');
					return call_user_func_array(array($this, 'relations_type_of'), $args);
				}

			/**
			 * Relations for a passtag ID (or of multiple passtag IDs).
			 *
			 * @param string        $type Relationship type.
			 *       • `siblings` Searches only for immediate siblings.
			 *       • `descendants` Searches parent/child relationships for descendants.
			 *       • `ancestors` Searches child/parent relationships for ancestors.
			 *
			 * @param integer|array $passtag_ids A single passtag ID (or an array of passtag IDs).
			 *
			 * @param boolean       $tree Optional. Defaults to FALSE. If this is TRUE, we'll return an array of arrays.
			 *    The array of arrays is returned to form a tree, based on generational level.
			 *    N/A... when/if ``$type`` is set to `sibling`.
			 *
			 * @param integer       $generations Optional. Defaults to `-1`, indicating we want all ancestors/descendants.
			 *    If this is set to a value greater than or equal to `0`, we'll look for X generations only.
			 *    N/A... when/if ``$type`` is set to `sibling`.
			 *
			 * @param boolean       $unique_values_only Optional. This defaults to a TRUE value.
			 *    However, there ARE times when it is necessary to get ALL values; and NOT just unique passtag IDs.
			 *    For instance, when adding user passtags we should add ALL descendants, even if those include duplicates.
			 *
			 *    NOTE: This value is NOT applicable when building a tree. A tree may ALWAYS contain duplicates.
			 *    WARNING: This flag CANNOT be FALSE when collecting relations across multiple ``$passtag_ids``.
			 *
			 * @return array Relations for a particular passtag (or of multiple passtags); possibly through X ``$generations`` only.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$type`` is NOT one of: `siblings|ancestors|descendants`.
			 * @throws exception If ``$unique_values_only`` is FALSE and there are multiple ``$passtag_ids``.
			 * @throws exception If a ``$passtag_ids`` array contains a non-integer value.
			 * @throws exception If ``$passtag_ids`` is empty.
			 */
			protected function relations_type_of($type, $passtag_ids, $tree = FALSE, $generations = -1, $unique_values_only = TRUE)
				{
					$this->check_arg_types('string:!empty', array('integer:!empty', 'array:!empty'),
					                       'boolean', 'integer', 'boolean', func_get_args());

					if(!($relationships = $this->relationships()))
						return array(); // Saves time.

					$relations         = array(); // Initialize.
					$passtags_ids      = (array)$passtag_ids; // Force array.
					$count_passtag_ids = count($passtags_ids); // Count once here.

					if(!$unique_values_only && $count_passtag_ids > 1)
						throw $this->©exception($this->method(__FUNCTION__).'#unique_values_only', get_defined_vars(),
						                        $this->i18n('Param `$unique_values_only`; CANNOT be FALSE w/ multiple passtag IDs.')
						);
					foreach($passtags_ids as $_passtag_id) // Iterates all passtag IDs.
						{
							if(!$this->©integer->is_not_empty($_passtag_id))
								throw $this->©exception($this->method(__FUNCTION__).'#invalid_passtag_id', get_defined_vars(),
								                        $this->i18n('Expecting a non-empty integer `$_passtag_id` value.').
								                        ' '.sprintf($this->i18n('Got: `%1$s`.'), gettype($_passtag_id))
								);
							switch($type) // Handle this based on relationship type.
							{
								case 'siblings': // Get siblings (special case).

										if(!empty($relationships['child_parent'][$_passtag_id]))
											foreach(array_keys($relationships['child_parent'][$_passtag_id]) as $_parent)
												if(!empty($relationships['parent_child'][$_parent]))
													{
														$_siblings = array_keys($relationships['parent_child'][$_parent]);
														$_siblings = array_diff($_siblings, (array)$_passtag_id);
														$relations = array_merge($relations, $_siblings);
														unset($_siblings); // Housekeeping.
													}
										unset($_parent); // Housekeeping.

										break; // Break switch handler.

								case 'ancestors': // Ancestors (`child_parent`).

										$_relations = $this->_relations_type_of($type, $_passtag_id, // Recursively.
										                                        $tree, $generations, $relationships['child_parent']);

										if($_relations) // Do we have relations?
											if($tree && $count_passtag_ids > 1) // Building a tree?
												$relations[] = $_relations; // Need to add a new branch :-)
											else $relations = array_merge($relations, $_relations); // Default behavior.

										unset($_relations); // Housekeeping.

										break; // Break switch handler.

								case 'descendants': // Descendants (`parent_child`).

										$_relations = $this->_relations_type_of($type, $_passtag_id, // Recursively.
										                                        $tree, $generations, $relationships['parent_child']);

										if($_relations) // Do we have relations?
											if($tree && $count_passtag_ids > 1) // Building a tree?
												$relations[] = $_relations; // Need to add a new branch :-)
											else $relations = array_merge($relations, $_relations); // Default behavior.

										unset($_relations); // Housekeeping.

										break; // Break switch handler.

								default: // Exception!
									throw $this->©exception(
										$this->method(__FUNCTION__).'#invalid_relationship_type', get_defined_vars(),
										$this->i18n('Invalid relationship `$type`. Expecting one of: `siblings|descendants|ancestors`.').
										' '.sprintf($this->i18n('Got: `%1$s`.'), $type)
									);
							}
						}
					unset($_passtag_id); // Housekeeping.

					if(!$tree && $unique_values_only && $relations) // If NOT building a tree.
						$relations = array_unique($relations); // Unique values only.

					return $relations; // Default return value.
				}

			/**
			 * Relations for a passtag ID.
			 *
			 * @param string        $type Relationship type.
			 *       • `descendants` Searches parent/child relationships for descendants.
			 *       • `ancestors` Searches child/parent relationships for ancestors.
			 *
			 * @param integer       $passtag_id A single passtag ID.
			 *
			 * @param boolean       $tree Optional. Defaults to FALSE. If this is TRUE, we'll return an array of arrays.
			 *    The array of arrays, is returned to form a tree, based on generational level.
			 *
			 * @param integer       $generations Optional. Defaults to `-1`, indicating we want all ancestors/descendants.
			 *    If this is set to a value greater than or equal to `0`, we'll look for X generations only.
			 *
			 * @param array         $relationships Relationships for `parent_child`, or `child_parent`; depending on ``$type``.
			 *    This is piped in to help prevent repeated function calls. Also prevents repeated conditionals.
			 *
			 * @param array         $___r This is an internal parameter; used in recursive function calls.
			 *    Defaults to ``array('scanned_thus_far' => array(), 'generations_thus_far' => 0)``.
			 *
			 * @return array Relations for a particular passtag; possibly through X ``$generations`` only.
			 */
			protected function _relations_type_of($type, $passtag_id, $tree, $generations, $relationships,
			                                      $___r = array('scanned_thus_far' => array(), 'generations_thus_far' => 0))
				{
					$___r['scanned_thus_far'][] = $passtag_id; // This prevents the possibility of endless recursion :-)

					unset($relationships[0], $relationships[$passtag_id][0]); // For siblings only (exclude here).

					if(empty($relationships[$passtag_id])) return array(); // Stop HERE if we're done.

					$relations = array_keys($relationships[$passtag_id]); // Start w/ immediate relations.

					$___r['generations_thus_far']++; // This value carries over into each recursive call below.
					if($generations < 0 || ($generations > 0 && $___r['generations_thus_far'] < $generations))
						{
							foreach($relations as $_relation) // Handles recursive calls.
								if(!in_array($_relation, $___r['scanned_thus_far'], TRUE)) // Already done?
									if(($_relations = $this->_relations_type_of($type, $_relation, $tree, $generations,
									                                            $relationships, $___r))
									) // We have nested relations in this case.
										{
											if($tree) // Building a tree?
												$relations[] = $_relations; // New branch :-)
											else $relations = array_merge($relations, $_relations);
										}
							unset($_relation, $_relations); // Housekeeping.
						}
					return $relations; // Array of relations (or a tree of relations).
				}

			/**
			 * Gets all passtag relationships.
			 *
			 * @note MySQL is NOT equipped to handle parent -› child hierarchies.
			 *    Therefore, we need to pull ALL relationships into PHP, so they can be analyzed recursively.
			 *    Another possibility, is to use a MySQL stored procedure, but this seems easier.
			 *
			 * @return array Array of passtag relationships.
			 */
			public function relationships()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$relationships = array(); // Initialize.

					$query = // ALL RELATIONSHIPS NEEDED to build a complete array below.
						"SELECT".
						" `passtag_relationships`.*". // Parent/child (by passtag ID).

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtag_relationships'))."` AS `passtag_relationships`".

						" WHERE".
						" `passtag_relationships`.`parent_passtag_id` IS NOT NULL".
						" AND `passtag_relationships`.`parent_passtag_id` > '0'".

						" AND `passtag_relationships`.`child_passtag_id` IS NOT NULL".
						" AND `passtag_relationships`.`child_passtag_id` > '0'";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$relationships['parent_child'][$_result->parent_passtag_id][$_result->child_passtag_id] = -1;
									$relationships['child_parent'][$_result->child_passtag_id][$_result->parent_passtag_id] = -1;
								}
							unset($_result); // Housekeeping.
						}
					if(($_passtags = $this->get_all())) // Add passtags NOT a child of any parent (e.g. at root level).
						foreach($_passtags['by_id'] as $_passtag) if(!isset($relationships['child_parent'][$_passtag->ID]))
							{
								$relationships['parent_child'][0][$_passtag->ID] = -1; // Root at index `0`.
								$relationships['child_parent'][$_passtag->ID][0] = -1; // Root at index `0`.
							}
					unset($_passtags, $_passtag); // Housekeeping.

					return $this->©db_cache->update($db_cache_key, $relationships);
				}

			/**
			 * Gets a specific passtag.
			 *
			 * @param integer|string $id_or_name The ID (or name) of a passtag.
			 *
			 * @return null|object A passtag object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$passtags = $this->get_all();

					if(is_integer($id_or_name) && isset($passtags['by_id'][$id_or_name]))
						return $passtags['by_id'][$id_or_name];

					if(is_string($id_or_name) && isset($passtags['by_name'][$id_or_name]))
						return $passtags['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets all passtags.
			 *
			 * @return array Array of passtags.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$passtags = array(); // Initialize.

					$query = // Everything in s2Member® runs on these :-)
						"SELECT".
						" `passtags`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtags'))."` AS `passtags`".

						" WHERE".
						" `passtags`.`name` IS NOT NULL".
						" AND `passtags`.`name` != ''".

						" AND `passtags`.`status` IS NOT NULL".
						" AND `passtags`.`status` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$passtags['by_id'][$_result->ID]                       = $_result;
									$passtags['by_name'][$_result->name]                   =& $passtags['by_id'][$_result->ID];
									$passtags['by_status'][$_result->status][$_result->ID] =& $passtags['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $passtags);
				}
		}
	}