==Ȧ�Ӷ�̬�־û�
CREATE TABLE `yxd_feed_gamecircle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '�������',
  `gid` int(11) NOT NULL COMMENT '��ϷID',
  `type` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '��̬����',
  `linkid` int(11) NOT NULL DEFAULT '0' COMMENT '��̬Ŀ��ID',
  `score` float(20,4) NOT NULL DEFAULT '0.0000' COMMENT '����',
  `data` text COLLATE utf8_bin NOT NULL COMMENT '��̬����',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
