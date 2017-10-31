<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;

use Yxd\Services\ThreadService;

use Yxd\Services\TopicService;

use Yxd\Services\CreditService;

use Yxd\Modules\Message\NoticeService;
use Yxd\Services\AtmeService;
use Yxd\Services\TaskService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Services\UserService;
use Yxd\Services\CircleFeedService;
use Yxd\Models\Cms\Game;
use Yxd\Services\Models\Comment;
use Yxd\Services\Models\Forum;
use Yxd\Services\Models\ForumTopic;
use Yxd\Services\Models\ForumSetbestMax;
use Yxd\Services\Models\Games;
use Yxd\Services\Models\News;
use Yxd\Services\Models\Gonglue;
use Yxd\Services\Models\Feedback;
use Yxd\Services\Models\Videos;
use Yxd\Services\Models\GameNotice;

/**
 * 评论
 */
class CommentService extends Service
{	
	const GAME = 1;
	const NEWS = 2;
	const GUIDE = 3;
	const OPINION = 4;
	const NEWGAME = 5;
	const VIDEO = 6;
		
	/**
	 * 
	 */
	public static function createComment($data,$atFriends=null)
	{
		$max = Comment::db()->where('target_id','=',$data['target_id'])->where('target_table','=',$data['target_table'])->max('storey');
		$data['storey'] = $max+1;
		$id = Comment::db()->insertGetId($data);
		if($id>0){
			$data['id'] = $id;
			Event::fire('comment.post',array(array($data)));
			if(isset($data['pid']) && $data['pid']>0){
				$reply = Comment::db()->where('id','=',$data['pid'])->first();
				$atFriends[] = $reply['uid'];
			}
			if($data['target_table']=='yxd_forum_topic'){
				$topic = ThreadService::showTopicInfo($data['target_id']);
				isset($topic['author_uid']) && $atFriends[] = $topic['author_uid'];
			}
			$section = 'comment::list::'.$data['target_table'].'::' . $data['target_id'];
			CLOSE_CACHE===false && CacheService::section($section)->flush();
			//AT好友
			AtmeService::atmeOfComment($atFriends, $data);
			return $id;
		}else{
			return array('error'=>'评论失败');
		}
	}
	
	public static function getInfo($id)
	{
		$cmt = Comment::db()->where('id','=',$id)->first();
		$uids = array();
		$uids[] = $cmt['uid'];
		
		$pid = $cmt['pid'];		
		//引用
		if($pid){
			$quote = Comment::db()
				->where('id','=',$pid)				
				->first();
		}else{
			$quote = array();
		}
		//用户
		if($quote){
			$uids[] = $quote['uid'];			
		}		
		$users = UserService::getBatchUserInfo(array_unique($uids));
		
		if($quote){
			$quote['author'] = $users[$quote['uid']];
		}			
		$cmt['author'] = $users[$cmt['uid']];
		$cmt['quote'] = $quote;
		$comment = array();
		$comment['cid'] = $cmt['id'];
		$comment['isBest'] = 0;
		$comment['floorIndex'] = $cmt['storey'];
		$cmt['content'] = json_decode($cmt['content'],true);
		$comment['replyInfo']['replyTopic'] = 0;
		if($cmt['content'] && count($cmt['content'])>0){									
		    $comment['replyInfo']['replyContent'] = $cmt['content'][0]['text'];
		    $comment['replyInfo']['replyImage'] = self::joinImgUrl($cmt['content'][0]['img']);				
		}
		$comment['replyInfo']['replyDate'] = date('Y-m-d H:i:s',$cmt['addtime']);
		$comment['replyInfo']['tocid'] = $cmt['pid'];
		
		$comment['replyInfo']['fromUser']['userID'] = $cmt['author']['uid'];
		$comment['replyInfo']['fromUser']['userName'] = $cmt['author']['nickname'];
		$comment['replyInfo']['fromUser']['userAvator'] = self::joinImgUrl($cmt['author']['avatar']);
		$comment['replyInfo']['fromUser']['userLevel'] = $cmt['author']['level_name'];
		$comment['replyInfo']['fromUser']['userLevelImage'] = self::joinImgUrl($cmt['author']['level_icon']);
		if(isset($cmt['quote']) && $cmt['quote']){
			$cmt['quote']['content'] = json_decode($cmt['quote']['content'],true);
			if($cmt['quote']['content'] && count($cmt['quote']['content'])>0){									
			    $comment['replyInfo']['toContent'] = $cmt['quote']['content'][0]['text'];
			    $comment['replyInfo']['toImage'] = self::joinImgUrl($cmt['quote']['content'][0]['img']);				
			}
			$comment['replyInfo']['toUser']['userID'] = $cmt['quote']['author']['uid'];
			$comment['replyInfo']['toUser']['userName'] = $cmt['quote']['author']['nickname'];
		}
		
		$out['commentInfos'] = $comment;
		return $out;
	}
	
	/**
	 * 获取评论列表
	 */
	public static function getAppOfList($target_id,$target_table,$page=1,$pagesize=10)
	{
		$section = 'comment::list::'.$target_table.'::' . $target_id;
		$cachekey = 'comment::list::'.$target_table.'::' . $target_id . '::page::'.$page.'::'.$pagesize;
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			return CacheService::section($section)->get($cachekey);
		}else{
			$comments = Comment::db()
				->where('target_id','=',$target_id)
				->where('target_table','=',$target_table)
				->where('isdel','=',0)
				->orderBy('best','desc')
				->orderBy('addtime','desc')
				->forPage($page,$pagesize)
				->get();
			$total = Comment::db()
				->where('target_id','=',$target_id)
				->where('target_table','=',$target_table)
				->where('isdel','=',0)
				->count();
			if($total==0) return array('result'=>array(),'total'=>0);
	
		    $pids = array();
			$uids = array();
			foreach($comments as $key=>$row){
				$row['pid'] && $pids[] = $row['pid'];
				$uids[] = $row['uid'];
			}
			$pids = array_unique($pids);		
			//引用
			if($pids){
				$quotes = Comment::db()
				    ->where('target_id','=',$target_id)
					->where('target_table','=',$target_table)
					->whereIn('id',$pids)
					->orderBy('pid','asc')
					->orderBy('addtime','desc')
					->get();
			}else{
				$quotes = array();
			}
			//用户
			foreach($quotes as $key=>$row){
				$uids[] = $row['uid'];
			}		
			$users = UserService::getBatchUserInfo(array_unique($uids));
			$sort_quotes = array();
			foreach($quotes as $row){
				$row['author'] = $users[$row['uid']];
				$sort_quotes[$row['id']] = $row;
			}	
			//print_r($sort_quotes);exit;    
			foreach($comments as $key=>$row){
				$row['author'] = $users[$row['uid']];			
				$row['quote'] = $row['pid']&&isset($sort_quotes[$row['pid']]) ? $sort_quotes[$row['pid']] : null;
				$comments[$key] = $row;
			}
		    $out = array('result'=>$comments,'total'=>$total);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$out);
		    return $out;
		}
	}
	
	/**
	 * 删评论
	 */
	public static function deleteComment($id,$uid)
	{
		$comment = Comment::db()->where('id','=',$id)->first();
		if(!$comment){
			return -1;
		}
		if($comment['uid'] != $uid){
			return -2;
		}
		//用户自己删除评论
		CreditService::handOpUserCredit($uid,0,-1,CreditService::CREDIT_RULE_ACTION_DELETE_COMMENT);
		self::addDelCache($id);
		self::clearCacheList($comment['target_id'],$comment['target_table']);
		return Comment::db()->where('id','=',$id)->where('uid','=',$uid)->delete();
		
	}
	
	/**
	 * 设置最佳答案
	 */
    public static function setBest($id,$uid,$flag=1)
	{
		$comment = Comment::db()->where('id','=',$id)->first();
		if(!$comment){
			return -1;
		}
		if($comment['best']){
			return;
		}
		//同一个设备的提问和回答不能设置最佳答案
		$a = UserService::getUserAppleIdentify($uid);
		$b = UserService::getUserAppleIdentify($comment['uid']);		
		if($a == $b){
			return -2;
		}
		
		ForumTopic::db()->where('tid','=',$comment['target_id'])->update(array('askstatus'=>1));
		$best = Comment::db()->where('id','=',$id)->update(array('best'=>$flag));
		//发放游币
		$topic = ForumTopic::db()->where('tid','=',$comment['target_id'])->first();
		$info = '回答<<' . $topic['subject'] . '>>被设置为最佳答案' ;
		//限制判断
		//$a = (int)self::redis()->get('setbest::uid::'.$comment['uid']);
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$a = ForumSetbestMax::db()->where('uid','=',$comment['uid'])->where('ctime','>=',$start)->sum('score');
		$can_score = 100-$a;		
		$reward = $topic['award'];		
		if($can_score>0){
			if($reward>$can_score){
				CreditService::handOpUserCredit($comment['uid'], $can_score,0,'topic_reply_best',$info);
				//self::redis()->incrby('setbest::uid::'.$comment['uid'],$can_score);
				ForumSetbestMax::db()->insert(array('uid'=>$comment['uid'],'score'=>$can_score,'ctime'=>time()));				
			}else{
			    CreditService::handOpUserCredit($comment['uid'], $reward,0,'topic_reply_best',$info);
			    //self::redis()->incrby('setbest::uid::'.$comment['uid'],$reward);
			    ForumSetbestMax::db()->insert(array('uid'=>$comment['uid'],'score'=>$reward,'ctime'=>time()));
			}
			/*
			if($a==0){
				$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
			    $expire = $end - time();
				self::redis()->expire('setbest::uid::'.$comment['uid'],$expire);
			}
			*/
		}
		//发系统消息
		$params = array();
		$game = GameService::getGameInfo($topic['gid']);
		$params['catename'] = $game['shortgname'];
		$params['title'] = $topic['subject'];
		$params['money'] = $can_score>0 ? (($reward>$can_score) ? $can_score : $reward) : 0;
		NoticeService::sendReplySetBest($comment['uid'], $comment['target_id'], $params);
		return true;
	}
	
	/**
	 * 
	 */
	public static function getList($target_id,$target_table,$page=1,$pagesize=10)
	{
		$comments = Comment::db()
			->where('target_id','=',$target_id)
			->where('target_table','=',$target_table)
			->where('pid','=',0)
			->orderBy('addtime','asc')
			->forPage($page,$pagesize)
			->get();
		
		//
		$total = Comment::db()
			->where('target_id','=',$target_id)
			->where('target_table','=',$target_table)
			->where('pid','=',0)
			->count();
		if($total==0) return array('result'=>array(),'total'=>0);
		$sort_comments = array();
		$pids = array();
		$uids = array();
		foreach($comments as $key=>$row){
			$pids[] = $row['id'];
			$uids[] = $row['uid'];
			$sort_comments[$row['id']] = $row;
		}
		
		$replys = Comment::db()
		    ->where('target_id','=',$target_id)
			->where('target_table','=',$target_table)
			->whereIn('pid',$pids)
			->orderBy('pid','asc')
			->orderBy('addtime','desc')
			->get();
		//
		foreach($replys as $key=>$row){
			$uids[] = $row['uid'];
		}
		
		$users = UserService::getBatchUserInfo(array_unique($uids));
		//
	    foreach($replys as $reply){
			$reply['author'] = $users[$reply['uid']];
			$reply['content'] = json_decode($reply['content'],true);
			$reply['addtime'] = date('Y-m-d H:i:s',$reply['addtime']);
			$sort_comments[$reply['pid']]['replys'][] = $reply;
		}
		foreach($sort_comments as $key=>$row){
			$row['author'] = $users[$row['uid']];
			$row['content'] = json_decode($row['content'],true);
			$row['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
			$sort_comments[$key] = $row;
		}
		
		return array('result'=>$sort_comments,'total'=>$total);
	}
	
	public static function getTotalByType($ids,$table)
	{
		if(!$ids) return array();
		return Comment::db()->where('target_table','=',$table)->whereIn('target_id',$ids)->select(DB::raw('target_id , count(*) as total'))->groupBy('target_id')->lists('total','target_id');
	}
	
	/**
	 * 更新评论数
	 */
	public static function updateCommentCount($table,$id,$incr=true)
	{

		$table  = (strpos($table,'m_')===0) ? substr ( $table , 2 ) : ((strpos($table,'yxd_')===0) ? substr ( $table , 4 ) : $table);
		/* 
		 * 截取出问题 2014-10-9 如： m_xyx_game 此两行代码执行过后 $table = 'game' 
		 * $table = ltrim($table,"m_");
		 * $table = ltrim($table,"yxd_"); 
		*/
		if($table=='forum_topic'){
			if($incr===true){
				ForumTopic::db()->where('tid','=',$id)->increment('replies',1,array('lastpost'=>time()));
				$topic = ForumTopic::db()->where('tid','=',$id)->first();
				$topic && Forum::db()->where('gid','=',$topic['gid'])->increment('posts',1);
				$topic && CircleFeedService::makeDataFeed(array('type'=>'reply','topic'=>$topic));
			}else{
				ForumTopic::db()->where('tid','=',$id)->decrement('replies',1,array('lastpost'=>time()));
				$topic = ForumTopic::db()->where('tid','=',$id)->first();
				$topic && Forum::db()->where('gid','=',$topic['gid'])->decrement('posts',1);
			}
		}elseif($table=='xyx_game'){
			
		}else{
			$tb = null;
			switch($table){
				case 'games':
					$tb = Games::db();
					break;
				case 'news':
					$tb = News::db();
					break;
				case 'gonglue':
					$tb = Gonglue::db();
					break;
				case 'feedback':
					$tb = Feedback::db();
					break;
				case 'videos':
					$tb = Videos::db();
					break;
				case 'game_notice':
					$tb = GameNotice::db();
					break;
				default:
					break;
			}
			if($incr===true){
			    $tb && $tb->where('id','=',$id)->increment('commenttimes');
			}else{
				$tb && $tb->where('id','=',$id)->decrement('commenttimes');
			}
		}
	}
	
	public static function doCredit($table,$uid)
	{
		$msg = '回复成功 经验+1';
		$score = null;
		switch($table){
			case 'm_games':
				//每日游戏评论任务
				$score = TaskService::doGameComment($uid);
				is_numeric($score) && $msg = '发布评论3次成功 游币+'.$score.'';
				break;
			case 'yxd_forum_topic':
				//每日回帖任务
				$score = TaskService::doPostReply($uid);
				is_numeric($score) && $msg = '回复帖子3次成功 游币 +'.$score.'';
				break;
			default:
				$msg = '回复帖子成功  经验+1';
				break;
		}
		
		return array('score'=>$score,'msg'=>$msg);
	}
	
	public static function isDeleted($id)
	{
		//$cachekey = 'comment::delete::ids';
		//if(self::redis()->sismember($cachekey,$id)) return true;
		$comment = Comment::db()->where('id','=',$id)->select('id')->first();
		//if(!$comment) self::addDelCache($id);
		return $comment ? false : true;
	}
	
	public static function addDelCache($ids)
	{
		$cachekey = 'comment::delete::ids';
		self::redis()->sadd($cachekey,$ids);
	}
	
	public static function deleteByAdmin($ids)
	{
		if(is_array($ids) && count($ids)>0){
			$comments = Comment::db()->whereIn('id',$ids)->get();
			if($comments){
				self::addDelCache($ids);
				foreach($comments as $row){
					$uid = $row['uid'];
					$info = InfoService::getShortInfo($row['target_id'],$row['target_table']);
					$params = array();
					$params['catename'] = $info['cate'];
					$params['title'] = $info['title'];
					NoticeService::sendCommentDeletedByAdmin($uid,$params);
					$uid && CreditService::doUserCredit($uid,CreditService::CREDIT_RULE_ACTION_DELETE_COMMENT,'评论被管理员删除');
					self::clearCacheList($row['target_id'],$row['target_table']);
				}
			}
			return Comment::db()->whereIn('id',$ids)->update(array('isdel'=>1));
		}elseif(is_numeric($ids)){
			$comment = Comment::db()->where('id','=',$ids)->first();
			if($comment){
				self::addDelCache($ids);
				self::clearCacheList($comment['target_id'],$comment['target_table']);
				$info = InfoService::getShortInfo($comment['target_id'],$comment['target_table']);
				$params = array();
				$params['catename'] = $info['cate'];
				$params['title'] = $info['title'];				
				$comment && $comment['uid'] && NoticeService::sendCommentDeletedByAdmin($comment['uid'],$params);
				$comment && $comment['uid'] && CreditService::doUserCredit($comment['uid'],CreditService::CREDIT_RULE_ACTION_DELETE_COMMENT,'评论被管理员删除');
			}
			return Comment::db()->where('id','=',$ids)->update(array('isdel'=>1));
		}
		return false;
	}
	
	public static function clearCacheList($target_id,$target_table)
	{
		$section = 'comment::list::'.$target_table.'::' . $target_id;
		CacheService::section($section)->flush();
		//self::updateCommentCount($target_table, $target_id,false);
	}
}