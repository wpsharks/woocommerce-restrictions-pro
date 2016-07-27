<?php
/**
 * Restrictions.
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
 * Restrictions.
 *
 * @since 160524 Installer.
 */
class Restrictions extends SCoreClasses\SCore\Base\Core
{
    /**
     * Post type.
     *
     * @since 160524
     *
     * @var string Post type.
     */
    protected $post_type;

    /**
     * Meta prefix.
     *
     * @since 160524
     *
     * @var string Meta prefix.
     */
    protected $meta_prefix;

    /**
     * Meta keys.
     *
     * @since 160524
     *
     * @var array Meta keys.
     */
    protected $meta_keys;

    /**
     * Full meta keys.
     *
     * @since 160524
     *
     * @var array Full meta keys.
     */
    protected $full_meta_keys;

    /**
     * Meta keys.
     *
     * @since 160524
     *
     * @var array Meta keys.
     */
    protected $int_meta_keys;

    /**
     * Class constructor.
     *
     * @since 160524 Restrictions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->post_type = a::restrictionPostType();

        $this->meta_prefix   = a::restrictionMetaPrefix();
        $this->meta_keys     = a::restrictionMetaKeys();
        $this->int_meta_keys = a::restrictionIntMetaKeys();

        $this->full_meta_keys = []; // Initialize.
        foreach ($this->meta_keys as $_meta_key) {
            $this->full_meta_keys[] = $this->meta_prefix.$_meta_key;
        } // unset($_meta_key); // Housekeeping.
    }

    /**
     * Clear cache.
     *
     * @since 160524 Restrictions.
     */
    public function clearCache()
    {
        $this->cacheClear(); // Object cache.
        s::deleteTransient('restrictions_by_meta_key');
        s::deleteTransient('restrictions_all_with_meta');
    }

    /**
     * Restriction slug to ID.
     *
     * @since 160524 Restrictions.
     *
     * @param string $slug A slug.
     *
     * @return int ID; else `0` on failure.
     */
    public function slugToId(string $slug): int
    {
        $ids_by_slug = $this->idsBySlug();
        // Note that a slug in this context can contain almost anything.
        // See: <http://wordpress.stackexchange.com/a/149192/81760>
        return !empty($ids_by_slug[$slug]) ? $ids_by_slug[$slug] : 0;
    }

    /**
     * Restriction IDs for slugs.
     *
     * @since 160524 Restrictions.
     *
     * @param array $slugs One or more slugs/IDs.
     * @note It's OK if the array also contains IDs.
     *
     * @return array An array of IDs (keys are slugs).
     */
    public function slugsToIds(array $slugs): array
    {
        $ids         = []; // Initialize.
        $ids_by_slug = $this->idsBySlug();

        foreach ($slugs as $_slug) {
            if ($_slug && is_int($_slug)) {
                $_slug = array_search($_slug, $ids_by_slug, true);
            }
            if ($_slug && is_string($_slug) && !empty($ids_by_slug[$_slug])) {
                // Note that a slug in this context can contain almost anything.
                // See: <http://wordpress.stackexchange.com/a/149192/81760>
                $ids[$_slug] = $ids_by_slug[$_slug];
            }
        } // unset($_slug); // Housekeeping.

        return $ids;
    }

    /**
     * Restriction titles by ID.
     *
     * @since 160524 Restriction titles.
     *
     * @return array Titles; keys are IDs.
     */
    public function titlesById(): array
    {
        global $blog_id; // Current blog ID.

        if (($titles_by_id = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
            return $titles_by_id; // Already cached these.
        }
        $all_restrictions_with_meta = $this->allWithMeta();
        $titles_by_id               = []; // Initialize array.

        foreach ($all_restrictions_with_meta as $_restriction) {
            $titles_by_id[$_restriction['data']->ID] = $_restriction['data']->post_title;
        } // unset($_restriction); // Housekeeping.

        return $titles_by_id;
    }

    /**
     * Restriction IDs by slug.
     *
     * @since 160524 Restrictions by slug.
     *
     * @return array IDs; keys are slugs.
     */
    public function idsBySlug(): array
    {
        global $blog_id; // Current blog ID.

        if (($ids_by_slug = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
            return $ids_by_slug; // Already cached these.
        }
        $all_restrictions_with_meta = $this->allWithMeta();
        $ids_by_slug                = []; // Initialize array.

        foreach ($all_restrictions_with_meta as $_restriction) {
            // Note: WordPress doesn't allow numeric slugs.
            $ids_by_slug[$_restriction['data']->post_name] = $_restriction['data']->ID;
            // Note that a slug in this context can contain almost anything.
            // See: <http://wordpress.stackexchange.com/a/149192/81760>
        } // unset($_restriction); // Housekeeping.

        return $ids_by_slug;
    }

    /**
     * Restrictions by meta key.
     *
     * @since 160524 Restrictions by meta key.
     *
     * @return array `['restrictions', 'restriction_ids']`
     */
    public function byMetaKey(): array
    {
        $transient_cache_key = 'restrictions_by_meta_key';
        $no_cache            = s::isMenuPageForPostType($this->post_type);

        if (!$no_cache && is_array($by_meta_key = s::getTransient($transient_cache_key))) {
            return $by_meta_key; // Already cached these recently.
        }
        $all_restrictions_with_meta = $this->allWithMeta();
        $by_meta_key                = [ // Initialize; by meta key.
            'restrictions'    => array_fill_keys($this->meta_keys, []),
            'restriction_ids' => array_fill_keys($this->meta_keys, []),
        ];
        foreach ($all_restrictions_with_meta as $_restriction) {
            foreach ($_restriction['meta'] as $_meta_key => $_meta_values) {
                foreach ($_meta_values as $_meta_value) {
                    $by_meta_key['restrictions'][$_meta_key][]                  = $_meta_value;
                    $by_meta_key['restriction_ids'][$_meta_key][$_meta_value][] = $_restriction['data']->ID;
                } // unset($_meta_value); // Housekeeping.
            } // unset($_meta_key, $_meta_values); // Housekeeping.
        } // unset($_restriction); // Housekeeping.

        foreach ($by_meta_key['restrictions'] as $_meta_key => $_meta_values) {
            $by_meta_key['restrictions'][$_meta_key] = array_unique(c::removeEmptys($_meta_values));
        } // unset($_meta_key, $_meta_values); // Housekeeping.

        foreach ($by_meta_key['restriction_ids'] as $_meta_key => $_meta_values) {
            foreach ($_meta_values as $_meta_value => $_by_restriction_ids) {
                $by_meta_key['restriction_ids'][$_meta_key][$_meta_value] = array_unique(c::removeEmptys($_by_restriction_ids));
            } // unset($_meta_value, $_by_restriction_ids); // Housekeeping.
        } // unset($_meta_key, $_meta_values); // Housekeeping.

        foreach ($by_meta_key['restrictions']['uri_patterns'] as $_key => $_uri_pattern) {
            $_against                                           = preg_match('/\[[?&]+\]/u', $_uri_pattern) ? 'uri' : 'uri_path';
            $by_meta_key['restrictions']['uri_patterns'][$_key] = [
                'against' => $_against,
                'wregx'   => $_uri_pattern,
                'regex'   => c::wRegx($_uri_pattern, '/', true).'i',
            ]; // Pre-compiles regex for each pattern.
        } // unset($_key, $_uri_pattern, $_against); // Housekeeping.

        s::setTransient($transient_cache_key, $by_meta_key, MINUTE_IN_SECONDS * 15);
        return $by_meta_key; // All restrictions; by meta key.
    }

    /**
     * Restrictions (all w/ meta).
     *
     * @since 160524 Restrictions.
     *
     * @return array All restrictions w/ meta values.
     */
    public function allWithMeta(): array
    {
        $transient_cache_key = 'restrictions_all_with_meta';
        $no_cache            = s::isMenuPageForPostType($this->post_type);

        if (!$no_cache && is_array($all = s::getTransient($transient_cache_key))) {
            return $all; // Already cached these recently.
        }
        $WpDb = $this->s::wpDb(); // DB object instance.

        $sql = // Restrictions.
            'SELECT * FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $sql = $WpDb->prepare($sql, $this->post_type);

        if (!($results = $WpDb->get_results($sql))) {
            s::setTransient($transient_cache_key, [], MINUTE_IN_SECONDS * 15);
            return $all = []; // Nothing.
        }
        foreach ($results as $_key => $_result) {
            $_result->ID               = (int) $_result->ID;
            $all[$_result->ID]['data'] = $_result; // Integer ID now.
            $all[$_result->ID]['meta'] = array_fill_keys($this->meta_keys, []);
        } // unset($_key, $_result); // Housekeeping.

        $post_ids_sub_query = // Restriction IDs.
            'SELECT `ID` FROM `'.esc_sql($WpDb->posts).'`'.
                " WHERE `post_type` = %s AND `post_status` = 'publish'";
        $post_ids_sub_query = $WpDb->prepare($post_ids_sub_query, $this->post_type);

        $meta_sql = 'SELECT `post_id`, `meta_key` AS `full_meta_key`, `meta_value` FROM `'.esc_sql($WpDb->postmeta).'`'.
                ' WHERE `post_id` IN('.$post_ids_sub_query.')'.// For published Restrictions.
                ' AND `meta_key` IN('.c::quoteSqlIn($this->full_meta_keys).')';// Restriction keys.

        if (($meta_results = $WpDb->get_results($meta_sql))) { // Multiple values per key.
            foreach ($meta_results as $_key => $_result) {
                $_result->post_id = (int) $_result->post_id;
                if (empty($all[$_result->post_id])) {
                    continue; // No matching restriction.
                } // ↑ This can happen with stale DB rows or corruption.
                $_meta_key                                    = preg_replace('/^'.c::escRegex($this->meta_prefix).'/u', '', $_result->full_meta_key);
                $_meta_value                                  = in_array($_meta_key, $this->int_meta_keys, true) ? (int) $_result->meta_value : (string) $_result->meta_value;
                $all[$_result->post_id]['meta'][$_meta_key][] = $_meta_value;
            } // unset($_key, $_result, $_meta_key, $_meta_value); // Housekeeping.
        }
        s::setTransient($transient_cache_key, $all, MINUTE_IN_SECONDS * 15);
        return $all; // All restrictions w/ meta.
    }
}
