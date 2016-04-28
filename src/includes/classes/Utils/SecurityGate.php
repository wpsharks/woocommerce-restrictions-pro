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
     * Required post IDs.
     *
     * @since 16xxxx Security gate.
     *
     * @type int[] Post IDs.
     */
    protected $required_post_ids;

    /**
     * Required post types.
     *
     * @since 16xxxx Security gate.
     *
     * @type string[] Post types.
     */
    protected $required_post_types;

    /**
     * Required tax:term IDs.
     *
     * @since 16xxxx Security gate.
     *
     * @type string[] Tax:term IDs.
     */
    protected $required_tax_term_ids;

    /**
     * Required author IDs.
     *
     * @since 16xxxx Security gate.
     *
     * @type int[] Author IDs.
     */
    protected $required_author_ids;

    /**
     * Required URIs.
     *
     * @since 16xxxx Security gate.
     *
     * @type string[] URIs.
     */
    protected $required_uris;

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

        $this->required_post_ids     = [];
        $this->required_post_types   = [];
        $this->required_tax_term_ids = [];
        $this->required_author_ids   = [];
        $this->required_uris         = [];
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
        $this->guardUriAccess();
        $this->maybeGuardSingularAccess();
    }

    /**
     * Guard URI access.
     *
     * @since 16xxxx Security gate.
     */
    protected function guardUriAccess()
    {
        $this->required_uris[] = c::currentUri();
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
        $this->required_post_ids[]   = $post->ID;
        $this->required_post_types[] = $post->post_type;
        $this->required_author_ids[] = $post->post_author;

        foreach (get_post_taxonomies($post) as $_taxonomy) {
            $_terms = wp_get_post_terms($post->ID, $_taxonomy);
            if (!$_terms || !is_array($_terms)) {
                continue; // No terms.
            }
            foreach ($_terms as $_term) {
                $this->required_tax_term_ids[] = $_taxonomy.':'.$_term->term_id;
                foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                    $this->required_tax_term_ids[] = $_taxonomy.':'.$_ancestor_term_id;
                } // unset($_ancestor_term_id);
            } // unset($_term); // Housekeeping.
        } // unset($_taxonomy, $_terms); // Housekeeping.

        foreach (get_post_ancestors($post) as $_ancestor_post_id) {
            if (!($_ancestor_post = get_post($_ancestor_post_id))) {
                continue; // Nothing to do.
            }
            $this->required_post_ids[]   = $_ancestor_post->ID;
            $this->required_post_types[] = $_ancestor_post->post_type;
            $this->required_author_ids[] = $_ancestor_post->post_author;

            foreach (get_post_taxonomies($_ancestor_post) as $_taxonomy) {
                $_terms = wp_get_post_terms($_ancestor_post->ID, $_taxonomy);
                if (!$_terms || !is_array($_terms)) {
                    continue; // No terms.
                }
                foreach ($_terms as $_term) {
                    $this->required_tax_term_ids[] = $_taxonomy.':'.$_term->term_id;
                    foreach (get_ancestors($_term->term_id, $_taxonomy) as $_ancestor_term_id) {
                        $this->required_tax_term_ids[] = $_taxonomy.':'.$_ancestor_term_id;
                    } // unset($_ancestor_term_id);
                } // unset($_term); // Housekeeping.
            } // unset($_taxonomy, $_terms); // Housekeeping.
        } // unset($_ancestor_post_id, $_ancestor_post); // Housekeeping.
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
