<?php
/**
 * ESP (Base Class Abstraction).
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\ESPs
 * @since 120318
 */
namespace s2member\esps
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * ESP (Base Class Abstraction).
		 *
		 * @package s2Member\ESPs
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		abstract class esp extends \s2member\framework implements \s2member\esp_interface
		{
			/**
			 * @var integer The `ID` for this ESP in the `esps` database table.
			 *
			 * @by-constructor Set dynamically by class constructor.
			 */
			public $ID = 0; // Default value `0`.

			/**
			 * @var string The `name` for this ESP in the `esps` database table.
			 *    This MUST also match the name of the class itself (so it can be referenced systematically).
			 *
			 * @by-constructor Set dynamically by class constructor.
			 */
			public $name = ''; // Default value.

			/**
			 * Constructor.
			 *
			 * @param object|array $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``;
			 *    or a new ``$___instance_config`` array.
			 *
			 * @throws \s2member\exception If ``$this->name`` is empty upon class construction.
			 * @throws \s2member\exception If ``$this->name`` does NOT match the ESP class.
			 */
			public function __construct($___instance_config)
				{
					parent::__construct($___instance_config);

					$this->name = $this->___instance_config->ns_class_basename;

					if(!($this->ID = $this->©esp->id($this->name)))
						throw $this->©exception(
							$this->method(__FUNCTION__).'#unexpected_esp_name', get_defined_vars(),
							$this->i18n('Unexpected ESP `$name`. Unable to locate an ID for this ESP.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $this->name)
						);
				}

			/**
			 * Maps segment vars; based on site owner configuration.
			 *
			 * @param array                                 $segment Segment specs.
			 *    An already parsed array of segment specs, with these elements.
			 *       • (string)`type` Segment type (perhaps: `list`).
			 *       • (string)`value` The segment value itself.
			 *
			 * @param null|integer|\WP_User|\s2member\users $user Optional. The user we're working with here.
			 *
			 * @return array An array of segment vars, else an empty array if nothing can be populated.
			 *    See also: {@link \s2member\esp_segment_vars::map()} for further details.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 * @throws \s2member\exception If ``$segment`` is empty (or is an invalid array of specs).
			 * @throws \s2member\exception If ``$user`` is NOT, or CANNOT, be populated (i.e. no email to work with).
			 */
			public function map_segment_vars($segment, $user = NULL)
				{
					return $this->©esp_segment_vars->map($this->ID, $segment, $user);
				}

			/**
			 * Gets a meta value associated with this ESP.
			 *
			 * @param string $name Name of a meta value that we're seeking.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_value($name)
				{
					return $this->©db_utils->get_meta_values('esp_meta', 'esp_id', $this->ID, (string)$name);
				}

			/**
			 * Gets meta value(s) associated with this ESP.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return mixed See {@link db_utils::get_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::get_meta_values()}.
			 */
			public function get_meta_values($names = self::all)
				{
					return $this->©db_utils->get_meta_values('esp_meta', 'esp_id', $this->ID, $names);
				}

			/**
			 * Inserts (or updates) a meta value associated with this ESP.
			 *
			 * @param string $name The name for this meta value (e.g. it's key in the database).
			 * @param mixed  $value The value for this meta entry.
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_value($name, $value)
				{
					return $this->©db_utils->update_meta_values('esp_meta', 'esp_id', $this->ID, array((string)$name => $value));
				}

			/**
			 * Inserts (or updates) meta value(s) associated with this ESP.
			 *
			 * @param array $values Associative array of meta values (e.g. key/value pairs).
			 *
			 * @return integer See {@link db_utils::update_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::update_meta_values()}.
			 */
			public function update_meta_values($values)
				{
					return $this->©db_utils->update_meta_values('esp_meta', 'esp_id', $this->ID, $values);
				}

			/**
			 * Deletes a meta value associated with this ESP.
			 *
			 * @param string $name The name of a meta value (e.g. it's key in the database).
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_value($name)
				{
					return $this->©db_utils->delete_meta_values('esp_meta', 'esp_id', $this->ID, (string)$name);
				}

			/**
			 * Deletes meta value(s) associated with this ESP.
			 *
			 * @param string|array $names Name(s) associated with a meta value.
			 *
			 * @return integer See {@link db_utils::delete_meta_values()} for further details.
			 *
			 * @throws \s2member\exception See {@link db_utils::delete_meta_values()}.
			 */
			public function delete_meta_values($names = self::all)
				{
					return $this->©db_utils->delete_meta_values('esp_meta', 'esp_id', $this->ID, $names);
				}
		}
	}