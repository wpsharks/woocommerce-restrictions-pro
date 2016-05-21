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
    const VERSION = '160521'; //v//

    /**
     * Constructor.
     *
     * @since 16xxxx Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $is_multisite = is_multisite();
        $is_main_site = !$is_multisite || is_main_site();

        $instance_base = [
            '©di' => [
                '©default_rule' => [
                    'new_instances' => [
                        ProductPermission::class,
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
                'security_gate_redirect_to_args_enable',
                'orders_always_grant_immediate_access',
            ],
            '§default_options' => [
                'if_shortcode_expr_enable'              => $is_multisite && !$is_main_site ? '0' : '1',
                'if_shortcode_for_blog_enable'          => $is_multisite && !$is_main_site ? '0' : '1',
                'security_gate_redirects_to_post_id'    => '0', // Post ID.
                'security_gate_redirect_to_args_enable' => '1',
                'orders_always_grant_immediate_access'  => '0',
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
                        'markup'       => '<p>'.sprintf(__('<strong>%1$s</strong> v%2$s installed successfully.', 's2member-x'), esc_html($this->Config->©brand['©name']), esc_html($this->c::version())).'<br />'.
                            sprintf(__('~ Start by protecting some of your content: <a href="%1$s" class="button" style="text-decoration:none;">Create Restriction</a>', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>',
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
                add_action('save_post_'.$this->Utils->Restriction->post_type, [$this->Utils->Restriction, 'onSaveRestriction']);

                add_action('admin_enqueue_scripts', [$this->Utils->Restriction, 'onAdminEnqueueScripts']);
            }
            # User-related hooks; including role/capability filters.

            add_action('delete_user', [$this->Utils->UserPermissions, 'onDeleteUser']);
            add_action('wpmu_delete_user', [$this->Utils->UserPermissions, 'onDeleteNetworkUser']);
            add_action('remove_user_from_blog', [$this->Utils->UserPermissions, 'onRemoveUserFromBlog']);

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
            # Checkout-related hooks; including checkout-specific option filters.

            add_action('woocommerce_before_checkout_form', [$this->Utils->Checkout, 'onBeforeCheckoutForm'], -1000);

            add_filter('woocommerce_checkout_fields', [$this->Utils->Checkout, 'onCheckoutFields'], 1000);
            add_filter('woocommerce_params', [$this->Utils->Checkout, 'onCheckoutParams']); // JS params.
            add_filter('wc_checkout_params', [$this->Utils->Checkout, 'onCheckoutParams']); // JS params.

            add_action('woocommerce_after_checkout_form', [$this->Utils->Checkout, 'onAfterCheckoutForm'], -1000);

            add_action('woocommerce_before_checkout_process', [$this->Utils->Checkout, 'onBeforeCheckoutProcess']);

            # Order-related hooks; attached to WooCommerce events.

            add_action('woocommerce_add_order_item_meta', [$this->Utils->OrderMeta, 'onAddOrderItemMeta']);
            add_action('woocommerce_saved_order_items', [$this->Utils->OrderMeta, 'onSavedOrderItems'], 10, 2);

            add_action('woocommerce_order_status_changed', [$this->Utils->OrderStatus, 'onOrderStatusChanged'], 1000, 3);
            add_action('woocommerce_subscription_status_changed', [$this->Utils->OrderStatus, 'onSubscriptionStatusChanged'], 1000, 3);
            add_action('woocommerce_subscriptions_switched_item', [$this->Utils->OrderStatus, 'onSubscriptionItemSwitched'], 1000, 3);

            add_action('update_post_meta', [$this->Utils->OrderMeta, 'onPostMetaUpdate'], 10, 4);

            add_filter('woocommerce_hidden_order_itemmeta', [$this->Utils->OrderMeta, 'onHiddenOrderItemMeta']);

            # Product-data and other product-related WooCommerce events.

            if ($is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->Product, 'onCurrentScreen']);

                add_action('admin_enqueue_scripts', [$this->Utils->Product, 'onAdminEnqueueScripts']);

                add_action('woocommerce_product_options_general_product_data', [$this->Utils->Product, 'onGeneralProductData']);
                add_action('woocommerce_product_after_variable_attributes', [$this->Utils->Product, 'onAfterVariableAttributes'], 10, 3);

                add_action('save_post_'.$this->Utils->Product->post_type, [$this->Utils->Product, 'onSaveProduct']);
                add_action('woocommerce_save_product_variation', [$this->Utils->Product, 'onSaveProductVariation'], 10, 2);
            }
            # Security gate; always after the `restriction` post type registration.

            // See also: <http://jas.xyz/1WlT51u> to review the BuddyPress loading order.
            // BuddyPress runs its setup on `plugins_loaded` at the latest, so this comes after BP.

            // See also: <https://github.com/wp-plugins/bbpress/blob/master/bbpress.php#L969>
            // bbPress runs its setup on `plugins_loaded` at the latest, so this comes after BBP.

            $this->Utils->SecurityGate->onInitGuardRestrictions();
        });
    }
}
