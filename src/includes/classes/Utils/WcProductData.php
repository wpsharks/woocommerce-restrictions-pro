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
 * WC product data utilities.
 *
 * @since 16xxxx WC product data.
 */
class WcProductData extends SCoreClasses\SCore\Base\Core
{
    /**
     * Post type.
     *
     * @since 16xxxx WC product data.
     *
     * @type string Post type.
     */
    public $post_type;

    /**
     * Meta prefix.
     *
     * @since 16xxxx WC product data.
     *
     * @type string Meta prefix.
     */
    public $meta_prefix;

    /**
     * Visibility classes.
     *
     * @since 16xxxx WC product data.
     *
     * @type string Visibility classes.
     */
    public $visibility_classes;

    /**
     * Client-side prefix.
     *
     * @since 16xxxx WC product data.
     *
     * @type string Client-side prefix.
     */
    public $client_side_prefix;

    /**
     * Current screen.
     *
     * @since 16xxxx WC product data.
     *
     * @type \WP_Screen|null Screen.
     */
    protected $screen;

    /**
     * Is screen mobile?
     *
     * @since 16xxxx WC product data.
     *
     * @type bool Is screen mobile?
     */
    protected $screen_is_mobile;

    /**
     * Class constructor.
     *
     * @since 16xxxx WC product data.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->post_type   = 'product'; // Established by WooCommerce.
        $this->meta_prefix = $this->App->Config->©brand['©var'].'_product_';

        $this->visibility_classes = [
            'hide_if_external',
            'hide_if_grouped',
            'hide_if_variable',
            'hide_if_variable_subscription',
            'hide_if_variable-subscription',

            // There is some inconsistency in the WC subscriptions plugin.
            // In some places they use a dash, and in others it uses an underscore.
            // The official definition is with an `_`, but that seems likely to change.
        ];
        $this->client_side_prefix = 'rsxqjzypgdqmrnnkkkrmgshvnnnkzzvu'; // JS, CSS, forms, etc.
    }

    /**
     * Current user can edit products?
     *
     * @since 16xxxx WC product data.
     *
     * @return bool True if the current user can.
     */
    protected function currentUserCan(): bool
    {
        return (bool) current_user_can('edit_'.$this->post_type.'s');
    }

    /**
     * Get screen object.
     *
     * @since 16xxxx WC product data.
     */
    public function onCurrentScreen(\WP_Screen $screen)
    {
        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        $this->screen           = $screen;
        $this->screen_is_mobile = wp_is_mobile();
    }

    /**
     * Enqueue styles/scripts.
     *
     * @since 16xxxx WC product data.
     */
    public function onAdminEnqueueScripts()
    {
        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        s::enqueueMomentLibs();
        s::enqueueJQueryJsGridLibs();

        wp_enqueue_style($this->client_side_prefix.'-product-data', c::appUrl('/client-s/css/admin/wc-product-data.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-product-data', c::appUrl('/client-s/js/admin/wc-product-data.min.js'), ['jquery', 'jquery-jsgrid', 'jquery-ui-tooltip', 'jquery-ui-sortable', 'underscore', 'moment'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-product-data',
            $this->client_side_prefix.'WcProductDataData',
            [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'i18n' => [

                ],
            ]
        );
    }

    /**
     * General product data.
     *
     * @since 16xxxx WC product data.
     */
    public function onGeneralProductData()
    {
        global $post; // Needed below.

        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif (!($post instanceof \WP_Post)) {
            return; // Not applicable.
        } elseif (get_post_type($post) !== $this->post_type) {
            return; // Not applicable.
        }
        $restriction_titles_by_id = a::restrictionTitlesById();
        if (!$restriction_titles_by_id && !current_user_can('create_'.a::restrictionPostType())) {
            return; // Not possible to grant access yet, and they can't create restrictions.
        }
        $product_permission_offset_periods = a::productPermissionOffsetPeriods();
        $current_permissions               = $this->getMeta($post->ID, 'permissions');

        echo '<div class="'.esc_attr($this->client_side_prefix.'-product-data options_group '.implode(' ', $this->visibility_classes)).'">';

        echo    '<input class="-product-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode($current_permissions)).'" />';
        echo    '<input class="-restriction-titles-by-id" type="hidden" value="'.esc_attr(json_encode($restriction_titles_by_id)).'" />';
        echo    '<input class="-product-permission-offset-periods" type="hidden" value="'.esc_attr(json_encode($product_permission_offset_periods)).'" />';

        echo    '<div class="-grid" data-toggle="jquery-jsgrid"></div>';

        echo '</div>';
    }

    /**
     * Variable product data.
     *
     * @since 16xxxx WC product data.
     */
    public function onAfterVariableAttributes()
    {
        global $post; // Needed below.

        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif (!($post instanceof \WP_Post)) {
            return; // Not applicable.
        } elseif (get_post_type($post) !== $this->post_type) {
            return; // Not applicable.
        }
        $restriction_titles_by_id = a::restrictionTitlesById();
        if (!$restriction_titles_by_id && !current_user_can('create_'.a::restrictionPostType())) {
            return; // Not possible to grant access yet, and they can't create restrictions.
        }
        $product_permission_offset_periods = a::productPermissionOffsetPeriods();
        $current_permissions               = $this->getMeta($post->ID, 'permissions');

        echo '<div class="'.esc_attr($this->client_side_prefix.'-product-data').'">';

        echo    '<input class="-product-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode($current_permissions)).'" />';
        echo    '<input class="-restriction-titles-by-id" type="hidden" value="'.esc_attr(json_encode($restriction_titles_by_id)).'" />';
        echo    '<input class="-product-permission-offset-periods" type="hidden" value="'.esc_attr(json_encode($product_permission_offset_periods)).'" />';

        echo    '<div class="-grid" data-toggle="jquery-jsgrid"></div>';

        echo '</div>';
    }

    /**
     * Get meta values.
     *
     * @since 16xxxx WC product data.
     *
     * @param string|int $post_id  Post ID.
     * @param \WP_Post   $post     Post object.
     * @param bool       $updating On update?
     */
    public function onSavePost($post_id, \WP_Post $post, bool $updating)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif (get_post_type($post_id) !== $this->post_type) {
            return; // Not applicable.
        } elseif (!isset($_REQUEST[$this->client_side_prefix.'_permissions'])) {
            return; // Not applicable.
        }
        $permissions = []; // Initialize array of permissions.

        $_r_permissions = c::unslash((string) $_REQUEST[$this->client_side_prefix.'_permissions']);
        if (!is_array($_r_permissions = json_decode($_r_permissions))) {
            return; // Corrupt form submission. Do not save.
        }
        foreach ($_r_permissions as $_key => $_r_permission) {
            if (!($_r_permission instanceof \StdClass)) {
                return; // Corrupt form submission.
            } elseif (empty($_r_permission->restriction_id)) {
                return; // Corrupt form submission.
            } // ↑ Should not happen, but better safe than sorry.
            $_r_permission->product_id = $post_id; // Force association.
            $permissions[]             = $this->App->Di->get(Classes\ProductPermission::class, ['data' => $_r_permission]);
        } // unset($_key, $_r_permission, $_r_permission_key); // Houskeeping.

        $this->updateMeta($post_id, 'permissions', $permissions);
    }

    /**
     * Get meta values.
     *
     * @since 16xxxx WC product data.
     *
     * @param string|int $post_id Post ID.
     * @param string     $key     Meta key.
     *
     * @return array Meta values.
     */
    public function getMeta($post_id, string $key): array
    {
        if (!($post_id = (int) $post_id)) {
            return []; // Not possible.
        }
        $values = get_post_meta($post_id, $this->meta_prefix.$key);

        return is_array($values) ? $values : [];
    }

    /**
     * Update meta values.
     *
     * @since 16xxxx WC product data.
     *
     * @param string|int $post_id Post ID.
     * @param string     $key     Meta key.
     * @param array      $values  Meta values.
     */
    public function updateMeta($post_id, string $key, array $values)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        }
        $this->deleteMeta($post_id, /* No prefix here.*/ $key);

        foreach ($values as $_value) {
            add_post_meta($post_id, $this->meta_prefix.$key, $_value);
        } // unset($_value); // Housekeeping.
    }

    /**
     * Delete meta values.
     *
     * @since 16xxxx WC product data.
     *
     * @param string|int $post_id Post ID.
     * @param string     $key     Meta key.
     */
    public function deleteMeta($post_id, string $key)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        }
        delete_post_meta($post_id, $this->meta_prefix.$key);
    }
}
