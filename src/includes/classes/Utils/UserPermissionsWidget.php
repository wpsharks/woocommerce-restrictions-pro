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
    public $client_side_prefix;

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
     * Get screen object.
     *
     * @since 16xxxx Restrictions.
     */
    public function onCurrentScreen(\WP_Screen $screen)
    {
        if (!in_array(s::menuPageNow(), ['profile.php', 'user-edit.php'], true)) {
            return; // Not applicable.
        } elseif (!current_user_can('edit_users') || !current_user_can('promote_users')) {
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
        if (!in_array(s::menuPageNow(), ['profile.php', 'user-edit.php'], true)) {
            return; // Not applicable.
        } elseif (!current_user_can('edit_users') || !current_user_can('promote_users')) {
            return; // Not applicable.
        } elseif (empty($GLOBALS['user_id']) || !current_user_can('edit_user', $GLOBALS['user_id'])) {
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
                'i18n' => [
                    'restrictionIdTitle'             => _x('Access', 'user-permissions-widget', 's2member-x'),
                    'restrictionIdStatusIsAllowed'   => _x('Access Granted', 'user-permissions-widget', 's2member-x'),
                    'restrictionIdStatusIsDisabled'  => _x('Temporarily Disabled', 'user-permissions-widget', 's2member-x'),
                    'restrictionIdStatusIsScheduled' => _x('Access Scheduled', 'user-permissions-widget', 's2member-x'),
                    'restrictionIdStatusIsExpired'   => _x('Access Expired', 'user-permissions-widget', 's2member-x'),

                    'idTitle'        => _x('ID', 'user-permissions-widget', 's2member-x'),
                    'userIdTitle'    => _x('User ID', 'user-permissions-widget', 's2member-x'),
                    'orderIdTitle'   => _x('Order ID', 'user-permissions-widget', 's2member-x'),
                    'productIdTitle' => _x('Product ID', 'user-permissions-widget', 's2member-x'),

                    'accessTimeTitle'       => _x('Starts', 'user-permissions-widget', 's2member-x'),
                    'accessDatePlaceholder' => _x('date', 'user-permissions-widget', 's2member-x'),
                    'accessTimePlaceholder' => _x('time', 'user-permissions-widget', 's2member-x'),
                    'emptyAccessDateTime'   => _x('—', 'user-permissions-widget', 's2member-x'),

                    'expireTimeTitle'       => _x('Ends', 'user-permissions-widget', 's2member-x'),
                    'expireTimeViaTitle'    => _x('Ends Via', 'user-permissions-widget', 's2member-x'),
                    'expireTimeViaIdTitle'  => _x('Ends Via ID', 'user-permissions-widget', 's2member-x'),
                    'expireDatePlaceholder' => _x('date', 'user-permissions-widget', 's2member-x'),
                    'expireTimePlaceholder' => _x('time', 'user-permissions-widget', 's2member-x'),
                    'emptyExpireDateTime'   => _x('—', 'user-permissions-widget', 's2member-x'),

                    'isEnabledTitle'    => _x('Enabled?', 'user-permissions-widget', 's2member-x'),
                    'displayOrderTitle' => _x('Display Order', 'user-permissions-widget', 's2member-x'),

                    'insertionTimeTitle'  => _x('Insertion Time', 'user-permissions-widget', 's2member-x'),
                    'lastUpdateTimeTitle' => _x('Last Update Time', 'user-permissions-widget', 's2member-x'),

                    'original'                  => _x('Original', 'user-permissions-widget', 's2member-x'),
                    'noDataContent'             => _x('No permissions yet.', 'user-permissions-widget', 's2member-x'),
                    'restrictionAccessRequired' => _x('Restriction \'Access\' is empty.', 'user-permissions-widget', 's2member-x'),
                    'accessTimeLtExpireTime'    => _x('When both are given, \'Starts\' must come before \'Ends\'.', 'user-permissions-widget', 's2member-x'),
                    'notReadyToSave'            => _x('Not ready to save all changes yet...', 'user-permissions-widget', 's2member-x'),
                    'stillInserting'            => _x('A Permission row is still pending insertion.', 'user-permissions-widget', 's2member-x'),
                    'stillEditing'              => _x('A Permission row is still open for editing.', 'user-permissions-widget', 's2member-x'),
                    'via'                       => _x('via', 'user-permissions-widget', 's2member-x'),
                ],
                'orderViewUrl=' => admin_url('/post.php?action=edit&post='),
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
        if (!current_user_can('edit_users')) {
            return; // Not applicable.
        } elseif (!current_user_can('promote_users')) {
            return; // Not applicable.
        } elseif (!current_user_can('edit_user', $WP_User->ID)) {
            return; // Not applicable.
        }
        $restriction_titles_by_id = a::restrictionTitlesById();
        if (!$restriction_titles_by_id && !current_user_can('create_'.a::restrictionPostType())) {
            return; // Not possible to grant access yet, and they can't create restrictions.
        }
        echo '<hr />'; // After other fields in the user edit page.

        echo '<div id="'.esc_attr($this->client_side_prefix.'-user-permissions-widget').'">';
        echo    '<h3>'.sprintf(__('Customer Permissions (<span class="dashicons dashicons-unlock"></span> %1$s Restriction Access)', 's2member-x'), $this->App->Config->©brand['©acronym']).'</h3>';

        if (!$restriction_titles_by_id) {
            echo '<div class="notice notice-info inline">';
            echo    '<p>'.sprintf(__('It\'s not possible to grant access yet, because no Restrictions have been configured. To create your first Restriction, <a href="%1$s">click here</a>.', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>';
            echo '</div>';
        } else {
            echo    '<p style="font-style:italic;">'.__('<strong>Note:</strong> Start and End dates are optional. No Start Date = starts immediately. If no End Date, access is indefinite. Unchecking the \'Enabled\' box will temporarily suspend access.', 's2member-x').'</p>';

            echo    '<input class="-user-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode(array_values(a::userPermissions($WP_User->ID)))).'" />';
            echo    '<input class="-restriction-titles-by-id" type="hidden" value="'.esc_attr(json_encode(a::restrictionTitlesById())).'" />';

            echo    '<div class="-grid" data-toggle="jquery-jsgrid"></div>';
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
        $user_id = (int) $user_id; // Force integer.

        if (!current_user_can('edit_users')) {
            return; // Not applicable.
        } elseif (!current_user_can('promote_users')) {
            return; // Not applicable.
        } elseif (!current_user_can('edit_user', $user_id)) {
            return; // Not applicable.
        } elseif (!isset($_REQUEST[$this->client_side_prefix.'_permissions'])) {
            return; // Not applicable.
        }
        // Initialize old/new permission arrays.

        $old_permissions = a::userPermissions($user_id);
        $new_permissions = []; // Initialize.

        // Collect and build the array of new permissions.

        $_r_permissions = stripslashes((string) $_REQUEST[$this->client_side_prefix.'_permissions']);
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

        foreach ($old_permissions as $_UserPermission) {
            if (!isset($new_permissions[$_UserPermission->ID])) {
                $_UserPermission->delete();
            }
        } // unset($_UserPermission); // Housekeeping.

        foreach ($new_permissions as $_UserPermission) {
            $_UserPermission->update(); // Updates existing or inserts/saves new one.
        } // unset($_UserPermission); // Housekeeping.
    }
}
