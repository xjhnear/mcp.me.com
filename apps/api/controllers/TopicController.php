<?php

use Yxd\Services\UserService;
use Yxd\Services\ThreadService;
use Yxd\Services\TaskService;
use Yxd\Services\ForumService;
use Yxd\Services\TopicService;
use Yxd\Utility\ForumUtility;
use Illuminate\Support\Facades\Input;
use PHPImageWorkshop\ImageWorkshop;
use Yxd\Utility\ImageHelper;
use Yxd\Modules\System\SettingService;

class TopicController extends BaseController
{	
	
    /**
	 * 普通帖
	 */
	public function postPostTopic()
	{
		$gid = (int)Input::get('gid');//游戏ID
		$uid  = (int)Input::get('uid');//发帖人UID
		$cid = (int)Input::get('bid');//板块ID
		$title = Input::get('title');//标题	
		$award  = (int)Input::get('award',0);//悬赏游币	
		$contentBlocks  = Input::get('contentBlocks');//内容块	
		$atFriends = explode(',',Input::get('atFriends'));//AT好友
		
		if(!$gid || !$uid || !$cid || !$title){
			return $this->fail(22222,'接口参数丢失');
		}
		
	    $config = SettingService::getConfig('pcweb_setting');
		if($config && isset($config['data']['close_topic']) && intval($config['data']['close_topic'])===1){
		    return $this->fail(22222,'系统更新,暂停评论');
		}
		
	    $is_speed = UserService::checkUserSpeed($uid);
		if($is_speed===true){
			return $this->fail(22222,'你操作太频繁了哦~请休息一下！');
		}
		
		if($cid == 2){
			$realmoney = UserService::getUserRealTimeCredit($uid,'score');
			if($realmoney<$award){
				return $this->fail(22222,'您的游币不足,悬赏失败');
			}
		}
		
	    //权限处理
		$is_ban = UserService::checkUserBan($uid);
		if($is_ban === true){
			return $this->fail(22222,'您被禁言中，无权发布的评论');
		}	
		$obj_message = array();
		
		try{
			if($contentBlocks){
				if(!is_array($contentBlocks)) $contentBlocks = json_decode($contentBlocks,true);
				foreach($contentBlocks as $item){
					$img = '';
					//$file = TopicService::doUpload($item['photoName'],null,$uid);
					$config = array(
			    	    'savePath'=>'/userdirs/',
			    	    'driverConfig'=>array('autoSize'=>array(640,480,320))
			    	);
			    	$uploader = new ImageHelper($config);
			    	$image = $uploader->upload($item['photoName']);
			    	if($image !== false){
			    		$img = $image['filepath'] . '/' . $image['filename'];
			    		$img = str_replace('.','_480.',$img);
			    	}
					
					
					//if($file['status']==200){
					//	$img = $file['data']['attachment'];
					//}
					//敏感词处理
					$filter = ForumUtility::filterWords($item['content']);
					
					if($filter===true){
						return $this->fail(22222,'您发布的评论包含非法词');
					}					
					$obj_message[] = array('img'=>$img,'text'=>$item['content']);
				}
			}else{
				return $this->fail(22222,'至少选择一张图片');
			}
		}catch(Exception $e){
			return $this->fail(22222,'数据格式传输错误');
		}
		$message = json_encode($obj_message);
		
		$topic = array(
		    'gid'=>$gid,
		    'cid'=>$cid,
		    'subject'=>$title,
		    'message'=>$message,
		    'uid'=>$uid,
		    'award'=> $cid==ForumService::CHANNEL_TYPE_QUESTION ? $award : 0,
		    'ask'=> $cid==ForumService::CHANNEL_TYPE_QUESTION ? 1 : 0		    
		);
        
		$result = Yxd\Services\ThreadService::createTopic($topic,$atFriends);
		if($result['status']==200){
			UserService::checkUserSpeed($uid,true);
			//每日发帖任务
			$msg = '发帖成功 经验+2';
		    $score = TaskService::doPostTopic($uid);
		    is_numeric($score) && $msg = '发布3条帖子成功 游币+'.$score;
		    if(is_numeric($score)){
		    	return $this->success(array('result'=>$result['data']['tid'],'errorCode'=>600,'errorMessage'=>$msg));
		    }
			return $this->success(array('result'=>$result['data']['tid'],'errorMessage'=>$msg));
		}else{
			return $this->fail(1121,$result['error_description']);
		}
	}	
	
	public function getDelete()
	{
		$tid = Input::get('aid');
		$uid = Input::get('uid');
		$success = ThreadService::doDelete($tid, $uid);
		if($success){
			return $this->success(array('result'=>null));
		}else{
			return $this->fail(11211,'帖子删除失败');
		}
	}
	
	/**
	 * 评分
	 */
	public function postGrade()
	{
		$pid = Input::get('pid');
		$access_token = Input::get('account_token');
	}
	
	/**
	 * 加精
	 */
	public function postDigest()
	{
		$tid = Input::get('tid');
		$access_token = Input::get('account_token');
	}
	
	/**
	 * 置顶
	 */
	public function postStick()
	{
		$tid = Input::get('tid');
		$access_token = Input::get('account_token');
	}	
}