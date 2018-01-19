/*
Navicat MySQL Data Transfer

Source Server         : 47.100.111.70
Source Server Version : 50720
Source Host           : 47.100.111.70:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50720
File Encoding         : 65001

Date: 2018-01-18 17:44:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_category
-- ----------------------------
DROP TABLE IF EXISTS `m_category`;
CREATE TABLE `m_category` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) DEFAULT NULL COMMENT '分类名',
  `count` int(20) DEFAULT '0' COMMENT '数量',
  `unicom` int(20) DEFAULT '0' COMMENT '联通',
  `mobile` int(20) DEFAULT '0' COMMENT '移动',
  `telecom` int(20) DEFAULT '0' COMMENT '电信',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_category
-- ----------------------------
INSERT INTO `m_category` VALUES ('1', '无分类', '132627', '0', '0', '0', '1511431037', '1516268093');
INSERT INTO `m_category` VALUES ('9', '1', '223661', '0', '0', '0', '1516268050', '1516268094');
