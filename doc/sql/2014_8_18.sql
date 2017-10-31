/* 新增活动表排序字段 */
ALTER TABLE `yxd_activity` ADD COLUMN `sort` TINYINT(4) DEFAULT 0 NULL COMMENT '排序' AFTER `addtime`;