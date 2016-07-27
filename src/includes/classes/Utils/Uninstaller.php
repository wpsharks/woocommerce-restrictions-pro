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
     * Other install routines.
     *
     * @since 160524 Restrictions.
     *
     * @param int $site_counter Site counter.
     */
    public function onOtherUninstallRoutines(int $site_counter)
    {
        a::removeAllRestrictionCaps();
        $this->deleteRestrictions($site_counter);
    }

    /**
     * Delete all restrictions.
     *
     * @since 160524 Restrictions.
     *
     * @param int $site_counter Site counter.
     */
    protected function deleteRestrictions(int $site_counter)
    {
        $WpDb = s::wpDb();

        $sql = /* Restriction post IDs. */ '
            SELECT `ID`
                FROM `'.esc_sql($WpDb->posts).'`
            WHERE `post_type` = %s
        ';
        $sql = $WpDb->prepare($sql, a::restrictionPostType());

        if (!($results = $WpDb->get_results($sql))) {
            return; // Nothing to delete.
        }
        foreach ($results as $_key => $_result) {
            wp_delete_post($_result->ID, true);
        } // unset($_key, $_result); // Housekeeping.
    }
}
