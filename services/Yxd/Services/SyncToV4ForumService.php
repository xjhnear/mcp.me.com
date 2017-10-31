<?php
namespace Yxd\Services;

use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\BaseService;
use Illuminate\Support\Facades\Log;

use Youxiduo\Bbs\TopicService as AnotherTopicService;

class SyncToV4ForumService extends BaseService
{
	public static function syncTopic($tid=0, $type=NULL)
	{
	    if ($tid>0) {
	        $wait_data = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->get();
	    } else {
	        $wait_data = self::dbClubMaster()->table('forum_topic')->where('is_sync','=',0)->orderBy('tid','asc')->forPage(1,10)->get();
	    }
		if($wait_data){
			foreach($wait_data as $row){
			    if ($type) {
			        $row['sync_type'] = $type;
			    }
				switch($row['sync_type']){
					case 'add':
						self::syncAddTopic($row);
						break;
					case 'edit':
						self::syncEditTopic($row);
						break;
					case 'delete':
						self::syncDeleteTopic($row);
						break;
					default:
						break;
				}
			}
		}
	}
	
	protected static function syncAddTopic($row)
	{
		if(!$row['subject'] || $row['displayorder']<0) return false;
		$tid = $row['tid'];
		$fid = self::getForumId($row['gid']);
		$uid = $row['author_uid'];
		$bid = $row['cid'];
		$subject = $row['subject'];
		$content = $row['message'];
		$formatContent = $row['format_message'];
		$award = $row['award'];
		$fromTag = 1;
		$result = AnotherTopicService::doPostAdd($fid, $uid, $bid, $subject,$content,$award,$fromTag,$displayOrder=0,$isActivity=false,
									$isAdmin=false,$isRule=false,$isGood=false,$isAsk=false,$askStatus=false,$hashValue=false,
									$summary=false,$listpic=false,$formatContent,$tagid=false,$tid,true,false,true);
	    if($result && $result['errorCode']=='0'){
			self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('is_sync'=>1));
			return true;
		}
		return false;
	} 
	
    protected static function syncEditTopic($row)
	{
		$tid = $row['tid'];
		$fid = self::getForumId($row['gid']);
		$uid = $row['author_uid'];
		$bid = $row['cid'];
		$subject = $row['subject'];
		$content = $row['message'];
		$formatContent = $row['format_message'];
		$award = $row['award'];
		$fromTag = 1;
		$result = AnotherTopicService::modifyTopic($tid,$uid,$fid,$bid,$subject,$content,$award,$formatContent,$listpic=false,$hashValue=false);
	    if($result && $result['errorCode']=='0'){
			self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('is_sync'=>1));
			return true;
		}		
		return false;
	}	
	
    protected static function syncDeleteTopic($row)
	{
		$tid = $row['tid'];
		$result = AnotherTopicService::delTopic($tid);
		if($result && $result['errorCode']=='0'){
			self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('is_sync'=>1));
			return true;
		}
		return false;
	}
	
	protected static function getForumId($game_id)
	{
		static $forumIds = array();
		if(isset($forumIds[$game_id])) return $forumIds[$game_id];
		$info = self::dbForumMaster()->table('forum_game_link')->where('gid','=',$game_id)->first();
		if($info){
			$forumIds[$game_id] = $info['fid'];
			return $info['fid'];
		}
		return 0;
	}
	
	public static function syncReply($id=0, $type='add')
	{
	    if ($id>0) {
    		$wait_data = self::dbClubMaster()->table('comment')
    		->where('target_table','=','yxd_forum_topic')
    		->where('id','=',$id)
    		->get();
	    } else {
    		$wait_data = self::dbClubMaster()->table('comment')
    		->where('is_sync','=',0)
    		->where('target_table','=','yxd_forum_topic')
    		->orderBy('id','asc')
    		->forPage(1,30)
    		->get();
	    }
		if($wait_data){
			foreach($wait_data as $row){
			    switch($type){
			        case 'add':
			            self::syncAddReply($row);
			            break;
			        case 'setbest':
			            self::syncBestReply($row);
			            break;
			        case 'delete':
			            self::syncDeleteReply($row);
			            break;
			        default:
			            break;
			    }
			}
		}
	}
	
	protected static function syncAddReply($row)
	{
	    if ($row['content']) {
	        $con_arr = json_decode($row['content'],true);
	        $listpic = isset($con_arr['img'])?$con_arr['img']:'';
	    }
	    $result = AnotherTopicService::doReplyAdd($row['uid'], $row['target_id'],$row['content'],$row['format_content'],'',false,'TOPIC',1,$listpic,'',$row['id']);
	    if($result && $result['errorCode']=='0'){
	        self::dbClubMaster()->table('comment')->where('id','=',$row['id'])->update(array('is_sync'=>1));
	    }
	}
	
	protected static function syncDeleteReply($row)
	{
	    $result = AnotherTopicService::delReply($row['id'],false,false);
// 	    if($result && $result['errorCode']=='0'){
// 	        self::dbClubMaster()->table('comment')->where('id','=',$row['id'])->update(array('is_sync'=>1));
// 	    }
	}
	
	protected static function syncBestReply($row)
	{
	    $result = AnotherTopicService::setBestReply($row['id'],$row['target_id'],$row['uid']);
// 	    if($result && $result['errorCode']=='0'){
// 	        self::dbClubMaster()->table('comment')->where('id','=',$row['id'])->update(array('is_sync'=>1));
// 	    }
	}
}