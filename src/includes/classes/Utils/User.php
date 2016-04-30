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
 * User can utilities.
 *
 * @since 16xxxx Installer.
 */
class User extends SCoreClasses\SCore\Base\Core
{
    /**
     * Restrictions by slug.
     *
     * @since 16xxxx Security gate.
     *
     * @type array Restrictions by slug.
     */
    protected $restrictions_by_slug;

    /**
     * Restrictions.
     *
     * @since 16xxxx Security gate.
     *
     * @type array Restrictions.
     */
    protected $restrictions;

    /**
     * Restriction IDs.
     *
     * @since 16xxxx Security gate.
     *
     * @type array Restriction IDs.
     */
    protected $restriction_ids;

    /**
     * Access rstr prefix.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access rstr prefix.
     */
    protected $access_rstr_prefix;

    /**
     * Access CCAP prefix.
     *
     * @since 16xxxx Security gate.
     *
     * @type string Access CCAP prefix.
     */
    protected $access_ccap_prefix;

    /**
     * Systematic roles.
     *
     * @since 16xxxx Security gate.
     *
     * @type array Systematic roles.
     */
    protected $systematic_roles;

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

        $by_meta_key                = a::restrictionsByMetaKey();
        $this->restrictions_by_slug = a::restrictionsBySlug();
        $this->restrictions         = $by_meta_key['restrictions'];
        $this->restriction_ids      = $by_meta_key['restriction_ids'];

        $this->access_rstr_prefix = a::restrictionAccessRstrPrefix();
        $this->access_ccap_prefix = a::restrictionAccessCcapPrefix();

        $this->systematic_roles = a::systematicRoles();
    }

    /**
     * Current user has access to restrictions?
     *
     * @since 16xxxx Restrictions.
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
     * @since 16xxxx Restrictions.
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
        } elseif ($satisfy !== 'any' && count($restriction_ids_slugs) !== count($restriction_ids)) {
            return false; // Cannot satisfy all; counts are off.
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
     * @since 16xxxx Restrictions.
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
        // Add role-based caps granted by restrictions the user can access.
        foreach ($this->restriction_ids['roles'] as $_role => $_restriction_ids) {
            if ($this->hasAccessToRestrictions($WP_User->ID, $_restriction_ids, 'any')) {
                if (!in_array($_role, $this->systematic_roles, true) && ($_role_object = get_role($_role))) {
                    $user_caps = array_merge($user_caps, $_role_object->capabilities);
                }
            }
        } // unset($_role, $_restriction_ids, $_role_object); // Housekeeping.

        // Check for the special `access_rstr_` prefix.
        if (mb_strpos($has_cap, $this->access_rstr_prefix) === 0) {
            $_slug = c::strReplaceOnce($this->access_rstr_prefix, '', $has_cap);

            if (!empty($this->restrictions_by_slug[$_slug])) {
                $_restriction_id = $this->restrictions_by_slug[$_slug];
                if ($this->hasAccessToRestrictions($WP_User->ID, [$_restriction_id])) {
                    $user_caps[$has_cap] = true;
                }
            } // unset($_slug, $_restriction_id);

        // Check for the special `access_ccap_` prefix.
        } elseif (mb_strpos($has_cap, $this->access_ccap_prefix) === 0) {
            $_ccap = c::strReplaceOnce($this->access_ccap_prefix, '', $has_cap);

            if (in_array($_ccap, $this->restrictions['ccaps'], true)) {
                $_by_restriction_ids = $this->restriction_ids['ccaps'][$ccap];
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
     * @since 16xxxx Restrictions.
     *
     * @param string|int $user_id User ID.
     *
     * @return array Accessible restriction IDs.
     */
    public function accessibleRestrictionIds($user_id): array
    {
        if (!($user_id = (int) $user_id)) {
            return []; // No access.
        }
        $accessible_restriction_ids = get_user_meta($user_id, 'accessible_restriction_ids');
        $accessible_restriction_ids = is_array($accessible_restriction_ids) ? $accessible_restriction_ids : [];

        return s::applyFilters('user_accessible_restriction_ids', $accessible_restriction_ids, $user_id);
    }
}
