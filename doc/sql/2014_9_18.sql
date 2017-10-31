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

Date: 2014-09-18 16:25:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `m_xyx_game`
-- ----------------------------
CREATE TABLE `m_xyx_game` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `gamename` varchar(50) DEFAULT NULL COMMENT '游戏名称',
  `phrase` varchar(250) DEFAULT NULL COMMENT '游戏短语',
  `introduced` varchar(250) DEFAULT NULL COMMENT '游戏介绍',
  `instructions` varchar(250) DEFAULT NULL COMMENT '操作说明',
  `litpic` varchar(250) DEFAULT NULL COMMENT '缩略图',
  `tid` int(11) DEFAULT NULL COMMENT '类型',
  `senddate` int(11) DEFAULT NULL COMMENT '发布时间',
  `editorrecommend` tinyint(4) DEFAULT NULL COMMENT '编辑推荐',
  `hot` tinyint(4) DEFAULT NULL COMMENT '热门',
  `gameaddress` varchar(250) DEFAULT NULL COMMENT '游戏地址',
  `editorsort` int(11) DEFAULT NULL COMMENT '编辑排序',
  `hotsort` int(11) DEFAULT NULL COMMENT '热门排序',
  `newsort` int(11) DEFAULT NULL COMMENT '最新排序',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小游戏 游戏详情表';


-- ----------------------------
-- Table structure for `m_xyx_pic`
-- ----------------------------

CREATE TABLE `m_xyx_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '图片ID',
  `url` varchar(250) DEFAULT NULL COMMENT '图片地址',
  `gid` int(11) DEFAULT NULL COMMENT '游戏id',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小游戏 关联图片表';

-- ----------------------------
-- Table structure for `m_xyx_type`
-- ----------------------------
CREATE TABLE `m_xyx_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '类别ID',
  `title` varchar(250) DEFAULT NULL COMMENT '类别名',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='小游戏 游戏类型表';

-- ----------------------------
-- Records of m_xyx_type
-- ----------------------------
INSERT INTO `m_xyx_type` VALUES ('1', '动作', '1');
INSERT INTO `m_xyx_type` VALUES ('2', '运动', '2');
INSERT INTO `m_xyx_type` VALUES ('3', '射击', '3');
INSERT INTO `m_xyx_type` VALUES ('4', '益智', '4');
INSERT INTO `m_xyx_type` VALUES ('5', '棋牌', '5');
INSERT INTO `m_xyx_type` VALUES ('6', '角色扮演', '6');
INSERT INTO `m_xyx_type` VALUES ('7', '街机', '7');
