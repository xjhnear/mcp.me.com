CREATE TABLE `yxd_game_download_adv_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adv_id` int(11) NOT NULL COMMENT '广告ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `times` int(10) NOT NULL COMMENT '次数',
  `lastupdatetime` int(10) NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adviduid` (`adv_id`,`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `yxd_game_download_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `times` int(10) NOT NULL COMMENT '次数',
  `lastupdatetime` int(10) NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gid_uid` (`game_id`,`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
