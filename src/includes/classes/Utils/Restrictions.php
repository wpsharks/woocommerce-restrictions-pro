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
     * Transient cache key.
     *
     * @since 16xxxx Restrictions.
     *
     * @type string
     */
    protected $transient_cache_key;

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

        $this->transient_cache_key = 'restrictions';
    }

    /**
     * Clear cache.
     *
     * @since 16xxxx Restrictions.
     */
    public function clearCache()
    {
        s::deleteTransient($this->transient_cache_key);
    }

    /**
     * By meta key.
     *
     * @since 16xxxx Restrictions.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return array[] By meta key.
     */
    public function byMetaKey(bool $no_cache = null): array
    {
        $WpDb = $this->s::wpDb();

        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (!$no_cache && is_array($restrictions = s::getTransient($this->transient_cache_key))) {
            return $restrictions; // Cached already.
        }
        $post_type     = a::restrictionPostType();
        $meta_keys     = a::restrictionMetaKeys();
        $int_meta_keys = a::restrictionIntMetaKeys();

        $full_meta_keys = []; // Initialize.
        foreach ($meta_keys as $_meta_key) {
            $full_meta_keys[] = $post_type.'_'.$_meta_key;
        }
        $restrictions = array_fill_keys($meta_keys, []);

        $sql_post_ids_sub_query = // Restrictions.
            'SELECT `ID` FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $sql_post_ids_sub_query = $WpDb->prepare($sql_post_ids_sub_query, $post_type);

        $sql = 'SELECT `post_id`, `meta_key` AS `full_meta_key`, `meta_value` FROM `'.esc_sql($WpDb->postmeta).'`'.
                ' WHERE `post_id` IN('.$sql_post_ids_sub_query.')'.// For published Restrictions.
                ' AND `meta_key` IN('.c::quoteSqlIn($full_meta_keys).')';// Restriction keys.

        if (!($results = $WpDb->get_results($sql))) {
            s::setTransient($this->transient_cache_key, $restrictions, HOUR_IN_SECONDS);
            return $restrictions; // Nothing.
        }
        foreach ($results as $_result) {
            $_meta_key                  = preg_replace('/^'.preg_quote($post_type.'_', '/').'/ui', '', $_result->full_meta_key);
            $_meta_value                = in_array($_meta_key, $int_meta_keys, true) ? (int) $_result->meta_value : (string) $_result->meta_value;
            $restrictions[$_meta_key][] = $_meta_value;
        } // unset($_result, $_meta_key, $_meta_value); // Housekeeping.

        foreach ($restrictions as $_meta_key => &$_restrictions) {
            $_restrictions = array_unique(c::removeEmptys($_restrictions));
        } // Must unset temp variable by reference.
        unset($_meta_key, $_restrictions); // Housekeeping.

        foreach ($restrictions['uri_patterns'] as $_key => &$_uri_pattern) {
            $_uri_pattern = c::wRegx($_uri_pattern, '/', true);
        } // Must unset temp variable by reference.
        unset($_key, $_uri_pattern); // Housekeeping.

        s::setTransient($this->transient_cache_key, $restrictions, HOUR_IN_SECONDS);

        return $restrictions; // Associative.
    }
}
