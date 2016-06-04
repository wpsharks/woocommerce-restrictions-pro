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
#
use function assert as debug;
use function get_defined_vars as vars;

trait Restriction
{
    /**
     * @since 160524 Initial release.
     */
    public static function restrictionIcon()
    {
        return $GLOBALS[static::class]->Utils->Restriction->icon;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionPostType()
    {
        return $GLOBALS[static::class]->Utils->Restriction->post_type;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionCategoryTaxonomy()
    {
        return $GLOBALS[static::class]->Utils->Restriction->category_taxonomy;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionMetaPrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->meta_prefix;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionAccessPkgPrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->access_pkg_prefix;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionAccessCcapPrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->access_ccap_prefix;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionClientSidePrefix()
    {
        return $GLOBALS[static::class]->Utils->Restriction->client_side_prefix;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionMetaKeys()
    {
        return $GLOBALS[static::class]->Utils->Restriction->meta_keys;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function restrictionIntMetaKeys()
    {
        return $GLOBALS[static::class]->Utils->Restriction->int_meta_keys;
    }

    /**
     * @since 160524 Initial release.
     */
    public static function getRestrictionMeta(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restriction->getMeta(...$args);
    }

    /**
     * @since 160524 Initial release.
     */
    public static function updateRestrictionMeta(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restriction->updateMeta(...$args);
    }

    /**
     * @since 160524 Initial release.
     */
    public static function deleteRestrictionMeta(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restriction->deleteMeta(...$args);
    }

    /**
     * @since 160524 Initial release.
     */
    public static function createRestrictionUrl(...$args)
    {
        return $GLOBALS[static::class]->Utils->Restriction->createUrl(...$args);
    }
}
