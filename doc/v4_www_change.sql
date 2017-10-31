CREATE TABLE `m_zt_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sort` int(10) NOT NULL,
  `addtime` int(11) NOT NULL,
  `platform` enum('ios','android') NOT NULL,
  PRIMARY KEY (`type_id`),
  KEY `type_name` (`type_name`),
  KEY `sort` (`sort`),
  KEY `addtime` (`addtime`),
  KEY `platform` (`platform`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


ALTER TABLE `m_videos` ADD COLUMN `duration` varchar(50) DEFAULT 0 NULL COMMENT '时长' AFTER `commenttimes`;

ALTER TABLE `m_zt_type` ADD COLUMN `type_id` int(11) DEFAULT 0 NULL COMMENT '分类' AFTER `id`;


CREATE TABLE `m_videos_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `isTop` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(10) NOT NULL,
  `platform` enum('ios','android','all') CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `m_gonglue` ADD COLUMN `litpic` varchar(250) DEFAULT 0 NULL COMMENT '图片' AFTER `commenttimes`;
ALTER TABLE `m_gonglue` ADD COLUMN `litpic2` varchar(250) DEFAULT 0 NULL COMMENT '图片' AFTER `commenttimes`;
ALTER TABLE `m_gonglue` ADD COLUMN `litpic3` varchar(250) DEFAULT 0 NULL COMMENT '图片' AFTER `commenttimes`;


ALTER TABLE `yxd_account` 
ADD COLUMN `phone` varchar(51) DEFAULT '' NULL COMMENT 'IOS手机号' AFTER `mobile`,
ADD COLUMN `is_open_android_money` tinyint(1) DEFAULT '0' NULL COMMENT '是否开通安卓游币账号' AFTER `phone`,
ADD COLUMN `is_open_ios_money` tinyint(1) DEFAULT '0' NULL COMMENT '是否开通IOS游币账号' AFTER `phone`,
ADD COLUMN `province` varchar(50) DEFAULT '' NULL COMMENT '' AFTER `phone`,
ADD COLUMN `city` varchar(50) DEFAULT '' NULL COMMENT '' AFTER `phone`,
ADD COLUMN `region` varchar(50) DEFAULT '' NULL COMMENT '' AFTER `phone`,
ADD COLUMN `address` varchar(100) DEFAULT '' NULL COMMENT '' AFTER `phone`,
