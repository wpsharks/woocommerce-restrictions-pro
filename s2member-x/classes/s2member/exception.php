<?php
/**
 * Exception.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Exceptions
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Exception.
		 *
		 * @package s2Member\Exceptions
		 * @since 120318
		 */
		class exception extends \websharks_core_v000000_dev\exception
		{
			/**
			 * @note s2Member® treats exceptions like diagnostics.
			 *    These go into our `diagnostic_log`, with type: `exception`.
			 */
			public function db_log()
				{
					if(!$this->db_log) return; // DB logging NOT enabled here.

					if(!$this->plugin->©db->insert($this->plugin->©db_table->get('diagnostic_log'),
					                               array('type' => 'exception', 'code' => $this->getCode(), 'time' => time()))
					   || !($diagnostic_id = $this->plugin->©db->insert_id)
					) return; // Insertion failure.

					$this->plugin->©db->insert($this->plugin->©db_table->get('diagnostic_messages'),
					                           array('diagnostic_id' => $diagnostic_id, 'message' => $this->getMessage()));

					$this->plugin->©db_utils->insert_meta_values(
						'diagnostic_meta', 'diagnostic_id', $diagnostic_id,
						array('exception_line'        => $this->getLine(),
						      'exception_file'        => $this->getFile(),
						      'exception_stack_trace' => $this->getTraceAsString(),
						      'memory_details'        => $this->plugin->©env->memory_details(),
						      'version_details'       => $this->plugin->©env->version_details(),
						      'current_user_id'       => $this->plugin->©user->ID,
						      'current_user_email'    => $this->plugin->©user->email));

					if($this->data) // Update meta values (in case data has conflicting keys).
						$this->plugin->©db_utils->update_meta_values('diagnostic_meta', 'diagnostic_id', $diagnostic_id,
						                                             $this->plugin->©db_utils->metafy($this->data));
				}
		}
	}