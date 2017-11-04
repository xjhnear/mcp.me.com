/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-04 12:25:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_phone_numbers
-- ----------------------------
DROP TABLE IF EXISTS `m_phone_numbers`;
CREATE TABLE `m_phone_numbers` (
  `num_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '手机号ID',
  `batch_id` int(11) NOT NULL DEFAULT '0' COMMENT '批次ID',
  `phone_number` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '运营商',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '城市',
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`num_id`),
  KEY `index_batch_id` (`batch_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_numbers
-- ----------------------------
INSERT INTO `m_phone_numbers` VALUES ('1', '1', '1231', '', '', '', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('2', '1', '12123', '', '', '', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('3', '1', '123', '', '', '', '1509613639', '1509613639');
INSERT INTO `m_phone_numbers` VALUES ('4', '1', '12334', '', '', '', '1509613640', '1509613640');
INSERT INTO `m_phone_numbers` VALUES ('5', '2', '1231', '', '', '', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('6', '2', '12123', '', '', '', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('7', '2', '123', '', '', '', '1509613948', '1509613948');
INSERT INTO `m_phone_numbers` VALUES ('8', '2', '12334', '', '', '', '1509613948', '1509613948');
