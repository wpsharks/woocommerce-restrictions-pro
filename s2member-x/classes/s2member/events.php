<?php
/**
 * Events.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Events
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Events.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @note This is one of the most SENSITIVE classes in all of s2Member®.
		 *    Please do NOT modify any methods in this class without careful consideration.
		 */
		class events extends framework
		{
			/**
			 * Handles loading sequence.
			 *
			 * @attaches-to WordPress® `wp_loaded` action hook.
			 * @hook-priority `PHP_INT_MAX` After CRONs are loaded up.
			 */
			public function wp_loaded()
				{
					$this->©crons->config($this->cron_jobs);
				}

			/**
			 * @var array CRON jobs in this class.
			 */
			public $cron_jobs = array
			(
				array('©class.method' =>
				      '©event_crons.event_processor',
				      'schedule'      => 'every15m'),

				array('©class.method' =>
				      '©event_crons.user_passtags_before_time_stops',
				      'schedule'      => 'every15m'),

				array('©class.method' =>
				      '©event_crons.user_passtags_time_stops',
				      'schedule'      => 'every15m'),

				array('©class.method' =>
				      '©event_crons.user_passtags_max_uses',
				      'schedule'      => 'every15m'),

				array('©class.method' =>
				      '©event_crons.user_passtags_max_ips',
				      'schedule'      => 'every15m')
			);

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_creation($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_creation($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_activation($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User. Passed by WordPress®.
			 * @param string                      $password Passed by WordPress® hook.
			 */
			public function wp_user_activation($user, $password)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), compact('password'));
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param integer                     $blog_id Passed by WordPress® hook.
			 * @param null|integer|\WP_User|users $user User. Passed by WordPress®.
			 * @param string                      $password Passed by WordPress® hook.
			 */
			public function wp_user_blog_activation($blog_id, $user, $password)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger('wp_user_activation', compact('user', 'aggregate_user_passtags'), compact('blog_id', 'password'));
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_update($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_update($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_deletion($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_deletion($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_login_success($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_login_success($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param string $username Username attempting to login.
			 * @param array  $vars Variables defined in the scope of the caller.
			 */
			public function user_login_failure($username, $vars)
				{
					$this->trigger(__FUNCTION__, array(), array_merge($vars, compact('username')));
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param string $username Username attempting to login.
			 * @param array  $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_login_failure($username, $vars)
				{
					$this->trigger(__FUNCTION__, array(), array_merge($vars, compact('username')));
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function user_logout($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Action handler (converts to event).
			 *
			 * @param null|integer|\WP_User|users $user User.
			 * @param array                       $vars Variables defined in the scope of the caller.
			 */
			public function wp_user_logout($user, $vars)
				{
					$user                    = $this->©user_utils->which($user);
					$aggregate_user_passtags = $user->active_passtags();

					$this->trigger(__FUNCTION__, compact('user', 'aggregate_user_passtags'), $vars);
				}

			/**
			 * Triggers an event (of a particular type).
			 *
			 * @param string $event_type_id_or_type Type of event (ID or type).
			 *
			 * @param array  $meta_vars Meta vars/data specific to this event (see below).
			 *
			 * @param array  $vars Variables defined in the scope of the calling routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function trigger($event_type_id_or_type, $meta_vars = array(), $vars = array())
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array', 'array', func_get_args());

					if(!($event_type = $this->©event_type->get($event_type_id_or_type)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_missing_event_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid/missing event type: `$1%s`.'), $event_type_id_or_type)
						);
					if($this->©event_type->is_disabled($event_type->ID)) return; // Disabled at runtime?

					$meta_vars = $this->populate_validate_meta_vars($event_type->ID, $meta_vars);

					foreach($vars as $_key => $_var)
						if(strpos($_key, '_') === 0) unset($vars[$_key]);
					unset($_key, $_var); // Ditch temp vars.

					validate_backtrace_max_triggers: // Target point.

					$triggers = 0; // Backtrace # of triggers that got us here.

					if(defined('DEBUG_BACKTRACE_IGNORE_ARGS')) // PHP v5.3.6+.
						$_debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
					else $_debug_backtrace = debug_backtrace();

					foreach($this->©method->get_backtrace_callers($_debug_backtrace) as $_caller)
						if($_caller === $this->___instance_config->ns_class.'->trigger') $triggers++;
					unset($_debug_backtrace, $_caller); // Housekeeping.

					if($triggers > $this->©option->get('events.triggers.max')) return; // Stop.

					trigger_processes: // Target point. Run all processes.

					$this->do_action(__FUNCTION__.'__'.$event_type->type, get_defined_vars());

					foreach($this->©event_handlers->for_type($event_type->ID) as $_handler)
						if($_handler->status !== 'deleted') $this->©event_handler->process($_handler->ID, $meta_vars, $vars);
					unset($_handler); // Housekeeping.
				}

			/**
			 * Validates meta vars against a particular event type.
			 *
			 * @param string $event_type_id_or_type Type of event (ID or type).
			 *
			 * @param array  $meta_vars Meta vars/data specific to this event (see below).
			 *
			 * @return array $meta_vars Meta vars/data specific to this event.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function populate_validate_meta_vars($event_type_id_or_type, $meta_vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array', func_get_args());

					if(!($event_type = $this->©event_type->get($event_type_id_or_type)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_missing_event_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid/missing event type: `$1%s`.'), $event_type_id_or_type)
						);
					$default_meta_vars       = array( // Default values. Meta vars are ALWAYS standardized here.

						'user'                    => NULL, // See {@link user_utils::which_types()}.
						// A NULL value does NOT indicate the current user (NULL is not allowable here).

						'user_passtag'            => NULL, // NULL; or a user passtag object.
						'aggregate_user_passtags' => NULL, // NULL; or an array of user passtag objects.
						'passtag'                 => NULL, // NULL; or a passtag object.

						'order_session_id'        => NULL, // NULL; or an order session ID.
						'transaction_id'          => NULL, // NULL; or a transaction ID.

						'futuristic_time'         => NULL, // NULL; or future UTC time.

						'unique_sha1'             => NULL // Any unique aspects (SHA1 hash).
					);
					$meta_vars               = $this->check_extension_arg_types // Validate.
						(
							array_diff($this->©user_utils->which_types(), array('null')),
							array('null', 'object'), array('null', 'array'), array('null', 'object'),
							array('null', 'integer:!empty'), array('null', 'integer:!empty'), array('null', 'integer:!empty'),
							array('null', 'string'), $default_meta_vars, $meta_vars // NO required args (all optional).
						);
					$meta_vars['event_type'] = $event_type; // Force.

					populate_validate_meta_vars__user: // Target point.

					if(isset($meta_vars['user'])) // We DO have a user to work with?
						{
							$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);
							$meta_vars['user']->populate_data(); // Populate user's data keys :-)
						}
					if($event_type->includes_user && !isset($meta_vars['user']))
						throw $this->©exception( // Invalid; MUST have a `user` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_user && isset($meta_vars['user']))
						throw $this->©exception( // Invalid; must NOT have a `user` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `user` for event type: `$1%s`.'), $event_type->type)
						);
					if(($event_type->includes_user_passtag || $event_type->includes_aggregate_user_passtags) && !$event_type->includes_user)
						throw $this->©exception( // Invalid; MUST include `user` if it includes user passtags in some way.
							$this->method(__FUNCTION__).'#event_type_must_include_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user` for event type: `$1%s`.'), $event_type->type)
						);
					if((isset($meta_vars['user_passtag']) || isset($meta_vars['aggregate_user_passtags'])) && !isset($meta_vars['user']))
						throw $this->©exception( // Invalid; MUST include `user` if it includes user passtags in some way.
							$this->method(__FUNCTION__).'#event_type_must_include_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user` for event type: `$1%s`.'), $event_type->type)
						);
					if(($event_type->includes_order_session_id || $event_type->includes_transaction_id) && !$event_type->includes_user)
						throw $this->©exception( // Invalid; MUST include `user` if it includes an order session or transaction ID.
							$this->method(__FUNCTION__).'#event_type_must_include_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user` for event type: `$1%s`.'), $event_type->type)
						);
					if((isset($meta_vars['order_session_id']) || isset($meta_vars['transaction_id'])) && !isset($meta_vars['user']))
						throw $this->©exception( // Invalid; MUST include `user` if it includes an order session or transaction ID.
							$this->method(__FUNCTION__).'#event_type_must_include_user', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__user_passtag: // Target point.

					if($event_type->includes_user_passtag && !isset($meta_vars['user_passtag']))
						throw $this->©exception( // Invalid; MUST have a `user_passtag` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_user_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `user_passtag` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_user_passtag && isset($meta_vars['user_passtag']))
						throw $this->©exception( // Invalid; must NOT have a `user_passtag` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_user_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `user_passtag` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__aggregate_user_passtags: // Target point.

					if($event_type->includes_aggregate_user_passtags && !isset($meta_vars['aggregate_user_passtags']))
						throw $this->©exception( // Invalid; MUST have `aggregate_user_passtags` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_aggregate_user_passtags', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `aggregate_user_passtags` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_aggregate_user_passtags && isset($meta_vars['aggregate_user_passtags']))
						throw $this->©exception( // Invalid; must NOT have `aggregate_user_passtags` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_aggregate_user_passtags', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `aggregate_user_passtags` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__passtag: // Target point.

					if($event_type->includes_passtag && !isset($meta_vars['passtag']))
						throw $this->©exception( // Invalid; MUST have `passtag` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. MUST have `passtag` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_passtag && isset($meta_vars['passtag']))
						throw $this->©exception( // Invalid; must NOT have `passtag` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `passtag` for event type: `$1%s`.'), $event_type->type)
						);
					if(($event_type->includes_user_passtag || $event_type->includes_aggregate_user_passtags) && $event_type->includes_passtag)
						throw $this->©exception( // Invalid; must NOT have `passtag` if it has a user passtag in some way.
							$this->method(__FUNCTION__).'#event_type_should_not_include_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `passtag` for event type: `$1%s`.'), $event_type->type)
						);
					if((isset($meta_vars['user_passtag']) || isset($meta_vars['aggregate_user_passtags'])) && isset($meta_vars['passtag']))
						throw $this->©exception( // Invalid; must NOT have `passtag` if it has a user passtag in some way.
							$this->method(__FUNCTION__).'#event_type_should_not_include_passtag', get_defined_vars(),
							sprintf($this->i18n('Invalid. Should NOT have `passtag` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__order_session_id: // Target point.

					if($event_type->includes_order_session_id && !isset($meta_vars['order_session_id']))
						throw $this->©exception( // Invalid; MUST have an `order_session_id` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_order_session_id', get_defined_vars(),
							sprintf($this->i18n('Missing `order_session_id` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_order_session_id && isset($meta_vars['order_session_id']))
						throw $this->©exception( // Invalid; should NOT have `order_session_id` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_order_session_id', get_defined_vars(),
							sprintf($this->i18n('Should NOT have `order_session_id` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__transaction_id: // Target point.

					if($event_type->includes_transaction_id && !isset($meta_vars['transaction_id']))
						throw $this->©exception( // Invalid; MUST have a `transaction_id` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_transaction_id', get_defined_vars(),
							sprintf($this->i18n('Missing `transaction_id` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_transaction_id && isset($meta_vars['transaction_id']))
						throw $this->©exception( // Invalid; should NOT have `transaction_id` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_transaction_id', get_defined_vars(),
							sprintf($this->i18n('Should NOT have `transaction_id` for event type: `$1%s`.'), $event_type->type)
						);
					populate_validate_meta_vars__futuristic_time: // Target point.

					if($event_type->includes_futuristic_time && !isset($meta_vars['futuristic_time']))
						throw $this->©exception( // Invalid; MUST have a `futuristic_time` in meta vars.
							$this->method(__FUNCTION__).'#event_type_requires_futuristic_time', get_defined_vars(),
							sprintf($this->i18n('Missing `futuristic_time` for event type: `$1%s`.'), $event_type->type)
						);
					if(!$event_type->includes_futuristic_time && isset($meta_vars['futuristic_time']))
						throw $this->©exception( // Invalid; should NOT have `futuristic_time` in meta vars.
							$this->method(__FUNCTION__).'#event_type_should_not_include_futuristic_time', get_defined_vars(),
							sprintf($this->i18n('Should NOT have `futuristic_time` for event type: `$1%s`.'), $event_type->type)
						);
					return $meta_vars; // We're good here :-)
				}

			/**
			 * IDs for an event trigger (from ``$meta_vars``).
			 *
			 * @param integer|string $event_handler_id_or_name An event handler ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @return array An array of related IDs for an event trigger.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_handler_id_or_name`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function meta_var_ids($event_handler_id_or_name, $meta_vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', func_get_args());

					$md5_cache_key = md5($event_handler_id_or_name.serialize($meta_vars));

					if(isset($this->cache[__FUNCTION__][$md5_cache_key]))
						return $this->cache[__FUNCTION__][$md5_cache_key];

					$this->cache[__FUNCTION__] = array($md5_cache_key => array());

					if(!($handler = $this->©event_handler->get($event_handler_id_or_name)))
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `%1$s`.'), $event_handler_id_or_name)
						);
					$ids = array( // Default IDs (all default to NULL; we only set these if possible).
						'user_id'          => NULL, // A user ID associated w/ this event (if applicable).
						'user_passtag_id'  => NULL, // The user passtag ID associated w/ this event (if applicable).
						'passtag_id'       => NULL, // The passtag ID associated with this event (if applicable).
						'user_passtag_ids' => NULL, // If there's a user, an array of all their user passtag IDs.
						'order_session_id' => $meta_vars['order_session_id'], // Copy of order session ID.
						'transaction_id'   => $meta_vars['transaction_id']); // Copy of transaction ID.

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					if(isset($meta_vars['user']) && $meta_vars['user']->has_id())
						$ids['user_id'] = $meta_vars['user']->ID;

					if(isset($meta_vars['user'])) // Can acquire passtag IDs?
						$ids['user_passtag_ids'] = $meta_vars['user']->passtag_ids();

					if(isset($meta_vars['user_passtag'])) // Give this highest precedence.
						{
							$ids['user_passtag_id'] = $meta_vars['user_passtag']->ID;
							$ids['passtag_id']      = $meta_vars['user_passtag']->passtag_id;

							if(isset($meta_vars['user'], $ids['user_passtag_ids'])) // Can we add this one?
								$ids['user_passtag_ids'] += array($ids['user_passtag_id'] => $ids['user_passtag_id']);
						}
					// There is a special case that should be considered BEFORE modifying an event handler.
					// Triggers that ONLY include `aggregate_user_passtags` are matched w/ ``$handler->passtag_id``.
					// Therefore, we should NOT allow conversion from a passtag event handler to a non-passtag event handler!
					// In addition, we should NOT allow a handler to change the passtag ID it's associated with either.
					// The ONLY viable solution is duplication; giving it a new ID; and deleting the old one.

					else if($handler->passtag_id && isset($meta_vars['aggregate_user_passtags'])) // Next in precedence.
						{
							foreach($meta_vars['aggregate_user_passtags'] as $_user_passtag)
								if($_user_passtag->passtag_id === $handler->passtag_id) // For this aggregate?
									{
										$ids['user_passtag_id'] = $meta_vars['user_passtag']->ID;
										$ids['passtag_id']      = $meta_vars['user_passtag']->passtag_id;

										if(isset($meta_vars['user'], $ids['user_passtag_ids'])) // Can we add this one?
											$ids['user_passtag_ids'] += array($ids['user_passtag_id'] => $ids['user_passtag_id']);

										break; // We can stop here; got what we needed :-)
									}
							unset($_user_passtag); // Housekeeping.
						}
					if(!isset($ids['passtag_id']) && isset($meta_vars['passtag']))
						$ids['passtag_id'] = $meta_vars['passtag']->ID; // Last in precedence.

					return ($this->cache[__FUNCTION__][$md5_cache_key] = $ids); // All IDs.
				}

			/**
			 * Generates a unique SHA1 hash for an event handler (by ID or name).
			 *
			 * @param integer|string $event_handler_id_or_name An event handler ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @return string A unique SHA1 hash (always 40 characters in length).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_handler_id_or_name`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function unique_sha1($event_handler_id_or_name, $meta_vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', func_get_args());

					$md5_cache_key = md5($event_handler_id_or_name.serialize($meta_vars));

					if(isset($this->cache[__FUNCTION__][$md5_cache_key]))
						return $this->cache[__FUNCTION__][$md5_cache_key];

					$this->cache[__FUNCTION__] = array($md5_cache_key => array());

					if(!($handler = $this->©event_handler->get($event_handler_id_or_name)))
						throw $this->©exception( // Exception!
							$this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
							sprintf($this->i18n('Missing event handler ID/name: `$1%s`.'), $event_handler_id_or_name)
						);
					if(!isset($meta_vars['event_type']->ID))
						throw $this->©exception( // Should NOT happen (MUST have).
							$this->method(__FUNCTION__).'#missing_meta_vars_event_type', get_defined_vars(),
							$this->i18n('Missing `event_type` (in `meta_vars`; from event trigger).')
						);
					$considerations = array('event_handler_id' => $handler->ID, 'meta_vars' => $meta_vars);
					$considerations += $this->meta_var_ids($handler->ID, $meta_vars);
					unset($considerations['user_passtag_ids']); // Do NOT consider these.

					foreach($considerations['meta_vars'] as $_key => &$_value)
						if(is_object($_value) && isset($_value->ID))
							{
								if($_key === 'user_passtag') // A new cycle is unique again :-)
									$_value = $_value->ID.$_value->time_stops.$_value->eot_time_stops;
								else $_value = $_value->ID; //  Else reduce to ID only.
							}
						else if(is_array($_value)) unset($considerations['meta_vars'][$_key]);
					unset($_key, $_value); // Housekeeping.

					$considerations = $this->©array->remove_nulls_deep($considerations);
					$considerations = $this->©array->ksort_deep($considerations);
					$considerations = sha1(serialize($considerations));

					return ($this->cache[__FUNCTION__][$md5_cache_key] = $considerations);
				}
		}
	}