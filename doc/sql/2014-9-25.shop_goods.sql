ALTER TABLE  `yxd_shop_goods` ADD  `day_limit_goods_total` INT( 10 ) NOT NULL DEFAULT  '0' AFTER  `gduihuan_max` ,
ADD  `day_limit_goods_last` INT( 10 ) NOT NULL DEFAULT  '0' AFTER  `day_limit_goods_total` ,
ADD  `limit_flag` SMALLINT( 2 ) NOT NULL DEFAULT  '0' AFTER  `day_limit_goods_last` ,
ADD  `limit_time` INT( 10 ) NOT NULL DEFAULT  '0' AFTER  `limit_flag`