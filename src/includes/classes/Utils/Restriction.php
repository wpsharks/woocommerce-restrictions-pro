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
     * Register post type.
     *
     * @since 16xxxx Restrictions.
     */
    public function onInitRegisterPostType()
    {
        $menu_icon = file_get_contents(dirname(__FILE__, 4).'/client-s/images/icon.svg');
        $menu_icon = 'data:image/svg+xml;base64,'.base64_encode($menu_icon);

        register_post_type(
            $this->App->Config->©brand['©prefix'].'_restriction',
            [
                'public'       => false,
                'hierarchical' => false,

                'show_ui'           => true,
                'show_in_menu'      => true,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => true,

                'rewrite'     => false,
                'has_archive' => false,
                'query_var'   => false,

                'supports' => [
                    'title',
                    'editor',
                    'revisions',
                ],
                'delete_with_user' => false,

                'menu_position' => null,
                'menu_icon'     => $menu_icon,
                'description'   => __('Content Restriction', 's2member-x'),

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
                    'all_items'             => __('All Restrictions', 's2member-x'),
                    'archives'              => __('Restriction Archives', 's2member-x'),
                    'insert_into_item'      => __('Insert into Restriction', 's2member-x'),
                    'uploaded_to_this_item' => __('Upload to this Restriction', 's2member-x'),
                    'featured_image'        => __('Set Featured Image', 's2member-x'),
                    'remove_featured_image' => __('Remove Featured Image', 's2member-x'),
                    'use_featured_image'    => __('Use as Featured Image', 's2member-x'),
                    'filter_items_list'     => __('Filter Restrictions List', 's2member-x'),
                    'items_list_navigation' => __('Restrictions List Navigation', 's2member-x'),
                    'items_list'            => __('Restrictions List', 's2member-x'),
                    'menu_name'             => __('Restrictions', 's2member-x'),
                    'name_admin_bar'        => __('Restriction', 's2member-x'),
                ],

                'map_meta_cap'    => true,
                'capability_type' => [
                    $this->App->Config->©brand['©prefix'].'_restriction',
                    $this->App->Config->©brand['©prefix'].'_restrictions',
                ],
            ]
        );
        register_taxonomy(
            $this->App->Config->©brand['©prefix'].'_restriction_cat',
            $this->App->Config->©brand['©prefix'].'_restriction',
            [
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

                'description' => __('Content Restriction Tags/Categories', 's2member-x'),

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
                    'menu_name'                  => __('Categories', 's2member-x'),
                    'name_admin_bar'             => __('Category', 's2member-x'),
                    'archives'                   => __('All Categories', 's2member-x'),
                ],

                'capabilities' => [
                    'assign_terms' => 'edit_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'edit_terms'   => 'edit_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'manage_terms' => 'edit_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'delete_terms' => 'delete_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                ],
            ]
        );
    }

    /**
     * Position restrictions.
     *
     * @since 16xxxx Restrictions.
     */
    public function onMenuOrder(array $menu_items): array
    {
        $woocommerce_item = 'woocommerce'; // Position after this.
        $woocommerce_key  = array_search($woocommerce_item, $menu_items, true);

        $restriction_item = 'edit.php?post_type='.$this->App->Config->©brand['©prefix'].'_restriction';
        $restriction_key  = array_search($restriction_item, $menu_items, true);

        if ($woocommerce_key === false || $restriction_key === false) {
            return $menu_items; // Not possible.
        }
        $new_menu_items = []; // Initialize new menu items.

        foreach ($menu_items as $_key => $_item) {
            if ($_item !== $restriction_item) {
                $new_menu_items[] = $_item;
            }
            if ($_item === $woocommerce_item) {
                $new_menu_items[] = $restriction_item;
            }
        } // unset($_key, $_item); // Housekeeping.

        return $new_menu_items;
    }

    /**
     * Restriction pointers.
     *
     * @since 16xxxx Restrictions.
     */
    public function setupPointers()
    {
        // @TODO: <http://wptavern.com/create-your-own-custom-pointers-in-the-wordpress-admin>
    }
}
