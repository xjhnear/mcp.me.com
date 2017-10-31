/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50621
Source Host           : localhost:3306
Source Database       : cwan_app

Target Server Type    : MYSQL
Target Server Version : 50621
File Encoding         : 65001

Date: 2015-02-05 14:25:20
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `m_end_year_summary`
-- ----------------------------
DROP TABLE IF EXISTS `m_end_year_summary`;
CREATE TABLE `m_end_year_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(11) NOT NULL,
  `audit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nick` (`nick`),
  KEY `addtime` (`addtime`),
  KEY `audit` (`audit`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of m_end_year_summary
-- ----------------------------
