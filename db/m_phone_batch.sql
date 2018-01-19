/*
Navicat MySQL Data Transfer

Source Server         : 47.100.111.70
Source Server Version : 50720
Source Host           : 47.100.111.70:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50720
File Encoding         : 65001

Date: 2018-01-18 17:44:33
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
  `category` int(11) DEFAULT NULL COMMENT '分类',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '修改时间',
  `down_at` int(11) unsigned DEFAULT NULL COMMENT '导出时间',
  `is_new` int(6) NOT NULL DEFAULT '1' COMMENT '是否新批次',
  `unicom` int(20) DEFAULT '0' COMMENT '联通',
  `mobile` int(20) DEFAULT '0' COMMENT '移动',
  `telecom` int(20) DEFAULT '0' COMMENT '电信',
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `index_batch_code` (`batch_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_batch
-- ----------------------------
INSERT INTO `m_phone_batch` VALUES ('31', '1124-CHED-合并', '102304', '', '1', '1511430173', '1511430175', null, '1', '0', '0', '0');
INSERT INTO `m_phone_batch` VALUES ('38', 'SDBS-1', '9827', '', '1', '1511430924', '1511430924', null, '1', '0', '0', '0');
INSERT INTO `m_phone_batch` VALUES ('46', '1123-SDBS1', '20496', '10', '1', '1511431037', '1511431047', null, '1', '0', '0', '0');
INSERT INTO `m_phone_batch` VALUES ('52', 'B1516268050', '143349', '', '9', '1516268051', '1516268057', null, '1', '0', '0', '0');
INSERT INTO `m_phone_batch` VALUES ('53', 'B1516268090', '80312', '', '9', '1516268090', '1516268094', null, '1', '0', '0', '0');
