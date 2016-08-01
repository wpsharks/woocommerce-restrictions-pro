<?php
/**
 * Restriction caps.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
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
 * Restriction caps.
 *
 * @since 160524 Installer.
 */
class RestrictionCaps extends SCoreClasses\SCore\Base\Core
{
    /**
     * All caps.
     *
     * @since 160524 Restrictions.
     *
     * @var array All caps.
     */
    public $caps;

    /**
     * Class constructor.
     *
     * @since 160524 Restrictions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->caps = [
            'create_restrictions',

            'edit_restrictions',
            'edit_others_restrictions',
            'edit_published_restrictions',
            'edit_private_restrictions',

            'publish_restrictions',

            'delete_restrictions',
            'delete_private_restrictions',
            'delete_published_restrictions',
            'delete_others_restrictions',

            'read_private_restrictions',
        ];
    }

    /**
     * Add default caps.
     *
     * @since 160524 Restrictions.
     */
    public function addDefaults()
    {
        foreach (['administrator', 'editor', 'shop_manager'] as $_role) {
            if (!($_WP_Role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_WP_Role->add_cap($_cap);
            } // unset($_cap);
        } // unset($_role, $_WP_Role);
    }

    /**
     * Remove all caps.
     *
     * @since 160524 Restrictions.
     */
    public function removeAll()
    {
        foreach (array_keys(wp_roles()->roles) as $_role) {
            if (!($_WP_Role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_WP_Role->remove_cap($_cap);
            } // unset($_cap);
        } // unset($_role, $_WP_Role);
    }

    /**
     * Restore default caps.
     *
     * @since 160524 Restrictions.
     */
    public function restoreDefaults()
    {
        $this->removeAll();
        $this->addDefaults();
    }
}
