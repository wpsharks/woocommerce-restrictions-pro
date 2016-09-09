<?php
/**
 * Plugin.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro;

use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\App;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
add_action('plugins_loaded', function () {
    require __DIR__.'/wp-sharks-core-rv.php';

    if (require(dirname(__FILE__, 2).'/vendor/websharks/wp-sharks-core-rv/src/includes/check.php')) {
        require_once __DIR__.'/stub.php';
        new App(); // Plugin instance.
    } else {
        wp_sharks_core_rv_notice('WooCommerce Restrictions Pro');
    }
});
