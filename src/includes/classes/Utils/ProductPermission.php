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
     * Offset times.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Offset times.
     */
    public $offset_times;

    /**
     * Access offset times.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Access offset times.
     */
    public $access_offset_times;

    /**
     * Expire offset times.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Expire offset times.
     */
    public $expire_offset_times;

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

        $this->offset_times        = [];
        $this->access_offset_times = [];
        $this->expire_offset_times = [];
        /*
         * There are also some special keys:
         *
         * - `immediately` is a valid access time.
         *    i.e., as soon as order status allows access. No offset time.
         *
         * - `naturally` (default behavior) is a valid expire time.
         *    When a fixed-term subscription reaches it's expiration date (i.e., status `expired`).
         *    Or, when an incomplete order|subscription status occurs, access is revoked then too.
         *
         * - `naturally -expired` (minus `expired` status) is a valid expire time.
         *   For a regular product, this normally provides lifetime access; i.e., under normal circumstances.
         *   For a fixed-term subscription, this provides access beyond the `expired` status; i.e., payments stop, access doesn't :-)
         *   In other words, if a fixed-term subscription reaches an expiration, access continues with this setting.
         *
         * - `never` provides unequivocal lifetime access.
         *   Even an incomplete order|subscription status will NOT revoke access.
         */
        # Define special keys for access offset times.

        $this->access_offset_times['--- Most Common ---'] = '---';
        $this->access_offset_times['immediately']         = __('immediately', 's2member-x');

        # Define special keys for expire offset times.

        $this->expire_offset_times['--- Most Common ---'] = '---';
        $this->expire_offset_times['naturally']           = __('naturally', 's2member-x');
        $this->expire_offset_times['naturally -expired']  = __('naturally -expired', 's2member-x');
        $this->expire_offset_times['never']               = __('never (unequivocally)', 's2member-x');

        # True offset times now. Starting with minutes and working down from there.

        $this->offset_times['--- Minutes ---'] = '---';
        for ($_i = 1; $_i <= 60; $_i = $_i === 1 ? 5 : $_i + 5) {
            $_key                      = $_i === 1 ? $_i.' minute' : $_i.' minutes';
            $this->offset_times[$_key] = sprintf(_n('%1$s minute', '%1$s minutes', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Hours ---'] = '---';
        for ($_i = 1; $_i <= 24; $_i = $_i + 1) {
            $_key                      = $_i === 1 ? $_i.' hour' : $_i.' hours';
            $this->offset_times[$_key] = sprintf(_n('%1$s hour', '%1$s hours', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Days ---'] = '---';
        for ($_i = 1; $_i <= 7; $_i = $_i + 1) {
            $_key                      = $_i === 1 ? $_i.' day' : $_i.' days';
            $this->offset_times[$_key] = sprintf(_n('%1$s day', '%1$s days', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Weeks ---'] = '---';
        for ($_i = 1; $_i <= 4; $_i = $_i + 1) {
            $_key                      = $_i === 1 ? $_i.' week' : $_i.' weeks';
            $this->offset_times[$_key] = sprintf(_n('%1$s week', '%1$s weeks', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Months ---'] = '---';
        for ($_i = 1; $_i <= 12; $_i = $_i + 1) {
            $_key                      = $_i === 1 ? $_i.' month' : $_i.' months';
            $this->offset_times[$_key] = sprintf(_n('%1$s month', '%1$s months', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Years ---'] = '---';
        for ($_i = 1; $_i <= 5; $_i = $_i + 1) {
            $_key                      = $_i === 1 ? $_i.' year' : $_i.' years';
            $this->offset_times[$_key] = sprintf(_n('%1$s year', '%1$s years', $_i, 's2member-x'), $_i);
        } // unset($_i, $_key); // Housekeeping.

        $this->offset_times['--- Custom Entry ---'] = '---'; // A custom time.
        $this->offset_times['other']                = __('other', 's2member-x');

        $this->access_offset_times = array_merge($this->access_offset_times, $this->offset_times);
        $this->expire_offset_times = array_merge($this->expire_offset_times, $this->offset_times);
    }
}
