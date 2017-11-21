/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-21 13:19:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for core_admin
-- ----------------------------
DROP TABLE IF EXISTS `core_admin`;
CREATE TABLE `core_admin` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `role` varchar(20) NOT NULL,
  `authorname` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `addtime` int(10) NOT NULL,
  `realname` varchar(20) NOT NULL DEFAULT '',
  `isopen` tinyint(1) DEFAULT '1' COMMENT '是否启用 1=启用,2=禁用',
  `group_id` int(10) NOT NULL DEFAULT '0',
  `menus_nodes` text,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of core_admin
-- ----------------------------
INSERT INTO `core_admin` VALUES ('1', 'xiajiahui', '', '小胖', 'e10adc3949ba59abbe56e057f20f883e', '0', '夏佳辉', '1', '1', '{\"menus\":{\"common\":{\"module_name\":\"common\",\"module_icon\":\"\",\"module_alias\":\"\\u9996\\u9875\",\"default_url\":\"common\\/home\\/index\",\"child_menu\":[{\"name\":\"\\u4e2a\\u4eba\\u9996\\u9875\",\"url\":\"common\\/home\\/index\"}]},\"admin\":{\"module_name\":\"admin\",\"module_icon\":\"core\",\"module_alias\":\"\\u9ad8\\u7ea7\\u7ba1\\u7406\",\"default_url\":\"admin\\/group\\/list\",\"child_menu\":[{\"name\":\"\\u6743\\u9650\\u7ec4\",\"url\":\"admin\\/group\\/list\"},{\"name\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"url\":\"admin\\/admin\\/list\"},{\"name\":\"\\u6a21\\u5757\\u7ba1\\u7406\",\"url\":\"admin\\/module\\/list\"}]},\"phone\":{\"module_name\":\"phone\",\"module_icon\":\"all\",\"module_alias\":\"\\u624b\\u673a\\u53f7\\u7801\\u7ba1\\u7406\",\"default_url\":\"phone\\/batch\\/list\",\"child_menu\":[{\"name\":\"\\u6279\\u6b21\\u7ba1\\u7406\",\"url\":\"phone\\/batch\\/list\"}]}},\"nodes\":[\"common\\/home\\/index\",\"common\\/home\\/index\",\"common\\/home\\/edit-profile\",\"common\\/uploader\\/*\",\"admin\\/group\\/list\",\"admin\\/admin\\/list\",\"admin\\/module\\/list\",\"admin\\/*\",\"admin\\/group\\/*\",\"admin\\/admin\\/*\",\"admin\\/module\\/*\",\"admin\\/module\\/uninstall\",\"admin\\/admin\\/modify-pwd\",\"phone\\/batch\\/list\",\"phone\\/*\",\"phone\\/batch\\/*\"],\"group_ids\":[],\"module_ids\":[]}');
INSERT INTO `core_admin` VALUES ('2', 'test', '', '测试号', 'e10adc3949ba59abbe56e057f20f883e', '0', '测试号', '1', '1', '{\"menus\":{\"common\":{\"module_name\":\"common\",\"module_icon\":\"\",\"module_alias\":\"\\u9996\\u9875\",\"default_url\":\"common\\/home\\/index\",\"child_menu\":[{\"name\":\"\\u4e2a\\u4eba\\u9996\\u9875\",\"url\":\"common\\/home\\/index\"}]},\"admin\":{\"module_name\":\"admin\",\"module_icon\":\"core\",\"module_alias\":\"\\u9ad8\\u7ea7\\u7ba1\\u7406\",\"default_url\":\"admin\\/group\\/list\",\"child_menu\":[{\"name\":\"\\u6743\\u9650\\u7ec4\",\"url\":\"admin\\/group\\/list\"},{\"name\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"url\":\"admin\\/admin\\/list\"},{\"name\":\"\\u6a21\\u5757\\u7ba1\\u7406\",\"url\":\"admin\\/module\\/list\"}]},\"phone\":{\"module_name\":\"phone\",\"module_icon\":\"all\",\"module_alias\":\"\\u624b\\u673a\\u53f7\\u7801\\u7ba1\\u7406\",\"default_url\":\"phone\\/batch\\/list\",\"child_menu\":[{\"name\":\"\\u6279\\u6b21\\u7ba1\\u7406\",\"url\":\"phone\\/batch\\/list\"}]}},\"nodes\":[\"common\\/home\\/index\",\"common\\/home\\/index\",\"common\\/home\\/edit-profile\",\"common\\/uploader\\/*\",\"admin\\/group\\/list\",\"admin\\/admin\\/list\",\"admin\\/module\\/list\",\"admin\\/*\",\"admin\\/group\\/*\",\"admin\\/admin\\/*\",\"admin\\/module\\/*\",\"admin\\/module\\/uninstall\",\"admin\\/admin\\/modify-pwd\",\"phone\\/batch\\/list\",\"phone\\/*\",\"phone\\/batch\\/*\"],\"group_ids\":[],\"module_ids\":[]}');

-- ----------------------------
-- Table structure for core_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `core_auth_group`;
CREATE TABLE `core_auth_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `group_desc` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `group_icon` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `menus_nodes` text COLLATE utf8_bin,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Records of core_auth_group
-- ----------------------------
INSERT INTO `core_auth_group` VALUES ('1', '超级管理员', '拥有全部后台权限', null, 0x7B226D656E7573223A7B22636F6D6D6F6E223A7B226D6F64756C655F6E616D65223A22636F6D6D6F6E222C226D6F64756C655F69636F6E223A22222C226D6F64756C655F616C696173223A225C75393939365C7539383735222C2264656661756C745F75726C223A22636F6D6D6F6E5C2F686F6D655C2F696E646578222C226368696C645F6D656E75223A5B7B226E616D65223A225C75346532615C75346562615C75393939365C7539383735222C2275726C223A22636F6D6D6F6E5C2F686F6D655C2F696E646578227D5D7D2C2261646D696E223A7B226D6F64756C655F6E616D65223A2261646D696E222C226D6F64756C655F69636F6E223A22636F7265222C226D6F64756C655F616C696173223A225C75396164385C75376561375C75376261315C7537343036222C2264656661756C745F75726C223A2261646D696E5C2F67726F75705C2F6C697374222C226368696C645F6D656E75223A5B7B226E616D65223A225C75363734335C75393635305C7537656334222C2275726C223A2261646D696E5C2F67726F75705C2F6C697374227D2C7B226E616D65223A225C75376261315C75373430365C75353435385C75353231375C7538383638222C2275726C223A2261646D696E5C2F61646D696E5C2F6C697374227D2C7B226E616D65223A225C75366132315C75353735375C75376261315C7537343036222C2275726C223A2261646D696E5C2F6D6F64756C655C2F6C697374227D5D7D2C2270686F6E65223A7B226D6F64756C655F6E616D65223A2270686F6E65222C226D6F64756C655F69636F6E223A22616C6C222C226D6F64756C655F616C696173223A225C75363234625C75363733615C75353366375C75373830315C75376261315C7537343036222C2264656661756C745F75726C223A2270686F6E655C2F62617463685C2F6C697374222C226368696C645F6D656E75223A5B7B226E616D65223A225C75363237395C75366232315C75376261315C7537343036222C2275726C223A2270686F6E655C2F62617463685C2F6C697374227D5D7D7D2C226E6F646573223A5B22636F6D6D6F6E5C2F686F6D655C2F696E646578222C22636F6D6D6F6E5C2F686F6D655C2F696E646578222C22636F6D6D6F6E5C2F686F6D655C2F656469742D70726F66696C65222C22636F6D6D6F6E5C2F75706C6F616465725C2F2A222C2261646D696E5C2F67726F75705C2F6C697374222C2261646D696E5C2F61646D696E5C2F6C697374222C2261646D696E5C2F6D6F64756C655C2F6C697374222C2261646D696E5C2F2A222C2261646D696E5C2F67726F75705C2F2A222C2261646D696E5C2F61646D696E5C2F2A222C2261646D696E5C2F6D6F64756C655C2F2A222C2261646D696E5C2F6D6F64756C655C2F756E696E7374616C6C222C2261646D696E5C2F61646D696E5C2F6D6F646966792D707764222C2270686F6E655C2F62617463685C2F6C697374222C2270686F6E655C2F2A222C2270686F6E655C2F62617463685C2F2A225D7D);

-- ----------------------------
-- Table structure for core_module
-- ----------------------------
DROP TABLE IF EXISTS `core_module`;
CREATE TABLE `core_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_type` enum('android','ios','all','core') COLLATE utf8_bin NOT NULL DEFAULT 'core',
  `module_alias` varchar(50) COLLATE utf8_bin NOT NULL,
  `module_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `module_desc` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `installed` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Records of core_module
-- ----------------------------
INSERT INTO `core_module` VALUES ('1', 'core', '通用接口', 'common', '包括图片上传接口等', '0', '0');
INSERT INTO `core_module` VALUES ('2', 'core', '高级管理', 'admin', '包括模块管理、账号管理、权限管理等', '0', '0');
INSERT INTO `core_module` VALUES ('4', 'all', '手机号码管理', 'phone', '包括手机号码管理、导入、导出管理等', '0', '9');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_batch
-- ----------------------------

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`num_id`),
  UNIQUE KEY `index_phone_number` (`phone_number`) USING BTREE,
  KEY `index_batch_id` (`batch_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=517551 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of m_phone_numbers
-- ----------------------------
