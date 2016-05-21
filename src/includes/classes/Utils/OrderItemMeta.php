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
 * Order item meta utilities.
 *
 * @since 16xxxx Order item meta utilities.
 */
class OrderItemMeta extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription post type.
     *
     * @since 16xxxx Order item meta utilities.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Product meta prefix.
     *
     * @since 16xxxx Order item meta utilities.
     *
     * @param string Product meta prefix.
     */
    protected $product_meta_prefix;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order item meta utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->subscription_post_type = a::subscriptionPostType();
        $this->product_meta_prefix    = a::productMetaPrefix();
    }

    /**
     * Hidden meta keys.
     *
     * @since 16xxxx Order item meta utilities.
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
     * On add order item meta (during checkout).
     *
     * @since 16xxxx Order item meta utilities.
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
            return; // Not applicable; not associated w/ a product ID.
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

        a::addLogEntry(__METHOD__, compact(
            'item_id',
            'product_type',
            'product_permissions'
        ), __('Updating custom order item meta.', 's2member-x'));
    }

    /**
     * On product added to order (admin side).
     *
     * @since 16xxxx Order item meta utilities.
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
                continue; // Not applicable; not associated w/ a product ID.
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

            a::addLogEntry(__METHOD__, compact(
                '_item_id',
                '_product_type',
                '_product_permissions'
            ), __('Updating custom order item meta.', 's2member-x'));
        } // unset($_item_id, $_product_id, $_WC_Product, $_product_type, $_product_permissions); // Housekeeping.
    }
}
