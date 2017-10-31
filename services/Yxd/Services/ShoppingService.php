<?php
namespace Yxd\Services;

use Yxd\Modules\Activity\GiftbagService;
use Yxd\Modules\Message\PromptService;
use Yxd\Modules\Message\NoticeService;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;
use Yxd\Services\Models\ShopCate;
use Yxd\Services\Models\ShopGoods;
use Yxd\Services\Models\ShopGoodsAccount;
use Yxd\Services\Models\TodayExchangeAccount;
use Yxd\Services\Models\Giftbag;
use Yxd\Services\Models\CreditAccount;


/**
 * 商城
 */
class ShoppingService extends Service
{
	/**
	 * 获取商品分类列表
	 */
	public static function getCateList($page=1,$pagesize=10)
	{  
		$total = ShopCate::db()->where('show','=',1)->count();
		$catelist = ShopCate::db()
		->where('show','=',1)
		->orderBy('sort','desc')
		->forPage($page,$pagesize)
		->get();
		return array('result'=>$catelist,'total'=>$total);
	}
	
	/**
	 * 获取商品列表
	 */
	public static function getGoodsList($page=1,$pagesize=10,$cate_id=0)
	{		
		$goodsList = self::buildGoodsList($cate_id)->orderBy('sort','desc')->orderBy('id','desc')->get();
		$total = self::buildGoodsList($cate_id)->count();
		return array('goods'=>$goodsList,'total'=>$total);
		
	}
	
	protected static function buildGoodsList($cate_id)
	{
		$tb = ShopGoods::db()->where('status','=',0);
		if($cate_id){  
			$tb = $tb->where('cate_id','=',$cate_id);
		}
		return $tb;
	}

	/**
	 * 商品详情
	 */
	public static function getGoodsInfo($id,$uid=0)
	{
		$goods = ShopGoods::db()->where('id','=',$id)->first();
		if($goods && $goods['limit_flag']==1){
			 $day_start = mktime('0','0','0',date('m'),date('d'),date('Y'));
			 $day_end = mktime('23','59','59',date('m'),date('d'),date('Y'));
			 if($day_start>$goods['limit_time']){
			 	$day_limit_goods_last = $goods['day_limit_goods_total'];
			 	$data = array('day_limit_goods_last'=>$day_limit_goods_last,'limit_time'=>$day_end);
			 	ShopGoods::db()->where('id',$id)->update($data);
			 	$goods['day_limit_goods_last'] = $goods['day_limit_goods_total'];
			 	$goods['limit_time'] = $day_end;
			 }
		}
		if(!$goods) return array();
		if($goods['gtype']==2 && $goods['gift_id']){
			$giftbag = GiftbagService::getInfoById($goods['gift_id']);
			if($giftbag){
				$goods['totalnum'] = $giftbag['total_num'];
			}
		}
		$goods['ishas'] = 0;
		$goods['exchangeinfo'] = '';
		if($uid){
			$exchange_times = ShopGoodsAccount::db()->where('uid','=',$uid)->where('goods_id','=',$id)->count();
			$mygoods_list = ShopGoodsAccount::db()->where('uid','=',$uid)->where('goods_id','=',$id)->get();
			if($exchange_times>0) $goods['ishas'] = 1;
			$goods['surplus_exchange_times'] = $goods['max_exchange_times']==0 ? 9999 : $goods['max_exchange_times'] - $exchange_times;
			foreach($mygoods_list as $mygoods){
				$params = array('goods_id'=>$goods['id'],'goods_name'=>$goods['name'],'expense'=>$mygoods['expense'],'cardno'=>$mygoods['cardno']);
				$tpl = (int)$goods['gtype']==1 ? NoticeService::AUTO_NOTICE_SHOP_GOODS_PRODUCT_EXCHANGE_SUCCESS : NoticeService::AUTO_NOTICE_SHOP_GOODS_GIFT_EXCHANGE_SUCCESS;
				$info = NoticeService::parseTpl($tpl, $params);
				if(self::getAppVersion()=='3.0.0'){
					$goods['exchangeinfo'] = $info ? $info : '';				    
				}else{
					$goods['exchangeinfo'][] = $info ? $info : '';
				}
			}
		}
		return $goods;
	}
	
	public static function getLastExchangeUserInfo($size=5)
	{
		$exchange_goods_list = ShopGoodsAccount::db()->forPage(1,$size)->orderBy('id','desc')->get();
		$uids = array();
		$goods_ids = array();
		foreach($exchange_goods_list as $row){
			$uids[] = $row['uid'];
			$goods_ids[] = $row['goods_id'];
		}
		$users = UserService::getBatchUserInfo($uids);
		if(!$goods_ids) return array();
		$goods_list = ShopGoods::db()->whereIn('id',$goods_ids)->lists('name','id');
		$notices = array();
		foreach($exchange_goods_list as $row){
			if(!isset($users[$row['uid']]) || !isset($goods_list[$row['goods_id']])) continue;
			$nickname = $users[$row['uid']]['nickname'];
			$money = $row['score'];
			$goods_name = $goods_list[$row['goods_id']];
			$notices[] = array('info'=>$nickname . '使用' . $money . '游币兑换了' . $goods_name);
		}
		return $notices;
	}
	
	/**
	 * 我兑换的商品
	 */
	public static function getMyGoodsList($uid,$page=1,$pagesize=10)
	{
		
		$goods_ids = ShopGoodsAccount::db()->where('uid','=',$uid)->orderBy('addtime','desc')->lists('goods_id');
		$total = count($goods_ids);
		if(empty($goods_ids)) return array();
		$goods = ShopGoods::db()->whereIn('id',$goods_ids)
			->forPage($page,$pagesize)
			->get();
		$tmp_goods = array();
		foreach($goods as $row)
		{
			$tmp_goods[$row['id']] = $row;
		}
		$out = array();
		ksort($goods_ids);
		foreach($goods_ids as $goods_id){
			$out[] = $tmp_goods[$goods_id];
		}
		return array('goods'=>$out,'total'=>$total);
	}
	
	public static function exchangeList($goods_id)
	{
		$tmp = ShopGoodsAccount::db()->where('goods_id','=',$goods_id)->orderBy('addtime','desc')->get();
		if(empty($tmp)) return array();
		foreach($tmp as $row){
			$uids[] = $row['uid'];
		}
		//$uids = array_values($tmp);
		$users = UserService::getBatchUserInfo($uids);
		$out = array();
		foreach($tmp as $one){
			$uid = $one['uid'];
			$addtime = $one['addtime'];
			if(!isset($users[$uid])) continue;
			$user = array();
			$user['nick'] = $users[$uid]['nickname'];
			$user['levelimg'] = self::joinImgUrl($users[$uid]['level_icon']);
			$user['avatarImg'] = self::joinImgUrl($users[$uid]['avatar']);
			$user['date'] = date('Y-m-d',$addtime);
			$out[] = $user;
		}
		return $out;
	}
	
    public static function exchangeListPage($goods_id,$pageIndex,$pageSize=10)
	{
		$total = ShopGoodsAccount::db()->where('goods_id','=',$goods_id)->count();
		$tmp = ShopGoodsAccount::db()->where('goods_id','=',$goods_id)->orderBy('addtime','desc')->forPage($pageIndex,$pageSize)->get();
		if(empty($tmp)) return array('result'=>array(),'totalCount'=>0);
		foreach($tmp as $row){
			$uids[] = $row['uid'];
		}
		//$uids = array_values($tmp);
		$users = UserService::getBatchUserInfo($uids);
		$out = array();
		foreach($tmp as $one){
			$uid = $one['uid'];
			$addtime = $one['addtime'];
			if(!isset($users[$uid])) continue;
			$user = array();
			$user['nick'] = $users[$uid]['nickname'];
			$user['levelimg'] = self::joinImgUrl($users[$uid]['level_icon']);
			$user['avatarImg'] = self::joinImgUrl($users[$uid]['avatar']);
			$user['date'] = date('Y-m-d',$addtime);
			$out[] = $user;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 兑换商品
	 */
	public static function exchangeGoods($goods_id,$uid,$idfa='',$mac='')
	{	
		if(!isset($mac) && empty($mac) && !isset($idfa) && empty($idfa))
		{
			return 9;	
		}
		$todaystarttime = strtotime(date('Y-m-d 0:0:0'));
		$todayendtime = strtotime(date('Y-m-d 23:59:59'));
		$goods = ShopGoods::db()->where('id','=',$goods_id)->first();
	    
		if(!$goods){
			return 1;//商品不存在
		}
		
		if($goods['limit_flag']==1){
			if($goods['day_limit_goods_last']<=0){
				return 2;//商品已经兑换完了
			}
		}
	    
		$max_exchange_times = (int)$goods['max_exchange_times'];
		$exchange_times = ShopGoodsAccount::db()->where('uid','=',$uid)->where('goods_id','=',$goods_id)->count();

		//新增了兑换次数的限制
		if($max_exchange_times==1){	
			if(isset($idfa) && !empty($idfa))
			{			
				$today_exchange_account = TodayExchangeAccount::db()->where('goods_id','=',$goods_id)->where('idfa','=',$idfa)->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
				if($today_exchange_account)
				{
					return 6;
				}
			}
			elseif(isset($mac) && !empty($mac))
			{
				if($mac=='02:00:00:00:00:00')
				{
					return 8;
				}
				$today_exchange_account = TodayExchangeAccount::db()
										  ->where('goods_id', $goods_id)->where('mac', $mac)
										  ->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
			    if($today_exchange_account)
			    {
			    	return 6;
			    }
			}			
			if($exchange_times>0){
				return 0;//已经兑换过
			}
		}elseif($max_exchange_times>0){
			if(isset($idfa) && !empty($idfa))
			{
				$today_exchange_account =TodayExchangeAccount::db()
										  ->where('goods_id', $goods_id)->where('idfa', $idfa)
										  ->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
				if($today_exchange_account)
				{
					return 7;
				}
			}
			elseif(isset($mac) && !empty($mac))
			{
				if($mac=='02:00:00:00:00:00')
					{
						return 8;
					}
				$today_exchange_account = TodayExchangeAccount::db()
										  ->where('goods_id', $goods_id)->where('mac', $mac)
										  ->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
			    if($today_exchange_account)
			    {
			    	return 7;
			    }
			}			
			if($exchange_times>=$max_exchange_times){
				return 0;//已经兑换过
			}
		}elseif($max_exchange_times==0){
			if(isset($idfa) && !empty($idfa))
			{
				$today_exchange_account =TodayExchangeAccount::db()
										  ->where('goods_id', $goods_id)->where('idfa', $idfa)
										  ->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
				if($today_exchange_account)
				{
					return 7;
				}
			}
			elseif(isset($mac) && !empty($mac))
			{
				if($mac=='02:00:00:00:00:00')
					{
						return 8;
					}
				$today_exchange_account = TodayExchangeAccount::db()
										  ->where('goods_id', $goods_id)->where('mac', $mac)
										  ->whereBetween('ctime', array($todaystarttime, $todayendtime))->get();
			    if($today_exchange_account)
			    {
			    	return 7;
			    }
			}			
		}		
		
		$time = time();
		if($goods['starttime']>$time){
			return 4;
		}
		if($goods['endtime']<$time){
			return 5;
		}
// 		$user_credit = CreditAccount::db()->where('uid','=',$uid)->first();
		$user_credit['score'] = UserService::getUserRealTimeCredit($uid,'score');
		if(!$user_credit){
			return 3;//游币不足
		}
		if($user_credit['score']<$goods['score']){
			return 3;//游币不足
		}
		$result = self::dbClubMaster()->transaction(function()use($goods_id,$uid,$goods,$idfa,$mac){			
			
        	//兑换记录
        	$exchange = array('goods_id'=>$goods_id,'uid'=>$uid,'score'=>$goods['score'],'addtime'=>time(),'adddate'=>mktime(0,0,0,date('m'),date('d'),date('Y')));
        	if((int)$goods['gtype']==1){
        		$exchange['goods_type'] = 1;
        		$exchange['expense'] = $goods['expense'];
        		$exchange['cardno'] = ''; 
        	}elseif((int)$goods['gtype']==2){
        		
        		$gift = GiftbagService::lockGiftbagCardNo($goods['gift_id']);
        		if($gift){
        			$exchange['cardno'] = $gift['cardno'];
        			$exchange['expense'] = '';
        			$exchange['goods_type'] = 2;
        			GiftbagService::updateGiftbagCardStatus($gift['id'],$uid,false);
        		}else{
        			return 2;
        		}
        	}
        	
        	
        	$today_exchange['goods_id'] = $goods_id;
        	$today_exchange['idfa'] = $idfa;
        	$today_exchange['mac'] = $mac;
        	$today_exchange['ctime'] = time();
        	
		    
			
			//减库存
			$can_buy = 0;
			if((int)$goods['gtype']==1){//实物类
        	    $can_buy = ShopGoods::db()->where('id','=',$goods_id)->whereRaw('totalnum > usednum')->increment('usednum');
			}elseif((int)$goods['gtype']==2){//虚拟类				
				$can_buy = Giftbag::db()->where('id','=',$goods['gift_id'])->whereRaw('last_num > 0')->decrement('last_num');
				$can_buy > 0 && ShoppingService::dbClubMaster()->table('shop_goods')->where('id','=',$goods_id)->increment('usednum');
			}
			
			if($can_buy==0){				
				return 2;
			}
			//减游币
        	$can_buy = CreditService::handOpUserCredit($uid, -$goods['score'],0,'goods_exchange','兑换商品《'.$goods['name'].'》花费'.$goods['score'].'游币');
        	if($can_buy===false){
        		ShoppingService::dbClubMaster()->rollBack();
        		return 3;
        	}
        	
        	/*
		    $goods = ShoppingService::dbClubSlave()->table('shop_goods')->where('id','=',$goods_id)->first();
			if((int)$goods['gtype']==1){
				if($goods['usednum']>=$goods['totalnum']){
					return 2;//商品已经兑换完了
					
				}
			}elseif((int)$goods['gtype']==2){
				$giftinfo = ShoppingService::dbClubSlave()->table('giftbag')->where('id','=',$goods['gift_id'])->first();
				if(!$giftinfo['last_num']){
					return 2;//商品已经兑换完了
				}
			}
			*/
        	
			ShopGoodsAccount::db()->insertGetId($exchange);
        	//把设备今天兑换的商品记录到today_exchange_account表中
        	TodayExchangeAccount::db()->insertGetId($today_exchange);
        	
        	
        	if($goods['limit_flag']==1){
        	    ShopGoods::db()->where('id','=',$goods_id)->decrement('day_limit_goods_last');
        	}        	
        	
        	//发通知
        	PromptService::addMyGoodsMsgNum($uid);
        	$params = array('goods_id'=>$goods_id,'goods_name'=>$goods['name'],'expense'=>$goods['expense'],'cardno'=>$exchange['cardno']);
        	NoticeService::sendShopGoodsExchange($uid,$goods['gtype'],$params);
        	return true;        	
        });
        if($result){
        	$myexchange = ShopGoodsAccount::db()->where('uid','=',$uid)->where('goods_id','=',$goods_id)->first();
        }
		return $result===true ? array('type'=>intval($myexchange['goods_type']==2) ? 0 : 1,'title'=>$goods['name'],'content'=>intval($myexchange['goods_type']==2) ? '激活码:'.$myexchange['cardno'] : $myexchange['expense']) : $result;
	}
}