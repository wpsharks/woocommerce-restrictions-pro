<?php
/**
 * Event Redirect Behaviors.
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
		 * Event Redirect Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_redirect_behaviors extends event_behaviors_base
		{
			/**
			 * @var boolean Redirecting?
			 */
			public $are_redireting = FALSE;

			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'redirect'; // For parent methods.

			/**
			 * Processes a specific event redirect behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event redirect behavior.
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
					$behavior = clone $behavior; // Need a shallow clone.

					if($behavior->status !== 'active') return; // Not even active?

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					$behavior      = $this->©strings->ireplace_codes_deep($behavior, $meta_vars, $vars, TRUE, TRUE);
					$behavior->url = $this->©php->evaluate($behavior->url, $meta_vars + $vars);

					if(!$behavior->url) return; // No URL to redirect to.

					wp_redirect($behavior->url, $this->©url->redirect_browsers_using_302_status(301));

					$this->are_redireting = TRUE; // Yes, event redirects ARE redirecting.
				}
		}
	}