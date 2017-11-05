/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-05 15:27:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_phone_batch
-- ----------------------------
DROP TABLE IF EXISTS `m_phone_batch`;
CREATE TABLE `m_phone_batch` (
  `batch_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '批次ID',
  `batch_code` varchar(50) NOT NULL DEFAULT '' COMMENT '批次Code',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '数据量',
  `coefficient` varchar(50) NOT NULL DEFAULT '' COMMENT '系数',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '修改时间',
  `down_at` int(11) unsigned DEFAULT NULL COMMENT '导出时间',
  `is_new` int(6) NOT NULL DEFAULT '1' COMMENT '是否新批次',
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `index_batch_code` (`batch_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_batch
-- ----------------------------
INSERT INTO `m_phone_batch` VALUES ('6', '2222222222222', '20195', '1.0', '1509854303', '1509866140', '1509854768', '0');
