<?php
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
 * Order item meta utilities.
 *
 * @since 160524 Order item meta utilities.
 */
class OrderItemMeta extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription post type.
     *
     * @since 160524 Order item meta utilities.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Product meta prefix.
     *
     * @since 160524 Order item meta utilities.
     *
     * @param string Product meta prefix.
     */
    protected $product_meta_prefix;

    /**
     * Class constructor.
     *
     * @since 160524 Order item meta utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->subscription_post_type = a::subscriptionPostType();
        $this->product_meta_prefix    = a::productMetaPrefix().'product_';
    }

    /**
     * Hidden meta keys.
     *
     * @since 160524 Order item meta utilities.
     *
     * @param array $meta_keys Hidden meta keys.
     *
     * @note This works for Subscriptions also.
     */
    public function onHiddenOrderItemMeta(array $meta_keys): array
    {
        return array_merge($meta_keys, [
            $this->product_meta_prefix.'type',
            $this->product_meta_prefix.'permissions',
        ]);
    }

    /**
     * On adding product to an order (i.e., an item).
     *
     * @since 160611 Order item meta utilities.
     *
     * @param string|int  $order_id   Order ID.
     * @param string|int  $item_id    Order item ID.
     * @param \WC_Product $WC_Product Product instance.
     * @param string|int  $quantity   Quantity.
     *
     * @note This works for subscription order items also.
     */
    public function onOrderAddProduct($order_id, $item_id, \WC_Product $WC_Product, $quantity)
    {
        if (!($order_id = (int) $order_id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible; empty order ID.
        } elseif (!($order_type = get_post_type($order_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order type.'));
            return; // Not possible; unable to acquire order type.
        } elseif (!($item_id = (int) $item_id)) {
            debug(0, c::issue(vars(), 'Empty item ID.'));
            return; // Not possible; empty item ID.
        } elseif (!$WC_Product->exists() || !($product_id = (int) $WC_Product->id)) {
            debug(0, c::issue(vars(), 'Unable to acquire product ID.'));
            return; // Not possible; unable to acquire product ID.
        } elseif (!($product_type = $WC_Product->get_type())) {
            debug(0, c::issue(vars(), 'Unable to acquire product type.'));
            return; // Not possible; unable to acquire product type.
        }
        $product_permissions = a::getProductMeta($product_id, 'permissions');

        wc_delete_order_item_meta($item_id, $this->product_meta_prefix.'type');
        wc_add_order_item_meta($item_id, $this->product_meta_prefix.'type', $product_type);

        wc_delete_order_item_meta($item_id, $this->product_meta_prefix.'permissions');
        foreach ($product_permissions as $_product_permission) { // Each permission.
            wc_add_order_item_meta($item_id, $this->product_meta_prefix.'permissions', $_product_permission);
        } // unset($_product_permission); // Housekeeping.

        c::review(compact(// Log for review.
            'order_id',
            'order_type',
            'item_id',
            'product_id',
            'product_type',
            'product_permissions'
        ), 'Updating custom order item meta.');
    }

    /**
     * On an order being given to a user.
     *
     * @since 160611 Orders given.
     *
     * @param string|int $order_id Order ID.
     *
     * @note This works for subscription order items also.
     */
    public function onOrderGiven($order_id)
    {
        if (!($order_id = (int) $order_id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible; empty order ID.
        } elseif (!($WC_Order = wc_get_order($item_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order.'));
            return; // Not possible; unable to acquire order.
        }
        foreach ($WC_Order->get_items() as $_item_id => $_item) {
            if (($_WC_Product = $WC_Order->get_product_from_item($_item))) {
                $this->onOrderAddProduct($order_id, $_item_id, $_WC_Product, $_item['qty'] ?? 1);
            }
        } // unset($_item_id, $_item, $_WC_Product); // Housekeeping.
    }

    /**
     * On product added to order (admin side).
     *
     * @since 160524 Order item meta utilities.
     *
     * @param string|int $order_id Order ID.
     * @param array      $data     AJAX data.
     *
     * @note This works for subscription order items also.
     */
    public function onSavedOrderItems($order_id, array $data)
    {
        if (!($order_id = (int) $order_id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible.
        } elseif (!($WC_Order = wc_get_order($order_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order.'));
            return; // Not possible; unable to acquire order.
        } elseif (!($order_type = get_post_type($order_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order type.'));
            return; // Not possible; unable to acquire order type.
        } elseif (empty($data['order_item_id'])) {
            debug(0, c::issue(vars(), 'Missing order item IDs.'));
            return; // Not possible; missing `order_item_id` index.
        } elseif (!is_array($data['order_item_id']) && !is_numeric($data['order_item_id'])) {
            debug(0, c::issue(vars(), 'Unexpected order item IDs.'));
            return; // Not possible.
        }
        foreach ((array) $data['order_item_id'] as $_item_id) {
            if (!($_item_id = (int) $_item_id)) {
                debug(0, c::issue(vars(), 'Empty item ID.'));
                continue; // Not possible; empty item ID.
            } elseif (!($_WC_Product = s::wcProductByOrderItemId($_item_id, $WC_Order))) {
                continue; // Not applicable; not associated w/ a product.
            } elseif (!$_WC_Product->exists() || !($_product_id = (int) $_WC_Product->id)) {
                debug(0, c::issue(vars(), 'Unable to acquire product ID.'));
                continue; // Not possible; unable to acquire product ID.
            } elseif (!($_product_type = $_WC_Product->get_type())) {
                debug(0, c::issue(vars(), 'Unable to acquire product type.'));
                continue; // Not possible; unable to acquire product type.
            }
            $_product_permissions = a::getProductMeta($_product_id, 'permissions');

            wc_delete_order_item_meta($_item_id, $this->product_meta_prefix.'type');
            wc_add_order_item_meta($_item_id, $this->product_meta_prefix.'type', $_product_type);

            wc_delete_order_item_meta($_item_id, $this->product_meta_prefix.'permissions');
            foreach ($_product_permissions as $_product_permission) { // Each permission.
                wc_add_order_item_meta($_item_id, $this->product_meta_prefix.'permissions', $_product_permission);
            } // unset($_product_permission); // Housekeeping.

            c::review(compact(// Log for review.
                'order_id',
                'order_type',
                '_item_id',
                '_product_id',
                '_product_type',
                '_product_permissions'
            ), 'Updating custom order item meta.');
        } // unset($_item_id, $_product_id, $_WC_Product, $_product_type, $_product_permissions); // Housekeeping.

        $old_status = $WC_Order->get_status();
        $new_status = (string) ($_POST['order_status'] ?? '');
        $new_status = mb_stripos($new_status, 'wc-') === 0 ? substr($new_status, 3) : $new_status;
        $new_status = $new_status ?: $old_status; // i.e., There is no change in the latter.

        if ($new_status === $old_status) { // Special case; i.e., the status is not changing?
            // In this case fake a status change so permissions are updated accordingly.

            switch ($order_type) { // Either an order or subscription.

                case $this->subscription_post_type:
                    $subscription_id = $order_id; // Subscription.
                    a::psuedoSubscriptionStatusChanged($subscription_id, $old_status, $new_status);
                    break; // Fake status change so permissions are updated accordingly.

                default: // Any other order type.
                    a::psuedoOrderStatusChanged($order_id, $old_status, $new_status);
                    break; // Fake status change so permissions are updated accordingly.
            }
        }
    }
}
