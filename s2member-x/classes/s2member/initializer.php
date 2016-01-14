<?php
/**
 * Initializer.
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
		 * Initializer.
		 *
		 * @package s2Member
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class initializer extends \websharks_core_v000000_dev\initializer
		{
			/**
			 * Initialization routines/hooks.
			 *
			 * @attaches-to WordPress® `after_setup_theme` hook.
			 * @hook-priority `-1` Before most everything else.
			 */
			public function after_setup_theme()
				{
					parent::after_setup_theme();

					if(!$this->©plugin->is_active_at_current_version())
						return; // Do NOT go any further here.

					// Passtag WordPress® caps.

					add_action('init', array($this, '©passtag_wp_caps.init'), -2);

					// Handler for `/wp-login.php`.

					add_action('init', array($this, '©user_utils.wp_login_handler'), 2);

					// Passtag restrictions.

					add_action('wp', array($this, '©passtag_restrictions.wp'), -1);
					add_action('admin_init', array($this, '©passtag_restrictions.admin_init'), -1);

					// Media files (after restrictions).

					add_action('wp', array($this, '©media.wp'), -1);

					// Events (including CRON jobs). CRON jobs in separate class.

					add_action('wp_loaded', array($this, '©events.wp_loaded'), PHP_INT_MAX);

					// User authentication.

					add_action('wp_login_failed', array($this, '©user_utils.wp_login_failure'));
					add_action('wp_login', array($this, '©user_utils.wp_login_success'), 10, 2);
					add_action('wp_logout', array($this, '©user_utils.wp_logout'));

					// Login redirections.

					add_action('wp_login', array($this, '©systematics.setup_login_redirect_filters'));

					// User registrations.

					add_action('user_register', array($this, '©user_utils.wp_register'), PHP_INT_MAX, 1);

					// User profile updates & deletions.

					add_action('profile_update', array($this, '©user_utils.wp_profile_update'), PHP_INT_MAX, 2);

					add_action('delete_user', array($this, '©user_utils.wp_delete_user'));
					add_action('wpmu_delete_user', array($this, '©user_utils.wp_delete_user'));
					add_action('remove_user_from_blog', array($this, '©user_utils.wp_delete_user'), 10, 2);

					// Integrates s2Member® profile fields with WordPress®.

					add_filter('get_user_metadata', array($this, '©user_utils.get_user_metadata'), PHP_INT_MAX, 4);

					// Shorter `___instance_config` variables.

					$plugin_prefix       = $this->___instance_config->plugin_prefix;
					$plugin_root_ns_stub = $this->___instance_config->plugin_root_ns_stub;

					// WordPress® shortcodes (the best part of s2Member® :-)

					add_shortcode($plugin_prefix.'login', array($this, '©shortcodes__login.do_shortcode'));
					add_shortcode($plugin_prefix.'profile_summary', array($this, '©shortcodes__profile_summary.do_shortcode'));
					add_shortcode($plugin_prefix.'profile_update', array($this, '©shortcodes__profile_update.do_shortcode'));

					// Event triggers (connected to other actions).

					add_action($plugin_root_ns_stub.'__user_utils__creation', array($this, '©events.user_creation'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__register', array($this, '©events.wp_user_creation'), PHP_INT_MAX, 2);

					add_action($plugin_root_ns_stub.'__user_utils__activation', array($this, '©events.user_activation'), PHP_INT_MAX, 2);
					add_action('wpmu_activate_user', array($this, '©events.wp_user_activation'), PHP_INT_MAX, 2);
					add_action('wpmu_activate_blog', array($this, '©events.wp_user_blog_activation'), PHP_INT_MAX, 3);

					add_action($plugin_root_ns_stub.'__users__update', array($this, '©events.user_update'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__update', array($this, '©events.wp_user_update'), PHP_INT_MAX, 2);

					add_action($plugin_root_ns_stub.'__users__delete', array($this, '©events.user_deletion'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__delete', array($this, '©events.wp_user_deletion'), PHP_INT_MAX, 2);

					add_action($plugin_root_ns_stub.'__user_utils__login_allowed', array($this, '©events.user_login_success'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__login_success', array($this, '©events.wp_user_login_success'), PHP_INT_MAX, 2);

					add_action($plugin_root_ns_stub.'__user_utils__login_denied', array($this, '©events.user_login_failure'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__login_failure', array($this, '©events.wp_user_login_failure'), PHP_INT_MAX, 2);

					add_action($plugin_root_ns_stub.'__users_logout', array($this, '©events.user_logout'), PHP_INT_MAX, 2);
					add_action($plugin_root_ns_stub.'__user_utils__logout', array($this, '©events.wp_user_logout'), PHP_INT_MAX, 2);
				}
		}
	}