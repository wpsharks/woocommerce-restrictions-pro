<?php
/**
 * Event Passtag Behaviors.
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
		 * Event Passtag Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_passtag_behaviors extends event_behaviors_base
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'passtag'; // For parent methods.

			/**
			 * Processes a specific event passtag behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event passtag behavior.
			 *
			 * @param array          $meta_vars Meta vars/data specific to this event; see {@link events::trigger()}.
			 *
			 * @param array          $vars Variables defined in the scope of the calling routine.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$id_or_name`` is empty for some reason.
			 * @throws exception If ``$meta_vars`` is empty.
			 */
			public function process($id_or_name, $meta_vars, $vars)
				{
					$this->check_arg_types(array('integer:!empty', 'string:!empty'), 'array:!empty', 'array', func_get_args());

					if(!($behavior = $this->get($id_or_name)))
						throw $this->©exception( // Should NOT happen.
							$this->method(__FUNCTION__).'#behavior_missing', get_defined_vars(),
							sprintf($this->i18n('Missing event behavior ID/name: `$1%s`.'), $id_or_name)
						);
					if(!($behavior_type = $this->©behavior_type->get($behavior->behavior_type_id)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_behavior_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid behavior type: `$1%s`.'), $behavior->behavior_type_id)
						);
					$behavior = clone $behavior; // Need a shallow clone.

					if($behavior->status !== 'active') return; // Not even active?

					if(!$behavior->passtag_id) return; // MUST have a passtag obviously.

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					if($behavior->user_id) // A specific user (by ID)?
						{
							$user = $this->©user_utils->which($behavior->user_id);
							if(!$user->has_id()) return; // Not possible.
						}
					else if(!($user = $meta_vars['user']))
						return; // Not possible.

					switch($behavior_type->type) // Based on behavior type.
					{
						# Adds a particular passtag to a particular user (most common).

						case 'add_user_passtag': // Add (w/ default functionality).
								$user->add_passtag($behavior->passtag_id);
								break; // Break switch handler.

						case 'add_user_passtag_x': // Add (exclude descendants).
								$user->add_passtag_x($behavior->passtag_id);
								break; // Break switch handler.

						# Removes a particular passtag from a particular user.

						case 'remove_user_passtag': // Remove (w/ default functionality).
								$user->remove_passtag($behavior->passtag_id);
								break; // Break switch handler.

						case 'remove_user_passtag_x': // Remove (exclude descendants).
								$user->remove_passtag_x($behavior->passtag_id);
								break; // Break switch handler.

						default: // There is NO default behavior.
							break; // Break switch handler.

					}
				}
		}
	}