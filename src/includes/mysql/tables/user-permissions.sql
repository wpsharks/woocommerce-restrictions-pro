CREATE TABLE IF NOT EXISTS `%%table%%` (
  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `user_id` bigint(20) UNSIGNED NOT NULL,
  KEY `user_id_index` (`user_id`),

  `order_id` bigint(20) UNSIGNED NOT NULL,
  KEY `order_id_index` (`order_id`),

  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  KEY `subscription_id_index` (`subscription_id`),

  `product_id` bigint(20) UNSIGNED NOT NULL,
  KEY `product_id_index` (`product_id`),

  `item_id` bigint(20) UNSIGNED NOT NULL,
  KEY `item_id_index` (`item_id`),

  `restriction_id` bigint(20) UNSIGNED NOT NULL,
  KEY `restriction_id_index` (`restriction_id`),

  `access_time` int(10) UNSIGNED NOT NULL,
  `expire_time` int(10) UNSIGNED NOT NULL,
  `expire_directive` varchar(128) NOT NULL,

  `status` varchar(64) NOT NULL,
  `is_trashed` int(1) UNSIGNED NOT NULL,
  KEY `is_trashed_index` (`is_trashed`),

  `display_order` int(10) UNSIGNED NOT NULL,
  KEY `display_order_index` (`display_order`),

  `insertion_time` int(10) UNSIGNED NOT NULL,
  `last_update_time` int(10) UNSIGNED NOT NULL
);
