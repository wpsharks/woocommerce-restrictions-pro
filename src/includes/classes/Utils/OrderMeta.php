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
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Order meta utilities.
 *
 * @since 160524 Order meta utilities.
 */
class OrderMeta extends SCoreClasses\SCore\Base\Core
{
    /**
     * Subscription post type.
     *
     * @since 160524 Order meta utilities.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * All order post types.
     *
     * @since 160524 Order meta utilities.
     *
     * @param array All order post types.
     */
    protected $all_order_post_types;

    /**
     * Class constructor.
     *
     * @since 160524 Order meta utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->subscription_post_type = a::subscriptionPostType();
        $this->all_order_post_types   = wc_get_order_types();
    }

    /**
     * On post meta update.
     *
     * @since 160524 Order meta utilities.
     *
     * @param string|int $meta_id    Meta ID.
     * @param string|int $post_id    Post ID.
     * @param string     $meta_key   Meta key.
     * @param mixed      $meta_value Meta value (new value).
     *
     * @note This works for Subscriptions also.
     */
    public function onPostMetaUpdate($meta_id, $post_id, $meta_key, $meta_value)
    {
        if (!($meta_id = (int) $meta_id)) {
            debug(0, c::issue(vars(), 'Empty meta ID.'));
            return; // Not possible.
        } elseif (!($post_id = (int) $post_id)) {
            debug(0, c::issue(vars(), 'Empty post ID.'));
            return; // Not possible.
        } elseif ($meta_key !== '_customer_user') {
            return; // Only key we look at, for now.
        } elseif (!($post_type = get_post_type($post_id))) {
            debug(0, c::issue(vars(), 'Unable to acquire post type.'));
            return; // Not possible; no post type.
        } elseif (!in_array($post_type, $this->all_order_post_types, true)) {
            return; // Not applicable; not an order post type.
        }
        $new_user_id = (int) $meta_value; // New customer ID.
        $old_user_id = (int) get_post_meta($post_id, '_customer_user', true);

        if ($old_user_id && $new_user_id && $old_user_id !== $new_user_id) {
            switch ($post_type) { // Either an order or subscription.

                case $this->subscription_post_type:
                    $subscription_id = $post_id; // Subscription.
                    a::transferUserPermissions($old_user_id, $new_user_id, ['where' => compact('subscription_id')]);
                    break; // Transfers permissions to new customer when user ID is changed on an order.

                default: // Any other order type.
                    $order_id = $post_id; // Order of some type.
                    a::transferUserPermissions($old_user_id, $new_user_id, ['where' => compact('order_id')]);
                    break; // Transfers permissions to new customer when user ID is changed on an order.
            }
            c::review(compact(// Log for review.
                'order_id',
                'subscription_id',
                'new_user_id',
                'old_user_id'
            ), 'Transferring user permissions because customer was changed.');
        }
    }
}
