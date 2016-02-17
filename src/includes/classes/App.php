<?php
declare (strict_types = 1);
namespace WebSharks\s2MemberX\Pro\Classes;

use WebSharks\s2MemberX\Pro\Classes\Utils;
use WebSharks\s2MemberX\Pro\Functions as a;
use WebSharks\s2MemberX\Pro\Interfaces;
use WebSharks\s2MemberX\Pro\Traits;
#
use WebSharks\WpSharks\Core\Functions as w;
use WebSharks\WpSharks\Core\Classes as WpCoreClasses;
use WebSharks\WpSharks\Core\Classes\Utils as WpCoreUtils;
use WebSharks\WpSharks\Core\Interfaces as WpCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as WpCoreTraits;
#
use WebSharks\Core\WpCore\Functions as c;
use WebSharks\Core\WpCore\Classes\Exception;
use WebSharks\Core\WpCore\Classes as CoreClasses;
use WebSharks\Core\WpCore\Classes\Utils as CoreUtils;
use WebSharks\Core\WpCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpCore\Traits as CoreTraits;

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
    const VERSION = '160216'; //v//

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
