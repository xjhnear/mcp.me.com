<?php
namespace Yxd\Services\Cms;

use Illuminate\Support\Facades\Config;

use Yxd\Services\CreditService;

use Yxd\Services\UserFeedService;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;

use ApnsPHP\AbstractClass;
use ApnsPHP\Push;
use ApnsPHP\Message\Custom;

class GiftService extends Service
{
	protected static $CONN = 'cms';
	
	/**
	 * 礼包列表
	 */
	public static function getGiftList($gid,$page=1,$pagesize=10)
	{
		
		$tb = DB::connection(self::$CONN)->table('gift')->where('apptype','!=',2)
		         ->where('isshow','=',1)
		         ->where('istop','=',0);
		         
		if($gid>0) {
			$tb = $tb->where('gid','=',$gid);
		}else{
			$tb = $tb->where('gid','>',0);
		}         
		$total = $tb->count();
		$gifts = $tb->orderBy('adddate','desc')
		       ->orderBy('sort','desc')->orderBy('addtime','desc')
		       ->forPage($page,$pagesize)
		       ->get();
		$hots = DB::connection(self::$CONN)->table('gift')->where('apptype','!=',2)
		         ->where('isshow','=',1)
		         ->where('istop','=',1)->orderBy('adddate','desc')->orderBy('sort','desc')->orderBy('addtime','desc')->get();
		if($page==1 && $gid==0){
			$total = $total + count($hots);
			$gifts = array_merge($hots,$gifts);
		}
		return array('total'=>$total,'gifts'=>$gifts);
	}

	public static function searchList($keyword,$page=1,$pagesize=10)
	{
		$gids = DB::connection(self::$CONN)->table('games')->where('isdel','=',0)->where('shortgname','like','%'.$keyword . '%')->lists('id','ico');		
		if(!$gids){
			return array('gifts'=>array(),'total'=>0);
		}
		
		$tb = DB::connection(self::$CONN)->table('gift')
		         ->where('isshow','=',1)
		         ->where('istop','=',0);
		         
		if($gids) $tb = $tb->whereIn('gid',array_values($gids));
		$games = array_flip($gids);         
		$total = $tb->count();
		$gifts = $tb->orderBy('adddate','desc')
		       ->orderBy('sort','desc')
		       ->forPage($page,$pagesize)
		       ->get();
		foreach($gifts as $key=>$row){
			if(!$row['pic']){
				if(isset($games[$row['gid']])) $row['pic'] = $games[$row['gid']];
			}
			$gifts[$key] = $row;
		}       
		return array('total'=>$total,'gifts'=>$gifts);
	}
	
	/**
	 * 礼包信息
	 */
	public static function getGiftInfo($gift_id)
	{
		$gift = DB::connection(self::$CONN)->table('gift')->where('id','=',$gift_id)->first();
		return $gift;
	}
	
	/**
	 * 礼包详情
	 */
	public static function getGiftDetail($gift_id,$uid=0)
	{
		$gift = DB::connection(self::$CONN)->table('gift')->where('id','=',$gift_id)->where('isshow','=',1)->first();
		if(!$gift){
			return null;
		}
		$game = GameService::getGameInfo($gift['gid']);
		$card = DB::connection('sqlite')->table('gift_card')->where('gfid','=',$gift_id)->where('uid','=',$uid)->where('number','!=','')->first();
		if($card){
			$ishas = true;
			$number = $card['number'];
		}else{
			$ishas = false;
			$number = '';
		}
		$out = array();
		$out['gfid']   = $gift['id'];			
		$out['title']  = trim($gift['title']);
		$out['gid']    = $gift['gid'] ? $gift['gid']  : 0;
		$out['gname']  = trim($game['shortgname']) ? trim($game['shortgname']) : $gift['gname'];
		$out['url']    = self::joinImgUrl(($gift['pic'] ? $gift['pic'] : $game['ico']));
		$out['starttime'] = date('Y-m-d H:i:s',$gift['starttime']);
		$out['endtime'] = date('Y-m-d H:i:s',$gift['endtime']);
		$out['ishas']  = (int)$ishas;
		$out['number'] = $number;
		$out['cardcount']	=	$gift['total_num'];
		$out['lastcount']	=	$gift['last_num'];
		$out['needTourCurrency'] = $gift['require_score'];
		$out['remainTourCurrency'] = $uid ? UserService::getUserRealTimeCredit($uid,'score') : 0;
		$out['company'] = '';
		//$out['body'] = $this->_getContent($row['content'],$row['video_url'],$row['video_pic']);
		$out['body'] = $gift['content'];	
		return array('result'=>$out);
	}
	
	/**
	 * 我的礼包
	 */
	public static function getMyGiftIds($uid)
	{
		return DB::table('gift_account')->where('uid','=',$uid)->orderBy('addtime','desc')->lists('card_no','gift_id');
	}
	
	/**
	 * 我的礼包
	 */
	public static function getMyGift($uid,$page=1,$pagesize=10)
	{
		$total = DB::table('gift_account')->where('uid','=',$uid)->count();
		$my_gifts = DB::table('gift_account')->where('uid','=',$uid)->orderBy('addtime','desc')->get();
		$games = $gifts = $gift_ids = $game_ids = array();
		if(!$my_gifts){
			return null;
		}
		foreach($my_gifts as $row){
			$gift_ids[] = $row['gift_id'];
			$game_ids[] = $row['game_id'];
		}
		
		$_gifts = DB::connection(self::$CONN)->table('gift')->whereIn('id',$gift_ids)->get();
		foreach($_gifts as $row){
			$gifts[$row['id']] = $row;
		}
		
		$games = GameService::getGamesByIds($game_ids);
		$out = array();
		foreach($my_gifts as $key=>$row){
			if(isset($games[$row['game_id']])){
				$gname = $games[$row['game_id']]['shortgname'];
				$ico = $games[$row['game_id']]['ico'];
			}else{
				$gname = $gifts[$row['gift_id']]['gname'];
				$ico = $gifts[$row['gift_id']]['pic'];
			}
			$out[$key]['gfid'] = $row['gift_id'];
			$out[$key]['url'] = GiftService::joinImgUrl($ico);
			$out[$key]['gname'] = $gname;
			$out[$key]['title'] = $gifts[$row['gift_id']]['title'];
			$out[$key]['date'] = date('Y-m-d H:i:s',$gifts[$row['gift_id']]['starttime']);
			$out[$key]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$key]['number'] = $row['card_no'];
		}
		
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 领取礼包
	 */
	public static function doMyGift($gift_id,$uid)
	{
		$gift = DB::connection(self::$CONN)->table('gift')->where('id','=',$gift_id)->first();
		if(!$gift){
			return -1;//礼包不存在
		}
		$user_score = UserService::getUserRealTimeCredit($uid,'score');
		if($gift['require_score']>0 && $user_score < $gift['require_score']){
			return 2;
		}
		$my_card = DB::connection('sqlite')->table('gift_card')
		->where('gfid','=',$gift_id)
		->where('uid','=',$uid)
		->where('number','!=','')
		->first();
		
		if($my_card){//已经领取过礼包
			$out['cardNum'] = $my_card['number'];
			$out['remainTourCurrency'] = UserService::getUserRealTimeCredit($uid,'score');
			return $out;
		}else{
			$card = DB::connection('sqlite')->table('gift_card')
			->where('gfid','=',$gift_id)
			->where('uid','=','0')
			->where('number','!=','')
			->first();
			//
			
			if($card){
				$success = DB::connection('sqlite')->table('gift_card')
			        ->where('gfid','=',$gift_id)
			        ->where('id','=',$card['id'])
			        ->update(array('uid'=>$uid,'gettime'=>time()));
				
				
				
				if($success){
					$data = array(
					    'gift_id'=>$gift_id,
					    'uid'=>$uid,
					    'game_id'=>$gift['gid'],
					    'card_no'=>$card['number'],
					    'addtime'=>time()
					);
					DB::table('gift_account')->insertGetId($data);
					DB::connection(self::$CONN)->table('gift')->where('id','=',$gift_id)->decrement('last_num');
					if($gift['require_score']>0){
						CreditService::handOpUserCredit($uid, (0-$gift['require_score']), CreditService::HAND_CREDIT_TYPE_GIFT);
					}
					$out['cardNum'] = $card['number'];
					$out['remainTourCurrency'] = UserService::getUserRealTimeCredit($uid,'score');
					//产生用户动态
					UserFeedService::makeFeedGift($uid,$gift_id);
					return $out;
				}else{
					return 1;
				}
			}else{
				return 0;//礼包已经被领取完
			}
		}
	}
	
	/**
	 * 我的预定
	 */
	public static function myReserve($uid,$page=1,$pagesize=10)
	{
		$my_reserve = DB::table('gift_reserve')->where('uid','=',$uid)->orderBy('addtime','desc')->get();
		$total = DB::table('gift_reserve')->where('uid','=',$uid)->count();
		if(!$my_reserve){
			return array('result'=>array(),'totalCount'=>0);
		}
		
		$game_ids = $games = array();
		foreach($my_reserve as $row){
			$game_ids[] = $row['game_id'];
		}
		//游戏
		$games = GameService::getGamesByIds($game_ids);
		//礼包
		$gift_ids = DB::connection(self::$CONN)->table('gift')->whereIn('gid',$game_ids)->lists('id','gid');
		$my_gift_ids = DB::table('gift_account')->where('uid','=',$uid)->lists('gift_id');
		$first_section = $second_section = array();
		$out = array();
		foreach($my_reserve as $row){			
			$reserve['gid'] = $row['game_id'];
			$reserve['url'] = self::joinImgUrl($games[$row['game_id']]['ico']);
			$reserve['gname'] = $games[$row['game_id']]['shortgname'];
			$reserve['bookdate'] = date('Y-m-d H:i:s',$row['addtime']);
			
		    if(isset($gift_ids[$row['game_id']])){
		    	$reserve['gfid'] = !in_array($gift_ids[$row['game_id']],$my_gift_ids) ? $gift_ids[$row['game_id']] : '';
				$first_section[] = $reserve;
			}else{
				$reserve['gfid'] = '';
				$second_section[] = $reserve;
			}			
		}
		$out = array_merge($first_section,$second_section);
		$pages = array_chunk($out,$pagesize,false);
		return array('result'=>isset($pages[$page-1])?$pages[$page-1]:array(),'totalCount'=>$total);
	}
	/**
	 * 删除预定
	 */
	public static function removeMyReserve($game_id,$uid)
	{
		$row = DB::table('gift_reserve')->where('game_id','=',$game_id)->where('uid','=',$uid)->delete();
		return true;
	}
	/**
	 * 预定礼包
	 */
	public static function doMyReserve($game_id,$uid)
	{
		$data = array(
		    'game_id'=>$game_id,
		    'uid'=>$uid,
		    'addtime'=>time()
		);
		if(self::isReserve($game_id, $uid)) return -1;
		//产生动态
		UserFeedService::makeFeedReserve($uid, $game_id);
		return DB::table('gift_reserve')->insertGetId($data);
	}
	
	public static function isReserve($game_id,$uid)
	{
		$count = DB::table('gift_reserve')->where('game_id','=',$game_id)->where('uid','=',$uid)->count();
		return $count>0 ? true : false;
	}
	
	/**
	 * 添加礼包通知
	 */
	public static function addNotice($gift_id,$game_id)
	{
		$data = array('game_id'=>$game_id,'gift_id'=>$gift_id);
		$queue_name = 'queue:gift:notice';
		$data = serialize($data);
		self::redis()->rpush($queue_name,$data);
		return true;
	}
	
	/**
	 * 分发礼包预定通知
	 * 
	 */
	public static function distributeNotice()
	{
		$queue_name = 'queue:gift:notice';
		$data = self::redis()->lpop($queue_name);
		if($data){
			$data = unserialize($data);
			$game_id = $data['game_id'];
			$gift_id = $data['gift_id'];
			$gift = GiftService::getGiftInfo($gift_id);
			$game = GameService::getGameInfo($game_id);
			if(!$gift || !$game) return false;
			$tpl = DB::connection(self::$CONN)->table()->where('type','=',3)->first(array('template'));
			
			$content = str_replace(array('{gname}','{title}'),array($game['shortgname'],$gift['title']),$tpl);
			//预约礼包的用户
			$uids = DB::table('gift_reserve')->where('game_id','=',$game_id)->lists('uid');
			//					
			$users = UserService::getBatchUserInfo($uids);
			if($users){
				$pem = Config::get('app.apple_push_file');
				$push = new Push(AbstractClass::ENVIRONMENT_PRODUCTION,$pem);
		        $push->connect();
				$pages = array_chunk($users,100,false);
				foreach($pages as $page){
					$message = new Custom();
					foreach($page as $user){
						$token = isset($user['apple_token']) ? $user['apple_token'] : '';
						if($token && strlen($token)==64){
							$message->addRecipient($token);
						}
					}
					//设置图标
					$message->setBadge(1);
					//设置消息
					$message->setText($content);
					//设置声音
					$message->setSound();
					//设置自定义属性
					$message->setCustomProperty('type','3');
					$message->setCustomProperty('linkid',$gift_id);
					//设置过期时间
					$message->setExpiry(30);
					//添加消息到队列
					$push->add($message);
					//发送消息
		            $push->send();
				}
				$push->disconnect();
			}			
		}
	}
	
}