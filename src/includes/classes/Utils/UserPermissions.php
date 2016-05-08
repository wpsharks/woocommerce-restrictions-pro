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
 * User permission utilities.
 *
 * @since 16xxxx Security gate.
 */
class UserPermissions extends SCoreClasses\SCore\Base\Core
{
    /**
     * Access RES prefix.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access RES prefix.
     */
    protected $access_res_prefix;

    /**
     * Access CCAP prefix.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access CCAP prefix.
     */
    protected $access_ccap_prefix;

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

        $this->access_res_prefix  = a::restrictionAccessResPrefix();
        $this->access_ccap_prefix = a::restrictionAccessCcapPrefix();
    }

    /**
     * Clear cache.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id User ID.
     */
    public function onCleanUserCache($user_id)
    {
        $this->clearCache($user_id);
    }

    /**
     * Clear cache.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id User ID.
     */
    public function clearCache($user_id)
    {
        if (!($user_id = (int) $user_id)) {
            return; // Not possible.
        }
        $this->cacheUnsetPattern('permissions', '*/'.$user_id);
        $this->cacheUnsetPattern('accessibleRestrictionIds', '*/'.$user_id);
    }

    /**
     * Current user has access to restrictions?
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int|string[]|int[] $restriction_ids_slugs ID(s) and/or slug(s).
     * @param string                    $satisfy               Defaults to `all` (`any` or `all`).
     *
     * @return bool True if the current user has access to restrictions.
     */
    public function currentHasAccessToRestrictions($restriction_ids_slugs, string $satisfy = 'all'): bool
    {
        return $this->hasAccessToRestrictions(get_current_user_id(), $restriction_ids_slugs, $satisfy);
    }

    /**
     * A user has access to restrictions?
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int                $user_id               User ID.
     * @param string|int|string[]|int[] $restriction_ids_slugs ID(s) and/or slug(s).
     * @param string                    $satisfy               Defaults to `all` (`any` or `all`).
     *
     * @return bool True if the user has access to restrictions.
     */
    public function hasAccessToRestrictions($user_id, $restriction_ids_slugs, string $satisfy = 'all'): bool
    {
        if (!($user_id = (int) $user_id)) {
            return false; // No access.
        }
        $restriction_ids_slugs      = (array) $restriction_ids_slugs;
        $restriction_ids            = a::restrictionSlugsToIds($restriction_ids_slugs);
        $accessible_restriction_ids = $this->accessibleRestrictionIds($user_id);

        if (!$restriction_ids || !$accessible_restriction_ids) {
            return false; // Not possible or no access.
        }
        foreach ($restriction_ids as $_restriction_id) {
            if ($satisfy === 'any') { // Any of the restrictions.
                if (in_array($_restriction_id, $accessible_restriction_ids, true)) {
                    return true; // Accessible (if any are satisfied).
                }
            } elseif (!in_array($_restriction_id, $accessible_restriction_ids, true)) {
                return false; // Not accessible (must satisfy all).
            }
        } // unset($_restriction_id); // Housekeeping.
        return $satisfy === 'any' ? false : true; // Default handling.
    }

    /**
     * Filter user capabilities.
     *
     * @since 16xxxx Security gate.
     *
     * @param array    $user_caps     {@link \WP_User::$allcaps}.
     * @param array    $required_caps Required caps, after having already run {@link map_meta_cap()}.
     * @param array    $args          The full set of arguments to {@link \WP_User::has_cap()}.
     * @param \WP_User $WP_User       The user object class instance.
     *
     * @return array Dynamically modified array of `$user_caps` for {@link \WP_User::has_cap()}.
     */
    public function onUserHasCap(array $user_caps, array $required_caps, array $args, \WP_User $WP_User): array
    {
        if (!($has_cap = (string) ($args[0] ?? ''))) {
            return $user_caps; // Not possible.
        }
        $systematic_roles         = a::systematicRoles();
        $restriction_ids_by_slug  = a::restrictionIdsBySlug();
        $restrictions_by_meta_key = a::restrictionsByMetaKey();
        $restrictions             = $restrictions_by_meta_key['restrictions'];
        $restriction_ids          = $restrictions_by_meta_key['restriction_ids'];

        // Add role-based caps granted by restrictions the user can access.
        foreach ($restriction_ids['roles'] as $_role => $_restriction_ids) {
            if ($this->hasAccessToRestrictions($WP_User->ID, $_restriction_ids, 'any')) {
                if (!in_array($_role, $systematic_roles, true) && ($_role_object = get_role($_role))) {
                    $user_caps = array_merge($user_caps, $_role_object->capabilities);
                }
            }
        } // unset($_role, $_restriction_ids, $_role_object); // Housekeeping.

        // Check for the special `access_res_` prefix.
        if (mb_strpos($has_cap, $this->access_res_prefix) === 0) {
            $_slug = c::strReplaceOnce($this->access_res_prefix, '', $has_cap);
            // Note that a slug in this context can contain almost anything.
            // See: <http://wordpress.stackexchange.com/a/149192/81760>

            if (!empty($restriction_ids_by_slug[$_slug])) {
                $_restriction_id = $restriction_ids_by_slug[$_slug];
                if ($this->hasAccessToRestrictions($WP_User->ID, [$_restriction_id])) {
                    $user_caps[$has_cap] = true;
                }
            } // unset($_slug, $_restriction_id);

        // Check for the special `access_ccap_` prefix.
        } elseif (mb_strpos($has_cap, $this->access_ccap_prefix) === 0) {
            $_ccap = c::strReplaceOnce($this->access_ccap_prefix, '', $has_cap);

            if (in_array($_ccap, $restrictions['ccaps'], true)) {
                $_by_restriction_ids = $restriction_ids['ccaps'][$_ccap];
                if ($this->hasAccessToRestrictions($WP_User->ID, $_by_restriction_ids, 'any')) {
                    $user_caps[$has_cap] = true;
                }
            } // unset($_ccap, $_by_restriction_ids);
        }
        return $user_caps;
    }

    /**
     * Accessible restriction IDs.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id User ID.
     *
     * @return int[] Accessible restriction IDs.
     */
    public function accessibleRestrictionIds($user_id): array
    {
        global $blog_id; // Current blog ID.

        if (!($user_id = (int) $user_id)) {
            return []; // Not possible.
        }
        if (($accessible_restriction_ids = &$this->cacheGet(__FUNCTION__, $blog_id.'/'.$user_id)) !== null) {
            return $accessible_restriction_ids; // Cached already.
        }
        $accessible_restriction_ids = []; // Initialize.

        foreach ($this->permissions($user_id) as $_UserPermission) {
            if (!isset($accessible_restriction_ids[$_UserPermission->data->restriction_id]) && $_UserPermission->isAllowed()) {
                $accessible_restriction_ids[$_UserPermission->data->restriction_id] = $_UserPermission->data->restriction_id;
            }
        } // unset($_UserPermission); // Housekeeping.

        return s::applyFilters('user_accessible_restriction_ids', $accessible_restriction_ids, $user_id);
    }

    /**
     * Permissions for a user ID.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id User ID.
     *
     * @return UserPermission[] Permissions.
     */
    public function permissions($user_id): array
    {
        global $blog_id; // Current blog ID.

        if (!($user_id = (int) $user_id)) {
            return []; // Not possible.
        }
        if (($permissions = &$this->cacheGet(__FUNCTION__, $blog_id.'/'.$user_id)) !== null) {
            return $permissions; // Cached already.
        }
        $WpDb = s::wpDb(); // WP database object class.

        $sql = /* Query all user permissions. */ '
            SELECT * FROM `'.esc_sql(s::dbPrefix().'user_permissions').'`
                WHERE `user_id` = %s';
        $sql = $WpDb->prepare($sql, $user_id);

        if (!($results = $WpDb->get_results($sql))) {
            return $permissions = []; // None.
        }
        $permissions             = []; // Initialize.
        $restriction_ids_by_slug = a::restrictionIdsBySlug();

        foreach ($results as $_key => &$_data) {
            $_data->ID = (int) $_data->ID; // Force integer.
            if (in_array($_data->ID, $restriction_ids_by_slug, true)) {
                $permissions[$_data->ID] = $this->App->Di->get(Classes\UserPermission::class, ['data' => $_data]);
            }
        } // unset($_key, $_data); // Housekeeping.

        return s::applyFilters('user_permissions', $permissions, $user_id);
    }
}
