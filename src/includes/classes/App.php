<?php
declare (strict_types = 1);
namespace WebSharks\s2MemberX\Pro\Classes;

use WebSharks\s2MemberX\Pro\Classes;
use WebSharks\s2MemberX\Pro\Interfaces;
use WebSharks\s2MemberX\Pro\Traits;
#
use WebSharks\s2MemberX\Pro\Classes\AppFacades as a;
use WebSharks\s2MemberX\Pro\Classes\SCoreFacades as s;
use WebSharks\s2MemberX\Pro\Classes\CoreFacades as c;
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
 * App.
 *
 * @since 16xxxx Initial release.
 */
class App extends SCoreClasses\App
{
    /**
     * Version.
     *
     * @since 16xxxx
     *
     * @type string Version.
     */
    const VERSION = '160229'; //v//

    /**
     * Constructor.
     *
     * @since 16xxxx Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $instance_base = [
            '©di' => [
                '©default_rule' => [
                    'new_instances' => [
                    ],
                ],
            ],

            '§options' => [

            ],
            '§pro_option_keys' => [

            ],
        ];
        parent::__construct($instance_base, $instance);
    }

    /**
     * Hook setup handler.
     *
     * @since 16xxxx Initial release.
     */
    protected function setupHooks()
    {
    }
}
