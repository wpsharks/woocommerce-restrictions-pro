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
 * User permissions widget.
 *
 * @since 16xxxx Security gate.
 */
class UserPermissionsWidget extends SCoreClasses\SCore\Base\Core
{
    /**
     * Client-side prefix.
     *
     * @since 16xxxx
     *
     * @type string Client-side prefix.
     */
    protected $client_side_prefix;

    /**
     * Screen.
     *
     * @since 16xxxx
     *
     * @type \WP_Screen|null Screen.
     */
    protected $screen;

    /**
     * Is screen mobile?
     *
     * @since 16xxxx
     *
     * @type bool Is screen mobile?
     */
    protected $screen_is_mobile;

    /**
     * Class constructor.
     *
     * @since 16xxxx Security gate.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->client_side_prefix = 'accrhcdehngpugpudhpcwtykbfdykarp';
    }

    /**
     * Is a profile edit page?
     *
     * @since 16xxxx Restrictions.
     *
     * @return bool True if is a profile edit page.
     */
    protected function isProfileEditPage(int $user_id = null): bool
    {
        return in_array(s::menuPageNow(), ['profile.php', 'user-edit.php'], true);
    }

    /**
     * Current user can edit permissions?
     *
     * @since 16xxxx Restrictions.
     *
     * @param int|null $user_id User ID that is being edited.
     *
     * @return bool True if the current user can.
     */
    protected function currentUserCan(int $user_id = null): bool
    {
        return current_user_can('edit_users') && current_user_can('promote_users')
            && (!$user_id || current_user_can('edit_user', $user_id));
    }

    /**
     * Get screen object.
     *
     * @since 16xxxx Restrictions.
     */
    public function onCurrentScreen(\WP_Screen $screen)
    {
        if (!$this->isProfileEditPage()) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        $this->screen           = $screen;
        $this->screen_is_mobile = wp_is_mobile();
    }

    /**
     * Enqueue styles/scripts.
     *
     * @since 16xxxx Restrictions.
     */
    public function onAdminEnqueueScripts()
    {
        if (!$this->isProfileEditPage()) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        s::enqueueMomentLibs();
        s::enqueueJQueryJsGridLibs();

        wp_enqueue_style($this->client_side_prefix.'-user-permissions-widget', c::appUrl('/client-s/css/admin/user-permissions-widget.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-user-permissions-widget', c::appUrl('/client-s/js/admin/user-permissions-widget.min.js'), ['jquery', 'jquery-jsgrid', 'jquery-ui-tooltip', 'jquery-ui-sortable', 'underscore', 'moment'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-user-permissions-widget',
            $this->client_side_prefix.'UserPermissionsWidgetData',
            [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'current_user' => [
                    'can_edit_shop_orders'        => current_user_can('edit_shop_orders'),
                    'can_edit_shop_subscriptions' => current_user_can('edit_shop_orders'),
                ],
                'i18n' => [
                    'idTitle'     => __('ID', 's2member-x'),
                    'userIdTitle' => __('User ID', 's2member-x'),

                    'orderIdTitle'        => __('Order ID', 's2member-x'),
                    'subscriptionIdTitle' => __('Subscription ID', 's2member-x'),
                    'productIdTitle'      => __('Product ID', 's2member-x'),
                    'itemIdTitle'         => __('Item ID', 's2member-x'),

                    'restrictionIdTitle'        => __('Access', 's2member-x'),
                    'restrictionIdTitleTip'     => __('Access to one or more configured Restrictions.<hr />It\'s OK for Permissions granted manually and/or via Orders &amp; Subscriptions to overlap with each other. This is because any Permission that currently grants access, does. Access is denied only if no Permission grants access.', 's2member-x'),
                    'restrictionAccessRequired' => __('\'Access\' selection is empty.', 's2member-x'),

                    'accessTimeTitle'    => __('Starts', 's2member-x'),
                    'accessTimeTitleTip' => __('If left empty, access starts immediately (no delay).', 's2member-x'),

                    'accessDatePlaceholder'  => __('date', 's2member-x'),
                    'accessTimePlaceholder'  => __('time', 's2member-x'),
                    'emptyAccessDateTime'    => __('immediately', 's2member-x'),
                    'accessTimeLtExpireTime' => __('When both are given, \'Starts\' must come before \'Ends\'.', 's2member-x'),

                    'expireTimeTitle'    => __('Ends', 's2member-x'),
                    'expireTimeTitleTip' => __('If left empty, access is indefinite (ongoing).<hr />If the Permission was acquired via an Order or Subscription, the End is controlled by your original Product configuration, which will be indicated below.<hr />You can always choose to set a specific End date here, which overrides the original Product configuration.', 's2member-x'),

                    'expireDatePlaceholder' => __('date', 's2member-x'),
                    'expireTimePlaceholder' => __('time', 's2member-x'),
                    'emptyExpireDateTime'   => __('— n/a —', 's2member-x'),
                    'expireDirectiveTitle'  => __('Expires', 's2member-x'),

                    'statusTitle'    => __('Status', 's2member-x'),
                    'statusTitleTip' => __('Current permission status.<hr />Anything other than \'Enabled\' is collectively referred to as Disabled.<hr />Disabled status variations simply help to convey why access is currently disabled.', 's2member-x'),

                    'isTrashedTitle'            => __('Trashed?', 's2member-x'),
                    'isTrashedStatus'           => __('Trashed', 's2member-x'),
                    'restrictionStatusRequired' => __('\'Status\' selection is empty.', 's2member-x'),

                    'statusIsDisabled'  => __('Access Disabled', 's2member-x'),
                    'statusIsScheduled' => __('Access Scheduled', 's2member-x'),
                    'statusIsExpired'   => __('Access Expired', 's2member-x'),

                    'displayOrderTitle' => __('Display Order', 's2member-x'),

                    'insertionTimeTitle'  => __('Insertion Time', 's2member-x'),
                    'lastUpdateTimeTitle' => __('Last Update Time', 's2member-x'),

                    'via'            => __('via', 's2member-x'),
                    'noDataContent'  => __('No permissions yet.', 's2member-x'),
                    'notReadyToSave' => __('Not ready to save all changes yet...', 's2member-x'),
                    'stillInserting' => __('A Customer Permission row is still pending insertion. Please click the green \'+\' icon to complete insertion. Or, empty the \'Access\' select menu in the green insertion row.', 's2member-x'),
                    'stillEditing'   => __('A Customer Permission row (in yellow) is still open for editing. Please save your changes there first, or click the \'x\' icon to cancel editing in the open row.', 's2member-x'),
                ],
                'restrictionTitlesById'                   => a::restrictionTitlesById(),
                'userPermissionStatuses'                  => a::userPermissionStatuses(true),
                'productPermissionExpireOffsetDirectives' => a::productPermissionExpireOffsetDirectives(),

                'orderViewUrl='        => admin_url('/post.php?action=edit&post='),
                'subscriptionViewUrl=' => admin_url('/post.php?action=edit&post='),
            ]
        );
    }

    /**
     * In user edit panel.
     *
     * @since 16xxxx Security gate.
     *
     * @param \WP_User $WP_User User object class.
     */
    public function onEditUserProfile(\WP_User $WP_User)
    {
        if (!($user_id = (int) $WP_User->ID)) {
            return; // Not possible.
        } elseif (!$this->isProfileEditPage()) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan($user_id)) {
            return; // Not applicable.
        }
        $restriction_titles_by_id = a::restrictionTitlesById();
        if (!$restriction_titles_by_id && !current_user_can('create_'.a::restrictionPostType())) {
            return; // Not possible to grant access yet, and they can't create restrictions.
        }
        echo '<hr />'; // After other fields in the user edit page.

        echo '<div id="'.esc_attr($this->client_side_prefix.'-user-permissions-widget').'">';
        echo    '<h3>'.__('Customer Permissions (<span class="dashicons dashicons-unlock"></span> Restriction Access)', 's2member-x').'</h3>';

        if (!$restriction_titles_by_id) {
            echo '<div class="notice notice-info inline">';
            echo    '<p>'.sprintf(__('It\'s not possible to grant access yet, because no Restrictions have been configured. To create your first Restriction, <a href="%1$s">click here</a>.', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>';
            echo '</div>';
        } else {
            $user_permissions = array_values(a::userPermissions($user_id, false)); // Exclude `trashed` status.
            echo '<input class="-user-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode($user_permissions)).'" />';
            echo '<div class="-grid" data-toggle="jquery-jsgrid"></div>';
        }
        echo '</div>';
    }

    /**
     * On update of the user.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id User ID.
     */
    public function onEditUserProfileUpdate($user_id)
    {
        if (!($user_id = (int) $user_id)) {
            return; // Not possible.
        } elseif (!$this->isProfileEditPage()) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan($user_id)) {
            return; // Not applicable.
        } elseif (!isset($_REQUEST[$this->client_side_prefix.'_permissions'])) {
            return; // Not applicable.
        }
        // Initialize old/new permission arrays.
        // Note that `$old_permissions` & `$new_permissions` both exclude trash.

        $old_permissions = a::userPermissions($user_id, false); // Exclude `trashed` status.
        $new_permissions = []; // Excludiung `trashed` status here also, because it's not an available option.

        // Collect and build the array of new permissions.

        $_r_permissions = c::unslash((string) $_REQUEST[$this->client_side_prefix.'_permissions']);
        if (!is_array($_r_permissions = json_decode($_r_permissions))) {
            return; // Corrupt form submission. Do not save.
        }
        foreach ($_r_permissions as $_key => $_r_permission) {
            if (!($_r_permission instanceof \StdClass)) {
                return; // Corrupt form submission.
            } elseif (empty($_r_permission->restriction_id)) {
                return; // Corrupt form submission.
            } // ↑ Should not happen, but better safe than sorry.
            $_r_permission->user_id              = $user_id; // Force association.
            $_r_permission_key                   = !empty($_r_permission->ID) ? (int) $_r_permission->ID : $_key.'_new';
            $new_permissions[$_r_permission_key] = $this->App->Di->get(Classes\UserPermission::class, ['data' => $_r_permission]);
        } // unset($_key, $_r_permission, $_r_permission_key); // Houskeeping.

        // Delete old permissions that do not appear in the new permissions array.
        // Note that `trashed` permissions are not deleted here, because they have been excluded above.

        foreach ($old_permissions as $_UserPermission) {
            if (!isset($new_permissions[$_UserPermission->ID])) {
                $_UserPermission->delete(); // Delete old permission.
            }
        } // unset($_UserPermission); // Housekeeping.

        // Update|insert all permissions in the new array; i.e., all being saved via the widget.
        // Any that do not have a valid `ID` (e.g., no `ID` property, or the `ID` no longer exists,
        // will be recreated as new user permissions. See also: {@link UserPermission::update()}.

        foreach ($new_permissions as $_UserPermission) {
            $_UserPermission->update(); // Updates existing or inserts/saves new one.
        } // unset($_UserPermission); // Housekeeping.
    }
}
