<?php
/**
 * Event Code Behaviors.
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
		 * Event Code Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_code_behaviors extends event_behaviors_base
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'code'; // For parent methods.

			/**
			 * Processes a specific code (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event code behavior.
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
							sprintf($this->i18n('Missing behavior ID/name: `$1%s`.'), $id_or_name)
						);
					$behavior = clone $behavior; // Need a shallow clone.

					if($behavior->status !== 'active') return; // Not even active?

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					$behavior       = $this->©strings->ireplace_codes_deep($behavior, $meta_vars, $vars, TRUE);
					$behavior->code = trim($this->©php->evaluate($behavior->code, $meta_vars + $vars));

					if(!strlen($behavior->code)) return; // No code to display.
					if(!$behavior->hook) return; // No action hook (or filter) to display on.
					// Some codes are intended for processing only; they are NOT displayed anywhere.

					add_action($behavior->hook, function ($filter_value = NULL) use ($behavior)
						{
							echo $behavior->code; // Displays code in this location.

							return $filter_value; // Passes through any value.

						}, $behavior->hook_priority);
				}
		}
	}