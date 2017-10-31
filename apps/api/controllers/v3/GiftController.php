<?php
/**
 * 礼包
 */

use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Cms\GiftService;

class GiftController extends BaseController
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
		    $mygift = GiftService::getMyGiftIds($uid);
		}else{
			$mygift = null;
		}
		$result = GiftService::getGiftList($gid,$page,$pagesize);
		$gids = array();
		foreach($result['gifts'] as $index=>$row){
			$gids[] = $row['gid'];
		}
		$games = GameService::getGamesByIds($gids);
		
		foreach($result['gifts'] as $index=>$row){
			$out[$index]['gfid'] = $row['id'];
			$ico = isset($games[$row['gid']]) ? $games[$row['gid']]['ico'] : '';
			$out[$index]['url'] = GiftService::joinImgUrl($row['pic'] ? $row['pic'] : $ico);
			$out[$index]['gname'] = !empty($row['gname']) ? $row['gname']: $games[$row['gid']]['shortgname'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['date'] = $row['adddate'];
			$out[$index]['adddate'] = date('Y-m-d',$row['addtime']);
			$out[$index]['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$out[$index]['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
			$out[$index]['ishot'] = $row['ishot'];
			$out[$index]['istop'] = $row['istop'];
			$out[$index]['cardcount'] = $row['total_num'];
			$out[$index]['lastcount'] = $row['last_num'];
			$ishas = false;
			$number = '';
			if($mygift && is_array($mygift)){
				$mygift_ids = array_keys($mygift);
				$ishas = in_array($row['id'],$mygift_ids);
				if($ishas){
					$number = $mygift[$row['id']];
				}
			}
			$out[$index]['ishas'] = (int)$ishas;
			$out[$index]['numbers'] = $number;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
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
		$out = array();
		if($uid){
		    $mygift = GiftService::getMyGiftIds($uid);
		}else{
			$mygift = null;
		}
		$result = GiftService::searchList($keyword,$page,$pagesize);
		
		$gids = array();
		foreach($result['gifts'] as $index=>$row){
			$gids[] = $row['gid'];
		}
		$games = GameService::getGamesByIds($gids);
		
		
		foreach($result['gifts'] as $index=>$row){
			$out[$index]['gfid'] = $row['id'];
			$ico = isset($games[$row['gid']]) ? $games[$row['gid']]['ico'] : '';
			$out[$index]['url'] = GiftService::joinImgUrl($row['pic'] ? $row['pic'] : $ico);
			$out[$index]['gname'] = $row['gname'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['date'] = $row['adddate'];
			$out[$index]['adddate'] = date('Y-m-d',$row['addtime']);
			$out[$index]['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$out[$index]['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
			$out[$index]['ishot'] = $row['ishot'];
			$out[$index]['istop'] = $row['istop'];
			$out[$index]['cardcount'] = $row['total_num'];
			$out[$index]['lastcount'] = $row['last_num'];
			$ishas = false;
			$number = '';
			if($mygift && is_array($mygift)){
				$mygift_ids = array_keys($mygift);
				$ishas = in_array($row['id'],$mygift_ids);
				if($ishas){
					$number = $mygift[$row['id']];
				}
			}
			$out[$index]['ishas'] = (int)$ishas;
			$out[$index]['numbers'] = $number;
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
			return $this->fail(1121,'礼包不存在');
		}
		$gift = GiftService::getGiftDetail($gift_id,$uid);
		if($gift){
			return $this->success($gift);
		}
		return $this->fail(1121,'礼包不存在');
	}
	
	/**
	 * 我的礼包
	 */
	public function myGift()
	{
		$uid = Input::get('uid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$result = GiftService::getMyGift($uid,$page,$pagesize);
		if($result===null){
			return $this->success(array('result'=>array(),'totalCount'=>0));
		}else{
			return $this->success($result);
		}
	}
	
    /**
	 * 领取礼包
	 */
	public function getGift()
	{
		$uid = Input::get('uid');
		$gift_id = Input::get('gfid');
		$card = GiftService::doMyGift($gift_id, $uid);
		if($card==-1){
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
		$result = GiftService::myReserve($uid,$page,$pagesize);
	    return $this->success($result);
	}
	
	/**
	 * 我的预定-删除
	 */
	public function removeMyReserveGift()
	{
		$game_id = Input::get('gid');
		$uid = Input::get('uid');
		GiftService::removeMyReserve($game_id, $uid);
		return $this->success(array('result'=>null));
	}
		
	/**
	 * 预定礼包
	 */
	public function reserveGift()
	{
		$uid = Input::get('uid');
		$game_id = Input::get('gid');
		$result = GiftService::doMyReserve($game_id, $uid);
		if($result>0){
			return $this->success(array('result'=>array()));
		}elseif($result===-1){
			return $this->fail('11211','该游戏礼包已经预定');
		}else{
			return $this->fail('11211','礼包预定失败');
		}
	}
	
	/**
	 * 礼包通知
	 */
	public function notice()
	{
		$gift_id = Input::get('gift_id');
		$game_id = Input::get('game_id');
		$result = GiftService::addNotice($gift_id, $game_id);
		return $this->success(array('result'=>null));
	}
	
}