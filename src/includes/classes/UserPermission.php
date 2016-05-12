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
     * Permission ID.
     *
     * @since 16xxxx User permission.
     *
     * @type int Permission ID.
     */
    protected $ID;

    /**
     * User ID w/ permission.
     *
     * @since 16xxxx User permission.
     *
     * @type int User ID w/ permission.
     */
    protected $user_id;

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

        $this->ID      = (int) ($data->ID ?? 0);
        $this->user_id = (int) ($data->user_id ?? 0);
        unset($data->ID, $data->user_id); // Read-only.

        $data->order_id   = (int) ($data->order_id ?? 0);
        $data->product_id = (int) ($data->product_id ?? 0);

        $data->restriction_id          = (int) ($data->restriction_id ?? 0);
        $data->original_restriction_id = (int) ($data->original_restriction_id ?? 0);

        $data->access_time          = (int) ($data->access_time ?? 0);
        $data->original_access_time = (int) ($data->original_access_time ?? 0);

        $data->expire_time          = (int) ($data->expire_time ?? 0);
        $data->expire_time_via      = (string) ($data->expire_time_via ?? '');
        $data->expire_time_via_id   = (int) ($data->expire_time_via_id ?? 0);
        $data->original_expire_time = (int) ($data->original_expire_time ?? 0);

        $data->is_enabled = (int) ($data->is_enabled ?? 0);
        $data->is_trashed = (int) ($data->is_trashed ?? 0);

        $data->display_order = (int) ($data->display_order ?? 0);

        $data->insertion_time   = (int) ($data->insertion_time ?? 0);
        $data->last_update_time = (int) ($data->last_update_time ?? 0);

        $this->overload(['ID', 'user_id']); // Public read-only.
        $this->overload($data, true); // Public with full write access.
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
        if ($this->is_trashed) {
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
     * Delete this permission.
     *
     * @since 16xxxx User permission.
     */
    public function delete()
    {
        $WpDb  = s::wpDb(); // DB instance.
        $where = ['ID' => $this->ID]; // This ID.

        if ($this->ID) { // Delete existing permission w/ ID.
            s::doAction('before_user_permissions_delete', $where);
            $WpDb->delete(s::dbPrefix().'user_permissions', $where);
        }
        if ($this->user_id) { // Belonged to a user?
            a::clearUserPermissionsCache($this->user_id);
        }
        if ($this->ID) { // Deleted existing permission w/ ID.
            s::doAction('user_permissions_deleted', $where);
        }
    }

    /**
     * Update/insert/save permission.
     *
     * @since 16xxxx Adding user permissions.
     *
     * @param array $data Data to update (optional).
     *
     * @note The `$data` is optional, because it can also be set directly.
     *  In other words, this can simply be called to save the object properties.
     *  e.g., If the object was just contructed from input `$data`.
     *
     * @note This is not called during a mass update.
     */
    public function update(array $data = [])
    {
        $WpDb = s::wpDb(); // DB instance.

        if (!$this->user_id) { // Required user ID.
            throw new Exception('Missing `user_id`.');
        }
        foreach ($data as $_property_key => $_value) {
            if (!isset($this->¤¤writable_overload_properties[$_property_key])) {
                throw new Exception(sprintf('Trying to update read-only property: `%1$s`.', $_property_key));
            } elseif (!property_exists($this, $_property_key)) {
                throw new Exception(sprintf('Trying to update undefined property: `%1$s`.', $_property_key));
            } else { // Otherwise, allow the property update to occur.
                $this->{$_property_key} = $_value; // Write access.
            }
        } // unset($_property, $_value); // Housekeeping.

        if ($this->ID) { // Update existing permission.
            $this->last_update_time = time();

            $where       = ['ID' => $this->ID];
            $update_data = c::cloneArray($this->¤¤overload);
            unset($update_data['ID']); // Exclude primary key ID.

            s::doAction('before_user_permissions_update', $where, $update_data);

            if ($WpDb->update(s::dbPrefix().'user_permissions', $update_data, $where) === false) {
                $this->ID = 0; // Possible race condition; e.g., ID no longer exists.
                unset($data['ID']); // Unset a possible ID to avoid loops.
                return $this->update($data); // Insert new.
            }
            a::clearUserPermissionsCache($this->user_id); // Clear user cache.

            s::doAction('user_permissions_updated', $where, $update_data);
            //
        } else { // Insertion of a brand new permission.
            $this->original_restriction_id = $this->restriction_id;
            $this->original_access_time    = $this->access_time;
            $this->original_expire_time    = $this->expire_time;
            $this->insertion_time          = $this->last_update_time          = time();

            $insert_data = c::cloneArray($this->¤¤overload);
            unset($insert_data['ID']); // Exclude primary key ID.

            s::doAction('before_user_permissions_insert', [], $insert_data);

            if ((int) $WpDb->insert(s::dbPrefix().'user_permissions', $insert_data) !== 1 || !($this->ID = (int) $WpDb->insert_id)) {
                throw new Exception('User permission insertion failure.');
            }
            a::clearUserPermissionsCache($this->user_id); // Clear user cache.

            s::doAction('user_permissions_inserted', ['ID' => $this->ID], $insert_data);
        }
    }
}
