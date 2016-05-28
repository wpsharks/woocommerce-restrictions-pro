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
 * Debugging utils.
 *
 * @since 160524 Debugging utils.
 */
class Debugging extends SCoreClasses\SCore\Base\Core
{
    /**
     * WC_Logger instance.
     *
     * @since 160524 Debugging utils.
     *
     * @type \WC_Logger|null Class instance.
     */
    protected $WC_Logger;

    /**
     * Class constructor.
     *
     * @since 160524 Debugging utils.
     *
     * @param Classes\App $App Instance of App.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        if (class_exists('WC_Logger')) {
            $this->WC_Logger = new \WC_Logger();
            // Only if WC_Logger is available.
            // e.g., in case dependencies are missing.
        }
    }

    /**
     * On debug log entry.
     *
     * @since 160524 Debugging utils.
     *
     * @param array $details Event details.
     */
    public function onLogEvent(array $details)
    {
        if (!$this->App->Config->©debug['©log']) {
            return; // Not applicable.
        } elseif (!$this->WC_Logger) {
            return; // Not possible at this time.
            // e.g., in case dependencies are missing.
        }
        extract($details); // `event`, `data`, `note`, `lines`.

        $handle = $this->App->Config->©brand['©slug'];

        if (mb_strpos($event, '#') !== false) {
            $event_handle = mb_strstr($event, '#');
            $handle .= '-'.c::nameToSlug($event_handle);
        }
        $entry = "\n".implode("\n", $lines)."\n\n".str_repeat('-', 3)."\n\n";

        $this->WC_Logger->add($handle, $entry);
    }
}
