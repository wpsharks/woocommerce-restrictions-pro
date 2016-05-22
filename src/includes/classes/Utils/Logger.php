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

        $event_log_file_handle = mb_strpos($event, '#issue') !== false
            ? $this->App->Config->©brand['©slug'].'-issues'
            : $this->App->Config->©brand['©slug'];

        $current_user = wp_get_current_user(); // If there is one; else an object placeholder.

        $log_entry_data[] = __('Microtime:', 's2member-x').'    '.number_format(microtime(true), 8, '.', '');
        $log_entry_data[] = __('Event:', 's2member-x').'        '.($event ? $event : __('unknown caller', 's2member-x'));
        $log_entry_data[] = __('Note:', 's2member-x').'         '.($note ? $note : __('nothing given by caller', 's2member-x'))."\n";

        $log_entry_data[] = __('System:', 's2member-x').'       '.PHP_OS.'; PHP v'.PHP_VERSION.' ('.PHP_SAPI.')';
        $log_entry_data[] = __('Software:', 's2member-x').'     WP v'.WP_VERSION.'; WC v'.WC_VERSION.'; '.$this->App->Config->©brand['©acronym'].' v'.$this->App::VERSION."\n";

        if (c::isCli()) { // The current URL may or may not be possible here.
            $log_entry_data[] = __('User:', 's2member-x').'         '.__('n/a; CLI process', 's2member-x');
            $log_entry_data[] = __('User Agent:', 's2member-x').'   '.__('n/a; CLI process', 's2member-x');
            $log_entry_data[] = __('URL:', 's2member-x').'          '.__('n/a; CLI process', 's2member-x')."\n";
        } else {
            $log_entry_data[] = __('User:', 's2member-x').'         #'.$current_user->ID.' @'.$current_user->user_login.' \''.$current_user->display_name.'\' ['.($_SERVER['REMOTE_ADDR'] ?? __('unknown', 's2member-x')).']';
            $log_entry_data[] = __('User Agent:', 's2member-x').'   '.($_SERVER['HTTP_USER_AGENT'] ?? __('unknown', 's2member-x'));
            $log_entry_data[] = __('URL:', 's2member-x').'          '.c::currentUrl()."\n"; // This could get quite long.
        }
        $log_entry_data[] = c::mbTrim(c::dump($data, true), "\r\n"); // A dump of the data (variables) recorded by the log entry caller.

        $this->WC_Logger->add($event_log_file_handle, "\n".implode("\n", $log_entry_data)."\n\n".str_repeat('-', 3)."\n");
    }
}
