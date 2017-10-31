ALTER TABLE `yxd_tuiguang_account` ADD COLUMN `idfa` VARCHAR(100) DEFAULT NULL  AFTER `ctime`;

ALTER TABLE `yxd_tuiguang_account` ADD COLUMN `mac` VARCHAR(100) DEFAULT NULL  AFTER `idfa`;