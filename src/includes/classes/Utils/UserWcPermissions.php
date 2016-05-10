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
 * User WC permission utilities.
 *
 * @since 16xxxx Order-related events.
 */
class UserWcPermissions extends SCoreClasses\SCore\Base\Core
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
     * Order is complete.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $order_id Order ID.
     */
    public function onOrderStatusComplete($order_id)
    {
        if (!($order_id = (int) $order_id)) {
            return; // Not possible.
        }
        $this->maybeGrantOrderPermissions($order_id);
    }

    /**
     * Order is processing.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $order_id Order ID.
     */
    public function onOrderStatusProcessing($order_id)
    {
        if (!($order_id = (int) $order_id)) {
            return; // Not possible.
        } elseif (!s::getOption('orders_always_grant_immediate_access')) {
            return; // Must wait for `complete` status.
        }
        $this->maybeGrantOrderPermissions($order_id);
    }

    /**
     * Maybe grant order permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param int $order_id Order ID.
     */
    protected function maybeGrantOrderPermissions(int $order_id)
    {
        if (!($order_id = (int) $order_id)) {
            return; // Not possible.
        } elseif ($this->orderPermissionsGranted($order_id)) {
            return; // Permissings granted already.
        } elseif (!($order = wc_get_order($order_id))) {
            return; // Not possible.
        }
        $this->orderPermissionsGranted($order_id, true); // Flag as true.

        foreach ($order->get_items() ?: [] as $_item) {
            if (($_product = $order->get_product_from_item($_item)) && $_product->exists()) {
                // @TODO Get product meta w/ permission details.
            }
        } // unset($_item, $_product); // Housekeeping.
    }

    /**
     * Order permissions granted?
     *
     * @since 16xxxx Order-related events.
     *
     * @param int  $order_id Order ID.
     * @param bool $granted  If setting the value.
     *
     * @return bool True if permissions have been granted.
     */
    protected function orderPermissionsGranted(int $order_id, bool $granted = null): bool
    {
        if (!$order_id) { // Empty?
            return false; // Not possible.
        }
        $meta_key = '_'.$this->App->Config->©brand['©var'].'_permissions_granted';

        if (isset($granted)) { // Setting the value?
            update_post_meta($order_id, $meta_key, (int) $granted);
        }
        return (bool) get_post_meta($order_id, $meta_key, true);
    }
}
