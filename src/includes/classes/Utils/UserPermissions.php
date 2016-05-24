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
#
use function assert as debug;
use function get_defined_vars as vars;

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
     * Access RES prefix regex.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access RES prefix regex.
     */
    protected $access_res_prefix_regex;

    /**
     * Access CCAP prefix regex.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access CCAP prefix regex.
     */
    protected $access_ccap_prefix_regex;

    /**
     * Subscription post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Subscription post type.
     */
    protected $subscription_post_type;

    /**
     * Restriction post type.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string Restriction post type.
     */
    protected $restriction_post_type;

    /**
     * All order post types.
     *
     * @since 16xxxx Order-related events.
     *
     * @param array All order post types.
     */
    protected $all_order_post_types;

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

        // This allows us to match `access_res_a_` or a mixture like: `access-res_a`.
        // WP slugs use `-` dashes by default. This allows site owners to type a prefix either way.
        // For instance, if you have `pro-membership`, you might prefer to test that with `access-res-pro-membership`.
        // Even better, change the slug to `pro_membership` and use `access_res_pro_membership` — which is possible in WP.

        $this->access_res_prefix_regex  = '/^'.preg_replace('/(?:_|\\\\-)/u', '[_\\-]', c::escRegex($this->access_res_prefix)).'/u';
        $this->access_ccap_prefix_regex = '/^'.preg_replace('/(?:_|\\\\-)/u', '[_\\-]', c::escRegex($this->access_ccap_prefix)).'/u';

        // Note: For performance, slug comparisons are caSe-sensitive throughout s2Member X.
        // In short, we make no assumption about caSe and therefore it is always caSe-sensitive.
        // This allows for `in_array()` and `isset()` without needing caSe-comparison when testing permissions.

        // However, it's a standard for WP slugs to be lowercase (they are forced to lowercase), so that is how examples should be written.
        // As long as `has_cap()` and other conditionals are written in lowercase, there's very little chance of error.
        // What we do allow (as seen above) is a mixture of either `_` or `-` as word separators.

        $this->subscription_post_type = a::subscriptionPostType();
        $this->restriction_post_type  = a::restrictionPostType();
        $this->all_order_post_types   = wc_get_order_types();
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
    public function clearCache($user_id = 0)
    {
        if (($user_id = (int) $user_id)) {
            $this->cacheUnsetPattern('permissions', '*/'.$user_id.':*');
            $this->cacheUnsetPattern('accessibleRestrictionIds', '*/'.$user_id.':*');
        } else {
            $this->cacheClear(); // Clear everything.
        }
    }

    /**
     * On user deletion.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $user_id User ID.
     */
    public function onDeleteUser($user_id)
    {
        if (!($user_id = (int) $user_id)) {
            return; // Not possible.
        }
        $WpDb  = s::wpDb(); // DB instance.
        $where = ['user_id' => $user_id];

        s::doAction('before_user_permissions_delete', $where);
        $WpDb->delete(s::dbPrefix().'user_permissions', $where);
        s::doAction('user_permissions_deleted', $where);

        $this->clearCache($user_id); // For this user.

        c::review(compact(// Log for review.
            'user_id',
            'where'
        ), 'Deleted user permissions.');
    }

    /**
     * On network user removal.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $user_id User ID.
     */
    public function onRemoveUserFromBlog($user_id)
    {
        if (!is_multisite()) {
            return; // Not applicable.
        }
        return $this->onDeleteUser($user_id);
        // Note: When this is triggered we are already inside a `switch_to_blog()` ID.
        // See: <https://developer.wordpress.org/reference/functions/remove_user_from_blog/>
    }

    /**
     * On network user deletion.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $user_id User ID.
     */
    public function onDeleteNetworkUser($user_id)
    {
        if (!is_multisite()) {
            return; // Not applicable.
        } elseif (!($user_id = (int) $user_id)) {
            return; // Not possible.
        }
        $WpDb  = s::wpDb(); // DB instance.
        $where = ['user_id' => $user_id];

        foreach (($sites = wp_get_sites()) ? $sites : [] as $_site) {
            switch_to_blog($_site['blog_id']);

            s::doAction('before_user_permissions_delete', $where);
            $WpDb->delete(s::dbPrefix().'user_permissions', $where);
            s::doAction('user_permissions_deleted', $where);

            restore_current_blog();
        } // unset($_site); // Housekeeping.

        $this->clearCache($user_id); // For this user.

        c::review(compact(// Log for review.
            'user_id',
            'where'
        ), 'Deleted user permissions.');
    }

    /**
     * After a post is trashed.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $post_id Post ID.
     */
    public function onTrashedPost($post_id)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        }
        $applicable_post_types = array_merge(
            $this->all_order_post_types,
            [$this->restriction_post_type]
        ); // Any of these are applicable here.

        $post_type = get_post_type($post_id); // This post type.
        if (!in_array($post_type, $applicable_post_types, true)) {
            return; // Not applicable.
        }
        switch ($post_type) { // Based on post type.

            case $this->subscription_post_type:
                $subscription_id = $post_id; // Subscription.
                $where           = compact('subscription_id');
                break;

            case $this->restriction_post_type:
                $restriction_id = $post_id; // Restriction.
                $where          = compact('restriction_id');
                break;

            default: // Any other order type.
                $order_id = $post_id; // Order of some type.
                $where    = compact('order_id');
                break;
        }
        $WpDb        = s::wpDb(); // DB instance.
        $update_data = ['is_trashed' => 1];

        s::doAction('before_user_permissions_update', $where, $update_data);
        $WpDb->update(s::dbPrefix().'user_permissions', $update_data, $where);
        s::doAction('user_permissions_updated', $where, $update_data);

        $this->clearCache(); // For all users.

        c::review(compact(// Log for review.
            'post_id',
            'post_type',
            'subscription_id',
            'restriction_id',
            'order_id',
            'where'
        ), 'Trashed associated user permissions.');
    }

    /**
     * After a post is restored.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $post_id Post ID.
     */
    public function onUntrashedPost($post_id)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        }
        $applicable_post_types = array_merge(
            $this->all_order_post_types,
            [$this->restriction_post_type]
        ); // Any of these are applicable here.

        $post_type = get_post_type($post_id); // This post type.
        if (!in_array($post_type, $applicable_post_types, true)) {
            return; // Not applicable.
        }
        switch ($post_type) { // Based on post type.

            case $this->subscription_post_type:
                $subscription_id = $post_id; // Subscription.
                $where           = compact('subscription_id');
                break;

            case $this->restriction_post_type:
                $restriction_id = $post_id; // Restriction.
                $where          = compact('restriction_id');
                break;

            default: // Any other order type.
                $order_id = $post_id; // Order of some type.
                $where    = compact('order_id');
                break;
        }
        $WpDb        = s::wpDb(); // DB instance.
        $update_data = ['is_trashed' => 0];

        s::doAction('before_user_permissions_update', $where, $update_data);
        $WpDb->update(s::dbPrefix().'user_permissions', $update_data, $where);
        s::doAction('user_permissions_updated', $where, $update_data);

        $this->clearCache(); // For all users.

        c::review(compact(// Log for review.
            'post_id',
            'post_type',
            'subscription_id',
            'restriction_id',
            'order_id',
            'where'
        ), 'Restored associated user permissions.');
    }

    /**
     * Before a post is deleted.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $post_id Post ID.
     */
    public function onBeforeDeletePost($post_id)
    {
        if (!($post_id = (int) $post_id)) {
            return; // Not possible.
        }
        $applicable_post_types = array_merge(
            $this->all_order_post_types,
            [$this->restriction_post_type]
        ); // Any of these are applicable here.

        $post_type = get_post_type($post_id); // This post type.
        if (!in_array($post_type, $applicable_post_types, true)) {
            return; // Not applicable.
        }
        switch ($post_type) { // Based on post type.

            case $this->subscription_post_type:
                $subscription_id = $post_id; // Subscription.
                $where           = compact('subscription_id');
                break;

            case $this->restriction_post_type:
                $restriction_id = $post_id; // Restriction.
                $where          = compact('restriction_id');
                break;

            default: // Any other order type.
                $order_id = $post_id; // Order of some type.
                $where    = compact('order_id');
                break;
        }
        $WpDb = s::wpDb(); // DB instance.

        s::doAction('before_user_permissions_delete', $where);
        $WpDb->delete(s::dbPrefix().'user_permissions', $where);
        s::doAction('user_permissions_deleted', $where);

        $this->clearCache(); // For all users.

        c::review(compact(// Log for review.
            'post_id',
            'post_type',
            'subscription_id',
            'restriction_id',
            'order_id',
            'where'
        ), 'Deleted associated user permissions.');
    }

    /**
     * Transfer permission to another user.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string|int $old_user_id Old user ID.
     * @param string|int $new_user_id New user ID.
     * @param array      $args        Any additional behavioral args.
     */
    public function transfer($old_user_id, $new_user_id, array $args = [])
    {
        if (!($old_user_id = (int) $old_user_id)) {
            return; // Not possible.
        } elseif (!($new_user_id = (int) $new_user_id)) {
            return; // Not possible.
        } elseif ($old_user_id === $new_user_id) {
            return; // Not necessary.
        }
        $default_args = [
            'where' => [], // Transfer where.
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $args['where'] = (array) $args['where']; // Force array.

        $WpDb        = s::wpDb(); // DB instance.
        $where       = array_merge($args['where'], ['user_id' => $old_user_id]);
        $update_data = ['user_id' => $new_user_id]; // Transfer ownership.

        s::doAction('before_user_permissions_update', $where, $update_data);
        $WpDb->update(s::dbPrefix().'user_permissions', $update_data, $where);
        s::doAction('user_permissions_updated', $where, $update_data);

        $this->clearCache(); // For all users.
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
        if (preg_match($this->access_res_prefix_regex, $has_cap)) {
            $_slug = preg_replace($this->access_res_prefix_regex, '', $has_cap);
            // Note that a slug in this context can contain almost anything.
            // See: <http://wordpress.stackexchange.com/a/149192/81760>

            if (!empty($restriction_ids_by_slug[$_slug])) {
                $_restriction_id = $restriction_ids_by_slug[$_slug];
                if ($this->hasAccessToRestrictions($WP_User->ID, [$_restriction_id])) {
                    $user_caps[$has_cap] = true;
                }
            } // unset($_slug, $_restriction_id);

        // Check for the special `access_ccap_` prefix.
        } elseif (preg_match($this->access_ccap_prefix_regex, $has_cap)) {
            $_ccap = preg_replace($this->access_ccap_prefix_regex, '', $has_cap);
            // Note that a CCAP can contain almost anything.

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
        $cache_key = $blog_id.'/'.$user_id.':-1'; // Trash status not a consideration here.
        if (($accessible_restriction_ids = &$this->cacheGet(__FUNCTION__, $cache_key)) !== null) {
            return $accessible_restriction_ids; // Cached already.
        }
        $accessible_restriction_ids = []; // Initialize.

        foreach ($this->permissions($user_id) as $_UserPermission) {
            if (!isset($accessible_restriction_ids[$_UserPermission->restriction_id]) && $_UserPermission->isAllowed()) {
                $accessible_restriction_ids[$_UserPermission->restriction_id] = $_UserPermission->restriction_id;
            }
        } // unset($_UserPermission); // Housekeeping.

        return s::applyFilters('user_accessible_restriction_ids', $accessible_restriction_ids, $user_id);
    }

    /**
     * Permissions for a user ID.
     *
     * @since 16xxxx Security gate.
     *
     * @param string|int $user_id       User ID.
     * @param bool       $include_trash Include trash?
     *
     * @return UserPermission[] Permissions (in `display_order`).
     */
    public function permissions($user_id, bool $include_trash = true): array
    {
        global $blog_id; // Current blog ID.

        if (!($user_id = (int) $user_id)) {
            return []; // Not possible.
        }
        $cache_key = $blog_id.'/'.$user_id.':'.(int) $include_trash;
        if (($permissions = &$this->cacheGet(__FUNCTION__, $cache_key)) !== null) {
            return $permissions; // Cached already.
        }
        $WpDb = s::wpDb(); // WP database object class.

        $sql = /* Query all user permissions. */ '
            SELECT * FROM `'.esc_sql(s::dbPrefix().'user_permissions').'`
                WHERE `user_id` = %s'.(!$include_trash ? ' AND `is_trashed` = 0' : '').
            ' ORDER BY `display_order` ASC';
        $sql = $WpDb->prepare($sql, $user_id);

        if (!($results = $WpDb->get_results($sql))) {
            return $permissions = []; // None.
        }
        $permissions             = []; // Initialize.
        $restriction_ids_by_slug = a::restrictionIdsBySlug();

        foreach ($results as $_key => $_data) {
            $_data->ID             = (int) $_data->ID;
            $_data->restriction_id = (int) $_data->restriction_id;
            if (in_array($_data->restriction_id, $restriction_ids_by_slug, true)) {
                $permissions[$_data->ID] = $this->App->Di->get(Classes\UserPermission::class, ['data' => $_data]);
            }
        } // unset($_key, $_data); // Housekeeping.

        return s::applyFilters('user_permissions', $permissions, $user_id, $include_trash);
    }
}
