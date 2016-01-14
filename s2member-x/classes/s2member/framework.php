<?php
/**
 * s2Member® Framework.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		if(!class_exists('\\'.__NAMESPACE__.'\\framework'))
			{
				/*
				 * WebSharks™ Core dependency.
				 */
				require_once dirname(dirname(dirname(__FILE__))).'/websharks-core.php';

				/**
				 * s2Member® Framework.
				 *
				 * @package s2Member
				 * @since 120318
				 *
				 * @assert ($GLOBALS[__NAMESPACE__])
				 *
				 * @note Dynamic properties/methods are defined explicitly here.
				 *    This way IDEs jive with ``__get()`` and ``__call()``.
				 *
				 * @note Magic properties/methods should be declared with a FQN because PhpStorm™ seems to have trouble
				 *    identifying them throughout the entire codebase w/o a FQN (for whatever reason — a possible bug).
				 *
				 * @property \s2member\behavior_types                      $©behavior_types
				 * @property \s2member\behavior_types                      $©behavior_type
				 * @method \s2member\behavior_types ©behavior_types()
				 * @method \s2member\behavior_types ©behavior_type()
				 *
				 * @property \s2member\db_tables                           $©db_tables
				 * @property \s2member\db_tables                           $©db_table
				 * @method \s2member\db_tables ©db_tables()
				 * @method \s2member\db_tables ©db_table()
				 *
				 * @property \s2member\diagnostics                         $©diagnostics
				 * @property \s2member\diagnostics                         $©diagnostic
				 * @method \s2member\diagnostics ©diagnostics()
				 * @method \s2member\diagnostics ©diagnostic()
				 *
				 * @property \s2member\esps                                $©esps
				 * @property \s2member\esps                                $©esp
				 * @method \s2member\esps ©esps()
				 * @method \s2member\esps ©esp()
				 *
				 * @property \s2member\esp_segment_types                   $©esp_segment_types
				 * @property \s2member\esp_segment_types                   $©esp_segment_type
				 * @method \s2member\esp_segment_types ©esp_segment_types()
				 * @method \s2member\esp_segment_types ©esp_segment_type()
				 *
				 * @property \s2member\esp_segment_vars                    $©esp_segment_vars
				 * @property \s2member\esp_segment_vars                    $©esp_segment_var
				 * @method \s2member\esp_segment_vars ©esp_segment_vars()
				 * @method \s2member\esp_segment_vars ©esp_segment_var()
				 *
				 * @property \s2member\events                              $©events
				 * @property \s2member\events                              $©event
				 * @method \s2member\events ©events()
				 * @method \s2member\events ©event()
				 *
				 * @property \s2member\event_behaviors                     $©event_behaviors
				 * @property \s2member\event_behaviors                     $©event_behavior
				 * @method \s2member\event_behaviors ©event_behaviors()
				 * @method \s2member\event_behaviors ©event_behavior()
				 *
				 * @property \s2member\event_code_behaviors                $©event_code_behaviors
				 * @property \s2member\event_code_behaviors                $©event_code_behavior
				 * @method \s2member\event_code_behaviors ©event_code_behaviors()
				 * @method \s2member\event_code_behaviors ©event_code_behavior()
				 *
				 * @property \s2member\event_email_behaviors               $©event_email_behaviors
				 * @property \s2member\event_email_behaviors               $©event_email_behavior
				 * @method \s2member\event_email_behaviors ©event_email_behaviors()
				 * @method \s2member\event_email_behaviors ©event_email_behavior()
				 *
				 * @property \s2member\event_esp_behaviors                 $©event_esp_behaviors
				 * @property \s2member\event_esp_behaviors                 $©event_esp_behavior
				 * @method \s2member\event_esp_behaviors ©event_esp_behaviors()
				 * @method \s2member\event_esp_behaviors ©event_esp_behavior()
				 *
				 * @property \s2member\event_handlers                      $©event_handlers
				 * @property \s2member\event_handlers                      $©event_handler
				 * @method \s2member\event_handlers ©event_handlers()
				 * @method \s2member\event_handlers ©event_handler()
				 *
				 * @property \s2member\event_logs                          $©event_logs
				 * @property \s2member\event_logs                          $©event_log
				 * @method \s2member\event_logs ©event_logs()
				 * @method \s2member\event_logs ©event_log()
				 *
				 * @property \s2member\event_notification_behaviors        $©event_notification_behaviors
				 * @property \s2member\event_notification_behaviors        $©event_notification_behavior
				 * @method \s2member\event_notification_behaviors ©event_notification_behaviors()
				 * @method \s2member\event_notification_behaviors ©event_notification_behavior()
				 *
				 * @property \s2member\event_passtag_behaviors             $©event_passtag_behaviors
				 * @property \s2member\event_passtag_behaviors             $©event_passtag_behavior
				 * @method \s2member\event_passtag_behaviors ©event_passtag_behaviors()
				 * @method \s2member\event_passtag_behaviors ©event_passtag_behavior()
				 *
				 * @property \s2member\event_redirect_behaviors            $©event_redirect_behaviors
				 * @property \s2member\event_redirect_behaviors            $©event_redirect_behavior
				 * @method \s2member\event_redirect_behaviors ©event_redirect_behaviors()
				 * @method \s2member\event_redirect_behaviors ©event_redirect_behavior()
				 *
				 * @property \s2member\event_types                         $©event_types
				 * @property \s2member\event_types                         $©event_type
				 * @method \s2member\event_types ©event_types()
				 * @method \s2member\event_types ©event_type()
				 *
				 * @property \s2member\exception                           $©exception
				 * @method \s2member\exception ©exception()
				 *
				 * @property \s2member\initializer                         $©initializer
				 * @method \s2member\initializer ©initializer()
				 *
				 * @property \s2member\installer                           $©installer
				 * @method \s2member\installer ©installer()
				 *
				 * @property \s2member\media                               $©media
				 * @method \s2member\media ©media()
				 *
				 * @property \s2member\menu_pages                          $©menu_pages
				 * @property \s2member\menu_pages                          $©menu_page
				 * @method \s2member\menu_pages ©menu_pages()
				 * @method \s2member\menu_pages ©menu_page()
				 *
				 * @property \s2member\menu_pages\menu_page                $©menu_pages__menu_page
				 * @method \s2member\menu_pages\menu_page ©menu_pages__menu_page()
				 *
				 * @property \s2member\menu_pages\panels\panel             $©menu_pages__panels__panel
				 * @method \s2member\menu_pages\panels\panel ©menu_pages__panels__panel()
				 *
				 * @property \s2member\options                             $©options
				 * @property \s2member\options                             $©option
				 * @method \s2member\options ©options()
				 * @method \s2member\options ©option()
				 *
				 * @property \s2member\passtags                            $©passtags
				 * @property \s2member\passtags                            $©passtag
				 * @method \s2member\passtags ©passtags()
				 * @method \s2member\passtags ©passtag()
				 *
				 * @property \s2member\passtag_restrictions                $©passtag_restrictions
				 * @property \s2member\passtag_restrictions                $©passtag_restriction
				 * @method \s2member\passtag_restrictions ©passtag_restrictions()
				 * @method \s2member\passtag_restrictions ©passtag_restriction()
				 *
				 * @property \s2member\passtag_wp_caps                     $©passtag_wp_caps
				 * @property \s2member\passtag_wp_caps                     $©passtag_wp_cap
				 * @method \s2member\passtag_wp_caps ©passtag_wp_caps()
				 * @method \s2member\passtag_wp_caps ©passtag_wp_cap()
				 *
				 * @property \s2member\profile_fields                      $©profile_fields
				 * @property \s2member\profile_fields                      $©profile_field
				 * @method \s2member\profile_fields ©profile_fields()
				 * @method \s2member\profile_fields ©profile_field()
				 *
				 * @property \s2member\profile_field_conversions           $©profile_field_conversions
				 * @property \s2member\profile_field_conversions           $©profile_field_conversion
				 * @method \s2member\profile_field_conversions ©profile_field_conversions()
				 * @method \s2member\profile_field_conversions ©profile_field_conversion()
				 *
				 * @property \s2member\profile_field_conversion_types      $©profile_field_conversion_types
				 * @property \s2member\profile_field_conversion_types      $©profile_field_conversion_type
				 * @method \s2member\profile_field_conversion_types ©profile_field_conversion_types()
				 * @method \s2member\profile_field_conversion_types ©profile_field_conversion_type()
				 *
				 * @property \s2member\profile_field_permissions           $©profile_field_permissions
				 * @property \s2member\profile_field_permissions           $©profile_field_permission
				 * @method \s2member\profile_field_permissions ©profile_field_permissions()
				 * @method \s2member\profile_field_permissions ©profile_field_permission()
				 *
				 * @property \s2member\profile_field_types                 $©profile_field_types
				 * @property \s2member\profile_field_types                 $©profile_field_type
				 * @method \s2member\profile_field_types ©profile_field_types()
				 * @method \s2member\profile_field_types ©profile_field_type()
				 *
				 * @property \s2member\profile_field_validations           $©profile_field_validations
				 * @property \s2member\profile_field_validations           $©profile_field_validation
				 * @method \s2member\profile_field_validations ©profile_field_validations()
				 * @method \s2member\profile_field_validations ©profile_field_validation()
				 *
				 * @property \s2member\profile_field_validation_patterns   $©profile_field_validation_patterns
				 * @property \s2member\profile_field_validation_patterns   $©profile_field_validation_pattern
				 * @method \s2member\profile_field_validation_patterns ©profile_field_validation_patterns()
				 * @method \s2member\profile_field_validation_patterns ©profile_field_validation_pattern()
				 *
				 * @property \s2member\restriction_types                   $©restriction_types
				 * @property \s2member\restriction_types                   $©restriction_type
				 * @method \s2member\restriction_types ©restriction_types()
				 * @method \s2member\restriction_types ©restriction_type()
				 *
				 * @property \s2member\systematics                         $©systematics
				 * @property \s2member\systematics                         $©systematic
				 * @method \s2member\systematics ©systematics()
				 * @method \s2member\systematics ©systematic()
				 *
				 * @property \s2member\unsubscribes                        $©unsubscribes
				 * @property \s2member\unsubscribes                        $©unsubscribe
				 * @method \s2member\unsubscribes ©unsubscribes()
				 * @method \s2member\unsubscribes ©unsubscribe()
				 *
				 * @property \s2member\users                               $©users
				 * @property \s2member\users                               $©user
				 * @method \s2member\users ©users()
				 * @method \s2member\users ©user()
				 *
				 * @property \s2member\user_utils                          $©user_utils
				 * @method \s2member\user_utils ©user_utils()
				 */
				class framework extends \websharks_core_v000000_dev\framework
				{
					/**
					 * @var array Additional dynamic class aliases.
					 */
					protected static $____dynamic_class_aliases = array(
						'behavior_type'                    => 'behavior_types',
						'db_table'                         => 'db_tables',
						'diagnostic'                       => 'diagnostics',
						'esp'                              => 'esps',
						'esp_segment_type'                 => 'esp_segment_types',
						'esp_segment_var'                  => 'esp_segment_vars',
						'event'                            => 'events',
						'event_behavior'                   => 'event_behaviors',
						'event_code_behavior'              => 'event_code_behaviors',
						'event_email_behavior'             => 'event_email_behaviors',
						'event_esp_behavior'               => 'event_esp_behaviors',
						'event_handler'                    => 'event_handlers',
						'event_log'                        => 'event_logs',
						'event_notification_behavior'      => 'event_notification_behaviors',
						'event_passtag_behavior'           => 'event_passtag_behaviors',
						'event_redirect_behavior'          => 'event_redirect_behaviors',
						'event_type'                       => 'event_types',
						'menu_page'                        => 'menu_pages',
						'option'                           => 'options',
						'passtag'                          => 'passtags',
						'passtag_restriction'              => 'passtag_restrictions',
						'passtag_wp_cap'                   => 'passtag_wp_caps',
						'profile_field'                    => 'profile_fields',
						'profile_field_conversion'         => 'profile_field_conversions',
						'profile_field_conversion_type'    => 'profile_field_conversion_types',
						'profile_field_permission'         => 'profile_field_permissions',
						'profile_field_type'               => 'profile_field_types',
						'profile_field_validation'         => 'profile_field_validations',
						'profile_field_validation_pattern' => 'profile_field_validation_patterns',
						'restriction_type'                 => 'restriction_types',
						'systematic'                       => 'systematics',
						'unsubscribe'                      => 'unsubscribes',
						'user'                             => 'users'
					);
				}

				/*
				 * Creates a global framework instance.
				 *
				 * If rebranding, please change `plugin_var_ns`, `plugin_name`, `plugin_site` below.
				 *    We suggest using WordPress® filters of your own to avoid editing the source code directly.
				 *
				 * Note (if rebranding): URLs throughout this software are generated with `plugin_site` as a base URL.
				 *    For instance, you will find references in the software w/ calls to ``$this->©url->to_plugin_site_uri('/path/')``.
				 *
				 *    You have TWO options when it comes to `plugin_site`.
				 *
				 *    1. If you want to host your own documentation, videos, etc; you will need to dig through the software,
				 *    and locate URL paths that need to exist on whatever domain you change `plugin_site` to (they need to match s2Member.com).
				 *
				 *    2. Or (an easier alternative); some developers prefer to use their own dedicated sub-domain for `plugin_site`,
				 *    and they simply redirect all requests from this software to a central location, regardless of the original path being requested.
				 */
				$GLOBALS[__NAMESPACE__] = new framework(

					array( // Array configures plugin instance.

					       'plugin_root_ns' => __NAMESPACE__,
					       'plugin_version' => '000000-dev', #!version!#
					       'plugin_dir'     => dirname(dirname(dirname(__FILE__))),

					       'plugin_var_ns'  => apply_filters(__NAMESPACE__.'__plugin_var_ns', 's2'),
					       'plugin_name'    => apply_filters(__NAMESPACE__.'__plugin_name', 's2Member®'),
					       'plugin_site'    => apply_filters(__NAMESPACE__.'__plugin_site', 'http://www.s2member.com')
					)
				);
			}
	}