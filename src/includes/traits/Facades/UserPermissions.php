<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\s2MemberX\Pro\Traits\Facades;

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

trait UserPermissions
{
    /**
     * @since 16xxxx Initial release.
     */
    public static function userHas(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->hasAccessToRestrictions(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function currentUserHas(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->currentHasAccessToRestrictions(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function clearUserPermissionsCache(...$args)
    {
        return $GLOBALS[static::class]->Utils->UserPermissions->clearCache(...$args);
    }
}
