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
 * Uninstaller.
 *
 * @since 16xxxx Installer.
 */
class Uninstaller extends SCoreClasses\SCore\Base\Core
{
    /**
     * Other install routines.
     *
     * @since 16xxxx Restrictions.
     *
     * @param int $counter Site counter.
     */
    public function onOtherUninstallRoutines(int $counter)
    {
        a::removeAllRestrictionCaps();
        $this->deleteRestrictions($counter);
    }

    /**
     * Delete all restrictions.
     *
     * @since 16xxxx Restrictions.
     *
     * @param int $counter Site counter.
     */
    protected function deleteRestrictions(int $counter)
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
