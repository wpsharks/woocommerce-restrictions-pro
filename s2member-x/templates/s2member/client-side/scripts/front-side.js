/**
 * Front-Side Extension.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Scripts
 * @since 120318
 */

(function($w) // Begin extension closure.
	{
		'use strict'; // Standards.

		/**
		 * @type {Object} Plugin.
		 */
		$w.$s2member = $w.$s2member || {};

		/**
		 * @type {Object} WebSharks™ Core.
		 */
		$w.$$websharks_core = $w.$$websharks_core || {};

		/**
		 * @type {Object} Plugin property in core namespace.
		 */
		$w.$$websharks_core.$s2member = $w.$$websharks_core.$s2member || {};
		if(typeof $w.$$websharks_core.$s2member.$front_side === 'function')
			return; // Extension already exists.

		/**
		 * @constructor Plugin extension constructor & prototype definitions.
		 */
		$$websharks_core.$s2member.$front_side = function(){this.setup_initialize.apply(this, arguments);};
		$$websharks_core.$s2member.$front_side.prototype = new $$websharks_core.$('s2member', 'front_side');
		$$websharks_core.$s2member.$front_side.prototype.constructor = $$websharks_core.$s2member.$front_side;
		$$websharks_core.$s2member.$front_side.prototype.setup_initialize = function()
			{
				var $$ = this, $ = jQuery; // $Quickies.

				$(document)// Handles document ready state event (DOM ready).
					.ready(function() // Setup/initialization routines.
					       {
						       // Setup toggles on profile update forms.
						       $('.' + $$.plugin_css_class() + ' form.profile-update').toggles();

						       // Fix nested avatar image corners.
						       $('.' + $$.plugin_css_class() + ' div.avatar img')
							       .addClass('border-all box-shadow-all ui-corner-all');

						       // Prepare UI forms.
						       $$.prepare_ui_forms();

						       // Wrap problematic UI theme components.
						       $$.wrap_problematic_ui_theme_components();
					       });
			};
		/**
		 * @type {Object} Creating an instance of this extension.
		 */
		$s2member.$front_side = new $$websharks_core.$s2member.$front_side();

	})(this); // End extension closure.