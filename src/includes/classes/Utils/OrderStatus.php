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
 * Order status changes.
 *
 * @since 16xxxx Order-related events.
 */
class OrderStatus extends SCoreClasses\SCore\Base\Core
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
            'variation', // A variable product variation.
            // ↑ These two product types can become line-items.

            'variable', // Variable product; i.e., sells a variation.
            // Forms a group of variations that aim to sell a `variation`.

            'external', // Listed in the storefront but sold elsewhere.
            // An external product is never a line-item, it is sold externally.

            'grouped', // A collection of other products; i.e., a group of products.
            // A group product is never a line-item; it only forms a group of others.
        ];
        $this->subscription_product_types = [
            'subscription', // Covers most subscriptions sold w/ WooCommerce.
            'subscription-variation', 'subscription_variation', // A subscription variation.
            // ↑ These two product types can become line-items.

            'variable-subscription', 'variable_subscription', // Variable subscription; i.e., has variations.
            // Forms a group of subscription variations that aim to sell a `subscription-variation`.

            // There is some inconsistency in the WC subscriptions plugin.
            // In some places they use a dash, and in others it uses an underscore.
            // The official definition is with an `_`, but that seems likely to change.
        ];
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
        }
        $always_grant_statuses = s::applyFilters('always_grant_user_persmissions_on_order_statuses', ['completed'], $WC_Order);
        $grant_statuses        = s::applyFilters('grant_user_persmissions_on_order_statuses', ['processing', 'completed'], $WC_Order);
        $revoke_statuses       = s::applyFilters('revoke_user_persmissions_on_order_statuses', ['pending', 'on-hold', 'cancelled', 'refunded', 'failed'], $WC_Order);

        if (in_array($new_status, $grant_statuses, true)) {
            if (in_array($new_status, $always_grant_statuses, true) || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantOrderPermissions($WC_Order, $old_status, $new_status);
            }
        } elseif (in_array($new_status, $revoke_statuses, true)) {
            $this->maybeRevokeOrderPermissions($WC_Order, $old_status, $new_status);
        }
        a::addLogEntry('order-status-changed', c::dump(compact('WC_Order', 'new_status', 'old_status'), true));
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
     *             This is also another word for `suspend`; i.e., suspending a subscription puts it `on-hold`.
     *
     * - `cancelled` Cancelled by an admin or user in one way or another.
     *               e.g., when a subscription reaches the end of a trial and there is no payment.
     *               - A cancellation event may also occur after a `pending-cancel` status.
     *                 e.g., when a cancellation occurs but prepaid time still remains.
     *               - Also occurs before a subscription is trashed/deleted.
     *               - Also occurs when a parent order is trashed/deleted.
     *               - Also occurs on max failed payments.
     *
     *               When a subscription is trashed it is `cancelled`, and it is currently
     *               impossible to restore the subscription in any meaningful/effective way.
     *               Once trashed, it can be restored, but it remains in a `cancelled` state.
     *               Attempting to change the status manually is also impossible for some reason.
     *               ~ i.e., Only the `cancelled` option is made available in the UI.
     *
     *               Another strange behavior is that trashing a parent order will somehow
     *               break the connection between the parent order and the child subscription.
     *               Once a parent order is trashed, the connection is broken and cannot be restored.
     *
     * - `switched` Deprecated in 2.0. This doesn't seem to be used any longer.
     *              Instead use hook: `woocommerce_subscriptions_switched_item`.
     *              ~ See {@link onSubscriptionItemSwitched()} below.
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
        }
        $always_grant_statuses = s::applyFilters('always_grant_user_persmissions_on_subscription_statuses', ['active'], $WC_Subscription);
        $grant_statuses        = s::applyFilters('grant_user_persmissions_on_subscription_statuses', ['active'], $WC_Subscription);
        $revoke_statuses       = s::applyFilters('revoke_user_persmissions_on_subscription_statuses', ['pending', 'on-hold', 'cancelled', 'expired'], $WC_Subscription);

        if (in_array($new_status, $grant_statuses, true)) {
            if (in_array($new_status, $always_grant_statuses, true) || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
            }
        } elseif (in_array($new_status, $revoke_statuses, true)) {
            $this->maybeRevokeSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
        }
        a::addLogEntry('subscription-status-changed', c::dump(compact('WC_Subscription', 'new_status', 'old_status'), true));
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
        if (!($order_id = (int) $WC_Order->id)) {
            return; // Not possible; no order ID.
        } elseif (!($user_id = (int) $WC_Order->user_id)) {
            return; // Not possible; no user ID.
        } elseif ($this->orderPermissionsGranted($order_id)) {
            return; // Permissings granted already.
        }
        $this->orderPermissionsGranted($order_id, true);

        // Note: We want to avoid looking for a `\WC_Product` object here.
        // An item may be associated with a product that no longer exists for whatever reason.

        foreach ($WC_Order->get_items() ?: [] as $_item) {
            if (!($_product_id = $this->productIdFromItem($_item))) {
                continue; // Not possible; no product ID.
            } elseif (!($_product_type = $this->productTypeFromItem($_item))) {
                continue; // Not possible; no product type.
            } elseif (in_array($_product_type, $this->subscription_product_types, true)) {
                continue; // Don't handle subscription product types here.
            } elseif (!($_product_permissions = $this->productPermissionsFromItem($_item))) {
                continue; // Not applicable; no product permissions.
            }
            $_user_permissions = a::userPermissions($user_id); // User permissions.

            foreach ($_product_permissions as $_ProductPermission) {
                if ($_ProductPermission->order_id === $order_id && $_ProductPermission->product_id === $_product_id) {
                    $_ProductPermission->update([]); // @TODO
                } else {
                    $_ProductPermission->update([]); // @TODO
                }
            } // unset($_ProductPermission); // Housekeeping.

            $_log_vars = compact(
                'order_id',
                'user_id',
                '_item',
                '_product_id',
                '_product_type',
                '_product_permissions'
            );
            a::addLogEntry('order-item-granted-permissions', c::dump($_log_vars, true));
        } // unset($_item, $_product_id, $_product_type, $_product_permissions, $_user_permissions, $_log_vars); // Housekeeping.
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
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->user_id)) {
            return; // Not possible; no user ID.
        } elseif ($this->subscriptionPermissionsGranted($subscription_id)) {
            return; // Permissings granted already.
        }
        $this->subscriptionPermissionsGranted($subscription_id, true);

        // Note: We want to avoid looking for a `\WC_Product` or `\WC_Subscription` object here.
        // An item may be associated with a product or subscription that no longer exists.

        foreach ($WC_Subscription->get_items() ?: [] as $_item) {
            if (!($_product_id = $this->productIdFromItem($_item))) {
                continue; // Not possible; no product ID.
            } elseif (!($_product_type = $this->productTypeFromItem($_item))) {
                continue; // Not possible; no product type.
            } elseif (!($_product_permissions = $this->productPermissionsFromItem($_item))) {
                continue; // Not applicable; no product permissions.
            }
            // Any type of product can be an item in a subscription; it's just like an order.
            // While we don't handle subscription product types when an order status changes, we DO handle
            // any type of product that is in a subscription; i.e., we don't check the product type here.

            // Under normal circumstances subscriptions will only contain subscription products. That's what a subscription is intended for.
            // However, if a subscription is created manually by a site owner, it may contain any line-item products that a site owner added to it.
            // So for instance, if a site owner creates a new subscription manually and adds three line-items, and one of those is a `simple` product,
            // we need to handle that here, even though it wouldn't ordinarily be associated with a subscription; it is if the site owner creates it that way.

            $_user_permissions = a::userPermissions($user_id); // User permissions.

            foreach ($_product_permissions as $_ProductPermission) {
                // @TODO
            } // unset($_ProductPermission); // Housekeeping.

            $_log_vars = compact(
                'subscription_id',
                'user_id',
                '_item',
                '_product_id',
                '_product_type',
                '_product_permissions'
            );
            a::addLogEntry('subscription-item-granted-permissions', c::dump($_log_vars, true));
        } // unset($_item, $_product_id, $_product_type, $_product_permissions, $_user_permissions, $_log_vars); // Housekeeping.
    }

    /**
     * Maybe switch subscription permissions.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param array            $new_item        The new item data.
     * @param array            $old_item        The old item data.
     */
    public function onSubscriptionItemSwitched(\WC_Subscription $WC_Subscription, array $new_item, array $old_item)
    {
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->user_id)) {
            return; // Not possible; no user ID.
        }
        // Note: We want to avoid looking for a `\WC_Product` or `\WC_Subscription` object here.
        // An item may be associated with a product or subscription that no longer exists.

        $new_product_id = $this->productIdFromItem($new_item);
        $old_product_id = $this->productIdFromItem($old_item);

        $new_product_type = $this->productTypeFromItem($new_item);
        $old_product_type = $this->productTypeFromItem($old_item);

        $new_product_permissions = $this->productPermissionsFromItem($new_item);
        $old_product_permissions = $this->productPermissionsFromItem($old_item);

        // Any type of product can be an item in a subscription; it's just like an order.
        // While we don't handle subscription product types when an order status changes, we DO handle
        // any type of product that is in a subscription; i.e., we don't check the product type here.

        // Under normal circumstances subscriptions will only contain subscription products. That's what a subscription is intended for.
        // However, if a subscription is created manually by a site owner, it may contain any line-item products that a site owner added to it.
        // So for instance, if a site owner creates a new subscription manually and adds three line-items, and one of those is a `simple` product,
        // we need to handle that here, even though it wouldn't ordinarily be associated with a subscription; it is if the site owner creates it that way.

        $user_permissions = a::userPermissions($user_id); // User permissions.

        foreach ($old_product_permissions as $_ProductPermission) {
            // @TODO
        } // unset($_ProductPermission); // Housekeeping.

        foreach ($new_product_permissions as $_ProductPermission) {
            // @TODO
        } // unset($_ProductPermission); // Housekeeping.

        $log_vars = compact(
            'subscription_id',
            'user_id',
            //
            'new_item',
            'new_product_id',
            'new_product_type',
            'new_product_permissions',
            //
            'old_item',
            'old_product_id',
            'old_product_type',
            'old_product_permissions'
        );
        a::addLogEntry('subscription-item-switched-permissions', c::dump($log_vars, true));
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
        if (!($order_id = (int) $WC_Order->id)) {
            return; // Not possible; no order ID.
        } elseif (!($user_id = (int) $WC_Order->user_id)) {
            return; // Not possible; no user ID.
        } elseif (!$this->orderPermissionsGranted($order_id)) {
            return; // Permissings revoked already.
        }
        $this->orderPermissionsGranted($order_id, false);

        // Note: We want to avoid looking for a `\WC_Product` object here.
        // An item may be associated with a product that no longer exists for whatever reason.

        foreach ($WC_Order->get_items() ?: [] as $_item) {
            if (!($_product_id = $this->productIdFromItem($_item))) {
                continue; // Not possible; no product ID.
            } elseif (!($_product_type = $this->productTypeFromItem($_item))) {
                continue; // Not possible; no product type.
            } elseif (in_array($_product_type, $this->subscription_product_types, true)) {
                continue; // Don't handle subscription product types here.
            } elseif (!($_product_permissions = $this->productPermissionsFromItem($_item))) {
                continue; // Not applicable; no product permissions.
            }
            $_user_permissions = a::userPermissions($user_id); // User permissions.

            foreach ($_product_permissions as $_ProductPermission) {
                // @TODO
            } // unset($_ProductPermission); // Housekeeping.

            $_log_vars = compact(
                'order_id',
                'user_id',
                '_item',
                '_product_id',
                '_product_type',
                '_product_permissions'
            );
            a::addLogEntry('order-item-revoked-permissions', c::dump($_log_vars, true));
        } // unset($_item, $_product_id, $_product_type, $_product_permissions, $_user_permissions, $_log_vars); // Housekeeping.
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
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->user_id)) {
            return; // Not possible; no user ID.
        } elseif (!$this->subscriptionPermissionsGranted($subscription_id)) {
            return; // Permissings revoked already.
        }
        $this->subscriptionPermissionsGranted($subscription_id, false);

        // Note: We want to avoid looking for a `\WC_Product` or `\WC_Subscription` object here.
        // An item may be associated with a product or subscription that no longer exists.

        foreach ($WC_Subscription->get_items() ?: [] as $_item) {
            if (!($_product_id = $this->productIdFromItem($_item))) {
                continue; // Not possible; no product ID.
            } elseif (!($_product_type = $this->productTypeFromItem($_item))) {
                continue; // Not possible; no product type.
            } elseif (!($_product_permissions = $this->productPermissionsFromItem($_item))) {
                continue; // Not applicable; no product permissions.
            }
            $_user_permissions = a::userPermissions($user_id); // User permissions.

            // Any type of product can be an item in a subscription; it's just like an order.
            // While we don't handle subscription product types when an order status changes, we DO handle
            // any type of product that is in a subscription; i.e., we don't check the product type here.

            // Under normal circumstances subscriptions will only contain subscription products. That's what a subscription is intended for.
            // However, if a subscription is created manually by a site owner, it may contain any line-item products that a site owner added to it.
            // So for instance, if a site owner creates a new subscription manually and adds three line-items, and one of those is a `simple` product,
            // we need to handle that here, even though it wouldn't ordinarily be associated with a subscription; it is if the site owner creates it that way.

            foreach ($_product_permissions as $_ProductPermission) {
                // @TODO
            } // unset($_ProductPermission); // Housekeeping.

            $_log_vars = compact(
                'subscription_id',
                'user_id',
                '_item',
                '_product_id',
                '_product_type',
                '_product_permissions'
            );
            a::addLogEntry('subscription-item-revoked-permissions', c::dump($_log_vars, true));
        } // unset($_item, $_product_id, $_product_type, $_product_permissions, $_user_permissions, $_log_vars); // Housekeeping.
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

    /**
     * Product ID from item.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array $item Order line item data.
     *
     * @return int Product ID from item.
     */
    protected function productIdFromItem(array $item): int
    {
        return (int) ($item['product_id'] ?? 0);
    }

    /**
     * Product type from item.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array $item Order line item data.
     *
     * @return string Product type from item.
     */
    protected function productTypeFromItem(array $item): string
    {
        if (empty($item['item_meta'])) {
            return ''; // Not possible; no meta values.
        }
        return (string) ($item['item_meta'][a::productMetaPrefix().'type'] ?? '');
    }

    /**
     * Product permissions from item.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array $item Order line item data.
     *
     * @return Classes\ProductPermission[] Product permissions from item.
     */
    protected function productPermissionsFromItem(array $item): array
    {
        if (empty($item['item_meta'])) {
            return []; // Not possible; no meta values.
        }
        $product_permissions  = []; // Initialize.
        $_product_permissions = $item['item_meta'][a::productMetaPrefix().'permissions'] ?? [];
        $_product_permissions = is_array($_product_permissions) ? $_product_permissions : [];

        foreach ($_product_permissions as $_product_permission) {
            $_product_permission = maybe_unserialize($_product_permission);

            if (!($_product_permission instanceof \StdClass)) {
                continue; // Invalid data; not possible.
            }
            $product_permissions[] = $this->App->Di->get(Classes\ProductPermission::class, ['data' => $_product_permission]);
        } // unset($_product_permissions, $_product_permission); // Housekeeping.

        return $product_permissions;
    }
}
