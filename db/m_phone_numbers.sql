/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-02 17:14:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_phone_numbers
-- ----------------------------
DROP TABLE IF EXISTS `m_phone_numbers`;
CREATE TABLE `m_phone_numbers` (
  `num_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL DEFAULT '0',
  `phone_number` varchar(20) NOT NULL DEFAULT '',
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  PRIMARY KEY (`num_id`),
  KEY `index_batch_id` (`batch_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_numbers
-- ----------------------------
INSERT INTO `m_phone_numbers` VALUES ('1', '1', '1231', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('2', '1', '12123', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('3', '1', '123', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('4', '1', '12334', '1509613640', '1509613640');
INSERT INTO `m_phone_numbers` VALUES ('5', '2', '1231', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('6', '2', '12123', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('7', '2', '123', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('8', '2', '12334', '1509613948', '1509613948');
