/* 管理员&权限组关系表  */
DROP TABLE IF EXISTS `yxd_admin_group`;
CREATE TABLE `yxd_admin_group` (
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员id',
  `group_id` tinyint(2) unsigned NOT NULL COMMENT '权限组id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 权限组表  */
DROP TABLE IF EXISTS `yxd_group`;
CREATE TABLE `yxd_group` (
  `group_id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '权限组名',
  `description` varchar(50) NOT NULL COMMENT '描述',
  `level` int(2) unsigned NOT NULL COMMENT '权限级别',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 权限组&权限关系表  */
DROP TABLE IF EXISTS `yxd_group_permission`;
CREATE TABLE `yxd_group_permission` (
  `group_id` tinyint(2) unsigned NOT NULL,
  `permission_id` int(10) unsigned DEFAULT NULL,
  KEY `yxd_group_permission_ibfk_1` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 权限表  */
DROP TABLE IF EXISTS `yxd_permission`;
CREATE TABLE `yxd_permission` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app` varchar(10) NOT NULL COMMENT '应用',
  `module` varchar(50) NOT NULL COMMENT '模块',
  `node` varchar(100) NOT NULL COMMENT '节点',
  `description` varchar(100) NOT NULL COMMENT '描述',
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 操作记录日志表  */
DROP TABLE IF EXISTS `yxd_monolog`;
CREATE TABLE `yxd_monolog` (
  `monolog_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) NOT NULL COMMENT '日志标示名',
  `operate` varchar(255) NOT NULL COMMENT '操作描述',
  `op_name` varchar(20) NOT NULL COMMENT '操作员姓名',
  `op_group` varchar(20) NOT NULL COMMENT '操作员权限组',
  `related_data` varchar(500) NOT NULL COMMENT '操作内容',
  `time` datetime NOT NULL COMMENT '操作时间',
  `level` varchar(20) NOT NULL COMMENT '日志级别',
  PRIMARY KEY (`monolog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 新增默认管理员数据  */
INSERT INTO yxd_group (`name`,`description`,`level`) VALUES ('superadmin','超级管理员','1');
INSERT INTO yxd_group (`name`,`description`,`level`) VALUES ('admin','管理员','2');
INSERT INTO yxd_group (`name`,`description`,`level`) VALUES ('defaultv','默认','3');

/* 新增默认管理员关系数据  */
INSERT INTO yxd_admin_group (`admin_id`,`group_id`) VALUES (36,1);

/* 新增权限数据  */
insert  into `yxd_permission`(`permission_id`,`app`,`module`,`node`,`description`) values (1,'system','permission','*','权限设置'),(2,'game','games','*','游戏库'),(3,'adv','*','*','广告'),(4,'system','*','*','系统'),(5,'user','*','*','用户主菜单'),(6,'*','*','*','全局权限'),(7,'forum','*','*','微论坛'),(8,'comment','*','*','评论'),(9,'activity','*','*','活动'),(10,'shop','*','*','商城'),(11,'giftbag','*','*','礼包'),(12,'message','*','*','消息推送'),(13,'feedback','*','*','用户反馈'),(19,'activity','event','list','有奖问答活动'),(20,'activity','event','add','添加有奖问答');

/* 新增组权限数据  */
insert  into `yxd_group_permission`(`group_id`,`permission_id`) values (1,6),(3,2);