<?php
// PHP v5.2 compatible.

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
$GLOBALS['wp_php_rv'] = array(
    'min'        => '7.0.4', //php-required-version//
    'bits'       => 64, //php-required-bits//
    'extensions' => array(), //php-required-extensions//
); // The following are back compat. keys.
$GLOBALS['wp_php_rv']['rv'] = $GLOBALS['wp_php_rv']['min'];
$GLOBALS['wp_php_rv']['re'] = $GLOBALS['wp_php_rv']['extensions'];
