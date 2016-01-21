<?php
declare (strict_types = 1);
namespace WebSharks\s2Member\X\Pro\Classes;

use WebSharks\s2Member\X\Pro\Classes\Utils;
use WebSharks\s2Member\X\Pro\Functions as a;
use WebSharks\s2Member\X\Pro\Interfaces;
use WebSharks\s2Member\X\Pro\Traits;
#
use WebSharks\Core\s2MemberXPro\Functions as c;
use WebSharks\Core\s2MemberXPro\Classes\Exception;
use WebSharks\Core\s2MemberXPro\Classes as CoreClasses;
use WebSharks\Core\s2MemberXPro\Classes\Utils as CoreUtils;
use WebSharks\Core\s2MemberXPro\Interfaces as CoreInterfaces;
use WebSharks\Core\s2MemberXPro\Traits as CoreTraits;

/**
 * Application.
 *
 * @since 16xxxx Initial release.
 */
class App extends CoreClasses\App
{
    /**
     * Version.
     *
     * @since 16xxxx
     *
     * @type string Version.
     */
    const VERSION = '160120'; //v//

    /**
     * Constructor.
     *
     * @since 16xxxx Initial release.
     *
     * @param array $instance Instance args (highest precedence).
     */
    public function __construct(array $instance = [])
    {
        $instance_base = [
            'di' => [
                'default_rule' => [
                    'new_instances' => [

                    ],
                ],
            ],
        ];
        parent::__construct($instance_base, $instance);
    }
}
