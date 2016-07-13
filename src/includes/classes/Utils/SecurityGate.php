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
 * Security gate.
 *
 * @since 160524 Security gate.
 */
class SecurityGate extends SCoreClasses\SCore\Base\Core
{
    /**
     * Restriction-related data.
     *
     * @since 160524 Security gate.
     *
     * @type array See {@link SecurityCheck{}).
     */
    protected $data; // See {@link SecurityCheck{}).

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

        $this->data = []; // Initialize.
    }

    /**
     * Guard restrictions.
     *
     * @since 160524 Security gate.
     */
    public function onWpLoaded()
    {
        if (c::isCli()) {
            return; // Not applicable.
        }
        if ($this->Wp->is_admin) {
            if ($this->restrictionsApply()) {
                $this->denyRedirect();
            }
        } else { // Wait until the main query is ready.
            add_action('wp', [$this, 'onWpGuardRestrictions'], -(PHP_INT_MAX - 10));
        }
    }

    /**
     * Guard restrictions.
     *
     * @since 160524 Security gate.
     *
     * @param \WP $WP WordPress base instance.
     */
    public function onWpGuardRestrictions(\WP $WP)
    {
        // This should fire once only, so let's remove it.
        remove_action('wp', [$this, 'onWpGuardRestrictions'], -(PHP_INT_MAX - 10));

        if ($this->restrictionsApply()) {
            $this->denyRedirect();
        }
    }

    /**
     * Do restrictions apply?
     *
     * @since 160524 Security gate.
     *
     * @return array See {@link SecurityCheck{}).
     */
    protected function restrictionsApply(): array
    {
        global $wp_the_query;

        if ($this->Wp->is_admin) {
            $post_id = 0;
            $uri     = c::currentUri();
        } else {
            if (!$wp_the_query->is_singular) {
                return []; // Not applicable.
            }
            if (!($post = $wp_the_query->get_queried_object())) {
                return []; // Not possible.
            }
            $post_id = $post->ID;
            $uri     = c::currentUri();
        }
        $WP_User = wp_get_current_user();

        return $this->data = a::restrictionsApply($post_id, $uri, $WP_User);
    }

    /**
     * Deny and redirect.
     *
     * @since 160524 Security gate.
     */
    protected function denyRedirect()
    {
        $redirect_to_post_id = (int) s::getOption('security_gate_redirects_to_post_id');
        $redirect_to         = $redirect_to_post_id ? get_permalink($redirect_to_post_id) : wp_login_url();
        $redirect_to         = !$redirect_to ? wp_login_url() : $redirect_to;

        $redirect_to = $this->maybeAddRedirectArgs($redirect_to);
        $redirect_to = s::applyFilters('security_gate_redirect_to', $redirect_to);

        wp_redirect($redirect_to, 307).exit(); // Stop on redirection.
    }

    /**
     * Maybe add redirect args.
     *
     * @since 160524 Security gate.
     *
     * @param string $redirect_to URL.
     *
     * @return string URL w/ possible redirect args.
     */
    protected function maybeAddRedirectArgs(string $redirect_to): string
    {
        if (!s::getOption('security_gate_redirect_to_args_enable')) {
            return $redirect_to; // Not applicable.
        }
        $args = $restriction_ids = []; // Initialize.

        foreach ($this->data['restricted_by'] as $_meta_key => $_restriction_ids) {
            $restriction_ids = array_merge($restriction_ids, $_restriction_ids);
        } // unset($_meta_key, $_restriction_ids); // Housekeeping.

        if (($restriction_ids = array_unique($restriction_ids))) {
            $args[$this->redirectArg('requires')] = implode('.', $restriction_ids);
        }
        $redirect_to = c::addUrlQueryArgs($args, $redirect_to);

        return $redirect_to;
    }

    /**
     * Redirect argument.
     *
     * @since 160524 Security gate.
     *
     * @param string $type Type of argument.
     *
     * @return string Query string argument name.
     */
    public function redirectArg(string $type): string
    {
        if ($type === 'requires') {
            $default = 'requires';
        } else {
            $default = $type; // Future consideration.
        }
        return s::applyFilters('security_gate_redirect_arg_'.$type, $default);
    }
}
