<?php
/**
 * User Utilities.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Users
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * User Utilities.
		 *
		 * @package s2Member\Users
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @property \s2member\events                  $©events
		 * @property \s2member\events                  $©event
		 * @method \s2member\events ©events()
		 * @method \s2member\events ©event()
		 *
		 * @property \s2member\exception               $©exception
		 * @method \s2member\exception ©exception()
		 *
		 * @property \s2member\passtags                $©passtags
		 * @property \s2member\passtags                $©passtag
		 * @method \s2member\passtags ©passtags()
		 * @method \s2member\passtags ©passtag()
		 *
		 * @property \s2member\profile_fields          $©profile_fields
		 * @property \s2member\profile_fields          $©profile_field
		 * @method \s2member\profile_fields ©profile_fields()
		 * @method \s2member\profile_fields ©profile_field()
		 *
		 * @property \s2member\systematics             $©systematics
		 * @property \s2member\systematics             $©systematic
		 * @method \s2member\systematics ©systematics()
		 * @method \s2member\systematics ©systematic()
		 *
		 * @property \s2member\users                   $©users
		 * @property \s2member\users                   $©user
		 * @method \s2member\users ©users()
		 * @method \s2member\users ©user()
		 */
		class user_utils extends \websharks_core_v000000_dev\user_utils
		{
			/**
			 * @var array Default keys/values for basic user data.
			 */
			public $basic_data_defaults = array(
				'ID'           => 0,
				'ip'           => '',
				'email'        => '', // Minimum requirement.
				'first_name'   => '',
				'last_name'    => '',
				'full_name'    => '',
				'display_name' => ''
			);

			/**
			 * @var array Default keys/values for user session data.
			 */
			public $session_data_defaults = array(
				'ID'                => 0,
				'ip'                => '',
				'email'             => '',
				'username'          => '',
				'first_name'        => '',
				'last_name'         => '',
				'full_name'         => '',
				'display_name'      => '',
				'order_session_key' => '',
				'access_keys'       => array()
			);

			/**
			 * Which user are we working with?
			 *
			 * @see \websharks_core_v000000_dev\users::which()
			 * @inheritdoc \websharks_core_v000000_dev\users::which()
			 *
			 * @return users {@inheritdoc} Need this to get an s2Member® User object instance.
			 */
			public function which($user = NULL)
				{
					return parent::which($user);
				}

			/**
			 * Gets a WordPress® user ID.
			 *
			 * @param string               $by Searches for a user ID, by a particular type of value.
			 *
			 *    MUST be one of these values:
			 *    • `ID`
			 *    • `username`
			 *    • `email`
			 *    • `activation_key`
			 *    • `access_key`
			 *    • `user_passtag_id`
			 *    • `transaction_id`
			 *    • `gateway::subscr_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *    • `gateway::txn_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *
			 * @note If ``$by`` is set to `gateway::subscr_id` or `gateway::txn_id`, the ``$value`` MUST be an array.
			 *    The first element in the array ``$value``, MUST contain a gateway name or ID to search within. If it's a gateway name,
			 *    the gateway name is matched with an SQL LIKE search, making it possible to search for multiple gateway service variations,
			 *    such as `paypal_*`, where `*` is automatically converted to an SQL wildcard pattern.
			 *
			 * @param string|integer|array $value A value to search for (e.g. username(s), email address(es), ID(s), key(s), etc.).
			 *
			 * @return integer A WordPress® user ID, else `0`. If multiple user IDs are found, this returns the newest one.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$by`` or ``$value`` are empty.
			 *
			 * @assert ('ID', 1) === 1
			 * @assert ('username', 'Jason') === 1
			 * @assert ('email', 'jason@websharks-inc.com') === 1
			 * @assert ('email', array('foo', 'jason@websharks-inc.com')) === 1
			 * @assert ('ID', array('99999', '1')) === 1
			 */
			public function get_id_by($by, $value)
				{
					$this->check_arg_types('string:!empty', array('string:!empty', 'integer:!empty', 'array:!empty'), func_get_args());

					$by = strtolower($by); // Force lowercase for easy comparison below.

					if(in_array($by, array('id', 'username', 'email', 'activation_key'), TRUE))
						{
							if($by === 'id') $by = 'ID';

							else if($by === 'username')
								$by = 'user_login';

							else if($by === 'email')
								$by = 'user_email';

							else if($by === 'activation_key')
								$by = 'user_activation_key';

							$query =
								"SELECT".
								" `users`.`ID`".

								" FROM".
								" `".$this->©db_tables->get_wp('users')."` AS `users`".

								" WHERE".
								" `users`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" ORDER BY".
								" `users`.`ID` DESC".

								" LIMIT 1";

							if(($user_id = (integer)$this->©db->get_var($query)))
								return $user_id;
						}
					else if(in_array($by, array('access_key', 'user_passtag_id', 'transaction_id'), TRUE))
						{
							if($by === 'user_passtag_id') $by = 'ID';

							$query =
								"SELECT".
								" `user_passtags`.`user_id`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `user_passtags`.`user_id` IS NOT NULL".
								" AND `user_passtags`.`user_id` > '0'".

								" ORDER BY".
								" `user_passtags`.`user_id` DESC".

								" LIMIT 1";

							if(($user_id = (integer)$this->©db->get_var($query)))
								return $user_id;
						}
					else if(in_array($by, array('gateway::subscr_id', 'gateway::txn_id'), TRUE))
						{
							if(is_array($value) && count($value) >= 2)
								{
									$gateway = (string)array_shift($value);
									$by      = $this->©string->replace_once('gateway::', '', $by);

									if(is_numeric($gateway)) // Specified by gateway ID?
										$gateway_ids_query = "'".$this->©string->esc_sql($gateway)."'";

									else $gateway_ids_query = // By name (or wildcard).
										"SELECT".
										" `gateways`.`ID`".

										" FROM".
										" `".$this->©db_tables->get('gateways')."` AS `gateways`".

										" WHERE".
										" `gateways`.`name` LIKE '".$this->©string->esc_sql(str_replace('*', '%', like_escape($gateway)))."'";

									$transaction_ids_query =
										"SELECT".
										" `transactions`.`ID`".

										" FROM".
										" `".$this->©db_tables->get('transactions')."` AS `transactions`".

										" WHERE".
										" `transactions`.`gateway_id` IN(".$gateway_ids_query.")".
										" AND `transactions`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify($value).")";

									$query =
										"SELECT".
										" `user_passtags`.`user_id`".

										" FROM".
										" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

										" WHERE".
										" `user_passtags`.`transaction_id` IN(".$transaction_ids_query.")".

										" AND `user_passtags`.`user_id` IS NOT NULL".
										" AND `user_passtags`.`user_id` > '0'".

										" ORDER BY".
										" `user_passtags`.`user_id` DESC".

										" LIMIT 1";

									if(($user_id = (integer)$this->©db->get_var($query)))
										return $user_id;
								}
						}
					return 0; // Failure.
				}

			/**
			 * Gets basic user data (if at all possible).
			 *
			 * @param string               $by Searches for user data, by a particular type of value.
			 *
			 *    MUST be one of these values:
			 *    • `ID`
			 *    • `username`
			 *    • `email`
			 *    • `activation_key`
			 *    • `access_key`
			 *    • `user_passtag_id`
			 *    • `transaction_id`
			 *    • `gateway::subscr_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *    • `gateway::txn_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *
			 * @note If ``$by`` is set to `gateway::subscr_id` or `gateway::txn_id`, the ``$value`` MUST be an array.
			 *    The first element in the array ``$value``, MUST contain a gateway name or ID to search within. If it's a gateway name,
			 *    the gateway name is matched with an SQL LIKE search, making it possible to search for multiple gateway service variations,
			 *    such as `paypal_*`, where `*` is automatically converted to an SQL wildcard pattern.
			 *
			 * @param string|integer|array $value A value to search for (e.g. username(s), email address(es), ID(s), key(s), etc.).
			 *
			 * @param boolean              $search_for_id_by Defaults to TRUE. By default, we will also search for a user ID, and if we find one,
			 *    data associated with that user ID, will be returned by this method. Set this to FALSE, to exclude this additional search routine.
			 *    Please set this to FALSE, if you already know there is NO user ID; and/or, if you already have data associated with the user ID.
			 *
			 * @return null|object Object with basic user data (if at all possible), else NULL.
			 *    If multiple rows of data are found, we give the latest data precedence over older data.
			 *
			 *    A non-NULL object return value, will always include the following properties:
			 *       See: ``$this->basic_data_defaults`` for further details.
			 *       • The `email` property will NEVER be empty.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$by`` or ``$value`` are empty.
			 *
			 * @assert ('ID', 1) is-type 'object'
			 * @assert ('username', 'Jason') is-type 'object'
			 * @assert ('email', 'jason@websharks-inc.com') is-type 'object'
			 * @assert ('email', array('foo', 'jason@websharks-inc.com')) is-type 'object'
			 * @assert ('ID', array('99999', '1')) is-type 'object'
			 */
			public function get_basic_data_iaap_by($by, $value, $search_for_id_by = TRUE)
				{
					$this->check_arg_types('string:!empty', array('string:!empty', 'integer:!empty', 'array:!empty'), 'boolean', func_get_args());

					$by = strtolower($by); // Force lowercase for easy comparison below.

					if(($search_for_id_by || $by === 'username')
					   && ($user_id = $this->get_id_by($by, $value))
					   && is_object($user = new \WP_User($user_id))
					   && !empty($user->user_email)
					   && !empty($user->ID)
					) // An actual user.
						{
							$data = array(
								'ID'           => $user->ID,
								'email'        => $user->user_email,
								'first_name'   => $user->first_name,
								'last_name'    => $user->last_name,
								'display_name' => $user->display_name,
								'full_name'    => trim($user->first_name.' '.$user->last_name),
								'ip'           => (string)get_user_meta($user->ID, 'ip', TRUE)
							);
							return (object)array_merge($this->basic_data_defaults, $data);
						}
					else if($by === 'id')
						{
							$order_session_ids_query = array();

							$order_session_ids_query[] =
								"SELECT".
								" `order_sessions`.`ID`".

								" FROM".
								" `".$this->©db_tables->get('order_sessions')."` AS `order_sessions`".

								" WHERE".
								" `order_sessions`.`user_id` IN(".$this->©db_utils->comma_quotify((array)$value).")";

							$order_session_ids_query[] =
								"SELECT".
								" `user_passtags`.`order_session_id`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`user_id` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `user_passtags`.`order_session_id` IS NOT NULL".
								" AND `user_passtags`.`order_session_id` > '0'";

							$order_session_ids_query = '('.implode(') UNION (', $order_session_ids_query).')';
						}
					else if($by === 'email')
						{
							$order_session_ids_query =
								"SELECT".
								" `order_session_meta`.`order_session_id`".

								" FROM".
								" `".$this->©db_tables->get('order_session_meta')."` AS `order_session_meta`".

								" WHERE".
								" `order_session_meta`.`name` = 'email'".
								" AND `order_session_meta`.`value` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `order_session_meta`.`order_session_id` IS NOT NULL".
								" AND `order_session_meta`.`order_session_id` > '0'";
						}
					else if(in_array($by, array('access_key', 'user_passtag_id', 'transaction_id'), TRUE))
						{
							if($by === 'user_passtag_id')
								$by = 'ID';

							$order_session_ids_query =
								"SELECT".
								" `user_passtags`.`order_session_id`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `user_passtags`.`order_session_id` IS NOT NULL".
								" AND `user_passtags`.`order_session_id` > '0'";
						}
					else if(in_array($by, array('gateway::subscr_id', 'gateway::txn_id'), TRUE))
						{
							if(is_array($value) && count($value) >= 2)
								{
									$gateway = (string)array_shift($value);
									$by      = $this->©string->replace_once('gateway::', '', $by);

									if(is_numeric($gateway)) // Specified by gateway ID?
										$gateway_ids_query = "'".$this->©string->esc_sql($gateway)."'";

									else $gateway_ids_query = // By name (or wildcard).
										"SELECT".
										" `gateways`.`ID`".

										" FROM".
										" `".$this->©db_tables->get('gateways')."` AS `gateways`".

										" WHERE".
										" `gateways`.`name` LIKE '".$this->©string->esc_sql(str_replace('*', '%', like_escape($gateway)))."'";

									$order_session_ids_query =
										"SELECT".
										" `transactions`.`order_session_id`".

										" FROM".
										" `".$this->©db_tables->get('transactions')."` AS `transactions`".

										" WHERE".
										" `transactions`.`gateway_id` IN(".$gateway_ids_query.")".
										" AND `transactions`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify($value).")".

										" AND `transactions`.`order_session_id` IS NOT NULL".
										" AND `transactions`.`order_session_id` > '0'";
								}
						}
					if($this->©string->is_not_empty($order_session_ids_query))
						{
							$query =
								"SELECT".
								" `order_session_meta`.`name`,".
								" `order_session_meta`.`value`".

								" FROM".
								" `".$this->©db_tables->get('order_session_meta')."` AS `order_session_meta`".

								" WHERE".
								" `order_session_meta`.`order_session_id` IN(".$order_session_ids_query.")".
								" AND `order_session_meta`.`name` IN('ip', 'email', 'first_name', 'last_name')".

								" AND `order_session_meta`.`value` IS NOT NULL".
								" AND `order_session_meta`.`value` != ''".

								" ORDER BY".
								" `order_session_meta`.`time` ASC,".
								" `order_session_meta`.`order_session_id` ASC";

							if(is_array($results = $this->©db->get_results($query, OBJECT)))
								{
									$data = array(); // Initialize array of data.

									foreach($results as $_result)
										{
											if($this->©strings->are_not_empty($_result->name, $_result->value))
												$data[$_result->name] = $_result->value;
										}
									unset($_result); // Housekeeping.

									if(!empty($data['email'])) // Minimum for basic data.
										{
											$this->©string->is_not_empty_or($data['first_name'], '', TRUE);
											$this->©string->is_not_empty_or($data['last_name'], '', TRUE);

											$data['full_name']    = trim($data['first_name'].' '.$data['last_name']);
											$data['display_name'] = $this->format_registration_display_name($data);

											return (object)array_merge($this->basic_data_defaults, $data);
										}
								}
						}
					return NULL; // Failure.
				}

			/**
			 * Gets user access keys (if at all possible).
			 *
			 * @param string               $by Searches for user access keys, by a particular type of value.
			 *
			 *    MUST be one of these values:
			 *    • `ID`
			 *    • `username`
			 *    • `email`
			 *    • `activation_key`
			 *    • `access_key`
			 *    • `user_passtag_id`
			 *    • `transaction_id`
			 *    • `gateway::subscr_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *    • `gateway::txn_id` (MUST be an array, w/ first element containing the gateway name or ID)
			 *
			 * @note If ``$by`` is set to `gateway::subscr_id` or `gateway::txn_id`, the ``$value`` MUST be an array.
			 *    The first element in the array ``$value``, MUST contain a gateway name or ID to search within. If it's a gateway name,
			 *    the gateway name is matched with an SQL LIKE search, making it possible to search for multiple gateway service variations,
			 *    such as `paypal_*`, where `*` is automatically converted to an SQL wildcard pattern.
			 *
			 * @param string|integer|array $value A value to search for (e.g. username(s), email address(es), ID(s), key(s), etc.).
			 *
			 * @param boolean              $search_for_id_by Defaults to TRUE. By default, we will also search for a user ID, and if we find one,
			 *    access keys associated with that user ID, will also be returned by this method. Set this to FALSE, to exclude this additional search routine.
			 *    Please set this to FALSE, if you already know there is NO user ID; and/or, if you already have access keys associated with the user ID.
			 *
			 * @return array Array of all user access keys, else an empty array if there are none.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$by`` or ``$value`` are empty.
			 *
			 * @assert ('ID', 1) is-type 'array'
			 * @assert ('username', 'Jason') is-type 'array'
			 * @assert ('email', 'jason@websharks-inc.com') is-type 'array'
			 * @assert ('email', array('foo', 'jason@websharks-inc.com')) is-type 'array'
			 * @assert ('ID', array('99999', '1')) is-type 'array'
			 */
			public function get_access_keys_iaap_by($by, $value, $search_for_id_by = TRUE)
				{
					$this->check_arg_types('string:!empty', array('string:!empty', 'integer:!empty', 'array:!empty'), 'boolean', func_get_args());

					$access_keys = array();
					$by          = strtolower($by);

					if(($search_for_id_by || $by === 'username')
					   && ($user_id = $this->get_id_by($by, $value))
					) // An actual user.
						{
							$query =
								"SELECT".
								" `user_passtags`.`access_key`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`user_id` = '".$this->©string->esc_sql((string)$user_id)."'".

								" AND `user_passtags`.`access_key` IS NOT NULL".
								" AND `user_passtags`.`access_key` != ''";

							if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
								$access_keys = array_merge($access_keys, $result_access_keys);
						}
					if($by === 'id')
						{
							$query =
								"SELECT".
								" `user_passtags`.`access_key`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`user_id` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `user_passtags`.`access_key` IS NOT NULL".
								" AND `user_passtags`.`access_key` != ''";

							if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
								$access_keys = array_merge($access_keys, $result_access_keys);
						}
					else if($by === 'email')
						{
							$order_session_ids_query =
								"SELECT".
								" `order_session_meta`.`order_session_id`".

								" FROM".
								" `".$this->©db_tables->get('order_session_meta')."` AS `order_session_meta`".

								" WHERE".
								" `order_session_meta`.`name` = 'email'".
								" AND `order_session_meta`.`value` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `order_session_meta`.`order_session_id` IS NOT NULL".
								" AND `order_session_meta`.`order_session_id` > '0'";

							$query =
								"SELECT".
								" `user_passtags`.`access_key`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`order_session_id` IN(".$order_session_ids_query.")".

								" AND `user_passtags`.`access_key` IS NOT NULL".
								" AND `user_passtags`.`access_key` != ''";

							if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
								$access_keys = array_merge($access_keys, $result_access_keys);
						}
					else if(in_array($by, array('access_key', 'user_passtag_id', 'transaction_id'), TRUE))
						{
							if($by === 'user_passtag_id')
								$by = 'ID';

							$query =
								"SELECT".
								" `user_passtags`.`access_key`".

								" FROM".
								" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

								" WHERE".
								" `user_passtags`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify((array)$value).")".

								" AND `user_passtags`.`access_key` IS NOT NULL".
								" AND `user_passtags`.`access_key` != ''";

							if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
								$access_keys = array_merge($access_keys, $result_access_keys);
						}
					else if(in_array($by, array('gateway::subscr_id', 'gateway::txn_id'), TRUE))
						{
							if(is_array($value) && count($value) >= 2)
								{
									$gateway = (string)array_shift($value);
									$by      = $this->©string->replace_once('gateway::', '', $by);

									if(is_numeric($gateway)) // Specified by gateway ID?
										$gateway_ids_query = "'".$this->©string->esc_sql($gateway)."'";

									else $gateway_ids_query = // By name (or wildcard).
										"SELECT".
										" `gateways`.`ID`".

										" FROM".
										" `".$this->©db_tables->get('gateways')."` AS `gateways`".

										" WHERE".
										" `gateways`.`name` LIKE '".$this->©string->esc_sql(str_replace('*', '%', like_escape($gateway)))."'";

									$transaction_ids_query =
										"SELECT".
										" `transactions`.`ID`".

										" FROM".
										" `".$this->©db_tables->get('transactions')."` AS `transactions`".

										" WHERE".
										" `transactions`.`gateway_id` IN(".$gateway_ids_query.")".
										" AND `transactions`.`".$this->©string->esc_sql($by)."` IN(".$this->©db_utils->comma_quotify($value).")";

									$query =
										"SELECT".
										" `user_passtags`.`access_key`".

										" FROM".
										" `".$this->©db_tables->get('user_passtags')."` AS `user_passtags`".

										" WHERE".
										" `user_passtags`.`transaction_id` IN(".$transaction_ids_query.")".

										" AND `user_passtags`.`access_key` IS NOT NULL".
										" AND `user_passtags`.`access_key` != ''";

									if(is_array($result_access_keys = $this->©db->get_col($query)) && $result_access_keys)
										$access_keys = array_merge($access_keys, $result_access_keys);
								}
						}
					return array_unique($access_keys);
				}

			/**
			 * Validates profile fields.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @param null|integer|\WP_User|users $reader_writer The user (reader/writer) that we need to check permissions against here.
			 *
			 * @param array                       $profile_field_values An associative array of profile field values (by code).
			 *
			 * @param string                      $context One of these values: {@link fw_constants::context_registration}, {@link fw_constants::context_profile_updates}.
			 *    The context in which profile fields are being updated (defaults to {@link fw_constants::context_profile_updates}).
			 *
			 * @param array                       $args Optional. Arguments that control validation behavior.
			 *    These arguments apply to BOTH this routine, and ALSO to the underlying form fields validator.
			 *    See {@link form_fields::validate()} for further details on these arguments.
			 *
			 * @return boolean|\websharks_core_v000000_dev\errors TRUE on success; else an errors object on failure.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function validate_profile_fields($user, $reader_writer, $profile_field_values, $context = self::context_profile_updates, $args = array())
				{
					$this->check_arg_types($this->which_types(), $this->which_types(), 'array', 'string:!empty', 'array', func_get_args());

					$default_args = array(
						'check_passtag_restrictions' => TRUE, // Check passtag restrictions against user profile fields?
						'passtag_ids'                => array(), // Consider passtag IDs during registration/checkout?
						'redisplay'                  => FALSE // If considering passtag IDs, redisplay existing fields?
					);
					$all_args     = $args; // All arguments (originals).
					$args         = $this->check_extension_arg_types('boolean', 'array', 'boolean', $default_args, $args);

					if($context === $this::context_registration)
						$profile_fields = $this->©profile_fields->for_user_registration_checkout_form_fields($user, $reader_writer, $args['check_passtag_restrictions'], $this::regex_php, $args['passtag_ids'], $args['redisplay']);
					else $profile_fields = $this->©profile_fields->for_user_profile_update_form_fields($user, $reader_writer, $args['check_passtag_restrictions'], $this::regex_php);

					return $this->©form_fields->validate($profile_field_values, $profile_fields, $user, $all_args);
				}

			/**
			 * Handles `/wp-login.php` interactions for WordPress®.
			 *
			 * @attaches-to WordPress® `init` hook.
			 * @hook-priority `2` Before most everything else.
			 *    ~ But AFTER "actions"; and other VERY early hooks.
			 */
			public function wp_login_handler()
				{
					if(!$this->©env->is_wp_login()) return;

					if(defined('RELOCATE')) // Let WordPress® handle relocation.
						return; // Return now (we should NOT do anything in this special case).

					$_r = $this->©vars->_REQUEST(); // Collect all ``$_REQUEST`` vars.

					switch($this->©string->is_not_empty_or($_r['action'], 'login'))
					{
						case 'login': // Default `login` action.

								if(isset($_r['interim-login']) || isset($_r['customize-login']))
									return; // Let WordPress® handle these.

								// Handle `/wp-login.php` here.

								$username     = $this->©string->is_not_empty_or($_r['log'], '');
								$password     = $this->©string->is_not_empty_or($_r['pwd'], '');
								$remember     = (boolean)$this->©string->isset_or($_r['rememberme'], '1');
								$test_cookies = (boolean)$this->©string->isset_or($_r['testcookie'], '0');

								if($username || $password) // Something to login with?
									$this->®login($username, $password, $remember, $test_cookies).exit();

								// Else handle default display for `/wp-login.php`.

								if((force_ssl_login() || force_ssl_admin()) && !is_ssl())
									wp_redirect($this->©url->current('https')).exit();

								$this->©cookie->set(TEST_COOKIE, '1', 0);

								extract((array)$this->©action->get_call_data_for($this->dynamic_call('®login')));

								if(isset($_r['loggedout']) || isset($_r['logged_out']))
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#logged_out', get_defined_vars(),
										$this->translate('You are now logged out.')
									);
								else if(!empty($_r['registration']) && $_r['registration'] === 'disabled')
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#registration_disabled', get_defined_vars(),
										$this->translate('User registration is currently NOT allowed.')
									);
								else if(!empty($_r['checkemail']) && $_r['checkemail'] === 'confirm')
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#check_email_confirm', get_defined_vars(),
										$this->translate('Check your e-mail for the confirmation link.')
									);
								else if(!empty($_r['checkemail']) && $_r['checkemail'] === 'newpass')
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#check_email_newpass', get_defined_vars(),
										$this->translate('Check your e-mail for your new password.')
									);
								else if(!empty($_r['checkemail']) && $_r['checkemail'] === 'registered')
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#check_email_registered', get_defined_vars(),
										$this->translate('Registration complete. Please check your e-mail.')
									);
								else if($this->©string->is_not_empty($_r['redirect_to']) && strpos($_r['redirect_to'], 'about.php?updated') !== FALSE)
									$messages = $this->©message(
										$this->method(__FUNCTION__).'#log_back_in_after_wp_update', get_defined_vars(),
										$this->translate('You have successfully updated WordPress®. Please log back in.')
									);
								$this->©headers->clean_status_type(200, 'text/html', TRUE);
								exit($this->©template('login.php', get_defined_vars())->content);

						case 'password':
						case 'lostpassword':
						case 'retrievepassword': // Variations.

							// Handle `/wp-login.php?action=lostpassword` here.

								if(($username_or_email = $this->©string->is_not_empty_or($_r['user_login'], '')))
									$this->®lost_password($username_or_email).exit();

								// Else handle default display for `/wp-login.php?action=lostpassword`.

								if((force_ssl_login() || force_ssl_admin()) && !is_ssl())
									wp_redirect($this->©url->current('https')).exit();

								extract((array)$this->©action->get_call_data_for($this->dynamic_call('®lost_password')));

								$this->©headers->clean_status_type(200, 'text/html', TRUE);
								exit($this->©template('lost-password.php', get_defined_vars())->content);

						case 'rp':
						case 'resetpass': // Variations.

							// Handle `/wp-login.php?action=resetpass` here.

								$activation_key = $this->©string->is_not_empty_or($_r['key'], '');
								$password       = $this->©string->is_not_empty_or($_r['pass1'], '');

								if($activation_key || $password)
									$this->®reset_password($activation_key, $password).exit();

								// Else handle default display for `/wp-login.php?action=resetpass`.

								if((force_ssl_login() || force_ssl_admin()) && !is_ssl())
									wp_redirect($this->©url->current('https')).exit();

								extract((array)$this->©action->get_call_data_for($this->dynamic_call('®reset_password')));

								$this->©headers->clean_status_type(200, 'text/html', TRUE);
								exit($this->©template('reset-password.php', get_defined_vars())->content);

						case 'register': // Registration MUST be handled via s2Member® shortcodes.

								$systematic_registration = $this->©systematic->url('register');
								if(strpos($systematic_registration, 'wp-login.php') === FALSE)
									wp_redirect($systematic_registration).exit();
								else wp_redirect($this->©url->to_wp_home_uri()).exit();

						case 'logout': // This is simple to deal with.

								$this->®logout().exit();

						default: // There is no default here.
							// In the case of an action that we do NOT support yet (or on purpose),
							// we simply allow the action to fall through, so it can be handled by WordPress®.
							break; // Break switch handler.
					}
				}

			/**
			 * Handles user register actions.
			 *
			 * @attaches-to WordPress® hook `user_register`.
			 * @hook-priority `PHP_INT_MAX` After most everything else.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @throws exception If the ``$user`` is NOT associated with a WordPress® ID.
			 */
			public function wp_register($user)
				{
					$this->check_arg_types($this->which_types(), func_get_args());

					// Establish some important variables.

					$user = $this->which($user);

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot handle registration).')
						);
					$this->do_action('register', $user->ID, get_defined_vars());
				}

			/**
			 * Handles user login success.
			 *
			 * @attaches-to WordPress® hook `wp_login`.
			 * @hook-priority Default is fine here.
			 *
			 * @param string                      $username A WordPress® username associated with this login.
			 *
			 * @param null|integer|\WP_User|users $user The user we're working with here.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If the ``$wp_user`` is NOT associated with a WordPress® ID.
			 */
			public function wp_login_success($username, $user)
				{
					$this->check_arg_types('string', $this->which_types(), func_get_args());

					$user = $this->which($user);

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot handle login).')
						);
					$this->©db->insert($this->©db_tables->get('user_login_log'),
					                   array('user_id'       => $user->ID,
					                         'event_type_id' => $this->©event_type->id('user_login_success'),
					                         'username'      => $user->username,
					                         'ip'            => $user->ip,
					                         'time'          => time()));
					$this->do_action('login_success', $user->ID, get_defined_vars());
				}

			/**
			 * Handles user login failure.
			 *
			 * @attaches-to WordPress® hook `wp_login_failed`.
			 * @hook-priority Default is fine here.
			 *
			 * @param string   $username A WordPress® username associated with this login.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function wp_login_failure($username)
				{
					$this->check_arg_types('string', func_get_args());

					$ip = $this->©env->ip();

					$this->©db->insert($this->©db_tables->get('user_login_log'),
					                   array('user_id'       => 0,
					                         'event_type_id' => $this->©event_type->id('user_login_failure'),
					                         'username'      => $username,
					                         'ip'            => $ip,
					                         'time'          => time()));
					$this->do_action('login_failure', $username, get_defined_vars());
				}

			/**
			 * Additional user authentications.
			 *
			 * @attaches-to WordPress® filter `wp_authenticate_user`.
			 * @hook-priority `PHP_INT_MAX` After most everything else.
			 *
			 * @param \WP_User|\WP_Error $authentication A `WP_User` object on success, else a `WP_Error` object failure.
			 *
			 * @return \WP_User|\WP_Error A `WP_Error` on failure, else pass ``$authentication`` through.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function wp_authenticate_user($authentication)
				{
					$this->check_arg_types(array('\\WP_User', '\\WP_Error'), func_get_args());

					$authentication = parent::wp_authenticate_user($authentication);

					if(!($authentication instanceof \WP_User))
						return $authentication; // Already an error.

					$user = $this->which($authentication->ID); // Get user object (by ID).
					if(!$user->has_id()) return $authentication; // Sanity check.

					// If we get here, everything is good thus far (e.g. the username checks out).
					// However, WordPress® has NOT checked the password yet (that check comes AFTER this filter :-).
					// Thus, we can check `max_failed_logins` here & catch a brute force ATTACK before errors on password failure.

					$max_failed_logins          = (integer)$this->©options->get('users.user_login_log.max_failed_logins');
					$max_failed_logins_exp_time = strtotime('-'.$this->©options->get('users.user_login_log.max_failed_logins_exp_offset_time'));
					$try_again_in_approx_time   = $this->©date->approx_time_difference(time(), strtotime('+'.$this->©options->get('users.user_login_log.max_failed_logins_exp_offset_time')));

					if($max_failed_logins <= 0) return $authentication; // We're NOT checking.

					$query = // Check failed logins for this account (guards against brute force attacks).
						"SELECT".
						" `user_login_log`.`ID`".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_login_log'))."` AS `user_login_log`".

						" WHERE".
						" `user_login_log`.`username` = '".$this->©string->esc_sql($user->username)."'".
						" AND `user_login_log`.`event_type_id` = '".$this->©string->esc_sql((string)$this->©event_type->id('user_login_failure'))."'".
						" AND `user_login_log`.`time` >= '".$this->©string->esc_sql((string)$max_failed_logins_exp_time)."'";

					$failed_logins = $this->©db_utils->calc_found_rows($query);

					if($failed_logins > $max_failed_logins) // Brute force attack?
						{
							$this->©event->trigger('user_reaches_max_login_failures', compact('user'), get_defined_vars());

							return new \WP_Error ('max_failed_logins', // Custom error code.
							                      sprintf($this->translate('Max failed logins. Please wait %1$s and try again.'),
							                              $try_again_in_approx_time));
						}
					return $authentication; // Default return value.
				}

			/**
			 * Handles user logout action.
			 *
			 * @attaches-to WordPress® hook `wp_logout`.
			 * @hook-priority Default is fine here.
			 */
			public function wp_logout()
				{
					$user = $this->which(NULL); // Current user.

					$this->do_action('logout', $user, get_defined_vars());
				}

			/**
			 * Handles user profile update actions.
			 *
			 * @attaches-to WordPress® hook `profile_update`.
			 * @hook-priority `PHP_INT_MAX` After most everything else.
			 *
			 * @param null|integer|\WP_User|users  $user The user we're working with here.
			 *
			 * @param object                       $old_user_data An object containing old user data properties.
			 *
			 * @throws exception If the ``$user`` is NOT associated with a WordPress® ID.
			 */
			public function wp_profile_update($user, $old_user_data)
				{
					$this->check_arg_types($this->which_types(), 'object:!empty', func_get_args());

					// Establish some important variables.

					$user = $this->which($user);

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot handle profile update).')
						);
					// Standardize this w/ our own user properties.

					$old_user_data->email    = $old_user_data->user_email;
					$old_user_data->username = $old_user_data->user_login;
					$old_user_data->nicename = $old_user_data->user_nicename;
					$old_user_data->password = $old_user_data->user_pass;
					$old_user_data->url      = $old_user_data->user_url;
					// $old_user_data->display_name = $old_user_data->display_name; (same)
					$old_user_data->user_status       = (integer)$old_user_data->user_status;
					$old_user_data->activation_key    = $old_user_data->user_activation_key;
					$old_user_data->registration_time = strtotime($old_user_data->user_registered);

					$this->do_action('update', $user->ID, get_defined_vars());
				}

			/**
			 * Handles user deletion actions.
			 *
			 * @attaches-to WordPress® hook `delete_user`.
			 * @attaches-to WordPress® hook `wpmu_delete_user`.
			 * @attaches-to WordPress® hook `remove_user_from_blog`.
			 * @hook-priority Default is fine here.
			 *
			 * @see The `delete_user` and `deleted_user` hooks in WordPress®.
			 *
			 * @param null|integer|\WP_User|users  $user The user we're working with here.
			 *
			 * @param null|string|integer          $blog_id Optional. Defaults to a NULL value. Passed ONLY by `remove_user_from_blog`.
			 *    Note: `remove_user_from_blog` might pass a string value in some cases (it even passes an empty string in some cases).
			 *
			 * @throws exception If the ``$user`` is NOT associated with a WordPress® ID.
			 */
			public function wp_delete_user($user, $blog_id = NULL)
				{
					$this->check_arg_types($this->which_types(), array('null', 'string', 'integer'), func_get_args());

					// Establish some important variables.

					$is_remove_user_from_blog = isset($blog_id);
					$user                     = $this->which($user);

					if(!$user->has_id())
						throw $this->©exception(
							$this->method(__FUNCTION__).'#id_missing', get_defined_vars(),
							$this->i18n('The `$user` has no ID (cannot handle deletion).')
						);
					// Deals with `remove_user_from_blog` scenarios.

					if($is_remove_user_from_blog) // The `remove_user_from_blog` hook?
						if(did_action('wpmu_new_user')) // A network user was created?
							{
								$callers = $this->©method->get_backtrace_callers(debug_backtrace());

								if(in_array('add_new_user_to_blog', $callers, TRUE))
									return; // Ignore this scenario.
							}
					// Process this deletion (fire consolidating hook).

					$this->do_action('delete', $user->ID, get_defined_vars());

					$user_passtag_ids_sub_query = // All user passtag IDs (need these below).
						"SELECT `user_passtags`.`ID`". // This string is used as a sub-query below.
						" FROM `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`".
						" WHERE `user_passtags`.`user_id` = '".$this->©string->esc_sql((string)$user->ID)."'";

					$this->©db->query( // We need this query to find user passtag log entries.
						"DELETE `user_passtag_log`". // Deletes all user passtag log entries.
						" FROM `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`".
						" WHERE `user_passtag_log`.`user_passtag_id` IN(".$user_passtag_ids_sub_query.")");

					$this->©db->delete($this->©db_tables->get('user_passtags'), array('user_id' => $user->ID));
					$this->©db->delete($this->©db_tables->get('user_profile_fields'), array('user_id' => $user->ID));
					$this->©db->delete($this->©db_tables->get('user_login_log'), array('user_id' => $user->ID));
					$this->©db->delete($this->©db_tables->get('unsubscribes'), array('user_id' => $user->ID));
					$this->©db->delete($this->©db_tables->get('unsubscribes'), array('email' => $user->email));
					$this->©db->update($this->©db_tables->get('event_log'), // Delete (but not really).
					                   array('status' => 'deleted'), array('user_id' => $user->ID));
				}

			/**
			 * Makes it possible to retrieve profile fields via WordPress® ``get_user_[meta|option]()``.
			 *
			 * @attaches-to WordPress® filter `get_user_metadata`.
			 *    This filter used in conjunction w/ ``get_user_meta()``, ``get_user_option()``, and ``WP_User->has_prop()``.
			 * @filter-priority `PHP_INT_MAX` After most everything else.
			 *
			 * @param mixed                        $current_filter_value Defaults to a NULL value.
			 *    We will only attempt a profile field value lookup if this still has a NULL value.
			 *    In other words, only if it has NOT already been provided by another filter.
			 *
			 * @param null|integer|\WP_User|users  $user The user we're working with here.
			 *
			 * @param string                       $key The meta key that's being requested (possible empty string here).
			 *
			 * @param boolean                      $single Is a single value is being requested, or the entire value?
			 *
			 * @return mixed Either a string or an array (if we provide the value). Else whatever ``$current_filter_value`` is.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function get_user_metadata($current_filter_value, $user, $key, $single)
				{
					$this->check_arg_types('', $this->which_types(), 'string', 'boolean', func_get_args());

					$blog_specific_plugin_option_prefix = $this->©db->prefix.$this->___instance_config->plugin_prefix;
					$plugin_meta_prefix                 = $this->___instance_config->plugin_prefix;

					if(is_null($current_filter_value) && strpos($key, $blog_specific_plugin_option_prefix) === 0)
						{
							$user = $this->which($user); // Supports all user argument types.

							if(!is_null($value = $user->profile_field_value($this->©string->replace_once($blog_specific_plugin_option_prefix, '', $key))))
								// We need to make sure array values are always indexed numerically here, starting with `0`, because WordPress®
								// will only return the first value (i.e. the `0` index key) if ``$single`` is FALSE (which it is by default).
								return (is_array($value)) ? array_values($value) : $value;
						}
					else if(is_null($current_filter_value) && strpos($key, $plugin_meta_prefix) === 0)
						{
							$user = $this->which($user); // Supports all user argument types.

							if(!is_null($value = $user->profile_field_value($this->©string->replace_once($plugin_meta_prefix, '', $key))))
								// We need to make sure array values are always indexed numerically here, starting with `0`, because WordPress®
								// will only return the first value (i.e. the `0` index key) if ``$single`` is FALSE (which it is by default).
								return (is_array($value)) ? array_values($value) : $value;
						}
					return $current_filter_value; // Default return value.
				}
		}
	}