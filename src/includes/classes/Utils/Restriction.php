<?php
/**
 * Restriction.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\Utils;

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
 * Restriction.
 *
 * @since 160524 Security gate.
 */
class Restriction extends SCoreClasses\SCore\Base\Core
{
    /**
     * Access PKG prefix.
     *
     * @since 160524 Restriction.
     *
     * @type string Access PKG prefix.
     */
    public $access_pkg_prefix;

    /**
     * Access CCAP prefix.
     *
     * @since 160524 Restriction.
     *
     * @type string Access CCAP prefix.
     */
    public $access_ccap_prefix;

    /**
     * Client-side prefix.
     *
     * @since 160524 Restriction.
     *
     * @type string Client-side prefix.
     */
    public $client_side_prefix;

    /**
     * Meta keys.
     *
     * @since 160524 Restriction.
     *
     * @type array Meta keys.
     */
    public $meta_keys;

    /**
     * Meta keys.
     *
     * @since 160524 Restriction.
     *
     * @type array Meta keys.
     */
    public $int_meta_keys;

    /**
     * Current screen.
     *
     * @since 160524 Restriction.
     *
     * @type \WP_Screen|null Screen.
     */
    protected $screen;

    /**
     * Is screen mobile?
     *
     * @since 160524 Restriction.
     *
     * @type bool Is screen mobile?
     */
    protected $screen_is_mobile;

    /**
     * Class constructor.
     *
     * @since 160524 Restrictions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->access_pkg_prefix  = s::applyFilters('restriction_pkg_prefix', 'access_pkg_');
        $this->access_ccap_prefix = s::applyFilters('restriction_ccap_prefix', 'access_ccap_');
        $this->client_side_prefix = 'fdbmjuxwzjfjtaucytprkbcqfpftudyg'; // JS, CSS, forms, etc.

        $this->meta_keys     = ['post_ids', 'post_types', 'tax_term_ids', 'author_ids', 'roles', 'ccaps', 'uri_patterns'];
        $this->int_meta_keys = ['post_ids', 'author_ids']; // Integer data type.

        $this->screen_is_mobile = false; // Initialize.
    }

    /**
     * On `init` hook.
     *
     * @since 160731 Restrictions.
     */
    public function onInit()
    {
        register_post_type(
            'restriction', // Args can be filtered by plugins.
            s::applyFilters('register_post_type_restriction_args', [
                'public'       => false,
                'hierarchical' => false,

                'show_ui'           => true,
                'show_in_menu'      => true,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => true,

                'rewrite'     => false,
                'has_archive' => false,
                'query_var'   => false,

                'supports'         => ['title'],
                'delete_with_user' => false,

                'menu_position' => null,
                'menu_icon'     => 'dashicons-lock',
                'description'   => __('Content restriction for membership.', 'woocommerce-restrictions'),

                'labels' => [ // See: <http://jas.xyz/244m2Sd>
                    'name'          => __('Restrictions', 'woocommerce-restrictions'),
                    'singular_name' => __('Restriction', 'woocommerce-restrictions'),

                    'name_admin_bar' => __('Restriction', 'woocommerce-restrictions'),
                    'menu_name'      => __('Restrictions', 'woocommerce-restrictions'),

                    'all_items'    => __('All Restrictions', 'woocommerce-restrictions'),
                    'add_new'      => __('Add Restriction', 'woocommerce-restrictions'),
                    'add_new_item' => __('Add New Restriction', 'woocommerce-restrictions'),
                    'new_item'     => __('New Restriction', 'woocommerce-restrictions'),
                    'edit_item'    => __('Edit Restriction', 'woocommerce-restrictions'),
                    'view_item'    => __('View Restriction', 'woocommerce-restrictions'),

                    'search_items'       => __('Search Restrictions', 'woocommerce-restrictions'),
                    'not_found'          => __('No Restrictions Found', 'woocommerce-restrictions'),
                    'not_found_in_trash' => __('No Restrictions Found in Trash', 'woocommerce-restrictions'),

                    'insert_into_item'      => __('Insert Into Restriction', 'woocommerce-restrictions'),
                    'uploaded_to_this_item' => __('Upload to this Restriction', 'woocommerce-restrictions'),

                    'featured_image'        => __('Set Featured Image', 'woocommerce-restrictions'),
                    'remove_featured_image' => __('Remove Featured Image', 'woocommerce-restrictions'),
                    'use_featured_image'    => __('Use as Featured Image', 'woocommerce-restrictions'),

                    'items_list'            => __('Restrictions List', 'woocommerce-restrictions'),
                    'items_list_navigation' => __('Restrictions List Navigation', 'woocommerce-restrictions'),

                    'archives'          => __('Restriction Archives', 'woocommerce-restrictions'),
                    'filter_items_list' => __('Filter Restrictions List', 'woocommerce-restrictions'),
                    'parent_item_colon' => __('Parent Restriction:', 'woocommerce-restrictions'),
                ],
                'map_meta_cap'    => true,
                'capability_type' => [
                    'restriction',
                    'restrictions',
                ],
            ])
        );
        register_taxonomy(
            'restriction_category',
            'restriction', // Args can be filtered by plugins.
            s::applyFilters('register_taxonomy_restriction_category_args', [
                'public'       => false,
                'hierarchical' => true,

                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_nav_menus'  => false,
                'show_tagcloud'      => false,
                'show_in_quick_edit' => true,
                'show_admin_column'  => true,

                'rewrite'   => false,
                'query_var' => false,

                'description' => __('Content restriction categories.', 'woocommerce-restrictions'),

                'labels' => [ // See: <http://jas.xyz/244m1Oc>
                    'name'          => __('Restriction Categories', 'woocommerce-restrictions'),
                    'singular_name' => __('Restriction Category', 'woocommerce-restrictions'),

                    'name_admin_bar' => __('Restriction Category', 'woocommerce-restrictions'),
                    'menu_name'      => __('Categories', 'woocommerce-restrictions'),

                    'all_items'           => __('All Categories', 'woocommerce-restrictions'),
                    'add_new_item'        => __('Add New Category', 'woocommerce-restrictions'),
                    'new_item_name'       => __('New Category Name', 'woocommerce-restrictions'),
                    'add_or_remove_items' => __('Add or Remove Categories', 'woocommerce-restrictions'),
                    'view_item'           => __('View Category', 'woocommerce-restrictions'),
                    'edit_item'           => __('Edit Category', 'woocommerce-restrictions'),
                    'update_item'         => __('Update Category', 'woocommerce-restrictions'),

                    'search_items' => __('Search Categories', 'woocommerce-restrictions'),
                    'not_found'    => __('No Categories Found', 'woocommerce-restrictions'),
                    'no_terms'     => __('No Categories', 'woocommerce-restrictions'),

                    'choose_from_most_used'      => __('Choose From the Most Used Categories', 'woocommerce-restrictions'),
                    'separate_items_with_commas' => __('Separate Categories w/ Commas', 'woocommerce-restrictions'),

                    'items_list'            => __('Categories List', 'woocommerce-restrictions'),
                    'items_list_navigation' => __('Categories List Navigation', 'woocommerce-restrictions'),

                    'archives'          => __('All Categories', 'woocommerce-restrictions'),
                    'popular_items'     => __('Popular Categories', 'woocommerce-restrictions'),
                    'parent_item'       => __('Parent Category', 'woocommerce-restrictions'),
                    'parent_item_colon' => __('Parent Category:', 'woocommerce-restrictions'),
                ],
                'capabilities' => [
                    'assign_terms' => 'edit_restrictions',
                    'edit_terms'   => 'edit_restrictions',
                    'manage_terms' => 'edit_others_restrictions',
                    'delete_terms' => 'delete_others_restrictions',
                ],
            ])
        );
    }

    /**
     * Post updated message translations.
     *
     * @since 160524 Restrictions.
     *
     * @param array $messages Message translations.
     *
     * @return array Message translations.
     */
    public function onPostUpdatedMessages(array $messages): array
    {
        if (!($post = get_post())) {
            return $messages; // Not possible.
        }
        $messages['restriction'] = [
            0 => '', // Not applicable.
            1 => __('Restriction updated.', 'woocommerce-restrictions'),
            2 => __('Custom field updated.', 'woocommerce-restrictions'),
            3 => __('Custom field deleted.', 'woocommerce-restrictions'),
            4 => __('Restriction updated.', 'woocommerce-restrictions'),
            5 => isset($_GET['revision'])
                ? sprintf(
                    __('Restriction restored to revision from %s.', 'woocommerce-restrictions'),
                    wp_post_revision_title((int) $_GET['revision'], false)
                ) : null,
            6 => __('Restriction published.', 'woocommerce-restrictions'),
            7 => __('Restriction saved.', 'woocommerce-restrictions'),
            8 => __('Restriction submitted.', 'woocommerce-restrictions'),
            9 => sprintf(
                __('Restriction scheduled for: <strong>%1$s</strong>.', 'woocommerce-restrictions'),
                date_i18n(__('M j, Y @ G:i', 'woocommerce-restrictions'), strtotime($post->post_date))
            ),
            10 => __('Restriction draft updated.', 'woocommerce-restrictions'),
        ];
        return $messages;
    }

    /**
     * Current user can edit restrictions?
     *
     * @since 160524 Restrictions.
     *
     * @return bool True if the current user can.
     */
    protected function currentUserCan(): bool
    {
        return (bool) current_user_can('edit_restrictions');
    }

    /**
     * Get screen object.
     *
     * @since 160524 Restrictions.
     */
    public function onCurrentScreen(\WP_Screen $screen)
    {
        if (!s::isMenuPageForPostType('restriction')) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        $this->screen           = $screen;
        $this->screen_is_mobile = wp_is_mobile();
    }

    /**
     * Position restrictions.
     *
     * @since 160524 Restrictions.
     */
    public function onMenuOrder(array $menu_items): array
    {
        // No conditional checks up here.
        // If the menu items exist they are reordered.

        $woocommerce_item = 'woocommerce'; // Position after this.
        $woocommerce_key  = array_search($woocommerce_item, $menu_items, true);

        $restriction_item = 'edit.php?post_type=restriction';
        $restriction_key  = array_search($restriction_item, $menu_items, true);

        if ($woocommerce_key !== false && $restriction_key !== false) {
            $new_menu_items = []; // Initialize new menu items.

            foreach ($menu_items as $_key => $_item) {
                if ($_item !== $restriction_item) {
                    $new_menu_items[] = $_item;
                }
                if ($_item === $woocommerce_item) {
                    $new_menu_items[] = $restriction_item;
                }
            } // unset($_key, $_item); // Housekeeping.

            $menu_items = $new_menu_items; // Alter.
        }
        return $menu_items; // Always return filtered value.
    }

    /**
     * Add meta boxes.
     *
     * @since 160524 Restrictions.
     *
     * @param string $post_type Post type.
     */
    public function onAddMetaBoxes(string $post_type)
    {
        if (!s::isMenuPageForPostType('restriction')) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif ($post_type !== 'restriction') {
            return; // Not applicable.
        }
        $meta_boxes = [ // @TODO Update these to WPSC meta boxes.
            $this->client_side_prefix.'-about'        => ['title' => __('About Restrictions', 'woocommerce-restrictions'), 'callback' => 'aboutRestrictionsMetaBox'],
            $this->client_side_prefix.'-post-ids'     => ['title' => __('Protected Posts/Pages', 'woocommerce-restrictions'), 'callback' => 'restrictsPostIdsMetaBox'],
            $this->client_side_prefix.'-post-types'   => ['title' => __('Protected Post Types', 'woocommerce-restrictions'), 'callback' => 'restrictsPostTypesMetaBox'],
            $this->client_side_prefix.'-author-ids'   => ['title' => __('Protected Authors', 'woocommerce-restrictions'), 'callback' => 'restrictsAuthorIdsMetaBox'],
            $this->client_side_prefix.'-tax-term-ids' => ['title' => __('Protected Categories/Tags', 'woocommerce-restrictions'), 'callback' => 'restrictsTaxTermIdsMetaBox'],
            $this->client_side_prefix.'-roles'        => ['title' => __('Protected Role Capabilities', 'woocommerce-restrictions'), 'callback' => 'restrictsRolesMetaBox'],
            $this->client_side_prefix.'-ccaps'        => ['title' => __('Protected Custom Capabilities', 'woocommerce-restrictions'), 'callback' => 'restrictsCcapsMetaBox'],
            $this->client_side_prefix.'-uri-patterns' => ['title' => __('Protected URI Patterns', 'woocommerce-restrictions'), 'callback' => 'restrictsUriPatternsMetaBox'],
        ];
        $closed_meta_boxes = get_user_option('closedpostboxes_restriction');

        foreach ($meta_boxes as $_id => $_data) {
            add_meta_box($_id, $_data['title'], [$this, $_data['callback']], null, 'normal', 'default', []);
            add_filter('postbox_classes_restriction_'.$_id, function (array $classes) use ($closed_meta_boxes, $_id): array {
                return !is_array($closed_meta_boxes) && (int) ($_GET['edit'] ?? '') !== $_id
                    && !in_array($_id, [$this->client_side_prefix.'-about'], true)
                    ? array_merge($classes, ['closed']) : $classes;
            });
        } // unset($_id, $_data); // Housekeeping.
    }

    /**
     * Default hidden meta boxes.
     *
     * @since 160524 Restrictions.
     *
     * @param array      $hidden Default hidden.
     * @param \WP_Screen $screen Screen object.
     *
     * @return array Default hidden meta boxes.
     */
    public function onDefaultHiddenMetaBoxes(array $hidden, \WP_Screen $screen)
    {
        if (!s::isMenuPageForPostType('restriction')) {
            return $hidden; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return $hidden; // Not applicable.
        } elseif (!$this->screen || $screen->id !== $this->screen->id) {
            return $hidden; // Not applicable.
        }
        return array_diff($hidden, ['slugdiv']);
    }

    /**
     * Enqueue styles/scripts.
     *
     * @since 160524 Restrictions.
     */
    public function onAdminEnqueueScripts()
    {
        if (!s::isMenuPageForPostType('restriction')) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        s::enqueueJQueryChosenLibs(); // Enqueue jQuery Chosen plugin.

        wp_enqueue_style($this->client_side_prefix.'-restriction-post-type', c::appUrl('/client-s/css/admin/restriction-post-type.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-restriction-post-type', c::appUrl('/client-s/js/admin/restriction-post-type.min.js'), ['jquery', 'jquery-chosen', 'underscore'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-restriction-post-type',
            $this->client_side_prefix.'RestrictionPostTypeData',
            s::applyFilters('restriction_post_type_client_side_data', [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'i18n' => [
                    'titlePlaceholder' => $this->screen_is_mobile
                        ? __('Descriptive summary here...', 'woocommerce-restrictions')
                        : __('Enter a descriptive summary here...', 'woocommerce-restrictions'),
                    'slugPlaceholder'     => __('Enter a unique identifier...', 'woocommerce-restrictions'),
                    'publishButtonCreate' => __('Create Restriction', 'woocommerce-restrictions'),
                    'suggestedLabel'      => __('Suggested', 'woocommerce-restrictions'),
                    'optionalLabel'       => __('Optional', 'woocommerce-restrictions'),
                ],
            ])
        );
    }

    /**
     * About meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function aboutRestrictionsMetaBox(\WP_Post $post, array $args = [])
    {
        echo '<div class="-about -section">';

        echo    '<h4>'.__('Each \'Restriction\' Serves Two Purposes:', 'woocommerce-restrictions').'</h4>';
        echo    '<ol>';
        echo        '<li>'.__('A Restriction allows you to protect content in WordPress. A single Restriction can protect multiple Posts, Pages, and more.', 'woocommerce-restrictions').'</li>';
        echo        '<li>'.__('It defines a set of permissions, because you can sell (via WooCommerce Products), or otherwise allow, access to what a Restriction protects.', 'woocommerce-restrictions').'</li>';
        echo    '</ol>';
        echo    '<p style="font-style:italic;">'.__('So you can think of <strong>Restrictions</strong> as both a form of <strong>protection</strong> and also as a way to prepare <strong>packages</strong> that can be accessed by others.', 'woocommerce-restrictions').'</p>';
        echo    '<p><span class="dashicons dashicons-book"></span> '.sprintf(__('If you\'d like to learn more about Restrictions, see: <a href="%1$s" target="_blank">%2$s Knowledge Base</a>', 'woocommerce-restrictions'), esc_url(s::brandUrl('/kb')), esc_html($this->App->Config->©brand['©name'])).'</p>';

        echo '</div>';
    }

    /**
     * Post IDs meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsPostIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name       = $this->client_side_prefix.'_post_ids';
        $field_id         = 'f-'.$this->client_side_prefix.'-post-ids';
        $current_post_ids = s::collectPostMeta($post->ID, '_post_ids');

        $post_id_select_options = s::postSelectOptions(
            s::applyFilters('restriction_ui_post_id_select_option_args', [
                'exclude_post_ids'           => a::systematicPostIds(),
                'include_post_types'         => get_post_types(['public' => true]),
                'exclude_post_types'         => array_merge(a::systematicPostTypes(), ['reply', 'redirect', 'snippet']),
                'exclude_password_protected' => false,
                'allow_empty'                => false,
                'current_post_ids'           => $current_post_ids,
            ])
        );
        echo '<div class="-post-ids -section">';

        if ($post_id_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$post_id_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 'woocommerce-restrictions').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Post IDs to Restrict (WordPress Post IDs, comma-delimited):', 'woocommerce-restrictions').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., 123, 345, 789, 3492', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_post_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Post of any type (e.g., Post, Page, Product) will protect the permalink leading to that Post. It will also protect any other child Posts in a hierarchy. For instance, protecting a parent Page also protects any child Pages, and protecting a bbPress Forum also protects all Topics/Replies in that Forum. This works for any type of Post in WordPress, including <a href="https://developer.wordpress.org/plugins/post-types/" target="_blank">Custom Post Types</a>.', 'woocommerce-restrictions').'</p>';

        echo '</div>';
    }

    /**
     * Post types meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsPostTypesMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name         = $this->client_side_prefix.'_post_types';
        $field_id           = 'f-'.$this->client_side_prefix.'-post-types';
        $current_post_types = s::collectPostMeta($post->ID, '_post_types');

        $post_type_select_options = s::postTypeSelectOptions(
            s::applyFilters('restriction_ui_post_type_select_option_args', [
                'filters' => [], // No other filters.
                'include' => get_post_types(['public' => true]),
                'exclude' => array_merge(a::systematicPostTypes(), ['product']),
                // Note that we exclude `product` here also, because protecting *all* product post types
                // would make no sense; i.e., that would prevent anyone from buying access.
                'allow_empty'        => false,
                'current_post_types' => $current_post_types,
            ])
        );
        echo '<div class="-post-types -section">';

        if ($post_type_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$post_type_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 'woocommerce-restrictions').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Post Types to Restrict (WordPress Post Types, comma-delimited):', 'woocommerce-restrictions').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., post, article, movie, book', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_post_types)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Post Type will automatically protect <em>all</em> Post permalinks associated with that Type.', 'woocommerce-restrictions').'</p>';

        echo '</div>';
    }

    /**
     * Author IDs meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsAuthorIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name         = $this->client_side_prefix.'_author_ids';
        $field_id           = 'f-'.$this->client_side_prefix.'-author-ids';
        $current_author_ids = s::collectPostMeta($post->ID, '_author_ids');

        $author_id_select_options = s::userSelectOptions(
            s::applyFilters('restriction_ui_author_id_select_option_args', [
                'allow_empty' => false,
                'filters'     => [
                    'orderby'  => 'display_name',
                    'role__in' => ['administrator', 'editor', 'author', 'contributor'],
                ],
                'current_user_ids' => $current_author_ids,
            ])
        );
        echo '<div class="-author-ids -section">';

        if ($author_id_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$author_id_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 'woocommerce-restrictions').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Author IDs to Restrict (WordPress User IDs, comma-delimited):', 'woocommerce-restrictions').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., 5, 239, 42', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_author_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting an Author will protect all permalinks leading to Posts (of any type) that were written by that Author.', 'woocommerce-restrictions').'</p>';

        echo '</div>';
    }

    /**
     * Tax:term IDs meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsTaxTermIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name           = $this->client_side_prefix.'_tax_term_ids';
        $field_id             = 'f-'.$this->client_side_prefix.'-tax-term-ids';
        $current_tax_term_ids = s::collectPostMeta($post->ID, '_tax_term_ids');

        $tax_term_id_select_options = s::termSelectOptions(
            s::applyFilters('restriction_ui_tax_term_id_select_option_args', [
                'allow_empty'              => false,
                'option_child_indent_char' => '|',
                'taxonomy_filters'         => ['public' => true],
                'taxonomies_exclude'       => ['restriction_category', 'product_type'],
                'current_tax_term_ids'     => $current_tax_term_ids,
            ])
        );
        echo '<div class="-tax-term-ids -section">';

        if ($tax_term_id_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$tax_term_id_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 'woocommerce-restrictions').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Taxonomy Terms to Restrict (<em style="font-style:normal; font-family:monospace;">[taxonomy]:[term ID]</em>s, comma-delimited):', 'woocommerce-restrictions').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., category:123, post_tag:456, product_cat:678, product_tag:789', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_tax_term_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Taxonomy Term of any type (e.g., Category, Tag) protects permalinks leading to Posts that are associated with that Term. For instance, a Post will be restricted automatically (now or in the future) if it\'s given a Tag that you\'ve protected. Or, if it\'s put into a Category that you\'ve protected (or into a child Category of a parent Category you\'ve protected).', 'woocommerce-restrictions').'</p>';

        echo '</div>';
    }

    /**
     * Roles meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsRolesMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name    = $this->client_side_prefix.'_roles';
        $field_id      = 'f-'.$this->client_side_prefix.'-roles';
        $current_roles = s::collectPostMeta($post->ID, '_roles');

        $role_select_options = s::roleSelectOptions(
            s::applyFilters('restriction_ui_role_select_option_args', [
                'exclude'       => a::systematicRoles(),
                'allow_empty'   => false,
                'current_roles' => $current_roles,
            ])
        );
        echo '<div class="-roles -section">';

        if ($role_select_options) {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('A <a href="https://developer.wordpress.org/plugins/users/roles-and-capabilities/" target="_blank">WordPress Role</a> is a predefined list of Capabilities. See also: <a href="https://wordpress.org/plugins/user-role-editor/" target="_blank">Role Editor</a>', 'woocommerce-restrictions').'</label>';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$role_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 'woocommerce-restrictions').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('<a href="https://developer.wordpress.org/plugins/users/roles-and-capabilities/" target="_blank">WordPress Roles</a> in comma-delimited format. See also: <a href="https://wordpress.org/plugins/user-role-editor/" target="_blank">Role Editor</a>', 'woocommerce-restrictions').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., contributor, pro_member, participant', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_roles)).'">';
            echo '</div>';
        }
        echo    '<p>'.sprintf(__('<strong>Note:</strong> Protecting a Role is to package the Capabilities associated with that Role. If a customer purchases access to a Restriction that protects a Role, they don\'t actually acquire the Role itself, but they do acquire the Capabilities provided by that Role; i.e., any Capabilities in the Role that a user doesn\'t already have, they acquire. Note also: There are a few Systematic Roles with special internal permissions and they cannot be associated with a Restriction. These include: <em>%1$s</em>.', 'woocommerce-restrictions'), esc_html(implode(', ', a::systematicRoles()))).'</p>';

        echo '</div>';
    }

    /**
     * CCAPs meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsCcapsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name    = $this->client_side_prefix.'_ccaps';
        $field_id      = 'f-'.$this->client_side_prefix.'-ccaps';
        $current_ccaps = s::collectPostMeta($post->ID, '_ccaps');

        echo '<div class="-ccaps -section">';

        echo    '<div class="-field">';
        echo        '<label for="'.esc_attr($field_id).'">'.__('CCAPs (<a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank">Custom Capabilities</a>) in comma-delimited format:', 'woocommerce-restrictions').'</label>';
        echo        '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., members_area, pro_membership, premium_content', 'woocommerce-restrictions').'" value="'.esc_attr(implode(', ', $current_ccaps)).'">';
        echo    '</div>';
        echo    '<p>'.sprintf(__('<strong>Note:</strong> Custom Capabilities are automatically prefixed with <code>%1$s</code> internally. You can test for them using: <a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank" style="text-decoration:none;">current_user_can(\'%1$s<code style="padding:0;">something</code>\')</a>, where <code>something</code> is one of the CCAPs you entered here. You can also test for access to an entire Restriction package (a more common use case) via <a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank" style="text-decoration:none;">current_user_can(\'%2$s<code style="padding:0;">slug</code>\')</a>, where <code>slug</code> is the unique identifier you assigned to a Restriction—and that works with or without CCAPs.', 'woocommerce-restrictions'), esc_html($this->access_ccap_prefix), esc_html($this->access_pkg_prefix)).'</p>';

        echo '</div>';
    }

    /**
     * URI patterns meta box.
     *
     * @since 160524 Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsUriPatternsMetaBox(\WP_Post $post, array $args = [])
    {
        global $wp_rewrite; // Needed for conditionals below.

        $field_name           = $this->client_side_prefix.'_uri_patterns';
        $field_id             = 'f-'.$this->client_side_prefix.'-uri-patterns';
        $current_uri_patterns = s::collectPostMeta($post->ID, '_uri_patterns');

        echo '<div class="-uri-patterns -section">';

        echo    '<div class="-field">';
        echo        '<label for="'.esc_attr($field_id).'">'.__('URI Patterns (line-delmited; i.e., one on each line):', 'woocommerce-restrictions').'</label>';
        echo        '<textarea id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" wrap="off" placeholder="'.__('e.g., /path/to/members-only/**', 'woocommerce-restrictions').'">'.esc_textarea(implode("\n", $current_uri_patterns)).'</textarea>';
        echo    '</div>';
        echo    '<p style="margin-bottom:0;">'.__('<strong>Tip:</strong> This allows you to protect almost <em>any</em> other location that is served by WordPress. &nbsp; <a href="#" data-toggle=".-uri-patterns.-section .-hidden.-instructions.-section"><span class="dashicons dashicons-visibility"></span> toggle instructions</a>', 'woocommerce-restrictions').'</p>';

        echo    '<div class="-hidden -instructions -section">';
        echo        '<h4>'.__('A \'URI\' is everything after the domain name in a URL:', 'woocommerce-restrictions').'</h4>';
        echo        '<ul class="-syntax-examples"><li>'.sprintf(__('http://example.com<code>/this/is/the/URI/part/in/a/location%1$s</code>', 'woocommerce-restrictions'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li></ul>';

        echo        '<h4>'.__('WRegx™ (Watered-Down Regex) can be used in your patterns:', 'woocommerce-restrictions').'</h4>';
        echo        '<ul class="-syntax-examples">'; // Expects the use of an wregx (watered-down regex) syntax.
        echo            '<li>'.__('<code>*</code> Matches zero or more characters that are not a <em><strong>/</strong></em>', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>**</code> Matches zero or more characters of any kind.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>?</code> Matches exactly one character that is not a <em><strong>/</strong></em>', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>??</code> Matches exactly one character of any kind.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>[abc]</code> Matches exactly one character: <em>a</em>, <em>b</em>, or <em>c</em>.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>[a-z0-9]</code> Matches exactly one character: <em>a</em> thru <em>z</em> or <em>0</em> thru <em>9</em>.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>[!abc]</code> A leading <em>!</em> inside <em>[]</em> negates; i.e., anything that is not: <em>a</em>, <em>b</em>, or <em>c</em>.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>{abc,def}</code> Matches the fragment <em>abc</em> or <em>def</em> (one or the other).', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>{abc,def,}</code> Matches <em>abc</em>, <em>def</em> or nothing; i.e., an optional match.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>{/**,}</code> Matches a <em>/</em> followed by zero or more characters. Or nothing.', 'woocommerce-restrictions').'</li>';
        echo            '<li>'.__('<code>[*?[]!{},]</code> Matches a literal special character. One of: <em>*?[]!{},</em> explicitly.', 'woocommerce-restrictions').'</li>';
        echo        '</ul>';

        echo        '<h4 style="margin-bottom:0;">'.__('Other details worth mentioning... <a href="#" data-toggle=".-uri-patterns.-section .-hidden.-details.-section" style="font-weight:normal;">toggle more info</a>', 'woocommerce-restrictions').'</h4>';
        echo        '<div class="-hidden -details -section">';
        echo            '<ul class="-syntax-tips">'; // Expects the use of an wregx (watered-down regex) syntax.
        echo                '<li>'.__('Comparison is always caSe-insensitive (i.e., case does not matter).', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.__('Your pattern must match an entire URI (beginning to end). Not just a small portion.', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.sprintf(__('A URI always starts with a slash (e.g., <em>/example-post%1$s</em>). The smallest possible URI (the home page) is: <em><strong>/</strong></em>', 'woocommerce-restrictions'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li>';
        if ($wp_rewrite->use_trailing_slashes) {
            echo            '<li>'.__('Your current Permalink Settings in WordPress indicate that all URIs on this site will have a trailing slash on the end. You must match that trailing slash in your patterns.', 'woocommerce-restrictions').'</li>';
        } else {
            echo            '<li>'.__('Your current Permalink Settings in WordPress indicate that URIs on this site will not end with a trailing slash. Your patterns should not depend on there always being a trailing slash.', 'woocommerce-restrictions').'</li>';
        }
        echo                '<li>'.sprintf(__('In WordPress it is common for any given URI to accept additional endpoint directives. For instance, paginated locations: <em>/example-post/page/2%1$s</em>, <em>/example-post/comments-page/2%1$s</em>. Therefore, we suggest a pattern that covers all possible endpoint variations. For instance: <em>/example-post{/**,}</em> will match the base URI by itself, and also match a possible trailing slash with any endpoint directives it may accept.', 'woocommerce-restrictions'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li>';
        echo                '<li>'.__('Any query string variables on the end of a URI (example: <em>?p=123&amp;key=value</em>) are stripped before comparison so you don\'t need to worry about them. However, if your pattern contains: <em>[?]</em> (literally, a <em>?</em> question mark in square brackets) it indicates that you DO want to check the query string, and they are NOT stripped away in that case; so your pattern will be capable of matching. Just remember that query string variables can appear in any order, as entered by a user. If you check for query strings, use <em>{**&,}</em> and <em>{&**,}</em> around the key=value pair you\'re looking for. For instance: <em>/example-post{/**,}[?]{**&,}key=value{&**,}</em>. If you\'re forced to look for multiple variables, the best you can do is: <em>{**&,}key=value{&**&,&}another=value{&**,}</em>. This still expects <em>key=value</em> to be first, but <em>{&**&,&}</em> helps find the second one amidst others.', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.__('It is possible to protect (and grant) access to portions of <em>/wp-admin/</em> with URI Patterns too. However, please remember that in order for a user to actually do anything inside the admin panel they will also need to have Capabilities which grant them additional permissions; such as the ability to <em>edit_posts</em>. See: <strong>Role Capabilities</strong> as a form of protection if you\'d like more information.', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.__('It is possible to restrict access to every page on the entire site using the pattern <em>/**</em> as a catch-all. In this scenario, everything is off-limits, except for the Systematic URIs listed below. Having said that, please be careful when using a catch-all pattern. Everything (yes, everything) will be off-limits, including your home page! We suggest this as a last resort only. Instead, restrict Posts, Pages, Categories, Tags and/or other specific URIs; i.e., it is best to restrict only portions of a site from public access.', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.__('Restrictions rely upon PHP as a server-side scripting language. Therefore, you can protect any location (page) served by WordPress via PHP, but you can\'t protect static files. For instance: <em>.jpg</em>, <em>.pdf</em>, and <em>.zip</em> are static. Generally speaking, if you upload something to the Media Library, it\'s a static asset. It cannot be protected here. Instead, configure a "Downloadable Product" with WooCommerce.', 'woocommerce-restrictions').'</li>';
        echo                '<li>'.__('There are a few Systematic URIs on your site that cannot be associated with a Restriction. It\'s OK if one of your patterns overlaps with these, but any URI matching one of these will simply not be allowed to have any additional Restrictions applied to it whatsoever. In other words, these are automatically excluded (internally), because they are associated with special functionality.', 'woocommerce-restrictions').
                                '<ul class="-syntax-tips">'.
                                    '<li style="margin:0;"><strong>URI:</strong> <em>'.implode('</em></li><li style="margin:0;"><strong>URI:</strong> <em>', array_map('esc_html', a::systematicUriPatterns(false))).'</em></li>'.
                                    '<li style="margin:0;"><strong>Post/Page IDs:</strong> <em>'.implode(',', array_map('esc_html', a::systematicPostIds())).'</em></li>'.
                                    '<li style="margin:0;"><strong>Post Types:</strong> <em>'.implode(',', array_map('esc_html', a::systematicPostTypes())).'</em></li>'.
                                    '<li style="margin:0;"><strong>Users w/ Role:</strong> <em>'.implode(',', array_map('esc_html', a::systematicRoles())).'</em></li>'.
                                '</ul>'.
                            '</li>';
        echo            '</ul>';
        echo        '</div>';
        echo    '</div>';

        echo '</div>';
    }

    /**
     * Get meta values.
     *
     * @since 160524 Restrictions.
     *
     * @param string|int $post_id Post ID.
     */
    public function onSaveRestriction($post_id)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif (get_post_type($post_id) !== 'restriction') {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        foreach ($this->meta_keys as $_meta_key) {
            $_split_regex        = $_meta_key === 'uri_patterns' ? '/['."\r\n".']+/' : '/[\s,]+/';
            $_array_map_callback = in_array($_meta_key, $this->int_meta_keys, true) ? 'intval' : 'strval';

            $_meta_values = c::unslash($_REQUEST[$this->client_side_prefix.'_'.$_meta_key] ?? []);
            $_meta_values = is_string($_meta_values) ? preg_split($_split_regex, $_meta_values, -1, PREG_SPLIT_NO_EMPTY) : $_meta_values;
            $_meta_values = array_map($_array_map_callback, is_array($_meta_values) ? $_meta_values : []);
            $_meta_values = array_unique(c::removeEmptys($_meta_values));

            s::setPostMeta($post_id, '_'.$_meta_key, $_meta_values);
        } // unset($_meta_key, $_split_regex, $_array_map_callback, $_meta_values); // Housekeeping.

        a::clearRestrictionsCache(); // Clear the cache now.
    }

    /**
     * Create restriction URL.
     *
     * @since 160524 Restrictions.
     *
     * @return string Create restriction URL.
     */
    public function createUrl(): string
    {
        return admin_url('/post-new.php?post_type=restriction');
    }
}
