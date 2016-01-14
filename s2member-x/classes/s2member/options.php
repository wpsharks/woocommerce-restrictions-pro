<?php
/**
 * Options.
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

		/**
		 * Options.
		 *
		 * @package s2Member
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class options extends \websharks_core_v000000_dev\options
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

					$default_options = array(
						'no_cache.headers.always'                                           => '1',

						'styles.front_side.load'                                            => '1',
						'scripts.front_side.load'                                           => '1',

						'shortcodes.if_conditionals.enable'                                 => '1',

						'systematics.register_post_id'                                      => '0',
						'systematics.login_post_id'                                         => '0',
						'systematics.account_post_id'                                       => '0',

						'passtag_restrictions.check_post_ancestors'                         => '1',
						'passtag_restrictions.check_taxonomy_term_ancestors'                => '1',
						'passtag_restrictions.redirect_users_with_passtags_to_account_page' => '1',
						'passtag_restrictions.redirect_args'                                => array('all'),

						'events.triggers.max'                                               => '25',
						'events.crons.default_limit'                                        => '25',
						'events.crons.default_limit_span_offset_time'                       => '1 day',
						'events.crons.min_futuristic_offset_time'                           => '1 day',
						'events.crons.max_futuristic_offset_time'                           => '1 year',

						'templates.stand_alone.bg_style'                                    => 'background: #F2F1F0'.
						                                                                       ' url(\''.$this->©string->esc_sq($this->©url->to_template_dir_file('client-side/images/stand-alone-bg.png')).'\')'.
						                                                                       ' repeat left top;',
						'templates.stand_alone.header'                                      => '<a href="'.esc_attr($this->©url->to_wp_home_uri()).'">'.
						                                                                       '<img class="logo" src="'.esc_attr($this->©url->to_template_dir_file('client-side/images/stand-alone-logo.png')).'"'.
						                                                                       ' title="'.esc_attr(get_bloginfo('name')).'" alt="'.esc_attr(get_bloginfo('name')).'" />'.
						                                                                       '</a>',

						'users.attach_init_hook'                                            => '1',
						'users.sessions.cookie_expiration_offset_time'                      => '0 days',
						'users.sessions.max_access_keys'                                    => '10',
						'users.user_login_log.max_failed_logins'                            => '5',
						'users.user_login_log.max_failed_logins_exp_offset_time'            => '30 minutes',
						'users.attach_wp_authentication_filter'                             => '1',

						'media.support_ranges'                                              => '1',
						'media.use_content_encoding_none'                                   => '0',
						'media.chunk_size'                                                  => '2097152',
						'media.default_storage'                                             => 'local',

						'media.amazon.s3.bucket'                                            => '',
						'media.amazon.s3.access_key'                                        => '',
						'media.amazon.s3.secret_key'                                        => '',
						'media.amazon.s3.expires_offset_time'                               => '24 hours',

						'media.amazon.cf.key_pair_id'                                       => '',
						'media.amazon.cf.private_key'                                       => '',
						'media.amazon.cf.downloads_cname'                                   => '',
						'media.amazon.cf.downloads_distro'                                  => '',
						'media.amazon.cf.downloads_distro_id'                               => '',
						'media.amazon.cf.streaming_cname'                                   => '',
						'media.amazon.cf.streaming_distro'                                  => '',
						'media.amazon.cf.streaming_distro_id'                               => '',
						'media.amazon.cf.expires_offset_time'                               => '24 hours',

						'widgets.enable_shortcodes'                                         => '1'
					);

					$validators = array(
						'no_cache.headers.always'                                           => array('string:numeric >=' => 0),

						'styles.front_side.load'                                            => array('string:numeric >=' => 0),
						'scripts.front_side.load'                                           => array('string:numeric >=' => 0),

						'shortcodes.if_conditionals.enable'                                 => array('string:numeric >=' => 0),

						'systematics.register_post_id'                                      => array('string:numeric >=' => 0),
						'systematics.login_post_id'                                         => array('string:numeric >=' => 0),
						'systematics.account_post_id'                                       => array('string:numeric >=' => 0),

						'passtag_restrictions.check_post_ancestors'                         => array('string:numeric >=' => 0),
						'passtag_restrictions.check_taxonomy_term_ancestors'                => array('string:numeric >=' => 0),
						'passtag_restrictions.redirect_users_with_passtags_to_account_page' => array('string:numeric >=' => 0),
						'passtag_restrictions.redirect_args'                                => array('array'),

						'events.triggers.max'                                               => array('string:numeric >=' => 1),
						'events.crons.default_limit'                                        => array('string:numeric >=' => 1),
						'events.crons.default_limit_span_offset_time'                       => array('string:!empty'),
						'events.crons.min_futuristic_offset_time'                           => array('string:!empty'),
						'events.crons.max_futuristic_offset_time'                           => array('string:!empty'),

						'templates.stand_alone.bg_style'                                    => array('string'),
						'templates.stand_alone.header'                                      => array('string:!empty'),

						'users.attach_init_hook'                                            => array('string:numeric >=' => 0),
						'users.sessions.cookie_expiration_offset_time'                      => array('string:!empty'),
						'users.sessions.max_access_keys'                                    => array('string:numeric >=' => 1),
						'users.user_login_log.max_failed_logins'                            => array('string:numeric >=' => -1),
						'users.user_login_log.max_failed_logins_exp_offset_time'            => array('string:!empty'),
						'users.attach_wp_authentication_filter'                             => array('string:numeric >=' => 0),

						'media.support_ranges'                                              => array('string:numeric >=' => 0),
						'media.use_content_encoding_none'                                   => array('string:numeric >=' => 0),
						'media.chunk_size'                                                  => array('string:numeric >=' => 100),
						'media.default_storage'                                             => array('string:!empty'),

						'media.amazon.s3.bucket'                                            => array('string:!empty'),
						'media.amazon.s3.access_key'                                        => array('string:!empty'),
						'media.amazon.s3.secret_key'                                        => array('string:!empty'),
						'media.amazon.s3.expires_offset_time'                               => array('string:!empty'),

						'media.amazon.cf.key_pair_id'                                       => array('string:!empty'),
						'media.amazon.cf.private_key'                                       => array('string:!empty'),
						'media.amazon.cf.downloads_cname'                                   => array('string:!empty'),
						'media.amazon.cf.downloads_distro'                                  => array('string:!empty'),
						'media.amazon.cf.downloads_distro_id'                               => array('string:!empty'),
						'media.amazon.cf.streaming_cname'                                   => array('string:!empty'),
						'media.amazon.cf.streaming_distro'                                  => array('string:!empty'),
						'media.amazon.cf.streaming_distro_id'                               => array('string:!empty'),
						'media.amazon.cf.expires_offset_time'                               => array('string:!empty'),

						'widgets.enable_shortcodes'                                         => array('string:numeric >=' => 0)
					);

					$default_options = array_merge($this->default_options, $default_options);
					$validators      = array_merge($this->validators, $validators);

					$this->setup($default_options, $validators);
				}
		}
	}