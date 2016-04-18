<?php
// PHP v5.3 compatible.
namespace WebSharks\WpSharks\s2MemberX\Pro;

use WebSharks\WpSharks\s2MemberX\Pro\Classes\App;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
add_action('plugins_loaded', function () {
    require __DIR__.'/rv.php'; // Setup `wp_sharks_core_rv` config.
    if (require(dirname(__DIR__).'/vendor/websharks/wp-sharks-core-rv/src/includes/check.php')) {
        require_once __DIR__.'/stub.php';
        new App();
    } else {
        wp_sharks_core_rv_notice('s2Member® X Pro');
    }
});
