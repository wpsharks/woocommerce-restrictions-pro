<?php
/**
 * Uninstaller.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\Utils;

use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\CoreFacades as c;
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
 * Uninstaller.
 *
 * @since 160524 Installer.
 */
class Uninstaller extends SCoreClasses\SCore\Base\Core
{
    /**
     * Other uninstall routines.
     *
     * @since 160524 Restrictions.
     *
     * @param int $site_counter Site counter.
     */
    public function onOtherUninstallRoutines(int $site_counter)
    {
        $this->deletePosts($site_counter);
        $this->deleteTaxonomies($site_counter);
        a::removeAllRestrictionCaps();
    }

    /**
     * Delete posts.
     *
     * @since 160729 Uninstaller.
     *
     * @param int $site_counter Site counter.
     */
    protected function deletePosts(int $site_counter)
    {
        $WpDb = s::wpDb();

        $sql = /* Post IDs. */ '
            SELECT `ID`
                FROM `'.esc_sql($WpDb->posts).'`
            WHERE `post_type` = \'restriction\'
        ';
        if (!($results = $WpDb->get_results($sql))) {
            return; // Nothing to delete.
        }
        foreach ($results as $_result) {
            wp_delete_post($_result->ID, true);
        } // unset($_result); // Housekeeping.
    }

    /**
     * Delete taxonomies.
     *
     * @since 160729 Uninstaller.
     *
     * @param int $site_counter Site counter.
     */
    protected function deleteTaxonomies(int $site_counter)
    {
        $term_ids = get_terms([
            'taxonomy'   => 'restriction_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ]);
        $term_ids = is_array($term_ids) ? $term_ids : [];

        foreach ($term_ids as $_term_id) {
            wp_delete_term($_term_id, 'restriction_category');
        } // unset($_term_id); // Houskeeping.
    }
}
