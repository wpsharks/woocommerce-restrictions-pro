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

                'menu_position' => 1,
                'menu_icon'     => $menu_icon,
                'description'   => __('Content Restriction', 's2member-x'),

                'labels' => [
                    'name'               => __('Restrictions', 's2member-x'),
                    'singular_name'      => __('Restriction', 's2member-x'),
                    'add_new'            => __('Add Restriction', 's2member-x'),
                    'add_new_item'       => __('Add New Restriction', 's2member-x'),
                    'edit_item'          => __('Edit Restriction', 's2member-x'),
                    'new_item'           => __('New Restriction', 's2member-x'),
                    'all_items'          => __('All Restrictions', 's2member-x'),
                    'view_item'          => __('View Restriction', 's2member-x'),
                    'search_items'       => __('Search Restrictions', 's2member-x'),
                    'not_found'          => __('No Restrictions found', 's2member-x'),
                    'not_found_in_trash' => __('No Restrictions found in Trash', 's2member-x'),
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

                'capabilities' => [
                    'assign_terms' => 'edit_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'edit_terms'   => 'edit_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'manage_terms' => 'edit_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                    'delete_terms' => 'delete_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',
                ],
            ]
        );
    }
}
