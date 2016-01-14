<?php
/**
 * Event ESP Behaviors.
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
		 * Event ESP Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_esp_behaviors extends event_behaviors_base
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'esp'; // For parent methods.

			/**
			 * Processes a specific event ESP behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event ESP behavior.
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
					if(!($esp = $this->©esp->get($behavior->esp_id)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_esp', get_defined_vars(),
						                        sprintf($this->i18n('Invalid ESP ID: `$1%s`.'), $behavior->esp_id)
						);
					if(!($behavior_type = $this->©behavior_type->get($behavior->behavior_type_id)))
						throw $this->©exception($this->method(__FUNCTION__).'#invalid_behavior_type', get_defined_vars(),
						                        sprintf($this->i18n('Invalid behavior type: `$1%s`.'), $behavior->behavior_type_id)
						);
					$behavior = clone $behavior; // Need a shallow clone.

					if($behavior->status !== 'active') return; // Not even active?

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					if(!isset($meta_vars['user']) || !$meta_vars['user']->is_populated()) return;
					if(!call_user_func(array($this, '©esps__'.$esp->name.'.is_implemented'))) return;

					switch($behavior_type->type) // Based on behavior type.
					{
						case 'esp_subscribe': // ESP subscriptions.

								if($behavior->esp_segment_type_id && $behavior->segment)
									call_user_func_array(array($this, '©esps__'.$esp->name.'.subscribe'),
									                     array($behavior->segment, $meta_vars['user']));

								break; // Break switch handler.

						case 'esp_silent_subscribe': // ESP silent subscriptions.

								if($behavior->esp_segment_type_id && $behavior->segment)
									call_user_func_array(array($this, '©esps__'.$esp->name.'.subscribe'),
									                     array($behavior->segment, $meta_vars['user'], TRUE));

								break; // Break switch handler.

						case 'esp_unsubscribe': // ESP unsubscribes.

								if($behavior->esp_segment_type_id && ($behavior->segment || is_null($behavior->segment)))
									call_user_func_array(array($this, '©esps__'.$esp->name.'.unsubscribe'),
									                     array($behavior->segment, $meta_vars['user']));

								break; // Break switch handler.

						case 'esp_silent_unsubscribe': // ESP silent unsubscribes.

								if($behavior->esp_segment_type_id && ($behavior->segment || is_null($behavior->segment)))
									call_user_func_array(array($this, '©esps__'.$esp->name.'.unsubscribe'),
									                     array($behavior->segment, $meta_vars['user'], TRUE));

								break; // Break switch handler.

						case 'esp_transition': // ESP transitions.

								if($behavior->esp_segment_type_id && $behavior->segment)
									if($behavior->old_esp_segment_type_id && ($behavior->old_segment || is_null($behavior->old_segment)))
										call_user_func_array(array($this, '©esps__'.$esp->name.'.transition'),
										                     array($behavior->old_segment, $behavior->segment, $meta_vars['user']));

								break; // Break switch handler.

						case 'esp_transition_subscribe': // ESP transition subscribes.

								if($behavior->esp_segment_type_id && $behavior->segment)
									if($behavior->old_esp_segment_type_id && ($behavior->old_segment || is_null($behavior->old_segment)))
										call_user_func_array(array($this, '©esps__'.$esp->name.'.transition'),
										                     array($behavior->old_segment, $behavior->segment, $meta_vars['user'], FALSE));

								break; // Break switch handler.

						case 'esp_silent_transition_subscribe': // ESP silent transition subscribes.

								if($behavior->esp_segment_type_id && $behavior->segment)
									if($behavior->old_esp_segment_type_id && ($behavior->old_segment || is_null($behavior->old_segment)))
										call_user_func_array(array($this, '©esps__'.$esp->name.'.transition'),
										                     array($behavior->old_segment, $behavior->segment, $meta_vars['user'], TRUE));

								break; // Break switch handler.

						case 'esp_sync_profile': // ESP sync user profile data.

								if($behavior->esp_segment_type_id && ($behavior->segment || is_null($behavior->segment)))
									if($this->©string->is_not_empty($vars['old_user_data']['email']))
										call_user_func_array(array($this, '©esps__'.$esp->name.'.update'),
										                     array($behavior->segment, $meta_vars['user'], $vars['old_user_data']['email']));

								break; // Break switch handler.

						default: // There is NO default behavior.

							break; // Break switch handler.

					}
				}
		}
	}