<?php
/**
 * Security gate.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\Utils;

use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\Restrictions\Pro\Classes\CoreFacades as c;
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
     * Did security check?
     *
     * @since 160801 Security gate.
     *
     * @var bool Did check?
     */
    protected $did_check;

    /**
     * Restriction-related data.
     *
     * @since 160524 Security gate.
     *
     * @var array See {@link SecurityCheck{}).
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

        $this->data      = [];
        $this->did_check = false;
    }

    /**
     * On `wp_loaded` hook.
     *
     * @since 160524 Security gate.
     */
    public function onWpLoaded()
    {
        if (c::isCli()) {
            return; // Not applicable.
        }
        if ($this->Wp->is_admin) {
            $this->doCheck(); // Right now.
            //
        } else { // Wait until main query is ready.
            add_action('wp', [$this, 'onWp'], -100000);
        }
    }

    /**
     * On `wp` hook.
     *
     * @since 160524 Security gate.
     *
     * @param \WP $WP WordPress class.
     */
    public function onWp(\WP $WP)
    {
        $this->doCheck();
    }

    /**
     * Guard restrictions.
     *
     * @since 160801 Security gate.
     */
    protected function doCheck()
    {
        if ($this->did_check) {
            return; // Done already.
        }
        $this->did_check = true; // Doing it now.

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
        $post_id = 0; // Initialize.
        $uri     = c::currentUri();
        $WP_User = wp_get_current_user();

        if (!$this->Wp->is_admin) {
            global $wp_the_query;
            $WP_Query = $wp_the_query;

            if ($WP_Query && $WP_Query->is_singular()) {
                $WP_Post = $WP_Query->get_queried_object();
                $post_id = $WP_Post instanceof \WP_Post ? (int) $WP_Post->ID : 0;
            }
        }
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
        $redirect_to = s::applyFilters('security_gate_redirect_to', $redirect_to, $this->data);

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
        } elseif (!($arg_name = s::getOption('security_gate_redirect_arg_name'))) {
            return $redirect_to; // Not possible.
        }
        $restriction_ids = []; // Intialize array of required restriction IDs.

        foreach ($this->data['restricted_by'] as $_meta_key => $_restriction_ids) {
            $restriction_ids = array_merge($restriction_ids, $_restriction_ids);
        } // unset($_meta_key, $_restriction_ids); // Housekeeping.

        if ($restriction_ids && ($restriction_ids = array_unique($restriction_ids))) {
            $redirect_to = c::addUrlQueryArgs([$arg_name => implode('.', $restriction_ids)], $redirect_to);
        }
        return $redirect_to;
    }
}
