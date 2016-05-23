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
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Systematics.
 *
 * @since 16xxxx Security gate.
 */
class Systematic extends SCoreClasses\SCore\Base\Core
{
    /**
     * Clear cache.
     *
     * @since 16xxxx Restrictions.
     */
    public function clearCache()
    {
        $this->cacheClear(); // Object cache.
        s::deleteTransient('systematic_wc_urls');
        s::deleteTransient('systematic_bp_urls');
    }

    /**
     * Post IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @return int[] Array of post IDs.
     */
    public function postIds(): array
    {
        global $blog_id; // Current blog ID.

        if (($post_ids = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
            return $post_ids; // Cached already.
        }
        $post_ids = []; // Initialize.
        $post_ids = array_merge($post_ids, [(int) s::getOption('security_gate_redirects_to_post_id')]);
        $post_ids = array_merge($post_ids, $this->collectWcPostIds());
        $post_ids = array_merge($post_ids, $this->collectBpPostIds());
        $post_ids = array_unique(c::removeEmptys($post_ids));

        return $post_ids = s::applyFilters('systematic_post_ids', $post_ids);
    }

    /**
     * Post types.
     *
     * @since 16xxxx Initial release.
     *
     * @return int[] Array of post IDs.
     */
    public function postTypes(): array
    {
        global $blog_id; // Current blog ID.

        if (($post_types = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
            return $post_types; // Cached already.
        }
        $post_types = []; // Initialize.
        $post_types = array_merge($post_types, [a::restrictionPostType()]);
        $post_types = array_merge($post_types, ['attachment', 'nav_menu_item']);
        $post_types = array_merge($post_types, [a::orderPostType(), a::subscriptionPostType(), a::couponPostType(), a::webhookPostType()]);
        $post_types = array_merge($post_types, wc_get_order_types());
        $post_types = array_unique(c::removeEmptys($post_types));

        return $post_types = s::applyFilters('systematic_post_types', $post_types);
    }

    /**
     * Roles.
     *
     * @since 16xxxx Initial release.
     *
     * @return string[] Array of roles.
     */
    public function roles(): array
    {
        global $blog_id; // Current blog ID.

        if (($roles = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
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
     * @param bool $compile Compile into arrays?
     *
     * @return array Array of URI patterns.
     */
    public function uriPatterns(bool $compile = true)
    {
        global $blog_id; // Current blog ID.

        if (($uri_patterns = &$this->cacheKey(__FUNCTION__, [$blog_id, $compile])) !== null) {
            return $uri_patterns; // Cached already.
        }
        $uri_patterns = []; // Initialize.

        $uri_patterns[] = '**/{wp-*,xmlrpc}.php{/**,}';

        $uri_patterns[] = c::urlToWRegxUriPattern(wp_login_url());
        $uri_patterns[] = c::urlToWRegxUriPattern(wp_registration_url());

        if (is_multisite()) { // There is a network admin panel?
            $uri_patterns[] = c::urlToWRegxUriPattern(network_admin_url());
        }
        foreach ($this->collectWcUrls() as $_wc_url) {
            $uri_patterns[] = c::urlToWRegxUriPattern($_wc_url);
        } // unset($_wc_url); // Housekeeping.

        foreach ($this->collectBpUrls() as $_bp_url) {
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
     * @return int[] Array of post IDs.
     */
    protected function collectWcPostIds(): array
    {
        global $blog_id; // Current blog ID.

        if (($wc_post_ids = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
            return $wc_post_ids; // Cached already.
        }
        $wc_post_ids = []; // Initialize.

        if (!c::canCallFunc('WC')) {
            return $wc_post_ids = []; // Not running.
        }
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
     * @return string[] Array of URLs.
     */
    protected function collectWcUrls(): array
    {
        $transient_cache_key = 'systematic_wc_urls';
        $post_type           = a::restrictionPostType();

        $no_cache = s::isMenuPageForPostType($post_type);
        if (!$no_cache && is_array($wc_urls = s::getTransient($transient_cache_key))) {
            return $wc_urls; // Cached already.
        }
        $wc_urls = []; // Initialize.

        if (!c::canCallFunc('WC')) {
            return $wc_urls = []; // Not running.
        }
        $home_url = get_home_url(); // Needed for comparison below.

        foreach (['shop', 'cart', 'terms', 'checkout', 'myaccount', 'view_order'] as $_wc_page) {
            if (($_wc_page_url = wc_get_page_permalink($_wc_page)) && $_wc_page_url !== $home_url) {
                $wc_urls[] = $_wc_page_url;
            }
        } // unset($_wc_page, $_wc_page_url); // Housekeeping.

        s::setTransient($transient_cache_key, $wc_urls, MINUTE_IN_SECONDS * 15);

        return $wc_urls;
    }

    /**
     * BP Post IDs.
     *
     * @since 16xxxx Initial release.
     *
     * @return int[] Array of post IDs.
     */
    protected function collectBpPostIds(): array
    {
        global $blog_id; // Current blog ID.

        if (($bp_post_ids = &$this->cacheKey(__FUNCTION__, $blog_id)) !== null) {
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
     * @return string[] Array of URLs.
     */
    protected function collectBpUrls(): array
    {
        $transient_cache_key = 'systematic_bp_urls';
        $post_type           = a::restrictionPostType();

        $no_cache = s::isMenuPageForPostType($post_type);
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
        s::setTransient($transient_cache_key, $bp_urls, MINUTE_IN_SECONDS * 15);

        return $bp_urls;
    }
}
