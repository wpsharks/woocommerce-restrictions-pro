CREATE TABLE IF NOT EXISTS `%%table%%` (
  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint(20) UNSIGNED NOT NULL,

  `order_id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,

  `restriction_id` bigint(20) UNSIGNED NOT NULL,
  `access_time` int(10) UNSIGNED NOT NULL,
  `expire_time` int(10) UNSIGNED NOT NULL,
  `expire_directive` varchar(128) NOT NULL,

  `status` varchar(64) NOT NULL,
  `is_trashed` int(1) UNSIGNED NOT NULL,

  `display_order` int(10) UNSIGNED NOT NULL,

  `insertion_time` int(10) UNSIGNED NOT NULL,
  `last_update_time` int(10) UNSIGNED NOT NULL
);
