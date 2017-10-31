CREATE TABLE IF NOT EXISTS `yxd_tuiguang_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oldid` int(11) NOT NULL,
  `newid` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=94 ;

INSERT INTO `yxd_task` (`id`, `typename`, `type`, `step_name`, `step_desc`, `condition`, `action`, `reward`, `ctime`) VALUES
(10, '推广任务', 3, '推荐1名用户注册', '推荐1名用户注册成功', '1', 'oldtuiguang_1', '{"score":"10","experience":"0"}', 0),
(11, '推广任务', 3, '推荐10名用户注册', '推荐10名用户注册成功', '10', 'oldtuiguang_10', '{"score":"200","experience":"0"}', 0),
(12, '推广任务', 3, '推广100名新用户注册', '成功推广100名新用户注册', '100', 'oldtuiguang100', '{"score":500,"experience":0}', 0),
(13, '推广任务', 3, '推广500名新用户注册', '成功推荐500名新用户注册', '500', 'oldtuiguang_500', '{"score":1000,"experience":"0"}', 0),
(14, '推广任务', 3, '推荐1000名用户注册', '成功推荐1000名用户注册', '1000', 'oldtuiguang_1000', '{"score":"3000","experience":"0"}', 0);


NSERT INTO `yxd_system_message_tpl` (`id`, `ename`, `content`) VALUES
(14, 'register_tuiguang', '恭喜使用你游戏多推广活动注册成功，获得游币[score]枚'),
(15, 'tuiguang_score', '恭喜使用你成功推广了[num]位新用户下载并注册了游戏多，获得游币[score]枚'),
(16, 'extra_score', '达到[num]奖励条件，额外奖励游币[score]枚');

INSERT INTO `yxd_system_setting` (`id`, `keyname`, `data`) VALUES
(6, 'tuiguang_setting', 'a:6:{s:13:"newtuiguang_1";s:2:"10";s:13:"oldtuiguang_1";s:3:"100";s:14:"oldtuiguang_10";s:4:"1000";s:15:"oldtuiguang_100";s:4:"2000";s:15:"oldtuiguang_500";s:4:"3000";s:16:"oldtuiguang_1000";s:4:"5000";}');
