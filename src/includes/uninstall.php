<?php
/**
 * Uninstaller.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro;

use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\App;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly.');
}
require __DIR__.'/wp-sharks-core-rv.php';

if (require(dirname(__FILE__, 2).'/vendor/websharks/wp-sharks-core-rv/src/includes/check.php')) {
    require_once __DIR__.'/stub.php';
    new App(['§uninstall' => true]);
}
