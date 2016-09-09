<?php
/**
 * Restriction.
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
 * Restriction.
 *
 * @since 160524
 */
trait Restriction
{
    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$icon
     */
    public static function restrictionIcon()
    {
        return $GLOBALS[static::class]->Utils->Restriction->icon;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$access_pkg_prefix
     */
    public static function restrictionAccessPkgPrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->access_pkg_prefix;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$access_ccap_prefix
     */
    public static function restrictionAccessCcapPrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->access_ccap_prefix;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$client_side_prefix
     */
    public static function restrictionClientSidePrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->client_side_prefix;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$meta_keys
     */
    public static function restrictionMetaKeys()
    {
        return $GLOBALS[static::class]->Utils->Restriction->meta_keys;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Restriction::$int_meta_keys
     */
    public static function restrictionIntMetaKeys()
    {
        return $GLOBALS[static::class]->Utils->Restriction->int_meta_keys;
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\Restriction::createUrl()
     */
    public static function createRestrictionUrl(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restriction->createUrl(...$args);
    }
}
