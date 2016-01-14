<?php
/**
 * Diagnostics.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Diagnostics
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Diagnostics.
		 *
		 * @package s2Member\Diagnostics
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class diagnostics extends \websharks_core_v000000_dev\diagnostics
		{
			/**
			 * Logs diagnostics.
			 *
			 * @param string $code Required diagnostic code (must NOT be empty).
			 *
			 * @param mixed  $data Required diagnostic data (i.e. something to assist in reporting/logging).
			 *    This is a required argument, but a NULL value is accepted here (that's fine).
			 *
			 * @param string $message Required diagnostic message (must NOT be empty).
			 */
			public function db_log($code, $data, $message)
				{
					$this->check_arg_types('string:!empty', '', 'string:!empty', func_get_args());

					if(!$this->db_log) return; // DB logging NOT enabled here.

					if(!$this->©db->insert($this->©db_table->get('diagnostic_log'),
					                       array('type' => $this->type, 'code' => $code, 'time' => time()))
					   || !($diagnostic_id = $this->©db->insert_id)
					) return; // Insertion failure.

					$this->©db->insert($this->©db_table->get('diagnostic_messages'),
					                   array('diagnostic_id' => $diagnostic_id, 'message' => $message));

					$this->©db_utils->insert_meta_values(
						'diagnostic_meta', 'diagnostic_id', $diagnostic_id,
						array('memory_details'     => $this->©env->memory_details(),
						      'version_details'    => $this->©env->version_details(),
						      'current_user_id'    => $this->©user->ID,
						      'current_user_email' => $this->©user->email));

					if($data) // Update meta values (in case data has conflicting keys).
						$this->©db_utils->update_meta_values('diagnostic_meta', 'diagnostic_id', $diagnostic_id,
						                                     $this->©db_utils->metafy($data));
				}
		}
	}