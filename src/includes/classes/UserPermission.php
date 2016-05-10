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

        $data->ID         = (int) ($data->ID ?? 0);
        $data->user_id    = (int) ($data->user_id ?? 0);
        $data->order_id   = (int) ($data->order_id ?? 0);
        $data->product_id = (int) ($data->product_id ?? 0);

        $data->restriction_id          = (int) ($data->restriction_id ?? 0);
        $data->original_restriction_id = (int) ($data->original_restriction_id ?? 0);

        $data->access_time          = (int) ($data->access_time ?? 0);
        $data->original_access_time = (int) ($data->original_access_time ?? 0);

        $data->expire_time          = (int) ($data->expire_time ?? 0);
        $data->original_expire_time = (int) ($data->original_expire_time ?? 0);

        $data->is_enabled = (int) ($data->is_enabled ?? 0);

        $data->display_order = (int) ($data->display_order ?? 0);

        $data->insertion_time   = (int) ($data->insertion_time ?? 0);
        $data->last_update_time = (int) ($data->last_update_time ?? 0);

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
        $time = time(); // Needed below.

        if ($this->access_time) {
            if ($this->access_time > $time) {
                return false;
            }
        }
        if ($this->expire_time) {
            if ($this->expire_time <= $time) {
                return false;
            }
        }
        return true;
    }

    /**
     * Update this permission.
     *
     * @since 16xxxx User permission.
     */
    public function update()
    {
        $WpDb = s::wpDb();

        if (!$this->user_id) { // Required!
            throw new Exception('Missing `user_id`.');
        }
        if ($this->ID) { // Update existing.
            $this->last_update_time = time();

            $_update_data = $this->¤¤overload; // Overload data.
            unset($_update_data['ID']); // Exclude primary key ID.

            if ($WpDb->update(s::dbPrefix().'user_permissions', $_update_data, ['ID' => $this->ID]) === false) {
                $this->ID = 0; // Possible race condition; e.g., ID no longer exists.
                $this->update(); // Try inserting as a new permission.
            }
        } else { // Insertion of a brand new permission.
            $this->original_restriction_id = $this->restriction_id;
            $this->original_access_time    = $this->access_time;
            $this->original_expire_time    = $this->expire_time;
            $this->insertion_time          = $this->last_update_time          = time();

            $_insertion_data = $this->¤¤overload; // Overload data.
            unset($_insertion_data['ID']); // Exclude primary key ID.

            if ((int) $WpDb->insert(s::dbPrefix().'user_permissions', $_insertion_data) !== 1 || !($this->ID = (int) $WpDb->insert_id)) {
                throw new Exception(__('User permission insertion failure.', 's2member-x'));
            }
        }
        a::clearUserPermissionsCache($this->user_id); // Clear permissions cache.
    }

    /**
     * Delete this permission.
     *
     * @since 16xxxx User permission.
     */
    public function delete()
    {
        $WpDb = s::wpDb();

        if ($this->ID) { // Update existing.
            $WpDb->delete(s::dbPrefix().'user_permissions', ['ID' => $this->ID]);
        }
        if ($this->user_id) { // Belongs to a user?
            a::clearUserPermissionsCache($this->user_id); // Clear permissions cache.
        }
    }
}
