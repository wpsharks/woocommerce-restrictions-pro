<?php
/**
 * Template.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro;

use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;
#
use function assert as debug;
use function get_defined_vars as vars;

$Form = $this->s::menuPageForm('§save-options');
?>
<?= $Form->openTag(); ?>

    <?= $Form->openTable(
        __('Security Gate Options', 'woocommerce-restrictions'),
        sprintf(__('You can browse <em>our</em> <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'woocommerce-restrictions'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Security Gate Redirects To', 'woocommerce-restrictions'),
            'tip'   => __('When someone attempts to access an area of the site that is currently off-limits to them, this is where they will be redirected to.<hr />If you leave this empty, the default redirection is to <code>/wp-login.php</code>', 'woocommerce-restrictions'),
            'note'  => sprintf(__('Please <a href="%1$s">create a page</a> specifically for this purpose.', 'woocommerce-restrictions'), esc_url(admin_url('/post-new.php?post_type=page'))),

            'name'    => 'security_gate_redirects_to_post_id',
            'value'   => s::getOption('security_gate_redirects_to_post_id'),
            'options' => s::postSelectOptions([
                'allow_empty'        => true,
                'include_post_types' => ['page'],
                'exclude_post_types' => a::systematicPostTypes(),
                'current_post_ids'   => [s::getOption('security_gate_redirects_to_post_id')],
            ]),
        ]); ?>

    <?= $Form->closeTable(); ?>

    <hr />

    <?= $Form->openTable(
        __('WooCommerce Order Options', 'woocommerce-restrictions')
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Access Always Immediate?', 'woocommerce-restrictions'),
            'tip'   => __('In WooCommerce, if you create a product that is both Virtual &amp; Downloadable, immediate access is already implied. This setting doesn\'t change that behavior.<hr />For other types of products do you want to \'always\' grant immediate access?', 'woocommerce-restrictions'),

            'name'    => 'orders_always_grant_immediate_access',
            'value'   => s::getOption('orders_always_grant_immediate_access'),
            'options' => [
                '1' => __('Yes (grant access when order is received; even if still \'processing\')', 'woocommerce-restrictions'),
                '0' => __('No (access granted only when order is \'complete\')', 'woocommerce-restrictions'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
