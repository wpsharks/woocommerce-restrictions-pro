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
    const VERSION = '160430'; //v//

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
                    ],
                ],
            ],
            '§pro_option_keys' => [
                'restriction_categories_enable',
                'security_gate_redirect_to_args_enable',
            ],
            '§default_options' => [
                'restriction_categories_enable'         => '0',
                'security_gate_redirects_to_post_id'    => '',
                'security_gate_redirect_to_args_enable' => '1',
            ],
            // @TODO Require WooCommerce.
            // @TODO Require fancy permalinks.
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
            # Restriction-related hooks.

            $this->Utils->Restriction->onInitRegisterPostType();

            add_action('current_screen', [$this->Utils->Restriction, 'onCurrentScreen']);

            add_filter('custom_menu_order', '__return_true'); // Enable custom order.
            add_filter('menu_order', [$this->Utils->Restriction, 'onMenuOrder'], 1000);

            add_action('add_meta_boxes', [$this->Utils->Restriction, 'onAddMetaBoxes']);
            add_filter('default_hidden_meta_boxes', [$this->Utils->Restriction, 'onDefaultHiddenMetaBoxes'], 10, 2);
            add_action('save_post_'.$this->Utils->Restriction->post_type, [$this->Utils->Restriction, 'onSavePost'], 10, 3);

            add_action('admin_enqueue_scripts', [$this->Utils->Restriction, 'onAdminEnqueueScripts']);

            # User-related hooks; including role/capability filters.

            add_filter('user_has_cap', [$this->Utils->User, 'onUserHasCap'], 1000, 4);

            # Security gate; always after the `restriction` post type registration.

            // See also: <http://jas.xyz/1WlT51u> to review the BuddyPress loading order.
            // BuddyPress runs its setup on `plugins_loaded` at the latest, so this comes after BP.

            // See also: <https://github.com/wp-plugins/bbpress/blob/master/bbpress.php#L969>
            // bbPress runs its setup on `plugins_loaded` at the latest, so this comes after BBP.

            $this->Utils->SecurityGate->onInitGuardRestrictions();
        });
    }
}
