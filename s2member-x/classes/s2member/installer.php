<?php
/**
 * Installation Utilities.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Installation Utilities.
		 *
		 * @package s2Member
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 *
		 * @property \s2member\media $©media
		 * @method \s2member\media ©media()
		 */
		class installer extends \websharks_core_v000000_dev\installer
		{
			/**
			 * Additional activation/installation routines.
			 *
			 * @return boolean TRUE if all routines were successful, else FALSE if there were any failures.
			 */
			public function additional_activations()
				{
					if($this->©media->activation_install(TRUE)) return TRUE;

					return FALSE; // Default return value.
				}

			/**
			 * Additional deactivation/uninstall routines.
			 *
			 * @return boolean TRUE if all routines were successful, else FALSE if there were any failures.
			 */
			public function additional_deactivations()
				{
					if($this->©media->deactivation_uninstall(TRUE)) return TRUE;

					return FALSE; // Default return value.
				}
		}
	}