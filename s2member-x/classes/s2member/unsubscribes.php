<?php
/**
 * Unsubscribes.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Unsubscribes
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Unsubscribes.
		 *
		 * @package s2Member\Unsubscribes
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class unsubscribes extends framework
		{
			/**
			 * Unsubscribe link/URL generator.
			 *
			 * @param string       $email Email address to be unsubscribed :-)
			 *
			 * @param null|integer $user_id Optional. Defaults to a NULL value; no user ID.
			 *
			 * @return string Unsubscribe link/URL (does NOT expire).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function url($email, $user_id = NULL)
				{
					$this->check_arg_types('string:!empty', array('null', 'integer:!empty'), func_get_args());

					if(!isset($this->cache[__FUNCTION__]))
						$this->cache[__FUNCTION__] = // Public (the same for all).
							$this->©action->url_for_call($this->dynamic_call('®unsubscribe'), $this::public_type);

					$args = array('email' => $email, 'user_id' => $user_id);

					return add_query_arg(urlencode_deep($args), $this->cache[__FUNCTION__]);
				}

			/**
			 * Unsubscribe action handler (registered).
			 *
			 * @param null|string  $email Optional. Email address.
			 *    If this is empty, we try ``$_REQUEST['email']`` as a fallback.
			 *
			 * @param null|integer $user_id Optional. Defaults to a NULL value; no user ID.
			 *    If this is empty, we try ``$_REQUEST['user_id']`` as a fallback.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function ®unsubscribe($email = NULL, $user_id = NULL)
				{
					$this->check_arg_types(array('null', 'string'), array('null', 'integer'), func_get_args());

					if(!func_get_args()) // Use ``$_REQUEST``?
						{
							$email   = (string)$this->©vars->_REQUEST('email');
							$user_id = (integer)$this->©vars->_REQUEST('user_id');
						}
					if(!$email) $email = ''; // Empty string otherwise (results in failure).
					if($user_id <= 0) $user_id = NULL; // Force NULL otherwise.

					unsubscribe: // Target point. Do unsubscribe (if possible).

					if(!$email) goto unsubscribe_failure; // No email?

					$this->©db->replace($this->©db_table->get('unsubscribes'),
					                    array('email' => $email, 'user_id' => $user_id, 'time' => time()));

					$successes = $this->©success( // For template.
						$this->method(__FUNCTION__), get_defined_vars(),
						$this->translate('Unsubscribe successful. ~ Thank you.'));

					goto template_display_handler; // All done :-)

					unsubscribe_failure: // Unable to unsubscribe.

					$errors = $this->©error( // For template.
						$this->method(__FUNCTION__), get_defined_vars(),
						$this->translate('Missing email address. ~ Please try again.'));

					template_display_handler: // Target point.

					$this->©headers->clean_status_type(200, 'text/html', TRUE);
					$this->©action->set_call_data_for($this->dynamic_call(__FUNCTION__), get_defined_vars());
					exit($this->©template('unsubscribe.php', get_defined_vars())->content);
				}

			/**
			 * Filters an array of emails by excluding unsubscribed addresses.
			 *
			 * @param array $emails An array of email addresses to filter.
			 *
			 * @return array Emails after excluding unsubscribed addresses.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function filter($emails)
				{
					$this->check_arg_types('array:!empty', func_get_args());

					$emails = $this->©array->to_one_dimension($emails);
					$emails = $this->©string->ify_deep($emails);
					$emails = array_map('strtolower', $emails);

					$query = // Filters unsubscribed email addresses.
						"SELECT".
						" `unsubscribes`.`email`".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('unsubscribes'))."` AS `unsubscribes`".

						" WHERE".
						" `unsubscribes`.`email` IN(".$this->©db_utils->comma_quotify($emails).")".
						" AND `unsubscribes`.`email` IS NOT NULL".
						" AND `unsubscribes`.`email` != ''";

					if(is_array($unsubscribes = $this->©db->get_col($query)))
						$emails = array_diff($emails, array_map('strtolower', $unsubscribes));

					return $emails;
				}
		}
	}