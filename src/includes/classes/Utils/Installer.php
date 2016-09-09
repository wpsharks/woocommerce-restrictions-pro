<?php
/**
 * Installer.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\Utils;

use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\CoreFacades as c;
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
 * Installer.
 *
 * @since 160524 Installer.
 */
class Installer extends SCoreClasses\SCore\Base\Core
{
    /**
     * Version-specific upgrades.
     *
     * @since 160826 Version-specific upgrade.
     *
     * @param array $history Install history.
     */
    public function onVsUpgrades(array $history)
    {
        // if (version_compare($history['last_version'], 'XXXXXX', '<')) {
        //     $this->App->Utils->VsUpgrades->fromLtXXXXXX();
        // }
    }

    /**
     * Other install routines.
     *
     * @since 160524 Restrictions.
     *
     * @param array $history Install history.
     */
    public function onOtherInstallRoutines(array $history)
    {
        a::addDefaultRestrictionCaps();

        a::clearSystematicCache();
        a::clearRestrictionsCache();
        a::clearUserPermissionsCache();
    }
}
