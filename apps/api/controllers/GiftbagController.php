<?php
use Illuminate\Support\Facades\Input;

use Yxd\Modules\Activity\GiftbagService;
use Yxd\Services\UserService;
use Yxd\Services\Cms\GameService;

class GiftbagController extends BaseController
{	
/**
	 * 礼包首页
	 */
	public function home()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$uid = Input::get('uid',0);
		$gid = Input::get('gid',0);
		$out = array();
		if($uid){
		    $mygift = GiftbagService::getMyCardNoList($uid);
		}else{
			$mygift = null;
		}
		$result = GiftbagService::getList($gid,$page,$pagesize);
		$gids = array();
		foreach($result['result'] as $index=>$row){
			$gids[] = $row['game_id'];
		}
		
		$hots = array();
		if($page==1 && $gid==0){
			$hots = GiftbagService::getHotList();
			foreach($hots as $row)
			{
				$gids[] = $row['game_id'];
			}
		}
		$total = count($hots) + (int)$result['total'];
		$games = GameService::getGamesByIds($gids);
		$giftbags = array_merge($hots,$result['result']);
		foreach($giftbags as $index=>$row){
			$gift = array();
			$gift['gfid'] = $row['id'];
			$ico = isset($games[$row['game_id']]) ? $games[$row['game_id']]['ico'] : '';
			$gift['url'] = $this->joinImgUrl($ico);
			$gift['gname'] = isset($games[$row['game_id']]) ? $games[$row['game_id']]['shortgname'] : '';
			$gift['title'] = $row['title'];
			$gift['date'] = date('Y-m-d',$row['ctime']);
			$gift['adddate'] = date('Y-m-d',$row['ctime']);
			$gift['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$gift['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
			$gift['ishot'] = $row['is_hot'];
			$gift['istop'] = $row['is_top'];
			$gift['cardcount'] = $row['total_num'];
			$gift['lastcount'] = $row['last_num'];
			$ishas = false;
			$number = '';
			if($mygift && is_array($mygift)){
				$mygift_ids = array_keys($mygift);
				$ishas = in_array($row['id'],$mygift_ids);
				if($ishas){
					$number = $mygift[$row['id']];
				}
			}
			$gift['ishas'] = (int)$ishas;
			$gift['numbers'] = $number;
			$out[] = $gift;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
	
	/**
	 * 搜索礼包
	 */
	public function search()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$uid = Input::get('uid',0);
		$keyword = Input::get('keyword');
		if(empty($keyword)){
			return $this->home();
		}
		$out = array();
		if($uid){
		    $mygift = GiftbagService::getMyCardNoList($uid);
		}else{
			$mygift = null;
		}
		$result = GiftbagService::search($keyword,$page,$pagesize);
		
		$gids = array();
		foreach($result['result'] as $index=>$row){
			$gids[] = $row['game_id'];
		}
		$games = GameService::getGamesByIds($gids);
		
		
	    foreach($result['result'] as $index=>$row){
			$gift = array();
			$gift['gfid'] = $row['id'];
			$ico = isset($games[$row['game_id']]) ? $games[$row['game_id']]['ico'] : '';
			$gift['url'] = $this->joinImgUrl($ico);
			$gift['gname'] = $games[$row['game_id']]['shortgname'];
			$gift['title'] = $row['title'];
			$gift['date'] = date('Y-m-d',$row['ctime']);
			$gift['adddate'] = date('Y-m-d',$row['ctime']);
			$gift['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$gift['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
			$gift['ishot'] = $row['is_hot'];
			$gift['istop'] = $row['is_top'];
			$gift['cardcount'] = $row['total_num'];
			$gift['lastcount'] = $row['last_num'];
			$ishas = false;
			$number = '';
			if($mygift && is_array($mygift)){
				$mygift_ids = array_keys($mygift);
				$ishas = in_array($row['id'],$mygift_ids);
				if($ishas){
					$number = $mygift[$row['id']];
				}
			}
			$gift['ishas'] = (int)$ishas;
			$gift['numbers'] = $number;
			$out[] = $gift;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 礼包详情
	 */
	public function detail()
	{
		$gift_id = Input::get('gfid');
		$uid = Input::get('uid',0);
		if(!$gift_id){
			return $this->fail(11211,'礼包不存在');
		}
		$gift = GiftbagService::getDetail($gift_id,$uid);
		if($gift){
			$out = array();
			$out['gfid']   = $gift['id'];			
			$out['title']  = trim($gift['title']);
			$out['gid']    = $gift['game_id'] ? $gift['game_id']  : 0;
			$out['gname']  = trim($gift['game']['shortgname']) ? trim($gift['game']['shortgname']) : $gift['game']['gname'];
			$out['url']    = self::joinImgUrl(($gift['listpic'] ? $gift['listpic'] : $gift['game']['ico']));
			$out['starttime'] = date('Y-m-d H:i:s',$gift['starttime']);
			$out['endtime'] = date('Y-m-d H:i:s',$gift['endtime']);
			$out['ishas']  = $gift['ishas'];
			$out['number'] = $gift['cardno'];
			$out['btnshow'] = $uid ? ($gift['is_appoint'] ? GiftbagService::isGiftbagAppointUser($gift_id, $uid) : 1) : 1;
			$out['cardcount']	=	$gift['total_num'];
			$out['lastcount']	=	$gift['last_num'];
			$out['needTourCurrency'] = isset($gift['condition']['score']) ? $gift['condition']['score'] : 0;
			$out['remainTourCurrency'] = $uid ? UserService::getUserRealTimeCredit($uid,'score') : 0;
			$out['company'] = $gift['game']['company'];
			$out['body'] = $gift['content'];
			return $this->success(array('result'=>$out));
		}
		return $this->fail(11211,'礼包不存在');
	}
	
	/**
	 * 我的礼包
	 */
	public function myGift()
	{
		$uid = Input::get('uid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$result = GiftbagService::getMyGift($uid,$page,$pagesize);
		$out = array();
		$games = $gifts = $gift_ids = $game_ids = array();
		foreach($result['result'] as $row){
			$gift_ids[] = $row['gift_id'];
			$game_ids[] = $row['game_id'];
		}
		
		$_gifts = GiftbagService::getListByIds($gift_ids);
		foreach($_gifts as $row){
			$gifts[$row['id']] = $row;
		}
		
		$games = GameService::getGamesByIds($game_ids);
	    foreach($result['result'] as $key=>$row){
			if(!isset($gifts[$row['gift_id']])) continue;
	    	if(isset($games[$row['game_id']])){
				$gname = $games[$row['game_id']]['shortgname'];
				$ico = $games[$row['game_id']]['ico'];
			}else{
				$gname = $gifts[$row['gift_id']]['gname'];
				$ico = $gifts[$row['gift_id']]['pic'];
			}			
			$gift = array();
			$gift['gfid'] = $row['gift_id'];
			$gift['url'] = $this->joinImgUrl($ico);
			$gift['gname'] = $gname;
			$gift['title'] = $gifts[$row['gift_id']]['title'];
			$gift['date'] = date('Y-m-d H:i:s',$gifts[$row['gift_id']]['starttime']);
			$gift['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$gift['number'] = $row['card_no'];
			$out[] = $gift;
		}
		if((int)$result['total']==0){
			return $this->success(array('result'=>array(),'totalCount'=>0));
		}else{
			return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
		}
	}
	
    /**
	 * 领取礼包
	 */
	public function getGift()
	{
		$uid = Input::get('uid');
		$gift_id = Input::get('gfid');
		
		if(!$uid || !$gift_id){
			return $this->fail('参数错误');
		}
		
		$card = GiftbagService::doMyGift($gift_id, $uid);
		if($card==-2){
			return $this->fail('11211','该礼包为活动专属礼包，只有参加活动的用户才能领取哦，如有问题请在“意见反馈”中及时和客服联系，谢谢！');
		}elseif($card==-1){
			return $this->fail('11211','礼包不存在');
		}elseif($card===0){
			return $this->fail('11211','礼包已经被领完');
		}elseif($card===1){
			return $this->fail('11211','礼包领取失败');
		}elseif($card===2){
		    return $this->fail('11211','游币不足');
		}else{
			return $this->success(array('result'=>$card));
		}
	}
	
	/**
	 * 我的预定
	 */
	public function myReserveGift()
	{
		$uid = Input::get('uid');
	    $page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$result = GiftbagService::myReserve($uid,$page,$pagesize);		
		$out = array();
		foreach($result['result'] as $row){
			$reserve = array();			
			$reserve['gid'] = $row['game_id'];
			$reserve['url'] = self::joinImgUrl($row['game']['ico']);
			$reserve['gname'] = $row['game']['shortgname'];
			$reserve['bookdate'] = date('Y-m-d H:i:s',$row['addtime']);			
			$reserve['gfid'] = $row['gift_id'] ? : '';
			$out[] = $reserve;
		}
		
	    return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 我的预定-删除
	 */
	public function removeMyReserveGift()
	{
		$game_id = Input::get('gid');
		$uid = Input::get('uid');
		GiftbagService::removeMyReserve($game_id, $uid);
		return $this->success(array('result'=>null));
	}
		
	/**
	 * 预定礼包
	 */
	public function reserveGift()
	{
		$uid = Input::get('uid',0);
		$game_id = Input::get('gid');
		$result = GiftbagService::doMyReserve($game_id, $uid);
		if($result>0){
			return $this->success(array('result'=>array()));
		}elseif($result===-1){
			return $this->fail('11211','该游戏礼包已经预定');
		}else{
			return $this->fail('11211','礼包预定失败');
		}
	}	
}