CREATE TABLE IF NOT EXISTS `%%table%%` (
  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,

  `restriction_id` bigint(20) UNSIGNED NOT NULL,
  `original_restriction_id` bigint(20) UNSIGNED NOT NULL,

  `access_time` int(10) UNSIGNED NOT NULL,
  `original_access_time` int(10) UNSIGNED NOT NULL,

  `expire_time` int(10) UNSIGNED NOT NULL,
  `expire_time_via` varchar(255) NOT NULL,
  `expire_time_via_id` bigint(20) UNSIGNED NOT NULL,
  `original_expire_time` int(10) UNSIGNED NOT NULL,

  `is_enabled` int(1) UNSIGNED NOT NULL,
  `is_trashed` int(1) UNSIGNED NOT NULL,

  `display_order` int(10) UNSIGNED NOT NULL,

  `insertion_time` int(10) UNSIGNED NOT NULL,
  `last_update_time` int(10) UNSIGNED NOT NULL
);
