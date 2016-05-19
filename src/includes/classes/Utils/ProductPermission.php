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
 * Product permission utilities.
 *
 * @since 16xxxx Security gate.
 */
class ProductPermission extends SCoreClasses\SCore\Base\Core implements CoreInterfaces\SecondConstants
{
    /**
     * Access offset prefix.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Access offset prefix.
     */
    public $access_offset_prefix;

    /**
     * Access offset key prefix.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Access offset key prefix.
     */
    public $access_offset_key_prefix;

    /**
     * Expire offset suffix.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Expire offset suffix.
     */
    public $expire_offset_suffix;

    /**
     * Expire offset key suffix.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Expire offset key suffix.
     */
    public $expire_offset_key_suffix;

    /**
     * Access offset directives.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Access offset directives.
     */
    public $access_offset_directives;

    /**
     * Expire offset directives.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Expire offset directives.
     */
    public $expire_offset_directives;

    /**
     * Class constructor.
     *
     * @since 16xxxx Product permissions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->access_offset_prefix     = __('after', 's2member-x');
        $this->access_offset_key_prefix = 'after'; // Hard-coded key.

        $this->expire_offset_suffix     = __('later', 's2member-x');
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

        $most_common_optgroup_key                                  = '--- '.__('Most Common').' ---';
        $this->access_offset_directives[$most_common_optgroup_key] = '---'; // Creates an `<optgroup>`.

        $this->access_offset_directives['immediately'] = __('immediately', 's2member-x');

        # Define special expire offset directives.

        $this->expire_offset_directives[$most_common_optgroup_key] = '---'; // Creates an `<optgroup>`.

        $this->expire_offset_directives['naturally']          = __('naturally', 's2member-x');
        $this->expire_offset_directives['naturally -expired'] = __('naturally -expired', 's2member-x');
        $this->expire_offset_directives['never']              = __('never (unequivocally)', 's2member-x');

        # Period-based offset directives. Starting with minutes and working down from there.

        $minutes_optgroup_key                                  = '--- '.__('Minutes').' ---';
        $this->access_offset_directives[$minutes_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$minutes_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 60; $_i = $_i === 1 ? 5 : $_i + 5) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' minute' : $_i.' minutes');
            $_expire_key                                  = ($_i === 1 ? $_i.' minute' : $_i.' minutes').' '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s minute', '%1$s %2$s minutes', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s minute %2$s', '%1$s minutes %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $hours_optgroup_key                                  = '--- '.__('Hours').' ---';
        $this->access_offset_directives[$hours_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$hours_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 24; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' hour' : $_i.' hours');
            $_expire_key                                  = ($_i === 1 ? $_i.' hour' : $_i.' hours').' '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s hour', '%1$s %2$s hours', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s hour %2$s', '%1$s hours %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $days_optgroup_key                                  = '--- '.__('Days').' ---';
        $this->access_offset_directives[$days_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$days_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 7; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' day' : $_i.' days').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' day' : $_i.' days').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s day', '%1$s %2$s days', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s day %2$s', '%1$s days %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $upcoming_day_based_optgroup_key                                  = '--- '.__('Upcoming Weekday').' ---';
        $this->access_offset_directives[$upcoming_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$upcoming_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'this sunday' => __('upcoming sunday', 's2member-x'),
            'this monday' => __('upcoming monday', 's2member-x'),
            'this tuesday' => __('upcoming tuesday', 's2member-x'),
            'this wednesday' => __('upcoming wednesday', 's2member-x'),
            'this thursday' => __('upcoming thursday', 's2member-x'),
            'this friday' => __('upcoming friday', 's2member-x'),
            'this saturday' => __('upcoming saturday', 's2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $next_day_based_optgroup_key                                  = '--- '.__('Next (After Upcoming) Day').' ---';
        $this->access_offset_directives[$next_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$next_day_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'next sunday' => __('next sunday', 's2member-x'),
            'next monday' => __('next monday', 's2member-x'),
            'next tuesday' => __('next tuesday', 's2member-x'),
            'next wednesday' => __('next wednesday', 's2member-x'),
            'next thursday' => __('next thursday', 's2member-x'),
            'next friday' => __('next friday', 's2member-x'),
            'next saturday' => __('next saturday', 's2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $weeks_optgroup_key                                  = '--- '.__('Weeks').' ---';
        $this->access_offset_directives[$weeks_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$weeks_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 4; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' week' : $_i.' weeks').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' week' : $_i.' weeks').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s week', '%1$s %2$s weeks', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s week %2$s', '%1$s weeks %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $week_based_optgroup_key                                  = '--- '.__('Week-Based').' ---';
        $this->access_offset_directives[$week_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$week_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'saturday this week' => __('last day (saturday) of current week', 's2member-x'),
            'monday next week' => __('first day (monday) of next week', 's2member-x'),
            'saturday next week' => __('last day (saturday) of next week', 's2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $months_optgroup_key                                  = '--- '.__('Months').' ---';
        $this->access_offset_directives[$months_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$months_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 12; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' month' : $_i.' months').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' month' : $_i.' months').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s month', '%1$s %2$s months', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s month %2$s', '%1$s months %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $month_based_optgroup_key                                  = '--- '.__('Month-Based').' ---';
        $this->access_offset_directives[$month_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$month_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            'last day of this month' => __('last day of current month', 's2member-x'),
            'first day of next month' => __('first day of next month', 's2member-x'),
            'last day of next month' => __('last day of next month', 's2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        $years_optgroup_key                                  = '--- '.__('Years').' ---';
        $this->access_offset_directives[$years_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$years_optgroup_key] = '---'; // Creates an `<optgroup>`.

        for ($_i = 1; $_i <= 5; $_i = $_i + 1) {
            $_access_key                                  = $this->access_offset_key_prefix.' '.($_i === 1 ? $_i.' year' : $_i.' years').' 12:00 am';
            $_expire_key                                  = ($_i === 1 ? $_i.' year' : $_i.' years').' 11:59 pm '.$this->expire_offset_key_suffix;
            $this->access_offset_directives[$_access_key] = sprintf(_n('%1$s %2$s year', '%1$s %2$s years', $_i, 's2member-x'), $this->access_offset_prefix, $_i);
            $this->expire_offset_directives[$_expire_key] = sprintf(_n('%1$s year %2$s', '%1$s years %2$s', $_i, 's2member-x'), $_i, $this->expire_offset_suffix);
        } // unset($_i, $_access_key, $_expire_key); // Housekeeping.

        $year_based_optgroup_key                                  = '--- '.__('Year-Based').' ---';
        $this->access_offset_directives[$year_based_optgroup_key] = '---'; // Creates an `<optgroup>`.
        $this->expire_offset_directives[$year_based_optgroup_key] = '---'; // Creates an `<optgroup>`.

        foreach ([ // Special cases made easy.
            '1/1 next year -1 day' => __('last day of current year', 's2member-x'),
            '1/1 next year' => __('first day of next year', 's2member-x'),
        ] as $_key => $_title) { // For access & expire.
            $this->access_offset_directives[$_key.' 12:00 am'] = $_title;
            $this->expire_offset_directives[$_key.' 11:59 pm'] = $_title;
        } // unset($_key, $_title); // Housekeeping.

        if (s::applyFilters('enable_other_product_permission_offset_directives', !defined('WPLANG') || !WPLANG || mb_stripos(WPLANG, 'en') === 0)) {
            // ↑ Must limit the use of `other` to the english language due to lack of support for a locale in PHP's `strtotime()` function.

            $custom_entry_optgroup_key                                  = '--- '.__('Custom Entry').' ---';
            $this->access_offset_directives[$custom_entry_optgroup_key] = '---'; // Creates an `<optgroup>`.
            $this->expire_offset_directives[$custom_entry_optgroup_key] = '---'; // Creates an `<optgroup>`.

            $this->access_offset_directives['other'] = $this->expire_offset_directives['other'] = __('other', 's2member-x');
        }
    }
}
