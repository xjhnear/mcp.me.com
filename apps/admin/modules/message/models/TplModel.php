<?php
namespace modules\message\models;
use Yxd\Modules\Core\BaseModel;
use Yxd\Modules\Message\NoticeService;

class TplModel extends BaseModel
{	
	
	
	static $AUTO_NOTICE_TYPES = array(
	    'subscribe_giftbag_success'=>'预约游戏礼包成功',
	    'subscribe_giftbag_update'=>'预约游戏有新礼包',
	    'get_giftbag_success'=>'成功领取礼包',
	    'hunt_award_money'=>'寻宝箱活动中奖通知-游币',
	    'hunt_award_product'=>'寻宝箱活动中奖通知-实物',
	    'hunt_award_giftbag'=>'寻宝箱活动中奖通知-礼包',
	    //'gameask_award_money'=>'游戏问答活动中奖通知-游币',
	    //'gameask_award_product'=>'游戏问答活动中奖通知-实物',
	    //'gameask_award_giftbag'=>'游戏问答活动中奖通知-礼包',
	    'shop_goods_giftbag_exchange_success'=>'商城礼包奖品兑换成功',
	    'shop_goods_product_exchange_success'=>'商城实物奖品兑换成功',
	    'comment_deleted'=>'评论回复被管理员删除',
	    'topic_deleted'=>'发帖被管理员删除',
	    'reply_best'=>'回复被设置为最佳',
	    'reply_best_no_score'=>'回复被设置为最佳,达到上限',
	    //'attention_you'=>'有人关注了你',
	    'register'=>'新用户注册',
	    'tuiguang_score'=>'推广任务奖励游币',
	    'extra_score'=>'推广活动奖励游币',
	    'register_tuiguang'=>'注册推广奖励游币',
	    'topic_digest'=>'加精',
	    'topic_undigest'=>'取消加精',
	    'invalid_invitecode'=>'无效的邀请码',
	    'exists_ios'=>'IOS设备已经被使用',
	);
	
	static $AUTO_NOTICE_TPL_VARS = array(
	    'subscribe_giftbag_success'=>array('{game_name}'=>'游戏名称'),
	    'subscribe_giftbag_update'=>array('{game_name}'=>'游戏名称'),
	    'get_giftbag_success'=>array('{cardno}'=>'礼包卡号'),
	    'hunt_award_money'=>array('{reward_no}'=>'奖品等级','{prize_name}'=>'奖品名称','{reward_score}'=>'奖励游币数'),
	    'hunt_award_product'=>array('{reward_no}'=>'奖品等级','{prize_name}'=>'奖品名称','{reward_expense}'=>'奖品领取方式'),
	    'hunt_award_giftbag'=>array('{reward_no}'=>'奖品等级','{prize_name}'=>'奖品名称','{reward_cardno}'=>'礼包卡号'),
	    //'gameask_award_money'=>'游戏问答活动中奖通知-游币',
	    //'gameask_award_product'=>'游戏问答活动中奖通知-实物',
	    //'gameask_award_giftbag'=>'游戏问答活动中奖通知-礼包',
	    'shop_goods_giftbag_exchange_success'=>array('{goods_name}'=>'商品名称','{cardno}'=>'礼包卡'),
	    'shop_goods_product_exchange_success'=>array('{goods_name}'=>'商品名称','{expense}'=>'领取方式'),
	    'comment_deleted'=>array('{catename}'=>'分类或游戏名称','{title}'=>'标题'),
	    'topic_deleted'=>array('{catename}'=>'分类或游戏名称','{title}'=>'标题'),
	    'reply_best'=>array('{catename}'=>'分类或游戏名称','{title}'=>'标题','{money}'=>'游币'),
	    'reply_best_no_score'=>array('{catename}'=>'分类或游戏名称','{title}'=>'标题'),
	    'extra_score'=>array('{username}'=>'用户名','{num}'=>'推广人数','{score}'=>'游币'),
	    'register_tuiguang'=>array('{username}'=>'用户名','{num}'=>'推广人数','{score}'=>'游币'),
	    'tuiguang_score'=>array('{num}'=>'推广人数','{score}'=>'游币'),
	    'topic_digest'=>array('{game_name}'=>'游戏名称','{money}'=>'游币'),
	    'topic_undigest'=>array('{game_name}'=>'游戏名称','{money}'=>'游币'),
	    'invalid_invitecode'=>array('{zhucema}'=>'邀请码'),
	    'exists_ios'=>array('{zhucema}'=>'邀请码'),
	    //'attention_you'=>'有人关注了你',
	    'register'=>array()
	);
	
	public static function getList()
	{
		return self::dbClubMaster()->table('system_message_tpl')->orderBy('id','asc')->get();
	}
	
	public static function getInfo($id)
	{
		return self::dbClubMaster()->table('system_message_tpl')->where('id','=',$id)->first();
	}
	
	public static function getNotExistsKeys()
	{
		$exists_key = self::dbClubMaster()->table('system_message_tpl')->lists('id','ename');
		return array_diff_key(self::$AUTO_NOTICE_TYPES,$exists_key);
		
	}
	
	public static function save($data)
	{
		$ename = $data['ename'];
		$count = self::dbClubMaster()->table('system_message_tpl')->where('ename','=',$ename)->count(); 
		if($count){			
			unset($data['ename']);
			unset($data['id']);
			return self::dbClubMaster()->table('system_message_tpl')->where('ename','=',$ename)->update($data);
		}else{
			unset($data['id']);
			return self::dbClubMaster()->table('system_message_tpl')->insertGetId($data);
		}
	}
}