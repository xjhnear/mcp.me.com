<?php
use Yxd\Modules\System\SettingService;
use Yxd\Services\UserService;
use Yxd\Utility\ForumUtility;
use Yxd\Services\ForumService;
use Yxd\Services\Cms\CommentService;
use Yxd\Services\Cms\ArticleService;
use Yxd\Services\TopicService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
/**
 * 评论
 */
class CommentController extends BaseController
{
	public function home()
	{
		$type = Input::get('typeID');
		$target_id = Input::get('linkid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$comment_type = Config::get('yxd.comment_type');
		if(!isset($comment_type[$type])){
			return $this->fail(1121,'评论类型不存在');
		}
		$target_table = $comment_type[$type];
		if(empty($target_id)){
			return $this->fail(1121,'评论目标不存在');
		}		
	    //评论
		$out = array();
		$out['isQuestion'] = 0;
		
		$out['isFinish'] = 0;
	    $out['commentInfos'] = array();
	    $comments = CommentService::getAppOfList($target_id,$target_table,$page,$pagesize);
	    
	    foreach($comments['result'] as $row){
			$comment = array();
			$comment['cid'] = $row['id'];
			$comment['isBest'] = 0;
			$comment['floorIndex'] = $row['storey'];
			$row['content'] = json_decode($row['content'],true);
			$comment['replyInfo']['replyTopic'] = 0;
			if($row['content'] && count($row['content'])>0){									
			    $comment['replyInfo']['replyContent'] = $row['content'][0]['text'];
			    $comment['replyInfo']['replyImage'] = CommentService::joinImgUrl($row['content'][0]['img']);		
				if($row['content'][0]['img'] && file_exists(storage_path() . $row['content'][0]['img'])){
					list($width,$height,$type,$attr) = getimagesize(storage_path() . $row['content'][0]['img']);
					$comment['replyInfo']['replyImageWidth'] = $width;
					$comment['replyInfo']['replyImageHeight'] = $height;
				}else{
					$comment['replyInfo']['replyImageWidth'] = 0;
					$comment['replyInfo']['replyImageHeight'] = 0;
				}		
			}
			$comment['replyInfo']['replyDate'] = date('Y-m-d H:i:s',$row['addtime']);
			$comment['replyInfo']['tocid'] = $row['pid'];
			
			$comment['replyInfo']['fromUser']['userID'] = $row['author']['uid'];
			$comment['replyInfo']['fromUser']['userName'] = $row['author']['nickname'];
			$comment['replyInfo']['fromUser']['userAvator'] = CommentService::joinImgUrl($row['author']['avatar']);
			$comment['replyInfo']['fromUser']['userLevel'] = $row['author']['level_name'];
			$comment['replyInfo']['fromUser']['userLevelImage'] = CommentService::joinImgUrl($row['author']['level_icon']);
			if(isset($row['quote']) && $row['quote']){
				$row['quote']['content'] = json_decode($row['quote']['content'],true);
				if($row['quote']['content'] && count($row['quote']['content'])>0){									
				    $comment['replyInfo']['toContent'] = $row['quote']['content'][0]['text'];
				    $comment['replyInfo']['toImage'] = CommentService::joinImgUrl($row['quote']['content'][0]['img']);	
					if($row['quote']['content'][0]['img'] && file_exists(storage_path() . $row['quote']['content'][0]['img'])){
						list($width,$height,$type,$attr) = getimagesize(storage_path() . $row['quote']['content'][0]['img']);
						$comment['replyInfo']['toImageWidth'] = $width;
						$comment['replyInfo']['toImageHeight'] = $height;
					}else{
						$comment['replyInfo']['toImageWidth'] = 0;
						$comment['replyInfo']['toImageHeight'] = 0;
					}			
				}
				$comment['replyInfo']['toUser']['userID'] = $row['quote']['author']['uid'];
				$comment['replyInfo']['toUser']['userName'] = $row['quote']['author']['nickname'];
			}
			
			$out['commentInfos'][] = $comment;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$comments['total']));
	}
	
	/**
	 * 评论/回帖
	 */
	public function postComment()
	{
		$uid = Input::get('uid');//用户ID
		$linkid = Input::get('linkid');//目标ID
		$typeid = Input::get('typeID');//类型		
		$isreply = Input::get('replyTopic');//是否回复主题
		$replycid = Input::get('replycid',0);//被回复的评论ID		
		
		if(!$uid){
			return $this->fail(22222,'请求参数不全');
		}
		
		$config = SettingService::getConfig('pcweb_setting');
		if($config && isset($config['data']['close_comment']) && intval($config['data']['close_comment'])===1){
		    return $this->fail(22222,'系统更新,暂停评论');
		}
		
		$atFriends = Input::get('atFriends',null);//AT好友
		if($atFriends){
			$atFriends = explode(',',$atFriends);
		}
		$obj_message = array();
		
		$is_speed = UserService::checkUserSpeed($uid);
		if($is_speed===true){
			return $this->fail(22222,'你操作太频繁了哦~请休息一下！');
		}
		
	    //权限处理
		$is_ban = UserService::checkUserBan($uid);
		if($is_ban === true){
			return $this->fail(22222,'您被禁言中，无权发布的评论');
		}
				
		
		//敏感词处理
		$filter = ForumUtility::filterWords(Input::get('replyContent',''));
		if($filter===true){
			return $this->fail(22222,'您发布的评论包含非法词');
		}
		
		$obj_message[0] = array('text'=>$filter);
		$file = TopicService::doUpload('image',null,$uid);
		if($file['status']==200){
			$obj_message[0]['img'] = $file['data']['attachment'];
		}else{
			$obj_message[0]['img'] = '';
		}
						
		$message = json_encode($obj_message);
		$comment_type = Config::get('yxd.comment_type');
		$target_table = $comment_type[$typeid];
	    $result = $this->replyComment($uid, $linkid,$replycid, $target_table, $message,$obj_message[0]['text'],$atFriends);		    
	    if(is_numeric($result) && $result>0){
	    	UserService::checkUserSpeed($uid,true);	    	
	    	$cmt = CommentService::getInfo($result);
	    	$msg = CommentService::doCredit($target_table,$uid);
	    	if($msg && is_numeric($msg['score'])){
	    		return $this->success(array('result'=>$cmt,'errorCode'=>600,'errorMessage'=>$msg['msg']));
	    	}else{
	    	    return $this->success(array('result'=>$cmt,'errorMessage'=>$msg['msg']));
	    	}
	    }else{
	    	return $this->fail('22222',$result['error']);
	    }
		
		/*
		if($typeid==0){//帖子
			$result = $this->replyTopic($uid,$linkid,$replycid,$typeid,$message,$atFriends);
		    if(is_numeric($result) && $result>0){
		    	$cmt = ForumService::getReplyInfo($result);
		    	return $this->success(array('result'=>$cmt));
		    }else{
		    	return $this->fail('11211',$result['error']);
		    }
		}else{//评论
		    $result = $this->replyComment($uid, $linkid,$replycid, $typeid, $message,$atFriends);		    
		    if(is_numeric($result) && $result>0){
		    	$cmt = CommentService::getInfo($result);
		    	return $this->success(array('result'=>$cmt));
		    }else{
		    	return $this->fail('11211',$result['error']);
		    }
		}
		*/
		
	}

	protected function replyComment($uid,$linkid,$replycid,$target_table,$message,$format_message,$atFriends)
	{
		
		$comment = array(
			'uid'=>$uid,
			'target_id'=>$linkid,
			'target_table'=>$target_table,
			'content'=>$message,
		    'format_content'=>$format_message,
		    'pid'=>$replycid,
		    'addtime'=>time()
		);
		
		return CommentService::createComment($comment,$atFriends);
	}
	
	/**
	 * @deprecated
	 */
	protected function replyTopic($uid,$linkid,$replycid,$typeid,$message,$atFriends)
	{
		$reply = array(
		    'gid'=>0,
		    'tid'=>$linkid,
		    'rid'=>$replycid,
		    'subject'=>'',
		    'message'=>$message,
		    'uid'=>$uid	
		);
		
	    $result = TopicService::createReply($reply,$atFriends);
	    if($result['status']===200){
	    	return $result['data']['pid'];
	    }else{
	    	return array('error'=>$result['error_description']);
	    }
	}
	
	/**
	 * 删除评论
	 */
	public function deleteComment()
	{
		$uid = Input::get('uid');
		$cid = Input::get('cid');
		$status = CommentService::deleteComment($cid,$uid);
		if($status===-1){
			return $this->fail(11211,'评论不存在');
		}elseif($status==-2){
			return $this->fail(11211,'你无权删除别人的评论');
		}elseif($status>0){
			return $this->success(array('result'=>null));
		}else{
			return $this->fail(11211,'评论删除失败');
		}
		
	}
	
	/**
	 * 设置最佳答案
	 */
    public function setBest()
	{
		$uid = Input::get('uid');
		$cid = Input::get('cid');
		$status = CommentService::setBest($cid,$uid,1);
	    if($status===-1){
			return $this->fail(11211,'评论不存在');
		}elseif($status===-2){
			return $this->fail(11211,'相同设备的提问者和回答者不能设置最大答案');
		}		
		return $this->success(array('result'=>null));
	}
}