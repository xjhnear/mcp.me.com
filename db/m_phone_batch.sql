/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-02 17:14:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_phone_batch
-- ----------------------------
DROP TABLE IF EXISTS `m_phone_batch`;
CREATE TABLE `m_phone_batch` (
  `batch_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `batch_code` varchar(50) NOT NULL DEFAULT '',
  `count` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `index_batch_code` (`batch_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_batch
-- ----------------------------
INSERT INTO `m_phone_batch` VALUES ('1', '123123', '0', '1509613027', '1509613027');
INSERT INTO `m_phone_batch` VALUES ('2', '123123111', '4', '1509613948', '1509613949');
