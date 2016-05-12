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
     * Core product types.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array Core product types.
     */
    protected $core_product_types;

    /**
     * Subscription product types.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array Subscription product types.
     */
    protected $subscription_product_types;

    /**
     * All product types.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array All product types.
     */
    protected $all_product_types;

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

        $this->core_product_types = [
            'simple', // Covers most products sold w/ WC.
            'grouped', // A collection of other products; i.e., a group of products.
            'external', // Listed in the storefront but sold elsewhere.
            'variable', // A variable product; i.e., has variations.
            'variation', // A product variation.
        ];
        $this->subscription_product_types = [
            'subscription', // Cover most subscriptions sold w/ WC.
            'variable-subscription', 'variable_subscription', // A variable subscription; i.e., has variations.
            'subscription-variation', 'subscription_variation', // A subscription variation.
        ];
        // There is some inconsistency in the WC subscriptions plugin.
        // In some places they use a dash, and in others it uses an underscore.
        // The official definition is with an `_`, but that seems likely to change.

        $this->all_product_types = array_keys(wc_get_product_types()); // Without a `wc-` prefix.
    }

    /**
     * Order status change.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $order_id   Order ID.
     * @param string     $old_status Old status prior to change.
     * @param string     $new_status The new status after this change.
     *
     * Order statuses explained in greater detail.
     * See also: `array_keys(wc_get_order_statuses())`.
     *  ↑ Array keys include the `wc-` prefix.
     *
     * - `pending` Order received (unpaid). e.g., abandoned orders have this status also.
     *
     * - `processing` Payment received & stock reduced. Awaiting review/fulfillment.
     *
     * - `on-hold` Stock is reduced. Awaiting payment.
     *
     * - `completed` Order fulfilled and complete.
     *
     * - `cancelled` Cancelled by an admin or the customer.
     *
     * - `refunded` Refunded by an admin or via a payment gateway notification.
     *
     * - `failed` Payment failed or was declined (unpaid). Note that this status may not show
     *            immediately and instead show as pending until verified (e.g., PayPal).
     */
    public function onOrderStatusChanged($order_id, string $old_status, string $new_status)
    {
        if (!($order_id = (int) $order_id)) {
            return; // Not possible.
        } elseif (!($WC_Order = wc_get_order($order_id))) {
            return; // Not possible.
        } elseif (!($WC_Order instanceof \WC_Order)) {
            return; // Not possible.
        }
        if (in_array($new_status, ['processing', 'completed'], true)) {
            if ($new_status === 'completed' || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantOrderPermissions($WC_Order, $old_status, $new_status);
            }
        } elseif (in_array($new_status, ['on-hold', 'cancelled', 'refunded', 'failed'], true)) {
            $this->maybeRevokeOrderPermissions($WC_Order, $old_status, $new_status);
        }
        file_put_contents(WP_CONTENT_DIR.'/order-status-change.log', print_r(compact('WC_Order', 'new_status', 'old_status'), true)."\n\n", FILE_APPEND);
    }

    /**
     * Subscription status change.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $subscription_id Subscription ID.
     * @param string     $old_status      Old status prior to change.
     * @param string     $new_status      The new status after this change.
     *
     * Subscription statuses explained in greater detail.
     * See also: `array_keys(wcs_get_subscription_statuses())`.
     *  ↑ Array keys include the `wc-` prefix.
     *
     * - `pending` When an order is received (unpaid); the subscription is created as pending.
     *             In other words, a `pending` subscription is the same as a `pending` order.
     *
     * - `active` The subscription becomes `active`; i.e., like a `complete` order status.
     *            Also occurs during a renewal; i.e., `on-hold` to `active` when payment goes through.
     *
     * - `on-hold` Also occurs before a renewal is processed, which could fail.
     *             If a renewal fails the `on-hold` status remains and it does not become `active` again.
     *
     * - `cancelled` Cancelled by an admin or user in one way or another.
     *               e.g., when a subscription reaches the end of a trial and there is no payment.
     *               - A cancellation event may also occur after a `pending-cancel` status.
     *                 e.g., when a cancellation occurs but prepaid time still remains.
     *               - Also occurs before a subscription is trashed/deleted.
     *               - Also occurs on max failed payments.
     *
     * - `switched` @TODO Figure out exactly when this occurs.
     *
     * - `expired` This is a lot like `cancelled`, except this occurs whenever a subscription
     *             reaches a fixed end date; e.g., valid from A until B (expires on B date).
     *
     * - `pending-cancel` Cancelled by an admin or user in one way or another.
     *                    e.g., when a cancellation occurs but prepaid time still remains.
     *                    See official docs here: <http://jas.xyz/1X0r6oC>
     */
    public function onSubscriptionStatusChanged($subscription_id, string $old_status, string $new_status)
    {
        if (!($subscription_id = (int) $subscription_id)) {
            return; // Not possible.
        } elseif (!($WC_Subscription = wcs_get_subscription($subscription_id))) {
            return; // Not possible.
        } elseif (!($WC_Subscription instanceof \WC_Subscription)) {
            return; // Not possible.
        }
        if (in_array($new_status, ['active'], true)) {
            if ($new_status === 'active' || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
            }
        } elseif (in_array($new_status, ['on-hold', 'cancelled', 'expired'], true)) {
            $this->maybeRevokeSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
        }
        file_put_contents(WP_CONTENT_DIR.'/subscription-status-change.log', print_r(compact('WC_Subscription', 'new_status', 'old_status'), true)."\n\n", FILE_APPEND);
    }

    /**
     * Maybe grant order permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Order $WC_Order   Order instance.
     * @param string    $old_status Old status prior to change.
     * @param string    $new_status The new status after this change.
     */
    protected function maybeGrantOrderPermissions(\WC_Order $WC_Order, string $old_status, string $new_status)
    {
        if ($this->orderPermissionsGranted((int) $WC_Order->post->ID)) {
            return; // Permissings granted already.
        }
        $this->orderPermissionsGranted((int) $WC_Order->post->ID, true); // Granting now.

        foreach ($WC_Order->get_items() ?: [] as $_item) {
            if (($_WC_Product = $WC_Order->get_product_from_item($_item)) && $_WC_Product->exists()) {
                if (!$_WC_Product->is_type($this->subscription_product_types)) {
                    file_put_contents(WP_CONTENT_DIR.'/order-grant-permissions.log', print_r(compact('_WC_Product'), true)."\n\n", FILE_APPEND);
                    // @TODO
                }
            }
        } // unset($_item, $_WC_Product); // Housekeeping.
    }

    /**
     * Maybe grant subscription permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param string           $old_status      Old status prior to change.
     * @param string           $new_status      The new status after this change.
     */
    protected function maybeGrantSubscriptionPermissions(\WC_Subscription $WC_Subscription, string $old_status, string $new_status)
    {
        if ($this->subscriptionPermissionsGranted((int) $WC_Subscription->post->ID)) {
            return; // Permissings granted already.
        }
        $this->subscriptionPermissionsGranted((int) $WC_Subscription->post->ID, true); // Granting now.

        foreach ($WC_Subscription->get_items() ?: [] as $_item) {
            if (($_WC_Product = $WC_Subscription->get_product_from_item($_item)) && $_WC_Product->exists()) {
                if ($_WC_Product->is_type($this->subscription_product_types)) {
                    file_put_contents(WP_CONTENT_DIR.'/subscription-grant-permissions.log', print_r(compact('_WC_Product'), true)."\n\n", FILE_APPEND);
                    // @TODO
                }
            }
        } // unset($_item, $_WC_Product); // Housekeeping.
    }

    /**
     * Maybe revoke order permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Order $WC_Order   Order instance.
     * @param string    $old_status Old status prior to change.
     * @param string    $new_status The new status after this change.
     */
    protected function maybeRevokeOrderPermissions(\WC_Order $WC_Order, string $old_status, string $new_status)
    {
        if (!$this->orderPermissionsGranted((int) $WC_Order->post->ID)) {
            return; // Permissings revoked already.
        }
        $this->orderPermissionsGranted((int) $WC_Order->post->ID, false); // Revoking now.

        foreach ($WC_Order->get_items() ?: [] as $_item) {
            if (($_WC_Product = $WC_Order->get_product_from_item($_item)) && $_WC_Product->exists()) {
                if (!$_WC_Product->is_type($this->subscription_product_types)) {
                    file_put_contents(WP_CONTENT_DIR.'/order-revoke-permissions.log', print_r(compact('_WC_Product'), true)."\n\n", FILE_APPEND);
                    // @TODO
                }
            }
        } // unset($_item, $_WC_Product); // Housekeeping.
    }

    /**
     * Maybe revoke subscription permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param string           $old_status      Old status prior to change.
     * @param string           $new_status      The new status after this change.
     */
    protected function maybeRevokeSubscriptionPermissions(\WC_Subscription $WC_Subscription, string $old_status, string $new_status)
    {
        if (!$this->subscriptionPermissionsGranted((int) $WC_Subscription->post->ID)) {
            return; // Permissings revoked already.
        }
        $this->subscriptionPermissionsGranted((int) $WC_Subscription->post->ID, false); // Revoking now.

        foreach ($WC_Subscription->get_items() ?: [] as $_item) {
            if (($_WC_Product = $WC_Subscription->get_product_from_item($_item)) && $_WC_Product->exists()) {
                if ($_WC_Product->is_type($this->subscription_product_types)) {
                    file_put_contents(WP_CONTENT_DIR.'/subscription-revoke-permissions.log', print_r(compact('_WC_Product'), true)."\n\n", FILE_APPEND);
                    // @TODO
                }
            }
        } // unset($_item, $_WC_Product); // Housekeeping.
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

    /**
     * Subscription permissions granted?
     *
     * @since 16xxxx Order-related events.
     *
     * @param int  $subscription_id Subscription ID.
     * @param bool $granted         If setting the value.
     *
     * @return bool True if permissions have been granted.
     */
    protected function subscriptionPermissionsGranted(int $subscription_id, bool $granted = null): bool
    {
        if (!$subscription_id) { // Empty?
            return false; // Not possible.
        }
        $meta_key = '_'.$this->App->Config->©brand['©var'].'_permissions_granted';

        if (isset($granted)) { // Setting the value?
            update_post_meta($subscription_id, $meta_key, (int) $granted);
        }
        return (bool) get_post_meta($subscription_id, $meta_key, true);
    }
}
