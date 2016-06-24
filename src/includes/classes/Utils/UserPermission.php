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
 * User permission utilities.
 *
 * @since 160524 Security gate.
 */
class UserPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Permission statuses.
     *
     * @since 160524 User permissions.
     *
     * @type array Permission statuses.
     */
    protected $statuses;

    /**
     * Class constructor.
     *
     * @since 160524 Security gate.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->statuses = s::applyFilters('user_permission_statuses', [
            '--- '.__('Enabled', 'woocommerce-s2member-x').' ---' => '---',
            'enabled' => __('Enabled', 'woocommerce-s2member-x'),

            '--- '.__('Disabled', 'woocommerce-s2member-x').' ---' => '---',
            'pending'    => __('Pending', 'woocommerce-s2member-x'),
            'processing' => __('Processing', 'woocommerce-s2member-x'),
            'on-hold'    => __('On-Hold', 'woocommerce-s2member-x'),
            'expired'    => __('Expired', 'woocommerce-s2member-x'),
            'refunded'   => __('Refunded', 'woocommerce-s2member-x'),
            'cancelled'  => __('Cancelled', 'woocommerce-s2member-x'),
            'failed'     => __('Payment(s) Failed', 'woocommerce-s2member-x'),
            'switched'   => __('Subscription Switched', 'woocommerce-s2member-x'),

            // This one does not apply. `trashed` has it's own flag.
            // Instead of a `trashed` status, we have the `is_trashed` property.
            // This allows `status` to be preserved if a permission is restored later.
            // 'trashed' => __('Trashed', 'woocommerce-s2member-x'),
        ]);
    }

    /**
     * Permission statuses.
     *
     * @since 160524 Security gate.
     *
     * @param bool $include_optgroups Include optgroups?
     *
     * @return array An array of user permission statuses.
     */
    public function statuses(bool $include_optgroups = false): array
    {
        $statuses = $this->statuses; // Copy of the array.

        if (!$include_optgroups) {
            foreach ($statuses as $_key => $_title) {
                if ($_title === '---') {
                    unset($statuses[$_key]);
                } // Removes `---` optgroups.
            } // unset($_key, $_title); // Housekeeping.
        }
        return $statuses;
    }

    /**
     * Add a new user permission.
     *
     * @since 160524 Security gate.
     *
     * @param string|int      $user_id     User ID.
     * @param string|int      $restriction Slug or ID.
     * @param array|\StdClass $data        Permission data.
     *
     * @return Classes\UserPermission|null New user permission or `null` on failure.
     */
    public function add($user_id, $restriction, $data = null)
    {
        if (!($user_id = (int) $user_id)) {
            return null; // Not possible.
        }
        if ($restriction && is_string($restriction)) {
            if (!($restriction_id = a::restrictionSlugToId($restriction))) {
                return null; // Not possible.
            }
        } elseif (!($restriction_id = (int) $restriction)) {
            return null; // Not possible.
        }
        $data = (object) ($data ?? new \StdClass());

        $data->user_id        = $user_id;
        $data->restriction_id = $restriction_id;
        $data->status         = $data->status ?? 'enabled';
        $data->display_order  = $data->display_order ?? count(a::userPermissions($user_id));

        $UserPermission = $this->App->Di->get(Classes\UserPermission::class, ['data' => $data]);
        $UserPermission->update(); // Save/update the new permission.

        return $UserPermission;
    }

    /**
     * Remove user permission(s).
     *
     * @since 160524 Security gate.
     *
     * @param string|int      $user_id       User ID.
     * @param string|int|null $restriction   Slug or ID (optional).
     * @param int|null        $permission_id A specific permission ID (optional).
     *
     * @note If a `$permission_id` is passed, it is used instead of `$restriction_id`.
     *  CAUTION: If neither are passed, all permission are removed.
     *
     * @return int Total permission deletions.
     */
    public function remove($user_id, $restriction = null, int $permission_id = null): int
    {
        if (!($user_id = (int) $user_id)) {
            return 0; // Not possible.
        }
        if (isset($restriction) && is_string($restriction)) {
            $restriction_id = a::restrictionSlugToId($restriction);
        } elseif (isset($restriction)) {
            $restriction_id = (int) $restriction;
        }
        $total_deletions = 0; // Initialize deletion counter.

        foreach (a::userPermissions($user_id) as $_UserPermission) {
            if (isset($permission_id)) {
                if ($permission_id === $_UserPermission->ID) {
                    $_UserPermission->delete();
                    ++$total_deletions;
                }
            } elseif (isset($restriction_id)) {
                if ($restriction_id === $_UserPermission->restriction_id) {
                    $_UserPermission->delete();
                    ++$total_deletions;
                }
            } else { // Remove all in this case.
                $_UserPermission->delete();
                ++$total_deletions;
            }
        } // unset($_UserPermission);

        return $total_deletions;
    }
}
