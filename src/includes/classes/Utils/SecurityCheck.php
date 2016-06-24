<?php
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
 * Security check.
 *
 * @since 160524 Security gate.
 */
class SecurityCheck extends SCoreClasses\SCore\Base\Core
{
    /**
     * Restriction meta keys.
     *
     * @since 160524 Security gate.
     *
     * @type array Meta keys.
     */
    protected $restriction_meta_keys;

    /**
     * Restriction integer meta keys.
     *
     * @since 160524 Security gate.
     *
     * @type array Integer meta keys.
     */
    protected $restriction_int_meta_keys;

    /**
     * Class constructor.
     *
     * @since 160524 Security gate.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->restriction_meta_keys     = a::restrictionMetaKeys();
        $this->restriction_int_meta_keys = a::restrictionIntMetaKeys();
    }

    /**
     * Restrictions apply?
     *
     * @since 160524 Security gate.
     *
     * @param string|int    $post_id        Post ID.
     * @param string        $url_uri        URL (or URI).
     * @param \WP_User|null $WP_User        User accessing.
     * @param bool          $post_id_lookup Perform lookup?
     *
     * @return array `['accessing', 'restricted', 'restricted_by']`.
     *               Returns a non-empty array if restrictions apply.
     */
    public function restrictionsApply($post_id = 0, string $url_uri = '', \WP_User $WP_User = null, bool $post_id_lookup = false): array
    {
        $accessing = $restricted = $restricted_by = [];

        $this->accessingInit($accessing);
        $this->restrictedInit($restricted);
        $this->restrictedByInit($restricted_by);

        if (($post_id = (int) $post_id)) {
            $this->compilePostAccess($post_id, $accessing);
        }
        if (isset($url_uri[0])) { // With a possible post ID lookup.
            if (!$post_id && $post_id_lookup && ($post_id = s::urlToPostId($url_uri))) {
                $this->compilePostAccess($post_id, $accessing);
            }
            $this->compileUriAccess($url_uri, $accessing);
        }
        if ($WP_User) { // User-based compilations.
            $this->compileRoleAccess($WP_User, $accessing);
        }
        $this->sanitizeAccessData($accessing); // Cleanup, force data types.

        if (!($is_systematic = $this->checkIfIsSystematic($accessing, $WP_User))) {
            if (($is_restricted = $this->checkIfIsRestricted($accessing, $restricted, $restricted_by, $WP_User))) {
                $this->sanitizeRestrictedData($restricted);
                $this->sanitizeRestrictedByData($restricted_by);
                return compact('accessing', 'restricted', 'restricted_by');
            }
        }
        return []; // Not restricted in this case.
    }

    /**
     * Compile post access.
     *
     * @since 160524 Security gate.
     *
     * @param int   $post_id    Post ID.
     * @param array &$accessing Array; by reference.
     */
    protected function compilePostAccess(int $post_id, array &$accessing)
    {
        if (!$post_id) { // Empty?
            return; // Not possible.
        }
        if (!($post = get_post($post_id))) {
            return; // Not possible.
        }
        $accessing['post_ids'][]   = $post->ID;
        $accessing['post_types'][] = $post->post_type;
        $accessing['author_ids'][] = $post->post_author;

        if (($permalink = get_permalink($post->ID))) {
            $this->compileUriAccess($permalink, $accessing);
        }
        foreach (get_post_taxonomies($post) as $_taxonomy) {
            $_terms = wp_get_post_terms($post->ID, $_taxonomy);
            if (!$_terms || !is_array($_terms)) {
                continue; // No terms.
            }
            foreach ($_terms as $_term) {
                $accessing['tax_term_ids'][] = $_taxonomy.':'.$_term->term_id;
                foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                    $accessing['tax_term_ids'][] = $_taxonomy.':'.$_ancestor_term_id;
                } // unset($_ancestor_term_id);
            } // unset($_term); // Housekeeping.
        } // unset($_taxonomy, $_terms); // Housekeeping.

        foreach (get_post_ancestors($post) as $_ancestor_post_id) {
            if (!($_ancestor_post = get_post($_ancestor_post_id))) {
                continue; // Nothing to do.
            }
            $accessing['post_ids'][]   = $_ancestor_post->ID;
            $accessing['post_types'][] = $_ancestor_post->post_type;
            $accessing['author_ids'][] = $_ancestor_post->post_author;

            foreach (get_post_taxonomies($_ancestor_post) as $_taxonomy) {
                $_terms = wp_get_post_terms($_ancestor_post->ID, $_taxonomy);
                if (!$_terms || !is_array($_terms)) {
                    continue; // No terms.
                }
                foreach ($_terms as $_term) {
                    $accessing['tax_term_ids'][] = $_taxonomy.':'.$_term->term_id;
                    foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                        $accessing['tax_term_ids'][] = $_taxonomy.':'.$_ancestor_term_id;
                    } // unset($_ancestor_term_id);
                } // unset($_term); // Housekeeping.
            } // unset($_taxonomy, $_terms); // Housekeeping.
        } // unset($_ancestor_post_id, $_ancestor_post); // Housekeeping.
    }

    /**
     * Compile URI access.
     *
     * @since 160524 Security gate.
     *
     * @param string $url_uri    Input URL (or URI).
     * @param array  &$accessing Array; by reference.
     */
    protected function compileUriAccess(string $url_uri, array &$accessing)
    {
        if (!isset($url_uri[0])) {
            return; // Not possible.
        }
        if (!($parts = c::parseUrl($url_uri))) {
            return; // Not possible.
        }
        $uri  = '/'.c::mbLTrim(preg_split('/#/u', $parts['uri'] ?? '', 2)[0], '/');
        $path = '/'.c::mbLTrim($parts['path'] ?? '', '/');

        $accessing['uris'][]      = $uri;
        $accessing['uri_paths'][] = $path;
    }

    /**
     * Compile role access.
     *
     * @since 160524 Security gate.
     *
     * @param \WP_User $WP_User    User accessing.
     * @param array    &$accessing Array; by reference.
     */
    protected function compileRoleAccess(\WP_User $WP_User, array &$accessing)
    {
        $accessing['roles'] = array_merge($accessing['roles'], $WP_User->roles);
    }

    /**
     * Check systematics.
     *
     * @since 160524 Security gate.
     *
     * @param array         &$accessing Array; by reference.
     * @param \WP_User|null $WP_User    User accessing.
     *
     * @return bool True if is systematic (or `$WP_User` roles are systematic).
     */
    protected function checkIfIsSystematic(array &$accessing, \WP_User $WP_User = null): bool
    {
        $systematic_post_ids     = a::systematicPostIds();
        $systematic_post_types   = a::systematicPostTypes();
        $systematic_roles        = a::systematicRoles();
        $systematic_uri_patterns = a::systematicUriPatterns();

        foreach (['post_ids', 'post_types', 'roles', 'uri_patterns'] as $_meta_key) {
            if ($_meta_key === 'uri_patterns') {
                if (!$accessing['uris'] && !$accessing['uri_paths']) {
                    continue; // Not accessing.
                }
            } elseif (!$accessing[$_meta_key]) { // Everything else.
                continue; // Not accessing.
            }
            foreach (${'systematic_'.$_meta_key} as $_systematic) {
                if ($_meta_key === 'uri_patterns') {
                    if ($_systematic['against'] === 'uri') {
                        if (preg_grep($_systematic['regex'], $accessing['uris'])) {
                            return true; // Systematic.
                        }
                    } elseif ($_systematic['against'] === 'uri_path') {
                        if (preg_grep($_systematic['regex'], $accessing['uri_paths'])) {
                            return true; // Systematic.
                        }
                    } // ↓ Everything else is a simple `in_array()` scan.
                } elseif (in_array($_systematic, $accessing[$_meta_key], true)) {
                    return true; // Systematic.
                }
            } // unset($_systematic);
        } // unset($_meta_key); // Housekeeping.

        return false; // Default response.
    }

    /**
     * Check restrictions.
     *
     * @since 160524 Security gate.
     *
     * @param array         &$accessing     Array; by reference.
     * @param array         &$restricted    Array; by reference.
     * @param array         &$restricted_by Array; by reference.
     * @param \WP_User|null $WP_User        User accessing.
     *
     * @return bool True if restricted and `$WP_User` lacks access.
     */
    protected function checkIfIsRestricted(array &$accessing, array &$restricted, array &$restricted_by, \WP_User $WP_User = null): bool
    {
        $restrictions_by_meta_key = a::restrictionsByMetaKey();
        $restrictions             = $restrictions_by_meta_key['restrictions'];
        $restriction_ids          = $restrictions_by_meta_key['restriction_ids'];

        foreach (array_diff($this->restriction_meta_keys, ['roles', 'ccaps']) as $_meta_key) {
            if ($_meta_key === 'uri_patterns') {
                if (!$accessing['uris'] && !$accessing['uri_paths']) {
                    continue; // Not accessing.
                }
            } elseif (!$accessing[$_meta_key]) { // Everything else.
                continue; // Not accessing.
            }
            foreach ($restrictions[$_meta_key] as $_restriction) {
                if ($_meta_key === 'uri_patterns') {
                    if ($_restriction['against'] === 'uri') {
                        if (!preg_grep($_restriction['regex'], $accessing['uris'])) {
                            continue; // Not accessing.
                        }
                    } elseif ($_restriction['against'] === 'uri_path') {
                        if (!preg_grep($_restriction['regex'], $accessing['uri_paths'])) {
                            continue; // Not accessing.
                        }
                    } else { // `wregx` key (original).
                        $_restriction = $_restriction['wregx'];
                    } // ↓ Everything else is a simple `in_array()` scan.
                } elseif (!in_array($_restriction, $accessing[$_meta_key], true)) {
                    continue; // Not accessing.
                }
                $_by_restriction_ids = $restriction_ids[$_meta_key][$_restriction];

                if (!$WP_User || !a::userHas($WP_User->ID, $_by_restriction_ids, 'any')) {
                    $is_restricted             = true;
                    $restricted[$_meta_key][]  = $_restriction;
                    $restricted_by[$_meta_key] = array_merge(
                        $restricted_by[$_meta_key],
                        $_by_restriction_ids
                    );
                }
            } // unset($_restriction, $_by_restriction_ids);
        } // unset($_meta_key); // Just some final housekeeping.

        return !empty($is_restricted);
    }

    /**
     * Initialize the `$accessing` array.
     *
     * @since 160524 Security gate.
     *
     * @param array &$accessing Array; by reference.
     */
    protected function accessingInit(array &$accessing)
    {
        $accessing = array_fill_keys($this->restriction_meta_keys, []);

        unset($accessing['uri_patterns']); // Ditch!
        $accessing['uris']      = []; // In favor of this.
        $accessing['uri_paths'] = []; // And this too.
    }

    /**
     * Sanitize `$accessing` data.
     *
     * @since 160524 Security gate.
     *
     * @param array &$accessing Array; by reference.
     */
    protected function sanitizeAccessData(array &$accessing)
    {
        foreach ($accessing as $_meta_key => &$_accessing) {
            $_accessing = array_map(in_array($_meta_key, $this->restriction_int_meta_keys, true) ? 'intval' : 'strval', $_accessing);
            $_accessing = array_unique(c::removeEmptys($_accessing));
        } // Must unset temp variable by reference.
        unset($_meta_key, $_accessing); // Housekeeping.
    }

    /**
     * Initialize the `$restricted` array.
     *
     * @since 160524 Security gate.
     *
     * @param array &$restricted Array; by reference.
     */
    protected function restrictedInit(array &$restricted)
    {
        $restricted = array_fill_keys($this->restriction_meta_keys, []);
    }

    /**
     * Sanitize `$restricted` data.
     *
     * @since 160524 Security gate.
     *
     * @param array &$restricted Array; by reference.
     */
    protected function sanitizeRestrictedData(array &$restricted)
    {
        foreach ($restricted as $_meta_key => &$_restricted) {
            $_restricted = array_map(in_array($_meta_key, $this->restriction_int_meta_keys, true) ? 'intval' : 'strval', $_restricted);
            $_restricted = array_unique(c::removeEmptys($_restricted));
        } // Must unset temp variable by reference.
        unset($_meta_key, $_restricted); // Housekeeping.
    }

    /**
     * Initialize the `$restricted_by` array.
     *
     * @since 160524 Security gate.
     *
     * @param array &$restricted_by Array; by reference.
     */
    protected function restrictedByInit(array &$restricted_by)
    {
        $restricted_by = array_fill_keys($this->restriction_meta_keys, []);
    }

    /**
     * Sanitize `$restricted_by` data.
     *
     * @since 160524 Security gate.
     *
     * @param array &$restricted_by Array; by reference.
     */
    protected function sanitizeRestrictedByData(array &$restricted_by)
    {
        foreach ($restricted_by as $_meta_key => &$_restricted_by) {
            $_restricted_by = array_map('intval', $_restricted_by);
            $_restricted_by = array_unique(c::removeEmptys($_restricted_by));
        } // Must unset temp variable by reference.
        unset($_meta_key, $_restricted_by); // Housekeeping.
    }
}
