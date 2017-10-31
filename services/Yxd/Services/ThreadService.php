<?php
/**
 * @category Forum
 * @link http://www.youxiduo.com
 * @author mawenpei<mawenpei@cwan.com>
 * @since 2014-03-15
 * @version 3.0.0
 */
namespace Yxd\Services;

use Yxd\Utility\ForumUtility;

use PHPImageWorkshop\ImageWorkshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yxd\Models\Thread;
use Yxd\Models\Forum;
/**
 * 论坛主题帖服务
 */
class ThreadService extends Service
{
	/**
	 * 发主题帖
	 * @param array $topic 
	 * @param array|null $atFriends
	 * 
	 */
	public static function createTopic($topic,$atFriends=null)
	{
	    $rule = array(
		    'subject'=>array('required'),
		    'gid'=>array('required','numeric'),
		    'cid'=>array('required','numeric'),
		    'message'=>array('required'),
		);
		$validator = Validator::make($topic,$rule);
		if($validator->fails()){
			return self::send(1303,null,'miss_params','参数不全');
		}
		if(!isset($topic['uid'])){
			$token = PassportService::accessTokenToUid($topic['access_token']);
			if($token===false){
				return self::send(1104,null,'invalid_access_token','无效的令牌access_token');
			}
			$uid = $token['user_id'];
		}else{
			$uid = $topic['uid'];
		}
		$author = UserService::getUserInfo($uid);
		if(!$author){
			return self::send(1104,null,'invalid_user','无效的用户');
		}
		$thread = array(
		    'gid'=>$topic['gid'],
		    'cid'=>$topic['cid'],
		    'subject'=>$topic['subject'],
		    'author_uid' =>$author['uid'],
		    'author'=>$author['nickname'],
		    'award'=>isset($topic['award']) ? $topic['award'] : 0,
		    'ask'=>isset($topic['ask']) ? $topic['ask'] : 0,
		    'dateline'=>(int)microtime(true),
		    'lastpost'=>(int)microtime(true),
		);		 
		$message = $topic['message'];
		
        $event = Event::fire('topic.post_before',array(array($thread,$message)));
        if($event && $event[0]){
        	$thread = $event[0][0];
        	$message = $event[0][1];
        }
	    $obj = json_decode($message,true);
	    $format_message = '';
		foreach($obj as $val){
		    if(!isset($thread['listpic']) && $val['img']){
				$thread['listpic'] = $val['img'];
			}
		    if(!isset($thread['summary']) && $val['text']){
				$thread['summary'] = $val['text'];
			}														    
		}
		$format_message = ForumUtility::formatTopicMessage($obj);
		$thread['message'] = $message;
		$thread['format_message'] = $format_message;
		$tid = self::dbClubMaster()->table('forum_topic')->insertGetId($thread);
		if($tid){
			//问答帖游币
			if($topic['ask'] && $topic['award']){
				CreditService::handOpUserCredit($topic['uid'], (0-(int)$topic['award']), 0, 'topic_post_ask','发布问答求助帖消费'.$topic['award'].'游币');
			}
			$out = Thread::getFullTopic($tid);
			//触发发帖事件
			Event::fire('topic.post',array(array($out)));
			//AT好友
			AtmeService::atmeOfPostTopic($atFriends,$out);
			return self::send(200,$out);
		}else{
			return self::send(1106,null,'server_error','服务器端错误');
		}
	}	
	/**
	 * 获取主题帖列表
	 * @param int $gid 游戏ID
	 * @param int $cid 板块ID
	 * @param int $page 当前分页码
	 * @param int $pagesize 分页大小
	 * @param int $feature 标志 1：置顶 2：加精
	 * 
	 * @ret array $data=array('topics','total')  
	 */
    public static function showTopicList($gid,$cid=0,$page=1,$pagesize=20,$feature=0,$sort='lastpost')
	{
		$data = array();
		$data['total'] = Thread::getThreadCount($gid,$cid,$feature);
		$topics = Thread::getThreadList($gid,$cid,$page,$pagesize,$feature,$sort);
		$channels = Forum::getChannelKV($gid);
		$uids = array();
		foreach($topics as $key=>$row){
			$uids[] = $row['author_uid'];
		}
		$uids = array_unique($uids);
		$users = UserService::getBatchUserInfo($uids);
		foreach($topics as $key=>$topic){
			$topic['author'] = $users[$topic['author_uid']];
			$topic['channel_name'] = $channels[$topic['cid']];
			$topic['ctime'] = date('Y-m-d',$topic['dateline']);
			$topics[$key] = $topic;
		}
		$data['topics'] = $topics;
		return $data;
	}
	
	/**
	 * 显示主题帖详情
	 * 包含回帖列表
	 * @param int $tid 
	 */
	public static function showTopicInfo($tid)
	{
		$topic = Thread::getFullTopic($tid);
		if(!$topic){
			return null;
		}
		$topic['ctime'] = date('Y-m-d',$topic['dateline']);
		$message = json_decode($topic['message'],true);
		$topic['content'] = $message ? : array();
		return $topic;
	}
	
	/**
	 * 保存帖子
	 */
	public static function doSave($data)
	{
		$thread = array();
		$thread = $data;
		$thread['ask'] = $data['cid'] == 2 ? 1 : 0;		
		$thread['dateline'] = (int)microtime(true);
		$thread['lastpost'] = (int)microtime(true);
		$tid = self::dbClubMaster()->table('forum_topic')->insertGetId($thread);
		return $tid;
	}
	
	public static function doDelete($tid,$uid)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->where('author_uid','=',$uid)->update(array('displayorder'=>-1));
	}
	
	public static function isDeleted($tid)
	{
		$topic = self::dbClubSlave()->table('forum_topic')->where('tid','=',$tid)->select('tid')->where('displayorder','=',1)->first();
		return $topic ? false : true;
	}
	
	/**
	 * 更新赞数
	 */
	public static function updateLikes($tid)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->increment('likes');
	}
}