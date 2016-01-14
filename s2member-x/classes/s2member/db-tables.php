<?php
/**
 * Database Tables.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\DB_Tables
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Database Tables.
		 *
		 * @package s2Member\DB_Tables
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class db_tables extends \websharks_core_v000000_dev\db_tables
		{
			/**
			 * Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					$mysql_tables_dir = $this->©dir->n_seps_up(__FILE__, 3).'/includes/mysql-tables';

					$this->install_file   = $mysql_tables_dir.'/install.sql';
					$this->upgrade_file   = $mysql_tables_dir.'/upgrade.sql';
					$this->uninstall_file = $mysql_tables_dir.'/uninstall.sql';

					$this->tables = array(
						'behavior_types',

						'coupons',
						'coupon_limits',
						'coupon_limit_types',
						'coupon_log',
						'coupon_modifiers',

						'diagnostic_log',
						'diagnostic_messages',
						'diagnostic_meta',

						'esps',
						'esp_meta',
						'esp_segment_types',
						'esp_segment_vars',

						'event_behaviors',
						'event_behavior_statuses',
						'event_code_behaviors',
						'event_email_behaviors',
						'event_handlers',
						'event_log',
						'event_log_meta',
						'event_notification_behaviors',
						'event_passtag_behaviors',
						'event_redirect_behaviors',
						'event_status_behaviors',
						'event_types',

						'gateways',
						'gateway_meta',

						'geo_areas',

						'order_sessions',
						'order_session_items',
						'order_session_item_meta',
						'order_session_item_types',
						'order_session_meta',

						'passtags',
						'passtag_relationships',
						'passtag_restrictions',
						'passtag_wp_caps',

						'profile_fields',
						'profile_field_conversions',
						'profile_field_conversion_types',
						'profile_field_meta',
						'profile_field_permissions',
						'profile_field_types',
						'profile_field_validations',
						'profile_field_validation_patterns',
						'profile_field_values',

						'restriction_types',

						'taxes',
						'tax_rates',

						'transactions',
						'transaction_meta',

						'unsubscribes',

						'user_login_log',
						'user_passtags',
						'user_passtag_log',
						'user_profile_fields'
					);
				}
		}
	}