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
        $this->cacheClear(); // Object cache.
        s::deleteTransient('restrictions_by_slug');
        s::deleteTransient('restrictions_by_meta_key');
    }

    /**
     * Restriction slug to ID.
     *
     * @since 16xxxx Restrictions.
     *
     * @param string $slug A slug.
     *
     * @return int ID; else `0` on failure.
     */
    public function slugToId(string $slug): int
    {
        $by_slug = $this->bySlug();
        // Note that a slug in this context can contain almost anything.
        // See: <http://wordpress.stackexchange.com/a/149192/81760>

        return !empty($by_slug[$slug]) ? $by_slug[$slug] : 0;
    }

    /**
     * Restriction IDs for slugs.
     *
     * @since 16xxxx Restrictions.
     *
     * @param array $slugs One or more slugs/IDs.
     * @note It's OK if the array also contains IDs.
     *
     * @return array An array of IDs (keys are slugs).
     */
    public function slugsToIds(array $slugs): array
    {
        $ids     = []; // Initialize.
        $by_slug = $this->bySlug();

        foreach ($slugs as $_slug) {
            if ($_slug && is_int($_slug)) {
                $_slug = array_search($_slug, $by_slug, true);
            }
            if ($_slug && is_string($_slug) && !empty($by_slug[$_slug])) {
                // Note that a slug in this context can contain almost anything.
                // See: <http://wordpress.stackexchange.com/a/149192/81760>
                $ids[$_slug] = $by_slug[$_slug];
            }
        } // unset($_slug); // Housekeeping.

        return $ids;
    }

    /**
     * Restriction IDs by slug.
     *
     * @since 16xxxx Restrictions.
     *
     * @return array IDs; keys are slugs.
     */
    public function bySlug(): array
    {
        $transient_cache_key = 'restrictions_by_slug';
        $post_type           = a::restrictionPostType();

        $no_cache = s::isMenuPageForPostType($post_type);
        if (!$no_cache && is_array($by_slug = s::getTransient($transient_cache_key))) {
            return $by_slug; // Cached already.
        }
        $WpDb = $this->s::wpDb(); // DB object instance.

        $by_slug = []; // Initialize array.

        $sql = // Restrictions from the posts table.
            'SELECT `ID`, `post_name` AS `slug` FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $sql = $WpDb->prepare($sql, $post_type);

        if (!($results = $WpDb->get_results($sql))) {
            s::setTransient($transient_cache_key, $by_slug, MINUTE_IN_SECONDS * 15);
            return $by_slug; // Nothing.
        }
        foreach ($results as $_key => $_result) {
            $by_slug[$_result->slug] = (int) $_result->ID;
            // Note that a slug in this context can contain almost anything.
            // See: <http://wordpress.stackexchange.com/a/149192/81760>
        } // unset($_key, $_result); // Housekeeping.

        s::setTransient($transient_cache_key, $by_slug, MINUTE_IN_SECONDS * 15);

        return $by_slug;
    }

    /**
     * Restrictions by meta key.
     *
     * @since 16xxxx Restrictions.
     *
     * @return array `['restrictions' => [], 'restriction_ids' => []]`
     */
    public function byMetaKey(): array
    {
        $transient_cache_key = 'restrictions_by_meta_key';
        $post_type           = a::restrictionPostType();

        $no_cache = s::isMenuPageForPostType($post_type);
        if (!$no_cache && is_array($by_meta_key = s::getTransient($transient_cache_key))) {
            return $by_meta_key; // Cached already.
        }
        $WpDb = $this->s::wpDb(); // DB object instance.

        $meta_keys     = a::restrictionMetaKeys();
        $int_meta_keys = a::restrictionIntMetaKeys();

        $full_meta_keys = []; // Initialize.
        foreach ($meta_keys as $_meta_key) {
            $full_meta_keys[] = 'restriction_'.$_meta_key;
        } // unset($_meta_key); // Housekeeping.

        $by_meta_key = [ // Initialize array; by meta key.
            'restrictions'    => array_fill_keys($meta_keys, []),
            'restriction_ids' => array_fill_keys($meta_keys, []),
        ];
        $sql_post_ids_sub_query = // Restrictions.
            'SELECT `ID` FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $sql_post_ids_sub_query = $WpDb->prepare($sql_post_ids_sub_query, $post_type);

        $sql = 'SELECT `post_id`, `meta_key` AS `full_meta_key`, `meta_value` FROM `'.esc_sql($WpDb->postmeta).'`'.
                ' WHERE `post_id` IN('.$sql_post_ids_sub_query.')'.// For published Restrictions.
                ' AND `meta_key` IN('.c::quoteSqlIn($full_meta_keys).')';// Restriction keys.

        if (!($results = $WpDb->get_results($sql))) {
            s::setTransient($transient_cache_key, $by_meta_key, MINUTE_IN_SECONDS * 15);
            return $by_meta_key; // Nothing.
        }
        foreach ($results as $_key => $_result) {
            $_meta_key                                                  = preg_replace('/^restriction_/u', '', $_result->full_meta_key);
            $_meta_value                                                = in_array($_meta_key, $int_meta_keys, true) ? (int) $_result->meta_value : (string) $_result->meta_value;
            $by_meta_key['restrictions'][$_meta_key][]                  = $_meta_value;
            $by_meta_key['restriction_ids'][$_meta_key][$_meta_value][] = (int) $_result->post_id;
        } // unset($_key, $_result, $_meta_key, $_meta_value); // Housekeeping.

        foreach ($by_meta_key['restrictions'] as $_meta_key => &$_restrictions) {
            $_restrictions = array_unique(c::removeEmptys($_restrictions));
        } // Must unset temp reference variable.
        unset($_meta_key, $_restrictions);

        foreach ($by_meta_key['restriction_ids'] as $_meta_key => &$_restrictions) {
            foreach ($_restrictions as $_restriction => &$_restriction_ids) {
                $_restriction_ids = array_unique(c::removeEmptys($_restriction_ids));
            } // Must unset temp reference variable.
            unset($_restriction, $_restriction_ids);
        } // Must unset temp reference variable.
        unset($_meta_key, $_restrictions);

        foreach ($by_meta_key['restrictions']['uri_patterns'] as $_key => &$_uri_pattern) {
            $_against     = preg_match('/\[[?&]+\]/u', $_uri_pattern) ? 'uri' : 'uri_path';
            $_uri_pattern = ['against' => $_against, 'wregx' => $_uri_pattern, 'regex' => c::wRegx($_uri_pattern, '/', true).'i'];
        } // Must unset temp reference variable.
        unset($_key, $_uri_pattern);

        s::setTransient($transient_cache_key, $by_meta_key, MINUTE_IN_SECONDS * 15);

        return $by_meta_key;
    }
}
