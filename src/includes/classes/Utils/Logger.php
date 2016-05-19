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
 * Logging utilities.
 *
 * @since 16xxxx Order-related events.
 */
class Logger extends SCoreClasses\SCore\Base\Core
{
    /**
     * WC logger class.
     *
     * @since 16xxxx Order-related events.
     *
     * @param \WC_Logger Logger instance.
     */
    protected $WC_Logger;

    /**
     * Class constructor.
     *
     * @since 16xxxx Order-related events.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->WC_Logger = new \WC_Logger();
    }

    /**
     * On add order item meta.
     *
     * @since 16xxxx Order-related events.
     *
     * @param string $handle  i.e., File name component.
     * @param string $message The message to log.
     */
    public function addEntry(string $handle, string $message)
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return; // Not applicable.
        }
        $this->WC_Logger->add($this->App->Config->©brand['©prefix'].'-'.$handle, $message);
    }
}
