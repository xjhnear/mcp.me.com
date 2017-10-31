CREATE TABLE `yxd_like_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8_bin NOT NULL COMMENT '唯一标识',
  `like_target_type` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '目标类型',
  `like_target_id` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '目标ID',
  `identify` varchar(100) COLLATE utf8_bin NOT NULL COMMENT '设备标识',
  `ctime` int(11) NOT NULL COMMENT '赞时间',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '赞次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
