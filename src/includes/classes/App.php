<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes;

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
 * App.
 *
 * @since 160524 Initial release.
 */
class App extends SCoreClasses\App
{
    /**
     * Version.
     *
     * @since 160524
     *
     * @type string Version.
     */
    const VERSION = '160707.3144'; //v//

    /**
     * Constructor.
     *
     * @since 160524 Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $instance_base = [
            '©debug' => [
                '©log_callback' => function (...$args) {
                    $this->Utils->Debugging->onLogEvent(...$args);
                },
            ],

            '©di' => [
                '©default_rule' => [
                    'new_instances' => [
                        ProductPermission::class,
                        UserPermission::class,
                    ],
                ],
            ],

            '§specs' => [
                '§type' => 'plugin',
                '§file' => dirname(__FILE__, 4).'/plugin.php',
            ],
            '©brand' => [
                '©acronym' => 'WC S2X',
                '©name'    => 'WooCommerce Restrictions — s2Member X',

                '©slug' => 'woocommerce-s2member-x',
                '©var'  => 'woocommerce_s2member_x',

                '©short_slug' => 'wc-s2x',
                '©short_var'  => 'wc_s2x',

                '©text_domain' => 'woocommerce-s2member-x',
            ],

            '§pro_option_keys' => [
                'security_gate_redirect_to_args_enable',
                'orders_always_grant_immediate_access',
            ],
            '§default_options' => [
                'security_gate_redirects_to_post_id'    => '0',
                'security_gate_redirect_to_args_enable' => '1',
                'orders_always_grant_immediate_access'  => '0',
            ],

            '§conflicts' => [
                '§plugins' => [
                    's2member'               => 's2Member Framework (Old)',
                    's2member-pro'           => 's2Member Pro Add-On (Old)',
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
                        'test'        => function (string $slug) {
                            $min_version = '2.5.5'; // Update when necessary.
                            if (version_compare(WC_VERSION, $min_version, '<')) {
                                return [
                                    'min_version' => $min_version,
                                    'reason'      => 'needs-upgrade',
                                ];
                            }
                        },
                    ],
                ],
                '§others' => [
                    'fancy_permalinks' => [
                        'name'        => __('Fancy Permalinks', 'woocommerce-s2member-x'),
                        'description' => __('a Permalink Structure other than <em>plain</em>', 'woocommerce-s2member-x'),

                        'test' => function (string $key) {
                            if (!get_option('permalink_structure')) {
                                return [
                                    'how_to_resolve' => sprintf(__('<a href="%1$s">change your Permalink settings</a> to anything but <em>plain</em>', 'woocommerce-s2member-x'), esc_url(admin_url('/options-permalink.php'))),
                                    'cap_to_resolve' => 'manage_options',
                                ];
                            }
                        },
                    ],
                ],
            ],
        ];
        parent::__construct($instance_base, $instance);
    }

    /**
     * Early hook setup handler.
     *
     * @since 160524 Initial release.
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
     * @since 160524 Initial release.
     */
    protected function onSetupOtherHooks()
    {
        parent::onSetupOtherHooks(); // Core hooks.

        add_action('init', function () {
            $this->Utils->Restriction->onInitRegisterPostType();
        }, 6); // Right after other WooCommerce post types.

        add_action('init', function () {
            # Restriction-related hooks.

            if ($this->Wp->is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->Restriction, 'onCurrentScreen']);

                add_filter('custom_menu_order', '__return_true'); // Enable custom order.
                add_filter('menu_order', [$this->Utils->Restriction, 'onMenuOrder'], 1000);

                add_action('add_meta_boxes', [$this->Utils->Restriction, 'onAddMetaBoxes']);
                add_filter('default_hidden_meta_boxes', [$this->Utils->Restriction, 'onDefaultHiddenMetaBoxes'], 10, 2);
                add_action('save_post_'.$this->Utils->Restriction->post_type, [$this->Utils->Restriction, 'onSaveRestriction']);
                add_filter('post_updated_messages', [$this->Utils->Restriction, 'onPostUpdatedMessages']);

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

            if ($this->Wp->is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->UserPermissionsWidget, 'onCurrentScreen']);

                add_action('admin_enqueue_scripts', [$this->Utils->UserPermissionsWidget, 'onAdminEnqueueScripts']);

                add_action('show_user_profile', [$this->Utils->UserPermissionsWidget, 'onEditUserProfile'], 1000);
                add_action('edit_user_profile', [$this->Utils->UserPermissionsWidget, 'onEditUserProfile'], 1000);

                add_action('personal_options_update', [$this->Utils->UserPermissionsWidget, 'onEditUserProfileUpdate']);
                add_action('edit_user_profile_update', [$this->Utils->UserPermissionsWidget, 'onEditUserProfileUpdate']);

                add_filter('manage_users_columns', [$this->Utils->UserColumns, 'onManageUsersColumns']);
                add_filter('manage_users_custom_column', [$this->Utils->UserColumns, 'onManageUsersCustomColumn'], 10, 3);
            }
            # Checkout-related hooks; including checkout-specific option filters.

            add_action('woocommerce_before_checkout_form', [$this->Utils->Checkout, 'onBeforeCheckoutForm'], -1000);

            add_filter('woocommerce_checkout_fields', [$this->Utils->Checkout, 'onCheckoutFields'], 1000);
            add_filter('woocommerce_params', [$this->Utils->Checkout, 'onCheckoutParams']); // JS params.
            add_filter('wc_checkout_params', [$this->Utils->Checkout, 'onCheckoutParams']); // JS params.

            add_action('woocommerce_after_checkout_form', [$this->Utils->Checkout, 'onAfterCheckoutForm'], -1000);

            add_action('woocommerce_before_checkout_process', [$this->Utils->Checkout, 'onBeforeCheckoutProcess']);

            # Order-related hooks; attached to WooCommerce events.

            add_action('update_post_meta', [$this->Utils->OrderMeta, 'onPostMetaUpdate'], 10, 4);

            add_action('woocommerce_before_delete_order_item', [$this->Utils->OrderItem, 'onBeforeDeleteOrderItem']);

            add_action('woocommerce_order_add_product', [$this->Utils->OrderItemMeta, 'onOrderAddProduct'], 10, 4);
            add_action('woocommerce_saved_order_items', [$this->Utils->OrderItemMeta, 'onSavedOrderItems'], 10, 2);
            add_filter('woocommerce_hidden_order_itemmeta', [$this->Utils->OrderItemMeta, 'onHiddenOrderItemMeta']);

            // See: <https://www.woothemes.com/products/woocommerce-give-products/>
            add_action('woocommerce_order_given', [$this->Utils->OrderItemMeta, 'onOrderGiven']);

            add_action('woocommerce_order_status_changed', [$this->Utils->OrderStatus, 'onOrderStatusChanged'], 1000, 3);
            add_action('woocommerce_subscription_status_changed', [$this->Utils->OrderStatus, 'onSubscriptionStatusChanged'], 1000, 3);
            add_action('woocommerce_subscriptions_switched_item', [$this->Utils->OrderStatus, 'onSubscriptionItemSwitched'], 1000, 3);

            // See: <https://www.woothemes.com/products/woocommerce-give-products/>
            add_action('woocommerce_order_given', [$this->Utils->OrderStatus, 'onOrderGiven']);

            // ↓ This would seem to be a bug in the WCS package.
            // We are moving this to after 'items', so that status changes will reflect new items.
            // Search for `WC_Meta_Box_Order_Items::save` to see the hook priority we need to come after.
            // In short, `WCS_Meta_Box_Subscription_Data::save` handles status and other data, which must come after `items`.
            if (has_action('woocommerce_process_shop_order_meta', 'WCS_Meta_Box_Subscription_Data::save')) {
                remove_action('woocommerce_process_shop_order_meta', 'WCS_Meta_Box_Subscription_Data::save', 10, 2);
                add_action('woocommerce_process_shop_order_meta', 'WCS_Meta_Box_Subscription_Data::save', 11, 2);
            }
            # Product-data and other product-related WooCommerce events.

            if ($this->Wp->is_admin) { // Admin areas only.
                add_action('current_screen', [$this->Utils->Product, 'onCurrentScreen']);

                add_action('admin_enqueue_scripts', [$this->Utils->Product, 'onAdminEnqueueScripts']);

                add_action('woocommerce_product_options_general_product_data', [$this->Utils->Product, 'onGeneralProductData']);
                add_action('woocommerce_product_after_variable_attributes', [$this->Utils->Product, 'onAfterVariableAttributes'], 10, 3);

                add_action('save_post_'.$this->Utils->Product->post_type, [$this->Utils->Product, 'onSaveProduct']);
                add_action('woocommerce_save_product_variation', [$this->Utils->Product, 'onSaveProductVariation'], 10, 2);
            }
        }, 10); // After hook priority `9`; i.e., after post types/statues have been registered by WooCommerce & WC extensions.

        add_action('wp_loaded', function () {
            $this->Utils->SecurityGate->onWpLoaded(); // Security gate.

            // See also: <http://jas.xyz/1WlT51u> to review the BuddyPress loading order.
            // BuddyPress runs its setup on `plugins_loaded` at the latest, so this comes after BP.

            // See also: <https://github.com/wp-plugins/bbpress/blob/master/bbpress.php#L969>
            // bbPress runs its setup on `plugins_loaded` at the latest, so this comes after BBP.

        }, 10); // Default hook priority is OK here. On the front-end, this reattaches to the `wp` hook anyway.
        // On the admin side, this will run immediately (i.e., on `wp_loaded`); but again, default priority is fine.
    }
}
