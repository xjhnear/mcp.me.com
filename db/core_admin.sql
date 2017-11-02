/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mcp_www

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-02 16:38:00
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
INSERT INTO `core_admin` VALUES ('1', 'xiajiahui', '', '小胖', 'e10adc3949ba59abbe56e057f20f883e', '0', '夏佳辉', '1', '1', '\r\n\r\n\r\n{\"menus\":{\"admin\":{\"module_name\":\"admin\",\"module_icon\":\"core\",\"module_alias\":\"\\u9ad8\\u7ea7\\u7ba1\\u7406\",\"default_url\":\"admin\\/group\\/list\",\"child_menu\":[{\"name\":\"\\u6743\\u9650\\u7ec4\",\"url\":\"admin\\/group\\/list\"},{\"name\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"url\":\"admin\\/admin\\/list\"},{\"name\":\"\\u6a21\\u5757\\u7ba1\\u7406\",\"url\":\"admin\\/module\\/list\"}]},\"common\":{\"module_name\":\"common\",\"module_icon\":\"\",\"module_alias\":\"\\u9996\\u9875\",\"default_url\":\"common\\/home\\/index\",\"child_menu\":[{\"name\":\"\\u4e2a\\u4eba\\u9996\\u9875\",\"url\":\"common\\/home\\/index\"}]},\"v4_task\":{\"module_name\":\"v4_task\",\"module_icon\":\"ios\",\"module_alias\":\"\\u72ee\\u543c\\u5206\\u53d1\\u5e73\\u53f0\\u4efb\\u52a1\",\"default_url\":\"v4_task\\/task\\/task-list\",\"child_menu\":[{\"name\":\"\\u4efb\\u52a1\\u5217\\u8868\",\"url\":\"v4_task\\/task\\/task-list\",\"separator\":\"\\u666e\\u901a\\u4efb\\u52a1\"},{\"name\":\"\\u6dfb\\u52a0\\u4efb\\u52a1\",\"url\":\"v4_task\\/task\\/task-add\"},{\"name\":\"\\u6dfb\\u52a0\\u4efb\\u52a1\\u7ebf1.4\",\"url\":\"v4_task\\/task\\/add-task-line\"},{\"name\":\"\\u6dfb\\u52a0\\u5b50\\u4efb\\u52a11.4\",\"url\":\"v4_task\\/task\\/sub-task-add\"},{\"name\":\"\\u5b50\\u4efb\\u52a1\\u5217\\u8868\",\"url\":\"v4_task\\/task\\/task-children-list\"},{\"name\":\"\\u5b50\\u4efb\\u52a1\\u5217\\u88681.4\",\"url\":\"v4_task\\/task\\/sub-task-list\"},{\"name\":\"\\u4efb\\u52a1\\u6807\\u7b7e\\u6392\\u5e8f\",\"url\":\"v4_task\\/task\\/task-tag\"},{\"name\":\"\\u7b7e\\u5230\\u8bbe\\u7f6e\",\"url\":\"v4_task\\/task\\/task-sign\"},{\"name\":\"\\u6bcf\\u65e5\\u7b7e\\u5230\\u7edf\\u8ba1\",\"url\":\"v4_task\\/task\\/task-sign-statistics\"}]}},\"nodes\":[\"admin\\/group\\/list\",\"admin\\/admin\\/list\",\"admin\\/module\\/list\",\"admin\\/*\",\"admin\\/group\\/*\",\"admin\\/admin\\/*\",\"admin\\/module\\/*\",\"admin\\/module\\/uninstall\",\"admin\\/admin\\/modify-pwd\",\"common\\/home\\/index\",\"common\\/home\\/index\",\"common\\/home\\/edit-profile\",\"common\\/uploader\\/*\",\"v4_task\\/task\\/task-list\",\"v4_task\\/task\\/task-add\",\"v4_task\\/task\\/add-task-line\",\"v4_task\\/task\\/sub-task-add\",\"v4_task\\/task\\/task-children-list\",\"v4_task\\/task\\/sub-task-list\",\"v4_task\\/task\\/task-tag\",\"v4_task\\/task\\/task-sign\",\"v4_task\\/task\\/task-sign-statistics\",\"v4_task\\/*\"]}');
INSERT INTO `core_admin` VALUES ('2', 'test', '', '测试号', 'e10adc3949ba59abbe56e057f20f883e', '0', '测试号', '1', '1', '{\"menus\":{\"common\":{\"module_name\":\"common\",\"module_icon\":\"\",\"module_alias\":\"\\u9996\\u9875\",\"default_url\":\"common\\/home\\/index\",\"child_menu\":[{\"name\":\"\\u4e2a\\u4eba\\u9996\\u9875\",\"url\":\"common\\/home\\/index\"}]},\"admin\":{\"module_name\":\"admin\",\"module_icon\":\"core\",\"module_alias\":\"\\u9ad8\\u7ea7\\u7ba1\\u7406\",\"default_url\":\"admin\\/group\\/list\",\"child_menu\":[{\"name\":\"\\u6743\\u9650\\u7ec4\",\"url\":\"admin\\/group\\/list\"},{\"name\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"url\":\"admin\\/admin\\/list\"},{\"name\":\"\\u6a21\\u5757\\u7ba1\\u7406\",\"url\":\"admin\\/module\\/list\"}]},\"v4_task\":{\"module_name\":\"v4_task\",\"module_icon\":\"ios\",\"module_alias\":\"\\u72ee\\u543c\\u5206\\u53d1\\u5e73\\u53f0\\u4efb\\u52a1\",\"default_url\":\"v4_task\\/task\\/task-list\",\"child_menu\":[{\"name\":\"\\u4efb\\u52a1\\u5217\\u8868\",\"url\":\"v4_task\\/task\\/task-list\",\"separator\":\"\\u666e\\u901a\\u4efb\\u52a1\"},{\"name\":\"\\u6dfb\\u52a0\\u4efb\\u52a1\",\"url\":\"v4_task\\/task\\/task-add\"},{\"name\":\"\\u6dfb\\u52a0\\u4efb\\u52a1\\u7ebf1.4\",\"url\":\"v4_task\\/task\\/add-task-line\"},{\"name\":\"\\u6dfb\\u52a0\\u5b50\\u4efb\\u52a11.4\",\"url\":\"v4_task\\/task\\/sub-task-add\"},{\"name\":\"\\u5b50\\u4efb\\u52a1\\u5217\\u8868\",\"url\":\"v4_task\\/task\\/task-children-list\"},{\"name\":\"\\u5b50\\u4efb\\u52a1\\u5217\\u88681.4\",\"url\":\"v4_task\\/task\\/sub-task-list\"},{\"name\":\"\\u4efb\\u52a1\\u6807\\u7b7e\\u6392\\u5e8f\",\"url\":\"v4_task\\/task\\/task-tag\"},{\"name\":\"\\u7b7e\\u5230\\u8bbe\\u7f6e\",\"url\":\"v4_task\\/task\\/task-sign\"},{\"name\":\"\\u6bcf\\u65e5\\u7b7e\\u5230\\u7edf\\u8ba1\",\"url\":\"v4_task\\/task\\/task-sign-statistics\"}]},\"phone\":{\"module_name\":\"phone\",\"module_icon\":\"all\",\"module_alias\":\"\\u624b\\u673a\\u53f7\\u7801\\u7ba1\\u7406\",\"default_url\":\"phone\\/batch\\/list\",\"child_menu\":[{\"name\":\"\\u6279\\u6b21\\u7ba1\\u7406\",\"url\":\"phone\\/batch\\/list\"}]}},\"nodes\":[\"common\\/home\\/index\",\"common\\/home\\/index\",\"common\\/home\\/edit-profile\",\"common\\/uploader\\/*\",\"admin\\/group\\/list\",\"admin\\/admin\\/list\",\"admin\\/module\\/list\",\"admin\\/*\",\"admin\\/group\\/*\",\"admin\\/admin\\/*\",\"admin\\/module\\/*\",\"admin\\/module\\/uninstall\",\"admin\\/admin\\/modify-pwd\",\"v4_task\\/task\\/task-list\",\"v4_task\\/task\\/task-add\",\"v4_task\\/task\\/add-task-line\",\"v4_task\\/task\\/sub-task-add\",\"v4_task\\/task\\/task-children-list\",\"v4_task\\/task\\/sub-task-list\",\"v4_task\\/task\\/task-tag\",\"v4_task\\/task\\/task-sign\",\"v4_task\\/task\\/task-sign-statistics\",\"v4_task\\/*\",\"phone\\/batch\\/list\",\"phone\\/*\",\"phone\\/batch\\/*\"],\"group_ids\":[],\"module_ids\":[]}');
