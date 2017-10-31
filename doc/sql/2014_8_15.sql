/* APP（游戏）控制表  */
CREATE TABLE `yxd_game_control` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL COMMENT '游戏id',
  `game_name` varchar(100) NOT NULL COMMENT '游戏名称',
  `zone_type` tinyint(1) NOT NULL COMMENT '类型1.简易2.精品',
  `version` varchar(10) NOT NULL COMMENT '版本号',
  `control_data` varchar(256) NOT NULL COMMENT '控制数据（checked:1,提审中;2,已审核）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
