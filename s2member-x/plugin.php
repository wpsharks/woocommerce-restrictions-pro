<?php
/**
 * s2Member® X (WP Plugin).
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member
 * @since 120318
 */
/* -- WordPress® ------------------------------------------------------------------------

Version: 130310
Stable tag: 130310
Tested up to: 3.6-alpha
Requires at least: 3.5.1

Requires at least PHP version: 5.3.1
Tested up to PHP version: 5.4.12

Requires: WordPress®, WebSharks™ Core
Uses WebSharks Core: websharks-core-v000000-dev

Copyright: © 2012 WebSharks, Inc.
License: GNU General Public License
Contributors: WebSharks

Author: s2Member® / WebSharks, Inc.
Author URI: http://www.s2member.com
Donate link: http://www.s2member.com/donate/

Text Domain: s2member
Domain Path: /includes/translations

Plugin Name: s2Member® X
Plugin URI: http://www.s2member.com/x/

Description: s2Member® X. A free membership management plugin for WordPress®.
Tags: s2, s2member, s2 member, membership, users, user, members, member, subscribers, subscriber, members only, roles, capabilities, capability, register, signup, paypal, paypal pro, pay pal, authorize, authorize.net, google checkout, clickbank, click bank, shopping cart, cart, checkout, ecommerce

-- end section for WordPress®. ------------------------------------------------------- */

if(!defined('WPINC'))
	exit('Do NOT access this file directly: '.basename(__FILE__));

/*
 * Load WebSharks™ Core dependency utilities.
 */
$GLOBALS['autoload_websharks_core_v000000_dev'] = FALSE;
require_once dirname(__FILE__).'/websharks-core.php';
require_once websharks_core_v000000_dev::deps();

/*
 * Check dependencies (and load framework; if possible).
 */
if(deps_websharks_core_v000000_dev::check('s2Member® X', dirname(__FILE__)) === TRUE)
	require_once dirname(__FILE__).'/classes/s2member/framework.php';