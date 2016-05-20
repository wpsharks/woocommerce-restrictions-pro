<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\s2MemberX\Pro\Classes\Utils;

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

/**
 * Checkout utilities.
 *
 * @since 16xxxx Order-related events.
 */
class Checkout extends SCoreClasses\SCore\Base\Core
{
    /**
     * On checkout init.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Checkout $WC_Checkout Class instance.
     */
    public function onCheckoutInit(\WC_Checkout $WC_Checkout)
    {
        $WC_Checkout->enable_signup         = true;
        $WC_Checkout->enable_guest_checkout = false;
        // @TODO Force users to register during checkout when cart contains a restriction.
        // @TODO See: https://github.com/woothemes/woocommerce/blob/653f79b25b79f953af04a4d1cf28f34ba5c862c9/includes/class-wc-checkout.php#L95-L97
        // @TODO Via hook: woocommerce_before_checkout_form (and others too).
        // @TODO Search for `woocommerce_before_checkout_form` in the subscriptions plugin for examples of how to do this.
    }
}
