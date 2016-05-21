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
     * @param string $event Event name.
     * @param array  $data  Data array to log.
     * @param string $note  Optional notes/description.
     */
    public function addEntry(string $event, array $data = [], string $note = '')
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return; // Not applicable.
        } elseif (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return; // Not applicable.
        }
        $event = str_replace(__NAMESPACE__, '', $event);
        $event = str_replace($this->App->namespace, '', $event);
        $event = c::mbTrim($event, '', '\\'); // Clean it up now.

        $log_entry_data[] = __('Event:', 's2member-x').'        '.($event ? $event : __('unknown caller', 's2member-x'));
        $log_entry_data[] = __('Note:', 's2member-x').'         '.($note ? $note : __('nothing given by caller', 's2member-x'));
        $log_entry_data[] = __('Time:', 's2member-x').'         '.s::dateI18nUtc('F jS, Y, g:i a T');
        $log_entry_data[] = __('Microtime:', 's2member-x').'    '.number_format(microtime(true), 8, '.', '');
        $log_entry_data[] = __('Current User:', 's2member-x').' '.get_current_user_id();

        if (c::isCli()) { // The current URL may or may not be possible here.
            $log_entry_data[] = __('URL:', 's2member-x').'          '.__('n/a; CLI process', 's2member-x')."\n";
        } else {
            $log_entry_data[] = __('URL:', 's2member-x').'          '.c::currentUrl()."\n";
        }
        $log_entry_data[] = (string) (!is_scalar($data) ? c::dump($data, true) : $data);

        $this->WC_Logger->add($this->App->Config->©brand['©slug'], implode("\n", $log_entry_data));
    }
}
