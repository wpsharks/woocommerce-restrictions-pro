<?php
/**
 * Template.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro;

use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\CoreFacades as c;
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
        __('Security Gate Options', 'woocommerce-s2member-x'),
        sprintf(__('You can browse <em>our</em> <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'woocommerce-s2member-x'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Security Gate Redirects To', 'woocommerce-s2member-x'),
            'tip'   => __('When someone attempts to access an area of the site that is currently off-limits to them, this is where they will be redirected to.<hr />If you leave this empty, the default redirection is to <code>/wp-login.php</code>', 'woocommerce-s2member-x'),
            'note'  => sprintf(__('Please <a href="%1$s">create a page</a> specifically for this purpose.', 'woocommerce-s2member-x'), esc_url(admin_url('/post-new.php?post_type=page'))),

            'name'    => 'security_gate_redirects_to_post_id',
            'value'   => s::getOption('security_gate_redirects_to_post_id'),
            'options' => s::postSelectOptions([
                'allow_empty'        => true,
                'include_post_types' => ['page'],
                'exclude_post_types' => a::systematicPostTypes(),
                'current_post_ids'   => [s::getOption('security_gate_redirects_to_post_id')],
            ]),
        ]); ?>

        <?= $Form->selectRow([
            'label' => __('Enable Redirection Args?', 'woocommerce-s2member-x'),
            'tip'   => __('When a redirection occurs, arguments in the URL (i.e., query string variables added by this plugin) can be used by WordPress themes to present context-specific messages to a user. This must be enabled if you intend to use them.', 'woocommerce-s2member-x'),

            'name'    => 'security_gate_redirect_to_args_enable',
            'value'   => s::getOption('security_gate_redirect_to_args_enable'),
            'options' => [
                '1' => __('Yes', 'woocommerce-s2member-x'),
                '0' => __('No', 'woocommerce-s2member-x'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <hr />

    <?= $Form->openTable(
        __('WooCommerce Order Options', 'woocommerce-s2member-x')
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Access Always Immediate?', 'woocommerce-s2member-x'),
            'tip'   => __('In WooCommerce, if you create a product that is both Virtual &amp; Downloadable, immediate access is already implied. This setting doesn\'t change that behavior.<hr />For other types of products do you want to \'always\' grant immediate access?', 'woocommerce-s2member-x'),

            'name'    => 'orders_always_grant_immediate_access',
            'value'   => s::getOption('orders_always_grant_immediate_access'),
            'options' => [
                '1' => __('Yes (grant access when order is received; even if still \'processing\')', 'woocommerce-s2member-x'),
                '0' => __('No (access granted only when order is \'complete\')', 'woocommerce-s2member-x'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
