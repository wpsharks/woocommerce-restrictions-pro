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
 * Product utilities.
 *
 * @since 16xxxx Product utilities.
 */
class Product extends SCoreClasses\SCore\Base\Core
{
    /**
     * Post type.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Post type.
     */
    public $post_type;

    /**
     * Type taxonomy.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Type taxonomy.
     */
    public $type_taxonomy;

    /**
     * Meta prefix.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Meta prefix.
     */
    public $meta_prefix;

    /**
     * Visibility classes.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Visibility classes.
     */
    public $visibility_classes;

    /**
     * Variation visibility classes.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Variation visibility classes.
     */
    public $variation_visibility_classes;

    /**
     * Client-side prefix.
     *
     * @since 16xxxx Product utilities.
     *
     * @type string Client-side prefix.
     */
    public $client_side_prefix;

    /**
     * Current screen.
     *
     * @since 16xxxx Product utilities.
     *
     * @type \WP_Screen|null Screen.
     */
    protected $screen;

    /**
     * Is screen mobile?
     *
     * @since 16xxxx Product utilities.
     *
     * @type bool Is screen mobile?
     */
    protected $screen_is_mobile;

    /**
     * Class constructor.
     *
     * @since 16xxxx Product utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->post_type     = 'product'; // Established by WooCommerce.
        $this->type_taxonomy = 'product_type'; // Established by WooCommerce.
        $this->meta_prefix   = '_'.$this->App->Config->©brand['©var'].'_product_';

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
        $this->variation_visibility_classes = []; // None at this time.

        $this->client_side_prefix = 'rsxqjzypgdqmrnnkkkrmgshvnnnkzzvu'; // JS, CSS, forms, etc.
    }

    /**
     * Current user can edit products?
     *
     * @since 16xxxx Product utilities.
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
     * @since 16xxxx Product utilities.
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
     * @since 16xxxx Product utilities.
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

        wp_enqueue_style($this->client_side_prefix.'-product-post-type', c::appUrl('/client-s/css/admin/product-post-type.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-product-post-type', c::appUrl('/client-s/js/admin/product-post-type.min.js'), ['jquery', 'jquery-jsgrid', 'jquery-ui-tooltip', 'jquery-ui-sortable', 'jquery-tiptip', 'underscore', 'moment'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-product-post-type',
            $this->client_side_prefix.'ProductPostTypeData',
            s::applyFilters('product_post_type_client_side_data', [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'i18n' => [
                    'productIdTitle' => __('Product ID', 's2member-x'),

                    'restrictionIdTitle'        => __('Access', 's2member-x'),
                    'restrictionIdTitleTip'     => __('Choose from your current list of configured Restrictions.', 's2member-x'),
                    'restrictionAccessRequired' => __('\'Access\' selection is empty.', 's2member-x'),

                    'accessOffsetDirectiveTitle'            => __('Starts', 's2member-x'),
                    'accessOffsetDirectiveTitleTip'         => __('Timer begins when Order status is \'completed\'. In the case of a Subscription, when the Subscription is \'active\'.<hr />i.e., \'immediately\' means access starts without delay.<hr />Choosing \'after 7 days\' creates a delay of 7 days. Also known as Content Dripping; i.e., loyal customers gain access to more over time, as configured here.<hr />Day, week, month &amp; year options start access at the very beginning of the calculated day: 12:00 AM (GMT/UTC)', 's2member-x'),
                    'accessOffsetDirectiveOtherPlaceholder' => sprintf(__('e.g., %1$s 48 hours', 's2member-x'), a::productPermissionAccessOffsetPrefix()),
                    'accessOffsetDirectiveRequired'         => __('\'Starts\' selection is empty.', 's2member-x'),

                    'expireOffsetDirectiveTitle'            => __('Ends', 's2member-x'),
                    'expireOffsetDirectiveTitleTip1'        => __('\'naturally\'; i.e., revoke access when an Order no longer has a \'completed\' status; or a Subscription no longer has an \'active\' status; or a fixed-term Subscription expires.<hr />\'naturally -expired\' excludes the case of a fixed-term Subscription expiring; i.e., when installments are complete, access remains.<hr />\'never\' means do not revoke (ever). Even if an Order or Subscription is cancelled.', 's2member-x'),
                    'expireOffsetDirectiveTitleTip2'        => __('Date-based options are relative to the Start time.<hr />Choosing \'7 days later\' means 7 days after access begins (according to Start time).<hr />Day, week, month &amp; year options will stop access at the very end of the calculated day: 11:59 PM (GMT/UTC)', 's2member-x'),
                    'expireOffsetDirectiveOtherPlaceholder' => sprintf(__('e.g., 90 days %1$s', 's2member-x'), a::productPermissionExpireOffsetSuffix()),
                    'expireOffsetDirectiveRequired'         => __('\'Ends\' selection is empty.', 's2member-x'),

                    'displayOrderTitle' => __('Display Order', 's2member-x'),

                    'noDataContent'  => __('No permissions.', 's2member-x'),
                    'notReadyToSave' => __('Not ready to save all changes yet...', 's2member-x'),
                    'stillInserting' => __('A Customer Permission row is still pending insertion. Please click the green \'+\' icon to complete insertion. Or, empty the \'Access\' select menu in the green insertion row.', 's2member-x'),
                    'stillEditing'   => __('A Customer Permission row (in yellow) is still open for editing. Please save your changes there first, or click the \'x\' icon to cancel editing in the open row.', 's2member-x'),
                ],
                'restrictionTitlesById'                   => a::restrictionTitlesById(),
                'productPermissionAccessOffsetDirectives' => a::productPermissionAccessOffsetDirectives(true),
                'productPermissionExpireOffsetDirectives' => a::productPermissionExpireOffsetDirectives(true),
            ])
        );
    }

    /**
     * General product data.
     *
     * @since 16xxxx Product utilities.
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
        echo '<div class="'.esc_attr($this->client_side_prefix.'-product-meta options_group '.implode(' ', $this->visibility_classes)).'">';
        echo    '<p style="margin-bottom:0 !important;">'.__('Customer Permissions (<span class="dashicons dashicons-unlock"></span> Restriction Access)', 's2member-x').'</p>';

        if (!$restriction_titles_by_id) {
            echo '<div class="notice notice-info inline">';
            echo    '<p>'.sprintf(__('It\'s not possible to grant access yet, because no Restrictions have been configured. To create your first Restriction, <a href="%1$s">click here</a>.', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>';
            echo '</div>';
        } else {
            $current_permissions = $this->getMeta($post->ID, 'permissions');
            echo '<input class="-product-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_permissions').'" value="'.esc_attr(json_encode($current_permissions)).'" />';
            echo '<div class="-permissions-grid" data-toggle="jquery-jsgrid"></div>';
        }
        echo '</div>';
    }

    /**
     * Variable product data.
     *
     * @since 16xxxx Product utilities.
     *
     * @param int      $key  Current index key.
     * @param array    $data Current variation data.
     * @param \WP_Post $post Current variation post.
     */
    public function onAfterVariableAttributes(int $key, array $data, \WP_Post $post)
    {
        if (!is_ajax()) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif (get_post_type($post) !== $this->post_type.'_variation') {
            return; // Not applicable.
        }
        $restriction_titles_by_id = a::restrictionTitlesById();
        if (!$restriction_titles_by_id && !current_user_can('create_'.a::restrictionPostType())) {
            return; // Not possible to grant access yet, and they can't create restrictions.
        }
        echo '<div class="'.esc_attr($this->client_side_prefix.'-product-meta '.implode(' ', $this->variation_visibility_classes)).'" data-variation-key="'.esc_attr($key).'">';
        echo    '<p style="margin-bottom:.2em !important;">'.__('Customer Permissions (<span class="dashicons dashicons-unlock"></span> Restriction Access)', 's2member-x').'</p>';

        if (!$restriction_titles_by_id) {
            echo '<div class="notice notice-info inline">';
            echo    '<p>'.sprintf(__('It\'s not possible to grant access yet, because no Restrictions have been configured. To create your first Restriction, <a href="%1$s">click here</a>.', 's2member-x'), esc_url(a::createRestrictionUrl())).'</p>';
            echo '</div>';
        } else {
            $current_permissions = $this->getMeta($post->ID, 'permissions');
            echo '<input class="-product-permissions" type="hidden" name="'.esc_attr($this->client_side_prefix.'_variation_permissions['.$key.']').'" value="'.esc_attr(json_encode($current_permissions)).'" />';
            echo '<div class="-permissions-grid" data-toggle="jquery-jsgrid"></div>';
        }
        echo '</div>';
    }

    /**
     * Save meta values.
     *
     * @since 16xxxx Product utilities.
     *
     * @param string|int $post_id Post ID.
     */
    public function onSaveProduct($post_id)
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
        $_r_permissions = c::unslash((string) $_REQUEST[$this->client_side_prefix.'_permissions']);
        if (!is_array($_r_permissions = json_decode($_r_permissions))) {
            return; // Corrupt form submission. Do not save.
        }
        $this->savePermissions($post_id, $_r_permissions);
    }

    /**
     * Save meta values.
     *
     * @since 16xxxx Product utilities.
     *
     * @param string|int $post_id    Post ID.
     * @param string|int $loop_index UI loop index.
     */
    public function onSaveProductVariation($post_id, $loop_index)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif (get_post_type($post_id) !== $this->post_type.'_variation') {
            return; // Not applicable.
        } elseif (!isset($_REQUEST[$this->client_side_prefix.'_variation_permissions'][$loop_index])) {
            return; // Not applicable.
        }
        $_r_permissions = c::unslash((string) $_REQUEST[$this->client_side_prefix.'_variation_permissions'][$loop_index]);
        if (!is_array($_r_permissions = json_decode($_r_permissions))) {
            return; // Corrupt form submission. Do not save.
        }
        $this->savePermissions($post_id, $_r_permissions);
    }

    /**
     * Save permissions.
     *
     * @since 16xxxx Product utilities.
     *
     * @param int   $post_id        Post ID.
     * @param array $_r_permissions Incoming permissions.
     */
    protected function savePermissions(int $post_id, array $_r_permissions)
    {
        $permissions = []; // Initialize array.

        foreach ($_r_permissions as $_key => $_r_permission) {
            if (!($_r_permission instanceof \StdClass)) {
                return; // Corrupt form submission.
            } elseif (empty($_r_permission->restriction_id)) {
                return; // Corrupt form submission.
            } // ↑ Should not happen, but better safe than sorry.
            $_r_permission->product_id = $post_id; // Force association.
            $_ProductPermission        = $this->App->Di->get(Classes\ProductPermission::class, ['data' => $_r_permission]);
            $permissions[]             = (object) $_ProductPermission->overloadArray();
        } // unset($_key, $_r_permission, $_ProductPermission); // Houskeeping.

        $this->updateMeta($post_id, 'permissions', $permissions);
    }

    /**
     * Get meta values.
     *
     * @since 16xxxx Product utilities.
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
     * @since 16xxxx Product utilities.
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
     * @since 16xxxx Product utilities.
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

    /**
     * Create product URL.
     *
     * @since 16xxxx Restrictions.
     *
     * @return string Create product URL.
     */
    public function createUrl(): string
    {
        return admin_url('/post-new.php?post_type='.urlencode($this->post_type));
    }
}
