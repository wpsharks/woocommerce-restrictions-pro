<?php
/**
 * Event Handlers.
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
		 * Event Handlers.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_handlers extends framework
		{
			/**
			 * Process (or reprocess) a specific event handler (by ID or name).
			 *
			 * @param integer|string $id_or_name An event handler ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @param null|integer   $reprocessing_log_id Optional. Defaults to a NULL value.
			 *    Integer indicates it's for a log entry that is being reprocessed after `offset_time`.
			 *    An integer causes this routine to ignore any `offset_time`. We process now.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function process($id_or_name, $meta_vars, $vars, $reprocessing_log_id = NULL)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array',
					                       array('null', 'integer:!empty'), func_get_args());

					$event_processed = FALSE; // Initialize return value. Are we processing?

					if(!($handler = $this->get($id_or_name))) // Should NOT happen. Missing handler?
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `$1%s`.'), $id_or_name));

					if(!($event_type = $this->©event_types->get($handler->event_type_id))) // Should NOT happen.
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_event_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid event type ID: `$1%s`.'), $handler->event_type_id));

					if($reprocessing_log_id) // Make sure ``$meta_vars`` are still OK :-)
						$meta_vars = $this->©event->populate_validate_meta_vars($event_type->ID, $meta_vars);

					$handler = clone $handler; // Need a shallow clone of this handler (see mods below).

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					# Is event handler still active? If NOT, stop here.

					check_active_status: // Target point. MUST be `active`.

					if($handler->status !== 'active') goto finale;

					# Does the handler apply to this user?

					check_user: // Target point.

					if($handler->user_id === 0) // Indicates N/A.
						goto check_passtags; // Don't care about user.

					if(!isset($handler->user_id)) // NULL indicates any (MUST have).
						{
							if(!isset($meta_vars['user']))
								goto finale; // Ignore.
							goto check_passtags; // We're good here.
						}
					if($handler->user_id < 0) // -1 indicates NO (MUST NOT have user).
						{
							if(isset($meta_vars['user']))
								goto finale; // Ignore.
							goto check_passtags; // We're good here.
						}
					// Any other value indicates a specific user (by ID).
					if(!isset($meta_vars['user']) || !$meta_vars['user']->has_id()
					   || $meta_vars['user']->ID !== $handler->user_id
					) goto finale; // It's NOT for this user.

					# Connected to a specific passtag ID, or no?

					check_passtags: // Target point.

					if($handler->passtag_id === 0) // Zero indicates N/A.
						goto check_offset_time; // Don't care about passtag.

					if(!isset($handler->passtag_id))
						{
							if(!isset($meta_vars['user_passtag']) && !isset($meta_vars['passtag']))
								goto finale; // Ignore. NULL indicates any passtag (MUST have passtag).
							goto check_offset_time; // We're good here.
						}
					if($handler->passtag_id < 0)
						{
							if(isset($meta_vars['user_passtag']) || isset($meta_vars['passtag']))
								goto finale; // Ignore. -1 indicates NO passtag (MUST NOT have passtag).
							goto check_offset_time; // We're good here.
						}
					// Any other value indicates a specific passtag (by ID).
					if(isset($meta_vars['user_passtag'])) // Highest precedence.
						{
							if($meta_vars['user_passtag']->passtag_id === $handler->passtag_id)
								goto check_offset_time; // Yes, this passtag :-)
							goto finale; // NOT related to this passtag.
						}
					if(isset($meta_vars['aggregate_user_passtags'])) // Next precedence.
						{
							foreach($meta_vars['aggregate_user_passtags'] as $_user_passtag)
								if($_user_passtag->passtag_id === $handler->passtag_id)
									goto check_offset_time; // Yes, this passtag :-)
							goto finale; // NOT related to any.
						}
					if(isset($meta_vars['passtag'])) // Last precedence.
						if($meta_vars['passtag']->ID === $handler->passtag_id)
							goto check_offset_time; // Yes, this passtag :-)

					goto finale; // NOT related to any.

					# Is it time to process this event, or no?

					check_offset_time: // Target point (we also validate `offset_time` here).

					if(isset($meta_vars['futuristic_time']) && $handler->offset_time >= 0) goto finale;
					if(!isset($meta_vars['futuristic_time']) && $handler->offset_time < 0) goto finale;

					if($reprocessing_log_id) $handler->offset_time = 0; // If reprocessing; time is now :-)

					if(isset($meta_vars['futuristic_time']) && $handler->offset_time < 0) // Futuristic?
						if($meta_vars['futuristic_time'] - time() > abs($handler->offset_time))
							goto finale; // Too far in the future (wait for next trigger).
						else $handler->offset_time = 0; // The time is now :-)

					# Does this event trigger overlap?

					check_consolidate_overlapping: // Target point.

					if($handler->consolidate && $this->is_overlapping($handler->ID, $meta_vars, $vars))
						goto finale; // Nothing to do in this case.

					# Does this handler have any behaviors?

					check_active_behaviors: // Target point.

					foreach(($behaviors = $this->©event_behaviors->for_($handler->ID)) as $_key => $_behavior)
						if($_behavior->status !== 'active' || $_behavior->behavior_type_id <= 1) unset($behaviors[$_key]);
					unset($_key, $_behavior); // Behavior types `0` and `1` indicate `none`; or a default behavior.

					if(!$behaviors) goto finale; // No active behaviors; ignore :-)

					# Is this a unique event for this handler? Do we even care?

					check_unique_sha1_instances: // Target point. Requires DB query.

					if($handler->unique) // Respond only to unique event triggers for this handler?
						if($this->©event_log->has_prev_unique_sha1_instances($handler->ID, $meta_vars, $reprocessing_log_id))
							goto finale; // This is a duplicate; ignore completely.

					# Should we process, queue; or ignore this event completely?

					check_occurrences: // Target point. Requires DB query.

					if(!$handler->start_after_nth && !$handler->stop_after_nth)
						goto check_conditions; // Don't care about the # of occurrences.

					$previous_occurrences = // Automatically considers occurrence-related options.
						$this->©event_log->has_previous_occurrences($handler->ID, $meta_vars, $reprocessing_log_id);

					if($handler->start_after_nth) // Start after X occurrences?
						if($previous_occurrences < $handler->start_after_nth) // Wait?
							goto event_log_instance; // Log only, do NOT process.

					if($handler->stop_after_nth) // Stop after X occurrences?
						if($previous_occurrences >= $handler->stop_after_nth) // Stop?
							goto finale; // Ignore this & all future instances.

					# Has custom conditions? If so, are they TRUE?

					check_conditions: // Target point.

					if($handler->conditions && !$this->©php->¤eval('return ('.$handler->conditions.');', $meta_vars + $vars))
						goto finale; // Conditions (when they exist); MUST evaluate to TRUE; else stop here.

					# Event should be processed now; or in the future?

					check_future_offset_time: // Target point.

					if($handler->offset_time) goto event_log_future_offset_time;

					# If we've fallen through this far; process & log it now :-)

					process_now: // Target point. Process & log this event now.

					foreach($behaviors as $_behavior) switch($_behavior->behavior_type)
					{
						case 0: // All behavior types; (integer)string = `0`.

								if($this->is_conflicting_behavior // Conflicts in some way?
									($handler->ID, $meta_vars, $vars, $_behavior->behavior_type_id)
								) break; // Behavior would cause a conflict (stop here).

						// Else fall through and continue checking behavior type.

						case 'code': // Code behaviors; see {@link event_code_behaviors}.

								$this->©event_code_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'email': // Email behaviors; see {@link event_email_behaviors}.

								$this->©event_email_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'esp': // Email service provider; see {@link event_esp_behaviors}.

								$this->©event_esp_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'notification': // Notification behaviors; see {@link event_notification_behaviors}.

								$this->©event_notification_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'passtag': // Passtag behaviors; see {@link event_passtag_behaviors}.

								$this->©event_passtag_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'redirect': // Redirect behaviors; see {@link event_redirect_behaviors}.

								$this->©event_redirect_behaviors->process_all($_behavior->ID, $meta_vars, $vars);

								break; // Break switch handler.

						case 'renew_user_passtag': // See {@link process_own_behavior()}.
						case 'renew_aggregate_user_passtags':
						case 'renew_all_user_passtags':

						case 'reactivate_user_passtag':
						case 'reactivate_aggregate_user_passtags':
						case 'reactivate_all_user_passtags':

						case 'deactivate_user_passtag':
						case 'deactivate_aggregate_user_passtags':
						case 'deactivate_all_user_passtags':

						case 'delete_user_passtag':
						case 'delete_aggregate_user_passtags':
						case 'delete_all_user_passtags':

								$this->process_own_behavior($_behavior->behavior_type_id, $meta_vars, $vars);

								break; // Break switch handler.

						default: // Default case handler (NO behavior).

							break; // Break switch handler.
					}
					unset($_behavior); // Just a little housekeeping.

					# Processed above; now log this event and we're done :-)

					event_process_log: // Target point.

					$event_processed = TRUE; // Flag this TRUE (see below).

					if($reprocessing_log_id) // Reprocessing after `offset_time`?
						goto finale; // NOTE: `processed_time` already updated by CRON job.

					$this->©event_log->insert(array( // Log as a new processed event :-)
					                                 'event_handler_id' => $handler->ID,
					                                 'meta_vars'        => $meta_vars, 'vars' => $vars,
					                                 'processed_time'   => time()));

					goto finale; // Jump down to finale. Do NOT to fall through.

					# We will process this event in the future (based on `offset_time`).

					event_log_future_offset_time: // Target point.

					if($reprocessing_log_id) // Reprocessing w/ `offset_time`? (VERY wrong).
						throw $this->©exception( // Throw exception as a STRONG WARNING. Should NOT happen!
							$this->method(__FUNCTION__).'#unexpected_reprocessing_offset_time', get_defined_vars(),
							sprintf($this->i18n('Unexpected offset time upon reprocessing log ID: `$1%s`.'), $reprocessing_log_id)
						);
					$this->©event_log->insert(array( // Schedule future processing.
					                                 'event_handler_id' => $handler->ID,
					                                 'meta_vars'        => $meta_vars, 'vars' => $vars,
					                                 'process_time'     => time() + $handler->offset_time));

					goto finale; // Jump down to finale. Do NOT to fall through.

					# Logging as instance only; did NOT actually run.

					event_log_instance: // Target point.

					if($reprocessing_log_id) // Reprocessing after `offset_time`?
						goto finale; // Nothing to do here. This is NOT a new instance.

					$this->©event_log->insert(array( // Log this as a new instance.
					                                 'event_handler_id' => $handler->ID,
					                                 'meta_vars'        => $meta_vars, 'vars' => $vars,
					                                 'processed_time'   => -1));

					finale: // Target point. We're almost done now :-)

					if($reprocessing_log_id && !$event_processed) // Fail silently?
						// Here we update `processed_time` to `-1` (recording an instance only).
						// This happens if the handler configuration was modified since original process.
						// This can also occur if a handler is no longer `active` (e.g. we're skipping this).
						$this->©event_log->update_processed_time($reprocessing_log_id, -1);

					grand_finale: // Target point. All done now.
				}

			/**
			 * Processes event handler having own behavior.
			 *
			 * @param integer|string $behavior_type_id_or_type A behavior type ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$behavior_type_id_or_type`` is NOT own behavior.
			 * @throws exception If ``$behavior_type_id_or_type`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function process_own_behavior($behavior_type_id_or_type, $meta_vars, $vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array', func_get_args());

					if(!($behavior_type = $this->©behavior_types->get($behavior_type_id_or_type)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_behavior_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid behavior type ID/name: `$1%s`.'), $behavior_type_id_or_type)
						);
					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					switch($behavior_type->type) // Based on behavior type.
					{
						# User passtag renewals (specific one, aggregates, or ALL).

						case 'renew_user_passtag': // Renew user passtag?

								if(isset($meta_vars['user'], $meta_vars['user_passtag']))
									$meta_vars['user']->renew_passtag($meta_vars['user_passtag']->ID);

								break; // Break switch handler.

						case 'renew_aggregate_user_passtags': // Renew aggregate user passtags?

								if(isset($meta_vars['user'], $meta_vars['aggregate_user_passtags']) && $meta_vars['aggregate_user_passtags'])
									$meta_vars['user']->renew_passtags($meta_vars['aggregate_user_passtags']);

								break; // Break switch handler.

						case 'renew_all_user_passtags': // Renew all user passtags?

								if(isset($meta_vars['user']))
									$meta_vars['user']->renew_passtags();

								break; // Break switch handler.

						# User passtag reactivations (specific one, aggregates, or ALL).

						case 'reactivate_user_passtag': // Reactivate user passtag?

								if(isset($meta_vars['user'], $meta_vars['user_passtag']))
									$meta_vars['user']->reactivate_passtag($meta_vars['user_passtag']->ID);

								break; // Break switch handler.

						case 'reactivate_aggregate_user_passtags': // Reactivate aggregate user passtags?

								if(isset($meta_vars['user'], $meta_vars['aggregate_user_passtags']) && $meta_vars['aggregate_user_passtags'])
									$meta_vars['user']->reactivate_passtags($meta_vars['aggregate_user_passtags']);

								break; // Break switch handler.

						case 'reactivate_all_user_passtags': // Reactivate all user passtags?

								if(isset($meta_vars['user']))
									$meta_vars['user']->reactivate_passtags();

								break; // Break switch handler.

						# User passtag deactivations (specific one, aggregates, or ALL).

						case 'deactivate_user_passtag': // Deactivate user passtag?

								if(isset($meta_vars['user'], $meta_vars['user_passtag']))
									$meta_vars['user']->deactivate_passtag($meta_vars['user_passtag']->ID);

								break; // Break switch handler.

						case 'deactivate_aggregate_user_passtags': // Deactivate aggregate user passtags?

								if(isset($meta_vars['user'], $meta_vars['aggregate_user_passtags']) && $meta_vars['aggregate_user_passtags'])
									$meta_vars['user']->deactivate_passtags($meta_vars['aggregate_user_passtags']);

								break; // Break switch handler.

						case 'deactivate_all_user_passtags': // Deactivate all user passtags?

								if(isset($meta_vars['user']))
									$meta_vars['user']->deactivate_passtags();

								break; // Break switch handler.

						# User passtag deletions (specific one, aggregates, or ALL).

						case 'delete_user_passtag': // Delete user passtag?

								if(isset($meta_vars['user'], $meta_vars['user_passtag']))
									$meta_vars['user']->delete_passtag($meta_vars['user_passtag']->ID);

								break; // Break switch handler.

						case 'delete_aggregate_user_passtags': // Delete aggregate user passtags?

								if(isset($meta_vars['user'], $meta_vars['aggregate_user_passtags']) && $meta_vars['aggregate_user_passtags'])
									$meta_vars['user']->delete_passtags($meta_vars['aggregate_user_passtags']);

								break; // Break switch handler.

						case 'delete_all_user_passtags': // Delete all user passtags?

								if(isset($meta_vars['user']))
									$meta_vars['user']->delete_passtags();

								break; // Break switch handler.

						default: // Exception!
							throw $this->©exception( // Exception!
								$this->method(__FUNCTION__).'#unexpected_behavior', get_defined_vars(),
								sprintf($this->i18n('Unexpected behavior (NOT own): `$1%s`.'), $behavior_type->type)
							);
					}
				}

			/**
			 * Checks for an overlapping event trigger.
			 *
			 * @param integer|string $id_or_name An event handler ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event (see below).
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @return boolean TRUE if the event is overlapping in some way.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$meta_vars`` is empty.
			 *
			 * @TODO Need to finalize this method by identifying overlapping event handlers.
			 */
			public function is_overlapping($id_or_name, $meta_vars, $vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array', func_get_args());

					if(!($handler = $this->get($id_or_name)))
						throw $this->©exception( // Exception!
							$this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
							sprintf($this->i18n('Missing event handler ID/name: `$1%s`.'), $id_or_name)
						);
					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					switch($handler->event_type) // Most conflicts are detected first by event type.
					{
						case 'user_reaches_passtag_time_stops': // See {@link passtag_restrictions}.
						case 'user_reaches_passtag_uses_limit':
						case 'user_reaches_passtag_ips_limit':

								break; // Break switch handler.
					}
					return FALSE; // Not overlapping.
				}

			/**
			 * Checks for a conflicting event handler behavior.
			 *
			 * @param integer|string $id_or_name An event handler ID or name.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event (see below).
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @param integer|string $behavior_type_id_or_type A behavior type ID or name.
			 *
			 * @return boolean TRUE if the behavior causes a conflict in some way.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$behavior_type_id_or_type`` is empty.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function is_conflicting_behavior($id_or_name, $meta_vars, $vars, $behavior_type_id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array',
					                       array('integer:!empty', 'string:!empty'), func_get_args());

					if(!($handler = $this->get($id_or_name)))
						throw $this->©exception( // Exception!
							$this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
							sprintf($this->i18n('Missing event handler ID/name: `$1%s`.'), $id_or_name)
						);
					if(!($behavior_type = $this->©behavior_types->get($behavior_type_id_or_type)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_behavior_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid behavior type ID/name: `$1%s`.'), $behavior_type_id_or_type)
						);
					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					switch($handler->event_type) // Most conflicts are detected first by event type.
					{
						case 'user_reaches_passtag_time_stops': // See {@link passtag_restrictions}.
						case 'user_reaches_passtag_uses_limit':
						case 'user_reaches_passtag_ips_limit':

								if($behavior_type->type === 'redirect' && empty($vars['behave']))
									return TRUE; // See {@link passtag_restrictions}.

								break; // Break switch handler.

						case 'user_loses_passtag': // User is losing a passtag (inactive/deleted).

								if($behavior_type->type === 'deactivate_user_passtag')
									return TRUE; // Stop ridiculous scenario.

								break; // Break switch handler.

						case 'user_regains_passtag': // User regains a passtag (now active).

								if($behavior_type->type === 'reactivate_user_passtag')
									return TRUE; // Stop ridiculous scenario.

								break; // Break switch handler.

						case 'user_renews_passtag': // User passtag is renewed for more time.

								if($behavior_type->type === 'renew_user_passtag')
									return TRUE; // Stop ridiculous scenario.

								if($behavior_type->type === 'reactivate_user_passtag')
									return TRUE; // Stop ridiculous scenario.

								break; // Break switch handler.
					}
					switch($behavior_type->type) // Other checks first by behavior type.
					{
						case 'redirect': // Redirects are one of the trickiest behaviors to consider.

								if($handler->offset_time) return TRUE; // Not possible.

								if(isset($meta_vars['user']) && !$meta_vars['user']->is_current())
									return TRUE; // NO redirect; this is NOT the current user.

								break; // Break switch handler.
					}
					return FALSE; // No conflicts.
				}

			/**
			 * Updates status of a specific event handler (by ID or name).
			 *
			 * @param integer|string $id_or_name An event handler ID or name.
			 *
			 * @param string         $status The new status we want to update with.
			 *
			 * @return integer The number of columns updated (should be `0` or `1` at all times).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$status`` is an empty string.
			 */
			public function update_status($id_or_name, $status)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'string:!empty', func_get_args());

					if(!($handler = $this->get($id_or_name)))
						throw $this->©exception($this->method(__FUNCTION__).'#handler_missing', get_defined_vars(),
						                        sprintf($this->i18n('Missing event handler ID/name: `%1$s`.'), $id_or_name)
						);
					$this->©db_cache->update($this->method('get_all'), NULL);

					return (integer)$this->©db->update($this->©db_tables->get('event_handlers'),
					                                   array('status' => $status), array('ID' => $handler->ID));
				}

			/**
			 * Gets event handlers for a specific event type.
			 *
			 * @param integer|string $event_type_id_or_type The ID (or name) of an event type.
			 *
			 * @return array An array of event handler objects, else an empty array.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$event_type_id_or_type`` is empty.
			 */
			public function for_type($event_type_id_or_type)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$event_handlers = $this->get_all();

					if(is_integer($event_type_id_or_type) && isset($event_handlers['by_event_type_id'][$event_type_id_or_type]))
						return $event_handlers['by_event_type_id'][$event_type_id_or_type];

					if(is_string($event_type_id_or_type) && isset($event_handlers['by_event_type'][$event_type_id_or_type]))
						return $event_handlers['by_event_type'][$event_type_id_or_type];

					return array(); // Default return value.
				}

			/**
			 * Gets a specific event handler (by ID or name).
			 *
			 * @param integer|string $id_or_name An event handler ID or name.
			 *
			 * @return null|object An event handler object, else NULL if unavailable.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty.
			 */
			public function get($id_or_name)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), func_get_args());

					$event_handlers = $this->get_all();

					if(is_integer($id_or_name) && isset($event_handlers['by_id'][$id_or_name]))
						return $event_handlers['by_id'][$id_or_name];

					if(is_string($id_or_name) && isset($event_handlers['by_name'][$id_or_name]))
						return $event_handlers['by_name'][$id_or_name];

					return NULL; // Default return value.
				}

			/**
			 * Gets all event handlers.
			 *
			 * @return array All event handlers.
			 */
			public function get_all()
				{
					$db_cache_key = $this->method(__FUNCTION__);

					if(is_array($cache = $this->©db_cache->get($db_cache_key)))
						return $cache; // Already cached these.

					$event_handlers = array(); // Initialize.

					$query = // All configured event handlers (by site owner).
						"SELECT".
						" `event_types`.`type` AS `event_type`,".
						" `event_handlers`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_types'))."` AS `event_types`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_handlers'))."` AS `event_handlers`".

						" WHERE".
						" `event_handlers`.`event_type_id` = `event_types`.`ID`".
						" AND `event_handlers`.`event_type_id` IS NOT NULL".
						" AND `event_handlers`.`event_type_id` > '0'".

						" AND `event_types`.`type` IS NOT NULL".
						" AND `event_types`.`type` != ''".

						" ORDER BY `event_handlers`.`order` ASC";

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$event_handlers['by_id'][$_result->ID]     = $_result;
									$event_handlers['by_name'][$_result->name] =& $event_handlers['by_id'][$_result->ID];

									$event_handlers['by_event_type_id'][$_result->event_type_id][$_result->ID] =& $event_handlers['by_id'][$_result->ID];
									$event_handlers['by_event_type'][$_result->event_type][$_result->ID]       =& $event_handlers['by_id'][$_result->ID];

									$event_handlers['by_passtag_id'][(integer)$_result->passtag_id][$_result->ID]                                        =& $event_handlers['by_id'][$_result->ID];
									$event_handlers['by_passtag_id_event_type_id'][(integer)$_result->passtag_id][$_result->event_type_id][$_result->ID] =& $event_handlers['by_id'][$_result->ID];
								}
							unset($_result); // Housekeeping.
						}
					return $this->©db_cache->update($db_cache_key, $event_handlers);
				}
		}
	}