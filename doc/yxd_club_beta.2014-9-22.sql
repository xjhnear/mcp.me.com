/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50533
Source Host           : localhost:3306
Source Database       : yxd_club_beta

Target Server Type    : MYSQL
Target Server Version : 50533
File Encoding         : 65001

Date: 2014-09-22 13:28:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `yxd_today_exchange_account`
-- ----------------------------
DROP TABLE IF EXISTS `yxd_today_exchange_account`;
CREATE TABLE `yxd_today_exchange_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `idfa` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `mac` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Records of yxd_today_exchange_account
-- ----------------------------
