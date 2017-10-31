/*评论回帖删除标识位*/

ALTER TABLE `yxd_comment` ADD COLUMN `isdel` TINYINT(1) DEFAULT 0 NULL COMMENT '是否删除' AFTER `is_admin`;


ALTER TABLE `m_games` ADD COLUMN `tosafari` TINYINT(1) DEFAULT 0 NULL COMMENT '浏览器打开类型1:外部0:内置' AFTER `downurl`;

/*
  圈子动态数量统计表
*/
CREATE TABLE `yxd_feed_gamecircle_count` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8_bin DEFAULT '' COMMENT '对应redis键名',
  `uid` int(11) DEFAULT '0' COMMENT '用户UID',
  `gid` int(10) DEFAULT '0' COMMENT '游戏ID',
  `total` int(10) NOT NULL DEFAULT '0'  COMMENT '数量',
  `last_update_time` int(10) NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `uid_gid` (`uid`,`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `yxd_comment` ADD COLUMN `is_sync` TINYINT(1) DEFAULT 0 NULL COMMENT '是否同步' AFTER `isdel`;
ALTER TABLE `yxd_comment` ADD INDEX is_sync(`is_sync`);
ALTER TABLE `yxd_forum_topic` ADD COLUMN `is_sync` TINYINT(1) DEFAULT 0 NULL COMMENT '是否同步' AFTER `is_admin`;
ALTER TABLE `yxd_forum_topic` ADD INDEX is_sync(`is_sync`);