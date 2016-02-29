<?php
if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
if ($is_wp_sharks_core_active) {
    require_once __DIR__.'/src/includes/uninstall.php';
}
