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
        s::enqueueJQueryJsGridLibs();

        wp_enqueue_style($this->client_side_prefix.'-user-permissions-widget', c::appUrl('/client-s/css/admin/user-permissions-widget.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-user-permissions-widget', c::appUrl('/client-s/js/admin/user-permissions-widget.min.js'), ['jquery', 'jquery-jsgrid'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-user-permissions-widget',
            $this->client_side_prefix.'UserPermissionsWidgetData',
            [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'i18n' => [
                    'restrictionIdTitle' => _x('Access', 'user-permissions-widget', 's2member-x'),

                    'accessTimeTitle'       => _x('Starts', 'user-permissions-widget', 's2member-x'),
                    'accessDatePlaceholder' => _x('date', 'user-permissions-widget', 's2member-x'),
                    'accessTimePlaceholder' => _x('time', 'user-permissions-widget', 's2member-x'),
                    'emptyAccessDateTime'   => _x('—', 'user-permissions-widget', 's2member-x'),

                    'expireTimeTitle'       => _x('Ends', 'user-permissions-widget', 's2member-x'),
                    'expireDatePlaceholder' => _x('date', 'user-permissions-widget', 's2member-x'),
                    'expireTimePlaceholder' => _x('time', 'user-permissions-widget', 's2member-x'),
                    'emptyExpireDateTime'   => _x('—', 'user-permissions-widget', 's2member-x'),

                    'isEnabledTitle'            => _x('Enabled', 'user-permissions-widget', 's2member-x'),
                    'noDataContent'             => _x('No permissions yet.', 'user-permissions-widget', 's2member-x'),
                    'restrictionAccessRequired' => _x('Restriction "Access" is a required field.', 'user-permissions-widget', 's2member-x'),
                ],
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
        echo '<hr />'; // After password-related fields on user edit page.

        echo '<div id="'.esc_attr($this->client_side_prefix.'-user-permissions-widget').'">';
        echo    '<h3>'.sprintf(__('Customer Permissions (<span class="dashicons dashicons-unlock"></span> %1$s Restriction Access)', 's2member-x'), $this->App->Config->©brand['©acronym']).'</h3>';

        if (!$restriction_titles_by_id) {
            echo '<div class="notice notice-info inline">';
            echo    '<p>'.sprintf(__('It\'s not possible to grant access yet, because no Restrictions have been configured. To create your first Restriction, <a href="%1$s">click here</a>.', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>';
            echo '</div>';
        } else {
            echo    '<p>'.__('<strong>Note:</strong> Start and End dates are optional. If there is no Start Date, it starts immediately. If no End Date, access is indefinite. Unchecking the Enabled box temporarily suspends access.', 's2member-x').'</p>';

            echo    '<input class="-user-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode(a::userPermissions($WP_User->ID))).'" />';
            echo    '<input class="-restriction-titles-by-id" type="hidden" value="'.esc_attr(json_encode(a::restrictionTitlesById())).'" />';

            echo    '<div class="-grid" data-toggle="jquery-jsgrid"></div>';
        }
        echo '</div>';

        echo '<hr />'; // Before customer information by WooCommerce; e.g., billing address, shipping address, etc.
    }
}
