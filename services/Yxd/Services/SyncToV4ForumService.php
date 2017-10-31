<?php
namespace Yxd\Services;

use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\BaseService;
use Illuminate\Support\Facades\Log;

use Youxiduo\Bbs\TopicService;

class SyncToV4ForumService extends BaseService
{
	public static function syncTopic()
	{
		$wait_data = self::dbClubMaster()->table('forum_topic')->where('is_sync','=',0)->orderBy('tid','asc')->forPage(1,10)->get();
		if($wait_data){
			foreach($wait_data as $row){
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
		$result = TopicService::doPostAdd($fid, $uid, $bid, $subject,$content,$award,$fromTag,$displayOrder=0,$isActivity=false,
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
		$result = TopicService::modifyTopic($tid,$uid,$fid,$bid,$subject,$content,$award,$formatContent,$listpic=false,$hashValue=false);
	    if($result && $result['errorCode']=='0'){
			self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('is_sync'=>1));
			return true;
		}		
		return false;
	}	
	
    protected static function syncDeleteTopic($row)
	{
		$tid = $row['tid'];
		$result = TopicService::delTopic($tid);
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
		$info = self::dbForumMaster()->table('forum_game_link')->where('gid','=',$game_id)->where('type','=',1)->first();
		if($info){
			$forumIds[$game_id] = $info['fid'];
			return $info['fid'];
		}
		return 0;
	}
	
	public static function syncReply()
	{
                return true;
		$wait_data = self::dbClubMaster()->table('comment')
		->where('is_sync','=',0)
		->where('target_table','=','yxd_forum_topic')
		->orderBy('id','asc')
                ->forPage(1,30)
		->get();
		if($wait_data){
			foreach($wait_data as $row){
				$result = TopicService::doReplyAdd($row['uid'], $row['target_id'],$row['content'],$row['format_content']);
				if($result && $result['errorCode']=='0'){
					self::dbClubMaster()->table('comment')->where('id','=',$row['id'])->update(array('is_sync'=>1));
				}
			}
		}
	}
}
