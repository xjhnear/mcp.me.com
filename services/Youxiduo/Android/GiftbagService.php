<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;

use Youxiduo\Android\Model\UserDevice;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\GiftbagCard;
use Youxiduo\Android\Model\Reserve;
use Youxiduo\V4\User\Model\MobileBlackList;
use Youxiduo\V4\User\Model\LoginIpBlackList;
use Youxiduo\User\Model\UserMobile;
use Youxiduo\User\Model\Account;
use Youxiduo\User\AccountService;
use Youxiduo\Android\Model\GiftbagAccount;

use Youxiduo\Message\YouPushService;

class GiftbagService extends BaseService
{
	/**
	 * 客户端礼包列表
	 */
	public static function getList($uid,$pageIndex,$pageSize,$gid=0,$return_arr=false)
	{
		$total = Giftbag::getCount($gid);
		$result = Giftbag::getList($gid,$pageIndex,$pageSize);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		
		$games = Game::getListByIds($gids);
		
		foreach($result as $row){
			$data = array();
			$gid = $row['game_id'];
			$data['gfid'] = $row['id'];			
			$data['title'] = $row['title'];
			$data['adddate'] = date('Y-m-d',$row['ctime']);
			$data['date'] =date('Y-m-d',$row['ctime']);
			$data['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtime'] = $row['endtime'] ? date('Y-m-d H:i:s',$row['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$data['url'] = Config::get('app.image_url') . (empty($row['listpic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['listpic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$data['ishot'] = $row['is_hot'] ? true : false;
			$data['istop'] = $row['is_top'] ? true : false;
			$data['cardcount'] = $row['total_num'];
			$data['lastcount'] = $row['last_num'];
			$data['ishas'] = false;
			$data['number'] = '';
			if($uid){
				Reserve::db()->where('uid','=',$uid)->where('gift_id','=',$row['id'])->update(array('gift_id'=>0));
				$card = GiftbagAccount::getMyGiftbagInfo($row['id'],$uid);
				if($card){
					$data['ishas'] = true;
					$data['number'] = $card['card_no'];
				}
			}
			$out[] = $data;
		}

        if($return_arr){
            return array('result'=>$out,'totalCount'=>$total);
        }else{
            return self::trace_result(array('result'=>$out,'totalCount'=>$total));
        }
	}
	
    public static function getListByGameIds($gids,$pageIndex,$pageSize,$uid=0)
	{
		$total = Giftbag::getTotalCountByGameIds($gids,true);
		$result = Giftbag::getListByGameIds($gids,$pageIndex,$pageSize,true);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		
		$games = Game::getListByIds($gids);
		
		foreach($result as $row){
			$data = array();
			$gid = $row['game_id'];
			$data['gfid'] = $row['id'];			
			$data['title'] = $row['title'];
			$data['adddate'] = date('Y-m-d',$row['ctime']);
			$data['date'] =date('Y-m-d',$row['ctime']);
			$data['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtime'] = $row['endtime'] ? date('Y-m-d H:i:s',$row['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$data['url'] = Config::get('app.image_url') . (empty($row['listpic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['listpic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$data['ishot'] = $row['is_hot'] ? true : false;
			$data['istop'] = $row['is_top'] ? true : false;
			$data['cardcount'] = $row['total_num'];
			$data['lastcount'] = $row['last_num'];
			$data['ishas'] = false;
			$data['number'] = '';
			if($uid){
				Reserve::db()->where('uid','=',$uid)->where('gift_id','=',$row['id'])->update(array('gift_id'=>0));
				$card = GiftbagAccount::getMyGiftbagInfo($row['id'],$uid);
				if($card){
					$data['ishas'] = true;
					$data['number'] = $card['card_no'];
				}
			}
			$out[] = $data;
		}
		
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function search($keyword,$pageIndex,$pageSize=10,$uid=0,$format=false)
	{
		$total = Giftbag::searchCount($keyword);
		$result = Giftbag::searchResult($keyword, $pageIndex, $pageSize);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		
		$games = Game::getListByIds($gids);
		
		foreach($result as $row){
			$data = array();
			$gid = $row['game_id'];
			$data['gfid'] = $row['id'];			
			$data['title'] = $row['title'];
			$data['adddate'] = date('Y-m-d',$row['ctime']);
			$data['date'] =date('Y-m-d',$row['ctime']);
			$data['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtime'] = $row['endtime'] ? date('Y-m-d H:i:s',$row['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$data['url'] = Config::get('app.image_url') . (empty($row['listpic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['listpic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$data['ishot'] = $row['is_hot'] ? true : false;
			$data['istop'] = $row['is_top'] ? true : false;
			$data['cardcount'] = $row['total_num'];
			$data['lastcount'] = $row['last_num'];
			$data['ishas'] = false;
			$data['number'] = '';
			if($uid){
				$card = GiftbagAccount::getMyGiftbagInfo($row['id'],$uid);
				if($card){
					$data['ishas'] = true;
					$data['number'] = $card['card_no'];
				}
			}
			$out[] = $data;
		}
		
		if($format==true) return array('result'=>$out,'totalCount'=>$total);
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	/**
	 * 悬浮礼包列表
	 */
	public static function getSuspendsionList($gid,$uid)
	{
		$total = Giftbag::getCount($gid,true);
		$result = Giftbag::getList($gid,1,20,true);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		
		$games = Game::getListByIds($gids);
		
		foreach($result as $row){
			$data = array();
			$gid = $row['game_id'];
			$data['gfid'] = $row['id'];			
			$data['title'] = $row['title'];
			$data['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtime'] = $row['endtime'] ? date('Y-m-d H:i:s',$row['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$data['url'] = Config::get('app.image_url') . (empty($row['listpic']) && isset($games[$gid]) ? $games[$gid]['ico'] : $row['listpic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$data['cardcount'] = $row['total_num'];
			$data['lastcount'] = $row['last_num'];
			$data['ishas'] = 0;
			$data['number'] = '';
			if($uid){
				$card = GiftbagAccount::getMyGiftbagInfo($row['id'],$uid);
				if($card){
					$data['ishas'] = 1;
					$data['number'] = $card['card_no'];
				}
			}
			$out[] = $data;
		}
		
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function myGiftbag($uid,$pageIndex,$pageSize,$gid=0)
	{
		$user_giftbag_list = GiftbagAccount::myGiftbag($uid,$pageIndex,$pageSize,$gid);
		$total = GiftbagAccount::myGiftbagCount($uid,$gid);
		$giftbag_ids = array();
		foreach($user_giftbag_list as $row){
			$giftbag_ids[] = $row['gift_id'];
		}
		$out = array();
		$giftbag_list = Giftbag::getInfoByIds($giftbag_ids);
		$gids = array();
		foreach($giftbag_list as $row){
			$gids[] = $row['game_id'];
		}
		$games = Game::getListByIds($gids);
		foreach($user_giftbag_list as $row){
			if(!isset($giftbag_list[$row['gift_id']])) continue;
			if(!isset($games[$giftbag_list[$row['gift_id']]['game_id']])) continue;
			$data = array();
			$tmp =  $giftbag_list[$row['gift_id']];
			$data['gfid'] = $tmp['id'];
			$data['url'] = Config::get('app.image_url') . $games[$giftbag_list[$row['gift_id']]['game_id']]['ico'];
			$data['gname'] = $games[$giftbag_list[$row['gift_id']]['game_id']]['shortgname'];
			$data['title'] = $tmp['title'];
			$data['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$data['date'] = date('Y-m-d',$tmp['ctime']);
			$data['number'] = $row['card_no'];
			
			$out[] = $data;
		}
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getDetail($giftbag_id,$uid,$format=false)
	{
		$info = Giftbag::getInfoById($giftbag_id);
		$out = array();
		if($info){
			$games = Game::getListByIds(array($info['game_id']));
			$gid = $info['game_id'];
			$out['gfid'] = $giftbag_id;
			$out['title'] = $info['title'];
			$out['gid'] = $info['game_id'] ? : 0;
			$out['gname'] = empty($info['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$out['url'] = Config::get('app.image_url') . (empty($info['pic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $info['listpic']);
			$out['starttime'] = date('Y-m-d H:i:s',$info['starttime']);
			$out['endtime'] = $info['endtime'] ? date('Y-m-d H:i:s',$info['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$out['ishas'] = false;
			$out['number'] = '';
			$out['cardcount'] = $info['total_num'];
			$out['lastcount'] = $info['last_num'];
			$out['body'] = Utility::formatContent($info['content'], '');
		    if($uid){
		    	//清除预约
		    	Reserve::db()->where('uid','=',$uid)->where('gift_id','=',$giftbag_id)->update(array('gift_id'=>0));
				$card = GiftbagAccount::getMyGiftbagInfo($giftbag_id,$uid);
				if($card){
					$out['ishas'] = true;
					$out['number'] = $card['card_no'];
				}
			}						
		}
		if($format==true) return array('result'=>$out);
		return self::trace_result(array('result'=>$out));
	}
	
	public static function buyGiftbagCard($giftbag_id,$uid,$verifyCode,$ip='')
	{
		//$_ip = $ip;
		//$ip = Input::get('idcode');
		$version = Input::get('version');
		if($ip && !in_array($version,array('2.9.0.3','2.9.1','2.9.2','2.9.2beta','2.9.2.1','2.9.2.2'))){
			return self::trace_error('E1','应用版本过低,请升级最新版后领取');
		}
		$limit_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
	    $limit_num = 5;
	    $allow = LoginIpBlackList::checkIsAllowLoginByIp($ip, $limit_time, $limit_num,$uid,LoginIpBlackList::LIMIT_TYPE_GIFTBAG);
	    if($allow == false){
	    	return self::trace_error('E1','该设备今日礼包领取次数太频繁,已经被禁用');
	    }
		$giftbag = Giftbag::getInfoById($giftbag_id,true);
		if(!$giftbag){
			return self::trace_error('E1','礼包不存在');
		}
		//验证用户状态
		$user = Account::getUserInfoById($uid);
		if(!$user || !$user['mobile'] || MobileBlackList::checkMobileExists($user['mobile'])===true || UserMobile::phoneVerifyStatus($user['mobile'])===false){
			return self::trace_error('E1','用户不存在或未验证手机号');	
		}
		
		$my_card = GiftbagAccount::getMyGiftbagInfo($giftbag_id, $uid);
		$out = array();
		if($my_card){
			$out['number'] = $my_card['card_no'];
			return self::trace_result(array('result'=>$out));
		}else{
			/*
			$card = self::lockGiftbagCardNo($giftbag_id);
			if($card){
								
				$data = array(
				    'gift_id'=>$giftbag_id,
				    'uid'=>$uid,
				    'game_id'=>$giftbag['game_id'],
				    'card_no'=>$card['cardno'],
				    'addtime'=>time()
				);
				GiftbagAccount::addMyGiftbag($data);
				//更新礼包卡剩余数量
				Giftbag::decrementLastNum($giftbag_id);
				
				$success = self::updateGiftbagCardStatus($card['id'],$uid);	
				
				$out['number'] = $card['cardno'];
				return self::trace_result(array('result'=>$out));
			}	
			*/
			$cards = GiftbagCard::getUsableCardList($giftbag_id);
			$success = false;
			$card = array();
			foreach($cards as $key=>$row){
				$success = self::updateGiftbagCardStatus($row['id'],$uid,$ip)>0 ? true : false;
				if($success===true){
					$card = $row;
					break;
				}
			}
			if($success===true && $card){
				$data = array(
				    'gift_id'=>$giftbag_id,
				    'uid'=>$uid,
				    'game_id'=>$giftbag['game_id'],
				    'card_no'=>$card['cardno'],
				    'addtime'=>time()
				);
				GiftbagAccount::addMyGiftbag($data);
				//更新礼包卡剩余数量
				Giftbag::decrementLastNum($giftbag_id);
				$out['number'] = $card['cardno'];
				return self::trace_result(array('result'=>$out));
			}		
		}
		return self::trace_error('E1','礼包已经被领完');						
	}
	
    public static function updateGiftbagCardStatus($id,$uid,$ip)
	{
		return GiftbagCard::updateGiftbagCardStatus($id,$uid,$ip);
	}
	
	
    public static function lockGiftbagCardNo($giftbag_id)
	{
		$queue = 'giftbag::android::queue::'.$giftbag_id;
		$len = Giftbag::redis()->llen($queue);		
		//if($len==0) self::initGiftbagCardNoQueue($giftbag_id);
	    if($len>0){
			$card = Giftbag::redis()->lpop($queue);
			return $card ? unserialize($card) : null;
		}
		return null;
	}
	
	public static function initGiftbagCardNoQueue($giftbag_id)
	{
		$sql = 'update yxd_giftbag as a,(select giftbag_id,count(*) as total from yxd_giftbag_card where is_get=1 group by giftbag_id order by giftbag_id desc) as b set a.last_num= a.total_num-b.total where a.id=b.giftbag_id';
		GiftbagCard::execUpdateBySql($sql);
		$queue = 'giftbag::android::queue::'.$giftbag_id;
		Giftbag::redis()->del($queue);	    			
	    $table = GiftbagCard::getUsableCardList($giftbag_id);
	    $cards = array();
	    foreach($table as $row){
	        $cards[] = serialize($row);
	    }
	    $cards && !empty($cards) && Giftbag::redis()->rpush($queue,$cards);
	    $len = Giftbag::redis()->llen($queue);
	}

	public static function addNewReserve($gid,$uid)
	{
		if(strpos(',',$gid)===false) {
			$gid = (int)$gid;
			if($gid<=0) return self::trace_result(array('result'=>true));
			$exists = Reserve::db()->where('game_id', '=', $gid)->where('uid', '=', $uid)->first();
			if ($exists) return self::trace_error('E1', '该游戏已经预约过了');
			$tag_name = Config::get('yxd.baidu_tags.reserve_giftbag') . $gid;
			YouPushService::createGiftbagReserveNotice($tag_name, $uid);
			$data = array('game_id' => $gid, 'uid' => $uid, 'addtime' => time());
			Reserve::db()->insertGetId($data);
		}else {
			$gids = explode(',', $gid);
			$my_gids = Reserve::db()->where('uid','=',$uid)->lists('game_id');
			if(is_array($my_gids) && $my_gids){
				$diff_gids = array_diff($gids,$my_gids);
				if($diff_gids){
					$data = array();
					foreach($diff_gids as $game_id){
						$game_id = (int)$game_id;
						if($game_id<=0) continue;
						$data[] = array('game_id'=>$gid,'uid'=>$uid,'addtime'=>time());
						$tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$game_id;
						YouPushService::createGiftbagReserveNotice($tag_name, $uid);
					}
					if($data){
						Reserve::db()->insert($data);
					}
				}
			}
		}

		return self::trace_result(array('result'=>true));
	}
	
	public static function addReserve($gid,$uid)
	{
		return self::addNewReserve($gid,$uid);
		/*
		$device_info = UserDevice::getNewestInfoByUid($uid);
	    if(!$device_info) return self::trace_error('E1','预约失败，请重试');
		if(strpos(',',$gid)===false){
			$count = Reserve::db()->where('game_id','=',$gid)->where('uid','=',$uid)->count();
			if($count) return self::trace_error('E1','该游戏已经预约过了');
			$data = array('game_id'=>$gid,'uid'=>$uid,'addtime'=>time());
			
	        $tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$gid;	        
	        //$set_tag = BaiduPushService::setTag($tag_name,$device_info['device_id']);
	        BaiduPushService::createTag($tag_name);
	        $set_tag = BaiduPushService::addTagDevice($tag_name,$device_info['channel_id']);
	        if(!$set_tag) return self::trace_error('E1','预约失败，请重试');
			Reserve::db()->insertGetId($data);
			return self::trace_result(array('result'=>true));
		}else{
			$gids = explode(',',$gid);
			$my_gids = Reserve::db()->where('uid','=',$uid)->lists('game_id');
			if(is_array($my_gids) && $my_gids){
				$diff_gids = array_diff($gids,$my_gids);
				if($diff_gids){
					$data = array();
					foreach($diff_gids as $game_id){
						$data[] = array('game_id'=>$gid,'uid'=>$uid,'addtime'=>time());
						$tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$game_id;	        
				        BaiduPushService::createTag($tag_name);
				        $set_tag = BaiduPushService::addTagDevice($tag_name,$device_info['channel_id']);
					}
					if($data){
						Reserve::db()->insert($data);
					}
				}
			}
			
			return self::trace_result(array('result'=>true));
		}
		*/
	}

	public static function deleteNewReserve(array $gids,$uid)
	{
		foreach($gids as $row){
			$tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$row;
			$tag_name && YouPushService::removeGiftbagReserveNotice($tag_name,$uid);
		}
		Reserve::db()->whereIn('game_id',$gids)->where('uid','=',$uid)->delete();
		return self::trace_result(array('result'=>true));
	}
	
    public static function deleteReserve($gids,$uid)
	{
		return self::deleteNewReserve($gids,$uid);
		/*
        $device_info = UserDevice::getNewestInfoByUid($uid);
        if($device_info && $gids){
            foreach($gids as $row){
                $tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$row;
                $tag_name && BaiduPushService::removeTag($tag_name,$device_info['channel_id']);
            }
        }
		Reserve::db()->whereIn('game_id',$gids)->where('uid','=',$uid)->delete();
		return self::trace_result(array('result'=>true));
		*/
	}
	
    public static function getMyReserve($uid)
	{
		$result = Reserve::db()->where('uid','=',$uid)->get();
		if($result){
			$gids = array();
			foreach($result as $row){
				$gids[] = $row['game_id'];
			}
			$games = Game::getListByIds($gids);
			$out = array();
			foreach($result as $row){
				if(!isset($games[$row['game_id']])) continue;
				$tmp = array();
				$game = $games[$row['game_id']];
				$tmp['gid'] = $game['id'];
				$tmp['gname'] = $game['shortgname'];
				$tmp['ico'] = Config::get('app.image_url') . $game['ico'];
				$tmp['is_has'] = $row['gift_id']>0 ? true : false;
				$tmp['gfid'] = $row['gift_id'];
				
				$out[] = $tmp;
			}
			return self::trace_result(array('result'=>$out));
		}
		
		return self::trace_result(array('result'=>array()));
	}

	public static function initBaiduTag()
	{
		ini_set('max_execution_time',0);
		ini_set('memory_limit','2048M');
		$prefix = Config::get('yxd.baidu_tags.reserve_giftbag');
		$result = Reserve::db()->get();
		$game_ids = array();
		$uids = array();
		foreach($result as $row){
			if(!in_array($row['game_id'],$game_ids)){
				$game_ids[] = $row['game_id'];
			}
			if(!in_array($row['uid'],$uids)){
				$uids[] = $row['uid'];
			}
		}

		foreach($game_ids as $gid){

			YouPushService::CreateTag($prefix . $gid,3);
		}
		foreach($result as $row){
			YouPushService::bindUserToTag($row['uid'],$prefix.$row['game_id'],3);
		}
	}
}

