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
class ProductPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Offset periods.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Offset periods.
     */
    public $offset_periods;

    /**
     * Offset period seconds.
     *
     * @since 16xxxx Product permissions.
     *
     * @type array Offset period seconds.
     */
    public $offset_period_seconds;

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

        $this->offset_periods = [
            'seconds' => __('seconds', 's2member-x'),
            'minutes' => __('minutes', 's2member-x'),
            'hours'   => __('hours', 's2member-x'),
            'days'    => __('days', 's2member-x'),
            'weeks'   => __('weeks', 's2member-x'),
            'years'   => __('years', 's2member-x'),
        ];
        $this->offset_period_seconds = [
            'seconds' => 1,
            'minutes' => 60,
            'hours'   => 3600,
            'days'    => 86400,
            'weeks'   => 604800,
            'years'   => 31536000,
        ];
    }
}
