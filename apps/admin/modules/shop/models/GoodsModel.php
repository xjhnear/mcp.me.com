<?php
namespace modules\shop\models;

use Yxd\Modules\Core\BaseModel;
use modules\forum\models\TopicModel;
use modules\system\models\SystemSettingModel;

class GoodsModel extends BaseModel
{
	public static function search($search,$page,$size)
	{
		$total = self::buildSearch($search)->count();
		$list = self::buildSearch($search)->forPage($page,$size)->orderBy('sort','desc')->orderBy('id','desc')->get();
		$out = array();
		foreach($list as $good){
			if($good['gtype'] == 2){
				$gift_id = $good['gift_id'];
				$giftinfo = self::getGiftnums($gift_id);
				$good['totalnum'] = $giftinfo['total_num'];
				$good['usednum'] = $giftinfo['total_num'] - $giftinfo['last_num'];
				$out[] = $good;
			}else{
				$out[] = $good;
			}	
		}
		return array('list'=>$out,'total'=>$total);
	}
	
    protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('shop_goods');
		
		return $tb;
	}
	/**
	 * 根据礼包的id，获取礼包数量,也就是该商品的数量
	 */
	public static function getGiftnums($gift_id){
		$giftinfo = self::dbClubMaster()->table('giftbag')->where('id', $gift_id)->first();
		return $giftinfo;
	}
	/**
	 * 检查该礼包是否已经有了对应的商品，有的话返回一个错误提示，没有的话，把该商品存入到数据库
	 */
	public static function checkShop($gift_id){
		if(self::dbClubMaster()->table('shop_goods')->where('gift_id', $gift_id)->get()){
			return true;
		}else{
			return false;
		}
	}
    /**
	 * 
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('shop_goods')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			self::dbClubMaster()->table('shop_goods')->where('id','=',$id)->update($data);
			return true;
		}else{
			return self::dbClubMaster()->table('shop_goods')->insertGetId($data);
		}
	}
	
	/**
	 * 删除信息
	 */
	public static function delete($id)
	{
		self::dbClubMaster()->table('shop_goods_account')->where('goods_id','=',$id)->delete();
		return self::dbClubMaster()->table('shop_goods')->where('id','=',$id)->delete();
	}
	
	public static function doStatus($id,$status)
	{
		return self::dbClubMaster()->table('shop_goods')->where('id','=',$id)->update(array('status'=>$status));
	}
	
	/**
	 * 许愿帖
	 */
    public static function getRuleInfo()
	{
		$rule = SystemSettingModel::getConfig('shop_wish_rule');
		
		if($rule && isset($rule['data']['rule_id'])){
			$tid = $rule['data']['rule_id'];
			$topic = TopicModel::getRuleInfo($tid);
			
			return $topic;
		}
		return array();
	}
	
	/**
	 * 保存许愿帖
	 */
	public static function saveRule($tid,$subject,$message,$uid)
	{
		$res = TopicModel::saveRuleTopic($tid,$subject,$message,$uid);
		if($res){
			SystemSettingModel::setConfig('shop_wish_rule',array('rule_id'=>$res));
			return true;
		}
		return false;
	}
}