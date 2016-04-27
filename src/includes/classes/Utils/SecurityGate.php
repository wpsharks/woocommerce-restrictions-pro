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
     * Class constructor.
     *
     * @since 16xxxx Security gate.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);
    }

    /**
     * Guard restrictions.
     *
     * @since 16xxxx Security gate.
     */
    public function onInitGuardRestrictions()
    {
        $this->maybeGuardSingularAccess();
        $this->maybeGuardCategoryArchiveAccess();
        $this->maybeGuardTagArchiveAccess();
        $this->maybeGuardTaxArchiveAccess();
        $this->maybeGuardOtherAccess();
    }

    /**
     * Singular.
     *
     * @since 16xxxx Security gate.
     */
    protected function maybeGuardSingularAccess()
    {
        global $wp_the_query;

        if (!$wp_the_query->is_singular()) {
            return; // Not applicable.
        }
    }

    /**
     * Category archive.
     *
     * @since 16xxxx Security gate.
     */
    protected function maybeGuardCategoryArchiveAccess()
    {
        global $wp_the_query;

        if (!$wp_the_query->is_category()) {
            return; // Not applicable.
        }
    }

    /**
     * Tag archive.
     *
     * @since 16xxxx Security gate.
     */
    protected function maybeGuardTagArchiveAccess()
    {
        global $wp_the_query;

        if (!$wp_the_query->is_tag()) {
            return; // Not applicable.
        }
    }

    /**
     * Custom taxonomy archive.
     *
     * @since 16xxxx Security gate.
     */
    protected function maybeGuardTaxArchiveAccess()
    {
        global $wp_the_query;

        if (!$wp_the_query->is_tax()) {
            return; // Not applicable.
        }
    }

    /**
     * Anything else; e.g., URIs.
     *
     * @since 16xxxx Security gate.
     *
     * @note `is_date()`, `is_author()`, `is_search()`, `is_feed()`, `is_comment_feed()` should be covered here.
     *  i.e., If a site owner wants to protect these areas (or anything else) they will need URI patterns.
     */
    protected function maybeGuardOtherAccess()
    {
        global $wp_the_query;
    }
}
