<?php
namespace Yxd\Services;

use Yxd\Services\Cms\ActivityService;

use Yxd\Models\Thread;

use Yxd\Services\Cms\InfoService;

use Yxd\Services\RelationService;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Service;
use Yxd\Services\Cms\GameService;
use Yxd\Models\Cms\Game;

class UserFeedService extends Service
{
	protected static  $CONN = 'cms';
	
	const FEED_TYPE_REPLY = 1;
	const FEED_TYPE_TOPIC = 2;
	const FEED_TYPE_JOIN_CIRCLE = 3;
	const FEED_TYPE_COMMENT_GAME = 4;
	const FEED_TYPE_COMMENT_VIDEO = 5;
	const FEED_TYPE_COMMENT_NEWS = 6;
	const FEED_TYPE_ACTIVITY = 7;
	const FEED_TYPE_GIFT = 8;
	const FEED_TYPE_COMMENT_OPINION = 9;
	const FEED_TYPE_COMMENT_NEWGAME = 10;
	
	/**
	 * 添加动态
	 * @param int $uid 
	 * @param string $type 动态类型 topic/reply/game_comment/article_comment/gift/activity
	 * @param array $data
	 */
	public static function addDataFeed($uid,$type,$data)
	{
		$key = 'feed:user:uid:' . $uid . '';
		$sort = microtime(true);
		//预处理
		$data = serialize($data);
		self::redis()->zadd($key,$sort,$data);
		return true;
	}
	
	/**
	 * 添加动态到数据库
	 * @param int $uid 
	 * @param string $type 动态类型 topic/reply/game_comment/article_comment/gift/activity
	 * @param int $linkid
	 * @param array $data
	 */
	public static function addDataFeedToDb($uid,$type,$linkid,$data)
	{
		$input = array(
		    'uid'=>$uid,
		    'feed_linktype'=>$type,
		    'feed_linkid'=>$linkid,
		    'score'=>time(),
		    'data'=>serialize($data)
		);
		self::dbClubMaster()->table('feed_userflow')->insert($input);
		return true;
	}
	
	/**
	 * 获取动态
	 */
	public static function getDataFeed($uid,$page=1,$pagesize=10,$lastpulltime=0)
	{
		return self::getDataFeedByDb($uid,$page,$pagesize);
		$key = 'feed:user:uid:' . $uid .'';
		$start  = $pagesize * ($page-1);
		$end    = $start + $pagesize;
		$time = microtime(true);
		$total = self::redis()->zcount($key,0,$time);
		$feeds = self::redis()->zrevrangebyscore($key,$time,0,'WITHSCORES','LIMIT',$start,$pagesize);
		return array('result'=>$feeds,'total'=>$total);
	}
	
	/**
	 * 从数据库获取动态
	 */
	public static function getDataFeedByDb($uid,$page=1,$pagesize=10)
	{
		$total = self::dbClubSlave()->table('feed_userflow')->where('uid','=',$uid)->count();
		$feeds = self::dbClubSlave()->table('feed_userflow')
		->where('uid','=',$uid)
		->orderBy('score','desc')
		->forPage($page,$pagesize)
		->get();
		return array('result'=>$feeds,'total'=>$total);
	}
	
	/**
	 * 产生动态
	 */
	public static function makeDataFeed($data)
	{
		$queue_name = 'queue:feed:user';
		$data = serialize($data);
		self::queue()->rpush($queue_name,$data);
		return true;
	}
	
	/**
	 * 发帖
	 */
	public static function makeFeedPostTopic()
	{
		
	}
	
	/**
	 * 帖子评论
	 */
	public static function makeFeedTopicComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
	/**
	 * 游戏评论
	 */
	public static function makeFeedGameComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
	/**
	 * 新闻评论
	 */
	public static function makeFeedNewsComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
    /**
	 * 攻略评论
	 */
	public static function makeFeedGuideComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
    /**
	 * 评测评论
	 */
	public static function makeFeedOpinionComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
	/**
	 * 新游评论
	 */
	public static function makeFeedNewGameComment($comment)
	{
		//self::makeDataFeed($comment);
	}
	
	/**
	 * 视频评论
	 */
	public static function makeFeedVideoComment($comment)
	{
		self::makeDataFeed($comment);
	}
	
	/**
	 * 加入圈子
	 */
	public static function makeFeedJoinCircle($uid,$game_ids)
	{
		$data = array();
		$data['uid'] = $uid;
		$data['game_ids'] = $game_ids;
		$data['jointime'] = time();
		$queue_name = 'queue::joincircle';
		$data = serialize($data);
		self::queue()->rpush($queue_name,$data);
		
	}
	
	/**
	 * 定时执行加入圈子动态
	 */
	public static function execFeedJoinCircle()
	{
		$queue_name = 'queue::joincircle';
		$data = self::queue()->lpop($queue_name);		
		$feeds = array();
		while($data){
			$data = unserialize($data);
			$uid = $data['uid'];
			if(!isset($feeds[$uid])){
				$feeds[$uid] = array('game_ids'=>array(),'jointime'=>time());
			}
			$feeds[$uid]['game_ids'] = array_merge($feeds[$uid]['game_ids'],$data['game_ids']);
			$feeds[$uid]['jointime'] = $data['jointime'];			
		    $data = self::redis()->lpop($queue_name);
		}
		foreach($feeds as $uuid=>$row){
			$circle = array();
			$circle['type'] = 'circle';
			$circle['uid'] = $uuid;
			$circle['game_ids'] = $row['game_ids'];
			$circle['joindate'] = $row['jointime'];
			self::makeDataFeed($circle);
		}
	}
	
	/**
	 * 领取礼包
	 */
	public static function makeFeedGift($uid,$gift_id)
	{
		$gift = array();
		$gift['type'] = 'gift';
		$gift['uid'] = $uid;
		$gift['gift_id'] = $gift_id;
		$gift['addtime'] = time();
		self::makeDataFeed($gift);
	}
	
	/**
	 * 预定礼包
	 */
	public static function makeFeedReserve($uid,$game_id)
	{
		$gift = array();
		$gift['type'] = 'reserve';
		$gift['uid'] = $uid;
		$gift['game_id'] = $game_id;
		$gift['addtime'] = time();
		self::makeDataFeed($gift);
	}
	
	/**
	 * 参与活动
	 */
	public static function makeFeedActivity($uid,$activity_id)
	{
		$data = array(
		    'type'=>'activity',
		    'uid'=>$uid,
		    'activity_id'=>$activity_id,
		    'addtime'=>time()
		);
		self::makeDataFeed($data);
	}
	
	public static function makeFeedHunt($uid,$hunt_id)
	{
		
	}
	
	/**
	 * 分发动态信息到用户
	 */
	public static function distributeDataFeed()
	{
	    $queue_name = 'queue:feed:user';
		$data = self::queue()->lpop($queue_name);
		while($data){
			$data = unserialize($data);
			$type = $data['type'];
			$uid = 0;		
			$linkid = 0;
			switch($type){
				case 'topic':
					$game_id = $data['topic']['gid'];
					$uid = $data['topic']['author']['uid'];
					$linkid = $data['topic']['tid'];
					$data['game'] = GameService::getGameInfo($game_id);	
					break;
				case 'reply':
					$tid = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['topic'] = Thread::getFullTopic($tid);
					$data['game'] = GameService::getGameInfo($data['topic']['gid']);
					break;
				case 'game_comment':
					$game_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['game'] = GameService::getGameInfo($game_id);
					$data['user'] = UserService::getUserInfo($data['comment']['uid']);	
				case 'news_comment':
					$news_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['news'] = InfoService::getNewsInfo($news_id);
                    unset($data['news']['content']);					
					break;
				case 'guide_comment':
					$guide_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['guide'] = InfoService::getNewsInfo($guide_id);
					unset($data['guide']['content']);					
					break;
				case 'opinion_comment':
					$opinion_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['opinion'] = InfoService::getOpinionInfo($opinion_id);
					unset($data['opinion']['content']);					
					break;
				case 'video_comment':
					$video_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['video'] = InfoService::getVideoInfo($video_id);
					break;
				case 'newgame_comment':
					$notice_id = $data['comment']['target_id'];
					$uid = $data['comment']['uid'];
					$linkid = $data['comment']['id'];
					$data['notice'] = InfoService::getNewGameInfo($notice_id);
					break;
				case 'circle':
					$uid = $data['uid'];
					$game_ids = $data['game_ids'];
					$data['games'] = GameService::getGamesByIds($game_ids);
					break;
				case 'gift':
					$uid = $data['uid'];
					$gift_id = $data['gift_id'];	
					$linkid = $data['gift_id'];									
					break;
				case 'reserve':
					$uid = $data['uid'];
					$game_id = $data['game_id'];
					$data['game'] = GameService::getGameInfo($game_id);
					break;
				case 'activity':
					$uid = $data['uid'];
					$activity_id = $data['activity_id'];
					$linkid = $activity_id;
					$data['activity'] = ActivityService::getInfo($activity_id);
					$data['joindate'] = $data['addtime'];
					break;
				default:
					break;
			}
				//分发信息到订阅者
			//self::addDataFeed($uid,$type, $data);
			self::addDataFeedToDb($uid, $type, $linkid, $data);
			
			$data = self::queue()->lpop($queue_name);
		}
	}
}
