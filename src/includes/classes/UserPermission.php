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
 * User permission.
 *
 * @since 16xxxx Installer.
 */
class UserPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Current time.
     *
     * @since 16xxxx User permission.
     *
     * @type int Current time.
     */
    protected $current_time;

    /**
     * Class constructor.
     *
     * @since 16xxxx User permission.
     *
     * @param Classes\App $App  Instance.
     * @param \StdClass   $data Permission data.
     */
    public function __construct(Classes\App $App, \StdClass $data)
    {
        parent::__construct($App);

        $this->current_time = time();

        $data->ID         = (int) $data->ID;
        $data->user_id    = (int) $data->user_id;
        $data->order_id   = (int) $data->order_id;
        $data->product_id = (int) $data->product_id;

        $data->restriction_id          = (int) $data->restriction_id;
        $data->original_restriction_id = (int) $data->original_restriction_id;

        $data->access_time          = (int) $data->access_time;
        $data->original_access_time = (int) $data->original_access_time;

        $data->expire_time          = (int) $data->expire_time;
        $data->original_expire_time = (int) $data->original_expire_time;

        $data->is_enabled = (int) $data->is_enabled;

        $data->display_order = (int) $data->display_order;

        $data->insertion_time   = (int) $data->insertion_time;
        $data->last_update_time = (int) $data->last_update_time;

        $this->overload($data, true);
    }

    /**
     * Allowed to access?
     *
     * @since 16xxxx User permission.
     *
     * @return bool True if allowed to access.
     */
    public function isAllowed(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }
        if ($this->access_time) {
            if ($this->access_time > $this->current_time) {
                return false;
            }
        }
        if ($this->expire_time) {
            if ($this->expire_time <= $this->current_time) {
                return false;
            }
        }
        return true;
    }
}
