/**
 * Stand-Alone Extension.
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
		if(typeof $w.$$websharks_core.$s2member.$stand_alone === 'function')
			return; // Extension already exists.

		/**
		 * @constructor Plugin extension constructor & prototype definitions.
		 */
		$$websharks_core.$s2member.$stand_alone = function(){this.setup_initialize.apply(this, arguments);};
		$$websharks_core.$s2member.$stand_alone.prototype = new $$websharks_core.$('s2member', 'stand_alone');
		$$websharks_core.$s2member.$stand_alone.prototype.constructor = $$websharks_core.$s2member.$stand_alone;
		$$websharks_core.$s2member.$stand_alone.prototype.setup_initialize = function()
			{
				var $$ = this, $ = jQuery; // $Quickies.

				$(document)// Handles document ready state event (DOM ready).
					.ready(function() // Setup/initialization routines.
					       {
						       // Add box-shadow class to stand-alone content containers.
						       $('.' + $$.plugin_css_class() + ' div.content.container')
							       .addClass('box-shadow-bottom');
					       });
			};
		/**
		 * @type {Object} Creating an instance of this extension.
		 */
		$s2member.$stand_alone = new $$websharks_core.$s2member.$stand_alone();

	})(this); // End extension closure.