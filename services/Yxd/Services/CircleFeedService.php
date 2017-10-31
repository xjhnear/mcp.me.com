<?php
namespace Yxd\Services;

use Yxd\Services\RelationService;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Service;
use Yxd\Services\Cms\GameService;
use Yxd\Models\Cms\Game;

class CircleFeedService extends Service
{
	
    /**
	 * 添加圈子动态
	 * @param int $uid 用户UID
	 * @param int $type 动态类型 topic:发帖;comment:评论
	 */
	protected static function addDataFeed($uid,$type,$data)
	{
		$key = 'feed:gamecircle:uid:' . $uid . '';
		$sort = microtime(true);
		//预处理
		$val = serialize($data);
		//存redis
		//self::redis()->zadd($key,$sort,$val);
		//存数据库
		self::dbClubMaster()->table('feed_gamecircle')->insert(array('key'=>$key,'score'=>$sort,'data'=>$val));
		
		return true;
	}
	
	public static function getFeedCount($gid,$uid)
	{
		//$key = 'feed:gamecircle:count:uid:' . $uid . ':gameid:' . $gid;
		//return (int)self::redis()->get($key);
		return self::dbClubSlave()->table('feed_gamecircle_count')->where('uid','=',$uid)->where('gid','=',$gid)->pluck('total');
		/*
		//帖子
		$topic = DB::table('forum_topic')->where('gid','=',$gid)->where('displayorder','=',0)->count();
		//评论
		$comment = DB::table('comment')->where('target_table','=','m_games')->where('target_id','=',$gid)->count();
		//攻略
		$guide = DB::connection(self::$CONN)->table('gonglue')->where('pid','>=',0)->where('gid','=',$gid)->count();
		
		return $topic+$comment+$guide;
		*/
	}
	
	public static function resetFeedCount($gid,$uid)
	{
		//$key = 'feed:gamecircle:count:uid:' . $uid . ':gameid:' . $gid;
		//self::redis()->set($key,0);
		return self::dbClubSlave()->table('feed_gamecircle_count')->where('uid','=',$uid)->where('gid','=',$gid)->update(array('total'=>0));
	}
	
	/**
	 * 获取游戏最新一条动态
	 */
	public static function getLastFeed($gid)
	{
		//帖子
		$topic = self::dbClubSlave()->table('forum_topic')->where('gid','=',$gid)->where('displayorder','=',0)->orderBy('dateline','desc')->first();
		//评论
		$comment = self::dbClubSlave()->table('comment')->where('target_table','=','m_games')->where('target_id','=',$gid)->orderBy('addtime','desc')->first();
		//攻略
		$guide = self::dbCmsSlave()->table('gonglue')->where('pid','>=',0)->where('gid','=',$gid)->orderBy('addtime','desc')->first();
		
		$type = 0;
		$topic_time = $topic ? $topic['dateline'] : 0;
		$comment_time = $comment ? $comment['addtime'] : 0;
		$guide_time = $guide ? $guide['addtime'] : 0;
		$max = max($topic_time,$comment_time,$guide_time);
		if($topic_time === $max){
			$type = 1;
		}elseif($comment_time === $max ){
			$type = 2;
		}elseif($guide_time === $max){
			$type = 3;
		}
		//echo $type;
		//isset($guide['id']) && print_r($guide);
		if($type==1){
			if(!isset($topic['tid'])) return null;
			$user = UserService::getUserInfo($topic['author_uid']);
			$feed = array();
			$feed['title'] = $user['nickname'] . ':发表了新帖'; 
			$feed['updatetime'] = $topic['dateline'];
			return array('type'=>$type,'feed'=>$feed);
		}
		if($type==2) {
			if(!isset($comment['id'])) return null;
			$user = UserService::getUserInfo($comment['uid']);
			$feed = array();
			$feed['title'] = $user['nickname'] . ':进行了评论';
			$feed['updatetime'] = $comment['addtime'];
			return array('type'=>$type,'feed'=>$feed);
		}
		if($type==3) {
			if(!isset($guide['id'])) return null;
			$feed = array();
			$feed['title'] = $guide['gtitle'];
			$feed['updatetime'] = $guide['addtime'];
			
			return array('type'=>$type,'feed'=>$feed);
		}
		return null;
	}
	
	/**
	 * 获取圈子动态
	 */
	public static function getDataFeed($uid,$page=1,$pagesize=10,$lastpulltime=0)
	{
		$key = 'feed:gamecircle:uid:' . $uid .'';
		/*
		$start  = $pagesize * ($page-1);
		$end    = $start + $pagesize;
		$time = microtime(true);
		$total = self::redis()->zcount($key,0,$time);
		$feeds = self::redis()->zrevrangebyscore($key,$time,0,'WITHSCORES','LIMIT',$start,$pagesize);
		*/
		
		$gids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->lists('game_id');
		$result = self::loadByJson('module_message/feed/gamecircle_list',array('gid'=>implode(',',$gids),'pageIndex'=>$page,'pageSize'=>$pagesize));
		$total = 0;
		$feeds = array();
		foreach($result as $row){
			$data = array();
			$data['type'] = $row['type'];
			$data['data'] = $row['content'];
			$feeds[] = $data;
		}
		//$gids && $total = self::dbClubSlave()->table('feed_gamecircle')->whereIn('gid',$gids)->count();
		//$gids && $feeds = self::dbClubSlave()->table('feed_gamecircle')->whereIn('gid',$gids)->orderBy('score','desc')->forPage($page,$pagesize)->get();
		return array('feeds'=>$feeds,'total'=>$total);
	}
	
	public static function deleteFeed($uid,$data)
	{
		$key = 'feed:gamecircle:uid:' . $uid .'';
		self::redis()->zrem($key,serialize($data));
	}
	
	public static function getCircleFeedCount($uid,$reset=false)
	{		
		/*
		$key = 'feed:gamecircle:count:uid:' . $uid . ':gameid:*';
		$feeds = self::redis()->keys($key);
		if($reset){
			is_array($feeds) && $feeds && self::redis()->del($feeds);
			return 0;
		}else{
		    if(is_array($feeds) && count($feeds)){
		    	$tmp = self::redis()->mget($feeds);
		    	return $tmp ? array_sum($tmp) : 0;
		    }
		}
		return 0;
		*/
		//圈子动态数有redis切换到mysql
		if($reset){
			self::dbClubSlave()->table('feed_gamecircle_count')->where('uid','=',$uid)->update(array('total'=>0));
			return 0;
		}else{
			return self::dbClubSlave()->table('feed_gamecircle_count')->where('uid','=',$uid)->sum('total') ? : 0;
		}
		
		
		
		/*
		$key = 'feed:gamecircle:uid:' . $uid .'';
		$time = microtime(true);
		$total = self::redis()->zcount($key,$last,$time);
		return $total;
		*/
	}
	
	/**
	 * 产生动态信息
	 */
	public static function makeDataFeed($data)
	{
		$queue_name = 'queue:feed:gamecircle';
		$data = serialize($data);
		self::queue()->rpush($queue_name,$data);
		return true;
	}
	
	/**
	 * 分发动态信息到游戏圈
	 */
	public static function distributeDataFeed()
	{
		self::distributeFeedToCircle();
	}
	
	protected static function distributeFeedToUser()
	{
	    $queue_name = 'queue:feed:gamecircle';
		$data = self::queue()->lpop($queue_name);
		while($data){
									
			$data = unserialize($data);
			
			//类型
			$type = $data['type'];
			$feed = true;
			if($type=='topic'){			    
			    $game_id = $data['topic']['gid'];			    
			}elseif($type=='comment'){
				$game_id = $data['comment']['target_id'];
			}elseif($type=='reply'){
				$game_id = $data['topic']['gid'];
				$feed = false;
			}
			
			$feed==true && $data['game'] = GameService::getGameInfo($game_id);		
			//获取订阅者的uid
			$uids = self::dbClubSlave()->table('account_circle')->where('game_id','=',$game_id)->lists('uid');
			//\Illuminate\Support\Facades\Log::info(json_encode($uids));
			foreach($uids as $uid){
				//分发信息到订阅者
				$feed==true && self::addDataFeed($uid,$type,$data);
				//更新圈子动态数据
				$key = 'feed:gamecircle:count:uid:' . $uid . ':gameid:' . $game_id;
				self::redis()->incr($key);
			}
			$data = self::queue()->lpop($queue_name);
		}
	}
	
	protected static function distributeFeedToCircle()
	{
	    $queue_name = 'queue:feed:gamecircle';
		$data = self::queue()->lpop($queue_name);
		while($data){
									
			$data = unserialize($data);
			
			//类型
			$type = $data['type'];
			$feed = true;
			if($type=='topic'){			    
			    $game_id = $data['topic']['gid'];
			    $linkid = $data['topic']['tid'];
			    $addtime = $data['topic']['dateline'];			    
			}elseif($type=='comment'){
				$game_id = $data['comment']['target_id'];
				$linkid = $data['comment']['id'];
				$addtime = $data['comment']['addtime'];
			}elseif($type=='reply'){
				$game_id = $data['topic']['gid'];
				$feed = false;
			}
			
			$feed==true && $data['game'] = GameService::getGameInfo($game_id);		
			
			$feed==true && self::dbClubMaster()->table('feed_gamecircle')->insert(array('gid'=>$game_id,'type'=>$type,'linkid'=>$linkid,'score'=>$addtime,'data'=>serialize($data)));
			
			$cachekey = 'feed_gamecircle_count_'.$game_id;
			$number = (int)Cache::get($cachekey);
			if($number>10){
			    self::dbClubMaster()->table('feed_gamecircle_count')->where('gid','=',$game_id)->increment('total',$number,array('last_update_time'=>time()));
			    Cache::forever($cachekey,0);
			}else{
				if(Cache::has($cachekey)){
				    Cache::increment($cachekey);
				}else{
					Cache::forever($cachekey,1);
				}
			}
			
			/*
			//获取订阅者的uid
			$uids = self::dbClubSlave()->table('account_circle')->where('game_id','=',$game_id)->lists('uid');			
			
			foreach($uids as $uid){
				//更新圈子动态数据
				$key = 'feed:gamecircle:count:uid:' . $uid . ':gameid:' . $game_id;
				//self::redis()->incr($key);
				$exists = self::dbClubMaster()->table('feed_gamecircle_count')->where('uid','=',$uid)->where('gid','=',$game_id)->count();
				if($exists){
				    self::dbClubMaster()->table('feed_gamecircle_count')->where('uid','=',$uid)->where('gid','=',$game_id)->increment('total',1,array('last_update_time'=>time()));
				}else{
					$value = array('key'=>$key,'uid'=>$uid,'gid'=>$game_id,'total'=>1,'last_update_time'=>time());
					self::dbClubMaster()->table('feed_gamecircle_count')->insert($value);
				}
			}
			*/
			$data = self::queue()->lpop($queue_name);
		}
	}
	
}
