<?php
/**
 * Product.
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
 * Product.
 *
 * @since 160524
 */
trait Product
{
    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Product::$visibility_classes
     */
    public static function productVisibilityClasses()
    {
        return $GLOBALS[static::class]->Utils->Product->visibility_classes;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Product::$variation_visibility_classes
     */
    public static function productVariationVisibilityClasses()
    {
        return $GLOBALS[static::class]->Utils->Product->variation_visibility_classes;
    }

    /**
     * @since 160524 Initial release.
     * @see Classes\Utils\Product::$client_side_prefix
     */
    public static function productClientSidePrefix()
    {
        return $GLOBALS[static::class]->Utils->Product->client_side_prefix;
    }

    /**
     * @since 160524 Initial release.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\Product::createUrl()
     */
    public static function createProductUrl(...$args)
    {
        return $GLOBALS[static::class]->Utils->Product->createUrl(...$args);
    }
}
