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
 * Product permission.
 *
 * @since 16xxxx Product permission.
 */
class ProductPermission extends SCoreClasses\SCore\Base\Core
{
    /**
     * Class constructor.
     *
     * @since 16xxxx Product permission.
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
     * @since 16xxxx Product permission.
     *
     * @return bool True if permission is valid.
     */
    public function isValid(): bool
    {
        if (!$this->product_id) {
            return false;
        } elseif (!$this->restriction_id) {
            return false;
        }
        return true; // No problems.
    }

    /**
     * Fill properties from data.
     *
     * @since 16xxxx Adding user permissions.
     *
     * @param \StdClass $data Object properties.
     */
    protected function fillProperties(\StdClass $data)
    {
        $is_overloaded = $this->isOverloaded();

        if ($is_overloaded && isset($data->product_id)) {
            throw new Exception('Trying to update contruct-only property.');
        }
        if (!$is_overloaded) { // Called by constructor?
            $is_overloaded = true; // Overloading now.

            $defaults = [
                'product_id'     => 0,
                'restriction_id' => 0,

                'access_offset_time' => 0,
                'expire_offset_time' => 0,

                'display_order' => 0,
            ];
            $defaults = (object) $defaults; // Converts to object now.
            // Separate line; see: <https://bugs.php.net/bug.php?id=72219>

            $this->overload($defaults, true); // Public read/write access for performance.
            // Please do NOT write to properties directly. Use {@link update()}.
        }
        $this->product_id     = abs((int) ($data->product_id ?? $this->product_id ?? 0));
        $this->restriction_id = abs((int) ($data->restriction_id ?? $this->restriction_id ?? 0));

        $this->access_offset_time = abs((int) ($data->access_offset_time ?? $this->access_offset_time ?? 0));
        $this->expire_offset_time = abs((int) ($data->expire_offset_time ?? $this->expire_offset_time ?? 0));
        // Note the `expire_offset_time` starts from the beginning of the access time.
        //  i.e., time() + `access_offset_time` + `expire_offset_time` = expire time.

        $this->display_order = abs((int) ($data->display_order ?? $this->display_order ?? 0));
    }
}
