/*
SQLyog Ultimate v11.27 (32 bit)
MySQL - 5.5.48-log : Database - yxd_club_beta
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`yxd_club_beta` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `yxd_club_beta`;

/*Table structure for table `core_module` */

DROP TABLE IF EXISTS `core_module`;

CREATE TABLE `core_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_type` enum('android','ios','core') COLLATE utf8_bin NOT NULL DEFAULT 'core',
  `module_alias` varchar(50) COLLATE utf8_bin NOT NULL,
  `module_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `module_desc` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `installed` tinyint(1) NOT NULL,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Data for the table `core_module` */

insert  into `core_module`(`id`,`module_type`,`module_alias`,`module_name`,`module_desc`,`installed`,`sort`) values (1,'core','设置','system','包括系统设置、账号管理、权限管理等',1,1),(3,'core','用户','user','包括用户管理、游币管理、用户权限管理、用户等级管理等',0,3),(4,'ios','游戏','game','包括游戏管理等',0,2),(5,'core','广告','adv','包括广告管理等',0,12),(6,'core','论坛','forum','包括论坛管理等',0,4),(7,'ios','商城','shop','包括商品管理等',0,6),(8,'ios','评论','comment','包括评论管理等',0,5),(9,'ios','反馈','feedback','包括反馈管理等',0,12),(10,'ios','消息','message','包括消息管理等',0,11),(11,'ios','礼包','giftbag','包括礼包管理等',0,7),(12,'ios','小游戏','xgame','包括小游戏管理等',0,13),(13,'ios','统计','statistics','包括用户统计、游币统计、推广统计等',0,10),(14,'ios','活动','activity','包括活动管理等',0,8),(15,'android','礼包','a_giftbag','包括礼包管理等',0,7),(16,'android','聊天室','chat','包括聊天室管理等',0,11),(17,'android','消息','a_message','包括系统消息管理、通知管理等',0,11),(18,'core','通用接口','common','包括图片上传接口等',0,0),(19,'android','游戏','a_game','包括游戏管理等',0,2),(20,'core','高级管理','admin','包括模块管理、账号管理、权限管理等',0,0),(21,'android','设置','a_system','包括应用设置、版本控制、积分设置等',0,1),(22,'core','资讯','cms','包括新闻、攻略、评测、视频等管理等',0,3),(23,'ios','微信分享','wxshare','包括微信分享管理等',0,8),(24,'ios','商城','product','包括商品管理等',0,6),(25,'core','小秘书','sproject','包括小秘书管理等',0,14),(31,'ios','礼包v4','v4_giftbag','包括礼包管理等v4',0,0),(32,'ios','消息V4','v4_message','包括消息V4管理等',0,11),(33,'core','web论坛','web_forum','包括web论坛管理等',0,0),(34,'android','duang','duang','包括Duang活动管理等',0,8),(35,'android','V4商城','v4a_product','包括商品管理等',0,0),(36,'core','V4论坛(Android)','weba_forum','包括V4论坛管理等',0,0),(37,'core','代充活动(Android)','lottery','包括代充管理等',0,0),(38,'android','活动','a_activity','包括活动管理等',0,8),(39,'ios','专题活动','zt_activity','包括专题活动管理等',0,0),(40,'android','游戏下载地址同步','plat360','包括游戏下载地址管理等',0,14),(41,'core','直播管理','zhibo','包括直播管理等',0,15),(44,'core','专题','topic','包括添加专题合集,专题合集列表',0,0),(45,'android','推荐位','a_adv','包括轮播管理、横幅管理、推荐游戏管理等',0,8),(46,'ios','V4商城','v4_product','包括商品管理等',0,0),(47,'core','广告','advs','包括广告管理等',0,0),(48,'android','礼包v4a','v4a_giftbag','包括礼包管理等v4a',0,0),(49,'','union','union','包括综合web管理等',0,8),(50,'ios','V4礼包库','v4_packagelibrary','包括礼包库等',0,0),(51,'ios','活动v4','v4_activity','包括活动管理等v4',0,0),(52,'ios','彩票v4','v4_lotteryproduct','v4彩票管理',0,10),(53,'ios','CMS','wcms','包括文章、图集、视频等',0,0),(54,'core','其他管理','other','发现等',0,0),(55,'android','活动','a_activity2','包括活动管理等',0,8),(56,'ios','活动（ios）','IOS_activity','包括活动管理等',0,8),(57,'core','游戏直播','gamelive','包括游戏直播管理等',0,8),(58,'ios','我的v4','v4_my','包括勋章管理等v4',0,0),(59,'ios','V4设置','v4system','包括设置、举报等',0,4),(60,'core','游戏电竞','yxvl_eSports','包括文章、图集、视频等',0,0),(61,'android','彩票v4a','v4a_lotteryproduct','v4a彩票管理',0,0),(62,'ios','V4消息','v4message','包括消息推送、消息通知等',0,8),(63,'core','V4用户','v4user','包括用户管理等',0,0),(64,'ios','v4广告管理','v4_adv','包括广告管理等v4',0,0),(65,'ios','V4玩家情报','v4gamecmt','包括玩家情报等',0,8),(70,'ios','v4统计','v4_statistics','v4统计管理',0,0),(71,'core','推广','tuiguang','推广',0,0),(72,'core','联赛','liansai','联赛',0,0),(73,'core','PC导量活动','liansais','PC导量活动',0,0),(74,'ios','v4分享','v4_share','v4分享',0,0),(75,'core','内容流','neirong','内容流',0,0),(76,'ios','v4推广','v4_tuiguang','v4推广',0,0),(77,'android','一元购','a_yiyuan','一元购',0,0),(78,'ios','一元购','IOS_yiyuan','一元购',0,0),(79,'android','qita','qita','qita',0,0),(80,'android','recharge','recharge','游币充值',0,0),(81,'ios','充值管理','IOS_recharge','recharge',0,0),(82,'core','专区专题','zhuanqu','专区专题',0,0),(83,'core','天娱','tianyu','天娱',0,0),(84,'core','金娱','jinyu','金娱',0,0),(85,'ios','V4背包管理','v4_backpack','包括背包物品管理等',0,0),(86,'ios','V4百宝箱','v4_box','包括方案管理等',0,0),(87,'ios','V4监控管理','v4_monitor','包括主线程管理等',0,0),(88,'core','V4论坛(提审)','webt_forum','包括V4论坛管理等',0,0),(89,'ios','V4Scheme管理','v4_scheme','包括Scheme记录等',0,0),(90,'ios','共享账号管理','v4_shareaccount','包括共享账号管理等',0,0),(91,'ios','V4留言板','v4messageboard','包括留言板等',0,8),(92,'ios','狮吼分发平台任务','v4_task','包括任务管理等',0,8);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
