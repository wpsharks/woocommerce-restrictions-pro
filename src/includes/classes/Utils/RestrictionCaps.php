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
 * Restriction caps.
 *
 * @since 16xxxx Installer.
 */
class RestrictionCaps extends SCoreClasses\SCore\Base\Core
{
    /**
     * All caps.
     *
     * @since 16xxxx Restrictions.
     *
     * @type array All caps.
     */
    protected $caps;

    /**
     * Class constructor.
     *
     * @since 16xxxx Restrictions.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->caps = [
            'edit_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'edit_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'edit_published_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'edit_private_'.$this->App->Config->©brand['©prefix'].'_restrictions',

            'publish_'.$this->App->Config->©brand['©prefix'].'_restrictions',

            'delete_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'delete_private_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'delete_published_'.$this->App->Config->©brand['©prefix'].'_restrictions',
            'delete_others_'.$this->App->Config->©brand['©prefix'].'_restrictions',

            'read_private_'.$this->App->Config->©brand['©prefix'].'_restrictions',
        ];
    }

    /**
     * Add default caps.
     *
     * @since 16xxxx Restrictions.
     */
    public function addDefaults()
    {
        foreach (['administrator'] as $_role) {
            if (!is_object($_role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_role->add_cap($_cap);
            }
        } // unset($_role, $_cap); // Housekeeping.
    }

    /**
     * Remove all caps.
     *
     * @since 16xxxx Restrictions.
     */
    public function removeAll()
    {
        foreach (array_keys(wp_roles()->roles) as $_role) {
            if (!is_object($_role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($this->caps as $_cap) {
                $_role->remove_cap($_cap);
            }
        } // unset($_role, $_cap); // Housekeeping.
    }

    /**
     * Restore default caps.
     *
     * @since 16xxxx Restrictions.
     */
    public function restoreDefaults()
    {
        $this->removeAll();
        $this->addDefaults();
    }
}
