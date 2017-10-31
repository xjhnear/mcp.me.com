<?php
namespace Yxd\Services;

use Yxd\Modules\Message\PromptService;

use Yxd\Services\Cms\InfoService;

use Yxd\Services\Cms\GameService;

use Yxd\Utility\ForumUtility;

use Illuminate\Support\Facades\DB as DB;

use Yxd\Models\User;
use Yxd\Models\Passport;

class AtmeService extends Service
{
	const ATME_TOPIC = 0;
	const ATME_REPLY = 0;
	const ATME_COMMENT_NEWS = 1;
	const ATME_COMMENT_GUIDE = 2;
	const ATME_COMMENT_OPINION = 3;
	const ATME_COMMENT_NEWGAME = 4;
	const ATME_COMMENT_VIDEO = 5;
	const ATME_COMMENT_GAME = 6;
	
	
	/**
	 * 我
	 */
	public static function getAtmeList($uid,$page=1,$pagesize=10)
	{	
		return self::getDataFeedByDb($uid,$page,$pagesize);
		/*	
		$key = 'atme:user:uid:' . $uid .'';
		$start  = $pagesize * ($page-1);
		$end    = $start + $pagesize;
		$time = microtime(true);
		$total = self::redis()->zcount($key,0,$time);
		$feeds = self::redis()->zrevrangebyscore($key,$time,0,'WITHSCORES','LIMIT',$start,$pagesize);
		return array('result'=>$feeds,'total'=>$total);
		*/		
	}
	
    /**
	 * 从数据库获取动态
	 */
	public static function getDataFeedByDb($uid,$page=1,$pagesize=10)
	{
		$total = self::dbClubSlave()->table('feed_atme')->where('uid','=',$uid)->count();
		$feeds = self::dbClubSlave()->table('feed_atme')
		->where('uid','=',$uid)
		->orderBy('score','desc')
		->forPage($page,$pagesize)
		->get();
		return array('result'=>$feeds,'total'=>$total);
	}
	
	/**
	 * 
	 */
	public static function saveAtme($uids,$target_table,$target_id)
	{
		$row = array('app'=>'','target_table'=>$target_table,'target_id'=>$target_id);
		$atme = array();
		$queue_name = 'queue:atme:user';
		foreach($uids as $uid){
			$row['uid'] = $uid;
			$atme[] = $row;
			$data = serialize($row);
		    //self::queue()->rpush($queue_name,$data);
		}
		if($atme) self::dbClubMaster()->table('atme')->insert($atme);
				
		return true;
	}
	
	/**
	 * 发帖
	 */
	public static function atmeOfPostTopic($at_uids,$topic)
	{
		$target_id = $topic['tid'];
	}
	
	/**
	 * 回复我的评论
	 */
	public static function atmeOfComment($at_uids,$comment)
	{
		if(!$at_uids) return false;
		$at_uids = array_unique($at_uids);
		$target_id = $comment['id'];
		$target_table = 'yxd_comment';
		self::saveAtme($at_uids, $target_table, $target_id);
		return true;
	}	
	
	/**
	 * 分发回复信息到用户
	 */
	public static function distributeDataFeed()
	{
		$queue_name = 'queue:atme:user';
		$data = self::queue()->lpop($queue_name);
		while($data){
			$data = unserialize($data);
			switch($data['target_table']){
				case 'yxd_comment':
					self::distributeComment($data['target_id'],$data['uid']);
					break;
			}
			
			$data = self::queue()->lpop($queue_name);
		}
	}
	
	/**
	 * 分发AT评论
	 */
	public static function distributeComment($id,$to_uid)
	{
		$comment = self::dbClubSlave()->table('comment')->where('id','=',$id)->first();
		if(!$comment) return null;
		$table = $comment['target_table'];
		$data = array();
		$data['type'] = $table;
		$data['comment'] = $comment;
		$data['comment']['user'] = UserService::getUserInfo($comment['uid']);
		if($comment['pid']>0){
			$data['reply'] = self::dbClubSlave()->table('comment')->where('id','=',$comment['pid'])->first();
			$data['reply']['user'] = UserService::getUserInfo($data['reply']['uid']);
		}
		switch($table){
			case 'm_games':
				$data['game'] = GameService::getGameInfo($comment['target_id']);				
				break;
			case 'm_news':
				$data['news'] = InfoService::getNewsInfo($comment['target_id']);
				break;
			case 'm_gonglue':
				$data['guide'] = InfoService::getGuideInfo($comment['target_id']);
				break;
			case 'm_feedback':
				$data['opinion'] = InfoService::getOpinionInfo($comment['target_id']);
				break;
			case 'm_videos':
				$data['video'] = InfoService::getVideoInfo($comment['target_id']);
				break;
			case 'm_game_notice':
				$data['newgame'] = InfoService::getNewGameInfo($comment['target_id']);
				break;
			case 'yxd_forum_topic':
				$data['topic'] = ThreadService::showTopicInfo($comment['target_id']);
				$data['game'] = GameService::getGameInfo($data['topic']['gid']);
				break;				
				
		}
		//self::addDataFeed($to_uid,'comment', $data);
		self::addDataFeedToDb($to_uid, 'comment', $comment['id'], $data);
		PromptService::addMyReplyMsgNum($to_uid);
	}	
	
    public static function addDataFeed($uid,$type,$data)
	{
		$key = 'atme:user:uid:' . $uid . '';
		$sort = microtime(true);
		//预处理
		$data = serialize($data);
		self::redis()->zadd($key,$sort,$data);
		return true;
	}	
	
    /**
	 * 添加回复到数据库
	 * @param int $uid 
	 * @param string $type 动态类型
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
		self::dbClubMaster()->table('feed_atme')->insert($input);
		return true;
	}
}