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
 * Order meta utilities.
 *
 * @since 16xxxx Order-related events.
 */
class OrderMeta extends SCoreClasses\SCore\Base\Core
{
    /**
     * Order post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Order post type.
     */
    protected $order_post_type;

    /**
     * Subscription post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Product meta prefix.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Product meta prefix.
     */
    protected $product_meta_prefix;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order-related events.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->order_post_type        = a::orderPostType();
        $this->subscription_post_type = a::subscriptionPostType();
        $this->product_meta_prefix    = a::productMetaPrefix();
    }

    /**
     * On add order item meta.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $item_id Order item ID.
     * @param array      $item    Order item.
     */
    public function onAddOrderItemMeta($item_id, array $item)
    {
        if (!($item_id = (int) $item_id)) {
            return; // Not possible.
        } elseif (empty($item['product_id'])) {
            return; // Not possible.
        } elseif (!($WC_Product = wc_get_product($item['product_id']))) {
            return; // Not possible.
        } elseif (!$WC_Product->exists()) {
            return; // Not possible.
        }
        $product_type        = $WC_Product->get_type();
        $product_permissions = a::getProductMeta($WC_Product->id, 'permissions');

        wc_add_order_item_meta($item_id, $this->product_meta_prefix.'type', $product_type);

        foreach ($product_permissions as $_permission) { // Each permission.
            wc_add_order_item_meta($item_id, $this->product_meta_prefix.'permissions', $_permission);
        } // unset($_permission); // Housekeeping.
    }

    /**
     * On post meta update.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $meta_id    Meta ID.
     * @param string|int $post_id    Post ID.
     * @param string     $meta_key   Meta key.
     * @param mixed      $meta_value Meta value (new value).
     */
    public function onPostMetaUpdate($meta_id, $post_id, $meta_key, $meta_value)
    {
        if (!($meta_id = (int) $meta_id)) {
            return; // Not possible.
        } elseif (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif ($meta_key !== '_customer_user') {
            return; // Only key we look at, for now.
        } elseif (!($post_type = get_post_type($post_id))) {
            return; // Not possible; no post type.
        } elseif (!in_array($post_type, [$this->order_post_type, $this->subscription_post_type], true)) {
            return; // Not applicable.
        }
        $new_user_id = (int) $meta_value; // New customer ID.
        $old_user_id = (int) get_post_meta($post_id, '_customer_user', true);

        if ($old_user_id && $new_user_id && $old_user_id !== $new_user_id) {
            $transfer_args = []; // Initialize args.

            if ($post_type === $this->order_post_type) {
                $transfer_args['where']['order_id'] = $post_id;
            } elseif ($post_type === $this->subscription_post_type) {
                $transfer_args['where']['subscription_id'] = $post_id;
            } else { // Totally unexpected given the above.
                throw new Exception('Unexpected post type.');
            }
            a::transferUserPermissions($old_user_id, $new_user_id, $transfer_args);
        } // Transfers permissions (as-is) to a new customer.
    }
}
