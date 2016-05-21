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
 * Order meta utilities.
 *
 * @since 16xxxx Order-related events.
 */
class OrderMeta extends SCoreClasses\SCore\Base\Core
{
    /**
     * Order post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Order post type.
     */
    protected $order_post_type;

    /**
     * Subscription post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Product meta prefix.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Product meta prefix.
     */
    protected $product_meta_prefix;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order-related events.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->order_post_type        = a::orderPostType();
        $this->subscription_post_type = a::subscriptionPostType();
        $this->product_meta_prefix    = a::productMetaPrefix();
    }

    /**
     * On add order item meta (during checkout).
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $item_id Order item ID.
     *
     * @note This works for Subscriptions also.
     */
    public function onAddOrderItemMeta($item_id)
    {
        if (!($item_id = (int) $item_id)) {
            return; // Not possible; empty item ID.
        } elseif (!($product_id = (int) wc_get_order_item_meta($item_id, '_product_id', true))) {
            return; // Not possible; unable to acquire product ID.
        } elseif (!($WC_Product = wc_get_product($product_id)) || !$WC_Product->exists()) {
            return; // Not possible; unable to acquire product instance.
        }
        $product_type        = $WC_Product->get_type();
        $product_permissions = a::getProductMeta($product_id, 'permissions');

        wc_delete_order_item_meta($item_id, $this->product_meta_prefix.'type');
        wc_add_order_item_meta($item_id, $this->product_meta_prefix.'type', $product_type);

        foreach ($product_permissions as $_product_permission) { // Each permission.
            wc_delete_order_item_meta($item_id, $this->product_meta_prefix.'permissions');
            wc_add_order_item_meta($item_id, $this->product_meta_prefix.'permissions', $_product_permission);
        } // unset($_product_permission); // Housekeeping.
    }

    /**
     * On product added to order (admin side).
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $order_id Order ID.
     * @param array      $data     AJAX data.
     *
     * @note This works for Subscriptions also.
     */
    public function onSavedOrderItems($order_id, array $data)
    {
        if (!($order_id = (int) $order_id)) {
            return; // Not possible.
        } elseif (empty($data['order_item_id'])) {
            return; // Not possible.
        } elseif (!is_array($data['order_item_id'])) {
            return; // Not possible.
        }
        foreach ($data['order_item_id'] as $_item_id) {
            if (!($_item_id = (int) $_item_id)) {
                continue; // Not possible; empty item ID.
            } elseif (!($_product_id = (int) wc_get_order_item_meta($_item_id, '_product_id', true))) {
                continue; // Not possible; unable to acquire product ID.
            } elseif (!($_WC_Product = wc_get_product($_product_id)) || !$_WC_Product->exists()) {
                continue; // Not possible; unable to acquire product instance.
            }
            $_product_type        = $_WC_Product->get_type();
            $_product_permissions = a::getProductMeta($_product_id, 'permissions');

            wc_delete_order_item_meta($_item_id, $this->product_meta_prefix.'type');
            wc_add_order_item_meta($_item_id, $this->product_meta_prefix.'type', $_product_type);

            foreach ($_product_permissions as $_product_permission) { // Each permission.
                wc_delete_order_item_meta($_item_id, $this->product_meta_prefix.'permissions');
                wc_add_order_item_meta($_item_id, $this->product_meta_prefix.'permissions', $_product_permission);
            } // unset($_product_permission); // Housekeeping.
        } // unset($_item_id, $_product_id, $_WC_Product, $_product_type, $_product_permissions); // Housekeeping.
    }

    /**
     * Hidden meta keys (do not display in admin).
     *
     * @since 16xxxx Order-related events.
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
     * On post meta update.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $meta_id    Meta ID.
     * @param string|int $post_id    Post ID.
     * @param string     $meta_key   Meta key.
     * @param mixed      $meta_value Meta value (new value).
     *
     * @note This works for Subscriptions also.
     */
    public function onPostMetaUpdate($meta_id, $post_id, $meta_key, $meta_value)
    {
        if (!($meta_id = (int) $meta_id)) {
            return; // Not possible.
        } elseif (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif ($meta_key !== '_customer_user') {
            return; // Only key we look at, for now.
        } elseif (!($post_type = get_post_type($post_id))) {
            return; // Not possible; no post type.
        } elseif (!in_array($post_type, [$this->order_post_type, $this->subscription_post_type], true)) {
            return; // Not applicable.
        }
        $new_user_id = (int) $meta_value; // New customer ID.
        $old_user_id = (int) get_post_meta($post_id, '_customer_user', true);

        if ($old_user_id && $new_user_id && $old_user_id !== $new_user_id) {
            switch ($post_type) { // Either an order or subscription.

                case $this->order_post_type:
                    $order_id = $post_id; // Post is an order.
                    a::transferUserPermissions($old_user_id, $new_user_id, ['where' => compact('order_id')]);
                    break; // Transfers permissions to new customer when user ID is changed on an order.

                case $this->subscription_post_type:
                    $subscription_id = $post_id; // Post is a subscription.
                    a::transferUserPermissions($old_user_id, $new_user_id, ['where' => compact('subscription_id')]);
                    break; // Transfers permissions to new customer when user ID is changed on an order.

                default: // Totally unexpected given the above validation.
                    throw new Exception('Expecting post type for order/subscription.');
            }
        }
    }
}
