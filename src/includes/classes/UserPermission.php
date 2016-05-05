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
     * Permission data.
     *
     * @since 16xxxx User permission.
     *
     * @type \StdClass Permission data.
     */
    public $data; // Exposed publicly.

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
        $this->data         = $data;

        $this->data->ID             = (int) $this->data->ID;
        $this->data->user_id        = (int) $this->data->user_id;
        $this->data->order_id       = $this->data->order_id ? (int) $this->data->order_id : null;
        $this->data->product_id     = $this->data->product_id ? (int) $this->data->product_id : null;
        $this->data->restriction_id = (int) $this->data->restriction_id;

        $this->data->access_time  = (int) $this->data->access_time;
        $this->data->expire_time  = (int) $this->data->expire_time;
        $this->data->is_suspended = (int) $this->data->is_suspended;

        $this->data->insertion_time   = (int) $this->data->insertion_time;
        $this->data->last_update_time = (int) $this->data->last_update_time;
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
        if ($this->data->is_suspended) {
            return false;
        }
        if ($this->data->access_time) {
            if ($this->data->access_time > $this->current_time) {
                return false;
            }
        }
        if ($this->data->expire_time) {
            if ($this->data->expire_time <= $this->current_time) {
                return false;
            }
        }
        return true;
    }
}
