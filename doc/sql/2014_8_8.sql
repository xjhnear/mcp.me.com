==圈子动态持久化
CREATE TABLE `yxd_feed_gamecircle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增序号',
  `gid` int(11) NOT NULL COMMENT '游戏ID',
  `type` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '动态类型',
  `linkid` int(11) NOT NULL DEFAULT '0' COMMENT '动态目标ID',
  `score` float(20,4) NOT NULL DEFAULT '0.0000' COMMENT '排序',
  `data` text COLLATE utf8_bin NOT NULL COMMENT '动态数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
