<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\s2MemberX\Pro\Classes\Utils;

use WebSharks\WpSharks\s2MemberX\Pro\Classes;
use WebSharks\WpSharks\s2MemberX\Pro\Interfaces;
use WebSharks\WpSharks\s2MemberX\Pro\Traits;
#
use WebSharks\WpSharks\s2MemberX\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\s2MemberX\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\s2MemberX\Pro\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;

/**
 * Order item utilities.
 *
 * @since 16xxxx Order item utilities.
 */
class OrderItem extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription post type.
     *
     * @since 16xxxx Order item utilities.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * All order post types.
     *
     * @since 16xxxx Order item utilities.
     *
     * @param array All order post types.
     */
    protected $all_order_post_types;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order item utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->subscription_post_type = a::subscriptionPostType();
        $this->all_order_post_types   = wc_get_order_types();
    }

    /**
     * Get order containing item.
     *
     * @since 16xxxx Order item utilities.
     *
     * @param string|int $item Order item ID.
     *
     * @return \WC_Order|null Order on success.
     */
    public function getOrderByItemId($item_id)
    {
        if (!($item_id = (int) $item_id)) {
            return null; // Not possible.
        }
        $WpDb  = s::wpDb(); // DB instance.
        $table = $WpDb->prefix.'woocommerce_order_items';

        $sql = /* Get the order ID for this item. */ '
            SELECT `order_id` FROM `'.esc_sql($table).'`
             WHERE `order_item_id` = %s LIMIT 1';
        $sql = $WpDb->prepare($sql, $item_id); // Prepare.

        if (!($order_id = (int) $WpDb->get_var($sql))) {
            return null; // Not possible; can't get order ID.
        } elseif (!($post_type = get_post_type($order_id))) {
            return null; // Not possible; can't get post type.
        } elseif (!in_array($post_type, $this->all_order_post_types, true)) {
            return null; // Not applicable; not an order post type.
        }
        switch ($post_type) { // Based on post type.

            case $this->subscription_post_type:
                $subscription_id = $order_id; // It's a subscription ID.
                if (($WC_Subscription = wcs_get_subscription($subscription_id))) {
                    return $WC_Subscription;
                }
                return null; // Not possible.

            default: // Or any other order type.
                if (($WC_Order = wc_get_order($order_id))) {
                    return $WC_Order;
                }
                return null; // Not possible.
        }
    }

    /**
     * On item deleted from order.
     *
     * @since 16xxxx Order item utilities.
     *
     * @param string|int $item_id Order item ID.
     *
     * @note This works for Subscriptions also.
     */
    public function onBeforeDeleteOrderItem($item_id)
    {
        if (!($item_id = (int) $item_id)) {
            return; // Not possible; empty item ID.
        } elseif (!($product_id = (int) wc_get_order_item_meta($item_id, '_product_id', true))) {
            return; // Not possible; unable to acquire product ID.
        } elseif (!($WC_Product = wc_get_product($product_id)) || !$WC_Product->exists()) {
            return; // Not possible; unable to acquire product instance.
        } elseif (!($WC_Order = $this->getOrderByItemId($item_id))) {
            return; // Not possible; unable to acquire order.
        }
        $WpDb = s::wpDb(); // DB class instance.

        switch ($WC_Order->post->post_type) { // Based on post type.

            case $this->subscription_post_type:
                $WC_Subscription = $WC_Order; // Subscription.
                $subscription_id = (int) $WC_Order->id;
                $where           = [
                    'subscription_id' => $subscription_id,
                    'product_id'      => $product_id,
                ];
                break; // Stop here.

            default: // Or any other order type.
                $order_id = (int) $WC_Order->id;
                $where    = [
                    'order_id'   => $order_id,
                    'product_id' => $product_id,
                ];
                break; // Stop here.
        }
        s::doAction('before_user_permissions_delete', $where);
        $WpDb->delete(s::dbPrefix().'user_permissions', $where);
        s::doAction('user_permissions_deleted', $where);

        a::clearUserPermissionsCache(); // For all users.

        a::addLogEntry(__METHOD__, compact(
            'item_id',
            'order_id',
            'subscription_id',
            'product_id',
            'where'
        ), __('Deleting user permissions when deleting order item.', 's2member-x'));
    }
}
