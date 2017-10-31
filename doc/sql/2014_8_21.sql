ALTER TABLE `yxd_shop_goods` ADD COLUMN `max_exchange_times` INT(10) DEFAULT 1 NULL COMMENT '最大兑换次数0:表示无限制' AFTER `expense`;

ALTER TABLE `yxd_shop_goods` ADD COLUMN `cate_id` INT(10) DEFAULT 0 NULL COMMENT '分类ID' AFTER `id`;

CREATE TABLE `yxd_shop_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `icon` varchar(250) COLLATE utf8_bin NOT NULL COMMENT '图标',
  `cate_name` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '名称',
  `summary` varchar(500) COLLATE utf8_bin NOT NULL COMMENT '描述',
  `sort` int(10) NOT NULL COMMENT '排序,越大越靠前',
  `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
