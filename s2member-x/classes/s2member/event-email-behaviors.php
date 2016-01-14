<?php
/**
 * Event Email Behaviors.
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
		 * Event Email Behaviors.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_email_behaviors extends event_behaviors_base
		{
			/**
			 * @var string Type of event behavior.
			 */
			public $type = 'email'; // For parent methods.

			/**
			 * Processes a specific event email behavior (by ID or name).
			 *
			 * @param integer|string $id_or_name The ID (or name) of an event email behavior.
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
					$behavior = clone $behavior; // Need a shallow clone of these properties.

					if($behavior->status !== 'active') return; // Not active?

					if(isset($meta_vars['user'])) // For IDEs; properties/methods.
						$meta_vars['user'] = $this->©user_utils->which($meta_vars['user']);

					if(!$behavior->from_addr || !$behavior->recipients || !$behavior->subject || !$behavior->message)
						return; // Missing component(s); CANNOT send in this case.

					$behavior->recipients = $this->©string->ireplace_codes($behavior->recipients, $meta_vars, $vars, FALSE, ', ');
					$behavior->recipients = $this->©php->evaluate($behavior->recipients, $meta_vars + $vars);

					if(!($behavior->recipients = $this->©mail->parse_emails_deep($behavior->recipients)))
						return; // No recipients (or unable to parse anything valid).

					if($behavior->respects_unsubscribes) // Respect unsubscribes?
						if(!($behavior->recipients = $this->©unsubscribes->filter($behavior->recipients)))
							return; // No allowable recipients.

					foreach($behavior->recipients as $_recipient) // Each recipient separately.
						{
							$_behavior = clone $behavior; // We need a shallow clone (below) for each iteration.
							unset($_behavior->recipients); // We DON'T need these here; already got them above :-)

							$_behavior->recipient = $vars['recipient'] = $_recipient; // For each `recipient` address.

							if(isset($meta_vars['user']) && $meta_vars['user']->has_id() && $_recipient === $meta_vars['user']->email)
								$_behavior->unsubscribe_url = $vars['unsubscribe_url'] = $this->©unsubscribe->url($_recipient, $meta_vars['user']->ID);
							else $_behavior->unsubscribe_url = $vars['unsubscribe_url'] = $this->©unsubscribe->url($_recipient);

							$_behavior = $this->©strings->ireplace_codes_deep($_behavior, $meta_vars, $vars, TRUE, FALSE, ', ');

							$_properties_with_php_tags = // Any of these properties may contain PHP tags.
								array('from_name', 'from_addr', 'subject', 'headers', 'attachments', 'message');

							foreach($_behavior as $_property => &$_value) // Support for PHP tags.
								if(in_array($_property, $_properties_with_php_tags, TRUE) && $this->©string->is_not_empty($_value))
									$_value = $this->©php->evaluate($_value, $meta_vars + $vars);
							unset($_property, $_value); // Housekeeping.

							if(isset($_behavior->headers)) $_behavior->headers = maybe_unserialize($_behavior->headers);
							if(isset($_behavior->headers) && !is_array($_behavior->headers)) // Or a line-delimited list of headers.
								$_behavior->headers = preg_split('/['."\r\n".']+/', $_behavior->headers, NULL, PREG_SPLIT_NO_EMPTY);

							if(isset($_behavior->attachments)) $_behavior->attachments = maybe_unserialize($_behavior->attachments);
							if(isset($_behavior->attachments) && !is_array($_behavior->attachments)) // Or a line-delimited list of attachments.
								$_behavior->attachments = preg_split('/['."\r\n".']+/', $_behavior->attachments, NULL, PREG_SPLIT_NO_EMPTY);

							if(!$_behavior->from_addr || !$_behavior->recipient || !$_behavior->subject || !$_behavior->message)
								continue; // Missing component(s); CANNOT send in this case.

							$this->©mail->send(array('from_name'   => (string)$_behavior->from_name, 'from_addr' => $_behavior->from_addr,
							                         'recipients'  => $_behavior->recipient, 'headers' => (array)$_behavior->headers,
							                         'subject'     => $_behavior->subject, 'message' => $_behavior->message,
							                         'attachments' => (array)$_behavior->attachments));
						}
					unset($_recipient, $_behavior, $_properties_with_php_tags); // Housekeeping.
				}
		}
	}