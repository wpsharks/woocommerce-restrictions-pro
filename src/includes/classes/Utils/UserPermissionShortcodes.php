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
 * User permission shortcodes.
 *
 * @since 160524 Security gate.
 */
class UserPermissionShortcodes extends SCoreClasses\SCore\Base\Core
{
    /**
     * Shortcode name.
     *
     * @since 160524 Shortcodes.
     *
     * @param string Shortcode name.
     */
    public $if_shortcode_name;

    /**
     * Shortcode name.
     *
     * @since 160524 Shortcodes.
     *
     * @param string Shortcode name.
     */
    public $else_shortcode_name;

    /**
     * Class constructor.
     *
     * @since 160524 Shortcodes.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->if_shortcode_name   = s::applyFilters('if_shortcode_name', 'if');
        $this->else_shortcode_name = s::applyFilters('else_shortcode_name', 'else');
    }

    /**
     * `[if /]` shortcode.
     *
     * @since 160524 Shortcodes.
     *
     * @param array       $atts      Shortcode attributes.
     * @param string|null $content   Shortcode content.
     * @param string      $shortcode Shortcode name.
     */
    public function onIf(array $atts, $content, string $shortcode): string
    {
        $default_atts = [
            'expr'              => '',
            'is_user_logged_in' => '',
            'current_user_can'  => '', 'for_blog' => '0',
            'satisfy'           => 'all',
        ];
        $atts    = c::mbTrim($atts);
        $atts    = shortcode_atts($default_atts, $atts, $shortcode);
        $content = (string) $content; // Force string value.

        $atts['expr'] = (string) $atts['expr'];
        $atts['expr'] = $atts['expr'] ? c::unescHtml($atts['expr']) : '';

        $atts['is_user_logged_in'] = (string) $atts['is_user_logged_in'];
        $atts['is_user_logged_in'] = isset($atts['is_user_logged_in'][0]) ? filter_var($atts['is_user_logged_in'], FILTER_VALIDATE_BOOLEAN) : null;

        $atts['current_user_can'] = (string) $atts['current_user_can'];
        $atts['current_user_can'] = $atts['current_user_can'] ? c::unescHtml($atts['current_user_can']) : '';
        $atts['for_blog']         = (int) $atts['for_blog']; // Network-only.

        $atts['satisfy'] = $atts['satisfy'] === 'any' ? 'any' : 'all';

        $is_multisite    = is_multisite(); // Network installation?
        $shortcode_depth = strspn($shortcode, '_'); // Based on a zero index.
        $else_tag        = '['.str_repeat('_', $shortcode_depth).$this->else_shortcode_name.']';
        $conditions      = ''; // Initialize the full set of all conditions built below.
        /*
         * Construct content for if/else conditions.
         */
        if (mb_strpos($content, $else_tag) !== false) {
            list($content_if, $content_else) = explode($else_tag, $content, 2);
            $content_if                      = c::htmlTrim($content_if);
            $content_else                    = c::htmlTrim($content_else);
        } else {
            $content_if   = c::htmlTrim($content);
            $content_else = ''; // Default (empty).
        }
        /*
         * Add conditions from the `expr=""` attribute, if applicable.
         *
         * - [if expr="current_user_can('access_pkg_slug1')" /]
         * - [if expr="current_user_can('access_pkg_slug1') and current_user_can('access_pkg_slug2')" /]
         * - [if expr="current_user_can('access_ccap_one') or get_user_option('meta_key') === 'value'" /]
         */
        if ($atts['expr']) {
            if (!s::getOption('if_shortcode_expr_enable')) {
                // This is disabled by default. If enabled, a filter can disable it on child sites of a network.
                // add_filter('s2member_x_options', function(array $options) { $options['if_shortcode_expr_enable'] = '0'; return $options; });
                trigger_error(sprintf(__('`[%1$s /]` shortcode attribute `expr=""` not enabled on this site.', 's2member-x'), $shortcode), E_USER_ERROR);
                return ''; // Return empty string in case of error handlers allowing this to slide.
            }
            if ($conditions) { // Taken as-is; raw PHP expression.
                $conditions .= ($atts['satisfy'] === 'any' ? ' || ' : ' && ').'('.$atts['expr'].')';
            } else {
                $conditions = '('.$atts['expr'].')';
            }
        }
        /*
         * Add conditions from the `is_user_logged_in=""` attribute, if applicable.
         *
         * - [if is_user_logged_in="true" /]
         * - [if is_user_logged_in="yes" /]
         * - [if is_user_logged_in="false" /], etc.
         */
        if (isset($atts['is_user_logged_in'])) {
            if ($atts['is_user_logged_in'] === false) {
                $_is_user_logged_in_condition = '!is_user_logged_in()';
            } else { // Default behavior is a boolean true.
                $_is_user_logged_in_condition = 'is_user_logged_in()';
            }
            if ($conditions) {
                $conditions .= ($atts['satisfy'] === 'any' ? ' || ' : ' && ').$_is_user_logged_in_condition;
            } else {
                $conditions = $_is_user_logged_in_condition;
            }
            // unset($_is_user_logged_in_condition); // Housekeeping.
        }
        /*
         * Add conditions from the `current_user_can=""` attribute, if applicable.
         *
         * - [if current_user_can="access_pkg_slug1" /]
         * - [if current_user_can="access_pkg_slug1 and access_pkg_slug2" /]
         * - [if current_user_can="(access_pkg_slug1 and access_pkg_slug2) or (access_ccap_one and access_ccap_two)" /]
         */
        if ($atts['current_user_can']) {
            if (mb_strpos($atts['current_user_can'], "'") !== false) {
                trigger_error(sprintf(__('`[%1$s /]` shortcode attribute `current_user_can="%2$s"` contains apostrophe.', 's2member-x'), $shortcode, $atts['current_user_can']), E_USER_ERROR);
                return ''; // Return empty string in case of error handlers allowing this to slide.
            } elseif (!preg_match('/\((?:(?:[^()]+)|(?R))*\)/u', '('.$atts['current_user_can'].')', $_m) || $_m[0] !== '('.$atts['current_user_can'].')') {
                trigger_error(sprintf(__('`[%1$s /]` shortcode attribute `current_user_can="%2$s"` contains unbalanced `()` brackets.', 's2member-x'), $shortcode, $atts['current_user_can']), E_USER_ERROR);
                return ''; // Return empty string in case of error handlers allowing this to slide.
            } elseif ($atts['for_blog'] && $is_multisite && !s::getOption('if_shortcode_for_blog_enable')) {
                // This is disabled by default. If enabled, a filter can disable it on child sites of a network.
                // add_filter('s2member_x_options', function(array $options) { $options['if_shortcode_for_blog_enable'] = '0'; return $options; });
                trigger_error(sprintf(__('`[%1$s /]` shortcode attribute `for_blog=""` not enabled on this site.', 's2member-x'), $shortcode), E_USER_ERROR);
                return ''; // Return empty string in case of error handlers allowing this to slide.
            }
            $_current_user_can_conditions = ''; // Initialize conditions.
            $_previous_frag               = ''; // Initialize previous fragment.

            foreach (preg_split('/([()])|\s+/u', $atts['current_user_can'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $_frag) {
                $_space = !$_current_user_can_conditions || !$_previous_frag || $_previous_frag === '(' || $_frag === '(' || $_frag === ')' ? '' : ' ';

                if (in_array($_lc_frag = mb_strtolower($_frag), ['(', ')', 'and', 'or', '&&', '||'], true)) {
                    $_frag = $_lc_frag === 'and' ? '&&' : $_frag;
                    $_frag = $_lc_frag === 'or' ? '||' : $_frag;
                    $_current_user_can_conditions .= $_space.$_frag;
                } elseif ($atts['for_blog'] && $is_multisite) { // Network-only.
                    $_current_user_can_conditions .= $_space.'current_user_can_for_blog('.$atts['for_blog'].', \''.$_frag.'\')';
                } else { // Build the `current_user_can('[cap]')` check.
                    $_current_user_can_conditions .= $_space.'current_user_can(\''.$_frag.'\')';
                }
                $_previous_frag = $_frag; // Previous fragment.
            } // unset($_frag, $_lc_frag, $_previous_frag, $_space); // Housekeeping.

            if ($_current_user_can_conditions) {
                if ($conditions) {
                    $conditions .= ($atts['satisfy'] === 'any' ? ' || ' : ' && ').'('.$_current_user_can_conditions.')';
                } else {
                    $conditions = '('.$_current_user_can_conditions.')';
                }
            } // unset($_current_user_can_conditions); // Housekeeping.
        }
        /*
         * Test the expression return value and deal with nested shortcodes.
         * This uses all possible conditions from above and tests them in one `eval()`.
         */
        return do_shortcode($conditions && c::phpEval('return ('.$conditions.');') ? $content_if : $content_else);
    }
}
