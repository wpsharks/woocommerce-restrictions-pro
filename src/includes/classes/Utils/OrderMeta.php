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
     * Class constructor.
     *
     * @since 16xxxx Order-related events.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);
    }

    /**
     * On add order item meta.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $item_id          Order item ID.
     * @param array      $cart_item_values Cart item values.
     */
    public function onAddOrderItemMeta($item_id, array $cart_item_values)
    {
        if (!($item_id = (int) $item_id)) {
            return; // Not possible.
        } elseif (empty($cart_item_values['product_id'])) {
            return; // Not possible.
        } elseif (!($WC_Product = wc_get_product($cart_item_values['product_id']))) {
            return; // Not possible.
        } elseif (!$WC_Product->exists()) {
            return; // Not possible.
        }
        $product_meta_prefix = a::productMetaPrefix();
        $product_type        = $WC_Product->get_type(); // i.e., `->product_type`.
        $product_permissions = a::getProductMeta($WC_Product->id, 'permissions');

        wc_add_order_item_meta($item_id, $product_meta_prefix.'type', $product_type);

        foreach ($product_permissions as $_permission) { // Each permission.
            wc_add_order_item_meta($item_id, $product_meta_prefix.'permissions', $_permission);
        } // unset($_permission); // Housekeeping.

        a::addLogEntry('order-meta', c::dump(compact('item_id', 'WC_Product', 'product_type', 'product_permissions'), true));
    }
}
