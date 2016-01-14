<?php
/**
 * Passtag Restrictions.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Restrictions
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Passtag Restrictions.
		 *
		 * @package s2Member\Restrictions
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @note This is one of the most COMPLEX classes in all of s2Member®.
		 *    Please do NOT modify any methods in this class without careful consideration.
		 */
		class passtag_restrictions extends framework
		{
			/**
			 * Handles front-side passtag restriction checks.
			 *
			 * @attaches-to WordPress® `wp` hook.
			 * @hook-priority `-1` Before most everything else.
			 */
			public function wp()
				{
					if(!$this->©env->is_systematic_routine())
						$this->check(NULL, TRUE, TRUE);
				}

			/**
			 * Handles admin-side passtag restriction checks.
			 *
			 * @attaches-to WordPress® `admin_init` hook.
			 * @hook-priority `-1` Before most everything else.
			 */
			public function admin_init()
				{
					if(!$this->©env->is_systematic_routine())
						$this->check(NULL, TRUE, TRUE);
				}

			/**
			 * Checks front-side or admin-side passtag restrictions (based on context).
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 */
			public function check($user = NULL, $behave = FALSE, $log = FALSE)
				{
					if(is_admin()) return $this->check_admin($user, $behave, $log);

					return $this->check_front($user, $behave, $log);
				}

			/**
			 * Checks front-side passtag restrictions.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 */
			public function check_front($user = NULL, $behave = FALSE, $log = FALSE)
				{
					if(is_admin()) return FALSE; // Do NOT check.

					if($this->©media->is_file()) // Media file.
						return $this->check_media(NULL, $user, $behave, $log);

					if(is_singular()) // Singular post (of any kind).
						return $this->check_post(NULL, $user, $behave, $log);

					if(is_tag() || is_category() || is_tax()) // Taxonomy.
						return $this->check_taxonomy_term(NULL, NULL, $user, $behave, $log);

					return $this->check_common_finale(NULL, NULL, $user, $behave, $log);
				}

			/**
			 * Checks administrative passtag restrictions.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 */
			public function check_admin($user = NULL, $behave = FALSE, $log = FALSE)
				{
					if(!is_admin()) return FALSE; // Do NOT check.

					return $this->check_common_finale(NULL, NULL, $user, $behave, $log);
				}

			/**
			 * Check all passtag restrictions that guard a post.
			 *
			 * @param null|integer                $post_id A WordPress® post ID.
			 *    Defaults to a NULL value (indicating the current post, possibly in "the loop").
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If unable to acquire ``$post`` object properties.
			 */
			public function check_post($post_id = NULL, $user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types(array('null', 'integer:!empty'),
					                       $this->©user_utils->which_types(), 'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Establish post object properties.

					if(is_null($post_id) && !empty($GLOBALS['post']))
						$post = $GLOBALS['post'];
					else $post = get_post($post_id);

					if(empty($post) || empty($post->type) || empty($post->ID))
						throw $this->©exception( // Should NOT happen.
							$this->method(__FUNCTION__).'#post_missing', get_defined_vars(),
							$this->i18n('Unable to acquire `$post` object properties.').
							' '.sprintf($this->i18n('Post ID: `%1$s`.'), $post_id)
						);
					// Check post against public systematics.

					if(in_array($post->ID, $this->©systematic->post_ids_public(), TRUE))
						return FALSE; // Unrestricted at all times.

					// Check post against private systematics (MUST be logged-in).

					if(in_array($post->ID, $this->©systematic->post_ids_private(), TRUE))
						{
							if($user->is_logged_in())
								return FALSE; // Unrestricted at all times.

							if($behave) // Redirect (they MUST be logged-in).
								wp_redirect($this->©systematic->url('login', $this->©url->to_wp_permalink_id($post->ID)),
								            $this->©url->redirect_browsers_using_302_status(301)).exit();

							return TRUE; // Deny access (return TRUE by default here).
						}
					// Check post ID/slug (and perhaps ancestors).

					$_check['type::post'][] = $post->post_type.'::'.$post->ID;
					$_check['type::post'][] = $post->post_type.'::'.$post->post_name;

					// The ability to check ancestors; adds support for posts/page hierarchies.
					// This ALSO adds support for bbPress® forums, which use a parent post structure.

					if($this->©options->get('passtag_restrictions.check_post_ancestors'))
						foreach(($_ancestors = get_ancestors($post->ID, $post->post_type)) as $_ancestor_id)
							if(is_object($_ancestor = get_post($_ancestor_id)) && !empty($_ancestor->ID))
								{
									$_check[$_ancestor->ID][] = $_ancestor->post_type.'::'.$_ancestor->ID;
									$_check[$_ancestor->ID][] = $_ancestor->post_type.'::'.$_ancestor->post_name;
								}
					unset($_ancestors, $_ancestor_id, $_ancestor); // Housekeeping.

					$log_identifier = 'type::post|'.$post->post_type.'::'.$post->ID; // Log identifier & primary check.
					$primary        = array('type'      => 'type::post', 'post_type' => $post->post_type, 'post_id' => $post->ID,
					                        'post_name' => $post->post_name, 'check' => $_check);

					if(($_failures = $this->check_type('type::post', $_check, $user, $log, $log_identifier)))
						$failures = array_merge($failures, $_failures);
					else if(is_null($_failures)) $user_can_access = TRUE;

					unset($_check, $_failures); // Housekeeping.

					// Check post taxonomy term(s), and perhaps ancestors.

					foreach(get_post_taxonomies($post->ID) as $_taxonomy)
						{
							if(!has_term('', $_taxonomy, $post->ID)) continue;
							if(!is_array($_terms = wp_get_post_terms($post->ID, $_taxonomy))) continue;

							foreach($_terms as $_term) // Group these by taxonomy type.
								{
									$_check[$_term->taxonomy][] = $_term->taxonomy.'::'.$_term->term_id;
									$_check[$_term->taxonomy][] = $_term->taxonomy.'::'.$_term->slug;

									if($this->©options->get('passtag_restrictions.check_taxonomy_term_ancestors'))
										foreach(($_ancestors = get_ancestors($_term->term_id, $_term->taxonomy)) as $_ancestor_id)
											if(is_object($_ancestor = get_term($_ancestor_id, $_term->taxonomy)) && !empty($_ancestor->term_id))
												{
													$_check[$_ancestor->taxonomy][] = $_ancestor->taxonomy.'::'.$_ancestor->term_id;
													$_check[$_ancestor->taxonomy][] = $_ancestor->taxonomy.'::'.$_ancestor->slug;
												}
									unset($_ancestors, $_ancestor_id, $_ancestor); // Housekeeping.
								}
							unset($_term); // Housekeeping.
						}
					unset($_taxonomy, $_terms); // Housekeeping.

					if(!empty($_check)) // Has taxonomy term(s)?
						{
							if(($_failures = $this->check_type('taxonomy::term', $_check, $user, $log, $log_identifier)))
								$failures = array_merge($failures, $_failures);
							else if(is_null($_failures)) $user_can_access = TRUE;
						}
					unset($_check, $_failures); // Housekeeping.

					// Common checks and finale.

					$url = $this->©url->to_wp_permalink_id($post->ID);

					return $this->check_common_finale($url, $user->ip, $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Check all passtag restrictions that guard a taxonomy term.
			 *
			 * @param null|string                 $taxonomy A WordPress® taxonomy type.
			 *    Defaults to a NULL value (indicating the current taxonomy).
			 *
			 * @param null|integer                $term_id A WordPress® term ID.
			 *    Defaults to a NULL value (indicating the current taxonomy term ID).
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If unable to acquire taxonomy ``$term`` object properties.
			 */
			public function check_taxonomy_term($taxonomy = NULL, $term_id = NULL, $user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types(array('null', 'string:!empty'), array('null', 'integer:!empty'),
					                       $this->©user_utils->which_types(), 'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Establish taxonomy term object properties.

					if(is_null($taxonomy) && is_null($term_id))
						$term = get_queried_object();

					else if($taxonomy && $term_id)
						$term = get_term($term_id, $taxonomy);

					if(empty($term) || empty($term->taxonomy) || empty($term->term_id) || empty($term->slug))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#taxonomy_term_missing', get_defined_vars(),
							$this->i18n('Unable to acquire taxonomy `$term` object properties.').
							' '.sprintf($this->i18n('Taxonomy: `%1$s`; term ID: `%2$s`.'), $taxonomy, $term_id)
						);
					// Check taxonomy term, and perhaps ancestors.

					$_check['taxonomy::term'][] = $term->taxonomy.'::'.$term->term_id;
					$_check['taxonomy::term'][] = $term->taxonomy.'::'.$term->slug;

					if($this->©options->get('passtag_restrictions.check_taxonomy_term_ancestors'))
						foreach(($_ancestors = get_ancestors($term->term_id, $term->taxonomy)) as $_ancestor_id)
							if(is_object($_ancestor = get_term($_ancestor_id, $term->taxonomy)) && !empty($_ancestor->term_id))
								{
									$_check[$_ancestor_id][] = $_ancestor->taxonomy.'::'.$_ancestor->term_id;
									$_check[$_ancestor_id][] = $_ancestor->taxonomy.'::'.$_ancestor->slug;
								}
					unset($_ancestors, $_ancestor_id, $_ancestor); // Housekeeping.

					$log_identifier = 'taxonomy::term|'.$term->taxonomy.'::'.$term->term_id; // Log identifier & primary check.
					$primary        = array('type'      => 'taxonomy::term', 'term_taxonomy' => $term->taxonomy, 'term_id' => $term->term_id,
					                        'term_slug' => $term->slug, 'check' => $_check);

					if(($_failures = $this->check_type('taxonomy::term', $_check, $user, $log, $log_identifier)))
						$failures = array_merge($failures, $_failures);
					else if(is_null($_failures)) $user_can_access = TRUE;

					unset($_check, $_failures); // Housekeeping.

					// Common checks and finale.

					$url = $this->©url->to_wp_term($term);

					return $this->check_common_finale($url, $user->ip, $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Check URI passtag restrictions that guard a particular URI.
			 *
			 * @param null|string                 $uri A partial URL (i.e. a URI).
			 *    Defaults to a NULL value (indicating the current URI).
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If unable to establish a ``$uri`` that we need to check.
			 */
			public function check_uri($uri = NULL, $user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types(array('null', 'string:!empty'), $this->©user_utils->which_types(),
					                       'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Establish the URI that we're checking here.

					if(is_null($uri)) $uri = $this->©url->current_uri();

					if(empty($uri)) // Missing URI? This should NOT happen.
						throw $this->©exception($this->method(__FUNCTION__).'#uri_missing', get_defined_vars(),
						                        $this->i18n('Unable to acquire `$uri` (value empty).').
						                        ' '.sprintf($this->i18n('Got: `%1$s`.'), $uri)
						);
					// Checks a partial URL (i.e. a URI).

					$log_identifier = 'uri|'.$uri; // Log identifier & primary check.
					$primary        = array('type' => 'uri', 'uri' => $uri, 'check' => array(array($uri)));

					// Common checks and finale.

					return $this->check_common_finale($uri, $user->ip, $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Check IP passtag restrictions against a particular user.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function check_ip($user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types($this->©user_utils->which_types(), 'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Checks IP address (based on ``$user`` object instance).

					$log_identifier = 'ip|'.$user->ip; // Log identifier & primary check.
					$primary        = array('type' => 'ip', 'ip' => $user->ip, 'check' => array(array($user->ip)));

					// Common checks and finale.

					return $this->check_common_finale('', $user->ip, $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Check media passtag restrictions that guard a particular file.
			 *
			 * @param null|string                 $file A media file (relative path).
			 *    Defaults to a NULL value (indicating the current media file).
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If unable to establish a ``$file`` that we need to check.
			 */
			public function check_media($file = NULL, $user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types(array('null', 'string:!empty'), $this->©user_utils->which_types(),
					                       'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Establish the media file we're checking here.

					if(is_null($file)) $file = $this->©media->is_file();

					if(empty($file)) // File is missing? This should NOT happen.
						throw $this->©exception($this->method(__FUNCTION__).'#file_missing', get_defined_vars(),
						                        $this->i18n('Unable to acquire `$file` (value empty).').
						                        ' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					// Check media file (based on relative path).

					$_check         = array(array($file));
					$log_identifier = 'media|'.$file; // Log identifier & primary check.
					$primary        = array('type' => 'media', 'file' => $file, 'check' => $_check);

					if(($_failures = $this->check_type('media', $_check, $user, $log, $log_identifier)))
						$failures = array_merge($failures, $_failures);
					else if(is_null($_failures)) $user_can_access = TRUE;

					unset($_check, $_failures); // Housekeeping.

					// Common checks and finale.

					$url = $this->©media->get_permalink($file); // No args; w/ rewrite structure.

					return $this->check_common_finale($url, $user->ip, $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Check profile field passtag restrictions that guard a particular profile field ID.
			 *
			 * @param integer                     $profile_field_id A profile field ID.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable).
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *    See methods below for additional clarification on these return values.
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$profile_field_id`` is empty (this is a required argument).
			 */
			public function check_profile_field($profile_field_id, $user = NULL, $behave = FALSE, $log = FALSE)
				{
					$this->check_arg_types('integer:!empty', $this->©user_utils->which_types(),
					                       'boolean', 'boolean', func_get_args());

					$user_can_access = FALSE; // Initialize.
					$failures        = array(); // Initialize.
					$user            = $this->©user_utils->which($user);

					// Check profile field (always based on ID passed in).

					$_check         = array(array($profile_field_id));
					$log_identifier = 'profile_field|'.$profile_field_id; // Log identifier & primary check.
					$primary        = array('type' => 'profile_field', 'profile_field_id' => $profile_field_id, 'check' => $_check);

					if(($_failures = $this->check_type('profile_field', $_check, $user, $log, $log_identifier)))
						$failures = array_merge($failures, $_failures);
					else if(is_null($_failures)) $user_can_access = TRUE;

					unset($_check, $_failures); // Housekeeping.

					// Common checks and finale.

					return $this->check_common_finale('', '', $user, $behave, $log, $log_identifier,
					                                  $primary, $failures, $user_can_access);
				}

			/**
			 * Checks common front/admin passtag restrictions, and handles finale/behaviors.
			 *
			 * @param null|string                 $uri Optional. A full URL (or a partial URI).
			 *    An empty string indicates a bypass of this common restriction check.
			 *    Defaults to a NULL value (indicating the current URI).
			 *
			 * @param null|string                 $ip Optional. An IP address matching the ``$user``.
			 *    An empty string indicates a bypass of this common restriction check.
			 *    Defaults to a NULL value (indicating the current ``$user->ip``).
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $behave Optional. Defaults to a FALSE value.
			 *    If this is TRUE, and failures occur, we WILL behave (when/if applicable); in whatever way we need to.
			 *    In other words, ``$failures`` returned by this routine, are automatically analyzed, and behaviors are performed automatically.
			 *    Behaviors are ONLY performed when/if applicable; as configured by a site owner (e.g. by one or more passtag restrictions).
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @param string                      $log_identifier Optional. We can build this value dynamically if it is NOT passed in.
			 *    If this IS passed in, any logging that occurs will use this content identifier instead of an auto-generated value.
			 *    If another primary check has already occurred, it should be passed in through this parameter.
			 *
			 * @param array                       $primary Optional. Defaults to an empty array.
			 *    If another primary check has already occurred, it should be passed in through this parameter.
			 *    If passed in, this should include (at a minimum), these two array elements.
			 *       • `type` = String indicating the primary type of restriction check.
			 *       • `check` = Original array passed to {@link check_type()}.
			 *
			 * @param array                       $failures Optional. Defaults to an empty array.
			 *    If other failures have already occurred, they should be passed in through this parameter.
			 *    These ``$failures`` will be considered during behavior handling; which is part of our finale here.
			 *    If passed in, this should be an array of failures, as returned by {@link check_type()}.
			 *
			 * @param boolean                     $user_can_access Optional. Defaults to a FALSE value.
			 *    If restrictions have already been checked (for another type); and access was granted; this should be set to a TRUE value.
			 *    This value will be considered before we return a value from this routine; which all part of our finale here.
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, TRUE, NULL, FALSE.
			 *
			 *    • An array of failures (aka: ``$failures`` — access should NOT be granted to this user).
			 *       If restrictions are guarding this, and the user fails to pass these restrictions, this returns a non-empty array of ``$failures``.
			 *       This array of ``$failures`` can be analyzed further, to see why a particular user failed to pass restrictions.
			 *
			 *    • A TRUE return value (ALSO indicates access should NOT be granted to this user).
			 *       This indicates a special type of failure; normally associated with systematic areas of the site.
			 *       In the case of a TRUE return value, access should NOT be granted; because a systematic area is currently unavailable.
			 *       Systematic areas, like a pre-configured `account` page, require that a user be logged into the site.
			 *
			 *    • A NULL return value (access SHOULD be granted to this user - similar to a FALSE return value).
			 *       If restrictions ARE guarding this, and the user passes restrictions; this routine returns a NULL value.
			 *       Indicates that access SHOULD be granted (e.g. the user actually has a passtag allowing them access).
			 *
			 *    • A FALSE return value (access SHOULD be granted to this user).
			 *       If no restrictions apply, this simply returns boolean FALSE (no restrictions, no failures).
			 *       Indicates that access SHOULD be granted (e.g. this is NOT restricted in any way).
			 *
			 * @note When/if ``$behave`` is TRUE, it may NOT be possible to test the return value of this routine.
			 *    A behavior that we perform here (depending on behavior type), will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If a ``$uri`` cannot be parsed for any reason (i.e. an invalid URI is detected).
			 * @throws exception If ``$primary`` is passed in, but does NOT contain required elements and/or value types.
			 */
			public function check_common_finale($uri = NULL, $ip = NULL, $user = NULL, $behave = FALSE, $log = FALSE, $log_identifier = '',
			                                    $primary = array(), $failures = array(), $user_can_access = FALSE)
				{
					$this->check_arg_types(array('null', 'string'), array('null', 'string'), $this->©user_utils->which_types(),
					                       'boolean', 'boolean', 'string', 'array', 'array', 'boolean', func_get_args());

					$user = $this->©user_utils->which($user); // The user we're checking here.

					if($primary) // Validate ``$primary`` array value (when/if applicable).
						if(!$this->©string->is_not_empty($primary['type']) || !$this->©array->is_not_empty($primary['check']))
							throw $this->©exception( // This should NOT happen.
								$this->method(__FUNCTION__).'#unexpected_primary', get_defined_vars(),
								$this->i18n('Unexpected `$primary` array values (invalid `type` or `check`).').
								' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($primary))
							);
					// Check URI restrictions (when/if applicable).

					if(is_null($uri)) // Current?
						$uri = $this->©url->current_uri();
					else if($uri) $uri = $this->©url->must_parse_uri($uri);

					if($uri && ($_check = array(array($uri)))) // Check URI restrictions?
						{
							if(!$log_identifier) // This is our log identifier?
								$log_identifier = 'uri|'.$uri; // Establish log identifier.

							if(!$primary) // This is our primary check?
								$primary = array('type' => 'uri', 'uri' => $uri, 'check' => $_check);

							// Check URI against public systematics (home/site URIs, and many others).

							if($this->©string->in_regex_patterns($uri, $this->©systematic->uris_public(TRUE)))
								return FALSE; // Unrestricted at all times.

							// Check URI against private systematics (MUST be logged-in).

							if($this->©string->in_regex_patterns($uri, $this->©systematic->uris_private(TRUE)))
								{
									if($user->is_logged_in())
										return FALSE; // Unrestricted at all times.

									if($behave) // Redirect (they MUST be logged-in).
										wp_redirect($this->©systematic->url('login', $this->©url->to_abs_uri($uri)),
										            $this->©url->redirect_browsers_using_302_status(301)).exit();

									return TRUE; // Deny access (return TRUE here by default).
								}
							// Continue with our URI check now.

							if(($_failures = $this->check_type('uri', $_check, $user, $log, $log_identifier)))
								$failures = array_merge($failures, $_failures);
							else if(is_null($_failures)) $user_can_access = TRUE;
						}
					unset($_check, $_failures); // Housekeeping.

					// Check IP/range restrictions (when/if applicable).

					if(is_null($ip)) $ip = $user->ip; // This user IP?

					if($ip && ($_check = array(array($ip)))) // Check IP restrictions?
						{
							if(!$log_identifier) // This is our log identifier?
								$log_identifier = 'ip|'.$ip; // Establish log identifier.

							if(!$primary) // This is our primary check?
								$primary = array('type' => 'ip', 'ip' => $ip, 'check' => $_check);

							if(($_failures = $this->check_type('ip', $_check, $user, $log, $log_identifier)))
								$failures = array_merge($failures, $_failures);
							else if(is_null($_failures)) $user_can_access = TRUE;
						}
					unset($_check, $_failures); // Housekeeping.

					// If we get here and we still do NOT have these details, there is a problem.
					// These details should have been passed in; or established by URI/IP checks above.

					if(!$log_identifier || !$primary)
						throw $this->©exception( // Should NOT happen.
							$this->method(__FUNCTION__).'#invalid_call', get_defined_vars(),
							$this->i18n('Invalid call. No `log_identifier` and/or `primary` check.')
						);
					// Custom restriction types (when/if applicable — requires a filter).

					if(($restriction_types = $this->©restriction_types->get_all())) foreach(array_keys($restriction_types['by_type']) as $_type)
						if(!in_array($_type, $this->©restriction_types->default_built_in_types, TRUE)) // Not a default/built-in type?
							if(($_check = $this->apply_filters($_type.'__check', array(), get_defined_vars())))
								// Custom restriction types require a filter that tells us what to check.
								// Most filters will want to inspect the ``$primary`` variable here.
								{
									if(($_failures = $this->check_type($_type, $_check, $user, $log, $log_identifier)))
										$failures = array_merge($failures, $_failures);
									else if(is_null($_failures)) $user_can_access = TRUE;
								}
					unset($_type, $_check, $_failures); // Housekeeping.

					// Finale for all restriction checks.
					finale: // Target point (via: ``goto finale``).

					if($user_can_access) return NULL; // User can access.

					$trigger_events = ($behave || $log); // If either are TRUE.
					if($failures && ($trigger_events || $behave)) // Handle failure events/behaviors.
						$this->process_failure_events_behaviors($failures, $user, $trigger_events, $behave, $primary, $uri);

					if($failures) return $failures; // Return failures (by type).

					return FALSE; // No restrictions, no failures.
				}

			/**
			 * Processes passtag restriction failures (events/behaviors).
			 *
			 * @param array                       $failures Required. Failures array.
			 *    This should be an array of failures, as returned by {@link check_type()}.
			 *
			 * @param null|integer|\WP_User|users $user Required. The user we're dealing with here.
			 *
			 * @param boolean                     $trigger_events Required. Trigger failure events?
			 *    If this is TRUE, we WILL trigger any failure events that occur (when/if applicable).
			 *
			 * @param boolean                     $behave Required. Process failure behaviors?
			 *    If this is TRUE, we WILL behave (when/if applicable); in whatever way we need to.
			 *
			 * @param array                       $primary Required. Primary check.
			 *    This should include (at a minimum), these two array elements at all times.
			 *       • `type` = String indicating the primary type of restriction check.
			 *       • `check` = Original array passed to {@link check_type()}.
			 *
			 * @param string                      $uri Optional. Defaults to an empty string.
			 *    If a URI was associated with the checks performed, pass it in through this parameter.
			 *
			 * @note When/if ``$behave`` is TRUE, this will likely stop script execution completely.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$failures`` or ``$primary`` are empty. This should NOT happen.
			 * @throws exception If ``$primary`` does NOT contain required elements and/or value types.
			 */
			public function process_failure_events_behaviors($failures, $user, $trigger_events, $behave, $primary, $uri = '')
				{
					$this->check_arg_types('array:!empty', $this->©user_utils->which_types(), 'boolean', 'boolean',
					                       'array:!empty', 'string', func_get_args());

					$user = $this->©user_utils->which($user); // The user we're dealing with here.

					if(!$this->©string->is_not_empty($primary['type']) || !$this->©array->is_not_empty($primary['check']))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#unexpected_primary', get_defined_vars(),
							$this->i18n('Unexpected `$primary` array values (missing `type` or `check`).').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->©var->dump($primary))
						);
					if($primary['type'] === 'profile_field') $behave = FALSE; // Never.

					$collective_failure_data = array( // Default redirection argument data values.
					                                  'restriction_types' => array_keys($failures), 'passtag_restriction_ids' => array(),
					                                  'passtag_ids'       => array(), 'passtag_names' => array(),
					                                  'event_types'       => array(), 'uri' => $uri
					); // Also used as redirection arg data (in behavior handling).
					$redirect_arg_data       =& $collective_failure_data; // Reference alias.

					foreach($failures as $_type => $_data) // Failure data by type.
						{
							$collective_failure_data['passtag_restriction_ids'] = // Merge together. May contain dupes (for now).
								array_merge($collective_failure_data['passtag_restriction_ids'], $_data['passtag_restriction_ids_guarding_access']);
							$collective_failure_data['passtag_ids']             = // Merge together. May contain dupes (for now).
								array_merge($collective_failure_data['passtag_ids'], $_data['passtag_ids_granting_access']);
							$collective_failure_data['passtag_names']           = // Merge together. May contain dupes (for now).
								array_merge($collective_failure_data['passtag_names'], $_data['passtag_names_granting_access']);
						}
					foreach($collective_failure_data as &$_data) // We only want unique array values.
						if(is_array($_data)) $_data = array_unique($_data); // Remove dupes.
					unset($_type, $_data); // Housekeeping.

					# Here we handle EVENTS associated with failures.
					# These events repeat each time they occur during restriction checks.

					event_triggers: // Target point for event processing (via ``goto event_triggers``).

					if(!$trigger_events) goto behavior_processor; // If NOT processing; jump to behaviors.

					foreach($user->inaccessible_expired_passtags() as $_user_passtag)
						if(in_array($_user_passtag->passtag_id, $collective_failure_data['passtag_ids'], TRUE))
							{
								$collective_failure_data['event_types'][] = // Add to list of events.
									($_event_type = 'user_reaches_passtag_time_stops');

								$this->©event->trigger($_event_type,
								                       array( // Meta vars.
								                              'user'         => $user,
								                              'user_passtag' => $_user_passtag
								                       ), get_defined_vars());
							}
					unset($_user_passtag, $_event_type); // Housekeeping.

					foreach($user->inaccessible_used_passtags() as $_user_passtag)
						if(in_array($_user_passtag->passtag_id, $collective_failure_data['passtag_ids'], TRUE))
							{
								$collective_failure_data['event_types'][] = // Add to list of events.
									($_event_type = 'user_reaches_passtag_uses_limit');

								$this->©event->trigger($_event_type,
								                       array( // Meta vars.
								                              'user'         => $user,
								                              'user_passtag' => $_user_passtag
								                       ), get_defined_vars());
							}
					unset($_user_passtag, $_event_type); // Housekeeping.

					foreach($user->inaccessible_iped_passtags() as $_user_passtag)
						if(in_array($_user_passtag->passtag_id, $collective_failure_data['passtag_ids'], TRUE))
							{
								$collective_failure_data['event_types'][] = // Add to list of events.
									($_event_type = 'user_reaches_passtag_ips_limit');

								$this->©event->trigger($_event_type,
								                       array( // Meta vars.
								                              'user'         => $user,
								                              'user_passtag' => $_user_passtag
								                       ), get_defined_vars());
							}
					unset($_user_passtag, $_event_type); // Housekeeping.

					if($collective_failure_data['event_types']) // Unique event types only.
						$collective_failure_data['event_types'] = array_unique($collective_failure_data['event_types']);

					# Here we handle BEHAVIORS associated with failures.
					# We perform the first non-default redirection behavior only.

					behavior_processor: // Target point for behavior handling (via ``goto behavior_processor``).

					if(!$behave) goto finale; // If NOT behaving; jump to finale (we're all done now).

					foreach($collective_failure_data['passtag_restriction_ids'] as $_passtag_restriction_id)
						{
							if(!($_restriction = $this->get($_passtag_restriction_id)))
								throw $this->©exception($this->method(__FUNCTION__).'#missing_passtag_restriction_id', get_defined_vars(),
								                        sprintf($this->i18n('Missing passtag restriction ID: `%1$s`.'), $_passtag_restriction_id)
								);
							switch($_restriction->behavior_type) // Handles various behavior types.
							{
								case 'redirect': // Handles redirect behaviors (only behavior we support currently).

										if($this->©event_redirects->are_redireting) // Do nothing in this case.
											break 2; // Break main loop (already redirecting via event process).

										// We stop after finding the first redirection URL that is NOT a default value.
										// In this way, we perform the first redirection behavior only.

										if(!isset($redirect_users_with_passtags_to_account_page))
											$redirect_users_with_passtags_to_account_page = // Only need to acquire this ONE time.
												$this->©options->get('passtag_restrictions.redirect_users_with_passtags_to_account_page');

										if($redirect_users_with_passtags_to_account_page && $user->is_logged_in() && $user->has_passtags())
											{
												$redirect_to = $this->©systematic->url('account'); // User already has a passtag.
												break 2; // Break main loop (we have our redirection URL now).
											}
										else if(($_passtag = $this->©passtag->get($_restriction->passtag_id)) && $_passtag->redirects_to)
											{
												$redirect_to = $_passtag->redirects_to; // Passtag redirects to this location.
												break 2; // Break main loop (we have our redirection URL now).
											}
										else // Default redirection URL (register page — last resort).
											{
												$redirect_to = $this->©systematic->url('register');
												break; // Break switch (continue behaviors).
											}
								default: // No behavior whatsoever (for this restriction).

									break; // Break switch (continue behaviors).
							}
						}
					unset($_passtag_restriction_id, $_restriction, $_passtag); // Housekeeping.

					if(!empty($redirect_to)) $this->redirect($redirect_to, $redirect_arg_data);

					finale: // Target point for finale (via ``goto finale``).
				}

			/**
			 * Redirect behavior for passtag restrictions.
			 *
			 * @param string $url Required. A full URL to redirect to.
			 *
			 * @param array  $arg_data Optional array of data for redirection argument values.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$url`` is empty. We MUST have a URL to redirect to.
			 * @throws exception If ``$arg_data`` contains invalid types.
			 */
			public function redirect($url, $arg_data = array())
				{
					$this->check_arg_types('string:!empty', 'array', func_get_args());

					if(headers_sent())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#headers_sent_already', get_defined_vars(),
							$this->i18n('Doing it wrong! Headers have already been sent. Please check hook priorities.')
						);
					$args             = array(); // Default arguments.
					$default_arg_data = array( // Default arg data values.
					                           'restriction_types' => array(), 'passtag_restriction_ids' => array(),
					                           'passtag_ids'       => array(), 'passtag_names' => array(),
					                           'event_types'       => array(), 'uri' => ''
					); // Now check arg data types and merge.
					$arg_data         = $this->check_extension_arg_types( // Validate these.
						'array', 'array', 'array', 'array', 'array', 'string', $default_arg_data, $arg_data);

					if(!($redirect_args = $this->©options->get('passtag_restrictions.redirect_args')))
						goto redirection; // Skip to redirection. No need to process these.

					$all_redirect_args = in_array('all', $redirect_args, TRUE); // All argument data?

					if($all_redirect_args || in_array('restriction_types', $redirect_args, TRUE) && $arg_data['restriction_types'])
						$args['restriction_types'] = implode("\n", $arg_data['restriction_types']);

					if($all_redirect_args || in_array('passtag_restriction_ids', $redirect_args, TRUE) && $arg_data['passtag_restriction_ids'])
						$args['passtag_restriction_ids'] = implode("\n", $arg_data['passtag_restriction_ids']);

					if($all_redirect_args || in_array('passtag_ids', $redirect_args, TRUE) && $arg_data['passtag_ids'])
						$args['passtag_ids'] = implode("\n", $arg_data['passtag_ids']);

					if($all_redirect_args || in_array('passtag_names', $redirect_args, TRUE) && $arg_data['passtag_names'])
						$args['passtag_names'] = implode("\n", $arg_data['passtag_names']);

					if($all_redirect_args || in_array('event_types', $redirect_args, TRUE) && $arg_data['event_types'])
						$args['event_types'] = implode("\n", $arg_data['event_types']);

					if($all_redirect_args || in_array('uri', $redirect_args, TRUE) && $arg_data['uri'])
						$args['uri'] = $arg_data['uri']; // String URI (when/if applicable).

					redirection: // Target point (via: ``goto redirection``).

					if($args) $args = array($this->___instance_config->plugin_var_ns.'_r2a' => $args);

					if(is_array($args = $this->apply_filters(__FUNCTION__.'__args', $args, get_defined_vars())) && $args)
						{
							$url = add_query_arg(urlencode_deep($args), $url);
							if(strlen($url) > 2000) // Do NOT allow URLs to grow larger than 2000 chars.
								$url = (string)substr($url, 0, 1997).'...';
						}
					wp_redirect($url, $this->©url->redirect_browsers_using_302_status(301)).exit();
				}

			/**
			 * Checks a passtag restriction type.
			 *
			 * @param string                      $type The restriction `type` string and NOT the `ID`.
			 *    This routine can check any restriction type. See also {@link restriction_types::$default_built_in_types}.
			 *
			 * @param array                       $checks What we are checking (depending on ``$type``).
			 *    This array value must NOT be empty, else an exception is thrown.
			 *
			 *    IMPORTANT (this MUST be passed as a two-dimensional array):
			 *
			 *    The ``$checks`` argument MUST be passed in as an array, with TWO dimensions.
			 *    This method searches grouped ``$checks``, to test for the possibility of multiple variations of something.
			 *    For instance, it is possible to check both a post ID and/or a post slug (at the same time).
			 *
			 *    Each set of grouped ``$checks``, is tested against passtag IDs restricting a particular group, collectively.
			 *    In other words, users with a passtag granting access to any value in a ``$checks`` group, will pass on that group.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're testing.
			 *
			 * @param boolean                     $log Optional. Defaults to a FALSE value.
			 *    If this is TRUE, we will log IP addresses and record uses (when/if applicable).
			 *
			 * @param string                      $log_identifier Optional. We can build this value dynamically if it is NOT passed in.
			 *    If this IS passed in, any logging that occurs will use this content identifier instead of an auto-generated value.
			 *
			 * @return array|null|boolean Possible return values include: an array of failures, NULL, or FALSE.
			 *
			 *    • An array of failures (aka: ``$failures`` — please be VERY careful with this).
			 *       If restrictions are guarding ``$checks`` of this ``$type``, and the user does NOT have a passtag granting access;
			 *       an array of ``$failures`` is returned, with details about each failure. These are ultimately used in behavior handling,
			 *       when/if access is denied to this user (after a broader scan is fully completed). This array can be analyzed further,
			 *       to see why a particular user failed to pass ``$checks`` of this ``$type``.
			 *
			 *       IMPORTANT: An array of failures, does NOT necessarily mean that access to ``$checks`` should be denied to this user.
			 *       Passtags don't just guard access to ``$checks``, they ALSO provide access. So passing ``$checks`` of another ``$type`` (when/if applicable),
			 *       even though they've failed to pass on ``$checks`` of this ``$type``, could still lead to access being granted to this user.
			 *       For example, a post ID/slug may fail, but a taxonomy/term (i.e. a different ``$type`` of ``$check``) for the same post, might pass.
			 *
			 *    • A NULL return value (access to ``$checks`` SHOULD be granted to this user under most scenarios).
			 *       If restrictions are guarding ``$checks`` of this ``$type``, and the user has a passtag granting access, this returns NULL.
			 *       So this is an IMPORTANT return value to consider. It normally indicates access to ``$checks`` SHOULD be granted to this user.
			 *       Passtags don't just guard access to ``$checks``, they ALSO provide access. So passing on ``$checks`` of this ``$type``,
			 *       normally indicates that access to ``$checks`` SHOULD be granted (the user has a passtag allowing them access).
			 *
			 *    • FALSE (no restrictions, no failures — please be VERY careful with this).
			 *       If no restrictions apply, this simply returns boolean FALSE (no restrictions, no failures).
			 *       This is usually NOT an important return value to consider, because this does NOT necessarily mean access should be granted to this user.
			 *       A FALSE return value, very simply indicates there were no restrictions/failures associated with ``$checks`` of this ``$type``.
			 *       Other restriction ``$types`` may still be triggered on ``$checks`` of a different ``$type`` (when/if applicable).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$type`` or ``$checks`` are empty. We MUST have both of these values.
			 * @throws exception If any group of ``$checks`` uses the string key `all` (which is reserved by this routine).
			 * @throws exception If ``$checks`` contains any value that is NOT nested into a two-dimensional structure.
			 * @throws exception If ``$checks`` is more than two dimensions in total array depth.
			 * @throws exception If ``$checks`` contains any value (regardless of dimension), that is empty.
			 * @throws exception If ``$checks`` contains a group check, with a value that is NOT an `integer|string`.
			 */
			public function check_type($type, $checks, $user = NULL, $log = FALSE, $log_identifier = '')
				{
					$this->check_arg_types('string:!empty', 'array:!empty', $this->©user_utils->which_types(), 'boolean', 'string', func_get_args());

					if(!($restrictions = $this->get_type_restricts($type)))
						return FALSE; // No restrictions, no failures.

					if(!$log_identifier) $log_identifier = $type.'|'.md5(serialize($checks));

					$user                                       = $this->©user_utils->which($user);
					$failures                                   = array(); // Initialize an array of failures.
					$passtag_ids_granting_access_to             = array(); // Initialize this array of passtag IDs.
					$passtag_names_granting_access_to           = array(); // Initialize this array of passtag names.
					$passtag_restriction_ids_guarding_access_to = array(); // Initialize this array of passtag restriction IDs.
					$passtag_restriction_checks_triggered_in    = array(); // Initialize this array of passtag restriction checks.
					$passtag_restriction_patterns_triggered_in  = array(); // Initialize this array of passtag restriction patterns.
					$passtag_restrictions_are_guarding_access   = FALSE; // This defaults to a FALSE value (we'll check below).

					if(!empty($restrictions['all'])) // Consider the `all` keyword (always check this first).
						{
							$passtag_restrictions_are_guarding_access          = TRUE;
							$passtag_restriction_patterns_triggered_in['all']  = array('all');
							$passtag_restriction_checks_triggered_in['all']    = array('all');
							$passtag_ids_granting_access_to['all']             = $restrictions['all']['passtag_ids'];
							$passtag_names_granting_access_to['all']           = $restrictions['all']['passtag_names'];
							$passtag_restriction_ids_guarding_access_to['all'] = $restrictions['all']['passtag_restriction_ids'];

							unset($restrictions['all']); // Remove this key; we do NOT want this in wildcard patterns.
						}
					$wildcard_patterns = array_keys($restrictions); // Wildcards (or CIDR notations).

					// Next we search wildcard patterns.
					// All restriction types support wildcard pattern matching.
					// IPs can be specified with a CIDR notation, to include a range of IPs.

					foreach($checks as $_group => $_group_checks) // Iterates each group of checks.
						{
							if($_group === 'all') // Key conflict?
								throw $this->©exception(
									$this->method(__FUNCTION__).'#invalid_group', get_defined_vars(),
									$this->i18n('Invalid `$_group`. Expecting key NOT equal to `all`.').
									' '.sprintf($this->i18n('Got: `%1$s`.'), $_group)
								);
							if(!$this->©array->is_not_empty($_group_checks))
								throw $this->©exception(
									$this->method(__FUNCTION__).'#invalid_group_checks', get_defined_vars(),
									$this->i18n('Invalid `$_group_checks`. Expecting `array` NOT empty.').
									' '.sprintf($this->i18n('Got: %1$s`%2$s`.'), ((!$_group_checks) ? $this->i18n('empty').' ' : ''), gettype($_group_checks))
								);
							foreach($_group_checks as $_check) // Iterates each of the checks in this group.
								{
									if(!$_check || (!is_integer($_check) && !is_string($_check)))
										throw $this->©exception(
											$this->method(__FUNCTION__).'#invalid_check', get_defined_vars(),
											$this->i18n('Invalid `$_check`. Expecting `integer|string` NOT empty.').
											' '.sprintf($this->i18n('Got: %1$s`%2$s`.'), ((!$_check) ? $this->i18n('empty').' ' : ''), gettype($_check))
										);
									// All restriction types support wildcard pattern matching.
									// This search routine is always caSe sensitive (no exceptions).
									// However, wildcard patterns CAN include caSe insensitive character classes.
									// Example: `post::slu[Gg]`, see: <http://php.net/manual/en/function.fnmatch.php>.

									if(($_matching_keys = $this->©string->in_wildcard_patterns((string)$_check, $wildcard_patterns, FALSE, TRUE)))
										{
											foreach($_matching_keys as $_matching_key) // Report each pattern that matches.
												{
													$passtag_restrictions_are_guarding_access = TRUE; // Matching key.
													$_restriction                             = & $restrictions[$wildcard_patterns[$_matching_key]];

													$passtag_restriction_checks_triggered_in[$_group][]   = $_check;
													$passtag_restriction_patterns_triggered_in[$_group][] = $wildcard_patterns[$_matching_key];

													if(!empty($passtag_ids_granting_access_to[$_group]))
														$passtag_ids_granting_access_to[$_group] = array_merge($passtag_ids_granting_access_to[$_group], $_restriction['passtag_ids']);
													else $passtag_ids_granting_access_to[$_group] = $_restriction['passtag_ids'];

													if(!empty($passtag_names_granting_access_to[$_group]))
														$passtag_names_granting_access_to[$_group] = array_merge($passtag_names_granting_access_to[$_group], $_restriction['passtag_names']);
													else $passtag_names_granting_access_to[$_group] = $_restriction['passtag_names'];

													if(!empty($passtag_restriction_ids_guarding_access_to[$_group]))
														$passtag_restriction_ids_guarding_access_to[$_group] = array_merge($passtag_restriction_ids_guarding_access_to[$_group], $_restriction['passtag_restriction_ids']);
													else $passtag_restriction_ids_guarding_access_to[$_group] = $_restriction['passtag_restriction_ids'];
												}
											unset($_matching_key, $_restriction); // Housekeeping.
										}
									unset($_matching_keys); // Housekeeping.

									// IPs can be specified with a CIDR notation, to include a range of IPs.

									if($type === 'ip') foreach($wildcard_patterns as $_cidr_notation) // Each notation.
										if(strpos($_cidr_notation, '/') !== FALSE && $this->©ip->in_range((string)$_check, $_cidr_notation))
											{
												$passtag_restrictions_are_guarding_access = TRUE; // Matches.
												$_restriction                             = & $restrictions[$_cidr_notation];

												$passtag_restriction_checks_triggered_in[$_group][]   = $_check;
												$passtag_restriction_patterns_triggered_in[$_group][] = $_cidr_notation;

												if(!empty($passtag_ids_granting_access_to[$_group]))
													$passtag_ids_granting_access_to[$_group] = array_merge($passtag_ids_granting_access_to[$_group], $_restriction['passtag_ids']);
												else $passtag_ids_granting_access_to[$_group] = $_restriction['passtag_ids'];

												if(!empty($passtag_names_granting_access_to[$_group]))
													$passtag_names_granting_access_to[$_group] = array_merge($passtag_names_granting_access_to[$_group], $_restriction['passtag_names']);
												else $passtag_names_granting_access_to[$_group] = $_restriction['passtag_names'];

												if(!empty($passtag_restriction_ids_guarding_access_to[$_group]))
													$passtag_restriction_ids_guarding_access_to[$_group] = array_merge($passtag_restriction_ids_guarding_access_to[$_group], $_restriction['passtag_restriction_ids']);
												else $passtag_restriction_ids_guarding_access_to[$_group] = $_restriction['passtag_restriction_ids'];
											}
									unset($_cidr_notation, $_restriction); // Housekeeping.
								}
							unset($_check); // Housekeeping.
						}
					unset($_group, $_group_checks); // Housekeeping.

					// Handle IP and use logging (when/if applicable); for this restriction type.

					if($passtag_restrictions_are_guarding_access && $log) // Restrictions to log?
						{
							$_passtag_ids_granting_access = array(); // Initialize array of passtag IDs.
							foreach($passtag_ids_granting_access_to as $_group => $_passtag_ids_granting_access_to_group)
								$_passtag_ids_granting_access = array_merge($_passtag_ids_granting_access, $_passtag_ids_granting_access_to_group);

							$user->log_accessible_passtag_uses(array_unique($_passtag_ids_granting_access), $log_identifier);
							unset($_passtag_ids_granting_access, $_group, $_passtag_ids_granting_access_to_group); // Housekeeping.
						}
					// OK. Now let's handle conditionals & return any failures that exist against this user.

					if($passtag_restrictions_are_guarding_access) // Restrictions are guarding access?
						{
							foreach($passtag_ids_granting_access_to as $_group => $_passtag_ids_granting_access_to_group)
								{
									$_passtag_ids_granting_access_to_group = // Unique passtag IDs only.
										array_unique($_passtag_ids_granting_access_to_group);

									if($user->can_access_any_passtag($_passtag_ids_granting_access_to_group))
										{
											$failures = array(); // No failures (empty this).
											break; // Break loop in this scenario (no failures).
										}
									$_group_failure_data = array( // Record this as a failure.
									                              'passtag_ids_granting_access'             => $passtag_ids_granting_access_to[$_group],
									                              'passtag_names_granting_access'           => $passtag_names_granting_access_to[$_group],
									                              'passtag_restriction_ids_guarding_access' => $passtag_restriction_ids_guarding_access_to[$_group],
									                              'passtag_restriction_checks_triggered'    => $passtag_restriction_checks_triggered_in[$_group],
									                              'passtag_restriction_patterns_triggered'  => $passtag_restriction_patterns_triggered_in[$_group]
									); // May contain dupes. We run ``array_unique()`` before returning failures.

									if(!empty($failures[$type])) // Merge with any existing failures on this type.
										$failures[$type] = array_merge_recursive($failures[$type], $_group_failure_data);
									else $failures[$type] = $_group_failure_data;

									unset($_group_failure_data); // Housekeeping.
								}
							unset($_group, $_passtag_ids_granting_access_to_group); // Housekeeping.

							if(!empty($failures[$type])) // Failures for this type?
								{
									foreach($failures[$type] as &$_failure_data)
										$_failure_data = array_unique($_failure_data);
									// Unique array values; removing overlaps on this type.
									unset($_failure_data); // Housekeeping.

									return $failures; // Failures w/ ``$type`` key.
								}
							return NULL; // Restrictions; but no failures.
						}
					return FALSE; // No restrictions, no failures.
				}

			/**
			 * Gets a specific passtag restriction.
			 *
			 * @param integer $id The ID of a passtag restriction.
			 *
			 * @return null|object A passtag restriction object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id`` is empty.
			 */
			public function get($id)
				{
					$this->check_arg_types('integer:!empty', func_get_args());

					$restrictions = $this->get_all();

					if(isset($restrictions['by_id'][$id]))
						return $restrictions['by_id'][$id];

					return NULL; // Default return value.
				}

			/**
			 * Gets passtag restrictions for a specific passtag.
			 *
			 * @param integer|string $passtag_id_or_name The ID (or name) of a passtag.
			 *
			 * @return array An array of passtag restriction objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$passtag_id_or_name`` is empty.
			 */
			public function for_passtag($passtag_id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$restrictions = $this->get_all();

					if(is_integer($passtag_id_or_name) && isset($restrictions['by_passtag_id'][$passtag_id_or_name]))
						return $restrictions['by_passtag_id'][$passtag_id_or_name];

					if(is_string($passtag_id_or_name) && isset($restrictions['by_passtag_name'][$passtag_id_or_name]))
						return $restrictions['by_passtag_name'][$passtag_id_or_name];

					return array(); // Default return value.
				}

			/**
			 * Gets all passtag restrictions (grouped by what they restrict); for a particular restriction type.
			 *
			 * @param string $type_id_or_type A specific restriction type (ID or type string).
			 *
			 * @return array An array of all restrictions, of a particular type; grouped by what they restrict (e.g. wildcards).
			 *
			 * @note This does NOT return restriction objects. It returns optimized arrays (for restriction type checks).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$type_id_or_type`` is empty.
			 */
			public function get_type_restricts($type_id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$restrictions = $this->get_all();

					if(is_integer($type_id_or_type) && isset($restrictions['by_type_id_restricts'][$type_id_or_type]))
						return $restrictions['by_type_id_restricts'][$type_id_or_type];

					if(is_string($type_id_or_type) && isset($restrictions['by_type_restricts'][$type_id_or_type]))
						return $restrictions['by_type_restricts'][$type_id_or_type];

					return array(); // No restrictions of this type.
				}

			/**
			 * Gets all passtag restrictions.
			 *
			 * @return array An array of all passtag restrictions.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$passtag_restrictions = array();

					$query =
						"SELECT".
						" `passtags`.`name` AS `passtag_name`,".
						" `restriction_types`.`type` AS `type`,".
						" `restriction_types`.`default_behavior_type_id` AS `default_behavior_type_id`,".
						" `behavior_types`.`type` AS `behavior_type`,".
						" `passtag_restrictions`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtags'))."` AS `passtags`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('restriction_types'))."` AS `restriction_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('behavior_types'))."` AS `behavior_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('passtag_restrictions'))."` AS `passtag_restrictions`".

						" WHERE".
						" `passtag_restrictions`.`passtag_id` = `passtags`.`ID`".
						" AND `passtag_restrictions`.`passtag_id` IS NOT NULL".
						" AND `passtag_restrictions`.`passtag_id` > '0'".

						" AND `passtag_restrictions`.`restriction_type_id` = `restriction_types`.`ID`".
						" AND `passtag_restrictions`.`restriction_type_id` IS NOT NULL".
						" AND `passtag_restrictions`.`restriction_type_id` > '0'".

						" AND `passtag_restrictions`.`behavior_type_id` = `behavior_types`.`ID`".
						" AND `passtag_restrictions`.`behavior_type_id` IS NOT NULL".
						" AND `passtag_restrictions`.`behavior_type_id` >= '0'".

						" AND `passtags`.`name` IS NOT NULL".
						" AND `passtags`.`name` != ''".

						" AND `restriction_types`.`type` IS NOT NULL".
						" AND `restriction_types`.`type` != ''".

						" AND `restriction_types`.`default_behavior_type_id` IS NOT NULL".
						" AND `restriction_types`.`default_behavior_type_id` >= '0'".

						" AND `behavior_types`.`type` IS NOT NULL".
						" AND `behavior_types`.`type` != ''".

						" AND `passtag_restrictions`.`restricts` IS NOT NULL".
						" AND `passtag_restrictions`.`restricts` != ''";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							$_default_behavior_type_id = $this->©behavior_type->id('default');

							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->behavior_type_id === $_default_behavior_type_id)
										{
											$_result->behavior_type_id = $_result->default_behavior_type_id;
											$_result->behavior_type    = $this->©behavior_type->type($_result->behavior_type_id);
										}
									$passtag_restrictions['by_id'][$_result->ID]                                   = $_result;
									$passtag_restrictions['by_type'][$_result->type][$_result->ID]                 =& $passtag_restrictions['by_id'][$_result->ID];
									$passtag_restrictions['by_passtag_id'][$_result->passtag_id][$_result->ID]     =& $passtag_restrictions['by_id'][$_result->ID];
									$passtag_restrictions['by_passtag_name'][$_result->passtag_name][$_result->ID] =& $passtag_restrictions['by_id'][$_result->ID];

									$passtag_restrictions['by_type_id_restricts'][$_result->restriction_type_id][$_result->restricts]['passtag_restriction_ids'][$_result->ID] = $_result->ID;
									$passtag_restrictions['by_type_id_restricts'][$_result->restriction_type_id][$_result->restricts]['passtag_ids'][$_result->ID]             = $_result->passtag_id;
									$passtag_restrictions['by_type_id_restricts'][$_result->restriction_type_id][$_result->restricts]['passtag_names'][$_result->ID]           = $_result->passtag_name;
									$passtag_restrictions['by_type_restricts'][$_result->type]                                                                                 =& $passtag_restrictions['by_type_id_restricts'][$_result->restriction_type_id];
								}
							unset($_default_behavior_type_id, $_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $passtag_restrictions);
				}
		}
	}