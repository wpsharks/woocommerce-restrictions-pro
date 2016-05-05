CREATE TABLE IF NOT EXISTS `%%table%%` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `restriction_id` bigint(20) UNSIGNED NOT NULL,
  `access_time` int(10) UNSIGNED NOT NULL,
  `expire_time` int(10) UNSIGNED NOT NULL,
  `is_suspended` int(1) UNSIGNED NOT NULL,
  `insertion_time` int(10) UNSIGNED NOT NULL,
  `last_update_time` int(10) UNSIGNED NOT NULL
);
