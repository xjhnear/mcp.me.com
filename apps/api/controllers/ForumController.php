<?php

use Yxd\Services\Cms\GameCircleService;
use Yxd\Services\ThreadService;
use Yxd\Services\RelationService;
use Yxd\Services\ForumService;
use Yxd\Services\LikeService;
use Yxd\Services\Cms\CommentService;
use Illuminate\Support\Facades\Input;

class ForumController extends BaseController
{		
	/**
	 * 获取版块信息
	 */
	public function getChannel()
	{
		$gid = Input::get('gid');
		//判断gid是否存在		
		$result = ForumService::getChannelList($gid);
		
		return $this->send(200,$result['data']);
	}	
		
	/**
	 * 论坛首页
	 */
	public function home()
	{
		$gid = Input::get('gid');
		$bid = Input::get('bid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$order = Input::get('sort',0);
		if($order==0){
			$sort = 'lastpost';
		}else{
			$sort = 'replies';
		}
		$out = array();
		$channels = ForumService::getChannelList($gid,true);
		foreach($channels['data'] as $row){	
			$channel['bType'] = $row['cid'];
			$channel['imageURL'] = ForumService::joinImgUrl(ForumService::getChannelIcon($row['cid']));
			$channel['imageSelectedURL'] = ForumService::joinImgUrl(str_replace('.png','_selected.png',ForumService::getChannelIcon($row['cid'])));
			$channel['title'] = $row['name'];	
			$out['BBSTags'][] = $channel;
		}
		
		
		$out['BBSMembersInfo'] = array();
		$members = RelationService::getCircleUserList($gid);
		
		$out['BBSMembersInfo']['memberCount'] = GameCircleService::getGameCircleUserCount($gid);;//$members['total'];
		$out['BBSMembersInfo']['userBaseInfos'] = array();
		foreach($members['users'] as $row){
			$member['userID'] = $row['uid'];
			$member['userName'] = $row['nickname'];
			$member['userAvator'] = RelationService::joinImgUrl($row['avatar']);
			$member['userLevel'] = $row['level_name'];
			$out['BBSMembersInfo']['userBaseInfos'][] = $member;
		}
		$out['BBSArticles'] = array();
		$topics = ThreadService::showTopicList($gid,$bid,$page,$pagesize,0,$sort);
		foreach($topics['topics'] as $row){
			$topic['articleID'] = $row['tid'];
			$topic['articleType'] = $row['cid'];
			$topic['articleTitle'] = $row['subject'];
			$topic['articleContent'] = $row['summary'];
			$topic['articleImage'] = ThreadService::joinImgUrl($row['listpic']);
			if($row['listpic'] && file_exists(storage_path() . $row['listpic'])){
				list($width,$height,$type,$attr) = getimagesize(storage_path() . $row['listpic']);
				$topic['imageWidth'] = $width;
				$topic['imageHeight'] = $height;
			}else{
				$topic['imageWidth'] = 0;
				$topic['imageHeight'] = 0;
			}
			$topic['authorName'] = $row['author']['nickname'];
			$topic['authorAvatar'] = ThreadService::joinImgUrl($row['author']['avatar']);
			$topic['authorLevelImage'] = ThreadService::joinImgUrl($row['author']['level_icon']);
			$topic['likes'] = $row['likes'];
			//3.1版新增字段
			$topic['digest'] = $row['digest'];
			$topic['pubDate'] = date('Y-m-d H:i:s',$row['lastpost']);		
			$topic['commentCount'] = $row['replies'];
			$topic['questionState'] = $row['askstatus'];
			$topic['reward'] = $row['award'];
			$out['BBSArticles'][] = $topic;
		}
				
		$out['announcementInfos'] = array();
		$notices = ForumService::getNoticeList($gid);
		foreach($notices as $row){		
			$notice['aid'] = $row['tid'];
			$notice['aTitle'] = $row['subject'];
			$out['announcementInfos'][]=$notice;
		}
		
		
		return $this->success(array('result'=>$out,'totalCount'=>$topics['total']));
	}
	
	/**
	 * 论坛帖子列表
	 */
	public function getTopicList()
	{
		$gid = Input::get('gid',0);
		$cid = Input::get('bid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$type = Input::get('type',0);
		$result = ForumService::getTopicList($gid,$cid,$page,$pagesize,$type);
		return $this->success(array('result'=>$result['list'],'totalCount'=>$result['total']));
	}	
	
	public function circleFriends()
	{
		$uid = Input::get('uid');
		$gid = Input::get('gid');
		
		$result = ForumService::getCircleFriends($gid, $uid);
		
		return $this->success($result);
	}
}