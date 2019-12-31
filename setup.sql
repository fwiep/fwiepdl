DROP TABLE IF EXISTS `url`;
CREATE TABLE `url` (
  `url_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url_uuid` varchar(36) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `short_url` varchar(6) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `long_url` varchar(400) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `mime_type` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `mime_encoding` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `file_name` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `file_extension` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `is_local` tinyint(1) DEFAULT 0,
  `download_count` bigint(20) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `UQ_short_url` (`short_url`),
  UNIQUE KEY `UQ_long_url` (`long_url`(100)),
  KEY `short_url` (`short_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
