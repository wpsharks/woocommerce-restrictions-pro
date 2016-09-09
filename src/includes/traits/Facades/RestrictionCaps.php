<?php
/**
 * Restriction caps.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Traits\Facades;

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
 * Restriction caps.
 *
 * @since 160524
 */
trait RestrictionCaps
{
    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\RestrictionCaps::$caps
     */
    public static function restrictionCaps()
    {
        return $GLOBALS[static::class]->Utils->RestrictionCaps->caps;
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\RestrictionCaps::addDefaults()
     */
    public static function addDefaultRestrictionCaps(...$args)
    {
        return $GLOBALS[static::class]->Utils->RestrictionCaps->addDefaults(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\RestrictionCaps::removeAll()
     */
    public static function removeAllRestrictionCaps(...$args)
    {
        return $GLOBALS[static::class]->Utils->RestrictionCaps->removeAll(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\RestrictionCaps::restoreDefaults()
     */
    public static function restoreDefaultRestrictionCaps(...$args)
    {
        return $GLOBALS[static::class]->Utils->RestrictionCaps->restoreDefaults(...$args);
    }
}
