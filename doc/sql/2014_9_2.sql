ALTER TABLE `yxd_giftbag` ADD COLUMN `is_appoint` TINYINT(1) DEFAULT 0 NOT NULL COMMENT '是否是专属礼包' AFTER `is_show`;

CREATE TABLE `yxd_giftbag_appoint`( `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, `giftbag_id` INT(11) NOT NULL COMMENT '礼包ID', `uid` INT(11) NOT NULL COMMENT '用户ID',`add_time` INT(10) NOT NULL COMMENT '添加时间', PRIMARY KEY (`id`) ) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;
