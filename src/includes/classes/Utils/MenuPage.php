<?php
/**
 * Menu page utils.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
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
 * Menu page utils.
 *
 * @since 160731 Initial release.
 */
class MenuPage extends SCoreClasses\SCore\Base\Core
{
    /**
     * Adds menu pages.
     *
     * @since 160731 Initial release.
     */
    public function onAdminMenu()
    {
        s::addMenuPageItem([
            'menu_title'    => __('Options', 'woocommerce-restrictions'),
            'parent_page'   => 'edit.php?post_type=restriction',
            'template_file' => 'admin/menu-pages/options/default.php',

            'tabs' => [
                'default' => sprintf(__('%1$s', 'woocommerce-restrictions'), esc_html($this->App->Config->©brand['©name'])),
                'restore' => [
                    'label' => __('Restore Default Options', 'woocommerce-restrictions'),
                    'url'   => s::restoreDefaultOptionsUrl(), 'onclick' => 'confirm',
                ],
            ],
        ]);
    }
}
