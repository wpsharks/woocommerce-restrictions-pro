<?php
/**
 * Plugin.
 *
 * @wp-plugin
 *
 * Version: 160302
 * Text Domain: wps-s2member-x
 * Plugin Name: s2Member X Pro
 *
 * Author: WP Sharks™
 * Author URI: https://wpsharks.com/
 *
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Plugin URI: https://wpsharks.com/product/s2member-x/
 * Description: Membership functionality for WooCommerce.
 */
if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
if ($is_wp_sharks_core_active) {
    require_once __DIR__.'/src/includes/stub.php';
}
