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
 * Security gate.
 *
 * @since 16xxxx Security gate.
 */
class SecurityGate extends SCoreClasses\SCore\Base\Core
{
    /**
     * What's required?
     *
     * @since 16xxxx Security gate.
     *
     * @type array[]
     */
    protected $restrictions;

    /**
     * Required by what?
     *
     * @since 16xxxx Security gate.
     *
     * @type array[]
     */
    protected $restriction_ids;

    /**
     * Accessing what?
     *
     * @since 16xxxx Security gate.
     *
     * @type array[]
     */
    protected $accessing;

    /**
     * Is systematic?
     *
     * @since 16xxxx Security gate.
     *
     * @type bool
     */
    protected $is_systematic;

    /**
     * Class constructor.
     *
     * @since 16xxxx Security gate.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $by_meta_key           = a::restrictionsByMetaKey();
        $this->restrictions    = $by_meta_key['restrictions'];
        $this->restriction_ids = $by_meta_key['restriction_ids'];

        $this->accessing = array_fill_keys(a::restrictionMetaKeys(), []);
        unset($this->accessing['uri_patterns']); // Ditch this meta key.
        $this->accessing['uris'] = []; // in favor of this more-appropriate key.

        $this->is_systematic = false; // Initialize.
    }

    /**
     * Guard restrictions.
     *
     * @since 16xxxx Security gate.
     */
    public function onInitGuardRestrictions()
    {
        if (c::isCli()) {
            return; // Not applicable.
        }
        if (is_admin()) {
            return; // Not applicable.
        }
        $this->alwaysGuardUriAccess();
        $this->maybeGuardSingularAccess();
        $this->sanitizeComparisonData();
        $this->checkIfIsSystematic();
    }

    /**
     * Guard URI access.
     *
     * @since 16xxxx Security gate.
     */
    protected function alwaysGuardUriAccess()
    {
        $this->accessing['uris'][] = c::currentUri();
    }

    /**
     * Maybe guard singular access.
     *
     * @since 16xxxx Security gate.
     */
    protected function maybeGuardSingularAccess()
    {
        global $wp_the_query;

        if (!$wp_the_query->is_singular) {
            return; // Not applicable.
        }
        if (!($post = $wp_the_query->queried_object())) {
            return; // Not possible.
        }
        $this->accessing['post_ids'][]   = $post->ID;
        $this->accessing['post_types'][] = $post->post_type;
        $this->accessing['author_ids'][] = $post->post_author;

        foreach (get_post_taxonomies($post) as $_taxonomy) {
            $_terms = wp_get_post_terms($post->ID, $_taxonomy);
            if (!$_terms || !is_array($_terms)) {
                continue; // No terms.
            }
            foreach ($_terms as $_term) {
                $this->accessing['tax_term_ids'][] = $_taxonomy.':'.$_term->term_id;
                foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                    $this->accessing['tax_term_ids'][] = $_taxonomy.':'.$_ancestor_term_id;
                } // unset($_ancestor_term_id);
            } // unset($_term); // Housekeeping.
        } // unset($_taxonomy, $_terms); // Housekeeping.

        foreach (get_post_ancestors($post) as $_ancestor_post_id) {
            if (!($_ancestor_post = get_post($_ancestor_post_id))) {
                continue; // Nothing to do.
            }
            $this->accessing['post_ids'][]   = $_ancestor_post->ID;
            $this->accessing['post_types'][] = $_ancestor_post->post_type;
            $this->accessing['author_ids'][] = $_ancestor_post->post_author;

            foreach (get_post_taxonomies($_ancestor_post) as $_taxonomy) {
                $_terms = wp_get_post_terms($_ancestor_post->ID, $_taxonomy);
                if (!$_terms || !is_array($_terms)) {
                    continue; // No terms.
                }
                foreach ($_terms as $_term) {
                    $this->accessing['tax_term_ids'][] = $_taxonomy.':'.$_term->term_id;
                    foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                        $this->accessing['tax_term_ids'][] = $_taxonomy.':'.$_ancestor_term_id;
                    } // unset($_ancestor_term_id);
                } // unset($_term); // Housekeeping.
            } // unset($_taxonomy, $_terms); // Housekeeping.
        } // unset($_ancestor_post_id, $_ancestor_post); // Housekeeping.
    }

    /**
     * Sanitize comparison data.
     *
     * @since 16xxxx Security gate.
     */
    protected function sanitizeComparisonData()
    {
        $int_meta_keys = a::restrictionIntMetaKeys();

        foreach ($this->accessing as $_meta_key => &$_accessing) {
            $_accessing = array_map(in_array($_meta_key, $int_meta_keys, true) ? 'intval' : 'strval', $_accessing);
            $_accessing = array_unique(c::removeEmptys($_accessing));
        } // Must unset temp variable by reference.
        unset($_meta_key, $_accessing); // Housekeeping.
    }

    /**
     * Check systematics.
     *
     * @since 16xxxx Security gate.
     */
    protected function checkIfIsSystematic()
    {
        $systematic_post_ids = a::systematicPostIds();
        foreach ($this->accessing['post_ids'] as $_post_id) {
            if (in_array($_post_id, $systematic_post_ids, true)) {
                $this->is_systematic = true;
                return; // Done here.
            }
        } // unset($_post_id); // Housekeeping.

        $systematic_post_types = a::systematicPostTypes();
        foreach ($this->accessing['post_types'] as $_post_type) {
            if (in_array($_post_type, $systematic_post_types, true)) {
                $this->is_systematic = true;
                return; // Done here.
            }
        } // unset($_post_type); // Housekeeping.

        $systematic_roles = a::systematicRoles();
        $current_user     = wp_get_current_user();
        foreach ($this->accessing['roles'] as $_role) {
            if (in_array($_role, $current_user->roles, true)) {
                $this->is_systematic = true;
                return; // Done here.
            }
        } // unset($_role); // Housekeeping.

        $systematic_uri_patterns_as_regex = a::systematicUriPatterns(null, true);
        foreach ($this->accessing['uris'] as $_uri) {
            if (preg_grep($systematic_uri_patterns_as_regex, $this->accessing['uris'])) {
                $this->is_systematic = true;
                return; // Done here.
            }
        } // unset($_uri); // Housekeeping.
    }

    /**
     * Deny and redirect.
     *
     * @since 16xxxx Security gate.
     */
    protected function denyWithRedirection()
    {
        $redirects_to_post_id  = (int) s::getOption('security_gate_redirects_to_post_id');
        $redirect_to_url       = $redirects_to_post_id ? get_permalink($redirects_to_post_id) : home_url('/');
        $redirect_to_url_parts = $redirect_to_url ? parse_url($redirect_to_url) : [];

        $redirect_to = !$redirect_to_url_parts || $redirect_to_url_parts['uri'] === c::currentUri()
            ? wp_login_url() : $redirect_to_url; // Try hard to avoid loops.

        wp_redirect($redirect_to, 307);
        exit; // Stop here.
    }
}
