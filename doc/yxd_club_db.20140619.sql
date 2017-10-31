/*
MySQL Data Transfer
Source Host: localhost
Source Database: yxd_club
Target Host: localhost
Target Database: yxd_club
Date: 2014/6/19 14:59:38
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for yxd_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account`;
CREATE TABLE `yxd_account` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_bin NOT NULL COMMENT '用户名',
  `nickname` varchar(20) COLLATE utf8_bin NOT NULL COMMENT '昵称',
  `email` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '邮箱',
  `avatar` varchar(200) COLLATE utf8_bin NOT NULL COMMENT '头像',
  `password` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '密码',
  `mobile` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '手机号码',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` int(10) NOT NULL DEFAULT '0' COMMENT '生日',
  `summary` varchar(500) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '我自介绍,个性签名',
  `homebg` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `dateline` int(10) NOT NULL COMMENT '注册时间',
  `reg_ip` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '注册IP',
  `longitude` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '经度',
  `latitude` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '纬度',
  `client` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '注册来源,pc/app/appweb',
  `apple_token` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'apple_token',
  `idfa` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'idfa',
  `mac` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'mac',
  `openudid` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'openudid',
  `osversion` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '客户端APP操作系统版本',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `email_pwd` (`email`,`password`)
) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_ban
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_ban`;
CREATE TABLE `yxd_account_ban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `type` int(10) NOT NULL COMMENT '类型',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  `expired` int(10) NOT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_circle
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_circle`;
CREATE TABLE `yxd_account_circle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `sort` int(10) NOT NULL DEFAULT '50' COMMENT '排序',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `gid` (`game_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_credit_history
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_credit_history`;
CREATE TABLE `yxd_account_credit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '键主ID',
  `uid` int(11) NOT NULL COMMENT '操作用户UID',
  `info` varchar(255) DEFAULT NULL COMMENT '动作描述',
  `action` char(30) DEFAULT NULL COMMENT '作动',
  `type` char(10) NOT NULL DEFAULT 'credit' COMMENT '类型:（experience:经验 gold:财富）',
  `credit` mediumint(3) NOT NULL DEFAULT '0' COMMENT '富财或者经验的曾减值',
  `mtime` int(11) NOT NULL COMMENT '操作时间戳',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_follow
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_follow`;
CREATE TABLE `yxd_account_follow` (
  `follow_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(11) NOT NULL COMMENT '关注者UID',
  `fuid` int(11) NOT NULL COMMENT '被关注者UID',
  `remark` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '备注',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`follow_id`),
  KEY `uid` (`uid`),
  KEY `fuid` (`fuid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_friend
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_friend`;
CREATE TABLE `yxd_account_friend` (
  `friend_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `friend_group_id` int(10) NOT NULL COMMENT '好友分组ID',
  `uid` int(11) NOT NULL COMMENT 'UID',
  `fuid` int(11) NOT NULL COMMENT '好友UID',
  `remark` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '备注',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`friend_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_friend_group
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_friend_group`;
CREATE TABLE `yxd_account_friend_group` (
  `friend_group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(11) NOT NULL COMMENT 'UID',
  `title` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '分组名称',
  PRIMARY KEY (`friend_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_group
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_group`;
CREATE TABLE `yxd_account_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `group_name` varchar(255) NOT NULL COMMENT '用户组名称',
  `ctime` int(11) DEFAULT NULL COMMENT '创建时间',
  `group_icon` varchar(120) NOT NULL COMMENT '用户组图标名称',
  `group_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型、0：普通组，1:特殊组，',
  `yxd_name` varchar(20) NOT NULL DEFAULT 'public' COMMENT '应用名称',
  `authorize_nodes` varchar(255) NOT NULL DEFAULT '' COMMENT '是否为认证组',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_group_link
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_group_link`;
CREATE TABLE `yxd_account_group_link` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) NOT NULL COMMENT '户用UID',
  `group_id` int(10) NOT NULL COMMENT '户用组ID',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_notice_email
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_notice_email`;
CREATE TABLE `yxd_account_notice_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(20) DEFAULT NULL,
  `appinfo` varchar(20) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `is_send` tinyint(1) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_notice_message
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_notice_message`;
CREATE TABLE `yxd_account_notice_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(20) DEFAULT NULL,
  `appinfo` varchar(20) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `ctime` int(10) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_page
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_page`;
CREATE TABLE `yxd_account_page` (
  `uid` int(11) NOT NULL,
  `bgimg_url` varchar(255) NOT NULL DEFAULT '' COMMENT '主页背景图',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_account_shield_history
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_shield_history`;
CREATE TABLE `yxd_account_shield_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `field` varchar(20) COLLATE utf8_bin DEFAULT NULL COMMENT '屏蔽的字段',
  `data` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '屏蔽的数据',
  `ctime` int(11) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_account_thirdlogin
-- ----------------------------
DROP TABLE IF EXISTS `yxd_account_thirdlogin`;
CREATE TABLE `yxd_account_thirdlogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL COMMENT '类型',
  `type_uid` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '第三方帐号ID',
  `access_token` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '访问令牌',
  `expires_in` int(11) DEFAULT NULL COMMENT '有效期',
  `refresh_token` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '刷新令牌',
  PRIMARY KEY (`id`),
  KEY `access_token` (`access_token`),
  KEY `third_login` (`type`,`type_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_activity
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity`;
CREATE TABLE `yxd_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '活动标题',
  `shorttitle` varchar(50) NOT NULL DEFAULT '' COMMENT '短标题',
  `listpic` varchar(255) NOT NULL COMMENT '列表图',
  `bigpic` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
  `startdate` int(10) NOT NULL COMMENT '开始时间',
  `enddate` int(10) NOT NULL COMMENT '结束时间',
  `lotterytime` int(10) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联游戏ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0关闭1开启',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '活动类型[1:问答][2:论坛][3:礼包]',
  `rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '帖子ID',
  `addtime` int(10) NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`),
  KEY `do_time` (`startdate`,`enddate`),
  KEY `game_id` (`startdate`,`enddate`,`game_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_ask
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_ask`;
CREATE TABLE `yxd_activity_ask` (
  `id` int(11) NOT NULL,
  `reward` varchar(1000) NOT NULL COMMENT '奖品',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_ask_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_ask_account`;
CREATE TABLE `yxd_activity_ask_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ask_id` int(11) NOT NULL,
  `answers` varchar(500) NOT NULL,
  `addtime` int(10) NOT NULL,
  `result` int(10) NOT NULL DEFAULT '0',
  `reward_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_askid` (`uid`,`ask_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_ask_question
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_ask_question`;
CREATE TABLE `yxd_activity_ask_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ask_id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `options` text,
  `answer` varchar(10) DEFAULT NULL,
  `sort` int(10) DEFAULT '50',
  `status` tinyint(1) DEFAULT '0' COMMENT '0',
  `addtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ask_id` (`ask_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_hunt
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_hunt`;
CREATE TABLE `yxd_activity_hunt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `bigpic` varchar(255) DEFAULT NULL,
  `rule_id` int(10) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `startdate` int(10) DEFAULT NULL,
  `enddate` int(10) DEFAULT NULL,
  `reward` text NOT NULL,
  `first_prize` text NOT NULL,
  `second_prize` text NOT NULL,
  `third_prize` text NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `clicktimes` int(10) NOT NULL DEFAULT '0',
  `addtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_hunt_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_hunt_account`;
CREATE TABLE `yxd_activity_hunt_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `hunt_id` int(11) NOT NULL,
  `reward_no` tinyint(1) NOT NULL COMMENT '奖品等级',
  `reward_score` int(10) NOT NULL,
  `reward_cardno` varchar(50) NOT NULL DEFAULT '',
  `reward_expense` varchar(500) NOT NULL DEFAULT '',
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_activity_prize
-- ----------------------------
DROP TABLE IF EXISTS `yxd_activity_prize`;
CREATE TABLE `yxd_activity_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(10) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '奖品名称',
  `shortname` varchar(50) DEFAULT NULL COMMENT '奖品简称',
  `listpic` varchar(255) NOT NULL COMMENT '图',
  `price` int(10) NOT NULL COMMENT '价格',
  `gift_id` int(11) NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL DEFAULT '0',
  `expense` varchar(500) NOT NULL DEFAULT '',
  `addtime` int(10) NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_admin_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_admin_account`;
CREATE TABLE `yxd_admin_account` (
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `realname` varchar(20) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `role_id` int(10) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '关联前台用户ID',
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_atme
-- ----------------------------
DROP TABLE IF EXISTS `yxd_atme`;
CREATE TABLE `yxd_atme` (
  `atme_id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(20) DEFAULT NULL COMMENT '应用',
  `target_table` varchar(20) DEFAULT NULL COMMENT '目标表',
  `target_id` int(11) DEFAULT NULL COMMENT '目标ID',
  `uid` int(11) DEFAULT NULL COMMENT '赞用户UID',
  PRIMARY KEY (`atme_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_authorize_node
-- ----------------------------
DROP TABLE IF EXISTS `yxd_authorize_node`;
CREATE TABLE `yxd_authorize_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(50) NOT NULL COMMENT '应用名称',
  `appinfo` varchar(50) NOT NULL COMMENT '应用说明',
  `module` varchar(50) NOT NULL COMMENT '模块名称',
  `rule` varchar(50) NOT NULL COMMENT '权限类型',
  `ruleinfo` varchar(50) NOT NULL COMMENT '权限名称',
  PRIMARY KEY (`id`),
  KEY `rule` (`rule`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_chat_log
-- ----------------------------
DROP TABLE IF EXISTS `yxd_chat_log`;
CREATE TABLE `yxd_chat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL COMMENT '发起人UID',
  `to_uid` int(11) NOT NULL COMMENT '接受人UID',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL COMMENT '消息内容',
  `addtime` int(10) NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`id`),
  KEY `from_to` (`from_uid`,`to_uid`),
  KEY `time` (`addtime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_chat_user
-- ----------------------------
DROP TABLE IF EXISTS `yxd_chat_user`;
CREATE TABLE `yxd_chat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL COMMENT '发送者uid',
  `to_uid` int(11) NOT NULL COMMENT '接受者uid',
  `last_message` varchar(500) DEFAULT NULL COMMENT '最后信息',
  `last_time` int(10) DEFAULT NULL COMMENT '最后时间',
  PRIMARY KEY (`id`),
  KEY `from_uid` (`from_uid`,`to_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_checkinfo
-- ----------------------------
DROP TABLE IF EXISTS `yxd_checkinfo`;
CREATE TABLE `yxd_checkinfo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_time` (`uid`,`ctime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_comment
-- ----------------------------
DROP TABLE IF EXISTS `yxd_comment`;
CREATE TABLE `yxd_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL COMMENT '父ID',
  `target_id` int(11) DEFAULT NULL COMMENT '目标ID',
  `target_table` varchar(50) DEFAULT NULL COMMENT '目标表',
  `content` text COMMENT '评论内容',
  `format_content` text DEFAULT NULL COMMENT '格式化后的内容',
  `addtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `uid` int(11) DEFAULT NULL COMMENT '评论人UID',
  `storey` int(11) NOT NULL DEFAULT '0' COMMENT '楼层',
  `best` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最佳[仅问题帖有效]',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被后台编辑',
  PRIMARY KEY (`id`),
  KEY `table_id` (`target_id`,`target_table`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_credit_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_credit_account`;
CREATE TABLE `yxd_credit_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `score` int(11) DEFAULT NULL COMMENT '积分总值',
  `experience` int(11) DEFAULT NULL COMMENT '经验总值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_credit_level
-- ----------------------------
DROP TABLE IF EXISTS `yxd_credit_level`;
CREATE TABLE `yxd_credit_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '名称',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '图标',
  `start` int(11) NOT NULL COMMENT '经验下限',
  `end` int(11) NOT NULL COMMENT '经验上限',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_credit_setting
-- ----------------------------
DROP TABLE IF EXISTS `yxd_credit_setting`;
CREATE TABLE `yxd_credit_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '积分动作',
  `alias` varchar(255) NOT NULL COMMENT '积分名称',
  `type` varchar(30) NOT NULL DEFAULT 'user' COMMENT '积分类型',
  `crcletype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '周期',
  `rewardnum` int(10) NOT NULL DEFAULT '0' COMMENT '奖励次数',
  `info` text NOT NULL COMMENT '积分说明',
  `score` int(11) DEFAULT NULL COMMENT '积分值',
  `experience` int(11) DEFAULT NULL COMMENT '经验值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_doc_category
-- ----------------------------
DROP TABLE IF EXISTS `yxd_doc_category`;
CREATE TABLE `yxd_doc_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(10) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_doc_interface
-- ----------------------------
DROP TABLE IF EXISTS `yxd_doc_interface`;
CREATE TABLE `yxd_doc_interface` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `http_method` varchar(10) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `cate_id` int(10) DEFAULT NULL,
  `cate_name` varchar(20) DEFAULT NULL,
  `require_login` tinyint(1) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `input_params` text,
  `out_code` text,
  `out_params` text,
  `error_params` text,
  `version` varchar(10) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_doc_kv
-- ----------------------------
DROP TABLE IF EXISTS `yxd_doc_kv`;
CREATE TABLE `yxd_doc_kv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_doc_project
-- ----------------------------
DROP TABLE IF EXISTS `yxd_doc_project`;
CREATE TABLE `yxd_doc_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `host_develop` varchar(255) DEFAULT NULL,
  `host_test` varchar(255) DEFAULT NULL,
  `host_product` varchar(255) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  `interface_num` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_forum
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum`;
CREATE TABLE `yxd_forum` (
  `gid` int(11) NOT NULL COMMENT '游戏ID',
  `name` varchar(20) COLLATE utf8_bin NOT NULL COMMENT '名称',
  `logo` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Logo',
  `displayorder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示方式',
  `styleid` int(10) NOT NULL DEFAULT '0' COMMENT '风格ID',
  `threads` int(10) NOT NULL DEFAULT '0' COMMENT '总发帖量',
  `posts` int(10) NOT NULL DEFAULT '0' COMMENT '总回帖量',
  `todays` int(10) NOT NULL DEFAULT '0' COMMENT '今天发帖数量',
  `lastpost` int(10) NOT NULL DEFAULT '0' COMMENT '最后发帖时间',
  `domain` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '子域名',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_forum_attachment
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_attachment`;
CREATE TABLE `yxd_forum_attachment` (
  `aid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(11) DEFAULT NULL COMMENT '主题帖ID',
  `pid` int(11) DEFAULT NULL COMMENT '帖子ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `dateline` int(10) DEFAULT NULL COMMENT '创建时间',
  `filename` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '原文件名',
  `filetype` varchar(10) COLLATE utf8_bin DEFAULT NULL COMMENT '文件类型',
  `filesize` int(10) DEFAULT NULL COMMENT '文件大小',
  `attachment` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '文件服务器路径',
  `remote` tinyint(1) DEFAULT NULL COMMENT '是否远程',
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '文件描述',
  `isimg` tinyint(1) DEFAULT NULL COMMENT '是否图片',
  `width` int(10) DEFAULT NULL COMMENT '图片宽度',
  `thumb` tinyint(1) DEFAULT NULL COMMENT '是否缩略图',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=328 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_forum_channel
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_channel`;
CREATE TABLE `yxd_forum_channel` (
  `cid` int(10) NOT NULL AUTO_INCREMENT COMMENT '论坛分类标签ID',
  `gid` int(11) NOT NULL COMMENT '论坛ID',
  `channel_name` varchar(20) COLLATE utf8_bin DEFAULT NULL COMMENT '分类标签名称',
  `displayorder` int(10) DEFAULT '0' COMMENT '显示顺序',
  `allowpost` tinyint(1) DEFAULT '1' COMMENT '否是允许发帖',
  `type` tinyint(1) DEFAULT NULL COMMENT '频道类型',
  PRIMARY KEY (`cid`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_forum_circle
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_circle`;
CREATE TABLE `yxd_forum_circle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_forum_notice
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_notice`;
CREATE TABLE `yxd_forum_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gid` int(11) DEFAULT NULL COMMENT '论坛ID',
  `title` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '标题',
  `message` text COLLATE utf8_bin COMMENT '公告内容',
  `startdate` int(10) DEFAULT NULL COMMENT '开始时间',
  `enddate` int(10) DEFAULT NULL COMMENT '终止时间',
  `dateline` int(10) DEFAULT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_forum_post
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_post`;
CREATE TABLE `yxd_forum_post` (
  `pid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gid` int(11) NOT NULL COMMENT '论坛ID',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '回复帖父帖ID',
  `tid` int(11) NOT NULL COMMENT '主题ID',
  `first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否属于主题',
  `subject` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '标题',
  `dateline` int(10) DEFAULT NULL COMMENT '发布时间',
  `message` text CHARACTER SET utf8 COLLATE utf8_bin COMMENT '内容',
  `format_message` text COMMENT '格式化后的内容',
  `author` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '作者',
  `author_uid` int(11) DEFAULT NULL COMMENT '作者UID',
  `best` tinyint(1) NOT NULL DEFAULT '0' COMMENT '否是最佳答案',
  `storey` int(11) NOT NULL DEFAULT '0' COMMENT '楼层',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for yxd_forum_thread_bk
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_thread_bk`;
CREATE TABLE `yxd_forum_thread_bk` (
  `tid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gid` int(11) NOT NULL COMMENT '论坛ID',
  `cid` int(11) NOT NULL COMMENT '分类标签ID',
  `subject` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '主题',
  `summary` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `listpic` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `author` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '作者',
  `author_uid` int(11) NOT NULL COMMENT '作者UID',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `lastpost` int(10) NOT NULL DEFAULT '0' COMMENT '最后回帖时间',
  `lastposter` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '最后回帖人',
  `views` int(10) NOT NULL DEFAULT '0' COMMENT '浏览数',
  `replies` int(10) NOT NULL DEFAULT '0' COMMENT '回复数',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '主题显示顺序，1级置顶 0正常 -1回收站 -2审核中 -3审核忽略 -4草稿',
  `highlight` tinyint(1) NOT NULL DEFAULT '0' COMMENT '高亮',
  `stick` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `digest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '精华',
  `rate` int(10) NOT NULL DEFAULT '0' COMMENT '评分',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '状态',
  `ask` tinyint(1) NOT NULL DEFAULT '0' COMMENT '问答帖',
  `award` int(10) NOT NULL DEFAULT '0' COMMENT '悬赏积分',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for yxd_forum_topic
-- ----------------------------
DROP TABLE IF EXISTS `yxd_forum_topic`;
CREATE TABLE `yxd_forum_topic` (
  `tid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gid` int(11) NOT NULL COMMENT '论坛ID',
  `cid` int(11) NOT NULL COMMENT '分类标签ID',
  `subject` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '主题',
  `summary` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `listpic` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `format_message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `author` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '作者',
  `author_uid` int(11) NOT NULL COMMENT '作者UID',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `lastpost` int(10) NOT NULL DEFAULT '0' COMMENT '最后回帖时间',
  `lastposter` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '最后回帖人',
  `views` int(10) NOT NULL DEFAULT '0' COMMENT '浏览数',
  `replies` int(10) NOT NULL DEFAULT '0' COMMENT '回复数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '赞数',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '主题显示顺序，1级置顶 0正常 -1回收站 -2审核中 -3审核忽略 -4草稿',
  `highlight` tinyint(1) NOT NULL DEFAULT '0' COMMENT '高亮',
  `stick` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `digest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '精华',
  `rate` int(10) NOT NULL DEFAULT '0' COMMENT '评分',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '状态',
  `ask` tinyint(1) NOT NULL DEFAULT '0' COMMENT '问答帖',
  `askstatus` tinyint(1) NOT NULL DEFAULT '0',
  `award` int(10) NOT NULL DEFAULT '0' COMMENT '悬赏积分',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否管理员发帖',
  PRIMARY KEY (`tid`),
  KEY `gid_cid` (`gid`,`cid`),
  KEY `sort` (`displayorder`),
  KEY `addtime` (`dateline`),
  KEY `uptime` (`lastpost`)
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for yxd_game_credit
-- ----------------------------
DROP TABLE IF EXISTS `yxd_game_credit`;
CREATE TABLE `yxd_game_credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增序列',
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '游币',
  `experience` int(10) NOT NULL DEFAULT '0' COMMENT '经验',
  `starttime` int(10) NOT NULL COMMENT '开始时间',
  `endtime` int(10) NOT NULL COMMENT '结束时间',
  `addtime` int(10) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_gamecircle
-- ----------------------------
DROP TABLE IF EXISTS `yxd_gamecircle`;
CREATE TABLE `yxd_gamecircle` (
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `homebg` varchar(255) DEFAULT NULL COMMENT '主页背景图',
  `comtimes` int(10) DEFAULT NULL COMMENT '评论数',
  `topictimes` int(10) DEFAULT NULL COMMENT '发帖数',
  `ctime` int(10) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_gift_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_gift_account`;
CREATE TABLE `yxd_gift_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `gift_id` int(11) NOT NULL COMMENT '礼包ID',
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `card_no` varchar(50) NOT NULL COMMENT '卡号',
  `addtime` int(10) NOT NULL COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_gift_reserve
-- ----------------------------
DROP TABLE IF EXISTS `yxd_gift_reserve`;
CREATE TABLE `yxd_gift_reserve` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `gift_id` int(11) NOT NULL COMMENT '礼包ID',
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `card_no` varchar(50) NOT NULL COMMENT '卡号',
  `addtime` int(10) NOT NULL COMMENT '预定时间',
  `gettime` int(10) NOT NULL COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_giftbag
-- ----------------------------
DROP TABLE IF EXISTS `yxd_giftbag`;
CREATE TABLE `yxd_giftbag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(10) NOT NULL DEFAULT '0' COMMENT '游戏ID',
  `is_android` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否属于安卓平台',
  `is_ios` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否属于IOS平台',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '标题',
  `shorttitle` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '短标题',
  `listpic` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '缩略图',
  `content` text NOT NULL COMMENT '内容',
  `editor` int(10) NOT NULL COMMENT '编辑',
  `starttime` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `ctime` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `total_num` int(10) NOT NULL DEFAULT '0' COMMENT '总数',
  `last_num` int(10) NOT NULL DEFAULT '0' COMMENT '剩余量',
  `condition` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '领取条件',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `is_activity` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否属于活动',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热门',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `sort` int(10) NOT NULL DEFAULT '50' COMMENT '排序',
  `is_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已发送通知',
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  KEY `is_ios` (`is_ios`),
  KEY `time` (`starttime`,`endtime`),
  KEY `cond` (`is_show`,`is_activity`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_giftbag_card
-- ----------------------------
DROP TABLE IF EXISTS `yxd_giftbag_card`;
CREATE TABLE `yxd_giftbag_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `giftbag_id` int(11) NOT NULL,
  `cardno` varchar(100) COLLATE utf8_bin DEFAULT '',
  `is_get` tinyint(1) NOT NULL DEFAULT '0',
  `gettime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `giftbag_id` (`giftbag_id`,`is_get`)
) ENGINE=InnoDB AUTO_INCREMENT=10002 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_inform
-- ----------------------------
DROP TABLE IF EXISTS `yxd_inform`;
CREATE TABLE `yxd_inform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL COMMENT '目标ID',
  `type` tinyint(1) NOT NULL COMMENT '类型',
  `uid` int(11) NOT NULL COMMENT '举报人',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '举报次数',
  `addtime` int(10) NOT NULL COMMENT '举报时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_like
-- ----------------------------
DROP TABLE IF EXISTS `yxd_like`;
CREATE TABLE `yxd_like` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '赞UID',
  `target_id` int(11) NOT NULL COMMENT '赞对象ID',
  `target_table` varchar(50) NOT NULL COMMENT '赞对象分类',
  `ctime` int(10) NOT NULL COMMENT '赞时间',
  PRIMARY KEY (`aid`),
  KEY `tid_table` (`target_id`,`target_table`)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_notice_setting
-- ----------------------------
DROP TABLE IF EXISTS `yxd_notice_setting`;
CREATE TABLE `yxd_notice_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `app` varchar(20) NOT NULL DEFAULT '',
  `appinfo` varchar(20) NOT NULL DEFAULT '',
  `module` varchar(20) NOT NULL DEFAULT '',
  `send_email` tinyint(1) NOT NULL DEFAULT '0',
  `send_message` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_access_tokens`;
CREATE TABLE `yxd_oauth2_access_tokens` (
  `access_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`access_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_authorization_codes
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_authorization_codes`;
CREATE TABLE `yxd_oauth2_authorization_codes` (
  `authorization_code` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `redirect_uri` varchar(2000) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_clients
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_clients`;
CREATE TABLE `yxd_oauth2_clients` (
  `client_id` varchar(80) NOT NULL,
  `client_secret` varchar(80) NOT NULL,
  `redirect_uri` varchar(2000) NOT NULL,
  `grant_types` varchar(80) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_jwt
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_jwt`;
CREATE TABLE `yxd_oauth2_jwt` (
  `client_id` varchar(80) NOT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `public_key` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_refresh_tokens
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_refresh_tokens`;
CREATE TABLE `yxd_oauth2_refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_scopes
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_scopes`;
CREATE TABLE `yxd_oauth2_scopes` (
  `type` varchar(255) NOT NULL DEFAULT 'supported',
  `scope` varchar(2000) DEFAULT NULL,
  `client_id` varchar(80) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_oauth2_users
-- ----------------------------
DROP TABLE IF EXISTS `yxd_oauth2_users`;
CREATE TABLE `yxd_oauth2_users` (
  `username` varchar(255) NOT NULL,
  `password` varchar(2000) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_shop_goods
-- ----------------------------
DROP TABLE IF EXISTS `yxd_shop_goods`;
CREATE TABLE `yxd_shop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `shortname` varchar(20) NOT NULL DEFAULT '' COMMENT '简称',
  `listpic` varchar(255) NOT NULL DEFAULT '' COMMENT '列表图',
  `bigpic_1` varchar(255) NOT NULL,
  `bigpic_2` varchar(255) NOT NULL DEFAULT '',
  `bigpic_3` varchar(255) NOT NULL DEFAULT '',
  `bigpic_4` varchar(255) NOT NULL DEFAULT '',
  `bigpic_5` varchar(255) NOT NULL DEFAULT '',
  `summary` text NOT NULL COMMENT '商品介绍',
  `instruction` text NOT NULL COMMENT '使用说明',
  `starttime` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '所需游币',
  `totalnum` int(10) NOT NULL DEFAULT '0' COMMENT '总数量',
  `usednum` int(10) NOT NULL DEFAULT '0' COMMENT '剩余数量',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热门',
  `isrecommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `isnew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最新',
  `sort` int(10) NOT NULL DEFAULT '50' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：正常1：结束-1：未开始',
  `addtime` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `gtype` tinyint(1) NOT NULL DEFAULT '1' COMMENT '商品类型[0:][1:实物][2:虚拟]',
  `vgoods` varchar(50) DEFAULT NULL,
  `gift_id` int(11) NOT NULL DEFAULT '0' COMMENT '礼包ID',
  `expense` varchar(500) NOT NULL DEFAULT '' COMMENT '实物领取方式',
  PRIMARY KEY (`id`),
  KEY `time` (`starttime`,`endtime`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_shop_goods_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_shop_goods_account`;
CREATE TABLE `yxd_shop_goods_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `goods_type` int(10) NOT NULL COMMENT '商品类型',
  `uid` int(11) NOT NULL COMMENT '兑换人UID',
  `score` int(10) NOT NULL COMMENT '消耗积分',
  `cardno` varchar(50) NOT NULL DEFAULT '',
  `expense` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL COMMENT '兑换时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_system_message
-- ----------------------------
DROP TABLE IF EXISTS `yxd_system_message`;
CREATE TABLE `yxd_system_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(10) NOT NULL COMMENT '消息类型',
  `linktype` tinyint(1) NOT NULL COMMENT '链接类型',
  `link` varchar(255) NOT NULL COMMENT '',
  `title` varchar(50) NOT NULL COMMENT '',
  `content` text NOT NULL COMMENT '',
  `to_uid` int(11) NOT NULL COMMENT '',
  `sendtime` int(10) NOT NULL COMMENT '',
  `istop` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `to_uid` (`to_uid`),
  KEY `dtime` (`sendtime`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_system_message_tpl
-- ----------------------------
DROP TABLE IF EXISTS `yxd_system_message_tpl`;
CREATE TABLE `yxd_system_message_tpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ename` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '模板标识',
  `content` varchar(1000) COLLATE utf8_bin DEFAULT NULL COMMENT '模板内容',
  PRIMARY KEY (`id`),
  KEY `ename` (`ename`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `yxd_system_setting`;
CREATE TABLE `yxd_system_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyname` varchar(50) COLLATE utf8_bin NOT NULL,
  `data` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyname` (`keyname`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for yxd_task
-- ----------------------------
DROP TABLE IF EXISTS `yxd_task`;
CREATE TABLE `yxd_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `typename` varchar(50) NOT NULL COMMENT '任务类型名称',
  `type` tinyint(1) NOT NULL COMMENT '任务类型',
  `step_name` varchar(100) NOT NULL COMMENT '任务名称',
  `step_desc` varchar(500) NOT NULL COMMENT '任务描述',
  `condition` varchar(500) NOT NULL COMMENT '完成条件',
  `action` varchar(20) NOT NULL COMMENT '动作',
  `reward` varchar(500) NOT NULL COMMENT '奖励',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yxd_task_account
-- ----------------------------
DROP TABLE IF EXISTS `yxd_task_account`;
CREATE TABLE `yxd_task_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `task_id` int(11) NOT NULL COMMENT '任务id',
  `task_type` tinyint(1) NOT NULL COMMENT '任务类型',
  `status` tinyint(1) NOT NULL COMMENT '任务状态',
  `desc` varchar(500) NOT NULL COMMENT '描述',
  `receive` tinyint(1) NOT NULL COMMENT '是否已经领取奖品',
  `ctime` int(11) NOT NULL COMMENT '领取任务时间',
  PRIMARY KEY (`id`),
  KEY `mytask` (`uid`,`status`,`ctime`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
