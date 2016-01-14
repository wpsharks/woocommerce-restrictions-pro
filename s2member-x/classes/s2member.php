<?php
/**
 * s2Member® API Class.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member
 * @since 120318
 */
namespace // Global namespace (for easy access).
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * s2Member® API Class.
		 *
		 * @package s2Member
		 * @since 120318
		 */
		final class s2member extends \websharks_core_v000000_dev\api
		{
			/**
			 * s2Member® Framework instance.
			 *
			 * @return \s2member\framework {@inheritdoc}
			 *
			 * @see \websharks_core_v000000_dev\api::___framework()
			 * @inheritdoc \websharks_core_v000000_dev\api::___framework()
			 */
			public static function ____framework()
				{
					return parent::___framework();
				}

			# Conditionals (used with `[if]` shortcode).

			/**
			 * Is the current user logged in?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::is_logged_in()
			 * @inheritdoc \s2member\users::is_logged_in()
			 */
			public static function user_is_logged_in()
				{
					return call_user_func_array(array(static::____framework()->©user, 'is_logged_in'), func_get_args());
				}

			/**
			 * Is the current user populated?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::is_populated()
			 * @inheritdoc \s2member\users::is_populated()
			 */
			public static function user_is_populated()
				{
					return call_user_func_array(array(static::____framework()->©user, 'is_populated'), func_get_args());
				}

			/**
			 * Does the current user HAVE a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::has_passtags()
			 * @inheritdoc \s2member\users::has_passtags()
			 */
			public static function user_has_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'has_passtags'), func_get_args());
				}

			/**
			 * Does the current user HAVE specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::has_passtags()
			 * @inheritdoc \s2member\users::has_passtags()
			 */
			public static function user_has_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'has_passtags'), func_get_args());
				}

			/**
			 * Does the current user HAVE any of the passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::has_any_passtag()
			 * @inheritdoc \s2member\users::has_any_passtag()
			 */
			public static function user_has_any_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'has_any_passtag'), func_get_args());
				}

			/**
			 * DID the current user HAVE a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_passtags()
			 * @inheritdoc \s2member\users::did_have_passtags()
			 */
			public static function user_had_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_passtags'), func_get_args());
				}

			/**
			 * DID the current user HAVE specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_passtags()
			 * @inheritdoc \s2member\users::did_have_passtags()
			 */
			public static function user_had_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_passtags'), func_get_args());
				}

			/**
			 * DID the current user HAVE a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_passtags()
			 * @inheritdoc \s2member\users::did_have_passtags()
			 */
			public static function user_did_have_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_passtags'), func_get_args());
				}

			/**
			 * DID the current user HAVE specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_passtags()
			 * @inheritdoc \s2member\users::did_have_passtags()
			 */
			public static function user_did_have_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_passtags'), func_get_args());
				}

			/**
			 * DID the current user HAVE any of the passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_any_passtag()
			 * @inheritdoc \s2member\users::did_have_any_passtag()
			 */
			public static function user_did_have_any_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_any_passtag'), func_get_args());
				}

			/**
			 * DID the current user HAVE any of the passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::did_have_any_passtag()
			 * @inheritdoc \s2member\users::did_have_any_passtag()
			 */
			public static function user_had_any_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'did_have_any_passtag'), func_get_args());
				}

			/**
			 * CAN the current user ACCESS a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::can_access_passtags()
			 * @inheritdoc \s2member\users::can_access_passtags()
			 */
			public static function user_can_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'can_access_passtags'), func_get_args());
				}

			/**
			 * CAN the current user ACCESS specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::can_access_passtags()
			 * @inheritdoc \s2member\users::can_access_passtags()
			 */
			public static function user_can_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'can_access_passtags'), func_get_args());
				}

			/**
			 * CAN the current user ACCESS a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::can_access_passtags()
			 * @inheritdoc \s2member\users::can_access_passtags()
			 */
			public static function user_can_access_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'can_access_passtags'), func_get_args());
				}

			/**
			 * CAN the current user ACCESS specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::can_access_passtags()
			 * @inheritdoc \s2member\users::can_access_passtags()
			 */
			public static function user_can_access_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'can_access_passtags'), func_get_args());
				}

			/**
			 * CAN the current user ACCESS any of the passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::can_access_any_passtag()
			 * @inheritdoc \s2member\users::can_access_any_passtag()
			 */
			public static function user_can_access_any_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'can_access_any_passtag'), func_get_args());
				}

			/**
			 * WILL the current user ACCESS a specific passtag?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::will_access_passtags()
			 * @inheritdoc \s2member\users::will_access_passtags()
			 */
			public static function user_will_access_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'will_access_passtags'), func_get_args());
				}

			/**
			 * WILL the current user ACCESS specific passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::will_access_passtags()
			 * @inheritdoc \s2member\users::will_access_passtags()
			 */
			public static function user_will_access_passtags()
				{
					return call_user_func_array(array(static::____framework()->©user, 'will_access_passtags'), func_get_args());
				}

			/**
			 * WILL the current user ACCESS any of the passtags?
			 *
			 * @return boolean {@inheritdoc}
			 *
			 * @see \s2member\users::will_access_any_passtag()
			 * @inheritdoc \s2member\users::will_access_any_passtag()
			 */
			public static function user_will_access_any_passtag()
				{
					return call_user_func_array(array(static::____framework()->©user, 'will_access_any_passtag'), func_get_args());
				}
		}
	}