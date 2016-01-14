-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: internal-db.s91896.gridserver.com
-- Generation Time: Jul 04, 2013 at 01:42 PM
-- Server version: 5.1.55-rel12.6
-- PHP Version: 5.3.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db91896_demo_s2_wp`
--

-- --------------------------------------------------------

--
-- Table structure for table `s2_behavior_types`
--

CREATE TABLE IF NOT EXISTS `s2_behavior_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of behavior.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this behavior type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_behavior_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

--
-- Dumping data for table `s2_behavior_types`
--

INSERT INTO `s2_behavior_types` (`ID`, `type`, `label`) VALUES
(0, 'none', 'No Behavior'),
(1, 'default', 'Default Behavior'),
(2, 'code', 'Process/Display Code'),
(3, 'email', 'Send Email'),
(4, 'esp', 'Email Service Provider'),
(5, 'notification', 'Send HTTP Notification'),
(6, 'passtag', 'Add/Remove Passtag'),
(7, 'redirect', 'Redirect Browser'),
(8, 'status', 'Change Status'),
(9, 'activate_status', 'Activate Status'),
(10, 'deactivate_status', 'Deactivate Status'),
(11, 'reactivate_status', 'Reactivate Status'),
(12, 'delete_status', 'Delete Status'),
(13, 'add_user_passtag', 'Add User Passtag'),
(14, 'add_user_passtag_x', 'Add User Passtag (X)'),
(15, 'remove_user_passtag', 'Remove User Passtag'),
(16, 'remove_user_passtag_x', 'Remove User Passtag (X)'),
(17, 'renew_user_passtag', 'Renew User Passtag'),
(18, 'renew_aggregate_user_passtags', 'Renew Aggregate User Passtags'),
(19, 'renew_all_user_passtags', 'Renew All User Passtags'),
(20, 'deactivate_user_passtag', 'Deactivate User Passtag'),
(21, 'deactivate_aggregate_user_passtags', 'Deactivate Aggregate User Passtags'),
(22, 'deactivate_all_user_passtags', 'Deactivate All User Passtags'),
(23, 'reactivate_user_passtag', 'Reactivate User Passtag'),
(24, 'reactivate_aggregate_user_passtags', 'Reactivate Aggregate User Passtags'),
(25, 'reactivate_all_user_passtags', 'Reactivate All User Passtags'),
(26, 'delete_user_passtag', 'Delete User Passtag'),
(27, 'delete_aggregate_user_passtags', 'Delete Aggregate User Passtags'),
(28, 'delete_all_user_passtags', 'Delete All User Passtags'),
(29, 'esp_subscribe', 'ESP Subscribe'),
(30, 'esp_silent_subscribe', 'ESP Subscribe (Silently)'),
(31, 'esp_unsubscribe', 'ESP Unsubscribe'),
(32, 'esp_silent_unsubscribe', 'ESP Unsubscribe (Silently)'),
(33, 'esp_transition', 'ESP Transition'),
(34, 'esp_transition_subscribe', 'ESP Transition/Subscribe'),
(35, 'esp_silent_transition_subscribe', 'ESP Transition/Subscribe (Silently)'),
(36, 'esp_sync_profile', 'ESP Synchronize Profile Data');

-- --------------------------------------------------------

--
-- Table structure for table `s2_coupons`
--

CREATE TABLE IF NOT EXISTS `s2_coupons` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Coupon code.',
  `affiliate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Associated affiliate ID (if applicable).',
  `message` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Message to display when coupon is accepted.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_coupon` (`code`),
  FULLTEXT KEY `ft_searchable_code_affiliate_message` (`code`,`affiliate`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_coupon_limits`
--

CREATE TABLE IF NOT EXISTS `s2_coupon_limits` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `coupon_id` bigint(20) unsigned NOT NULL COMMENT 'Related coupon ID.',
  `coupon_limit_type_id` bigint(20) unsigned NOT NULL COMMENT 'Type of limitation.',
  `limitation` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Actual limitation (depending on type).',
  `message` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Message to display when limitation is reached.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_coupon_limit` (`coupon_id`,`coupon_limit_type_id`,`limitation`),
  FULLTEXT KEY `ft_searchable_message` (`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_coupon_limit_types`
--

CREATE TABLE IF NOT EXISTS `s2_coupon_limit_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Coupon limit type.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this coupon limit type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_coupon_limit_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `s2_coupon_limit_types`
--

INSERT INTO `s2_coupon_limit_types` (`ID`, `type`, `label`) VALUES
(1, 'time_starts', 'Start Time'),
(2, 'time_stops', 'Stop Time'),
(3, 'max_uses', 'Max Uses'),
(4, 'max_ips', 'Max IPs'),
(5, 'max_users', 'Max Users'),
(6, 'required_post_id', 'Required Post ID'),
(7, 'required_form_id', 'Required Form ID'),
(8, 'required_passtag_id', 'Required Passtag ID');

-- --------------------------------------------------------

--
-- Table structure for table `s2_coupon_log`
--

CREATE TABLE IF NOT EXISTS `s2_coupon_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `coupon_id` bigint(20) unsigned NOT NULL COMMENT 'Related coupon ID.',
  `order_session_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related order session ID (if applicable).',
  `transaction_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related transaction ID (if applicable).',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user ID (if applicable).',
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Customer IP address.',
  `time` int(10) unsigned NOT NULL COMMENT 'Time this coupon was accepted.',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_coupon_modifiers`
--

CREATE TABLE IF NOT EXISTS `s2_coupon_modifiers` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `coupon_id` bigint(20) unsigned NOT NULL COMMENT 'Related coupon ID.',
  `attribute_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute name that will be modified.',
  `attribute_modifier` text COLLATE utf8_unicode_ci COMMENT 'New attribute value, or modifier (if applicable).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_coupon_modifier` (`coupon_id`,`attribute_name`),
  FULLTEXT KEY `ft_searchable_attribute_name_modifier` (`attribute_name`,`attribute_modifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_diagnostic_log`
--

CREATE TABLE IF NOT EXISTS `s2_diagnostic_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` enum('diagnostic','exception','success','error') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Diagnostic type.',
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Diagnostic code.',
  `time` int(10) unsigned NOT NULL COMMENT 'Diagnostic time.',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_diagnostic_messages`
--

CREATE TABLE IF NOT EXISTS `s2_diagnostic_messages` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `diagnostic_id` bigint(20) unsigned NOT NULL COMMENT 'Related diagnostic ID.',
  `message` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Diagnostic message.',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_message` (`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_diagnostic_meta`
--

CREATE TABLE IF NOT EXISTS `s2_diagnostic_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `diagnostic_id` bigint(20) unsigned NOT NULL COMMENT 'Related diagnostic ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_diagnostic_meta` (`diagnostic_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_esps`
--

CREATE TABLE IF NOT EXISTS `s2_esps` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Internal name (for administrative reference).',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this ESP.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_esp` (`name`),
  FULLTEXT KEY `ft_searchable_name_label` (`name`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `s2_esps`
--

INSERT INTO `s2_esps` (`ID`, `name`, `label`) VALUES
(1, 'mailchimp', 'MailChimp®'),
(2, 'aweber', 'AWeber®');

-- --------------------------------------------------------

--
-- Table structure for table `s2_esp_meta`
--

CREATE TABLE IF NOT EXISTS `s2_esp_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `esp_id` bigint(20) unsigned NOT NULL COMMENT 'Related email service provider ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_esp_meta` (`esp_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_esp_segment_types`
--

CREATE TABLE IF NOT EXISTS `s2_esp_segment_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Segment type.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this ESP segment type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_esp_segment_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `s2_esp_segment_types`
--

INSERT INTO `s2_esp_segment_types` (`ID`, `type`, `label`) VALUES
(1, 'list', 'List');

-- --------------------------------------------------------

--
-- Table structure for table `s2_esp_segment_vars`
--

CREATE TABLE IF NOT EXISTS `s2_esp_segment_vars` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `esp_id` bigint(20) unsigned NOT NULL COMMENT 'Related email service provider ID.',
  `esp_segment_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related segment type ID.',
  `segment` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Segment (depending on type).',
  `esp_var` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Variable (on the ESP side).',
  `var` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Variable (on application side).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_esp_segment_var` (`esp_id`,`esp_segment_type_id`,`segment`,`esp_var`),
  FULLTEXT KEY `ft_searchable_segment_vars` (`segment`,`esp_var`,`var`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_handler_id` bigint(20) unsigned NOT NULL COMMENT 'Related event handler ID.',
  `behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Related behavior type ID.',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Order that behaviors should behave in.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_behavior_statuses`
--

CREATE TABLE IF NOT EXISTS `s2_event_behavior_statuses` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Related user ID (if applicable). Defaults to 0 instead of NULL because we need to maintain a unique index across what would otherwise be NULL values. NOTE: A value of -1 indicates all users. -2 indicates ALL users (even NO user).',
  `user_passtag_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Related user passtag ID (if applicable). Defaults to 0 instead of NULL because we need to maintain a unique index across what would otherwise be NULL values (NULL values are excluded from a unique MySQL index).',
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Forced behavior status.',
  `time` int(10) unsigned NOT NULL COMMENT 'Time status was created; or last updated.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_behavior_status` (`event_behavior_id`,`user_id`,`user_passtag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_code_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_code_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event code behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event code behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `code` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Code. PHP tags are supported here.',
  `hook` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'wp_footer' COMMENT 'Action hook (or filter) for this code (if applicable; e.g. if it should be displayed on-site in a particular location). Some codes are evaluated only, they are NOT displayed on-site when this is NULL.',
  `hook_priority` int(10) DEFAULT '10' COMMENT 'Action hook (or filter) priority; if applicable. NOTE: this can also be negative thanks to WordPress®.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_code_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_code` (`name`,`label`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_email_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_email_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event email behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event email behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `from_name` text COLLATE utf8_unicode_ci COMMENT 'From name.',
  `from_addr` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email from address.',
  `recipients` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email recipients (use semicolon delimitation).',
  `subject` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email subject line.',
  `headers` text COLLATE utf8_unicode_ci COMMENT 'Serialized array of email headers; or a line-delimited list of email headers. Example: `Header: value`.',
  `attachments` text COLLATE utf8_unicode_ci COMMENT 'Serialized array of email attachments; or a line-delimited list of all email attachment paths. Paths can be absolute or relative to ABSPATH.',
  `message` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Message body (HTML). If message contains HTML, it will be sent as-is (HTML) w/ an auto-generated plain text alternative (multipart). Else, it''s converted from plain text to HTML automatically (new lines convert to <br />). PHP tags are supported here too.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_email_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_subject_messsage` (`name`,`label`,`subject`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_esp_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_esp_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event ESP behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event ESP behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Related behavior type ID (for the ESP action).',
  `esp_id` bigint(20) unsigned NOT NULL COMMENT 'Related ESP ID.',
  `esp_segment_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related ESP segment type ID.',
  `segment` text COLLATE utf8_unicode_ci COMMENT 'Segment (depending on type). NULL indicates all segments of a particular type (based on the segment type ID); and when supported by the behavior. Unsubscribes & profile updates are examples where NULL works nicely (handling all segments automatically).',
  `old_esp_segment_type_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Old ESP segment type ID (when/if applicable); and if supported by the behavior. This is used primarily for ESP transitions.',
  `old_segment` text COLLATE utf8_unicode_ci COMMENT 'Old ESP segment (when/if applicable); and if supported by the behavior. NULL indicates all segments of a particular type (based on old segment type ID). Used primarly in transitions; where NULL works nicely (e.g. transitioning away from all segments).',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_esp_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_segments` (`name`,`label`,`segment`,`old_segment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_handlers`
--

CREATE TABLE IF NOT EXISTS `s2_event_handlers` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event handler name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event handler.',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Related user ID. NULL indicates any user (MUST have user). -1 indicates NO user (MUST NOT have). 0 indicates N/A; dont care about user. Any other value indicates a specific user.',
  `passtag_id` bigint(20) DEFAULT NULL COMMENT 'Related passtag ID. NULL indicates any passtag (MUST have passtag). -1 indicates NO passtag (MUST NOT have). 0 indicates N/A; dont care about passtag. Any other value indicates a specific passtag.',
  `event_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related event type ID.',
  `unique` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Process unique events only? In other words, ignore events after first occurrence; until such time as the event is triggered again w/ different data.',
  `consolidate` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Consolidate this event across any/all overlapping passtags? In other words, if a user has five passtag B''s, only process this event on one of those; or on all instances of passtag B?',
  `start_after_nth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'After nth occurence only? Only behave after the event has already been fired X times before. To start behaving on and after 3rd occurrence, set this to a value of 2. Default is 0 (immediately). If the event is unique; this is based on unique occurrences.',
  `stop_after_nth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Stop after nth occurence? Stop behaving after the event has been processed X times. For example, to stop behaving after 5th occurrence, set this to a value of 5. Default is 0 (do NOT stop). If the event is unique; this is based on unique occurrences.',
  `conditions` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'PHP conditional expression. If supplied, conditions MUST evaluate to TRUE; else processing is stopped (e.g. nothing is processed; event is ignored). Custom conditions are applied last (e.g. after unique checks, consolidation checks, and start/stop checks)',
  `offset_time` int(10) NOT NULL DEFAULT '0' COMMENT 'Offset time for this behavior, after the event occurs (or before the event occurs if negative). Defaults to 0, indicating the behavior will be processed immediately. Negative offset times work only w/ futuristic event types.',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Order that handlers should be applied in.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Event handler status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_handler` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_conditions` (`name`,`label`,`conditions`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_log`
--

CREATE TABLE IF NOT EXISTS `s2_event_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `event_handler_id` bigint(20) unsigned NOT NULL COMMENT 'Related event handler ID.',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user ID (if applicable).',
  `user_passtag_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user passtag ID (if applicable).',
  `passtag_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related passtag ID (if applicable).',
  `order_session_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related order session ID (if applicable).',
  `transaction_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related transaction ID (if applicable).',
  `vars` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Serialized array of all defined vars passed by the underlying event caller (in PHP code). This is less important than meta values we store also.',
  `time` int(10) unsigned NOT NULL COMMENT 'Time this event took place.',
  `process_time` int(10) unsigned DEFAULT NULL COMMENT 'Time to process this event occurrence (applicable only if this event occurrence has not yet been processed).',
  `processed_time` int(10) NOT NULL DEFAULT '0' COMMENT 'Time this event was processed (0 indicates unprocessed; e.g. it is still awaiting processing). If logging an instance only (i.e. NOT processing; only tracking nth occurrences) set this to -1 to indicate it''s a processed instance.',
  `unique_sha1` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'SHA1 hash identifying all unique aspects of the event process that logged this entry. This MUST be generated dynamically, and with careful consideration. Considerations should include (at a minimum) the event ID and serialized meta vars.',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_log_meta`
--

CREATE TABLE IF NOT EXISTS `s2_event_log_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `event_log_id` bigint(20) unsigned NOT NULL COMMENT 'Related event log ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_log_meta` (`event_log_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_notification_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_notification_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event notification behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event notification behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `url` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Notification URL. PHP tags are supported here.',
  `method` enum('GET','POST') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'POST' COMMENT 'HTTP connection method (GET or POST).',
  `timeout` int(10) unsigned NOT NULL DEFAULT '5' COMMENT 'Connection timeout value (defaults to 5 seconds).',
  `blocking` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Use blocking mode (e.g. wait for a response); or non-blocking mode (e.g. do NOT wait for a response)?',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_notification_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_url` (`name`,`label`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_passtag_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_passtag_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event passtag behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event passtag behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Related behavior type ID (e.g. add or remove).',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Apply behavior to a specific user ID? NULL (or 0) indicates the user associated with the event.',
  `passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Related passtag ID (to add/remove).',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_passtag_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label` (`name`,`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_redirect_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_redirect_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event redirect behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event redirect behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `url` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Redirection URL. PHP tags are supported here.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_redirect_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_url` (`name`,`label`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_status_behaviors`
--

CREATE TABLE IF NOT EXISTS `s2_event_status_behaviors` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event status behavior name.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event status behavior.',
  `event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID.',
  `behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Related behavior type ID (e.g. activate/deactivate).',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Apply behavior to a specific user ID? NULL or 0 indicates the user associated with the event (if there is one). -1 indicates all users. -2 indicates ALL users (even NO user).',
  `this_event_behavior_id` bigint(20) unsigned NOT NULL COMMENT 'Related event behavior ID (to activate/deactivate).',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Behavior status.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_status_behavior` (`name`),
  FULLTEXT KEY `ft_searchable_name_label` (`name`,`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_event_types`
--

CREATE TABLE IF NOT EXISTS `s2_event_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of event.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this event type.',
  `includes_user` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes a specific user?',
  `includes_user_passtag` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes a specific user passtag? NOTE: If an event type includes a user passtag, it MUST also include a user.',
  `includes_aggregate_user_passtags` enum('active','inactive','deleted','accessible','inaccessible','order_session','transaction') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Includes aggregate user passtags? Specify aggregate group if applicable. NOTE: If an event type includes aggregate user passtags, it MUST also include a user.',
  `includes_passtag` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes a specific passtag? NOTE: An event may NOT include a passtag if it includes a user passtag in some way. User passtags are already associated with a passtag.',
  `includes_order_session_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes order session ID? NOTE: If an event type includes an order session ID, it MUST also include a user.',
  `includes_transaction_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes transaction ID? NOTE: If an event type includes a transaction ID, it MUST also include a user.',
  `includes_futuristic_time` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Includes futuristic time?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_event_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

--
-- Dumping data for table `s2_event_types`
--

INSERT INTO `s2_event_types` (`ID`, `type`, `label`, `includes_user`, `includes_user_passtag`, `includes_aggregate_user_passtags`, `includes_passtag`, `includes_order_session_id`, `includes_transaction_id`, `includes_futuristic_time`) VALUES
(1, 'checkout_success', 'Checkout Success', 1, 0, 'order_session', 0, 1, 1, 0),
(2, 'checkout_failure', 'Checkout Failure', 1, 0, 'order_session', 0, 1, 0, 0),
(3, 'checkout_abandonment', 'Checkout Abandonment', 1, 0, 'order_session', 0, 1, 0, 0),
(4, 'payment_success', 'Payment Success', 1, 0, 'transaction', 0, 1, 1, 0),
(5, 'payment_failure', 'Payment Failure', 1, 0, 'transaction', 0, 1, 1, 0),
(6, 'subscr_payment_failure', 'Subscription Payment Failure', 1, 0, 'transaction', 0, 1, 1, 0),
(7, 'subscr_payment_success', 'Subscription Payment Success', 1, 0, 'transaction', 0, 1, 1, 0),
(8, 'before_subscr_card_expiration', 'Before Subscription Card Expiration', 1, 0, 'transaction', 0, 1, 1, 1),
(9, 'subscr_card_expiration', 'Subscription Card Expiration', 1, 0, 'transaction', 0, 1, 1, 0),
(10, 'before_subscr_expiration', 'Before Subscription Expiration', 1, 0, 'transaction', 0, 1, 1, 1),
(11, 'subscr_expiration', 'Subscription Expiration', 1, 0, 'transaction', 0, 1, 1, 0),
(12, 'subscr_cancellation', 'Subscription Cancellation', 1, 0, 'transaction', 0, 1, 1, 0),
(13, 'subscr_suspension', 'Subscription Suspension', 1, 0, 'transaction', 0, 1, 1, 0),
(14, 'txn_chargeback', 'Transaction Chargeback', 1, 0, 'transaction', 0, 1, 1, 0),
(15, 'txn_refund', 'Transaction Refund', 1, 0, 'transaction', 0, 1, 1, 0),
(16, 'user_creation', 'User Creation', 1, 0, 'active', 0, 0, 0, 0),
(17, 'wp_user_creation', 'WP User Creation (system-wide)', 1, 0, 'active', 0, 0, 0, 0),
(18, 'user_activation', 'User Activation', 1, 0, 'active', 0, 0, 0, 0),
(19, 'wp_user_activation', 'WP User Activation (system-wide)', 1, 0, 'active', 0, 0, 0, 0),
(20, 'user_update', 'User Update', 1, 0, 'active', 0, 0, 0, 0),
(21, 'wp_user_update', 'WP User Update (system-wide)', 1, 0, 'active', 0, 0, 0, 0),
(22, 'user_gets_passtag', 'User Gets Passtag', 1, 1, NULL, 0, 0, 0, 0),
(23, 'user_renews_passtag', 'User Renews Passtag', 1, 1, NULL, 0, 0, 0, 0),
(24, 'user_loses_passtag', 'User Loses Passtag', 1, 1, NULL, 0, 0, 0, 0),
(25, 'user_regains_passtag', 'User Regains Passtag', 1, 1, NULL, 0, 0, 0, 0),
(26, 'user_login_success', 'User Login Success', 1, 0, 'active', 0, 0, 0, 0),
(27, 'wp_user_login_success', 'WP User Login Success (system-wide)', 1, 0, 'active', 0, 0, 0, 0),
(28, 'user_login_failure', 'User Login Failure', 1, 0, NULL, 0, 0, 0, 0),
(29, 'wp_user_login_failure', 'WP User Login Failure (system-wide)', 1, 0, NULL, 0, 0, 0, 0),
(30, 'user_reaches_max_login_failures', 'User Reaches Max Login Failures', 1, 0, NULL, 0, 0, 0, 0),
(31, 'user_deletion', 'User Deletion', 1, 0, 'active', 0, 0, 0, 0),
(32, 'wp_user_deletion', 'WP User Deletion (system-wide)', 1, 0, 'active', 0, 0, 0, 0),
(33, 'before_user_passtag_reaches_time_stops', 'Before User Passtag Reaches Expiration', 1, 1, NULL, 0, 0, 0, 1),
(34, 'user_reaches_passtag_time_stops', 'User Reaches Passtag Expiration', 1, 1, NULL, 0, 0, 0, 0),
(35, 'user_passtag_reaches_time_stops', 'User Passtag Reaches Expiration', 1, 1, NULL, 0, 0, 0, 0),
(36, 'user_reaches_passtag_uses_limit', 'User Reaches Passtag Max Uses', 1, 1, NULL, 0, 0, 0, 0),
(37, 'user_passtag_reaches_uses_limit', 'User Passtag Reaches Max Uses', 1, 1, NULL, 0, 0, 0, 0),
(38, 'user_reaches_passtag_ips_limit', 'User Reaches Passtag Max IPs', 1, 1, NULL, 0, 0, 0, 0),
(39, 'user_passtag_reaches_ips_limit', 'User Passtag Reaches Max IPs', 1, 1, NULL, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `s2_gateways`
--

CREATE TABLE IF NOT EXISTS `s2_gateways` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Internal name (for administrative reference).',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this gateway.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_gateway` (`name`),
  FULLTEXT KEY `ft_searchable_name_label` (`name`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `s2_gateways`
--

INSERT INTO `s2_gateways` (`ID`, `name`, `label`) VALUES
(1, 'paypal_standard', 'PayPal® Standard'),
(2, 'paypal_advanced', 'PayPal® Advanced'),
(3, 'paypal_pro_payflow', 'PayPal® Pro (Payflow™ Edition)'),
(4, 'paypal_pro', 'PayPal® Pro'),
(5, 'authnet', 'Authorize.Net®'),
(6, 'clickbank', 'ClickBank®'),
(7, 'google_wallet', 'Google® Wallet'),
(8, 'stripe', 'Stripe™');

-- --------------------------------------------------------

--
-- Table structure for table `s2_gateway_meta`
--

CREATE TABLE IF NOT EXISTS `s2_gateway_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `gateway_id` bigint(20) unsigned NOT NULL COMMENT 'Related gateway ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_gateway_meta` (`gateway_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_geo_areas`
--

CREATE TABLE IF NOT EXISTS `s2_geo_areas` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `parent_geo_area_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related parent area ID (NULL for countries).',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Area name. If listable, this is displayed in select menus. Otherwise, it''s simply for administrative reference.',
  `abbr` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Abbreviated name (if applicable).',
  `code_2` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Two character abbreviation/code (if applicable).',
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Postal code (if applicable). Possibly an international postal code. Possibly a minimum, where postal_code_range is the maximum. In the case of a range, this MUST be an integer. See also: http://www.barnesandnoble.com/help/cds2.asp?PID=8134',
  `postal_code_range` int(10) unsigned DEFAULT NULL COMMENT 'If applicable. A postal code range (with postal_code being the minimum, and this being the maximum). Must be an integer.',
  `regex_php` text COLLATE utf8_unicode_ci COMMENT 'PHP regex pattern matching form input data (if applicable).',
  `listable` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Can this be listed in forms? If even one entry under a specific parent area ID is listable, standard form input fields are disabled, in favor of select menus (matching DB entries).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_geo_area` (`parent_geo_area_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_abbr_postal_code` (`name`,`abbr`,`postal_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=328 ;

--
-- Dumping data for table `s2_geo_areas`
--

INSERT INTO `s2_geo_areas` (`ID`, `parent_geo_area_id`, `name`, `abbr`, `code_2`, `postal_code`, `postal_code_range`, `regex_php`, `listable`) VALUES
(1, NULL, 'AFGHANISTAN', 'AF', 'AF', NULL, NULL, NULL, 1),
(2, NULL, 'ÅLAND ISLANDS', 'AX', 'AX', NULL, NULL, NULL, 1),
(3, NULL, 'ALBANIA', 'AL', 'AL', NULL, NULL, NULL, 1),
(4, NULL, 'ALGERIA', 'DZ', 'DZ', NULL, NULL, NULL, 1),
(5, NULL, 'AMERICAN SAMOA', 'AS', 'AS', NULL, NULL, NULL, 1),
(6, NULL, 'ANDORRA', 'AD', 'AD', NULL, NULL, NULL, 1),
(7, NULL, 'ANGOLA', 'AO', 'AO', NULL, NULL, NULL, 1),
(8, NULL, 'ANGUILLA', 'AI', 'AI', NULL, NULL, NULL, 1),
(9, NULL, 'ANTARCTICA', 'AQ', 'AQ', NULL, NULL, NULL, 1),
(10, NULL, 'ANTIGUA AND BARBUDA', 'AG', 'AG', NULL, NULL, NULL, 1),
(11, NULL, 'ARGENTINA', 'AR', 'AR', NULL, NULL, NULL, 1),
(12, NULL, 'ARMENIA', 'AM', 'AM', NULL, NULL, NULL, 1),
(13, NULL, 'ARUBA', 'AW', 'AW', NULL, NULL, NULL, 1),
(14, NULL, 'AUSTRALIA', 'AU', 'AU', NULL, NULL, NULL, 1),
(15, NULL, 'AUSTRIA', 'AT', 'AT', NULL, NULL, NULL, 1),
(16, NULL, 'AZERBAIJAN', 'AZ', 'AZ', NULL, NULL, NULL, 1),
(17, NULL, 'BAHAMAS', 'BS', 'BS', NULL, NULL, NULL, 1),
(18, NULL, 'BAHRAIN', 'BH', 'BH', NULL, NULL, NULL, 1),
(19, NULL, 'BANGLADESH', 'BD', 'BD', NULL, NULL, NULL, 1),
(20, NULL, 'BARBADOS', 'BB', 'BB', NULL, NULL, NULL, 1),
(21, NULL, 'BELARUS', 'BY', 'BY', NULL, NULL, NULL, 1),
(22, NULL, 'BELGIUM', 'BE', 'BE', NULL, NULL, NULL, 1),
(23, NULL, 'BELIZE', 'BZ', 'BZ', NULL, NULL, NULL, 1),
(24, NULL, 'BENIN', 'BJ', 'BJ', NULL, NULL, NULL, 1),
(25, NULL, 'BERMUDA', 'BM', 'BM', NULL, NULL, NULL, 1),
(26, NULL, 'BHUTAN', 'BT', 'BT', NULL, NULL, NULL, 1),
(27, NULL, 'BOLIVIA, PLURINATIONAL STATE OF', 'BO', 'BO', NULL, NULL, NULL, 1),
(28, NULL, 'BONAIRE, SINT EUSTATIUS AND SABA', 'BQ', 'BQ', NULL, NULL, NULL, 1),
(29, NULL, 'BOSNIA AND HERZEGOVINA', 'BA', 'BA', NULL, NULL, NULL, 1),
(30, NULL, 'BOTSWANA', 'BW', 'BW', NULL, NULL, NULL, 1),
(31, NULL, 'BOUVET ISLAND', 'BV', 'BV', NULL, NULL, NULL, 1),
(32, NULL, 'BRAZIL', 'BR', 'BR', NULL, NULL, NULL, 1),
(33, NULL, 'BRITISH INDIAN OCEAN TERRITORY', 'IO', 'IO', NULL, NULL, NULL, 1),
(34, NULL, 'BRUNEI DARUSSALAM', 'BN', 'BN', NULL, NULL, NULL, 1),
(35, NULL, 'BULGARIA', 'BG', 'BG', NULL, NULL, NULL, 1),
(36, NULL, 'BURKINA FASO', 'BF', 'BF', NULL, NULL, NULL, 1),
(37, NULL, 'BURUNDI', 'BI', 'BI', NULL, NULL, NULL, 1),
(38, NULL, 'CAMBODIA', 'KH', 'KH', NULL, NULL, NULL, 1),
(39, NULL, 'CAMEROON', 'CM', 'CM', NULL, NULL, NULL, 1),
(40, NULL, 'CANADA', 'CA', 'CA', NULL, NULL, NULL, 1),
(41, NULL, 'CAPE VERDE', 'CV', 'CV', NULL, NULL, NULL, 1),
(42, NULL, 'CAYMAN ISLANDS', 'KY', 'KY', NULL, NULL, NULL, 1),
(43, NULL, 'CENTRAL AFRICAN REPUBLIC', 'CF', 'CF', NULL, NULL, NULL, 1),
(44, NULL, 'CHAD', 'TD', 'TD', NULL, NULL, NULL, 1),
(45, NULL, 'CHILE', 'CL', 'CL', NULL, NULL, NULL, 1),
(46, NULL, 'CHINA', 'CN', 'CN', NULL, NULL, NULL, 1),
(47, NULL, 'CHRISTMAS ISLAND', 'CX', 'CX', NULL, NULL, NULL, 1),
(48, NULL, 'COCOS (KEELING) ISLANDS', 'CC', 'CC', NULL, NULL, NULL, 1),
(49, NULL, 'COLOMBIA', 'CO', 'CO', NULL, NULL, NULL, 1),
(50, NULL, 'COMOROS', 'KM', 'KM', NULL, NULL, NULL, 1),
(51, NULL, 'CONGO', 'CG', 'CG', NULL, NULL, NULL, 1),
(52, NULL, 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'CD', 'CD', NULL, NULL, NULL, 1),
(53, NULL, 'COOK ISLANDS', 'CK', 'CK', NULL, NULL, NULL, 1),
(54, NULL, 'COSTA RICA', 'CR', 'CR', NULL, NULL, NULL, 1),
(55, NULL, 'CÔTE D''IVOIRE', 'CI', 'CI', NULL, NULL, NULL, 1),
(56, NULL, 'CROATIA', 'HR', 'HR', NULL, NULL, NULL, 1),
(57, NULL, 'CUBA', 'CU', 'CU', NULL, NULL, NULL, 1),
(58, NULL, 'CURAÇAO', 'CW', 'CW', NULL, NULL, NULL, 1),
(59, NULL, 'CYPRUS', 'CY', 'CY', NULL, NULL, NULL, 1),
(60, NULL, 'CZECH REPUBLIC', 'CZ', 'CZ', NULL, NULL, NULL, 1),
(61, NULL, 'DENMARK', 'DK', 'DK', NULL, NULL, NULL, 1),
(62, NULL, 'DJIBOUTI', 'DJ', 'DJ', NULL, NULL, NULL, 1),
(63, NULL, 'DOMINICA', 'DM', 'DM', NULL, NULL, NULL, 1),
(64, NULL, 'DOMINICAN REPUBLIC', 'DO', 'DO', NULL, NULL, NULL, 1),
(65, NULL, 'ECUADOR', 'EC', 'EC', NULL, NULL, NULL, 1),
(66, NULL, 'EGYPT', 'EG', 'EG', NULL, NULL, NULL, 1),
(67, NULL, 'EL SALVADOR', 'SV', 'SV', NULL, NULL, NULL, 1),
(68, NULL, 'EQUATORIAL GUINEA', 'GQ', 'GQ', NULL, NULL, NULL, 1),
(69, NULL, 'ERITREA', 'ER', 'ER', NULL, NULL, NULL, 1),
(70, NULL, 'ESTONIA', 'EE', 'EE', NULL, NULL, NULL, 1),
(71, NULL, 'ETHIOPIA', 'ET', 'ET', NULL, NULL, NULL, 1),
(72, NULL, 'FALKLAND ISLANDS (MALVINAS)', 'FK', 'FK', NULL, NULL, NULL, 1),
(73, NULL, 'FAROE ISLANDS', 'FO', 'FO', NULL, NULL, NULL, 1),
(74, NULL, 'FIJI', 'FJ', 'FJ', NULL, NULL, NULL, 1),
(75, NULL, 'FINLAND', 'FI', 'FI', NULL, NULL, NULL, 1),
(76, NULL, 'FRANCE', 'FR', 'FR', NULL, NULL, NULL, 1),
(77, NULL, 'FRENCH GUIANA', 'GF', 'GF', NULL, NULL, NULL, 1),
(78, NULL, 'FRENCH POLYNESIA', 'PF', 'PF', NULL, NULL, NULL, 1),
(79, NULL, 'FRENCH SOUTHERN TERRITORIES', 'TF', 'TF', NULL, NULL, NULL, 1),
(80, NULL, 'GABON', 'GA', 'GA', NULL, NULL, NULL, 1),
(81, NULL, 'GAMBIA', 'GM', 'GM', NULL, NULL, NULL, 1),
(82, NULL, 'GEORGIA', 'GE', 'GE', NULL, NULL, NULL, 1),
(83, NULL, 'GERMANY', 'DE', 'DE', NULL, NULL, NULL, 1),
(84, NULL, 'GHANA', 'GH', 'GH', NULL, NULL, NULL, 1),
(85, NULL, 'GIBRALTAR', 'GI', 'GI', NULL, NULL, NULL, 1),
(86, NULL, 'GREECE', 'GR', 'GR', NULL, NULL, NULL, 1),
(87, NULL, 'GREENLAND', 'GL', 'GL', NULL, NULL, NULL, 1),
(88, NULL, 'GRENADA', 'GD', 'GD', NULL, NULL, NULL, 1),
(89, NULL, 'GUADELOUPE', 'GP', 'GP', NULL, NULL, NULL, 1),
(90, NULL, 'GUAM', 'GU', 'GU', NULL, NULL, NULL, 1),
(91, NULL, 'GUATEMALA', 'GT', 'GT', NULL, NULL, NULL, 1),
(92, NULL, 'GUERNSEY', 'GG', 'GG', NULL, NULL, NULL, 1),
(93, NULL, 'GUINEA', 'GN', 'GN', NULL, NULL, NULL, 1),
(94, NULL, 'GUINEA-BISSAU', 'GW', 'GW', NULL, NULL, NULL, 1),
(95, NULL, 'GUYANA', 'GY', 'GY', NULL, NULL, NULL, 1),
(96, NULL, 'HAITI', 'HT', 'HT', NULL, NULL, NULL, 1),
(97, NULL, 'HEARD ISLAND AND MCDONALD ISLANDS', 'HM', 'HM', NULL, NULL, NULL, 1),
(98, NULL, 'HOLY SEE (VATICAN CITY STATE)', 'VA', 'VA', NULL, NULL, NULL, 1),
(99, NULL, 'HONDURAS', 'HN', 'HN', NULL, NULL, NULL, 1),
(100, NULL, 'HONG KONG', 'HK', 'HK', NULL, NULL, NULL, 1),
(101, NULL, 'HUNGARY', 'HU', 'HU', NULL, NULL, NULL, 1),
(102, NULL, 'ICELAND', 'IS', 'IS', NULL, NULL, NULL, 1),
(103, NULL, 'INDIA', 'IN', 'IN', NULL, NULL, NULL, 1),
(104, NULL, 'INDONESIA', 'ID', 'ID', NULL, NULL, NULL, 1),
(105, NULL, 'IRAN, ISLAMIC REPUBLIC OF', 'IR', 'IR', NULL, NULL, NULL, 1),
(106, NULL, 'IRAQ', 'IQ', 'IQ', NULL, NULL, NULL, 1),
(107, NULL, 'IRELAND', 'IE', 'IE', NULL, NULL, NULL, 1),
(108, NULL, 'ISLE OF MAN', 'IM', 'IM', NULL, NULL, NULL, 1),
(109, NULL, 'ISRAEL', 'IL', 'IL', NULL, NULL, NULL, 1),
(110, NULL, 'ITALY', 'IT', 'IT', NULL, NULL, NULL, 1),
(111, NULL, 'JAMAICA', 'JM', 'JM', NULL, NULL, NULL, 1),
(112, NULL, 'JAPAN', 'JP', 'JP', NULL, NULL, NULL, 1),
(113, NULL, 'JERSEY', 'JE', 'JE', NULL, NULL, NULL, 1),
(114, NULL, 'JORDAN', 'JO', 'JO', NULL, NULL, NULL, 1),
(115, NULL, 'KAZAKHSTAN', 'KZ', 'KZ', NULL, NULL, NULL, 1),
(116, NULL, 'KENYA', 'KE', 'KE', NULL, NULL, NULL, 1),
(117, NULL, 'KIRIBATI', 'KI', 'KI', NULL, NULL, NULL, 1),
(118, NULL, 'KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF', 'KP', 'KP', NULL, NULL, NULL, 1),
(119, NULL, 'KOREA, REPUBLIC OF', 'KR', 'KR', NULL, NULL, NULL, 1),
(120, NULL, 'KUWAIT', 'KW', 'KW', NULL, NULL, NULL, 1),
(121, NULL, 'KYRGYZSTAN', 'KG', 'KG', NULL, NULL, NULL, 1),
(122, NULL, 'LAO PEOPLE''S DEMOCRATIC REPUBLIC', 'LA', 'LA', NULL, NULL, NULL, 1),
(123, NULL, 'LATVIA', 'LV', 'LV', NULL, NULL, NULL, 1),
(124, NULL, 'LEBANON', 'LB', 'LB', NULL, NULL, NULL, 1),
(125, NULL, 'LESOTHO', 'LS', 'LS', NULL, NULL, NULL, 1),
(126, NULL, 'LIBERIA', 'LR', 'LR', NULL, NULL, NULL, 1),
(127, NULL, 'LIBYA', 'LY', 'LY', NULL, NULL, NULL, 1),
(128, NULL, 'LIECHTENSTEIN', 'LI', 'LI', NULL, NULL, NULL, 1),
(129, NULL, 'LITHUANIA', 'LT', 'LT', NULL, NULL, NULL, 1),
(130, NULL, 'LUXEMBOURG', 'LU', 'LU', NULL, NULL, NULL, 1),
(131, NULL, 'MACAO', 'MO', 'MO', NULL, NULL, NULL, 1),
(132, NULL, 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'MK', 'MK', NULL, NULL, NULL, 1),
(133, NULL, 'MADAGASCAR', 'MG', 'MG', NULL, NULL, NULL, 1),
(134, NULL, 'MALAWI', 'MW', 'MW', NULL, NULL, NULL, 1),
(135, NULL, 'MALAYSIA', 'MY', 'MY', NULL, NULL, NULL, 1),
(136, NULL, 'MALDIVES', 'MV', 'MV', NULL, NULL, NULL, 1),
(137, NULL, 'MALI', 'ML', 'ML', NULL, NULL, NULL, 1),
(138, NULL, 'MALTA', 'MT', 'MT', NULL, NULL, NULL, 1),
(139, NULL, 'MARSHALL ISLANDS', 'MH', 'MH', NULL, NULL, NULL, 1),
(140, NULL, 'MARTINIQUE', 'MQ', 'MQ', NULL, NULL, NULL, 1),
(141, NULL, 'MAURITANIA', 'MR', 'MR', NULL, NULL, NULL, 1),
(142, NULL, 'MAURITIUS', 'MU', 'MU', NULL, NULL, NULL, 1),
(143, NULL, 'MAYOTTE', 'YT', 'YT', NULL, NULL, NULL, 1),
(144, NULL, 'MEXICO', 'MX', 'MX', NULL, NULL, NULL, 1),
(145, NULL, 'MICRONESIA, FEDERATED STATES OF', 'FM', 'FM', NULL, NULL, NULL, 1),
(146, NULL, 'MOLDOVA, REPUBLIC OF', 'MD', 'MD', NULL, NULL, NULL, 1),
(147, NULL, 'MONACO', 'MC', 'MC', NULL, NULL, NULL, 1),
(148, NULL, 'MONGOLIA', 'MN', 'MN', NULL, NULL, NULL, 1),
(149, NULL, 'MONTENEGRO', 'ME', 'ME', NULL, NULL, NULL, 1),
(150, NULL, 'MONTSERRAT', 'MS', 'MS', NULL, NULL, NULL, 1),
(151, NULL, 'MOROCCO', 'MA', 'MA', NULL, NULL, NULL, 1),
(152, NULL, 'MOZAMBIQUE', 'MZ', 'MZ', NULL, NULL, NULL, 1),
(153, NULL, 'MYANMAR', 'MM', 'MM', NULL, NULL, NULL, 1),
(154, NULL, 'NAMIBIA', 'NA', 'NA', NULL, NULL, NULL, 1),
(155, NULL, 'NAURU', 'NR', 'NR', NULL, NULL, NULL, 1),
(156, NULL, 'NEPAL', 'NP', 'NP', NULL, NULL, NULL, 1),
(157, NULL, 'NETHERLANDS', 'NL', 'NL', NULL, NULL, NULL, 1),
(158, NULL, 'NEW CALEDONIA', 'NC', 'NC', NULL, NULL, NULL, 1),
(159, NULL, 'NEW ZEALAND', 'NZ', 'NZ', NULL, NULL, NULL, 1),
(160, NULL, 'NICARAGUA', 'NI', 'NI', NULL, NULL, NULL, 1),
(161, NULL, 'NIGER', 'NE', 'NE', NULL, NULL, NULL, 1),
(162, NULL, 'NIGERIA', 'NG', 'NG', NULL, NULL, NULL, 1),
(163, NULL, 'NIUE', 'NU', 'NU', NULL, NULL, NULL, 1),
(164, NULL, 'NORFOLK ISLAND', 'NF', 'NF', NULL, NULL, NULL, 1),
(165, NULL, 'NORTHERN MARIANA ISLANDS', 'MP', 'MP', NULL, NULL, NULL, 1),
(166, NULL, 'NORWAY', 'NO', 'NO', NULL, NULL, NULL, 1),
(167, NULL, 'OMAN', 'OM', 'OM', NULL, NULL, NULL, 1),
(168, NULL, 'PAKISTAN', 'PK', 'PK', NULL, NULL, NULL, 1),
(169, NULL, 'PALAU', 'PW', 'PW', NULL, NULL, NULL, 1),
(170, NULL, 'PALESTINIAN TERRITORY, OCCUPIED', 'PS', 'PS', NULL, NULL, NULL, 1),
(171, NULL, 'PANAMA', 'PA', 'PA', NULL, NULL, NULL, 1),
(172, NULL, 'PAPUA NEW GUINEA', 'PG', 'PG', NULL, NULL, NULL, 1),
(173, NULL, 'PARAGUAY', 'PY', 'PY', NULL, NULL, NULL, 1),
(174, NULL, 'PERU', 'PE', 'PE', NULL, NULL, NULL, 1),
(175, NULL, 'PHILIPPINES', 'PH', 'PH', NULL, NULL, NULL, 1),
(176, NULL, 'PITCAIRN', 'PN', 'PN', NULL, NULL, NULL, 1),
(177, NULL, 'POLAND', 'PL', 'PL', NULL, NULL, NULL, 1),
(178, NULL, 'PORTUGAL', 'PT', 'PT', NULL, NULL, NULL, 1),
(179, NULL, 'PUERTO RICO', 'PR', 'PR', NULL, NULL, NULL, 1),
(180, NULL, 'QATAR', 'QA', 'QA', NULL, NULL, NULL, 1),
(181, NULL, 'RÉUNION', 'RE', 'RE', NULL, NULL, NULL, 1),
(182, NULL, 'ROMANIA', 'RO', 'RO', NULL, NULL, NULL, 1),
(183, NULL, 'RUSSIAN FEDERATION', 'RU', 'RU', NULL, NULL, NULL, 1),
(184, NULL, 'RWANDA', 'RW', 'RW', NULL, NULL, NULL, 1),
(185, NULL, 'SAINT BARTHÉLEMY', 'BL', 'BL', NULL, NULL, NULL, 1),
(186, NULL, 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA', 'SH', 'SH', NULL, NULL, NULL, 1),
(187, NULL, 'SAINT KITTS AND NEVIS', 'KN', 'KN', NULL, NULL, NULL, 1),
(188, NULL, 'SAINT LUCIA', 'LC', 'LC', NULL, NULL, NULL, 1),
(189, NULL, 'SAINT MARTIN (FRENCH PART)', 'MF', 'MF', NULL, NULL, NULL, 1),
(190, NULL, 'SAINT PIERRE AND MIQUELON', 'PM', 'PM', NULL, NULL, NULL, 1),
(191, NULL, 'SAINT VINCENT AND THE GRENADINES', 'VC', 'VC', NULL, NULL, NULL, 1),
(192, NULL, 'SAMOA', 'WS', 'WS', NULL, NULL, NULL, 1),
(193, NULL, 'SAN MARINO', 'SM', 'SM', NULL, NULL, NULL, 1),
(194, NULL, 'SAO TOME AND PRINCIPE', 'ST', 'ST', NULL, NULL, NULL, 1),
(195, NULL, 'SAUDI ARABIA', 'SA', 'SA', NULL, NULL, NULL, 1),
(196, NULL, 'SENEGAL', 'SN', 'SN', NULL, NULL, NULL, 1),
(197, NULL, 'SERBIA', 'RS', 'RS', NULL, NULL, NULL, 1),
(198, NULL, 'SEYCHELLES', 'SC', 'SC', NULL, NULL, NULL, 1),
(199, NULL, 'SIERRA LEONE', 'SL', 'SL', NULL, NULL, NULL, 1),
(200, NULL, 'SINGAPORE', 'SG', 'SG', NULL, NULL, NULL, 1),
(201, NULL, 'SINT MAARTEN (DUTCH PART)', 'SX', 'SX', NULL, NULL, NULL, 1),
(202, NULL, 'SLOVAKIA', 'SK', 'SK', NULL, NULL, NULL, 1),
(203, NULL, 'SLOVENIA', 'SI', 'SI', NULL, NULL, NULL, 1),
(204, NULL, 'SOLOMON ISLANDS', 'SB', 'SB', NULL, NULL, NULL, 1),
(205, NULL, 'SOMALIA', 'SO', 'SO', NULL, NULL, NULL, 1),
(206, NULL, 'SOUTH AFRICA', 'ZA', 'ZA', NULL, NULL, NULL, 1),
(207, NULL, 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'GS', 'GS', NULL, NULL, NULL, 1),
(208, NULL, 'SOUTH SUDAN', 'SS', 'SS', NULL, NULL, NULL, 1),
(209, NULL, 'SPAIN', 'ES', 'ES', NULL, NULL, NULL, 1),
(210, NULL, 'SRI LANKA', 'LK', 'LK', NULL, NULL, NULL, 1),
(211, NULL, 'SUDAN', 'SD', 'SD', NULL, NULL, NULL, 1),
(212, NULL, 'SURINAME', 'SR', 'SR', NULL, NULL, NULL, 1),
(213, NULL, 'SVALBARD AND JAN MAYEN', 'SJ', 'SJ', NULL, NULL, NULL, 1),
(214, NULL, 'SWAZILAND', 'SZ', 'SZ', NULL, NULL, NULL, 1),
(215, NULL, 'SWEDEN', 'SE', 'SE', NULL, NULL, NULL, 1),
(216, NULL, 'SWITZERLAND', 'CH', 'CH', NULL, NULL, NULL, 1),
(217, NULL, 'SYRIAN ARAB REPUBLIC', 'SY', 'SY', NULL, NULL, NULL, 1),
(218, NULL, 'TAIWAN, PROVINCE OF CHINA', 'TW', 'TW', NULL, NULL, NULL, 1),
(219, NULL, 'TAJIKISTAN', 'TJ', 'TJ', NULL, NULL, NULL, 1),
(220, NULL, 'TANZANIA, UNITED REPUBLIC OF', 'TZ', 'TZ', NULL, NULL, NULL, 1),
(221, NULL, 'THAILAND', 'TH', 'TH', NULL, NULL, NULL, 1),
(222, NULL, 'TIMOR-LESTE', 'TL', 'TL', NULL, NULL, NULL, 1),
(223, NULL, 'TOGO', 'TG', 'TG', NULL, NULL, NULL, 1),
(224, NULL, 'TOKELAU', 'TK', 'TK', NULL, NULL, NULL, 1),
(225, NULL, 'TONGA', 'TO', 'TO', NULL, NULL, NULL, 1),
(226, NULL, 'TRINIDAD AND TOBAGO', 'TT', 'TT', NULL, NULL, NULL, 1),
(227, NULL, 'TUNISIA', 'TN', 'TN', NULL, NULL, NULL, 1),
(228, NULL, 'TURKEY', 'TR', 'TR', NULL, NULL, NULL, 1),
(229, NULL, 'TURKMENISTAN', 'TM', 'TM', NULL, NULL, NULL, 1),
(230, NULL, 'TURKS AND CAICOS ISLANDS', 'TC', 'TC', NULL, NULL, NULL, 1),
(231, NULL, 'TUVALU', 'TV', 'TV', NULL, NULL, NULL, 1),
(232, NULL, 'UGANDA', 'UG', 'UG', NULL, NULL, NULL, 1),
(233, NULL, 'UKRAINE', 'UA', 'UA', NULL, NULL, NULL, 1),
(234, NULL, 'UNITED ARAB EMIRATES', 'AE', 'AE', NULL, NULL, NULL, 1),
(235, NULL, 'UNITED KINGDOM', 'GB', 'GB', NULL, NULL, NULL, 1),
(236, NULL, 'UNITED STATES', 'US', 'US', NULL, NULL, NULL, 1),
(237, NULL, 'UNITED STATES MINOR OUTLYING ISLANDS', 'UM', 'UM', NULL, NULL, NULL, 1),
(238, NULL, 'URUGUAY', 'UY', 'UY', NULL, NULL, NULL, 1),
(239, NULL, 'UZBEKISTAN', 'UZ', 'UZ', NULL, NULL, NULL, 1),
(240, NULL, 'VANUATU', 'VU', 'VU', NULL, NULL, NULL, 1),
(241, NULL, 'VENEZUELA, BOLIVARIAN REPUBLIC OF', 'VE', 'VE', NULL, NULL, NULL, 1),
(242, NULL, 'VIET NAM', 'VN', 'VN', NULL, NULL, NULL, 1),
(243, NULL, 'VIRGIN ISLANDS, BRITISH', 'VG', 'VG', NULL, NULL, NULL, 1),
(244, NULL, 'VIRGIN ISLANDS, U.S.', 'VI', 'VI', NULL, NULL, NULL, 1),
(245, NULL, 'WALLIS AND FUTUNA', 'WF', 'WF', NULL, NULL, NULL, 1),
(246, NULL, 'WESTERN SAHARA', 'EH', 'EH', NULL, NULL, NULL, 1),
(247, NULL, 'YEMEN', 'YE', 'YE', NULL, NULL, NULL, 1),
(248, NULL, 'ZAMBIA', 'ZM', 'ZM', NULL, NULL, NULL, 1),
(249, NULL, 'ZIMBABWE', 'ZW', 'ZW', NULL, NULL, NULL, 1),
(250, 236, 'ALABAMA', 'AL', 'AL', NULL, NULL, NULL, 1),
(251, 236, 'ALASKA', 'AK', 'AK', NULL, NULL, NULL, 1),
(252, 236, 'AMERICAN SAMOA', 'AS', 'AS', NULL, NULL, NULL, 1),
(253, 236, 'ARIZONA', 'AZ', 'AZ', NULL, NULL, NULL, 1),
(254, 236, 'ARKANSAS', 'AR', 'AR', NULL, NULL, NULL, 1),
(255, 236, 'CALIFORNIA', 'CA', 'CA', NULL, NULL, NULL, 1),
(256, 236, 'COLORADO', 'CO', 'CO', NULL, NULL, NULL, 1),
(257, 236, 'CONNECTICUT', 'CT', 'CT', NULL, NULL, NULL, 1),
(258, 236, 'DELAWARE', 'DE', 'DE', NULL, NULL, NULL, 1),
(259, 236, 'DISTRICT OF COLUMBIA', 'DC', 'DC', NULL, NULL, NULL, 1),
(260, 236, 'FEDERATED STATES OF MICRONESIA', 'FM', 'FM', NULL, NULL, NULL, 1),
(261, 236, 'FLORIDA', 'FL', 'FL', NULL, NULL, NULL, 1),
(262, 236, 'GEORGIA', 'GA', 'GA', NULL, NULL, NULL, 1),
(263, 236, 'GUAM', 'GU', 'GU', NULL, NULL, NULL, 1),
(264, 236, 'HAWAII', 'HI', 'HI', NULL, NULL, NULL, 1),
(265, 236, 'IDAHO', 'ID', 'ID', NULL, NULL, NULL, 1),
(266, 236, 'ILLINOIS', 'IL', 'IL', NULL, NULL, NULL, 1),
(267, 236, 'INDIANA', 'IN', 'IN', NULL, NULL, NULL, 1),
(268, 236, 'IOWA', 'IA', 'IA', NULL, NULL, NULL, 1),
(269, 236, 'KANSAS', 'KS', 'KS', NULL, NULL, NULL, 1),
(270, 236, 'KENTUCKY', 'KY', 'KY', NULL, NULL, NULL, 1),
(271, 236, 'LOUISIANA', 'LA', 'LA', NULL, NULL, NULL, 1),
(272, 236, 'MAINE', 'ME', 'ME', NULL, NULL, NULL, 1),
(273, 236, 'MARSHALL ISLANDS', 'MH', 'MH', NULL, NULL, NULL, 1),
(274, 236, 'MARYLAND', 'MD', 'MD', NULL, NULL, NULL, 1),
(275, 236, 'MASSACHUSETTS', 'MA', 'MA', NULL, NULL, NULL, 1),
(276, 236, 'MICHIGAN', 'MI', 'MI', NULL, NULL, NULL, 1),
(277, 236, 'MINNESOTA', 'MN', 'MN', NULL, NULL, NULL, 1),
(278, 236, 'MISSISSIPPI', 'MS', 'MS', NULL, NULL, NULL, 1),
(279, 236, 'MISSOURI', 'MO', 'MO', NULL, NULL, NULL, 1),
(280, 236, 'MONTANA', 'MT', 'MT', NULL, NULL, NULL, 1),
(281, 236, 'NEBRASKA', 'NE', 'NE', NULL, NULL, NULL, 1),
(282, 236, 'NEVADA', 'NV', 'NV', NULL, NULL, NULL, 1),
(283, 236, 'NEW HAMPSHIRE', 'NH', 'NH', NULL, NULL, NULL, 1),
(284, 236, 'NEW JERSEY', 'NJ', 'NJ', NULL, NULL, NULL, 1),
(285, 236, 'NEW MEXICO', 'NM', 'NM', NULL, NULL, NULL, 1),
(286, 236, 'NEW YORK', 'NY', 'NY', NULL, NULL, NULL, 1),
(287, 236, 'NORTH CAROLINA', 'NC', 'NC', NULL, NULL, NULL, 1),
(288, 236, 'NORTH DAKOTA', 'ND', 'ND', NULL, NULL, NULL, 1),
(289, 236, 'NORTHERN MARIANA ISLANDS', 'MP', 'MP', NULL, NULL, NULL, 1),
(290, 236, 'OHIO', 'OH', 'OH', NULL, NULL, NULL, 1),
(291, 236, 'OKLAHOMA', 'OK', 'OK', NULL, NULL, NULL, 1),
(292, 236, 'OREGON', 'OR', 'OR', NULL, NULL, NULL, 1),
(293, 236, 'PALAU', 'PW', 'PW', NULL, NULL, NULL, 1),
(294, 236, 'PENNSYLVANIA', 'PA', 'PA', NULL, NULL, NULL, 1),
(295, 236, 'PUERTO RICO', 'PR', 'PR', NULL, NULL, NULL, 1),
(296, 236, 'RHODE ISLAND', 'RI', 'RI', NULL, NULL, NULL, 1),
(297, 236, 'SOUTH CAROLINA', 'SC', 'SC', NULL, NULL, NULL, 1),
(298, 236, 'SOUTH DAKOTA', 'SD', 'SD', NULL, NULL, NULL, 1),
(299, 236, 'TENNESSEE', 'TN', 'TN', NULL, NULL, NULL, 1),
(300, 236, 'TEXAS', 'TX', 'TX', NULL, NULL, NULL, 1),
(301, 236, 'UTAH', 'UT', 'UT', NULL, NULL, NULL, 1),
(302, 236, 'VERMONT', 'VT', 'VT', NULL, NULL, NULL, 1),
(303, 236, 'VIRGIN ISLANDS', 'VI', 'VI', NULL, NULL, NULL, 1),
(304, 236, 'VIRGINIA', 'VA', 'VA', NULL, NULL, NULL, 1),
(305, 236, 'WASHINGTON', 'WA', 'WA', NULL, NULL, NULL, 1),
(306, 236, 'WEST VIRGINIA', 'WV', 'WV', NULL, NULL, NULL, 1),
(307, 236, 'WISCONSIN', 'WI', 'WI', NULL, NULL, NULL, 1),
(308, 236, 'WYOMING', 'WY', 'WY', NULL, NULL, NULL, 1),
(309, 236, 'ARMED FORCES AFRICA', 'AE', 'AE', NULL, NULL, NULL, 1),
(310, 236, 'ARMED FORCES AMERICAS', 'AA', 'AA', NULL, NULL, NULL, 1),
(311, 236, 'ARMED FORCES CANADA', 'AE', 'AE', NULL, NULL, NULL, 1),
(312, 236, 'ARMED FORCES EUROPE', 'AE', 'AE', NULL, NULL, NULL, 1),
(313, 236, 'ARMED FORCES MIDDLE EAST', 'AE', 'AE', NULL, NULL, NULL, 1),
(314, 236, 'ARMED FORCES PACIFIC', 'AP', 'AP', NULL, NULL, NULL, 1),
(315, 40, 'ALBERTA', 'AB', 'AB', NULL, NULL, NULL, 1),
(316, 40, 'BRITISH COLUMBIA', 'BC', 'BC', NULL, NULL, NULL, 1),
(317, 40, 'MANITOBA', 'MB', 'MB', NULL, NULL, NULL, 1),
(318, 40, 'NEW BRUNSWICK', 'NB', 'NB', NULL, NULL, NULL, 1),
(319, 40, 'NEWFOUNDLAND AND LABRADOR', 'NL', 'NL', NULL, NULL, NULL, 1),
(320, 40, 'NORTHWEST TERRITORIES', 'NT', 'NT', NULL, NULL, NULL, 1),
(321, 40, 'NOVA SCOTIA', 'NS', 'NS', NULL, NULL, NULL, 1),
(322, 40, 'NUNAVUT', 'NU', 'NU', NULL, NULL, NULL, 1),
(323, 40, 'ONTARIO', 'ON', 'ON', NULL, NULL, NULL, 1),
(324, 40, 'PRINCE EDWARD ISLAND', 'PE', 'PE', NULL, NULL, NULL, 1),
(325, 40, 'QUEBEC', 'QC', 'QC', NULL, NULL, NULL, 1),
(326, 40, 'SASKATCHEWAN', 'SK', 'SK', NULL, NULL, NULL, 1),
(327, 40, 'YUKON', 'YT', 'YT', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `s2_order_sessions`
--

CREATE TABLE IF NOT EXISTS `s2_order_sessions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `key` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Session key (64 characters, unique value).',
  `phase` enum('cart','checkout','checkout_validation_failure','checkout_decline_failure','checkout_offsite','checkout_success') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'cart' COMMENT 'Current phase of this session.',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user ID (if applicable).',
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Customer IP address.',
  `user_passtag_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user passtag ID (if applicable).',
  `time_started` int(10) unsigned NOT NULL COMMENT 'Time this session started.',
  `time_last_active` int(10) unsigned NOT NULL COMMENT 'Time this session was last active.',
  `time_completed` int(10) unsigned DEFAULT NULL COMMENT 'Time completed. Defaults to NULL (incomplete).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_session` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_order_session_items`
--

CREATE TABLE IF NOT EXISTS `s2_order_session_items` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `order_session_id` bigint(20) unsigned NOT NULL COMMENT 'Related order session ID.',
  `order_session_item_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related order session item type ID.',
  `item_id` bigint(20) unsigned NOT NULL COMMENT 'Which related item ID? Depending on the item type.',
  `quantity` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Item quantity.',
  `http_referer` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The referring URL, which added this item to the session.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_order_session_item` (`order_session_id`,`order_session_item_type_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_order_session_item_meta`
--

CREATE TABLE IF NOT EXISTS `s2_order_session_item_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `order_session_item_id` bigint(20) unsigned NOT NULL COMMENT 'Related order session item ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_order_session_item_meta` (`order_session_item_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_order_session_item_types`
--

CREATE TABLE IF NOT EXISTS `s2_order_session_item_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Item type.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this order session item type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_order_session_item_type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `s2_order_session_item_types`
--

INSERT INTO `s2_order_session_item_types` (`ID`, `type`, `label`) VALUES
(1, 'passtag', 'Passtag'),
(2, 'coupon', 'Coupon');

-- --------------------------------------------------------

--
-- Table structure for table `s2_order_session_meta`
--

CREATE TABLE IF NOT EXISTS `s2_order_session_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `order_session_id` bigint(20) unsigned NOT NULL COMMENT 'Related order session ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_order_session_meta` (`order_session_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_passtags`
--

CREATE TABLE IF NOT EXISTS `s2_passtags` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Internal name (for administrative reference).',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this passtag.',
  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Additional description for this passtag.',
  `redirects_to` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'If access is denied, redirect to ID, or URL.',
  `time_starts_offset` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Offset in seconds (defaults to 0, indicating immediate access).',
  `time_stops_offset` int(10) NOT NULL DEFAULT '-1' COMMENT 'Offset in seconds (-1 indicates infinity, access is ongoing; e.g. lifetime access). This should define ONE term only. If a passtag is sold under a recurring plan; each payment on the plan will extend the time again; else it will expire automatically.',
  `sales_limit` int(10) NOT NULL DEFAULT '-1' COMMENT 'How many times can this passtag be sold? Defaults to -1, can be sold an infinite number of times.',
  `shareable_limit` int(10) NOT NULL DEFAULT '0' COMMENT 'Can parents share (if so, max share times). Set this to -1 for infinite sharing. Defaults to 0 (NOT shareable).',
  `uses_limit` int(10) NOT NULL DEFAULT '-1' COMMENT 'Max uses (defaults to -1, allowing infinite uses).',
  `uses_limit_term` int(10) NOT NULL DEFAULT '-1' COMMENT 'Max uses within this time frame (in seconds). Defaults to -1, no time frame. A value of -1 or 0 indicates no time frame.',
  `uses_limit_recurs` int(10) NOT NULL DEFAULT '-1' COMMENT 'Does the term recur (if so, how many times)? Defaults to -1, the term recurs indefinitely. A value of 0 indicates it does NOT recur. A value > 0 indicates a fixed number of recurrences.',
  `uses_limit_rolls_over` int(1) NOT NULL DEFAULT '0' COMMENT 'Do unclaimed uses roll over (i.e. are they automatically added to any future/recurring terms)? Defaults to 0 (does NOT roll over). A value of 1 indicates they do. A value of -1 indicates that uses roll over into terms following even the last term.',
  `uses_limit_unique` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Count unique uses only? See `identifier` column in `user_passtag_log` table for further details. If this is enabled, we count unique identifiers only. In other words, we do NOT count repeat uses of the same content.',
  `ips_limit` int(10) NOT NULL DEFAULT '5' COMMENT 'Max IPs (defaults to a max of 5 unique IPs). A value of -1 indicates infinite IPs are allowed. A value of 0 (although strange) would indicate nobody is allowed.',
  `ips_limit_term` int(10) NOT NULL DEFAULT '2592000' COMMENT 'Max IPs within this time frame (in seconds). Defaults to 2592000 (30 days). A value of -1 or 0 indicates no time frame.',
  `ips_limit_recurs` int(10) NOT NULL DEFAULT '-1' COMMENT 'Does the term recur (if so, how many times)? Defaults to -1, the term recurs indefinitely. A value of 0 indicates it does NOT recur. A value > 0 indicates a fixed number of recurrences.',
  `ips_limit_rolls_over` int(1) NOT NULL DEFAULT '0' COMMENT 'Do unused IPs roll over (i.e. are they automatically added to any future/recurring terms)? Defaults to 0 (does NOT roll over). A value of 1 indicates they do. A value of -1 indicates they roll over into terms following even the last term.',
  `taxable` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Is this passtag taxable?',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Status of this passtag.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_passtag` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_description` (`name`,`label`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_passtag_relationships`
--

CREATE TABLE IF NOT EXISTS `s2_passtag_relationships` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `parent_passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Parent passtag ID.',
  `child_passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Child passtag ID.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_passtag_relationship` (`parent_passtag_id`,`child_passtag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_passtag_restrictions`
--

CREATE TABLE IF NOT EXISTS `s2_passtag_restrictions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Related passtag ID.',
  `restriction_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related restriction type ID.',
  `behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Related behavior type ID. Defaults to 1 (default type); indicating the default behavior associated with the restriction type.',
  `restricts` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Restricts what? Depending on type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_passtag_restriction` (`passtag_id`,`restriction_type_id`,`restricts`),
  FULLTEXT KEY `ft_searchable_restricts` (`restricts`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_passtag_wp_caps`
--

CREATE TABLE IF NOT EXISTS `s2_passtag_wp_caps` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Related passtag ID.',
  `wp_cap` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'WordPress® capability.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_passtag_wp_cap` (`passtag_id`,`wp_cap`),
  FULLTEXT KEY `ft_searchable_wp_cap` (`wp_cap`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_fields`
--

CREATE TABLE IF NOT EXISTS `s2_profile_fields` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `profile_field_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field type ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Internal name (for administrative reference).',
  `label` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Profile field label (HTML allowed).',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Display order (and default tabindex).',
  `require` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Require this profile field?',
  `unique` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Must this field value be unique (across all users)?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field` (`name`),
  FULLTEXT KEY `ft_searchable_name_label` (`name`,`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_conversions`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_conversions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `profile_field_conversion_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field conversion type ID.',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_conversion_types`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_conversion_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of conversion to apply.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this profile field conversion type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_conversion_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `s2_profile_field_conversion_types`
--

INSERT INTO `s2_profile_field_conversion_types` (`ID`, `type`, `label`) VALUES
(1, 'uppercase', 'Force Uppercase'),
(2, 'lowercase', 'Force Lowercase'),
(3, 'ucfirst', 'Force Uppercase (First Letter)'),
(4, 'ucwords', 'Force Uppercase Words (Title Case)');

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_meta`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_meta` (`profile_field_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_permissions`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_permissions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `wp_cap` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Permission for a specific WP capability.',
  `context` enum('registration','profile_updates','profile_views') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'registration' COMMENT 'Permission applies in which context.',
  `read_access` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Read access for this profile field (boolean).',
  `write_access` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Write access for this profile field (boolean).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_permission` (`profile_field_id`,`wp_cap`,`context`),
  FULLTEXT KEY `ft_searchable_wp_cap` (`wp_cap`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_types`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Profile field type.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this profile field type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=25 ;

--
-- Dumping data for table `s2_profile_field_types`
--

INSERT INTO `s2_profile_field_types` (`ID`, `type`, `label`) VALUES
(1, 'tel', 'Telephone Input'),
(2, 'url', 'URL Input'),
(3, 'text', 'Text Input'),
(4, 'file', 'File Upload(s)'),
(5, 'email', 'Email Address'),
(6, 'number', 'Numeric Input'),
(7, 'search', 'Search Input'),
(8, 'password', 'Password Input'),
(9, 'color', 'Color Input/Picker'),
(10, 'range', 'Range Selector'),
(11, 'date', 'Date Input'),
(12, 'datetime', 'Date/Time Input'),
(13, 'datetime-local', 'Local Date/Time Input'),
(14, 'radio', 'Radio Button'),
(15, 'radios', 'Radio Buttons'),
(16, 'checkbox', 'Checkbox'),
(17, 'checkboxes', 'Checkboxes'),
(18, 'image', 'Image Button'),
(19, 'button', 'Button'),
(20, 'reset', 'Reset Button'),
(21, 'submit', 'Submit Button'),
(22, 'select', 'Select Menu'),
(23, 'textarea', 'Text Input (Multiline)'),
(24, 'hidden', 'Hidden Input');

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_validations`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_validations` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `profile_field_validation_pattern_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field validation pattern ID.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_validation` (`profile_field_id`,`profile_field_validation_pattern_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_validation_patterns`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_validation_patterns` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name for this profile field validation pattern.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this profile field validation pattern.',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description of requirements.',
  `regex_php` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'PHP regex pattern.',
  `regex_js` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'JavaScript regex pattern.',
  `minimum` decimal(10,2) DEFAULT NULL COMMENT 'Minimum numeric value, file size, string length, array length; for this pattern. Defaults to NULL (no minimum).',
  `maximum` decimal(10,2) DEFAULT NULL COMMENT 'Maximum numeric value, file size, string length, array length; for this pattern. Defaults to NULL (no maximum).',
  `min_max_type` enum('numeric_value','file_size','string_length','array_length') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The way in which a minimum/maximum value is validated.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_profile_field_validation_pattern` (`name`),
  FULLTEXT KEY `ft_searchable_name_label_description` (`name`,`label`,`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

--
-- Dumping data for table `s2_profile_field_validation_patterns`
--

INSERT INTO `s2_profile_field_validation_patterns` (`ID`, `name`, `label`, `description`, `regex_php`, `regex_js`, `minimum`, `maximum`, `min_max_type`) VALUES
(1, 'any', 'Anything', 'Anything goes.', '/^.+$/s', '/^[\\s\\S]+$/', NULL, NULL, NULL),
(2, 'integer', 'Integer', 'An integer value (e.g. no decimal point).', '/^\\-?[0-9]+$/', '/^\\-?[0-9]+$/', NULL, NULL, NULL),
(3, 'float', 'Float/Decimal', 'A float/decimal value.', '/^\\-?(?:[0-9]*\\.[0-9]+|[0-9]+)$/', '/^\\-?(?:[0-9]*\\.[0-9]+|[0-9]+)$/', NULL, NULL, NULL),
(4, 'numeric', 'Numeric', 'Any numeric value.', '/^\\-?(?:[0-9]*\\.[0-9]+|[0-9]+)$/', '/^\\-?(?:[0-9]*\\.[0-9]+|[0-9]+)$/', NULL, NULL, NULL),
(5, 'alphabetic', 'Alphabetic', 'Alphabetic characters only.', '/^[a-zA-Z]+$/', '/^[a-zA-Z]+$/', NULL, NULL, NULL),
(6, 'alphabetic_punctuation', 'Alphabetic + Allow Punctuation', 'Alphabetic characters and/or punctuation only.', '/^[a-zA-Z_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-]+$/', '/^[a-zA-Z_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-]+$/', NULL, NULL, NULL),
(7, 'alphabetic_whitespace', 'Alphabetic + Allow Whitespace', 'Alphabetic characters and/or whitespace only.', '/^[a-zA-Z\\s]+$/', '/^[a-zA-Z\\s]+$/', NULL, NULL, NULL),
(8, 'alphabetic_punctuation_whitespace', 'Alphabetic + Allow Punc./Whitespace', 'Alphabetic characters, punctuation and/or whitespace only.', '/^[a-zA-Z_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-\\s]+$/', '/^[a-zA-Z_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-\\s]+$/', NULL, NULL, NULL),
(9, 'alphanumeric', 'Alphanumeric', 'Alphanumeric characters only.', '/^[a-zA-Z0-9]+$/', '/^[a-zA-Z0-9]+$/', NULL, NULL, NULL),
(10, 'alphanumeric_punctuation', 'Alphanumeric + Allow Punctuation', 'Alphanumeric characters and/or punctuation only.', '/^[a-zA-Z0-9_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-]+$/', '/^[a-zA-Z0-9_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-]+$/', NULL, NULL, NULL),
(11, 'alphanumeric_whitespace', 'Alphanumeric + Allow Whitespace', 'Alphanumeric characters and/or whitespace only.', '/^[a-zA-Z0-9\\s]+$/', '/^[a-zA-Z0-9\\s]+$/', NULL, NULL, NULL),
(12, 'alphanumeric_punctuation_whitespace', 'Alphanumeric + Allow Punc./Whitespace', 'Alphanumeric characters, punctuation and/or whitespace only.', '/^[a-zA-Z0-9_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-\\s]+$/', '/^[a-zA-Z0-9_;,.~!@#$%&*(){}:?<>"''=+|\\\\\\/\\^[\\]\\-\\s]+$/', NULL, NULL, NULL),
(13, 'url', 'URL', 'A valid URL (starting with http://).', '/^(?:[a-zA-Z0-9]+\\:)?\\/\\/(?:[a-zA-Z0-9\\-_.~+%]+(?:\\:[a-zA-Z0-9\\-_.~+%]+)?@)?[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?(?:\\:[0-9]+)?(?:\\/(?!\\/)[a-zA-Z0-9\\-_.~+%]*)*(?:\\?(?:[a-zA-Z0-9\\-_.~+%]+(?:\\=[a-zA-Z0-9\\-_.~+%&]*)?)*)?(?:#[^\\s]*)?$/', '/^(?:[a-zA-Z0-9]+\\:)?\\/\\/(?:[a-zA-Z0-9\\-_.~+%]+(?:\\:[a-zA-Z0-9\\-_.~+%]+)?@)?[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?(?:\\:[0-9]+)?(?:\\/(?!\\/)[a-zA-Z0-9\\-_.~+%]*)*(?:\\?(?:[a-zA-Z0-9\\-_.~+%]+(?:\\=[a-zA-Z0-9\\-_.~+%&]*)?)*)?(?:#[^\\s]*)?$/', NULL, NULL, NULL),
(14, 'domain', 'Domain Name', 'A valid domain name.', '/^[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?$/', '/^[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?$/', NULL, NULL, NULL),
(15, 'email', 'Email Address', 'A valid email address.', '/^[a-zA-Z0-9_!#$%&*+=?`{}~|\\/\\^\\''\\-]+(?:\\.?[a-zA-Z0-9_!#$%&*+=?`{}~|\\/\\^\\''\\-]+)*@[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?$/', '/^[a-zA-Z0-9_!#$%&*+=?`{}~|\\/\\^\\''\\-]+(?:\\.?[a-zA-Z0-9_!#$%&*+=?`{}~|\\/\\^\\''\\-]+)*@[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*(?:\\.[a-zA-Z0-9]+(?:\\-*[a-zA-Z0-9]+)*)*(?:\\.[a-zA-Z][a-zA-Z0-9]+)?$/', NULL, NULL, NULL),
(16, 'us_zip', 'U.S. Zipcode', 'A valid U.S. zipcode.', '/^[0-9]{5}(?:\\-[0-9]{4})?$/', '/^[0-9]{5}(?:\\-[0-9]{4})?$/', NULL, NULL, NULL),
(17, 'ca_zip', 'Canadian Postal Code', 'A valid Canadian postal code.', '/^[a-zA-Z0-9]{3} ?[a-zA-Z0-9]{3}$/', '/^[a-zA-Z0-9]{3} ?[a-zA-Z0-9]{3}$/', NULL, NULL, NULL),
(18, 'date_dd/mm/yyyy', 'Date (format: DD/MM/YYYY)', 'A valid date (with format: DD/MM/YYYY).', '/^(?:0[1-9]|[12][0-9]|3[01])\\/(?:0[1-9]|1[012])\\/(?:19|20)\\d\\d$/', '/^(?:0[1-9]|[12][0-9]|3[01])\\/(?:0[1-9]|1[012])\\/(?:19|20)\\d\\d$/', NULL, NULL, NULL),
(19, 'date_mm/dd/yyyy', 'Date (format: MM/DD/YYYY)', 'A valid date (with format: MM/DD/YYYY).', '/^(?:0[1-9]|1[012])\\/(?:0[1-9]|[12][0-9]|3[01])\\/(?:19|20)\\d\\d$/', '/^(?:0[1-9]|1[012])\\/(?:0[1-9]|[12][0-9]|3[01])\\/(?:19|20)\\d\\d$/', NULL, NULL, NULL),
(20, 'date_yyyy-mm-dd', 'Date (format: YYYY-MM-DD)', 'A valid date (with format: YYYY-MM-DD).', '/^(?:19|20)\\d\\d-(?:0[1-9]|1[012])-(?:0[1-9]|[12][0-9]|3[01])$/', '/^(?:19|20)\\d\\d-(?:0[1-9]|1[012])-(?:0[1-9]|[12][0-9]|3[01])$/', NULL, NULL, NULL),
(21, 'us_phone', 'U.S. Phone #', 'A valid U.S. phone number.', '/^\\(?\\d{3}\\)?[. \\-]?\\d{3}[. \\-]?\\d{4}$/', '/^\\(?\\d{3}\\)?[. \\-]?\\d{3}[. \\-]?\\d{4}$/', NULL, NULL, NULL),
(22, 'phone', 'Any Phone #', 'A valid phone number.', '/^[0-9 .()\\-]+$/', '/^[0-9 .()\\-]+$/', NULL, NULL, NULL),
(23, 'password', 'Password', 'Six character minimum.', '/^.+$/s', '/^[\\s\\S]+$/', '6.00', NULL, 'string_length');

-- --------------------------------------------------------

--
-- Table structure for table `s2_profile_field_values`
--

CREATE TABLE IF NOT EXISTS `s2_profile_field_values` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `label` text COLLATE utf8_unicode_ci COMMENT 'Label for this value (if applicable).',
  `value` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Value.',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Order for this value.',
  `default` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Selected/checked/input by default?',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_label_value` (`label`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_restriction_types`
--

CREATE TABLE IF NOT EXISTS `s2_restriction_types` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `default_behavior_type_id` bigint(20) unsigned NOT NULL DEFAULT '6' COMMENT 'Default related behavior type ID. Defaults to 6 (redirect).',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The type of restriction.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text label for this restriction type.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_restriction_type` (`type`),
  FULLTEXT KEY `ft_searchable_type_label` (`type`,`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `s2_restriction_types`
--

INSERT INTO `s2_restriction_types` (`ID`, `default_behavior_type_id`, `type`, `label`) VALUES
(1, 6, 'type::post', 'Post (by Post Type)'),
(2, 6, 'taxonomy::term', 'Term (by Taxonomy)'),
(3, 6, 'uri', 'URI (Absolute Relative)'),
(4, 6, 'ip', 'IP Address'),
(5, 6, 'media', 'Media File'),
(6, 0, 'profile_field', 'Profile Field');

-- --------------------------------------------------------

--
-- Table structure for table `s2_taxes`
--

CREATE TABLE IF NOT EXISTS `s2_taxes` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `geo_area_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related area ID (or NULL if tax applies globally).',
  `tax_rate_id` bigint(20) unsigned NOT NULL COMMENT 'Related tax rate ID.',
  `overrides` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Does this tax override others? For instance, in the case of a global tax rate, should this override the global rate? If geo_area_id is NULL, this is irrelevant.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_tax` (`geo_area_id`,`tax_rate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_tax_rates`
--

CREATE TABLE IF NOT EXISTS `s2_tax_rates` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'A flat rate, i.e. a specific tax amount (if applicable).',
  `percentage` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'A percentage rate (if applicable).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_tax_rate` (`amount`,`percentage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_transactions`
--

CREATE TABLE IF NOT EXISTS `s2_transactions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `order_session_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related order session ID (if applicable).',
  `gateway_id` bigint(20) unsigned NOT NULL COMMENT 'Related gateway ID.',
  `subscr_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subscription ID (if applicable, supplied by gateway).',
  `txn_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Transaction ID (if applicable, supplied by gateway).',
  `time` int(10) unsigned NOT NULL COMMENT 'Time this transaction took place.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_gateway_txn_id` (`gateway_id`,`txn_id`),
  FULLTEXT KEY `ft_searchable_subscr_txn_ids` (`subscr_id`,`txn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_transaction_meta`
--

CREATE TABLE IF NOT EXISTS `s2_transaction_meta` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `transaction_id` bigint(20) unsigned NOT NULL COMMENT 'Related transaction ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field name.',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Meta field value.',
  `time` int(10) unsigned NOT NULL COMMENT 'Row creation time (and/or last update time).',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_transaction_meta` (`transaction_id`,`name`),
  FULLTEXT KEY `ft_searchable_name_value` (`name`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_user_login_log`
--

CREATE TABLE IF NOT EXISTS `s2_user_login_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user ID (if successful).',
  `event_type_id` bigint(20) unsigned NOT NULL COMMENT 'Related event type ID.',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username (the one submitted).',
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP address during this login attempt.',
  `time` int(10) unsigned NOT NULL COMMENT 'Time this login attempt took place.',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_username_ip` (`username`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_user_passtags`
--

CREATE TABLE IF NOT EXISTS `s2_user_passtags` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `access_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Access key (64 characters, unique value).',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related user ID (if applicable).',
  `passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Related passtag ID.',
  `order_session_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related order session ID (if applicable).',
  `transaction_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related transaction ID (if applicable).',
  `time_created` int(10) unsigned NOT NULL COMMENT 'Time this user passtag was created.',
  `time_starts` int(10) unsigned NOT NULL COMMENT 'When this passtag starts access.',
  `time_stops` int(10) NOT NULL DEFAULT '-1' COMMENT 'When this passtag stops access (defaults to -1 for infinity, access is ongoing; e.g. lifetime access).',
  `eot_time_stops` int(10) unsigned DEFAULT NULL COMMENT 'Time stops override. Define this to forcibly terminate access at a specific time. This effectively removes a user passtag (forcing an EOT time). Access can be restored to its original state (schedule) by nullifying this column.',
  `status` enum('active','inactive','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'User passtag status.',
  `last_cron_time._before_time_stops` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time we checked for a coming expiration.',
  `last_cron_time._time_stops` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time we checked for expiration.',
  `last_cron_value._time_stops` int(10) unsigned DEFAULT NULL COMMENT 'Last time_stops value on expiration.',
  `last_cron_time._max_uses` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time we checked for max uses.',
  `last_cron_value._max_uses` int(10) unsigned DEFAULT NULL COMMENT 'Last use time (according to logs) at the time of our last check for max uses.',
  `last_cron_time._max_ips` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time we checked for max IPs.',
  `last_cron_value._max_ips` int(10) unsigned DEFAULT NULL COMMENT 'Last use time (according to logs) at the time of our last check for max IPs.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_user_passtag` (`access_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_user_passtag_log`
--

CREATE TABLE IF NOT EXISTS `s2_user_passtag_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `user_passtag_id` bigint(20) unsigned NOT NULL COMMENT 'Related user passtag ID.',
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Content identifier, such as a post type/ID or file name. Any content accessed via passtags should be assigned a unique identifier so it is possible to exclude repeat uses of the same content when we need to.',
  `time` int(10) unsigned NOT NULL COMMENT 'Time this passtag access took place.',
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP address during this passtag access.',
  `counts` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Does this count against any limits?',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_identifier_ip` (`identifier`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `s2_user_profile_fields`
--

CREATE TABLE IF NOT EXISTS `s2_user_profile_fields` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID.',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'Related user ID.',
  `profile_field_id` bigint(20) unsigned NOT NULL COMMENT 'Related profile field ID.',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Profile field value label (if applicable).',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Value for this profile field.',
  `in_array` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Is this value part of an array?',
  `time` int(10) unsigned NOT NULL COMMENT 'Time last updated.',
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `ft_searchable_label_value` (`label`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
