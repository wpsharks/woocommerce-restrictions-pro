<?php
/**
 * Product permission.
 *
 * @author @jaswsinc
 * @copyright WebSharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes;

use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerce\s2MemberX\Pro\Classes\CoreFacades as c;
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
 * Product permission.
 *
 * @since 160524 Product permission.
 */
class ProductPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Class constructor.
     *
     * @since 160524 Product permission.
     *
     * @param Classes\App $App  Instance.
     * @param \StdClass   $data Permission data.
     */
    public function __construct(Classes\App $App, \StdClass $data)
    {
        parent::__construct($App);

        $this->fillProperties($data); // Overloading.
    }

    /**
     * A valid permission?
     *
     * @since 160524 Product permission.
     *
     * @return bool True if permission is valid.
     */
    public function isValid(): bool
    {
        if (!$this->product_id) {
            return false;
        } elseif (!$this->restriction_id) {
            return false;
        } elseif (!$this->access_offset_directive) {
            return false;
        } elseif (!$this->expire_offset_directive) {
            return false;
        }
        return true; // No problems.
    }

    /**
     * Calculate access time.
     *
     * @since 160524 Product permission.
     *
     * @param int $from Basis for time calculation.
     *
     * @return int Access timestamp, else `0` if n/a.
     */
    public function accessTime(int $from = null)
    {
        if (!isset($from)) { // Defaults to now.
            $from = time(); // Basis for `strtotime()`.
        }
        if (!$this->isValid()) {
            return 0; // Not possible.
        }
        switch ($this->access_offset_directive) {
            case 'immediately':
                return 0;

            default: // Relative offset time.
                $directive                           = $this->access_offset_directive;
                $access_offset_key_prefix_regex_frag = c::escRegex(a::productPermissionAccessOffsetKeyPrefix());
                $directive                           = preg_replace('/^'.$access_offset_key_prefix_regex_frag.'\s+/ui', '', $directive, -1, $contained_access_offset_key_prefix);

                if ($contained_access_offset_key_prefix) {
                    if (($time = (int) strtotime('+'.$directive, $from)) >= $from) {
                        return $time; // Access timestamp.
                    } else {
                        return 0; // `0` on failure.
                    }
                } else { // Anything else compatible w/ `strtotime()`.
                    // e.g., `first day of next month`, `last day of next month`, etc.
                    // See: <http://php.net/manual/en/datetime.formats.relative.php>
                    if (($time = (int) strtotime($directive, $from)) >= $from) {
                        return $time; // Access timestamp.
                    } else {
                        return 0; // `0` on failure.
                    }
                }
        }
    }

    /**
     * Calculate expire time.
     *
     * @since 160524 Product permission.
     *
     * @param int $from Basis for time calculation.
     *
     * @return int Expiration timestamp, else `0` if n/a.
     */
    public function expireTime(int $from = null)
    {
        if (!$this->isValid()) {
            return 0; // Not possible.
        }
        if (!($from = $this->accessTime($from))) {
            $from = time(); // Basis for `strtotime()`.
        }
        switch ($this->expire_offset_directive) {
            case 'naturally':
            case 'naturally -expired':
            case 'never':
                return 0;

            default: // Relative offset time.
                $directive                           = $this->expire_offset_directive;
                $expire_offset_key_suffix_regex_frag = c::escRegex(a::productPermissionExpireOffsetKeySuffix());
                $directive                           = preg_replace('/\s+'.$expire_offset_key_suffix_regex_frag.'$/ui', '', $directive, -1, $contained_expire_offset_key_suffix);

                if ($contained_expire_offset_key_suffix) {
                    if (($time = (int) strtotime('+'.$directive, $from)) >= $from) {
                        return $time; // Expire timestamp.
                    } else {
                        return 0; // `0` on failure.
                    }
                } else { // Anything else compatible w/ `strtotime()`.
                    // e.g., `first day of next month`, `last day of next month`, etc.
                    // See: <http://php.net/manual/en/datetime.formats.relative.php>
                    if (($time = (int) strtotime($directive, $from)) >= $from) {
                        return $time; // Expire timestamp.
                    } else {
                        return 0; // `0` on failure.
                    }
                }
        }
    }

    /**
     * Fill properties from data.
     *
     * @since 160524 Adding user permissions.
     *
     * @param \StdClass $data Object properties.
     */
    protected function fillProperties(\StdClass $data)
    {
        $is_overloaded = $this->isOverloaded();

        if ($is_overloaded && isset($data->product_id)) {
            throw c::issue('Trying to update contruct-only property.');
        }
        if (!$is_overloaded) { // Called by constructor?
            $is_overloaded = true; // Overloading now.

            $defaults = [
                'product_id'     => 0,
                'restriction_id' => 0,

                'access_offset_directive' => '',
                'expire_offset_directive' => '',

                'display_order' => 0,
            ];
            $defaults = (object) $defaults; // Converts to object now.
            // Separate line; see: <https://bugs.php.net/bug.php?id=72219>

            $this->overload($defaults, true); // Public read/write access for performance.
            // Please do NOT write to properties directly. Use {@link update()}.
        }
        $this->product_id     = abs((int) ($data->product_id ?? $this->product_id ?? 0));
        $this->restriction_id = abs((int) ($data->restriction_id ?? $this->restriction_id ?? 0));

        $this->access_offset_directive = mb_strtolower((string) ($data->access_offset_directive ?? $this->access_offset_directive ?? ''));
        $this->expire_offset_directive = mb_strtolower((string) ($data->expire_offset_directive ?? $this->expire_offset_directive ?? ''));
        // Note the `expire_offset_directive` starts from the beginning of the `access_offset_directive` calculation.
        //  i.e., time() + `access_offset_directive` + `expire_offset_directive` = expire time.

        $this->display_order = abs((int) ($data->display_order ?? $this->display_order ?? 0));
    }
}
