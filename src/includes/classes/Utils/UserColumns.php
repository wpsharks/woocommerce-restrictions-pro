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
 * User column utilities.
 *
 * @since 16xxxx User column utilities.
 */
class UserColumns extends SCoreClasses\SCore\Base\Core
{
    /**
     * Restriction titles by ID.
     *
     * @since 16xxxx User column utilities.
     *
     * @param array Restriction titles by ID.
     */
    protected $restriction_titles_by_id;

    /**
     * Permissions column name.
     *
     * @since 16xxxx User column utilities.
     *
     * @param string Permissions column name.
     */
    protected $permissions_column;

    /**
     * Max permission column lines.
     *
     * @since 16xxxx User column utilities.
     *
     * @param int Max permission column lines.
     */
    protected $max_permission_column_lines;

    /**
     * Class constructor.
     *
     * @since 16xxxx User column utilities.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->restriction_titles_by_id    = a::restrictionTitlesById();
        $this->permissions_column          = $this->App->Config->©brand['©var'].'_permissions';
        $this->max_permission_column_lines = s::applyFilters('max_user_permission_column_lines', 15);
    }

    /**
     * User columns.
     *
     * @since 16xxxx User column utilities.
     *
     * @param array $columns Current columns.
     *
     * @return array Filtered user columns.
     */
    public function onManageUsersColumns(array $columns): array
    {
        return array_merge($columns, [$this->permissions_column => __('Permissions', 's2member-x')]);
    }

    /**
     * User columns.
     *
     * @since 16xxxx User column utilities.
     *
     * @param string     $display Value to display.
     * @param string     $column  Custom column name/key.
     * @param string|int $user_id User ID the column is for.
     *
     * @return string The custom column display value.
     */
    public function onManageUsersCustomColumn(string $display, string $column, $user_id): string
    {
        if ($column !== $this->permissions_column) {
            return $display; // Not applicable.
        }
        $display_lines = []; // Initialize lines to display.

        foreach (a::userPermissions($user_id) as $_UserPermission) {
            if (empty($this->restriction_titles_by_id[$_UserPermission->restriction_id])) {
                debug(0, c::issue(vars(), 'Missing restriction title by ID.'));
                continue; // Not possible to display this.
            }
            $_line           = esc_html($this->restriction_titles_by_id[$_UserPermission->restriction_id]);
            $_line           = !$_UserPermission->isAllowed() ? '<span style="opacity:0.5;">'.$_line.'</span>' : $_line;
            $display_lines[] = $_line; // Add line to the array.

            if (count($display_lines) > $this->max_permission_column_lines) {
                $display_lines[] = '<em style="opacity:0.5;">'.__('...and more', 's2member-x').'</em>';
                break; // Stop short of full display.
            }
        } // unset($_UserPermission, $_line); // Housekeeping.

        return $display_lines ? implode('<br />', $display_lines) : '—';
    }
}
