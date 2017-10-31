/*
MySQL Data Transfer
Source Host: localhost
Source Database: yxd_www
Target Host: localhost
Target Database: yxd_www
Date: 2014/2/28 14:36:48
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for m_admin
-- ----------------------------
DROP TABLE IF EXISTS `m_admin`;
CREATE TABLE `m_admin` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `role` varchar(20) NOT NULL,
  `authorname` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `addtime` int(10) NOT NULL,
  `realname` varchar(20) NOT NULL DEFAULT '',
  `isopen` tinyint(1) DEFAULT '1' COMMENT '是否启用 1=启用,2=禁用',
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_adv
-- ----------------------------
DROP TABLE IF EXISTS `m_adv`;
CREATE TABLE `m_adv` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `isstarting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首发',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门',
  `link_id` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `litpic` varchar(255) NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` tinyint(2) NOT NULL DEFAULT '0',
  `tab` tinyint(1) NOT NULL DEFAULT '0',
  `appname` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=349 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_adv_app
-- ----------------------------
DROP TABLE IF EXISTS `m_adv_app`;
CREATE TABLE `m_adv_app` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `gname` varchar(100) NOT NULL COMMENT '游戏名称',
  `litpic` varchar(255) NOT NULL COMMENT '广告图',
  `words` varchar(255) NOT NULL COMMENT '广告语',
  `downurl` varchar(200) NOT NULL,
  `editor` varchar(30) NOT NULL DEFAULT '',
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  `isshow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示1=显示，2=隐藏',
  `extend` varchar(30) NOT NULL DEFAULT '',
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `isshow` (`isshow`),
  KEY `sort` (`addtime`,`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_adv_state
-- ----------------------------
DROP TABLE IF EXISTS `m_adv_state`;
CREATE TABLE `m_adv_state` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `hits` int(11) NOT NULL,
  `activations` int(11) NOT NULL,
  `source` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=299 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_adv_tapjoy
-- ----------------------------
DROP TABLE IF EXISTS `m_adv_tapjoy`;
CREATE TABLE `m_adv_tapjoy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `add_date` date NOT NULL,
  `macaddress` varchar(150) NOT NULL,
  `device` varchar(30) NOT NULL,
  `type` varchar(20) NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=298 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_adv_user
-- ----------------------------
DROP TABLE IF EXISTS `m_adv_user`;
CREATE TABLE `m_adv_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mac` varchar(30) NOT NULL,
  `addtime` int(11) unsigned NOT NULL,
  `updatetime` int(11) DEFAULT NULL,
  `source` varchar(20) DEFAULT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `callback` varchar(200) NOT NULL DEFAULT '',
  `idfa` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `mac` (`mac`,`source`)
) ENGINE=MyISAM AUTO_INCREMENT=511928 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_appadv
-- ----------------------------
DROP TABLE IF EXISTS `m_appadv`;
CREATE TABLE `m_appadv` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '广告位置类型',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `aid` varchar(50) NOT NULL DEFAULT '' COMMENT '广告标识',
  `appname` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  `advname` varchar(20) NOT NULL DEFAULT '' COMMENT '广告应用名称',
  `litpic` varchar(255) NOT NULL DEFAULT '',
  `bigpic` varchar(255) NOT NULL DEFAULT '' COMMENT '启动广告大图',
  `downurl` varchar(255) NOT NULL DEFAULT '' COMMENT '下载地址',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '第三方地址',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '广告位置',
  `tosafari` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0使用内置浏览器1跳转safari',
  `sendmac` varchar(100) NOT NULL DEFAULT '' COMMENT '第三方mac',
  `sendidfa` varchar(100) NOT NULL DEFAULT '' COMMENT '第三方idfa',
  `sendudid` varchar(100) NOT NULL DEFAULT '' COMMENT '第三方openudid',
  `sendos` varchar(100) NOT NULL DEFAULT '',
  `sendplat` varchar(100) NOT NULL DEFAULT '',
  `sendactive` varchar(100) NOT NULL DEFAULT '' COMMENT 'callback',
  `editor` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_appadv_active_stat
-- ----------------------------
DROP TABLE IF EXISTS `m_appadv_active_stat`;
CREATE TABLE `m_appadv_active_stat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` varchar(50) NOT NULL DEFAULT '' COMMENT '广告标识',
  `code` varchar(50) NOT NULL DEFAULT '',
  `idfa` varchar(100) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `aid_addtime` (`aid`,`addtime`),
  KEY `code` (`code`),
  KEY `idfa` (`idfa`)
) ENGINE=MyISAM AUTO_INCREMENT=12290 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_appadv_stat
-- ----------------------------
DROP TABLE IF EXISTS `m_appadv_stat`;
CREATE TABLE `m_appadv_stat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appname` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '广告位置',
  `iosversion` varchar(40) NOT NULL DEFAULT '' COMMENT 'ios版本',
  `aid` varchar(50) NOT NULL DEFAULT '' COMMENT '广告标识',
  `code` varchar(50) NOT NULL DEFAULT '',
  `idfa` varchar(100) NOT NULL DEFAULT '',
  `openudid` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '判断点击的类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '其他类型的id',
  `number` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1724103 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_comment
-- ----------------------------
DROP TABLE IF EXISTS `m_comment`;
CREATE TABLE `m_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcid` int(11) NOT NULL DEFAULT '0',
  `ptype` tinyint(1) unsigned DEFAULT '0' COMMENT 'è¯„è®ºç±»åž‹ 0=æ‰‹æœºè¯„è®º,1=pcè¯„è®º',
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `nick` varchar(32) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `up` int(5) NOT NULL DEFAULT '0',
  `down` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `addtime` (`addtime`),
  KEY `pcid` (`pcid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=241002 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_configs
-- ----------------------------
DROP TABLE IF EXISTS `m_configs`;
CREATE TABLE `m_configs` (
  `name` char(25) NOT NULL,
  `value` char(200) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_count_one
-- ----------------------------
DROP TABLE IF EXISTS `m_count_one`;
CREATE TABLE `m_count_one` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `add_time` int(10) NOT NULL,
  `entry` tinyint(3) NOT NULL,
  `number` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=10222 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_count_three
-- ----------------------------
DROP TABLE IF EXISTS `m_count_three`;
CREATE TABLE `m_count_three` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iid` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `add_time` int(10) NOT NULL,
  `entry` tinyint(3) NOT NULL,
  `number` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=78417 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_count_two
-- ----------------------------
DROP TABLE IF EXISTS `m_count_two`;
CREATE TABLE `m_count_two` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `add_time` int(10) NOT NULL,
  `entry` tinyint(3) NOT NULL,
  `number` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=44626 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_feedback
-- ----------------------------
DROP TABLE IF EXISTS `m_feedback`;
CREATE TABLE `m_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `ftitle` varchar(100) NOT NULL,
  `writer` char(20) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `litpic` varchar(60) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `gid` (`gid`),
  KEY `sort` (`sort`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=1997 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_crawl
-- ----------------------------
DROP TABLE IF EXISTS `m_game_crawl`;
CREATE TABLE `m_game_crawl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itunesid` int(11) NOT NULL,
  `price` float(10,2) DEFAULT NULL,
  `gname` varchar(30) DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `size` varchar(30) DEFAULT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `itunesid` (`itunesid`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4909 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_download_count
-- ----------------------------
DROP TABLE IF EXISTS `m_game_download_count`;
CREATE TABLE `m_game_download_count` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) unsigned NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `down_time` int(10) unsigned NOT NULL,
  `number` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `down_time` (`down_time`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=237077 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_gao7
-- ----------------------------
DROP TABLE IF EXISTS `m_game_gao7`;
CREATE TABLE `m_game_gao7` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itunesid` int(11) NOT NULL,
  `gname` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `oldprice` int(11) NOT NULL,
  `page` int(6) NOT NULL,
  `url` varchar(100) NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `itunesid` (`itunesid`),
  KEY `price` (`price`),
  KEY `oldprice` (`oldprice`)
) ENGINE=MyISAM AUTO_INCREMENT=102620 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_mustplay
-- ----------------------------
DROP TABLE IF EXISTS `m_game_mustplay`;
CREATE TABLE `m_game_mustplay` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL,
  `pic` varchar(100) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `sort` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `sort` (`sort`),
  KEY `addtime` (`addtime`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_notice
-- ----------------------------
DROP TABLE IF EXISTS `m_game_notice`;
CREATE TABLE `m_game_notice` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `gname` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `notice_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:新游预告,2:活动',
  `editor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `platform` varchar(30) NOT NULL,
  `company` varchar(30) NOT NULL,
  `art_content` text NOT NULL,
  `content` text NOT NULL,
  `video_url` varchar(100) NOT NULL,
  `video_pic` varchar(50) NOT NULL,
  `date` varchar(30) NOT NULL,
  `pic` varchar(30) NOT NULL,
  `litpic` varchar(60) DEFAULT '',
  `art_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:老版,2:新版',
  `apptype` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:显示,2:隐藏',
  `sort` int(6) NOT NULL DEFAULT '0',
  `adddate` date NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=775 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_recommend
-- ----------------------------
DROP TABLE IF EXISTS `m_game_recommend`;
CREATE TABLE `m_game_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('h','g') NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `addtime` (`addtime`),
  KEY `sort` (`sort`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_score
-- ----------------------------
DROP TABLE IF EXISTS `m_game_score`;
CREATE TABLE `m_game_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `score` float(2,1) NOT NULL DEFAULT '0.0',
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `uid` (`uid`),
  KEY `addtime` (`addtime`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=138816 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_game_type
-- ----------------------------
DROP TABLE IF EXISTS `m_game_type`;
CREATE TABLE `m_game_type` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `typename` varchar(20) NOT NULL,
  `img` varchar(100) NOT NULL,
  `isapptop` tinyint(1) NOT NULL DEFAULT '0',
  `updatetime` int(10) NOT NULL,
  `sort` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `isapptop` (`isapptop`),
  KEY `updatetime` (`updatetime`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games
-- ----------------------------
DROP TABLE IF EXISTS `m_games`;
CREATE TABLE `m_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itunesid` int(11) NOT NULL,
  `schemesurl` varchar(100) NOT NULL DEFAULT '',
  `gname` varchar(100) NOT NULL,
  `shortgname` varchar(50) NOT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `pricetype` tinyint(1) NOT NULL DEFAULT '0',
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `version` varchar(20) NOT NULL,
  `size` varchar(30) NOT NULL,
  `score` float(2,1) NOT NULL DEFAULT '0.0',
  `language` varchar(30) NOT NULL,
  `downurl` varchar(200) NOT NULL,
  `apkurl` varchar(255) DEFAULT '',
  `platform` varchar(200) NOT NULL,
  `company` varchar(100) NOT NULL,
  `shortcomt` varchar(30) NOT NULL,
  `editorcomt` text NOT NULL,
  `description` text NOT NULL,
  `ico` varchar(200) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `zonetype` tinyint(1) NOT NULL DEFAULT '0',
  `isdel` tinyint(1) NOT NULL DEFAULT '0',
  `viewscore` float(2,1) unsigned NOT NULL DEFAULT '0.0',
  `funnyscore` float(2,1) NOT NULL DEFAULT '0.0',
  `smoothscore` float(2,1) NOT NULL DEFAULT '0.0',
  `istop` tinyint(1) NOT NULL DEFAULT '0',
  `advpic` varchar(200) NOT NULL DEFAULT '',
  `isapptop` tinyint(1) NOT NULL DEFAULT '0',
  `isstarting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首发',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门',
  `isup` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否上架1=已上架,0=未上架',
  `updatetime` int(10) NOT NULL DEFAULT '0',
  `oldprice` float(10,2) NOT NULL DEFAULT '0.00',
  `downtimes` int(10) NOT NULL DEFAULT '1',
  `weekdown` int(11) NOT NULL COMMENT '每周下载',
  `realdown` int(11) NOT NULL DEFAULT '0' COMMENT 'çœŸå®žä¸‹è½½æ•°æ®',
  `downrand` int(10) NOT NULL DEFAULT '1',
  `recommendsort` tinyint(6) NOT NULL COMMENT '推荐专区排序',
  `sort` int(6) NOT NULL COMMENT '精品排序',
  `commenttimes` int(11) unsigned NOT NULL DEFAULT '0',
  `gnameletter` varchar(30) DEFAULT NULL,
  `updatestate` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1:成功,2:失败',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `flag` (`flag`),
  KEY `zonetype` (`zonetype`),
  KEY `isdel` (`isdel`),
  KEY `updatetime` (`updatetime`),
  KEY `pricetype` (`pricetype`),
  KEY `sort` (`sort`),
  KEY `score` (`score`),
  KEY `itunesid` (`itunesid`),
  KEY `zonetype_2` (`zonetype`)
) ENGINE=MyISAM AUTO_INCREMENT=17100 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_apk
-- ----------------------------
DROP TABLE IF EXISTS `m_games_apk`;
CREATE TABLE `m_games_apk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gname` varchar(100) NOT NULL,
  `shortgname` varchar(50) NOT NULL,
  `igid` int(10) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `pricetype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `version` varchar(20) NOT NULL,
  `size` varchar(30) NOT NULL,
  `score` float(2,1) NOT NULL DEFAULT '0.0',
  `language` varchar(30) NOT NULL,
  `apkurl` varchar(255) NOT NULL,
  `platform` varchar(200) NOT NULL,
  `company` varchar(100) NOT NULL,
  `shortcomt` varchar(30) NOT NULL,
  `editorcomt` text NOT NULL,
  `description` text NOT NULL,
  `ico` varchar(200) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `zonetype` tinyint(1) NOT NULL DEFAULT '0',
  `isdel` tinyint(1) NOT NULL DEFAULT '0',
  `viewscore` float(2,1) unsigned NOT NULL DEFAULT '0.0',
  `funnyscore` float(2,1) NOT NULL DEFAULT '0.0',
  `smoothscore` float(2,1) NOT NULL DEFAULT '0.0',
  `istop` tinyint(1) NOT NULL DEFAULT '0',
  `advpic` varchar(200) NOT NULL DEFAULT '',
  `pics` text NOT NULL COMMENT '游戏截图',
  `isapptop` tinyint(1) NOT NULL DEFAULT '0',
  `isstarting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首发',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门',
  `isup` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否上架1=已上架,0=未上架',
  `updatetime` int(10) NOT NULL DEFAULT '0',
  `downtimes` int(10) NOT NULL DEFAULT '1',
  `weekdown` int(11) NOT NULL COMMENT '每周下载',
  `realdown` int(11) NOT NULL DEFAULT '0' COMMENT '真实下载次数',
  `recommendsort` tinyint(6) NOT NULL COMMENT '推荐专区排序',
  `sort` int(6) NOT NULL COMMENT '精品排序',
  `commenttimes` int(11) unsigned NOT NULL DEFAULT '0',
  `gnameletter` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `flag` (`flag`),
  KEY `zonetype` (`zonetype`),
  KEY `isdel` (`isdel`),
  KEY `sort` (`sort`),
  KEY `score` (`score`),
  KEY `pricetype` (`pricetype`)
) ENGINE=MyISAM AUTO_INCREMENT=2361 DEFAULT CHARSET=utf8 COMMENT='android 游戏';

-- ----------------------------
-- Table structure for m_games_infopic
-- ----------------------------
DROP TABLE IF EXISTS `m_games_infopic`;
CREATE TABLE `m_games_infopic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '0',
  `litpic` varchar(200) NOT NULL,
  `sort` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `linkurl` varchar(200) DEFAULT '' COMMENT '链接地址',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_litpic
-- ----------------------------
DROP TABLE IF EXISTS `m_games_litpic`;
CREATE TABLE `m_games_litpic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `litpic` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=70917 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_map
-- ----------------------------
DROP TABLE IF EXISTS `m_games_map`;
CREATE TABLE `m_games_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `igid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ios游戏id',
  `agid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'android游戏id',
  PRIMARY KEY (`id`),
  KEY `igid` (`igid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=528 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_new
-- ----------------------------
DROP TABLE IF EXISTS `m_games_new`;
CREATE TABLE `m_games_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itunesid` int(11) NOT NULL,
  `gname` varchar(100) NOT NULL,
  `pricetype` tinyint(1) NOT NULL DEFAULT '0',
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `oldprice` float(10,2) NOT NULL,
  `version` varchar(20) NOT NULL,
  `size` varchar(30) NOT NULL,
  `score` float(2,1) DEFAULT '0.0',
  `language` varchar(30) NOT NULL,
  `downurl` varchar(200) NOT NULL,
  `platform` varchar(200) NOT NULL,
  `company` varchar(100) NOT NULL,
  `editorcomt` text NOT NULL,
  `description` text NOT NULL,
  `ico` varchar(200) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `itunesid_2` (`itunesid`),
  KEY `addtime` (`addtime`),
  KEY `pricetype` (`pricetype`),
  KEY `score` (`score`),
  KEY `itunesid` (`itunesid`)
) ENGINE=MyISAM AUTO_INCREMENT=9104 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_newpic
-- ----------------------------
DROP TABLE IF EXISTS `m_games_newpic`;
CREATE TABLE `m_games_newpic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `litpic` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=38148 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_tag
-- ----------------------------
DROP TABLE IF EXISTS `m_games_tag`;
CREATE TABLE `m_games_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `tag` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=66598 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_games_video
-- ----------------------------
DROP TABLE IF EXISTS `m_games_video`;
CREATE TABLE `m_games_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `video` varchar(250) NOT NULL,
  `ico` varchar(200) NOT NULL DEFAULT '',
  `writer` varchar(20) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `addtime` int(10) NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `viewtimes` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'æ’­æ”¾æ¬¡æ•°',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `addtime` (`addtime`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=5232 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_gift
-- ----------------------------
DROP TABLE IF EXISTS `m_gift`;
CREATE TABLE `m_gift` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `writer` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `gid` int(10) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `gname` varchar(50) DEFAULT NULL,
  `video_url` varchar(100) DEFAULT NULL,
  `video_pic` varchar(50) DEFAULT NULL,
  `pic` varchar(50) DEFAULT NULL,
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示1:显示,2:不显示',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `apptype` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型(多选)',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `adddate` date NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `ispriority` tinyint(3) NOT NULL DEFAULT '0' COMMENT '优先级',
  `total_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总数量',
  `last_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余数量',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `isshow` (`isshow`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=471 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_gift_card
-- ----------------------------
DROP TABLE IF EXISTS `m_gift_card`;
CREATE TABLE `m_gift_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gfid` int(10) NOT NULL,
  `uid` int(10) DEFAULT '0',
  `number` varchar(60) NOT NULL COMMENT '卡号',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '导入时间',
  `gettime` int(10) DEFAULT '0' COMMENT '领取时间',
  `ctype` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `isget` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gfid` (`gfid`),
  KEY `uid` (`uid`),
  KEY `ctype` (`ctype`,`isget`)
) ENGINE=MyISAM AUTO_INCREMENT=252241 DEFAULT CHARSET=utf8 COMMENT='礼包卡';

-- ----------------------------
-- Table structure for m_gonglue
-- ----------------------------
DROP TABLE IF EXISTS `m_gonglue`;
CREATE TABLE `m_gonglue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gtitle` varchar(100) NOT NULL,
  `writer` char(20) NOT NULL,
  `linkgame` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `gid` (`gid`),
  KEY `sort` (`sort`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=19447 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_gonglue_cwan
-- ----------------------------
DROP TABLE IF EXISTS `m_gonglue_cwan`;
CREATE TABLE `m_gonglue_cwan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gtitle` varchar(100) NOT NULL,
  `writer` char(20) NOT NULL,
  `linkgame` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `gid` (`gid`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=195 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_hot_activity
-- ----------------------------
DROP TABLE IF EXISTS `m_hot_activity`;
CREATE TABLE `m_hot_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `writer` varchar(30) DEFAULT NULL,
  `type` varchar(30) NOT NULL COMMENT '活动类型',
  `content` text NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `gid` int(10) DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `gname` varchar(50) NOT NULL DEFAULT '',
  `video_url` varchar(100) DEFAULT NULL,
  `video_pic` varchar(50) DEFAULT NULL,
  `pic` varchar(50) NOT NULL DEFAULT '',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示1:显示,2:不显示',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `apptype` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型(多选)',
  `starttime` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `sort` int(6) NOT NULL DEFAULT '0',
  `adddate` date NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `ispriority` tinyint(3) NOT NULL DEFAULT '0' COMMENT '优先级',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `isshow` (`isshow`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_log
-- ----------------------------
DROP TABLE IF EXISTS `m_log`;
CREATE TABLE `m_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `arc_type` smallint(2) NOT NULL,
  `arc_author` varchar(20) NOT NULL,
  `arc_id` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `userip` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`)
) ENGINE=MyISAM AUTO_INCREMENT=3158 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_newgame
-- ----------------------------
DROP TABLE IF EXISTS `m_newgame`;
CREATE TABLE `m_newgame` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏状态',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `istop` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `isfirst` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否首发',
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_news
-- ----------------------------
DROP TABLE IF EXISTS `m_news`;
CREATE TABLE `m_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  `writer` char(20) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `litpic` varchar(60) DEFAULT '',
  `zxshow` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'h5资讯显示 1=显示,0=不显示',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `gid` (`gid`),
  KEY `zxshow` (`zxshow`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=3279 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_ptbus_games
-- ----------------------------
DROP TABLE IF EXISTS `m_ptbus_games`;
CREATE TABLE `m_ptbus_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itunesid` int(11) NOT NULL DEFAULT '0',
  `gname` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏名称',
  `shortgname` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏简称',
  `flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主页推荐',
  `pricetype` varchar(20) NOT NULL DEFAULT '0' COMMENT '价格类型 ',
  `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `type` varchar(20) NOT NULL DEFAULT '0' COMMENT '游戏类型',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '版本',
  `size` varchar(30) NOT NULL DEFAULT '' COMMENT '大小',
  `score` float(2,1) NOT NULL DEFAULT '0.0' COMMENT '评分 ',
  `language` varchar(30) NOT NULL DEFAULT '' COMMENT '语言',
  `downurl` varchar(200) NOT NULL DEFAULT '' COMMENT '下载地址',
  `apkurl` varchar(200) NOT NULL DEFAULT '',
  `sourceurl` varchar(200) NOT NULL DEFAULT '' COMMENT '抓取地址',
  `platform` varchar(200) NOT NULL DEFAULT '' COMMENT '兼容平台',
  `company` varchar(100) NOT NULL DEFAULT '' COMMENT '开发商',
  `shortcomt` varchar(30) NOT NULL DEFAULT '' COMMENT '短评',
  `editorcomt` text COMMENT '游戏介绍',
  `description` text COMMENT '抓取的游戏介绍',
  `ico` varchar(200) NOT NULL DEFAULT '' COMMENT '游戏图标',
  `addtime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `editor` int(10) NOT NULL DEFAULT '0' COMMENT '编辑者',
  `zonetype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '游戏专区',
  `isdel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `viewscore` float(2,1) NOT NULL DEFAULT '0.0' COMMENT '画面评分',
  `funnyscore` float(2,1) NOT NULL DEFAULT '0.0' COMMENT '趣味性评分',
  `smoothscore` float(2,1) NOT NULL DEFAULT '0.0' COMMENT '流畅性评分',
  `advpic` varchar(200) NOT NULL DEFAULT '' COMMENT '推荐位图片',
  `isapptop` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP置顶',
  `isstarting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首发推荐',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '热门推荐',
  `updatetime` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `oldprice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '历史价格',
  `downtimes` int(10) NOT NULL DEFAULT '1' COMMENT '下载次数',
  `weekdown` int(10) NOT NULL DEFAULT '0' COMMENT '每周下载',
  `recommendsort` int(6) NOT NULL DEFAULT '0' COMMENT '推荐专区排序',
  `sort` int(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `updatestate` tinyint(1) NOT NULL DEFAULT '2' COMMENT '同步状态(1成功,2失败)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_recommend
-- ----------------------------
DROP TABLE IF EXISTS `m_recommend`;
CREATE TABLE `m_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(100) NOT NULL,
  `ico` varchar(200) NOT NULL,
  `downurl` varchar(200) NOT NULL,
  `apkurl` varchar(255) NOT NULL,
  `sort` int(4) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL,
  `editor` int(4) NOT NULL,
  `apptype` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型',
  PRIMARY KEY (`id`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_search_tag
-- ----------------------------
DROP TABLE IF EXISTS `m_search_tag`;
CREATE TABLE `m_search_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(30) NOT NULL,
  `sort` int(6) NOT NULL DEFAULT '0',
  `hits` int(10) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_send_message
-- ----------------------------
DROP TABLE IF EXISTS `m_send_message`;
CREATE TABLE `m_send_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `appname` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:游戏推荐,2:精彩视频,3:专题,4:新游预告',
  `link_id` int(11) NOT NULL DEFAULT '0',
  `message` varchar(200) NOT NULL,
  `editor` int(11) NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`)
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_share
-- ----------------------------
DROP TABLE IF EXISTS `m_share`;
CREATE TABLE `m_share` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(3) unsigned NOT NULL COMMENT '分享类型',
  `weibo` text NOT NULL COMMENT '微博内容',
  `weixin` varchar(255) NOT NULL DEFAULT '' COMMENT '微信内容',
  `append` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  PRIMARY KEY (`id`),
  KEY `tid` (`typeid`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_spread_activity
-- ----------------------------
DROP TABLE IF EXISTS `m_spread_activity`;
CREATE TABLE `m_spread_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `gname` varchar(50) NOT NULL DEFAULT '',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `linktype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '关联类型1=活动,2=礼包',
  `linkid` int(10) unsigned NOT NULL DEFAULT '0',
  `pic` varchar(50) NOT NULL DEFAULT '',
  `downurl` varchar(150) NOT NULL,
  `adv_pic` varchar(50) NOT NULL DEFAULT '',
  `adv_url` varchar(100) NOT NULL DEFAULT '',
  `editor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `writer` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `starttime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0',
  `isshow` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示1:显示,2:不显示',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `isshow` (`isshow`),
  KEY `gid` (`gid`),
  KEY `linktype` (`linktype`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_spread_article
-- ----------------------------
DROP TABLE IF EXISTS `m_spread_article`;
CREATE TABLE `m_spread_article` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `writer` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示1:显示,2:不显示',
  `sort` int(6) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `isshow` (`isshow`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_spread_comment
-- ----------------------------
DROP TABLE IF EXISTS `m_spread_comment`;
CREATE TABLE `m_spread_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `said` int(11) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `litpic` text,
  `ip` varchar(50) DEFAULT NULL,
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `said` (`said`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_sync_log
-- ----------------------------
DROP TABLE IF EXISTS `m_sync_log`;
CREATE TABLE `m_sync_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `art_type` varchar(20) NOT NULL DEFAULT '' COMMENT '文章类型 game,guide,news,opinion',
  `opt_type` varchar(20) NOT NULL DEFAULT '' COMMENT '同步操作类型 add:添加,del:删除',
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '信息id',
  `oldaid` int(10) DEFAULT '0' COMMENT '移动栏目之前的信息id',
  `old_arttype` varchar(20) DEFAULT '',
  `issucc` tinyint(2) DEFAULT '0' COMMENT '是否同步成功 0=失败,1成功',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='数据同步失败信息日志';

-- ----------------------------
-- Table structure for m_system_feedback
-- ----------------------------
DROP TABLE IF EXISTS `m_system_feedback`;
CREATE TABLE `m_system_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback` text NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `appver` char(50) NOT NULL DEFAULT '',
  `iosver` char(50) NOT NULL DEFAULT '',
  `ostype` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2418 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_tag
-- ----------------------------
DROP TABLE IF EXISTS `m_tag`;
CREATE TABLE `m_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(3) NOT NULL DEFAULT '0',
  `tag` varchar(20) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `typeid` (`typeid`)
) ENGINE=MyISAM AUTO_INCREMENT=959 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_tmp_adv
-- ----------------------------
DROP TABLE IF EXISTS `m_tmp_adv`;
CREATE TABLE `m_tmp_adv` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `sort` tinyint(2) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  `des` varchar(100) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:首页轮播2:内容页轮播',
  `art_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文章类型1:评测,2:攻略,3:新闻',
  `link_id` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `litpic` varchar(255) NOT NULL,
  `tab` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_token
-- ----------------------------
DROP TABLE IF EXISTS `m_token`;
CREATE TABLE `m_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appname` varchar(30) NOT NULL DEFAULT '',
  `token` varchar(100) NOT NULL,
  `curversion` varchar(40) NOT NULL,
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  `push_time` int(10) NOT NULL DEFAULT '0',
  `issend` tinyint(3) NOT NULL DEFAULT '0',
  `errmsg` varchar(30) NOT NULL DEFAULT '',
  `is_valid` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `appname` (`appname`),
  KEY `is_valid` (`is_valid`),
  KEY `appname_2` (`appname`,`token`,`curversion`,`is_valid`)
) ENGINE=MyISAM AUTO_INCREMENT=287882 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_tools
-- ----------------------------
DROP TABLE IF EXISTS `m_tools`;
CREATE TABLE `m_tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(60) NOT NULL,
  `code` text NOT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_user
-- ----------------------------
DROP TABLE IF EXISTS `m_user`;
CREATE TABLE `m_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passwd` char(32) NOT NULL,
  `nick` varchar(32) NOT NULL,
  `avatar` varchar(200) NOT NULL,
  `email` varchar(32) NOT NULL,
  `user_type` tinyint(3) NOT NULL DEFAULT '0',
  `gender` tinyint(1) NOT NULL DEFAULT '1',
  `phone` varchar(20) NOT NULL,
  `code` char(32) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `islock` tinyint(1) NOT NULL DEFAULT '0',
  `curchannel` varchar(30) NOT NULL,
  `iosversion` varchar(40) NOT NULL DEFAULT '',
  `curversion` varchar(40) NOT NULL DEFAULT '',
  `token` varchar(100) NOT NULL DEFAULT '',
  `openudid` varchar(100) NOT NULL DEFAULT '',
  `idfa` varchar(100) NOT NULL DEFAULT '',
  `validtoken` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:æœ‰æ•ˆ,2:æ— æ•ˆ',
  `logintime` int(10) NOT NULL,
  `pushtime` int(10) DEFAULT NULL COMMENT 'æŽ¨é€æ—¶é—´',
  `rand_v` int(6) DEFAULT NULL COMMENT 'éšæœºç‰ˆæœ¬',
  `issend` tinyint(2) NOT NULL COMMENT '1:æˆåŠŸ',
  `errmsg` varchar(30) DEFAULT NULL,
  `vuser` tinyint(1) NOT NULL DEFAULT '0' COMMENT '虚拟用户1:为虚拟用户',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `code` (`code`),
  KEY `issend` (`issend`),
  KEY `idfa` (`idfa`),
  KEY `token` (`token`),
  KEY `openudid` (`openudid`)
) ENGINE=MyISAM AUTO_INCREMENT=590120 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_user_fav
-- ----------------------------
DROP TABLE IF EXISTS `m_user_fav`;
CREATE TABLE `m_user_fav` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_user_gift
-- ----------------------------
DROP TABLE IF EXISTS `m_user_gift`;
CREATE TABLE `m_user_gift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gfid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼包id',
  `gcid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼包卡id',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `number` varchar(50) NOT NULL DEFAULT '' COMMENT '礼包卡号',
  `gettime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `gfid` (`gfid`),
  KEY `gcid` (`gcid`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=297602 DEFAULT CHARSET=utf8 COMMENT='用户礼包';

-- ----------------------------
-- Table structure for m_user_privilege
-- ----------------------------
DROP TABLE IF EXISTS `m_user_privilege`;
CREATE TABLE `m_user_privilege` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(20) NOT NULL DEFAULT '',
  `privilege` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_version
-- ----------------------------
DROP TABLE IF EXISTS `m_version`;
CREATE TABLE `m_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(20) NOT NULL DEFAULT '',
  `channel` varchar(50) NOT NULL DEFAULT '',
  `appstoreurl` varchar(200) NOT NULL,
  `betaopen` varchar(30) NOT NULL,
  `rateopen` varchar(30) NOT NULL,
  `versionstate` tinyint(1) NOT NULL COMMENT '版本状态:1开启，2关闭',
  `scorestate` tinyint(1) NOT NULL COMMENT '评论：1开启，2关闭',
  `version` varchar(10) NOT NULL,
  `append` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `editor` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_videos
-- ----------------------------
DROP TABLE IF EXISTS `m_videos`;
CREATE TABLE `m_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vname` varchar(100) NOT NULL,
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `litpic` varchar(200) NOT NULL,
  `writer` char(20) NOT NULL,
  `video` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `linkgame` varchar(100) NOT NULL,
  `addtime` int(10) NOT NULL,
  `editor` tinyint(3) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `preview` varchar(100) NOT NULL DEFAULT '',
  `score` float(2,1) NOT NULL DEFAULT '0.0',
  `isapptop` tinyint(1) NOT NULL DEFAULT '0',
  `apptype` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型',
  `updatetime` int(10) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL,
  `viewtimes` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `gid` (`gid`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_videos_games
-- ----------------------------
DROP TABLE IF EXISTS `m_videos_games`;
CREATE TABLE `m_videos_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=542 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_zone
-- ----------------------------
DROP TABLE IF EXISTS `m_zone`;
CREATE TABLE `m_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `writer` char(20) NOT NULL,
  `linkurl` varchar(200) NOT NULL DEFAULT '' COMMENT '链接地址',
  `litpic` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(100) DEFAULT NULL,
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(6) NOT NULL DEFAULT '0',
  `gid` int(10) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '类型 0=h5首页精品专区1=app游戏详情按钮',
  `isshow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示1=显示，2=隐藏',
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_zt
-- ----------------------------
DROP TABLE IF EXISTS `m_zt`;
CREATE TABLE `m_zt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ztitle` varchar(100) NOT NULL,
  `flag` tinyint(1) NOT NULL,
  `writer` char(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `addtime` int(10) NOT NULL,
  `litpic` varchar(255) NOT NULL DEFAULT '',
  `editor` tinyint(3) NOT NULL DEFAULT '0',
  `isapptop` tinyint(1) NOT NULL DEFAULT '0',
  `apptype` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '应用类型',
  `updatetime` int(10) NOT NULL DEFAULT '0',
  `viewtimes` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `apptype` (`apptype`)
) ENGINE=MyISAM AUTO_INCREMENT=160 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_zt_games
-- ----------------------------
DROP TABLE IF EXISTS `m_zt_games`;
CREATE TABLE `m_zt_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zt_id` int(11) NOT NULL,
  `linkgame` varchar(200) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `agid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zt_id` (`zt_id`),
  KEY `gid` (`gid`),
  KEY `agid` (`agid`)
) ENGINE=MyISAM AUTO_INCREMENT=8563 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
