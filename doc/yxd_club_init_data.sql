
INSERT INTO `yxd_credit_level` VALUES ('1', '0', '游戏菜鸟', 'level1@2x.png?v=3', '0', '9');
INSERT INTO `yxd_credit_level` VALUES ('2', '0', '游戏新手', 'level2@2x.png?v=3', '10', '49');
INSERT INTO `yxd_credit_level` VALUES ('3', '0', '游戏老鸟', 'level3@2x.png?v=3', '50', '199');
INSERT INTO `yxd_credit_level` VALUES ('4', '0', '游戏精英', 'level4@2x.png?v=3', '200', '499');
INSERT INTO `yxd_credit_level` VALUES ('5', '0', '游戏大师', 'level5@2x.png?v=3', '500', '1999');
INSERT INTO `yxd_credit_level` VALUES ('6', '0', '游戏达人', 'level6@2x.png?v=3', '2000', '4999');
INSERT INTO `yxd_credit_level` VALUES ('7', '0', '游戏至尊', 'level7@2x.png?v=3', '5000', '9999');
INSERT INTO `yxd_credit_level` VALUES ('8', '0', '游戏大神', 'level8@2x.png?v=3', '10000', '9999999');

INSERT INTO `yxd_account_group` VALUES ('1', '管理员', null, 'level10@2x.png?v=4', '0', 'public', '');
INSERT INTO `yxd_account_group` VALUES ('2', '总编', null, 'level9@2x.png?v=4', '0', 'public', '');
INSERT INTO `yxd_account_group` VALUES ('3', '编辑', null, 'level9@2x.png?v=4', '0', 'public', '');
INSERT INTO `yxd_account_group` VALUES ('5', '普通用户', null, '', '0', 'public', '');



INSERT INTO `yxd_credit_setting` VALUES ('1', 'user_register', '注册积分', 'core', '0', '1', '{action}{sign}了{score}{typecn}', '0', '0');
INSERT INTO `yxd_credit_setting` VALUES ('2', 'invite_friend', '邀请好友', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '50', '50');
INSERT INTO `yxd_credit_setting` VALUES ('3', 'post_topic', '发表帖子', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '0', '2');
INSERT INTO `yxd_credit_setting` VALUES ('4', 'post_reply', '回复帖子', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '0', '1');
INSERT INTO `yxd_credit_setting` VALUES ('5', 'post_comment', '发布评论', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '0', '1');
INSERT INTO `yxd_credit_setting` VALUES ('6', 'share', '分享', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '0', '5');
INSERT INTO `yxd_credit_setting` VALUES ('7', 'delete_topic', '被举报并删除帖子', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '-2', '0');
INSERT INTO `yxd_credit_setting` VALUES ('8', 'delete_reply', '被举报并删除回复', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '-1', '0');
INSERT INTO `yxd_credit_setting` VALUES ('9', 'delete_comment', '被举报并删除评论', 'core', '0', '0', '{action}{sign}了{score}{typecn}', '-1', '0');


INSERT INTO `yxd_task` VALUES ('1', '每日任务', '1', '发布3条帖子', '在任意论坛版块发布3条主题帖', '{\"post-topic\":3}', 'post-topic', '{\"score\":\"10\",\"experience\":\"10\"}', '0');
INSERT INTO `yxd_task` VALUES ('2', '每日任务', '1', '回复3条帖子', '在任意论坛版块回复3条帖子', '{\"post-reply\":3}', 'post-reply', '{\"score\":\"5\",\"experience\":\"5\"}', '0');
INSERT INTO `yxd_task` VALUES ('3', '每日任务', '1', '发布3条评论', '对任意一款游戏发表评论', '{\"game-comment\":3}', 'game-comment', '{\"score\":\"5\",\"experience\":\"5\"}', '0');
INSERT INTO `yxd_task` VALUES ('4', '新手任务', '2', '上传头像', '在帐号-设置-头像设置里上传头像', '{\"upload-avatar\":1}', 'upload-avatar', '{\"score\":\"20\",\"experience\":\"20\"}', '0');
INSERT INTO `yxd_task` VALUES ('5', '新手任务', '2', '上传主页背景', '在帐号-设置-头像设置里上传主页背景', '{\"upload-homebg\":1}', 'upload-homebg', '{\"score\":\"15\",\"experience\":\"15\"}', '0');
INSERT INTO `yxd_task` VALUES ('6', '新手任务', '2', '完善个人资料', '在帐号-设置-基本信息里完善你的个人资料', '{\"edit-info\":1}', 'edit-info', '{\"score\":\"25\",\"experience\":\"25\"}', '0');
INSERT INTO `yxd_task` VALUES ('8', '每日任务', '1', '下载游戏(每日仅限3次)', '下载游戏(每日仅限3次)', '{\"download\":1,\"max_times\":3}', 'download', '{\"score\":\"5\",\"experience\":\"0\"}', '0');
INSERT INTO `yxd_task` VALUES ('9', '每日任务', '1', '分享3次', '分享3次', '{\"share\":3,\"max_times\":1}', 'share', '{\"score\":\"5\",\"experience\":\"0\"}', '0');


INSERT INTO `yxd_system_message_tpl` VALUES ('1', 'subscribe_giftbag_success', '恭喜您成功预约了《{game_name}》游戏礼包');
INSERT INTO `yxd_system_message_tpl` VALUES ('2', 'shop_goods_giftbag_exchange_success', '恭喜你成功兑换了商品《{goods_name}》礼包卡激活码为：{cardno}');
INSERT INTO `yxd_system_message_tpl` VALUES ('3', 'shop_goods_product_exchange_success', '恭喜你成功兑换了商品《{goods_name}》,{expense}');
INSERT INTO `yxd_system_message_tpl` VALUES ('4', 'subscribe_giftbag_update', '您预约的游戏《{game_name}》有新礼包啦');
INSERT INTO `yxd_system_message_tpl` VALUES ('5', 'hunt_award_money', '恭喜您赢得寻宝箱活动的{reward_no}等奖，奖品为{prize_name},共获得游币{reward_score}枚');
INSERT INTO `yxd_system_message_tpl` VALUES ('6', 'hunt_award_product', '恭喜您赢得寻宝箱活动的{reward_no}等奖，奖品为{prize_name},{reward_expense}');
INSERT INTO `yxd_system_message_tpl` VALUES ('7', 'hunt_award_giftbag', '恭喜您赢得寻宝箱活动的{reward_no}等奖，奖品为{prize_name},激活码：{reward_cardno}');
INSERT INTO `yxd_system_message_tpl` VALUES ('8', 'get_giftbag_success', '恭喜您成功领取游戏礼包，激活码为：{cardno}');
INSERT INTO `yxd_system_message_tpl` VALUES ('9', 'comment_deleted', '您发布的评论因包含非法信息已被删除');
INSERT INTO `yxd_system_message_tpl` VALUES ('10', 'topic_deleted', '您发布的帖子因包含非法信息已被删除');
INSERT INTO `yxd_system_message_tpl` VALUES ('11', 'reply_best', '您的回答的被选择最佳答案');


INSERT INTO `yxd_forum_channel` VALUES ('1', '0', '八卦吐槽', '1', '1', '0');
INSERT INTO `yxd_forum_channel` VALUES ('2', '0', '游戏问答', '3', '1', '1');
INSERT INTO `yxd_forum_channel` VALUES ('3', '0', '寻找伙伴', '2', '1', '0');

INSERT INTO `yxd_system_setting` VALUES ('1', 'credit_rule', 0x613A313A7B733A373A2272756C655F6964223B693A3236373B7D);
INSERT INTO `yxd_system_setting` VALUES ('2', 'pcweb_setting', 0x613A353A7B733A343A226E616D65223B733A3130353A22E6B8B8E6888FE5A49A7CE68F90E4BE9BE5A5BDE78EA9E79A84E6898BE69CBAE6B8B8E6888FE4B88BE8BDBD5FE6898BE69CBAE6B8B8E6888FE694BBE795A55FE59BBDE58685E69C80E5A4A7E79A84E5A49AE7BB88E7ABAFE6898BE69CBAE6B8B8E6888FE997A8E688B7223B733A31363A226D6574615F6465736372697074696F6E223B733A3139343A22E6B8B8E6888FE5A49AE6AF8FE5A4A93234E5B08FE697B6E68F90E4BE9BE5A5BDE78EA9E79A84E6898BE69CBAE6B8B8E6888FE4B88BE8BDBDE58F8AE694BBE795A52EE69C80E58F8AE697B6E6898BE69CBAE6B8B8E6888FE8B584E8AEAF2CE6898BE69CBAE6B8B8E6888FE694BBE795A52CE7A4BCE58C85E5A4A7E585A82EE69C80E5A5BDE78EA9E79A84E6898BE69CBAE6B8B8E6888F2CE69C80E4B8B0E5AF8CE6898BE69CBAE6B8B8E6888FE694BBE795A5E5B0B1E59CA8E6B8B8E6888FE5A49A2E223B733A31333A226D6574615F6B6579776F726473223B733A36333A22E6B8B8E6888FE5A49A2CE5A5BDE78EA9E79A84E6898BE69CBAE6B8B8E6888F2CE6898BE69CBAE6B8B8E6888F2CE6898BE69CBAE6B8B8E6888FE694BBE795A5223B733A333A22696370223B733A32323A22E6B2AA494350E5A4873130303139383634E58FB72D39223B733A31323A2266696C7465725F776F726473223B733A31393A22E6AF9BE6B3BDE4B89C7CE6B19FE6B3BDE6B091223B7D);
INSERT INTO `yxd_system_setting` VALUES ('4', 'checkin_setting', 0x613A383A7B733A393A2266697273745F646179223B733A313A2235223B733A31303A227365636F6E645F646179223B733A323A223130223B733A393A2274686972645F646179223B733A323A223135223B733A31303A22666F757274685F646179223B733A323A223230223B733A393A2266696674685F646179223B733A323A223235223B733A393A2273697874685F646179223B733A323A223330223B733A31313A22736576656E74685F646179223B733A323A223335223B733A31373A22677265617465725F736576656E5F646179223B733A323A223335223B7D);
INSERT INTO `yxd_system_setting` VALUES ('5', 'home_picture_setting', 0x613A353A7B733A353A22797567616F223B733A34303A222F75736572646972732F323031342F30362F32303134303630363133323030365259514B2E706E67223B733A373A22706C617A615F32223B733A34303A222F75736572646972732F323031342F30352F323031343035333031333436323351417A342E706E67223B733A373A22706C617A615F33223B733A34303A222F75736572646972732F323031342F30362F32303134303630343131333635365A5239482E706E67223B733A353A227A6978756E223B733A34303A222F75736572646972732F323031342F30352F3230313430353330313331343434464A52612E706E67223B733A373A22706C617A615F31223B733A34303A222F75736572646972732F323031342F30352F3230313430353330313334363233416944432E706E67223B7D);


INSERT INTO `yxd_account` VALUES ('1', '', '管理员', 'admin@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '18658179152', '0', '386870400', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('2', '', '主编', 'editor@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');

INSERT INTO `yxd_account` VALUES ('3', '', '编辑3', 'editor_3@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('4', '', '编辑4', 'editor_4@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('5', '', '编辑5', 'editor_5@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('6', '', '编辑6', 'editor_6@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('7', '', '编辑7', 'editor_7@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('8', '', '编辑8', 'editor_8@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('9', '', '编辑9', 'editor_9@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('10', '', '编辑10', 'editor_10@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('11', '', '编辑11', 'editor_11@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('12', '', '编辑12', 'editor_12@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('13', '', '编辑13', 'editor_13@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('14', '', '编辑14', 'editor_14@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('15', '', '编辑15', 'editor_15@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('16', '', '编辑16', 'editor_16@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('17', '', '编辑17', 'editor_17@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('18', '', '编辑18', 'editor_18@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('19', '', '编辑19', 'editor_19@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');
INSERT INTO `yxd_account` VALUES ('20', '', '编辑20', 'editor_20@youxiduo.com', '', '770a6e7a2eada8facf51f1240c0b3612', '', '0', '', '', '', '1385740800', '', '', '', '', '', '', '', '', '');

INSERT INTO `yxd_account_group_link` VALUES ('1', '1', '1');
INSERT INTO `yxd_account_group_link` VALUES ('2', '2', '2');
INSERT INTO `yxd_account_group_link` VALUES ('3', '3', '3');
INSERT INTO `yxd_account_group_link` VALUES ('4', '4', '3');
INSERT INTO `yxd_account_group_link` VALUES ('5', '5', '3');
INSERT INTO `yxd_account_group_link` VALUES ('6', '6', '3');
INSERT INTO `yxd_account_group_link` VALUES ('7', '7', '3');
INSERT INTO `yxd_account_group_link` VALUES ('8', '8', '3');
INSERT INTO `yxd_account_group_link` VALUES ('9', '9', '3');
INSERT INTO `yxd_account_group_link` VALUES ('10', '10', '3');
INSERT INTO `yxd_account_group_link` VALUES ('11', '11', '3');
INSERT INTO `yxd_account_group_link` VALUES ('12', '12', '3');
INSERT INTO `yxd_account_group_link` VALUES ('13', '13', '3');
INSERT INTO `yxd_account_group_link` VALUES ('14', '14', '3');
INSERT INTO `yxd_account_group_link` VALUES ('15', '15', '3');
INSERT INTO `yxd_account_group_link` VALUES ('16', '16', '3');
INSERT INTO `yxd_account_group_link` VALUES ('17', '17', '3');
INSERT INTO `yxd_account_group_link` VALUES ('18', '18', '3');
INSERT INTO `yxd_account_group_link` VALUES ('19', '19', '3');
INSERT INTO `yxd_account_group_link` VALUES ('20', '20', '3');
