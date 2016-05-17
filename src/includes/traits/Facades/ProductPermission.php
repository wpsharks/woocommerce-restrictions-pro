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

trait ProductPermission
{
    /**
     * @since 16xxxx Initial release.
     */
    public static function productPermissionOffsetTimes()
    {
        return $GLOBALS[static::class]->Utils->ProductPermission->offset_times;
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function productPermissionAccessOffsetTimes()
    {
        return $GLOBALS[static::class]->Utils->ProductPermission->access_offset_times;
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function productPermissionExpireOffsetTimes()
    {
        return $GLOBALS[static::class]->Utils->ProductPermission->expire_offset_times;
    }
}
