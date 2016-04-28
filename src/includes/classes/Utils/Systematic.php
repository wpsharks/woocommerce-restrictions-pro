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

        return $post_ids;
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

        return $post_types;
    }

    /**
     * Role IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     *
     * @return string[] Array of role IDs.
     */
    public function roleIds(bool $no_cache = null): array
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($role_ids = &$this->cacheKey(__FUNCTION__)) !== null && !$no_cache) {
            return $role_ids; // Cached already.
        }
        $role_ids = [
            'administrator', 'editor',
            'bbp_keymaster', 'bbp_blocked',
            'customer', 'shop_manager',
        ];
        $role_ids = array_unique(c::removeEmptys($role_ids));

        return $role_ids;
    }

    /**
     * URI patterns.
     *
     * @since 16xxxx Initial release.
     *
     * @param bool|null $no_cache Bypass cache?
     * @param bool      $as_regex As a regex pattern?
     *
     * @return string[]|string Array of URI patterns.
     */
    public function uriPatterns(bool $no_cache = null, bool $as_regex = false)
    {
        $no_cache = !isset($no_cache) && is_admin() ? true : (bool) $no_cache;
        if (($uri_patterns = &$this->cacheKey(__FUNCTION__, $as_regex)) !== null && !$no_cache) {
            return $uri_patterns; // Cached already.
        }
        $uri_patterns        = []; // Initialize.
        $wp_login_url        = c::parseUrl(wp_login_url());
        $wp_registration_url = c::parseUrl(wp_registration_url());
        $wc_urls             = $this->collectWcUrls($no_cache);
        $bp_urls             = $this->collectBpUrls($no_cache);

        $uri_patterns[] = '**/wp-admin{/**,}';
        $uri_patterns[] = '**/{wp-*,xmlrpc}.php{/**,}';

        if (!empty($wp_login_url['path']) && $wp_login_url['path'] !== '/') {
            $uri_patterns[] = '/'.c::mbTrim($wp_login_url['path'], '/').'{/**,}';
        }
        if (!empty($wp_registration_url['path']) && $wp_registration_url['path'] !== '/') {
            $uri_patterns[] = '/'.c::mbTrim($wp_registration_url['path'], '/').'{/**,}';
        }
        foreach ($wc_urls as $_wc_url) { // WooCommerce systematics.
            if (($_wc_url = c::parseUrl($_wc_url))  && $_wc_url['path'] && $_wc_url['path'] !== '/') {
                $uri_patterns[] = '/'.c::mbTrim($_wc_url['path'], '/').'{/**,}';
            }
        } // unset($_wc_url); // Housekeeping.
        foreach ($bp_urls as $_bp_url) { // WooCommerce systematics.
            if (($_bp_url = c::parseUrl($_bp_url))  && $_bp_url['path'] && $_bp_url['path'] !== '/') {
                $uri_patterns[] = '/'.c::mbTrim($_bp_url['path'], '/').'{/**,}';
            }
        } // unset($_bp_url); // Housekeeping.

        $uri_patterns = array_unique(c::removeEmptys($uri_patterns));

        if ($as_regex) { // Conver to regex?
            $uri_patterns = c::wregx($uri_patterns);
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
