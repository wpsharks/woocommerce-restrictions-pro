<?php
/**
 * Facades.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\Base;

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
 * Pseudo-static facades.
 *
 * @since 160227 Initial release.
 */
abstract class Facades
{
    use Traits\Facades\Coupon;
    use Traits\Facades\Order;
    use Traits\Facades\OrderStatus;
    use Traits\Facades\Product;
    use Traits\Facades\ProductPermission;
    use Traits\Facades\Restriction;
    use Traits\Facades\RestrictionCaps;
    use Traits\Facades\Restrictions;
    use Traits\Facades\SecurityChecks;
    use Traits\Facades\SecurityGate;
    use Traits\Facades\Subscription;
    use Traits\Facades\Systematics;
    use Traits\Facades\UserPermission;
    use Traits\Facades\UserPermissions;
    use Traits\Facades\UserPermissionShortcodes;
    use Traits\Facades\Webhook;
}
