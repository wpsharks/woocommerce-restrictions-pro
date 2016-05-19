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
 * Restriction.
 *
 * @since 16xxxx Security gate.
 */
class Restriction extends SCoreClasses\SCore\Base\Core
{
    /**
     * Post type.
     *
     * @since 16xxxx Restriction.
     *
     * @type string Post type.
     */
    public $post_type;

    /**
     * Meta prefix.
     *
     * @since 16xxxx Restriction.
     *
     * @type string Meta prefix.
     */
    public $meta_prefix;

    /**
     * Access RES prefix.
     *
     * @since 16xxxx Restriction.
     *
     * @type string Access RES prefix.
     */
    public $access_res_prefix;

    /**
     * Access CCAP prefix.
     *
     * @since 16xxxx Restriction.
     *
     * @type string Access CCAP prefix.
     */
    public $access_ccap_prefix;

    /**
     * Client-side prefix.
     *
     * @since 16xxxx Restriction.
     *
     * @type string Client-side prefix.
     */
    public $client_side_prefix;

    /**
     * Meta keys.
     *
     * @since 16xxxx Restriction.
     *
     * @type array Meta keys.
     */
    public $meta_keys;

    /**
     * Meta keys.
     *
     * @since 16xxxx Restriction.
     *
     * @type array Meta keys.
     */
    public $int_meta_keys;

    /**
     * Current screen.
     *
     * @since 16xxxx Restriction.
     *
     * @type \WP_Screen|null Screen.
     */
    protected $screen;

    /**
     * Is screen mobile?
     *
     * @since 16xxxx Restriction.
     *
     * @type bool Is screen mobile?
     */
    protected $screen_is_mobile;

    /**
     * Class constructor.
     *
     * @since 16xxxx Restrictions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->post_type   = $this->App->Config->©brand['©prefix'].'_restriction';
        $this->meta_prefix = $this->App->Config->©brand['©var'].'_restriction_';

        $this->access_res_prefix  = s::applyFilters('restriction_res_prefix', 'access_res_');
        $this->access_ccap_prefix = s::applyFilters('restriction_ccap_prefix', 'access_ccap_');
        $this->client_side_prefix = 'fdbmjuxwzjfjtaucytprkbcqfpftudyg'; // JS, CSS, forms, etc.

        $this->meta_keys     = ['post_ids', 'post_types', 'tax_term_ids', 'author_ids', 'roles', 'ccaps', 'uri_patterns'];
        $this->int_meta_keys = ['post_ids', 'author_ids']; // Integer data type.

        $this->screen_is_mobile = false; // Initialize.
    }

    /**
     * Register post type.
     *
     * @since 16xxxx Restrictions.
     */
    public function onInitRegisterPostType()
    {
        register_post_type(
            $this->post_type, // Args can be filtered by plugins.
            s::applyFilters('register_post_type_'.$this->post_type.'_args', [
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
                'description'   => __('Content restriction for membership.', 's2member-x'),

                'labels' => [ // See: <http://jas.xyz/244m2Sd>
                    'name'                  => __('Restrictions', 's2member-x'),
                    'singular_name'         => __('Restriction', 's2member-x'),
                    'add_new'               => __('Add Restriction', 's2member-x'),
                    'add_new_item'          => __('Add New Restriction', 's2member-x'),
                    'edit_item'             => __('Edit Restriction', 's2member-x'),
                    'new_item'              => __('New Restriction', 's2member-x'),
                    'view_item'             => __('View Restriction', 's2member-x'),
                    'search_items'          => __('Search Restrictions', 's2member-x'),
                    'not_found'             => __('No Restrictions found', 's2member-x'),
                    'not_found_in_trash'    => __('No Restrictions found in Trash', 's2member-x'),
                    'parent_item_colon'     => __('Parent Restriction:', 's2member-x'),
                    'archives'              => __('Restriction Archives', 's2member-x'),
                    'insert_into_item'      => __('Insert into Restriction', 's2member-x'),
                    'uploaded_to_this_item' => __('Upload to this Restriction', 's2member-x'),
                    'featured_image'        => __('Set Featured Image', 's2member-x'),
                    'remove_featured_image' => __('Remove Featured Image', 's2member-x'),
                    'use_featured_image'    => __('Use as Featured Image', 's2member-x'),
                    'filter_items_list'     => __('Filter Restrictions List', 's2member-x'),
                    'items_list_navigation' => __('Restrictions List Navigation', 's2member-x'),
                    'items_list'            => __('Restrictions List', 's2member-x'),
                    'name_admin_bar'        => __('Restriction', 's2member-x'),
                    'menu_name'             => __('Restrictions', 's2member-x'),
                    'all_items'             => __('All Restrictions', 's2member-x'),
                ],

                'map_meta_cap'    => true,
                'capability_type' => [
                    $this->post_type,
                    $this->post_type.'s',
                ],
            ])
        );
        register_taxonomy(
            $this->post_type.'_category',
            $this->post_type, // Args can be filtered by plugins.
            s::applyFilters('register_taxonomy_'.$this->post_type.'_category_args', [
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

                'description' => __('Content restriction tags/categories.', 's2member-x'),

                'labels' => [ // See: <http://jas.xyz/244m1Oc>
                    'name'                       => __('Categories', 's2member-x'),
                    'singular_name'              => __('Category', 's2member-x'),
                    'search_items'               => __('Search Categories', 's2member-x'),
                    'popular_items'              => __('Popular Categories', 's2member-x'),
                    'all_items'                  => __('All Categories', 's2member-x'),
                    'parent_item'                => __('Parent Category', 's2member-x'),
                    'parent_item_colon'          => __('Parent Category:', 's2member-x'),
                    'edit_item'                  => __('Edit Category', 's2member-x'),
                    'view_item'                  => __('View Category', 's2member-x'),
                    'update_item'                => __('Update Category', 's2member-x'),
                    'add_new_item'               => __('Add New Category', 's2member-x'),
                    'new_item_name'              => __('New Category Name', 's2member-x'),
                    'separate_items_with_commas' => __('Separate Categories w/ Commas', 's2member-x'),
                    'add_or_remove_items'        => __('Add or Remove Categories', 's2member-x'),
                    'choose_from_most_used'      => __('Choose From the Most Used Categories', 's2member-x'),
                    'not_found'                  => __('No Categories Found', 's2member-x'),
                    'no_terms'                   => __('No Categories', 's2member-x'),
                    'items_list_navigation'      => __('Categories List Navigation', 's2member-x'),
                    'items_list'                 => __('Categories List', 's2member-x'),
                    'name_admin_bar'             => __('Category', 's2member-x'),
                    'menu_name'                  => __('Categories', 's2member-x'),
                    'archives'                   => __('All Categories', 's2member-x'),
                ],

                'capabilities' => [
                    'assign_terms' => 'edit_'.$this->post_type.'s',
                    'edit_terms'   => 'edit_'.$this->post_type.'s',
                    'manage_terms' => 'edit_others_'.$this->post_type.'s',
                    'delete_terms' => 'delete_others_'.$this->post_type.'s',
                ],
            ])
        );
    }

    /**
     * Current user can edit restrictions?
     *
     * @since 16xxxx Restrictions.
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
     * @since 16xxxx Restrictions.
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
     * Position restrictions.
     *
     * @since 16xxxx Restrictions.
     */
    public function onMenuOrder(array $menu_items): array
    {
        // No conditional checks up here.
        // If the menu items exist they are reordered.

        $woocommerce_item = 'woocommerce'; // Position after this.
        $woocommerce_key  = array_search($woocommerce_item, $menu_items, true);

        $restriction_item = 'edit.php?post_type='.$this->post_type;
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
     * @since 16xxxx Restrictions.
     *
     * @param string $post_type Post type.
     */
    public function onAddMetaBoxes(string $post_type)
    {
        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        } elseif ($post_type !== $this->post_type) {
            return; // Not applicable.
        }
        $meta_boxes = [
            $this->client_side_prefix.'-about'        => ['title' => __('About Restrictions', 's2member-x'), 'callback' => 'aboutRestrictionsMetaBox'],
            $this->client_side_prefix.'-post-ids'     => ['title' => __('Protected Posts/Pages', 's2member-x'), 'callback' => 'restrictsPostIdsMetaBox'],
            $this->client_side_prefix.'-post-types'   => ['title' => __('Protected Post Types', 's2member-x'), 'callback' => 'restrictsPostTypesMetaBox'],
            $this->client_side_prefix.'-author-ids'   => ['title' => __('Protected Authors', 's2member-x'), 'callback' => 'restrictsAuthorIdsMetaBox'],
            $this->client_side_prefix.'-tax-term-ids' => ['title' => __('Protected Categories/Tags', 's2member-x'), 'callback' => 'restrictsTaxTermIdsMetaBox'],
            $this->client_side_prefix.'-roles'        => ['title' => __('Protected Role Capabilities', 's2member-x'), 'callback' => 'restrictsRolesMetaBox'],
            $this->client_side_prefix.'-ccaps'        => ['title' => __('Protected Custom Capabilities', 's2member-x'), 'callback' => 'restrictsCcapsMetaBox'],
            $this->client_side_prefix.'-uri-patterns' => ['title' => __('Protected URI Patterns', 's2member-x'), 'callback' => 'restrictsUriPatternsMetaBox'],
        ];
        $closed_meta_boxes = get_user_option('closedpostboxes_'.$this->post_type);

        foreach ($meta_boxes as $_id => $_data) {
            add_meta_box($_id, $_data['title'], [$this, $_data['callback']], null, 'normal', 'default', []);
            add_filter('postbox_classes_'.$this->post_type.'_'.$_id, function (array $classes) use ($closed_meta_boxes, $_id) : array {
                return !is_array($closed_meta_boxes) && (int) ($_GET['edit'] ?? '') !== $_id
                    && !in_array($_id, [$this->client_side_prefix.'-about'], true)
                    ? array_merge($classes, ['closed']) : $classes;
            });
        } // unset($_id, $_data); // Housekeeping.
    }

    /**
     * Default hidden meta boxes.
     *
     * @since 16xxxx Restrictions.
     *
     * @param array      $hidden Default hidden.
     * @param \WP_Screen $screen Screen object.
     *
     * @return array Default hidden meta boxes.
     */
    public function onDefaultHiddenMetaBoxes(array $hidden, \WP_Screen $screen)
    {
        if (!s::isMenuPageForPostType($this->post_type)) {
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
     * @since 16xxxx Restrictions.
     */
    public function onAdminEnqueueScripts()
    {
        if (!s::isMenuPageForPostType($this->post_type)) {
            return; // Not applicable.
        } elseif (!$this->currentUserCan()) {
            return; // Not applicable.
        }
        s::enqueueJQueryChosenLibs(); // Enqueue jQuery Chosen plugin.

        wp_enqueue_style($this->client_side_prefix.'-restriction-post-type', c::appUrl('/client-s/css/admin/restriction-post-type.min.css'), [], $this->App::VERSION, 'all');
        wp_enqueue_script($this->client_side_prefix.'-restriction-post-type', c::appUrl('/client-s/js/admin/restriction-post-type.min.js'), ['jquery', 'underscore', 'jquery-chosen'], $this->App::VERSION, true);

        wp_localize_script(
            $this->client_side_prefix.'-restriction-post-type',
            $this->client_side_prefix.'RestrictionPostTypeData',
            [
                'is' => [
                    'mobile' => $this->screen_is_mobile,
                ],
                'i18n' => [
                    'titlePlaceholder' => $this->screen_is_mobile
                        ? __('Descriptive summary here...', 's2member-x')
                        : __('Enter a descriptive summary here...', 's2member-x'),
                    'slugPlaceholder' => __('Enter a unique identifier...', 's2member-x'),
                    'suggestedLabel'  => __('Suggested', 's2member-x'),
                    'optionalLabel'   => __('Optional', 's2member-x'),
                ],
            ]
        );
    }

    /**
     * About meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function aboutRestrictionsMetaBox(\WP_Post $post, array $args = [])
    {
        echo '<div class="-about -section">';

        echo    '<h4>'.__('Each \'Restriction\' Serves Two Purposes:', 's2member-x').'</h4>';
        echo    '<ol>';
        echo        '<li>'.__('A Restriction allows you to protect content in WordPress. A single Restriction can protect multiple Posts, Pages, and more.', 's2member-x').'</li>';
        echo        '<li>'.__('It defines a set of permissions, because you can sell via WooCommerce Products, or otherwise allow, access to what a Restriction protects.', 's2member-x').'</li>';
        echo    '</ol>';
        echo    '<p style="font-style:italic;">'.__('So you can think of <strong>Restrictions</strong> as both a form of <strong>protection</strong> and also as a way to prepare <strong>packages</strong> that can be accessed by others.', 's2member-x').'</p>';
        echo    '<p><span class="dashicons dashicons-book"></span> '.sprintf(__('If you\'d like to learn more about Restrictions, see: <a href="%1$s" target="_blank">%2$s Knowledge Base</a>', 's2member-x'), esc_url(s::brandUrl('/kb')), esc_html($this->App->Config->©brand['©name'])).'</p>';

        echo '</div>';
    }

    /**
     * Post IDs meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsPostIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name       = $this->client_side_prefix.'_post_ids';
        $field_id         = 'f-'.$this->client_side_prefix.'-post-ids';
        $current_post_ids = $this->getMeta($post->ID, 'post_ids');

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
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 's2member-x').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Post IDs to Restrict (WordPress Post IDs, comma-delimited):', 's2member-x').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., 123, 345, 789, 3492', 's2member-x').'" value="'.esc_attr(implode(', ', $current_post_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Post of any type (e.g., Post, Page, Product) will protect the permalink leading to that Post. It will also protect any other child Posts in a hierarchy. For instance, protecting a parent Page also protects any child Pages, and protecting a bbPress Forum also protects all Topics/Replies in that Forum. This works for any type of Post in WordPress, including <a href="https://developer.wordpress.org/plugins/post-types/" target="_blank">Custom Post Types</a>.', 's2member-x').'</p>';

        echo '</div>';
    }

    /**
     * Post types meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsPostTypesMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name         = $this->client_side_prefix.'_post_types';
        $field_id           = 'f-'.$this->client_side_prefix.'-post-types';
        $current_post_types = $this->getMeta($post->ID, 'post_types');

        $post_type_select_options = s::postTypeSelectOptions(
            s::applyFilters('restriction_ui_post_type_select_option_args', [
                'include'            => get_post_types(['public' => true]),
                'exclude'            => a::systematicPostTypes(),
                'allow_empty'        => false,
                'current_post_types' => $current_post_types,
            ])
        );
        echo '<div class="-post-types -section">';

        if ($post_type_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$post_type_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 's2member-x').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Post Types to Restrict (WordPress Post Types, comma-delimited):', 's2member-x').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., post, article, movie, book', 's2member-x').'" value="'.esc_attr(implode(', ', $current_post_types)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Post Type will automatically protect <em>all</em> Post permalinks associated with that Type.', 's2member-x').'</p>';

        echo '</div>';
    }

    /**
     * Author IDs meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsAuthorIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name         = $this->client_side_prefix.'_author_ids';
        $field_id           = 'f-'.$this->client_side_prefix.'-author-ids';
        $current_author_ids = $this->getMeta($post->ID, 'author_ids');

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
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 's2member-x').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Author IDs to Restrict (WordPress User IDs, comma-delimited):', 's2member-x').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., 5, 239, 42', 's2member-x').'" value="'.esc_attr(implode(', ', $current_author_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting an Author will protect all permalinks leading to Posts (of any type) that were written by that Author.', 's2member-x').'</p>';

        echo '</div>';
    }

    /**
     * Tax:term IDs meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsTaxTermIdsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name           = $this->client_side_prefix.'_tax_term_ids';
        $field_id             = 'f-'.$this->client_side_prefix.'-tax-term-ids';
        $current_tax_term_ids = $this->getMeta($post->ID, 'tax_term_ids');

        $tax_term_id_select_options = s::termSelectOptions(
            s::applyFilters('restriction_ui_tax_term_id_select_option_args', [
                'allow_empty'              => false,
                'option_child_indent_char' => '|',
                'current_tax_term_ids'     => $current_tax_term_ids,
            ])
        );
        echo '<div class="-tax-term-ids -section">';

        if ($tax_term_id_select_options) {
            echo '<div class="-field">';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$tax_term_id_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 's2member-x').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('Taxonomy Terms to Restrict (<em style="font-style:normal; font-family:monospace;">[taxonomy]:[term ID]</em>s, comma-delimited):', 's2member-x').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., category:123, post_tag:456, product_cat:678, product_tag:789', 's2member-x').'" value="'.esc_attr(implode(', ', $current_tax_term_ids)).'">';
            echo '</div>';
        }
        echo    '<p>'.__('<strong>Note:</strong> Protecting a Taxonomy Term of any type (e.g., Category, Tag) protects permalinks leading to Posts that are associated with that Term. For instance, a Post will be restricted automatically (now or in the future) if it\'s given a Tag that you\'ve protected. Or, if it\'s put into a Category that you\'ve protected (or into a child Category of a parent Category you\'ve protected).', 's2member-x').'</p>';

        echo '</div>';
    }

    /**
     * Roles meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsRolesMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name    = $this->client_side_prefix.'_roles';
        $field_id      = 'f-'.$this->client_side_prefix.'-roles';
        $current_roles = $this->getMeta($post->ID, 'roles');

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
            echo    '<label for="'.esc_attr($field_id).'">'.__('A <a href="https://developer.wordpress.org/plugins/users/roles-and-capabilities/" target="_blank">WordPress Role</a> is a predefined list of Capabilities. See also: <a href="https://wordpress.org/plugins/user-role-editor/" target="_blank">Role Editor</a>', 's2member-x').'</label>';
            echo    '<select id="'.esc_attr($field_id).'" name="'.esc_attr($field_name.'[]').'" autocomplete="off" data-toggle="'.($this->screen_is_mobile ? '' : 'jquery-chosen').'" multiple>'.$role_select_options.'</select>';
            echo '</div>';
            echo $this->screen_is_mobile ? '<p>'.__('<strong>Tip:</strong> Use <kbd>Ctrl</kbd> or <kbd>⌘</kbd> to select multiple options.', 's2member-x').'</p>' : '';
        } else {
            echo '<div class="-field">';
            echo    '<label for="'.esc_attr($field_id).'">'.__('<a href="https://developer.wordpress.org/plugins/users/roles-and-capabilities/" target="_blank">WordPress Roles</a> in comma-delimited format. See also: <a href="https://wordpress.org/plugins/user-role-editor/" target="_blank">Role Editor</a>', 's2member-x').'</label>';
            echo    '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., contributor, pro_member, participant', 's2member-x').'" value="'.esc_attr(implode(', ', $current_roles)).'">';
            echo '</div>';
        }
        echo    '<p>'.sprintf(__('<strong>Note:</strong> Protecting a Role is to package the Capabilities associated with that Role. If a customer purchases access to a Restriction that protects a Role, they don\'t actually acquire the Role itself, but they do acquire the Capabilities provided by that Role; i.e., any Capabilities in the Role that a user doesn\'t already have, they acquire. Note also: There are a few Systematic Roles with special internal permissions and they cannot be associated with a Restriction. These include: <em>%1$s</em>.', 's2member-x'), esc_html(implode(', ', a::systematicRoles()))).'</p>';

        echo '</div>';
    }

    /**
     * CCAPs meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsCcapsMetaBox(\WP_Post $post, array $args = [])
    {
        $field_name    = $this->client_side_prefix.'_ccaps';
        $field_id      = 'f-'.$this->client_side_prefix.'-ccaps';
        $current_ccaps = $this->getMeta($post->ID, 'ccaps');

        echo '<div class="-ccaps -section">';

        echo    '<div class="-field">';
        echo        '<label for="'.esc_attr($field_id).'">'.__('CCAPs (<a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank">Custom Capabilities</a>) in comma-delimited format:', 's2member-x').'</label>';
        echo        '<input type="text" id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" placeholder="'.__('e.g., members_area, pro_membership, premium_content', 's2member-x').'" value="'.esc_attr(implode(', ', $current_ccaps)).'">';
        echo    '</div>';
        echo    '<p>'.sprintf(__('<strong>Note:</strong> Custom Capabilities are automatically prefixed with <code>%1$s</code> internally. You can test for them using: <a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank" style="text-decoration:none;">current_user_can(\'%1$s<code style="padding:0;">something</code>\')</a>, where <code>something</code> is one of the CCAPs you entered here. You can also test for access to an entire Restriction (a more common use case) via <a href="https://developer.wordpress.org/reference/functions/current_user_can/" target="_blank" style="text-decoration:none;">current_user_can(\'%2$s<code style="padding:0;">slug</code>\')</a>, where <code>slug</code> is the unique identifier you assigned to a Restriction—which works with or without CCAPs.', 's2member-x'), esc_html($this->access_ccap_prefix), esc_html($this->access_res_prefix)).'</p>';

        echo '</div>';
    }

    /**
     * URI patterns meta box.
     *
     * @since 16xxxx Restrictions.
     *
     * @param \WP_Post $post Post object.
     * @param array    $args Callback args, if any.
     */
    public function restrictsUriPatternsMetaBox(\WP_Post $post, array $args = [])
    {
        global $wp_rewrite; // Needed for conditionals below.

        $field_name           = $this->client_side_prefix.'_uri_patterns';
        $field_id             = 'f-'.$this->client_side_prefix.'-uri-patterns';
        $current_uri_patterns = $this->getMeta($post->ID, 'uri_patterns');

        echo '<div class="-uri-patterns -section">';

        echo    '<div class="-field">';
        echo        '<label for="'.esc_attr($field_id).'">'.__('URI Patterns (line-delmited; i.e., one on each line):', 's2member-x').'</label>';
        echo        '<textarea id="'.esc_attr($field_id).'" name="'.esc_attr($field_name).'" autocomplete="off" spellcheck="false" wrap="off" placeholder="'.__('e.g., /path/to/members-only/**', 's2member-x').'">'.esc_textarea(implode("\n", $current_uri_patterns)).'</textarea>';
        echo    '</div>';
        echo    '<p style="margin-bottom:0;">'.__('<strong>Tip:</strong> This allows you to protect almost <em>any</em> other location that is served by WordPress. &nbsp; <a href="#" data-toggle=".-uri-patterns.-section .-hidden.-instructions.-section"><span class="dashicons dashicons-visibility"></span> toggle instructions</a>', 's2member-x').'</p>';

        echo    '<div class="-hidden -instructions -section">';
        echo        '<h4>'.__('A \'URI\' is everything after the domain name in a URL:', 's2member-x').'</h4>';
        echo        '<ul class="-syntax-examples"><li>'.sprintf(__('http://example.com<code>/this/is/the/URI/part/in/a/location%1$s</code>', 's2member-x'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li></ul>';

        echo        '<h4>'.__('WRegx™ (Watered-Down Regex) can be used in your patterns:', 's2member-x').'</h4>';
        echo        '<ul class="-syntax-examples">'; // Expects the use of an wregx (watered-down regex) syntax.
        echo            '<li>'.__('<code>*</code> Matches zero or more characters that are not a <em><strong>/</strong></em>', 's2member-x').'</li>';
        echo            '<li>'.__('<code>**</code> Matches zero or more characters of any kind.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>?</code> Matches exactly one character that is not a <em><strong>/</strong></em>', 's2member-x').'</li>';
        echo            '<li>'.__('<code>??</code> Matches exactly one character of any kind.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>[abc]</code> Matches exactly one character: <em>a</em>, <em>b</em>, or <em>c</em>.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>[a-z0-9]</code> Matches exactly one character: <em>a</em> thru <em>z</em> or <em>0</em> thru <em>9</em>.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>[!abc]</code> A leading <em>!</em> inside <em>[]</em> negates; i.e., anything that is not: <em>a</em>, <em>b</em>, or <em>c</em>.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>{abc,def}</code> Matches the fragment <em>abc</em> or <em>def</em> (one or the other).', 's2member-x').'</li>';
        echo            '<li>'.__('<code>{abc,def,}</code> Matches <em>abc</em>, <em>def</em> or nothing; i.e., an optional match.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>{/**,}</code> Matches a <em>/</em> followed by zero or more characters. Or nothing.', 's2member-x').'</li>';
        echo            '<li>'.__('<code>[*?[]!{},]</code> Matches a literal special character. One of: <em>*?[]!{},</em> explicitly.', 's2member-x').'</li>';
        echo        '</ul>';

        echo        '<h4 style="margin-bottom:0;">'.__('Other details worth mentioning... <a href="#" data-toggle=".-uri-patterns.-section .-hidden.-details.-section" style="font-weight:normal;">toggle more info</a>', 's2member-x').'</h4>';
        echo        '<div class="-hidden -details -section">';
        echo            '<ul class="-syntax-tips">'; // Expects the use of an wregx (watered-down regex) syntax.
        echo                '<li>'.__('Comparison is always caSe-insensitive (i.e., case does not matter).', 's2member-x').'</li>';
        echo                '<li>'.__('Your pattern must match an entire URI (beginning to end). Not just a small portion.', 's2member-x').'</li>';
        echo                '<li>'.sprintf(__('A URI always starts with a slash (e.g., <em>/example-post%1$s</em>). The smallest possible URI (the home page) is: <em><strong>/</strong></em>', 's2member-x'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li>';
        if ($wp_rewrite->use_trailing_slashes) {
            echo            '<li>'.__('Your current Permalink Settings in WordPress indicate that all URIs on this site will have a trailing slash on the end. You must match that trailing slash in your patterns.', 's2member-x').'</li>';
        } else {
            echo            '<li>'.__('Your current Permalink Settings in WordPress indicate that URIs on this site will not end with a trailing slash. Your patterns should not depend on there always being a trailing slash.', 's2member-x').'</li>';
        }
        echo                '<li>'.sprintf(__('In WordPress it is common for any given URI to accept additional endpoint directives. For instance, paginated locations: <em>/example-post/page/2%1$s</em>, <em>/example-post/comments-page/2%1$s</em>. Therefore, we suggest a pattern that covers all possible endpoint variations. For instance: <em>/example-post{/**,}</em> will match the base URI by itself, and also match a possible trailing slash with any endpoint directives it may accept.', 's2member-x'), $wp_rewrite->use_trailing_slashes ? '/' : '').'</li>';
        echo                '<li>'.__('Any query string variables on the end of a URI (example: <em>?p=123&amp;key=value</em>) are stripped before comparison so you don\'t need to worry about them. However, if your pattern contains: <em>[?]</em> (literally, a <em>?</em> question mark in square brackets) it indicates that you DO want to check the query string, and they are NOT stripped away in that case; so your pattern will be capable of matching. Just remember that query string variables can appear in any order, as entered by a user. If you check for query strings, use <em>{**&,}</em> and <em>{&**,}</em> around the key=value pair you\'re looking for. For instance: <em>/example-post{/**,}[?]{**&,}key=value{&**,}</em>. If you\'re forced to look for multiple variables, the best you can do is: <em>{**&,}key=value{&**&,&,}another=value{&**,}</em>. This still expects <em>key=value</em> to be first, but <em>{&**&,&,}</em> helps some.', 's2member-x').'</li>';
        echo                '<li>'.__('It is possible to protect (and grant) access to portions of <em>/wp-admin/</em> with URI Patterns too. However, please remember that in order for a user to actually do anything inside the admin panel they will also need to have Capabilities which grant them additional permissions; such as the ability to <em>edit_posts</em>. See: <strong>Role Capabilities</strong> as a form of protection if you\'d like more information.', 's2member-x').'</li>';
        echo                '<li>'.__('It is possible to restrict access to every page on the entire site using the pattern <em>/**</em> as a catch-all. In this scenario, everything is off-limits, except for the Systematic URIs listed below. Having said that, please be careful when using a catch-all pattern. Everything (yes, everything) will be off-limits, including your home page! We suggest this as a last resort only. Instead, restrict Posts, Pages, Categories, Tags and/or other specific URIs; i.e., it is best to restrict only portions of a site from public access.', 's2member-x').'</li>';
        echo                '<li>'.__('Restrictions rely upon PHP as a server-side scripting language. Therefore, you can protect any location (page) served by WordPress via PHP, but you can\'t protect static files. For instance: <em>.jpg</em>, <em>.pdf</em>, and <em>.zip</em> are static. Generally speaking, if you upload something to the Media Library, it\'s a static asset. It cannot be protected here. Instead, configure a "Downloadable Product" with WooCommerce.', 's2member-x').'</li>';
        echo                '<li>'.__('There are a few Systematic URIs on your site that cannot be associated with a Restriction. It\'s OK if one of your patterns overlaps with these, but any URI matching one of these will simply not be allowed to have any additional Restrictions applied to it whatsoever. In other words, these are automatically excluded (internally), because they are associated with special functionality.', 's2member-x').
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
     * @since 16xxxx Restrictions.
     *
     * @param string|int $post_id Post ID.
     */
    public function onSaveRestriction($post_id)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        } elseif (get_post_type($post_id) !== $this->post_type) {
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

            $this->updateMeta($post_id, $_meta_key, $_meta_values);
        } // unset($_meta_key, $_split_regex, $_array_map_callback, $_meta_values); // Housekeeping.

        a::clearRestrictionsCache(); // Clear the cache now.
    }

    /**
     * Get meta values.
     *
     * @since 16xxxx Restrictions.
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
     * @since 16xxxx Restrictions.
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
     * @since 16xxxx Restrictions.
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
     * Create restriction URL.
     *
     * @since 16xxxx Restrictions.
     *
     * @return string Create restriction URL.
     */
    public function createUrl(): string
    {
        return admin_url('/post-new.php?post_type='.urlencode($this->post_type));
    }
}
