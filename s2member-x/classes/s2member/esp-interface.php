<?php
/**
 * ESP Interface.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\ESPs
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * ESP Interface.
		 *
		 * @package s2Member\ESPs
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		interface esp_interface
		{
			/**
			 * Checks if the current ESP has been implemented by the site owner.
			 *
			 * @return boolean TRUE if the current ESP has been implemented by the site owner.
			 */
			public function is_implemented();

			/**
			 * Subscribes an email address.
			 *
			 * @param string|array                $segment Segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 *    An already parsed array MUST include these elements (at minimum).
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're working with.
			 *
			 * @param boolean                     $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the subscribe action should NOT send a welcome email or ask the user to confirm.
			 *    IMPORTANT: This may NOT be supported by all ESPs. If the ESP supports it, please implement it as such.
			 *
			 * @param array                       $other_vars Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual underlying API call in this routine.
			 *
			 * @return boolean TRUE if the address was subscribed, else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function subscribe($segment, $user = NULL, $silently = FALSE, $other_vars = array());

			/**
			 * Updates existing subscriber data.
			 *
			 * @param null|string|array           $segment Segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an update for ALL subscribed segments.
			 *    In other words, a NULL value indicates that an update should occur for each of the user's currently subscribed segments.
			 *
			 *    An already parsed array MUST include these elements (at minimum).
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're working with.
			 *
			 * @param string                      $prev_email Optional. Defaults to an empty string.
			 *    If the email address is changing, please pass this in.
			 *
			 * @param array                       $other_vars Optional. This defaults to an empty array.
			 *    Any other vars that are accepted by the actual underlying API call in this routine.
			 *
			 * @return integer The number of updates that occurred; else `0` by default.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function update($segment = NULL, $user = NULL, $prev_email = '', $other_vars = array());

			/**
			 * Unsubscribes an email address.
			 *
			 * @param null|string|array           $segment Segment specs. Defaults to a NULL value.
			 *    NULL, a string, or an already parsed array of segment specs. A NULL value indicates an unsubscribe from ALL subscribed segments.
			 *    In other words, a NULL value indicates that an unsubscribe should occur for each of the user's currently subscribed segments.
			 *
			 *    An already parsed array MUST include these elements (at minimum).
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're working with.
			 *
			 * @param boolean                     $silently Optional. Defaults to a FALSE value.
			 *    If this is TRUE, the unsubscribe action should NOT send a goodbye email or ask the user to confirm.
			 *    IMPORTANT: This may NOT be supported by all ESPs. If the ESP supports it, please implement it as such.
			 *
			 * @param array                       $other_vars Optional. This defaults to an empty array.
			 *    Any other vars that are accepted by the actual underlying API call in this routine.
			 *
			 * @return boolean TRUE if the address was unsubscribed, else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function unsubscribe($segment = NULL, $user = NULL, $silently = FALSE, $other_vars = array());

			/**
			 * Moves an email address from one segment to another.
			 *
			 * @param null|string|array           $old_segment Old segment specs.
			 *    NULL, a string, or an already parsed array of segment specs (e.g. to specify a specific segment).
			 *    A NULL value indicates a transition for ALL subscribed segments. In other words, a NULL value indicates
			 *    that a transition to ``$new_segment``, should occur for each of the user's currently subscribed segments.
			 *
			 *    An already parsed array MUST include these elements (at minimum).
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param string|array                $new_segment New segment specs.
			 *    Either a string, or an already parsed array of segment specs.
			 *
			 *    An already parsed array MUST include these elements (at minimum).
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|users $user Optional. The user we're working with.
			 *
			 * @param boolean|null                $silently Defaults to a NULL value.
			 *
			 *    • NULL — Transition only (silent, but the user MUST exist on at least one ``$old_segment``).
			 *       If this is NULL, the user MUST exist on at least one ``$old_segment``, before we allow a transition.
			 *       When they exist on an ``$old_segment`` (we're simply moving them); and that always occurs silently.
			 *
			 *    • FALSE — Transition, else subscribe (subscribe is NOT handled silently).
			 *       If this is FALSE, the user does NOT need to exist before a transition occurs.
			 *       That is, if they do NOT exist on any ``$old_segment``, they'll be subscribed to the ``$new_segment`` (but NOT silently).
			 *       We say "(FALSE)NOT silent", because a confirmation email WILL be sent if they do NOT exist on any ``$old_segment``.
			 *       We treat them as a brand new subscriber (w/ a confirmation email if they do NOT exist on any ``$old_segment``).
			 *
			 *    • TRUE — Transition, else subscribe (subscribe is silent, please use with caution).
			 *       If this is TRUE (the same applies); the user does NOT need to exist before a transition occurs.
			 *       However, if they do NOT exist on any ``$old_segment``, they'll be subscribed to the ``$new_segment`` (silently).
			 *       We say "(TRUE)silent", because a confirmation email will NOT be sent, even if they do NOT exist on any ``$old_segment``.
			 *       We treat them as a brand new subscriber (but w/o a confirmation email if they do NOT exist on any ``$old_segment``).
			 *       IMPORTANT: This may NOT be supported by all ESPs. If the ESP supports it, please implement it as such.
			 *
			 *    • In any of these scenarios, an actual "transition" always occur silently.
			 *       In other words, when/if they DO exist on an ``$old_segment`` (we're simply moving them silently — in all cases).
			 *
			 *    • In any of these scenarios, if the user IS currently subscribed, we will NOT subscribe them to the ``$new_segment``,
			 *       unless we can successfully unsubscribe them from an ``$old_segment`` (or, if they do NOT exist at all, in the case of `FALSE|TRUE`).
			 *
			 * @param array                       $other_vars_unsubscribe Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual API call, for the underlying unsubscribe (or `move`) action in this routine.
			 *
			 * @param array                       $other_vars_subscribe Optional. Defaults to an empty array.
			 *    Any other vars that are accepted by the actual API call, for the underlying subscribe action in this routine.
			 *
			 * @return integer The number of transitions that occurred; else `0` by default.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$old_segment`` is NOT NULL; and it's empty (or is an invalid array of specs).
			 * @throws exception If ``$new_segment`` is empty (or is an invalid array of specs).
			 * @throws exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function transition($old_segment, $new_segment, $user = NULL, $silently = NULL, $other_vars_unsubscribe = array(), $other_vars_subscribe = array());
		}
	}