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
     * @type array All caps.
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

        $post_type = a::restrictionPostType();

        $this->caps = [ // All capabilities.
            'create_'.$post_type.'s',

            'edit_'.$post_type.'s',
            'edit_others_'.$post_type.'s',
            'edit_published_'.$post_type.'s',
            'edit_private_'.$post_type.'s',

            'publish_'.$post_type.'s',

            'delete_'.$post_type.'s',
            'delete_private_'.$post_type.'s',
            'delete_published_'.$post_type.'s',
            'delete_others_'.$post_type.'s',

            'read_private_'.$post_type.'s',
        ];
    }

    /**
     * Add default caps.
     *
     * @since 160524 Restrictions.
     *
     * @note Must be compatible w/ config `§caps['§manage']`.
     */
    public function addDefaults()
    {
        foreach (['administrator'] as $_role) {
            if (!is_object($_role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_role->add_cap($_cap);
            }
        } // unset($_role, $_cap); // Housekeeping.
    }

    /**
     * Remove all caps.
     *
     * @since 160524 Restrictions.
     */
    public function removeAll()
    {
        foreach (array_keys(wp_roles()->roles) as $_role) {
            if (!is_object($_role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_role->remove_cap($_cap);
            }
        } // unset($_role, $_cap); // Housekeeping.
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
