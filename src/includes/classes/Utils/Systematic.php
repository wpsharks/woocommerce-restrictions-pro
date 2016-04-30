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
 * Systematics.
 *
 * @since 16xxxx Security gate.
 */
class Systematic extends SCoreClasses\SCore\Base\Core
{
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
    }

    /**
     * Post IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return int[] Array of post IDs.
     */
    public function postIds(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($post_ids = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $post_ids; // Cached already.
        }
        $post_ids = []; // Initialize.
        $post_ids = array_merge($post_ids, [(int) s::getOption('security_gate_redirects_to_post_id')]);
        $post_ids = array_merge($post_ids, $this->collectWcPostIds($no_cache));
        $post_ids = array_merge($post_ids, $this->collectBpPostIds($no_cache));
        $post_ids = array_unique(c::removeEmptys($post_ids));

        return $post_ids = s::applyFilters('systematic_post_ids', $post_ids);
    }

    /**
     * Post types.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return int[] Array of post IDs.
     */
    public function postTypes(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($post_types = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $post_types; // Cached already.
        }
        $post_types = []; // Initialize.
        $post_types = array_merge($post_types, [a::restrictionPostType()]);
        $post_types = array_merge($post_types, ['attachment', 'nav_menu_item']);
        $post_types = array_merge($post_types, ['shop_order', 'shop_coupon', 'shop_webhook']);
        $post_types = array_unique(c::removeEmptys($post_types));

        return $post_types = s::applyFilters('systematic_post_types', $post_types);
    }

    /**
     * Roles.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return string[] Array of roles.
     */
    public function roles(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($roles = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $roles; // Cached already.
        }
        $roles = [
            'administrator', 'editor',
            'bbp_keymaster', 'bbp_blocked',
            'customer', 'shop_manager',
        ];
        $roles = array_unique(c::removeEmptys($roles));

        return $roles = s::applyFilters('systematic_roles', $roles);
    }

    /**
     * URI patterns.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     * @param bool      $compile  Compile into arrays?
     *
     * @return array Array of URI patterns.
     */
    public function uriPatterns(bool $no_cache = null, bool $compile = true)
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($uri_patterns = &$this->cacheKey(__FUNCTION__, $compile)) !== null && !$no_cache) {
            return $uri_patterns; // Cached already.
        }
        $uri_patterns = []; // Initialize.

        $uri_patterns[] = '**/{wp-*,xmlrpc}.php{/**,}';

        $uri_patterns[] = c::urlToWRegxUriPattern(wp_login_url());
        $uri_patterns[] = c::urlToWRegxUriPattern(wp_registration_url());

        if (is_multisite()) { // There is a network admin panel?
            $uri_patterns[] = c::urlToWRegxUriPattern(network_admin_url());
        }
        foreach ($this->collectWcUrls($no_cache) as $_wc_url) {
            $uri_patterns[] = c::urlToWRegxUriPattern($_wc_url);
        } // unset($_wc_url); // Housekeeping.

        foreach ($this->collectBpUrls($no_cache) as $_bp_url) {
            $uri_patterns[] = c::urlToWRegxUriPattern($_bp_url);
        } // unset($_bp_url); // Housekeeping.

        foreach ($uri_patterns as $_key => $_uri_pattern) {
            if (!$_uri_pattern) { // Quite possible.
                unset($uri_patterns[$_key]); // Exclude empties.
            } elseif (preg_match('/\/wp\-[a-z0-9]+?\.php/ui', $_uri_pattern)) {
                unset($uri_patterns[$_key]); // Already covered these.
            }
        } // unset($_key, $_uri_pattern); // Housekeeping.

        $uri_patterns = array_unique(c::removeEmptys($uri_patterns));
        $uri_patterns = s::applyFilters('systematic_uri_patterns', $uri_patterns);

        if ($compile) { // Compile into arrays?
            foreach ($uri_patterns as $_key => &$_uri_pattern) {
                $_against     = preg_match('/\[[?&]+\]/u', $_uri_pattern) ? 'uri' : 'uri_path';
                $_uri_pattern = ['against' => $_against, 'wregx' => $_uri_pattern, 'regex' => c::wRegx($_uri_pattern, '/', true).'i'];
            } // Must unset temp reference variable.
            unset($_key, $_uri_pattern);
        }
        return $uri_patterns;
    }

    /**
     * WC Post IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return int[] Array of post IDs.
     */
    protected function collectWcPostIds(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($wc_post_ids = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $wc_post_ids; // Cached already.
        }
        $wc_post_ids = []; // Initialize.

        foreach (['shop', 'cart', 'terms', 'checkout', 'myaccount', 'view_order'] as $_wc_page) {
            if (($_wc_page_id = (int) wc_get_page_id($_wc_page)) > 0) {
                $wc_post_ids[] = $_wc_page_id;
            }
        } // unset($_wc_page, $_wc_page_id); // Housekeeping.

        return $wc_post_ids;
    }

    /**
     * WooCommerce URLs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return string[] Array of URLs.
     */
    protected function collectWcUrls(bool $no_cache = null): array
    {
        $transient_cache_key = 'systematic_wc_urls';

        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (!$no_cache && is_array($wc_urls = s::getTransient($transient_cache_key))) {
            return $wc_urls; // Cached already.
        }
        $wc_urls  = []; // Initialize.
        $home_url = get_home_url();

        foreach (['shop', 'cart', 'terms', 'checkout', 'myaccount', 'view_order'] as $_wc_page) {
            if (($_wc_page_url = wc_get_page_permalink($_wc_page)) && $_wc_page_url !== $home_url) {
                $wc_urls[] = $_wc_page_url;
            }
        } // unset($_wc_page, $_wc_page_url); // Housekeeping.

        s::setTransient($transient_cache_key, $wc_urls, HOUR_IN_SECONDS);

        return $wc_urls;
    }

    /**
     * BP Post IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return int[] Array of post IDs.
     */
    protected function collectBpPostIds(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($bp_post_ids = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $bp_post_ids; // Cached already.
        }
        $bp_post_ids = []; // Initialize.

        if (!c::canCallFunc('buddypress')) {
            return $bp_post_ids = []; // Not running.
        }
        foreach ((array) bp_core_get_directory_page_ids() as $_bp_page => $_bp_page_id) {
            if (in_array($_bp_page, ['register', 'activate'], true) && ($_bp_page_id = (int) $_bp_page_id) > 0) {
                $bp_post_ids[] = $_bp_page_id;
            }
        } // unset($_bp_page, $_bp_page_id); // Housekeeping.

        return $bp_post_ids;
    }

    /**
     * BuddyPress URLs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return string[] Array of URLs.
     */
    protected function collectBpUrls(bool $no_cache = null): array
    {
        $transient_cache_key = 'systematic_bp_urls';

        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (!$no_cache && is_array($bp_urls = s::getTransient($transient_cache_key))) {
            return $bp_urls; // Cached already.
        }
        $bp_urls = []; // Initialize.

        if (!c::canCallFunc('buddypress')) {
            return $bp_urls = []; // Not running.
        }
        if (($bp_signup_page_url = bp_get_signup_page())) {
            $bp_urls[] = $bp_signup_page_url;
        }
        if (($bp_activation_page_url = bp_get_activation_page())) {
            $bp_urls[] = $bp_activation_page_url;
        }
        s::setTransient($transient_cache_key, $bp_urls, HOUR_IN_SECONDS);

        return $bp_urls;
    }
}
