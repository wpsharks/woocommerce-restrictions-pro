<?php
/**
 * User permissions.
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
 * User permissions.
 *
 * @since 160524
 */
trait UserPermissions
{
    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::transfer()
     */
    public static function transferUserPermissions(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->transfer(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::hasAccessToRestrictions()
     */
    public static function userHas(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->hasAccessToRestrictions(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::currentHasAccessToRestrictions()
     */
    public static function currentUserHas(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->currentHasAccessToRestrictions(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::accessibleRestrictionIds()
     */
    public static function userAccessibleRestrictionIds(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->accessibleRestrictionIds(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::permissions()
     */
    public static function userPermissions(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->permissions(...$args);
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\UserPermissions::clearCache()
     */
    public static function clearUserPermissionsCache(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->clearCache(...$args);
    }
}
