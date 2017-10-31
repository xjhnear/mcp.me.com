<?php
use Yxd\Services\UserService;
use Yxd\Services\ShoppingService;
use Yxd\Services\TaskService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Yxd\Modules\System\SettingService;
/**
 * 商品
 */
class ShopController extends BaseController
{
	/**
	 * 商品分类列表
	 * 3.1.0新增的接口
	 * 
	 */
	public function CateList()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$result = ShoppingService::getCateList($page,$pagesize);
		$out = array();
		$config = SettingService::getConfig('home_picture_setting');
		$picture = $config ? $config['data'] : array();
		$img = isset($picture['shop_topbar']) ? Config::get('app.img_url') . $picture['shop_topbar'] : '';
		$config = SettingService::getConfig('shop_wish_rule');
		$tid = isset($config['data']['rule_id']) ? $config['data']['rule_id'] : 0;
		$out['topbar'] = array($img,$tid);
		$out['lastlist'] = ShoppingService::getLastExchangeUserInfo(5);
		foreach($result['result'] as $row){
			$cate = array();
			$cate['clid'] = $row['id'];
			$cate['title'] = $row['cate_name'];
			$cate['icon'] = ShoppingService::joinImgUrl($row['icon']);
			$cate['desc'] = $row['summary'];
			$cate['istaobao'] = $row['istaobao'];
			$cate['taobaotype'] = $row['taobaotype'];
			$cate['taobaoid'] = $row['taobaoid'];
			$out['catelist'][] = $cate;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 商品列表
	 * myReward值为0表示取出所有商品的列表，值为1表示取出我兑换过的商品
	 */
	public function goods()
	{
		$mygoods = Input::get('myReward',0);
		if($mygoods==1){
			return $this->mygoods();
		}
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$cate_id = Input::get('cateID',0);
		$result = ShoppingService::getGoodsList($page,$pagesize,$cate_id);
		$out = array();
		foreach($result['goods'] as $index=>$row){
			$goods = array();
			$goods['atid']  = $row['id'];
			$goods['url'] = ShoppingService::joinImgUrl($row['listpic']);
			$goods['title'] = $row['name'];
			$goods['shorttitle'] = $row['shortname'];
			$goods['coinNum'] = $row['score'];
			$goods['isNew'] = $row['isnew'];
			$goods['isHot'] = $row['ishot'];
			$goods['isRecommend'] = $row['isrecommend'];
			$out[] = $goods;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
    /**
	 * 我兑换的商品
	 */
	public function mygoods()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$uid = Input::get('uid');
		$result = ShoppingService::getMyGoodsList($uid,$page,$pagesize);
		if(!$result) return $this->success(array('result'=>array(),'total'=>0));
		$out = array();
		foreach($result['goods'] as $index=>$row){
			$goods = array();
			$goods['atid']  = $row['id'];
			$goods['url'] = ShoppingService::joinImgUrl($row['listpic']);
			$goods['title'] = $row['name'];	
			$goods['coinNum'] = $row['score'];
			$goods['isNew'] = 0;
			$goods['isHot'] = 0;
			$goods['isRecommend'] = 0;
			$out[] = $goods;		
		}
		
		return $this->success(array('result'=>$out,'total'=>$result['total']));
	}
	
	/**
	 * 商品详情
	 * atid表示商品id
	 * uid表示用户id
	 */
	public function goods_detail()
	{		
		$id = Input::get('atid');
		$uid = Input::get('uid',0);
		$goods = ShoppingService::getGoodsInfo($id,$uid);
		$out = array();
		$out['img'] = ShoppingService::joinImgUrl($goods['bigpic_1']);
		$out['title'] = $goods['name'];
		$out['desc'] = $goods['summary'];
		$out['starttime'] = date('Y-m-d H:i:s',$goods['starttime']);
		$out['endtime'] = date('Y-m-d H:i:s',$goods['endtime']);
		$out['use'] = $goods['instruction'];
		$out['needcoinnum'] = $goods['score'];
		$out['ishas'] = isset($goods['ishas']) ? $goods['ishas'] : 0;
		if($goods['limit_flag']==1){
			$out['lastnum'] = $goods['day_limit_goods_last']>0 ? $goods['day_limit_goods_last'] : 0;
		}else{
			$out['lastnum'] = abs($goods['totalnum']-$goods['usednum']);
		}
		$out['totalnum'] = $goods['totalnum'];
		$out['exchangeinfo'] = $goods['exchangeinfo'];
		$out['exchangelist'] = ShoppingService::exchangeList($id);
		
		$out['status'] = 0;//可兑换
		
		//if(intval($goods['usednum'])>=intval($goods['totalnum'])){
		if($out['lastnum']==0){
			$out['status'] = 4;//已兑换完
		}
				
		if((int)$goods['ishas'] == 1){
			if($goods['surplus_exchange_times']>0){
				$out['status'] = 5;//已兑换,还可再次兑换
			}else{
			    $out['status'] = 1;//已兑换
			}
		}elseif($goods['endtime'] <= time()){
			$out['status'] = 2;//已过期
		}elseif($uid>0){
			$user_score = UserService::getUserRealTimeCredit($uid,'score');
			if($user_score<$goods['score']){
				$out['status'] = 3;//游币不足
			}
		}
				
		return $this->success(array('result'=>$out));		
	}	
	
	/**
	 * 兑换商品
	 * atid 商品id
	 * idfa或mac有一个是必须的
	 * 当只有mac一个值得时候，mac不能等于02:00:00:00:00:00
	 */
	public function exchange()
	{
		$id = Input::get('atid');
		$uid = Input::get('uid');
		//$idfa = Input::get('idfa');
		//$mac = Input::get('mac');
		$idfa = UserService::getUserAppleIdentifyBy($uid,'idfa');
		$mac  = UserService::getUserAppleIdentifyBy($uid,'mac');
		
		$result = ShoppingService::exchangeGoods($id,$uid,$idfa,$mac);
		if(is_array($result)){
			return $this->success(array('result'=>$result));
		}elseif($result===false){
			return $this->fail('11211','兑换失败');
		}elseif($result===0){
			return $this->fail('11211','已经兑换过该商品了');
		}elseif($result===1){
			return $this->fail('11211','商品不存在');
		}elseif($result===2){
			return $this->fail('-1','来晚了，商品已经被兑换完了');
		}elseif($result===3){
			return $this->fail('11211','游币不足');
		}elseif($result===4){
			return $this->fail('11211','商品兑换活动尚未开始');
		}elseif($result===5){
			return $this->fail('11211','商品兑换活动已结束');
		}elseif($result===6){
			return $this->fail('11211','您的设备已经兑换过该商品');
		}elseif($result===7){
			return $this->fail('11211','您的设备今天已经兑换过该商品，请明天再来!');
		}elseif($result===8){
			return $this->fail('11211','如果只给mac赋值，则mac不等于02:00:00:00:00:00');
		}elseif($result===9){
			return $this->fail('11211','idfa或者mac两者必须有一个被赋值并且不能为空，如果只给mac赋值，则mac！=02:00:00:00:00:00');
		}
	}
}