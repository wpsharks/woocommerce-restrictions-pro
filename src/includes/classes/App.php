<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\s2MemberX\Pro\Classes;

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
 * App.
 *
 * @since 16xxxx Initial release.
 */
class App extends SCoreClasses\App
{
    /**
     * Version.
     *
     * @since 16xxxx
     *
     * @type string Version.
     */
    const VERSION = '160512'; //v//

    /**
     * Constructor.
     *
     * @since 16xxxx Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $instance_base = [
            '©di' => [
                '©default_rule' => [
                    'new_instances' => [
                        UserPermission::class,
                    ],
                ],
            ],
            '©brand' => [
                '©name'        => 's2Member X',
                '©text_domain' => 's2member-x',
                '©slug'        => 's2member-x',
                '©var'         => 's2member_x',
                '©name'        => 's2Member X',
                '©acronym'     => 's2',
                '©prefix'      => 's2x',

                '§domain'      => 'wpsharks.com',
                '§domain_path' => '/product/s2member-x',
            ],
            '§pro_option_keys' => [
                'if_shortcode_expr_enable',
                'if_shortcode_for_blog_enable',
                'restriction_categories_enable',
                'security_gate_redirect_to_args_enable',
                'orders_always_grant_immediate_access',
            ],
            '§default_options' => [
                'if_shortcode_expr_enable'              => '1',
                'if_shortcode_for_blog_enable'          => '0',
                'restriction_categories_enable'         => '0',
                'security_gate_redirects_to_post_id'    => '0',
                'security_gate_redirect_to_args_enable' => '1',
                'orders_always_grant_immediate_access'  => '1',
            ],
            '§conflicts' => [
                '§plugins' => [
                    'woocommerce-membership' => 'WooCommerce Membership',
                ], // <https://www.woothemes.com/products/woocommerce-memberships/>
            ],
            '§dependencies' => [
                '§plugins' => [
                    'woocommerce' => [
                        'in_wp'       => true,
                        'name'        => 'WooCommerce',
                        'url'         => 'https://wordpress.org/plugins/woocommerce/',
                        'archive_url' => 'https://wordpress.org/plugins/woocommerce/developers/',
                    ],
                ],
                '§others' => [
                    'fancy_permalinks' => [
                        'name'        => __('Fancy Permalinks', 's2member-x'),
                        'description' => __('a Permalink Structure other than <em>plain</em>', 's2member-x'),

                        'test' => function (string $key) {
                            if (get_option('permalink_structure')) {
                                return true; // No problem.
                            } else {
                                return [
                                    'how_to_resolve' => sprintf(__('<a href="%1$s">change your Permalink settings</a> to anything but <em>plain</em>', 's2member-x'), esc_url(admin_url('/options-permalink.php'))),
                                    'cap_to_resolve' => 'manage_options',
                                ];
                            }
                        },
                    ],
                ],
            ],
            '§notices' => [
                '§on_install' => function (array $installion_history) {
                    return [
                        'is_transient' => true,
                        'markup'       => '<p>'.sprintf(__('<strong>%1$s</strong> v%2$s installed successfully. You can get started by creating a \'Restriction\'.', 's2member-x'), esc_html($this->Config->©brand['©name']), esc_html($this->c::version())).'</p>'.
                            '<p>'.sprintf(__('<a href="%1$s" class="button" style="text-decoration:none;">Create Restriction</a>', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>',
                    ];
                },
            ],
        ];
        parent::__construct($instance_base, $instance);
    }

    /**
     * Early hook setup handler.
     *
     * @since 16xxxx Initial release.
     */
    protected function onSetupEarlyHooks()
    {
        parent::onSetupEarlyHooks(); // Core hooks.

        s::addAction('other_install_routines', [$this->Utils->Installer, 'onOtherInstallRoutines']);
        s::addAction('other_uninstall_routines', [$this->Utils->Uninstaller, 'onOtherUninstallRoutines']);
    }

    /**
     * Other hook setup handler.
     *
     * @since 16xxxx Initial release.
     */
    protected function onSetupOtherHooks()
    {
        parent::onSetupOtherHooks(); // Core hooks.

        add_action('woocommerce_init', function () {
            # Misc. variables.

            $is_admin = is_admin();
            $is_multisite = is_multisite();

            # Restriction-related hooks.

            $this->Utils->Restriction->onInitRegisterPostType();

            if ($is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->Restriction, 'onCurrentScreen']);

                add_filter('custom_menu_order', '__return_true'); // Enable custom order.
                add_filter('menu_order', [$this->Utils->Restriction, 'onMenuOrder'], 1000);

                add_action('add_meta_boxes', [$this->Utils->Restriction, 'onAddMetaBoxes']);
                add_filter('default_hidden_meta_boxes', [$this->Utils->Restriction, 'onDefaultHiddenMetaBoxes'], 10, 2);
                add_action('save_post_'.$this->Utils->Restriction->post_type, [$this->Utils->Restriction, 'onSavePost'], 10, 3);

                add_action('admin_enqueue_scripts', [$this->Utils->Restriction, 'onAdminEnqueueScripts']);
            }
            # User-related hooks; including role/capability filters.

            add_action('delete_user', [$this->Utils->UserPermissions, 'onDeleteUser']);
            add_action('wpmu_delete_user', [$this->Utils->UserPermissions, 'onDeleteNetworkUser']);

            add_action('trashed_post', [$this->Utils->UserPermissions, 'onTrashedPost']);
            add_action('untrashed_post', [$this->Utils->UserPermissions, 'onUntrashedPost']);
            add_action('before_delete_post', [$this->Utils->UserPermissions, 'onBeforeDeletePost']);

            add_filter('user_has_cap', [$this->Utils->UserPermissions, 'onUserHasCap'], 1000, 4);
            add_action('clean_user_cache', [$this->Utils->UserPermissions, 'onCleanUserCache']);

            for ($_i = 0, $if_shortcode_name = a::ifShortcodeName(), $if_shortcode_names = []; $_i < 5; ++$_i) {
                add_shortcode($if_shortcode_names[] = str_repeat('_', $_i).$if_shortcode_name, [$this->Utils->UserPermissionShortcodes, 'onIf']);
            } // unset($_i); // Housekeeping.

            add_filter('widget_text', 'do_shortcode'); // Enable shortcodes in widgets.

            add_filter('no_texturize_shortcodes', function (array $shortcodes) use ($if_shortcode_names) {
                return array_merge($shortcodes, $if_shortcode_names);
            }); // See: <http://jas.xyz/24AusB7> for more about this filter.

            if ($is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->UserPermissionsWidget, 'onCurrentScreen']);

                add_action('admin_enqueue_scripts', [$this->Utils->UserPermissionsWidget, 'onAdminEnqueueScripts']);

                add_action('show_user_profile', [$this->Utils->UserPermissionsWidget, 'onEditUserProfile'], 1000);
                add_action('edit_user_profile', [$this->Utils->UserPermissionsWidget, 'onEditUserProfile'], 1000);

                add_action('personal_options_update', [$this->Utils->UserPermissionsWidget, 'onEditUserProfileUpdate']);
                add_action('edit_user_profile_update', [$this->Utils->UserPermissionsWidget, 'onEditUserProfileUpdate']);
            }
            # Order-related hooks; attached to WooCommerce events.

            add_action('woocommerce_order_status_changed', [$this->Utils->UserWcPermissions, 'onOrderStatusChanged'], 1000, 3);
            add_action('woocommerce_subscription_status_changed', [$this->Utils->UserWcPermissions, 'onSubscriptionStatusChanged'], 1000, 3);

            # Security gate; always after the `restriction` post type registration.

            // See also: <http://jas.xyz/1WlT51u> to review the BuddyPress loading order.
            // BuddyPress runs its setup on `plugins_loaded` at the latest, so this comes after BP.

            // See also: <https://github.com/wp-plugins/bbpress/blob/master/bbpress.php#L969>
            // bbPress runs its setup on `plugins_loaded` at the latest, so this comes after BBP.

            $this->Utils->SecurityGate->onInitGuardRestrictions();
        });
    }
}
