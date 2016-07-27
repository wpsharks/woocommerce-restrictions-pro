<?php
/**
 * Order item utilities.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\Utils;

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

/**
 * Order item utilities.
 *
 * @since 160524 Order item utilities.
 */
class OrderItem extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription post type.
     *
     * @since 160524 Order item utilities.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Class constructor.
     *
     * @since 160524 Order item utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->subscription_post_type = a::subscriptionPostType();
    }

    /**
     * On item deleted from order.
     *
     * @since 160524 Order item utilities.
     *
     * @param string|int $item_id Order item ID.
     *
     * @note This works for Subscriptions also.
     */
    public function onBeforeDeleteOrderItem($item_id)
    {
        if (!($item_id = (int) $item_id)) {
            debug(0, c::issue(vars(), 'Empty item ID.'));
            return; // Not possible; empty item ID.
        } elseif (!($WC_Order = s::wcOrderByItemId($item_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order.'));
            return; // Not possible; unable to acquire order.
        } elseif (!($order_id = (int) $WC_Order->id)) {
            debug(0, c::issue(vars(), 'Unable to acquire order ID.'));
            return; // Not possible; unable to acquire order ID.
        } elseif (!($order_type = get_post_type($order_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order type.'));
            return; // Not possible; unable to acquire order type.
        } elseif (!($item = s::wcOrderItemById($item_id, $WC_Order))) {
            debug(0, c::issue(vars(), 'Unable to acquire order item.'));
            return; // Not possible; unable to acquire order item.
        } elseif (!($product_id = s::wcProductIdFromItem($item))) {
            return; // Not applicable; not associated with a product ID.
        }
        $WpDb = s::wpDb(); // DB class object instance.

        switch ($order_type) { // Based on post type.

            case $this->subscription_post_type:
                $WC_Subscription = $WC_Order;
                $subscription_id = $order_id;
                $where           = [
                    'subscription_id' => $subscription_id,
                    'product_id'      => $product_id,
                    'item_id'         => $item_id,
                ];
                break; // Stop here.

            default: // Any other order type.
                $where = [
                    'order_id'   => $order_id,
                    'product_id' => $product_id,
                    'item_id'    => $item_id,
                ];
                break; // Stop here.
        }
        s::doAction('before_user_permissions_delete', $where);
        $WpDb->delete(s::dbPrefix().'user_permissions', $where);
        s::doAction('user_permissions_deleted', $where);

        a::clearUserPermissionsCache(); // For all users.

        c::review(compact(// Log for review.
            'order_id',
            'order_type',
            'subscription_id',
            'product_id',
            'item_id',
            'where'
        ), 'Deleting user permissions when deleting order item.');
    }
}
