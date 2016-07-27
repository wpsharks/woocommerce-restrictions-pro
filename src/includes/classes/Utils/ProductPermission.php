<?php
/**
 * Product permission utilities.
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
 * Product permission utilities.
 *
 * @since 160524 Security gate.
 */
class ProductPermission extends SCoreClasses\SCore\Base\Core implements CoreInterfaces\SecondConstants
{
    /**
     * Access offset prefix.
     *
     * @since 160524 Product permissions.
     *
     * @var array Access offset prefix.
     */
    public $access_offset_prefix;

    /**
     * Access offset key prefix.
     *
     * @since 160524 Product permissions.
     *
     * @var array Access offset key prefix.
     */
    public $access_offset_key_prefix;

    /**
     * Expire offset suffix.
     *
     * @since 160524 Product permissions.
     *
     * @var array Expire offset suffix.
     */
    public $expire_offset_suffix;

    /**
     * Expire offset key suffix.
     *
     * @since 160524 Product permissions.
     *
     * @var array Expire offset key suffix.
     */
    public $expire_offset_key_suffix;

    /**
     * Access offset directives.
     *
     * @since 160524 Product permissions.
     *
     * @var array Access offset directives.
     */
    protected $access_offset_directives;

    /**
     * Expire offset directives.
     *
     * @since 160524 Product permissions.
     *
     * @var array Expire offset directives.
     */
    protected $expire_offset_directives;

    /**
     * Class constructor.
     *
     * @since 160524 Product permissions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->access_offset_prefix     = __('after', 'woocommerce-s2member-x');
        $this->access_offset_key_prefix = 'after'; // Hard-coded key.

        $this->expire_offset_suffix     = __('later', 'woocommerce-s2member-x');
        $this->expire_offset_key_suffix = 'later'; // Hard-coded key.
        /*
         * Special offset directives:
         *
         * - `immediately` is a valid access offset directive.
         *    i.e., as soon as order status allows access. No offset.
         *
         * - `naturally` (default behavior) is a valid expire offset directive.
         *    When a fixed-term subscription reaches it's expiration date (i.e., status `expired`).
         *    Or, when an incomplete order|subscription status occurs, access is revoked then too.
         *
         * - `naturally -expired` (minus `expired` status) is a valid expire offset directive.
         *   For a regular product, this normally provides lifetime access; i.e., under normal circumstances.
         *   For a fixed-term subscription, this provides access beyond the `expired` status; i.e., payments stop, access remains.
         *   In other words, if a fixed-term subscription reaches an expiration, access continues with this setting.
         *
         * - `never` provides unequivocal lifetime access.
         *   Even an incomplete order|subscription status will NOT revoke access.
         */
        $this->access_offset_directives = $this->expire_offset_directives = [];

        # Define special access offset directives.

        $most_common_optgroup_key                                  = '--- '.__('Most Common', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$most_common_optgroup_key] = '---'; // Creates an `<optgroup>`.

        $this->access_offset_directives['immediately'] = __('immediately', 'woocommerce-s2member-x');

        # Define special expire offset directives.

        $this->expire_offset_directives[$most_common_optgroup_key] = '---'; // Creates an `<optgroup>`.

        $this->expire_offset_directives['naturally']          = __('naturally', 'woocommerce-s2member-x');
        $this->expire_offset_directives['naturally -expired'] = __('naturally -expired', 'woocommerce-s2member-x');
        $this->expire_offset_directives['never']              = __('never (unequivocally)', 'woocommerce-s2member-x');

        # Period-based offset directives. Starting with minutes and working down from there.

        $minutes_optgroup_key                                  = '--- '.__('Minutes', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$minutes_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$minutes_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 60; $_i = $_i === 1 ? 5 : $_i + 5) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' minute' : $_i.' minutes');
            $_expire_key                                  = ($_i === 1 ? $_i.' minute' : $_i.' minutes').' '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s minute', '%1$s %2$s minutes', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s minute %2$s', '%1$s minutes %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $hours_optgroup_key                                  = '--- '.__('Hours', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$hours_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$hours_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 24; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' hour' : $_i.' hours');
            $_expire_key                                  = ($_i === 1 ? $_i.' hour' : $_i.' hours').' '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s hour', '%1$s %2$s hours', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s hour %2$s', '%1$s hours %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $days_optgroup_key                                  = '--- '.__('Days', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$days_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$days_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 7; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' day' : $_i.' days').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' day' : $_i.' days').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s day', '%1$s %2$s days', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s day %2$s', '%1$s days %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $upcoming_day_based_optgroup_key                                  = '--- '.__('Upcoming Weekday', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$upcoming_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$upcoming_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'this sunday' => __('upcoming sunday', 'woocommerce-s2member-x'),
            'this monday' => __('upcoming monday', 'woocommerce-s2member-x'),
            'this tuesday' => __('upcoming tuesday', 'woocommerce-s2member-x'),
            'this wednesday' => __('upcoming wednesday', 'woocommerce-s2member-x'),
            'this thursday' => __('upcoming thursday', 'woocommerce-s2member-x'),
            'this friday' => __('upcoming friday', 'woocommerce-s2member-x'),
            'this saturday' => __('upcoming saturday', 'woocommerce-s2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $next_day_based_optgroup_key                                  = '--- '.__('Next (After Upcoming) Day', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$next_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$next_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'next sunday' => __('next sunday', 'woocommerce-s2member-x'),
            'next monday' => __('next monday', 'woocommerce-s2member-x'),
            'next tuesday' => __('next tuesday', 'woocommerce-s2member-x'),
            'next wednesday' => __('next wednesday', 'woocommerce-s2member-x'),
            'next thursday' => __('next thursday', 'woocommerce-s2member-x'),
            'next friday' => __('next friday', 'woocommerce-s2member-x'),
            'next saturday' => __('next saturday', 'woocommerce-s2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $weeks_optgroup_key                                  = '--- '.__('Weeks', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$weeks_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$weeks_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 4; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' week' : $_i.' weeks').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' week' : $_i.' weeks').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s week', '%1$s %2$s weeks', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s week %2$s', '%1$s weeks %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $week_based_optgroup_key                                  = '--- '.__('Week-Based', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$week_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$week_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'saturday this week' => __('last day (saturday) of current week', 'woocommerce-s2member-x'),
            'monday next week' => __('first day (monday) of next week', 'woocommerce-s2member-x'),
            'saturday next week' => __('last day (saturday) of next week', 'woocommerce-s2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $months_optgroup_key                                  = '--- '.__('Months', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$months_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$months_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 12; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' month' : $_i.' months').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' month' : $_i.' months').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s month', '%1$s %2$s months', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s month %2$s', '%1$s months %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $month_based_optgroup_key                                  = '--- '.__('Month-Based', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$month_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$month_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'last day of this month' => __('last day of current month', 'woocommerce-s2member-x'),
            'first day of next month' => __('first day of next month', 'woocommerce-s2member-x'),
            'last day of next month' => __('last day of next month', 'woocommerce-s2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $years_optgroup_key                                  = '--- '.__('Years', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$years_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$years_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 5; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' year' : $_i.' years').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' year' : $_i.' years').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s year', '%1$s %2$s years', $_i, 'woocommerce-s2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s year %2$s', '%1$s years %2$s', $_i, 'woocommerce-s2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $year_based_optgroup_key                                  = '--- '.__('Year-Based', 'woocommerce-s2member-x').' ---';
        $this->access_offset_directives[$year_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$year_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            '1/1 next year -1 day' => __('last day of current year', 'woocommerce-s2member-x'),
            '1/1 next year' => __('first day of next year', 'woocommerce-s2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        if (s::applyFilters('enable_other_product_permission_offset_directives', !defined('WPLANG') || !WPLANG || mb_stripos(WPLANG, 'en') === 0)) {
            // ↑ Must limit the use of `other` to the english language due to lack of support for a locale in PHP's `strtotime()` function.

            $custom_entry_optgroup_key                                  = '--- '.__('Custom Entry', 'woocommerce-s2member-x').' ---';
            $this->access_offset_directives[$custom_entry_optgroup_key] = '---'; // Creates an `<optgroup>`.
            $this->expire_offset_directives[$custom_entry_optgroup_key] = '---'; // Creates an `<optgroup>`.

            $this->access_offset_directives['other'] = $this->expire_offset_directives['other'] = __('other', 'woocommerce-s2member-x');
        }
    }

    /**
     * Access offset directives.
     *
     * @since 160524 Security gate.
     *
     * @param bool $include_optgroups Include optgroups?
     *
     * @return array An array of access offset directives.
     */
    public function accessOffsetDirectives(bool $include_optgroups = false): array
    {
        $directives = $this->access_offset_directives; // Copy of the array.

        if (!$include_optgroups) {
            foreach ($directives as $_key => $_title) {
                if ($_title === '---') {
                    unset($directives[$_key]);
                } // Removes `---` optgroups.
            } // unset($_key, $_title); // Housekeeping.
        }
        return $directives;
    }

    /**
     * Expire offset directives.
     *
     * @since 160524 Security gate.
     *
     * @param bool $include_optgroups Include optgroups?
     *
     * @return array An array of expire offset directives.
     */
    public function expireOffsetDirectives(bool $include_optgroups = false): array
    {
        $directives = $this->expire_offset_directives; // Copy of the array.

        if (!$include_optgroups) {
            foreach ($directives as $_key => $_title) {
                if ($_title === '---') {
                    unset($directives[$_key]);
                } // Removes `---` optgroups.
            } // unset($_key, $_title); // Housekeeping.
        }
        return $directives;
    }
}
