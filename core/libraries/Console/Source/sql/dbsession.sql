CREATE TABLE `session` (
  `id`  int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'primary key' ,
  `session_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
  `session_data`  text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
  `create_time`  timestamp NULL ,
  `expired_time`  timestamp NULL ,
  PRIMARY KEY (`id`),
  INDEX `sid` (`session_id`) USING BTREE
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci
;