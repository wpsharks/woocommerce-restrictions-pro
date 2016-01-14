/**
 * Menu Pages Extension.
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
		if(typeof $w.$$websharks_core.$s2member.$menu_pages === 'function')
			return; // Extension already exists.

		/**
		 * @constructor Plugin extension constructor & prototype definitions.
		 */
		$w.$$websharks_core.$s2member.$menu_pages = function(){this.setup_initialize.apply(this, arguments);};
		$w.$$websharks_core.$s2member.$menu_pages.prototype = new $w.$$websharks_core.$('s2member', 'menu_pages');
		$w.$$websharks_core.$s2member.$menu_pages.prototype.constructor = $w.$$websharks_core.$s2member.$menu_pages;
		$w.$$websharks_core.$s2member.$menu_pages.prototype.setup_initialize = function()
			{
				var $$ = this, $ = jQuery; // $Quickies.

				$(document)// Handles document ready state event (DOM ready).
					.ready(function() // Setup/initialization routines.
					       {
						       var div = {}; // Start by initializing a few variables.
					       });
			};
		/**
		 * @type {Object} Creating an instance of this extension.
		 */
		$w.$s2member.$menu_pages = new $w.$$websharks_core.$s2member.$menu_pages();

	})(this); // End extension closure.