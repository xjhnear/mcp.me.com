/*
Navicat MySQL Data Transfer
autor jiangfengjun
describe 小游戏数据表
Source Server         : localhost
Source Server Version : 50538
Source Host           : localhost:3306
Source Database       : yxd_www

Target Server Type    : MYSQL
Target Server Version : 50538
File Encoding         : 65001

Date: 2014-10-11 18:01:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `m_xyx_game_infopic`
-- ----------------------------

CREATE TABLE `m_xyx_game_infopic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL COMMENT 'title',
  `litpic` varchar(200) NOT NULL COMMENT '图',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `linkurl` varchar(200) DEFAULT '' COMMENT '链接地址',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
