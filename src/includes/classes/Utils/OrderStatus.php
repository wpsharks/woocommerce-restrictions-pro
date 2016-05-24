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
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Order status changes.
 *
 * @since 16xxxx Order status changes.
 */
class OrderStatus extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription product types.
     *
     * @since 16xxxx Order status changes.
     *
     * @param array Subscription product types.
     */
    protected $subscription_product_types;

    /**
     * User permission status map.
     *
     * @since 16xxxx Order status changes.
     *
     * @param array User permission status map.
     */
    protected $user_permission_status_map;

    /**
     * Product meta prefix.
     *
     * @since 16xxxx Order status changes.
     *
     * @param string Product meta prefix.
     */
    protected $product_meta_prefix;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order status changes.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);
        /*
         * `simple`  Covers most products.
         * `variation` A variable product variation.
         * ↑ These two are the most common product types.
         *
         * `variable` Variable product; i.e., sells a variation.
         * Forms a group of variations. A `variable` product sells a `variation`.
         *
         * `external` Listed in the storefront but sold elsewhere.
         * An external product is never a line-item, it is sold externally.
         *
         * `grouped` A collection of other products; i.e., a group of products.
         * A group product is never a line-item; it only forms a group of others.
         */
        $this->subscription_product_types = [
            'subscription', // Covers most subscriptions sold via WooCommerce.
            'subscription-variation', 'subscription_variation', // A subscription variation.
            // ↑ These two are the most common subscription product types.

            'variable-subscription', 'variable_subscription', // Variable subscription; i.e., has variations.
            // Forms a group of subscription variations. A `variable-subscription` sells a `subscription-variation`.

            // There is some inconsistency in the WC subscriptions plugin.
            // In some places they use a dash, and in others it uses an underscore.
            // The official definition is with an `_`, but that seems likely to change.
        ];
        $this->user_permission_status_map = [
            // Enabled statuses.
            'processing' => 'enabled',
            'completed'  => 'enabled',
            'active'     => 'enabled',

            // Disabled statuses.
            'draft'     => 'pending',
            'pending'   => 'pending',
            'on-hold'   => 'on-hold',
            'expired'   => 'expired',
            'refunded'  => 'refunded',
            'cancelled' => 'cancelled',
            'failed'    => 'failed',
            'switched'  => 'switched',
        ];
        $this->subscription_product_types = s::applyFilters('order_status_subscription_product_types', $this->subscription_product_types);
        $this->user_permission_status_map = s::applyFilters('order_to_user_permission_status_map', $this->user_permission_status_map);
        $this->product_meta_prefix        = a::productMetaPrefix(); // Plugin-specific meta prefix.
    }

    /**
     * Order status change.
     *
     * @since 16xxxx Order status changes.
     *
     * @param string|int $order_id   Order ID.
     * @param string     $old_status Old status prior to change.
     * @param string     $new_status The new status after this change.
     *
     * Order statuses explained in greater detail.
     * See also: `array_keys(wc_get_order_statuses())`.
     *  ↑ Array keys include the `wc-` prefix.
     *
     * - `draft` When an order is still in the draft phase.
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
        c::review(compact(// Log for review.
            'order_id',
            'new_status',
            'old_status'
        ), 'Monitoring order status changes.');

        if (!($order_id = (int) $order_id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible.
        } elseif (empty($this->user_permission_status_map[$old_status])) {
            debug(0, c::issue(vars(), 'Unexpected old status.'));
            return; // Unrecognized new status string.
        } elseif (empty($this->user_permission_status_map[$new_status])) {
            debug(0, c::issue(vars(), 'Unexpected new status.'));
            return; // Unrecognized new status string.
        } elseif (!($WC_Order = wc_get_order($order_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire order.'));
            return; // Not possible.
        }
        $always_grant_statuses = s::applyFilters('always_grant_user_persmissions_on_order_statuses', ['completed'], $WC_Order);
        $grant_statuses        = s::applyFilters('grant_user_persmissions_on_order_statuses', ['processing', 'completed'], $WC_Order);
        $revoke_statuses       = s::applyFilters('revoke_user_persmissions_on_order_statuses', ['draft', 'pending', 'on-hold', 'cancelled', 'refunded', 'failed'], $WC_Order);

        if (in_array($new_status, $grant_statuses, true)) {
            if (in_array($new_status, $always_grant_statuses, true) || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantOrderPermissions($WC_Order, $old_status, $new_status);
            }
        } elseif (in_array($new_status, $revoke_statuses, true)) {
            $this->maybeRevokeOrderPermissions($WC_Order, $old_status, $new_status);
        }
    }

    /**
     * Subscription status change.
     *
     * @since 16xxxx Order status changes.
     *
     * @param string|int $subscription_id Subscription ID.
     * @param string     $old_status      Old status prior to change.
     * @param string     $new_status      The new status after this change.
     *
     * Subscription statuses explained in greater detail.
     * See also: `array_keys(wcs_get_subscription_statuses())`.
     *  ↑ Array keys include the `wc-` prefix.
     *
     * - `draft` When a subscription is still in the draft phase.
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
        c::review(compact(// Log for review.
            'subscription_id',
            'new_status',
            'old_status'
        ), 'Monitoring subscription status changes.');

        if (!($subscription_id = (int) $subscription_id)) {
            debug(0, c::issue(vars(), 'Empty subscription ID.'));
            return; // Not possible.
        } elseif (empty($this->user_permission_status_map[$old_status])) {
            debug(0, c::issue(vars(), 'Unexpected old status.'));
            return; // Unrecognized new status string.
        } elseif (empty($this->user_permission_status_map[$new_status])) {
            debug(0, c::issue(vars(), 'Unexpected new status.'));
            return; // Unrecognized new status string.
        } elseif (!($WC_Subscription = wcs_get_subscription($subscription_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire subscription.'));
            return; // Not possible.
        }
        $always_grant_statuses = s::applyFilters('always_grant_user_persmissions_on_subscription_statuses', ['active'], $WC_Subscription);
        $grant_statuses        = s::applyFilters('grant_user_persmissions_on_subscription_statuses', ['active'], $WC_Subscription);
        $revoke_statuses       = s::applyFilters('revoke_user_persmissions_on_subscription_statuses', ['draft', 'pending', 'on-hold', 'cancelled', 'expired'], $WC_Subscription);

        if (in_array($new_status, $grant_statuses, true)) {
            if (in_array($new_status, $always_grant_statuses, true) || s::getOption('orders_always_grant_immediate_access')) {
                $this->maybeGrantSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
            }
        } elseif (in_array($new_status, $revoke_statuses, true)) {
            $this->maybeRevokeSubscriptionPermissions($WC_Subscription, $old_status, $new_status);
        }
    }

    /**
     * Maybe grant order permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param \WC_Abstract_Order $WC_Order   Order instance.
     * @param string             $old_status Old status prior to change.
     * @param string             $new_status The new status after this change.
     */
    protected function maybeGrantOrderPermissions(\WC_Abstract_Order $WC_Order, string $old_status, string $new_status)
    {
        if (!($order_id = (int) $WC_Order->id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible; no order ID.
        } elseif (!($user_id = (int) $WC_Order->get_user_id())) {
            return; // Not possible; no user ID.
        }
        foreach ($WC_Order->get_items() ?: [] as $_item_id => $_item) {
            $_item_id = (int) $_item_id; // Force integer.

            if (!($_product_id = a::productIdFromItem($_item))) {
                continue; // Not applicable; not associated w/ a product ID.
            } elseif (!($_product_type = $this->itemProductType($_item_id))) {
                debug(0, c::issue(vars(), 'Missing product type.'));
                continue; // Not possible; missing product type.
            } elseif (in_array($_product_type, $this->subscription_product_types, true)) {
                continue; // Don't handle subscription product types here.
            } elseif (!($_product_permissions = $this->itemProductPermissions($_item_id))) {
                continue; // Not applicable; no product permissions.
            }
            $_user_permissions         = a::userPermissions($user_id); // User permissions.
            $_updated_user_permissions = $_new_user_permissions = []; // Initialize.

            foreach ($_product_permissions as $_ProductPermission) {
                $_updated_existing_user_permission = false; // Initialize.

                # Attempt to update an existing user permission.
                foreach ($_user_permissions as $_UserPermission) {
                    if (!isset($_updated_user_permissions[$_UserPermission->ID])
                            && $_UserPermission->order_id === $order_id
                            && $_UserPermission->product_id === $_product_id
                            && $_UserPermission->item_id === $_item_id
                            && $_UserPermission->restriction_id === $_ProductPermission->restriction_id) {
                        $_UserPermission->update((object) ['status' => $this->user_permission_status_map[$new_status]]);
                        $_updated_existing_user_permission               = true;
                        $_updated_user_permissions[$_UserPermission->ID] = $_UserPermission;
                    } // Normally just one; but update all matching criteria.
                } // unset($_UserPermission); // Housekeeping.

                # Otherwise, create a new user permission.
                if (!$_updated_existing_user_permission) {
                    $_new_UserPermission = a::addUserPermission($user_id, $_ProductPermission->restriction_id, (object) [
                        'order_id'         => $order_id, 'product_id' => $_product_id, 'item_id' => $_item_id,
                        'access_time'      => $_ProductPermission->accessTime(),
                        'expire_time'      => $_ProductPermission->expireTime(),
                        'expire_directive' => $_ProductPermission->expire_offset_directive,
                        'status'           => $this->user_permission_status_map[$new_status],
                    ]);
                    if (!$_new_UserPermission) { // Catch insertion failures.
                        throw c::issue('Failed to add new user permission.');
                    }
                    $_new_user_permissions[$_new_UserPermission->ID] = $_new_UserPermission; // Record new permission.
                    $_user_permissions[$_new_UserPermission->ID]     = $_new_UserPermission; // Add to existing array also.
                }
            } // unset($_ProductPermission, $_updated_existing_user_permission, $_new_UserPermission);

            c::review(compact(// Log for review.
                'order_id',
                'user_id',
                'old_status',
                'new_status',
                '_item_id',
                '_product_id',
                '_product_type',
                '_product_permissions',
                '_updated_user_permissions',
                '_new_user_permissions'
            ), 'Granting user permissions on order status change.');
        } // unset($_item_id, $_item, $_product_id, $_product_type, $_product_permissions);
        // unset($_user_permissions, $_updated_user_permissions, $_new_user_permissions);
    }

    /**
     * Maybe grant subscription permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param string           $old_status      Old status prior to change.
     * @param string           $new_status      The new status after this change.
     */
    protected function maybeGrantSubscriptionPermissions(\WC_Subscription $WC_Subscription, string $old_status, string $new_status)
    {
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            debug(0, c::issue(vars(), 'Empty subscription ID.'));
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->get_user_id())) {
            return; // Not possible; no user ID.
        }
        foreach ($WC_Subscription->get_items() ?: [] as $_item_id => $_item) {
            $_item_id = (int) $_item_id; // Force integer.

            if (!($_product_id = a::productIdFromItem($_item))) {
                continue; // Not applicable; not associated w/ a product ID.
            } elseif (!($_product_type = $this->itemProductType($_item_id))) {
                debug(0, c::issue(vars(), 'Missing product type.'));
                continue; // Not possible; missing product type.
            } elseif (!($_product_permissions = $this->itemProductPermissions($_item_id))) {
                continue; // Not applicable; no product permissions.
            }
            // Any type of product can be an item in a subscription; it's just like an order.
            // While we don't handle subscription product types when an order status changes, we DO handle
            // any type of product that is in a subscription; i.e., we don't check the product type here.

            // Under normal circumstances subscriptions will only contain subscription products. That's what a subscription is intended for.
            // However, if a subscription is created manually by a site owner, it may contain any line-item products that a site owner added to it.
            // So for instance, if a site owner creates a new subscription manually and adds three line-items, and one of those is a `simple` product,
            // we need to handle that here, even though it wouldn't ordinarily be associated with a subscription; it is if the site owner creates it that way.

            $_user_permissions         = a::userPermissions($user_id); // User permissions.
            $_updated_user_permissions = $_new_user_permissions = []; // Initialize.

            foreach ($_product_permissions as $_ProductPermission) {
                $_updated_existing_user_permission = false; // Initialize.

                # Attempt to update an existing user permission.
                foreach ($_user_permissions as $_UserPermission) {
                    if (!isset($_updated_user_permissions[$_UserPermission->ID])
                            && $_UserPermission->subscription_id === $subscription_id
                            && $_UserPermission->product_id === $_product_id
                            && $_UserPermission->item_id === $_item_id
                            && $_UserPermission->restriction_id === $_ProductPermission->restriction_id) {
                        $_UserPermission->update((object) ['status' => $this->user_permission_status_map[$new_status]]);
                        $_updated_existing_user_permission               = true;
                        $_updated_user_permissions[$_UserPermission->ID] = $_UserPermission;
                    } // Normally just one; but update all matching criteria.
                } // unset($_UserPermission); // Housekeeping.

                # Otherwise, create a new user permission.
                if (!$_updated_existing_user_permission) {
                    $_new_UserPermission = a::addUserPermission($user_id, $_ProductPermission->restriction_id, (object) [
                        'subscription_id'  => $subscription_id, 'product_id' => $_product_id, 'item_id' => $_item_id,
                        'access_time'      => $_ProductPermission->accessTime(),
                        'expire_time'      => $_ProductPermission->expireTime(),
                        'expire_directive' => $_ProductPermission->expire_offset_directive,
                        'status'           => $this->user_permission_status_map[$new_status],
                    ]);
                    if (!$_new_UserPermission) { // Catch insertion failures.
                        throw c::issue('Failed to add new user permission.');
                    }
                    $_new_user_permissions[$_new_UserPermission->ID] = $_new_UserPermission; // Record new permission.
                    $_user_permissions[$_new_UserPermission->ID]     = $_new_UserPermission; // Add to existing array also.
                }
            } // unset($_ProductPermission, $_updated_existing_user_permission, $_new_UserPermission);

            c::review(compact(// Log for review.
                'subscription_id',
                'user_id',
                'old_status',
                'new_status',
                '_item_id',
                '_product_id',
                '_product_type',
                '_product_permissions',
                '_updated_user_permissions',
                '_new_user_permissions'
            ), 'Granting user permissions on subscription status change.');
        } // unset($_item_id, $_item, $_product_id, $_product_type, $_product_permissions);
        // unset($_user_permissions, $_updated_user_permissions, $_new_user_permissions);
    }

    /**
     * Maybe switch subscription permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param array            $new_item        The new item data.
     * @param array            $old_item        The old item data.
     */
    public function onSubscriptionItemSwitched(\WC_Subscription $WC_Subscription, array $new_item, array $old_item)
    {
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            debug(0, c::issue(vars(), 'Empty subscription ID.'));
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->get_user_id())) {
            return; // Not possible; no user ID.
        }
        $new_item_id = (int) ($old_item['switched_subscription_new_item_id'] ?? 0);
        $old_item_id = (int) ($new_item['switched_subscription_item_id'] ?? 0);

        $new_product_id = a::productIdFromItem($new_item); // Product or variation ID.
        $old_product_id = a::productIdFromItem($old_item); // Product or variation ID.

        $new_product_type = $new_item_id ? $this->itemProductType($new_item_id) : '';
        $old_product_type = $old_item_id ? $this->itemProductType($old_item_id) : '';

        $new_product_permissions = $new_item_id ? $this->itemProductPermissions($new_item_id) : [];
        $old_product_permissions = $old_item_id ? $this->itemProductPermissions($old_item_id) : [];

        if (!$new_product_id || !$old_product_id || !$new_item_id || !$old_item_id || !$new_product_type || !$old_product_type) {
            debug(0, c::issue(vars(), 'Missing one or more IDs/types.'));
            return; // Not applicable/possible. This is a case that should be logged for review.
        } elseif (!($new_status = $WC_Subscription->get_status()) || empty($this->user_permission_status_map[$new_status])) {
            debug(0, c::issue(vars(), 'Unexpected (or missing) subscription status.'));
            return; // Not applicable/possible. This is a case that should be logged for review.
        }
        // Any type of product can be an item in a subscription; it's just like an order.
        // While we don't handle subscription product types when an order status changes, we DO handle
        // any type of product that is in a subscription; i.e., we don't check the product type here.

        // Under normal circumstances subscriptions will only contain subscription products. That's what a subscription is intended for.
        // However, if a subscription is created manually by a site owner, it may contain any line-item products that a site owner added to it.
        // So for instance, if a site owner creates a new subscription manually and adds three line-items, and one of those is a `simple` product,
        // we need to handle that here, even though it wouldn't ordinarily be associated with a subscription; it is if the site owner creates it that way.

        $trashed_old_user_permissions = $created_new_user_permissions = []; // Initialize.
        $original_insertion_time      = null; // For access/expire calculations in new permissions.

        foreach ($old_product_permissions as $_OldProductPermission) {
            foreach (a::userPermissions($user_id) as $_UserPermission) {
                if (!isset($trashed_old_user_permissions[$_UserPermission->ID])
                        && $_UserPermission->subscription_id === $subscription_id
                        && $_UserPermission->product_id === $old_product_id
                        && $_UserPermission->item_id === $old_item_id
                        && $_UserPermission->restriction_id === $_OldProductPermission->restriction_id) {
                    $_UserPermission->update((object) ['status' => $this->user_permission_status_map['switched'], 'is_trashed' => 1]);
                    $original_insertion_time                            = $_UserPermission->insertion_time;
                    $trashed_old_user_permissions[$_UserPermission->ID] = $_UserPermission;
                } // Normally just one; but update all matching criteria.
            } // unset($_UserPermission); // Housekeeping.
        } // unset($_OldProductPermission); // Housekeeping.

        foreach ($new_product_permissions as $_NewProductPermission) {
            $_new_UserPermission = a::addUserPermission($user_id, $_NewProductPermission->restriction_id, (object) [
                'subscription_id'  => $subscription_id, 'product_id' => $new_product_id, 'item_id' => $new_item_id,
                'access_time'      => $_NewProductPermission->accessTime($original_insertion_time),
                'expire_time'      => $_NewProductPermission->expireTime($original_insertion_time),
                'expire_directive' => $_NewProductPermission->expire_offset_directive,
                'status'           => $this->user_permission_status_map[$new_status],
            ]);
            if (!$_new_UserPermission) { // Catch insertion failures.
                throw c::issue('Failed to add new user permission.');
            }
            $created_new_user_permissions[$_new_UserPermission->ID] = $_new_UserPermission; // Record new permission.
        } // unset($_NewProductPermission, $_new_UserPermission); // Housekeeping.

        c::review(compact(// Log for review.
            'subscription_id',
            'user_id',
            //
            'new_item_id',
            'new_product_id',
            'new_product_type',
            'new_product_permissions',
            'created_new_user_permissions',
            //
            'old_item_id',
            'old_product_id',
            'old_product_type',
            'old_product_permissions',
            'trashed_old_user_permissions'
        ), 'Switching user permisssions on subscription upgrade/downgrade.');
    }

    /**
     * Maybe revoke order permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param \WC_Abstract_Order $WC_Order   Order instance.
     * @param string             $old_status Old status prior to change.
     * @param string             $new_status The new status after this change.
     */
    protected function maybeRevokeOrderPermissions(\WC_Abstract_Order $WC_Order, string $old_status, string $new_status)
    {
        if (!($order_id = (int) $WC_Order->id)) {
            debug(0, c::issue(vars(), 'Empty order ID.'));
            return; // Not possible; no order ID.
        } elseif (!($user_id = (int) $WC_Order->get_user_id())) {
            return; // Not possible; no user ID.
        }
        $user_permissions         = a::userPermissions($user_id); // User permissions.
        $revoked_user_permissions = []; // Array of permissions that are being revoked here.

        foreach ($user_permissions as $_UserPermission) {
            if (!isset($revoked_user_permissions[$_UserPermission->ID]) && $_UserPermission->order_id === $order_id) {
                if ($_UserPermission->expire_directive !== 'never') { // Anything other than `never` will be altered here.
                    $_UserPermission->update((object) ['status' => $this->user_permission_status_map[$new_status]]);
                    $revoked_user_permissions[$_UserPermission->ID] = $_UserPermission;
                }
            } // Revoke all matching criteria.
        } // unset($_UserPermission); // Housekeeping.

        c::review(compact(// Log for review.
            'order_id',
            'user_id',
            'old_status',
            'new_status',
            'revoked_user_permissions'
        ), 'Revoking user permissions on order status change.');
    }

    /**
     * Maybe revoke subscription permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param string           $old_status      Old status prior to change.
     * @param string           $new_status      The new status after this change.
     */
    protected function maybeRevokeSubscriptionPermissions(\WC_Subscription $WC_Subscription, string $old_status, string $new_status)
    {
        if (!($subscription_id = (int) $WC_Subscription->id)) {
            debug(0, c::issue(vars(), 'Empty subscription ID.'));
            return; // Not possible; no subscription ID.
        } elseif (!($user_id = (int) $WC_Subscription->get_user_id())) {
            return; // Not possible; no user ID.
        }
        $user_permissions         = a::userPermissions($user_id); // User permissions.
        $revoked_user_permissions = []; // Array of permissions that are being revoked here.

        foreach ($user_permissions as $_UserPermission) {
            if (!isset($revoked_user_permissions[$_UserPermission->ID]) && $_UserPermission->subscription_id === $subscription_id) {
                if ($_UserPermission->expire_directive !== 'never' && ($new_status !== 'expired' || $_UserPermission->expire_directive !== 'naturally -expired')) {
                    $_UserPermission->update((object) ['status' => $this->user_permission_status_map[$new_status]]);
                    $revoked_user_permissions[$_UserPermission->ID] = $_UserPermission;
                }
            } // Revoke all matching criteria.
        } // unset($_UserPermission); // Housekeeping.

        c::review(compact(// Log for review.
            'subscription_id',
            'user_id',
            'old_status',
            'new_status',
            'revoked_user_permissions'
        ), 'Revoking user permissions on subscription status change.');
    }

    /**
     * Item product type.
     *
     * @since 16xxxx Order status changes.
     *
     * @param int $item_id Order item ID.
     *
     * @return string Item product type.
     */
    protected function itemProductType(int $item_id): string
    {
        return (string) wc_get_order_item_meta($item_id, $this->product_meta_prefix.'type', true);
    }

    /**
     * Item product permissions.
     *
     * @since 16xxxx Order status changes.
     *
     * @param int $item_id Order item ID.
     *
     * @return Classes\ProductPermission[] Item product permissions.
     */
    protected function itemProductPermissions(int $item_id): array
    {
        $product_permissions  = []; // Initialize.
        $_product_permissions = wc_get_order_item_meta($item_id, $this->product_meta_prefix.'permissions', false);
        $_product_permissions = is_array($_product_permissions) ? $_product_permissions : [];

        foreach ($_product_permissions as $_product_permission) {
            if (!($_product_permission instanceof \StdClass)) {
                debug(0, c::issue(vars(), 'Unexpected product permission.'));
                continue; // Invalid data; not possible.
            }
            $product_permissions[] = $this->App->Di->get(Classes\ProductPermission::class, ['data' => $_product_permission]);
        } // unset($_product_permissions, $_product_permission); // Housekeeping.

        return $product_permissions;
    }
}
