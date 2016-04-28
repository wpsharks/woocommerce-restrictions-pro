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
 * Restrictions.
 *
 * @since 16xxxx Installer.
 */
class Restrictions extends SCoreClasses\SCore\Base\Core
{
    /**
     * Clear cache.
     *
     * @since 16xxxx Restrictions.
     */
    public function clearCache()
    {
        s::deleteTransient('restrictions_by_meta_key');
    }

    /**
     * By meta key.
     *
     * @since 16xxxx Restrictions.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return array `['restrictions' => [], 'restriction_ids' => []]`
     */
    public function byMetaKey(bool $no_cache = null): array
    {
        $WpDb                = $this->s::wpDb();
        $transient_cache_key = 'restrictions_by_meta_key';

        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (!$no_cache && is_array($by_meta_key = s::getTransient($transient_cache_key))) {
            return $by_meta_key; // Cached already.
        }
        $post_type     = a::restrictionPostType();
        $meta_keys     = a::restrictionMetaKeys();
        $int_meta_keys = a::restrictionIntMetaKeys();

        $full_meta_keys = []; // Initialize.
        foreach ($meta_keys as $_meta_key) {
            $full_meta_keys[] = 'restriction_'.$_meta_key;
        } // unset($_meta_key); // Housekeeping.

        $by_meta_key = [
            'restrictions'     => array_fill_keys($meta_keys, []),
            'restrictions_ids' => array_fill_keys($meta_keys, []),
        ];
        $sql_post_ids_sub_query = // Restrictions.
            'SELECT `ID` FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $sql_post_ids_sub_query = $WpDb->prepare($sql_post_ids_sub_query, $post_type);

        $sql = 'SELECT `post_id`, `meta_key` AS `full_meta_key`, `meta_value` FROM `'.esc_sql($WpDb->postmeta).'`'.
                ' WHERE `post_id` IN('.$sql_post_ids_sub_query.')'.// For published Restrictions.
                ' AND `meta_key` IN('.c::quoteSqlIn($full_meta_keys).')';// Restriction keys.

        if (!($results = $WpDb->get_results($sql))) {
            s::setTransient($transient_cache_key, $by_meta_key, HOUR_IN_SECONDS);
            return $by_meta_key; // Nothing.
        }
        foreach ($results as $_result) {
            $_meta_key                                                  = preg_replace('/^restriction_/u', '', $_result->full_meta_key);
            $_meta_value                                                = in_array($_meta_key, $int_meta_keys, true) ? (int) $_result->meta_value : (string) $_result->meta_value;
            $by_meta_key['restrictions'][$_meta_key][]                  = $_meta_value;
            $by_meta_key['restriction_ids'][$_meta_key][$_meta_value][] = (int) $_result->post_id;
        } // unset($_result, $_meta_key, $_meta_value); // Housekeeping.

        foreach ($by_meta_key['restrictions'] as &$_restrictions) {
            $_restrictions = array_unique(c::removeEmptys($_restrictions));
        } // Must unset temp variable by reference.
        unset($_restrictions); // Housekeeping.

        foreach ($by_meta_key['restriction_ids'] as &$_restriction_ids) {
            $_restriction_ids = array_map('intval', $_restriction_ids);
            $_restriction_ids = array_unique(c::removeEmptys($_restriction_ids));
        } // Must unset temp variable by reference.
        unset($_restriction_ids); // Housekeeping.

        foreach ($by_meta_key['restrictions']['uri_patterns'] as &$_uri_pattern) {
            $_uri_pattern = [$_uri_pattern => c::wRegx($_uri_pattern, '/', true)];
        } // Must unset temp variable by reference.
        unset($_uri_pattern); // Housekeeping.

        s::setTransient($transient_cache_key, $by_meta_key, HOUR_IN_SECONDS);

        return $by_meta_key;
    }
}
