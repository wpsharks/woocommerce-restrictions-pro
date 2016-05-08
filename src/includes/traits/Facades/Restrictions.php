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

trait Restrictions
{
    /**
     * @since 16xxxx Initial release.
     */
    public static function clearRestrictionsCache(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->clearCache(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionSlugToId(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->slugToId(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionSlugsToIds(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->slugsToIds(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionTitlesById(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->titlesById(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionIdsBySlug(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->idsBySlug(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionsByMetaKey(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->byMetaKey(...$args);
    }

    /**
     * @since 16xxxx Initial release.
     */
    public static function restrictionsAllWithMeta(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restrictions->allWithMeta(...$args);
    }
}
