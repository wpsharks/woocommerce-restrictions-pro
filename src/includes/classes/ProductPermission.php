<?php
declare (strict_types = 1);
namespace WebSharks\WpSharks\s2MemberX\Pro\Classes;

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
 * Product permission.
 *
 * @since 16xxxx Product permission.
 */
class ProductPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Valid offset periods.
     *
     * @since 16xxxx User permission.
     *
     * @type array Valid offset periods.
     */
    protected $valid_offset_periods;

    /**
     * Valid offset period a seconds.
     *
     * @since 16xxxx User permission.
     *
     * @type array Valid offset period a seconds.
     */
    protected $valid_offset_period_seconds;

    /**
     * Class constructor.
     *
     * @since 16xxxx Product permission.
     *
     * @param Classes\App $App  Instance.
     * @param \StdClass   $data Permission data.
     */
    public function __construct(Classes\App $App, \StdClass $data)
    {
        parent::__construct($App);

        $this->valid_offset_periods        = a::productPermissionOffsetPeriods();
        $this->valid_offset_period_seconds = a::productPermissionOffsetPeriodSeconds();

        $this->fillProperties($data); // Overloading.
    }

    /**
     * A valid permission?
     *
     * @since 16xxxx Product permission.
     *
     * @return bool True if permission is valid.
     */
    public function isValid(): bool
    {
        if (!$this->product_id) {
            return false;
        } elseif (!$this->restriction_id) {
            return false;
        } elseif ($this->access_time_offset_period &&
            !isset($this->valid_offset_periods[$this->access_time_offset_period])) {
            return false;
        } elseif ($this->expire_time_offset_period &&
            !isset($this->valid_offset_periods[$this->expire_time_offset_period])) {
            return false;
        }
        return true; // No problems.
    }

    /**
     * Access time offset (in seconds).
     *
     * @since 16xxxx Product permission.
     *
     * @return int Access time offset (in seconds).
     */
    public function accessTimeOffset(): int
    {
        if (!$this->isValid()) {
            return 0; // Not valid.
        } elseif (!$this->access_time_offset_count) {
            return 0; // Not applicable.
        } elseif (!$this->access_time_offset_period) {
            return 0; // Not applicable.
        }
        $offset_period_seconds = // Convert period to seconds.
            $this->valid_offset_period_seconds[$this->access_time_offset_period];

        return $this->access_time_offset_count * $offset_period_seconds;
    }

    /**
     * Expire time offset (in seconds).
     *
     * @since 16xxxx Product permission.
     *
     * @return int Expire time offset (in seconds).
     */
    public function expireTimeOffset(): int
    {
        if (!$this->isValid()) {
            return 0; // Not valid.
        } elseif (!$this->expire_time_offset_count) {
            return 0; // Not applicable.
        } elseif (!$this->expire_time_offset_period) {
            return 0; // Not applicable.
        }
        $offset_period_seconds = // Convert period to seconds.
            $this->valid_offset_period_seconds[$this->expire_time_offset_period];

        return $this->accessTimeOffset() + ($this->expire_time_offset_count * $offset_period_seconds);
    }

    /**
     * Fill properties from data.
     *
     * @since 16xxxx Adding user permissions.
     *
     * @param \StdClass $data Object properties.
     */
    protected function fillProperties(\StdClass $data)
    {
        $is_overloaded = $this->isOverloaded();

        if ($is_overloaded && isset($data->product_id)) {
            throw new Exception('Trying to update contruct-only property.');
        }
        if (!$is_overloaded) { // Called by constructor?
            $is_overloaded = true; // Overloading now.

            $defaults = [
                'product_id'     => 0,
                'restriction_id' => 0,

                'access_time_offset_count'  => 0,
                'access_time_offset_period' => '',

                'expire_time_offset_count'  => 0,
                'expire_time_offset_period' => '',
            ];
            $defaults = (object) $defaults; // Converts to object now.
            // Separate line; see: <https://bugs.php.net/bug.php?id=72219>

            $this->overload($defaults, true); // Public read/write access for performance.
            // Please do NOT write to properties directly. Use {@link update()}.
        }
        $this->product_id     = abs((int) ($data->product_id ?? $this->product_id ?? 0));
        $this->restriction_id = abs((int) ($data->restriction_id ?? $this->restriction_id ?? 0));

        $this->access_time_offset_count  = abs((int) ($data->access_time_offset_count ?? $this->access_time_offset_count ?? 0));
        $this->access_time_offset_period = mb_strtolower((string) ($data->access_time_offset_period ?? $this->access_time_offset_period ?? ''));

        $this->expire_time_offset_count  = abs((int) ($data->expire_time_offset_count ?? $this->expire_time_offset_count ?? 0));
        $this->expire_time_offset_period = mb_strtolower((string) ($data->expire_time_offset_period ?? $this->expire_time_offset_period ?? ''));
    }
}
