<?php
/**
 * Event Notification Behaviors.
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
		 * Event Notification Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_notification_behaviors extends event_behaviors_base
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'notification'; // For parent methods.

			/**
			 * Processes a specific event notification behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event notification behavior.
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

					if(!$behavior->url) return; // No URL to notify.

					if($behavior->method === 'POST' && strpos($behavior->url, '?') > 0)
						{
							$_post_vars    = (string)$this->©url->parse($behavior->url, PHP_URL_QUERY);
							$behavior->url = strstr($behavior->url, '?', TRUE); // Strip query now.
						}
					else $_post_vars = NULL; // No POST vars (or it's NOT an HTTP POST notification).

					$this->©url->remote($behavior->url, $_post_vars, // Possible POST vars.
					                    array('method'   => $behavior->method, // GET or POST.
					                          'timeout'  => $behavior->timeout, // 5 seconds (default).
					                          'blocking' => (boolean)$behavior->blocking));

					unset($_post_vars); // Just a little housekeeping.
				}
		}
	}