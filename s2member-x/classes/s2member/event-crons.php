<?php
/**
 * Event Crons.
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
		 * Event Crons.
		 *
		 * @package s2Member\Events
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class event_crons extends framework
		{
			/**
			 * @var integer CRON offset time.
			 * @note This CANNOT be changed here.
			 * @see events::$cron_jobs
			 */
			public $offset_time = 900; // Every 15 minutes.

			/**
			 * @var integer Current - offset time.
			 * @by-constructor Set by class constructor.
			 */
			public $offset_time_ago;

			/**
			 * @var integer CRON per span (per day; by default).
			 * @by-constructor Set by class constructor.
			 */
			public $cron_processes_in_span;

			/**
			 * @var integer Default MySQL limit.
			 * @by-constructor Set by class constructor.
			 */
			public $default_limit;

			/**
			 * @var integer Min futuristic time.
			 * @by-constructor Set by class constructor.
			 */
			public $min_futuristic_time;

			/**
			 * @var integer Max futuristic time.
			 * @by-constructor Set by class constructor.
			 */
			public $max_futuristic_time;

			/**
			 * Constructor (Event Crons).
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					$this->time            = time(); // UTC time.
					$this->offset_time_ago = $this->time - $this->offset_time;

					$default_limit_span_offset_time = // Default is `1 day`.
						$this->©option->get('events.crons.default_limit_span_offset_time');
					$default_limit_span_time        = strtotime('+'.$default_limit_span_offset_time) - time();

					$this->cron_processes_in_span = $default_limit_span_time / $this->offset_time;
					$this->default_limit          = (integer)$this->©option->get('events.crons.default_limit');

					$this->min_futuristic_time = // Default is `1 day`. Nothing sooner.
						strtotime('+'.$this->©option->get('events.crons.min_futuristic_offset_time'));

					$this->max_futuristic_time = // Default is `1 year`. Nothing farther away than this.
						strtotime('+'.$this->©option->get('events.crons.max_futuristic_offset_time'));
				}

			/**
			 * CRON job (reprocesses events with an `offset_time`).
			 *
			 * @see {@link events::wp_loaded()} and {@link events::$cron_jobs}.
			 */
			public function event_processor()
				{
					$query = // NOTE: This does NOT care about status.
						"SELECT".
						" `event_log`.*".

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('event_log'))."` AS `event_log`".

						" WHERE".
						" AND `event_log`.`processed_time` = '0'".
						" AND `event_log`.`process_time` IS NOT NULL".
						" AND `event_log`.`process_time` <= '".$this->©string->esc_sql((string)$this->time)."'";

					$calc_found_rows = $this->©db_utils->calc_found_rows($query);
					$limit           = max($this->default_limit, $calc_found_rows / $this->cron_processes_in_span);

					$query .= " ORDER BY `event_log`.`process_time` ASC"; // Those waiting the longest time.
					$query .= " LIMIT ".$limit; // So we can process ALL within 1 day w/o exceeding time/memory limits.

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									$this->©event_log->update_processed_time($_result->ID); // Reprocess ONCE!
									// In if an EXCEPTION occurs for any reason; this will be ignored completely.
									// Avoids reprocessing corrupted events by failing after this DB update :-)

									$_result->meta_vars = $this->©event_log->get_meta_values($_result->ID);
									$_result->vars      = (array)maybe_unserialize($_result->vars);

									if(!$_result->meta_vars) // Missing meta vars for this log entry?
										{
											$this->©error( // This should NOT happen (but just in case it does).
												$this->method(__FUNCTION__).'#missing_meta_vars', get_defined_vars(), // For diagnostic reporting.
												sprintf($this->i18n('Missing `meta_vars` for event log entry ID: `%1$s`.'), $_result->ID));
											continue; // Not possible WITHOUT these.
										}
									$this->©event_handler->process($_result->event_handler_id, $_result->meta_vars, $_result->vars, $_result->ID);
								}
							unset($_result); // Housekeeping.
						}
				}

			/**
			 * CRON job (triggers `before_user_passtag_reaches_time_stops`).
			 *
			 * @see {@link events::wp_loaded()} and {@link events::$cron_jobs}.
			 */
			public function user_passtags_before_time_stops()
				{
					$query           = // Triggers `before_user_passtag_reaches_time_stops`.
						"SELECT".
						" `user_passtags`.*". // This query detects `time_stops`.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`".

						" WHERE".
						" `user_passtags`.`status` != 'deleted'". // Exclude deleted user passtags.
						" AND `user_passtags`.`last_cron_time._before_time_stops` <= '".$this->©string->esc_sql((string)$this->offset_time_ago)."'".

						" AND (". // If either of these conditions are TRUE, the user passtag expires in the future.

						"     (`user_passtags`.`eot_time_stops` IS NULL".
						"        AND (`user_passtags`.`last_cron_value._before_time_stops` IS NULL".
						"           OR `user_passtags`.`last_cron_value._before_time_stops` != `user_passtags`.`time_stops`)".
						"        AND `user_passtags`.`time_stops` IS NOT NULL AND `user_passtags`.`time_stops` >= '0'".
						"        AND `user_passtags`.`time_stops`". // Anywhere within this time frame.
						"           BETWEEN '".$this->©string->esc_sql((string)$this->min_futuristic_time)."'".
						"              AND '".$this->©string->esc_sql((string)$this->max_futuristic_time)."'".
						"     )".
						"     OR (`user_passtags`.`eot_time_stops` IS NOT NULL".
						"        AND (`user_passtags`.`last_cron_value._before_time_stops` IS NULL".
						"           OR `user_passtags`.`last_cron_value._before_time_stops` != `user_passtags`.`eot_time_stops`)".
						"        AND `user_passtags`.`eot_time_stops` IS NOT NULL AND `user_passtags`.`eot_time_stops` >= '0'".
						"        AND `user_passtags`.`eot_time_stops`". // Anywhere within this time frame.
						"           BETWEEN '".$this->©string->esc_sql((string)$this->min_futuristic_time)."'".
						"              AND '".$this->©string->esc_sql((string)$this->max_futuristic_time)."'".
						"     )".
						" )";
					$calc_found_rows = $this->©db_utils->calc_found_rows($query);
					$limit           = max($this->default_limit, $calc_found_rows / $this->cron_processes_in_span);

					$query .= " ORDER BY `user_passtags`.`last_cron_time._before_time_stops` ASC,". // Those waiting the longest time.
					          " `user_passtags`.`eot_time_stops` IS NOT NULL ASC, `user_passtags`.`time_stops` ASC"; // Additional precedence.
					$query .= " LIMIT ".$limit; // So we can process ALL within 1 day w/o exceeding time/memory limits.

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->user_id) // Has user ID?
										$_user = $this->©user($_result->user_id);
									else $_user = $this->©user(NULL, 'user_passtag_id', $_result->ID);

									if(isset($_result->eot_time_stops)) // Determine EOT time.
										$_time_stops = $_result->eot_time_stops;
									else $_time_stops = $_result->time_stops;

									$_user->update_passtag($_result->ID, // Update time first :-)
									                       array('last_cron_time._before_time_stops' => $this->time));

									if(!($_user_passtag = $_user->passtag($_result->ID)))
										throw $this->©exception( // Exception!
											$this->method(__FUNCTION__).'#missing_user_passtag', get_defined_vars(),
											sprintf($this->i18n('Missing user passtag ID: `$1%s`.'), $_result->ID)
										);
									$this->©event->trigger('before_user_passtag_reaches_time_stops',
									                       array('user'            => $_user, // This user.
									                             'user_passtag'    => $_user_passtag, // User passtag.
									                             'futuristic_time' => $_time_stops // Future time.
									                       ), get_defined_vars()); // Plus all defined vars.
								}
							unset($_result, $_user, $_user_passtag, $_time_stops); // Housekeeping.
						}
				}

			/**
			 * CRON job (triggers `user_passtag_reaches_time_stops`).
			 *
			 * @see {@link events::wp_loaded()} and {@link events::$cron_jobs}.
			 */
			public function user_passtags_time_stops()
				{
					$query           = // Triggers `user_passtag_reaches_time_stops`.
						"SELECT".
						" `user_passtags`.*". // This query detects `time_stops`.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`".

						" WHERE".
						" `user_passtags`.`status` != 'deleted'". // Exclude deleted user passtags.
						" AND `user_passtags`.`last_cron_time._time_stops` <= '".$this->©string->esc_sql((string)$this->offset_time_ago)."'".

						" AND (". // If either of these conditions are TRUE, the user passtag expires now.

						"     (`user_passtags`.`eot_time_stops` IS NULL".
						"        AND (`user_passtags`.`last_cron_value._time_stops` IS NULL".
						"           OR `user_passtags`.`last_cron_value._time_stops` != `user_passtags`.`time_stops`)".
						"        AND `user_passtags`.`time_stops` IS NOT NULL AND `user_passtags`.`time_stops` >= '0'".
						"        AND `user_passtags`.`time_stops` <= '".$this->©string->esc_sql((string)$this->time)."'".
						"     )".
						"     OR (`user_passtags`.`eot_time_stops` IS NOT NULL".
						"        AND (`user_passtags`.`last_cron_value._time_stops` IS NULL".
						"           OR `user_passtags`.`last_cron_value._time_stops` != `user_passtags`.`eot_time_stops`)".
						"        AND `user_passtags`.`eot_time_stops` IS NOT NULL AND `user_passtags`.`eot_time_stops` >= '0'".
						"        AND `user_passtags`.`eot_time_stops` <= '".$this->©string->esc_sql((string)$this->time)."'".
						"     )".
						" )";
					$calc_found_rows = $this->©db_utils->calc_found_rows($query);
					$limit           = max($this->default_limit, $calc_found_rows / $this->cron_processes_in_span);

					$query .= " ORDER BY `user_passtags`.`last_cron_time._time_stops` ASC,". // Those waiting the longest time.
					          " `user_passtags`.`eot_time_stops` IS NOT NULL ASC, `user_passtags`.`time_stops` ASC"; // Additional precedence.
					$query .= " LIMIT ".$limit; // So we can process ALL within 1 day w/o exceeding time/memory limits.

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->user_id) // Has user ID?
										$_user = $this->©user($_result->user_id);
									else $_user = $this->©user(NULL, 'user_passtag_id', $_result->ID);

									if(isset($_result->eot_time_stops)) // Determine EOT time.
										$_time_stops = $_result->eot_time_stops;
									else $_time_stops = $_result->time_stops;

									$_user->update_passtag($_result->ID, // Update these first :-)
									                       array('last_cron_time._time_stops'  => $this->time,
									                             'last_cron_value._time_stops' => $_time_stops));
									$_user->deactivate_passtag($_result->ID, $_time_stops);

									if(!($_user_passtag = $_user->passtag($_result->ID)))
										throw $this->©exception( // Exception!
											$this->method(__FUNCTION__).'#missing_user_passtag', get_defined_vars(),
											sprintf($this->i18n('Missing user passtag ID: `$1%s`.'), $_result->ID)
										);
									$this->©event->trigger('user_passtag_reaches_time_stops',
									                       array('user'         => $_user, // This user.
									                             'user_passtag' => $_user_passtag // User passtag.
									                       ), get_defined_vars()); // Plus all defined vars.
								}
							unset($_result, $_user, $_user_passtag, $_time_stops); // Housekeeping.
						}
				}

			/**
			 * CRON job (triggers `user_passtag_reaches_uses_limit`).
			 *
			 * @see {@link events::wp_loaded()} and {@link events::$cron_jobs}.
			 */
			public function user_passtags_max_uses()
				{
					$query = // Triggers `user_passtag_reaches_uses_limit`.
						"SELECT".
						" MAX(`user_passtag_log`.`time`) AS `last_use_time`,". // Last use time :-)
						" `user_passtags`.*". // This query pulls user passtags that MIGHT be at max uses.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`".

						" WHERE".
						" `user_passtags`.`status` != 'deleted'". // Exclude deleted user passtags.
						" AND `user_passtags`.`last_cron_time._max_uses` <= '".$this->©string->esc_sql((string)$this->offset_time_ago)."'".
						" AND `user_passtags`.`time_starts` <= '".$this->©string->esc_sql((string)$this->time)."'".

						" AND `user_passtags`.`ID` = `user_passtag_log`.`user_passtag_id`". // Join log entries.
						" AND `user_passtag_log`.`user_passtag_id` IS NOT NULL AND `user_passtag_log`.`user_passtag_id` > '0'".
						" AND (`user_passtags`.`last_cron_value._max_uses` IS NULL OR `user_passtags`.`last_cron_value._max_uses` < `last_use_time`)";

					$calc_found_rows = $this->©db_utils->calc_found_rows($query);
					$limit           = max($this->default_limit, $calc_found_rows / $this->cron_processes_in_span);

					$query .= " ORDER BY `user_passtags`.`last_cron_time._max_uses` ASC,". // Those waiting the longest time.
					          " `user_passtags`.`time_starts` ASC"; // Additional precedence. Those in use for the longest time.
					$query .= " LIMIT ".$limit; // So we can process ALL within 1 day w/o exceeding time/memory limits.

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->user_id) // Has user ID?
										$_user = $this->©user($_result->user_id);
									else $_user = $this->©user(NULL, 'user_passtag_id', $_result->ID);

									$_user->update_passtag($_result->ID, // Update these first :-)
									                       array('last_cron_time._max_uses'  => $this->time,
									                             'last_cron_value._max_uses' => $this->last_use_time));

									if(!($_user_passtag = $_user->passtag($_result->ID)))
										throw $this->©exception( // Exception!
											$this->method(__FUNCTION__).'#missing_user_passtag', get_defined_vars(),
											sprintf($this->i18n('Missing user passtag ID: `$1%s`.'), $_result->ID)
										);
									$_passtag_is_within_uses_limit = $_user->passtag_is_within_uses_limit($_user_passtag, $this::array_a);

									if(!$_passtag_is_within_uses_limit['boolean'])
										$this->©event->trigger('user_passtag_reaches_uses_limit',
										                       array('user'         => $_user, // This user.
										                             'user_passtag' => $_user_passtag, // User passtag.
										                             'unique_sha1'  => sha1($this->last_use_time)
										                       ), get_defined_vars()); // Plus all defined vars.
								}
							unset($_result, $_user, $_user_passtag, $_passtag_is_within_uses_limit); // Housekeeping.
						}
				}

			/**
			 * CRON job (triggers `user_passtag_reaches_ips_limit`).
			 *
			 * @see {@link events::wp_loaded()} and {@link events::$cron_jobs}.
			 */
			public function user_passtags_max_ips()
				{
					$query = // Triggers `user_passtag_reaches_ips_limit`.
						"SELECT".
						" MAX(`user_passtag_log`.`time`) AS `last_use_time`,". // Last use time :-)
						" `user_passtags`.*". // This query pulls user passtags that MIGHT be at max IPs.

						" FROM".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtag_log'))."` AS `user_passtag_log`,".
						" `".$this->©string->esc_sql($this->©db_tables->get('user_passtags'))."` AS `user_passtags`".

						" WHERE".
						" `user_passtags`.`status` != 'deleted'". // Exclude deleted user passtags.
						" AND `user_passtags`.`last_cron_time._max_ips` <= '".$this->©string->esc_sql((string)$this->offset_time_ago)."'".
						" AND `user_passtags`.`time_starts` <= '".$this->©string->esc_sql((string)$this->time)."'".

						" AND `user_passtags`.`ID` = `user_passtag_log`.`user_passtag_id`". // Join log entries.
						" AND `user_passtag_log`.`user_passtag_id` IS NOT NULL AND `user_passtag_log`.`user_passtag_id` > '0'".
						" AND (`user_passtags`.`last_cron_value._max_ips` IS NULL OR `user_passtags`.`last_cron_value._max_ips` < `last_use_time`)";

					$calc_found_rows = $this->©db_utils->calc_found_rows($query);
					$limit           = max($this->default_limit, $calc_found_rows / $this->cron_processes_in_span);

					$query .= " ORDER BY `user_passtags`.`last_cron_time._max_ips` ASC,". // Those waiting the longest time.
					          " `user_passtags`.`time_starts` ASC"; // Additional precedence. Those in use for the longest time.
					$query .= " LIMIT ".$limit; // So we can process ALL within 1 day w/o exceeding time/memory limits.

					if(is_array($results = $this->©db->get_results($query, OBJECT)))
						{
							foreach($this->©db_utils->typify_results_deep($results) as $_result)
								{
									if($_result->user_id) // Has user ID?
										$_user = $this->©user($_result->user_id);
									else $_user = $this->©user(NULL, 'user_passtag_id', $_result->ID);

									$_user->update_passtag($_result->ID, // Update these first :-)
									                       array('last_cron_time._max_ips'  => $this->time,
									                             'last_cron_value._max_ips' => $this->last_use_time));

									if(!($_user_passtag = $_user->passtag($_result->ID)))
										throw $this->©exception( // Exception!
											$this->method(__FUNCTION__).'#missing_user_passtag', get_defined_vars(),
											sprintf($this->i18n('Missing user passtag ID: `$1%s`.'), $_result->ID)
										);
									$_passtag_is_within_ips_limit = $_user->passtag_is_within_ips_limit($_user_passtag, $this::array_a);

									if(!$_passtag_is_within_ips_limit['boolean'])
										$this->©event->trigger('user_passtag_reaches_ips_limit',
										                       array('user'         => $_user, // This user.
										                             'user_passtag' => $_user_passtag, // User passtag.
										                             'unique_sha1'  => sha1($this->last_use_time)
										                       ), get_defined_vars()); // Plus all defined vars.
								}
							unset($_result, $_user, $_user_passtag, $_passtag_is_within_ips_limit); // Housekeeping.
						}
				}
		}
	}