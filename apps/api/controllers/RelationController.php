<?php
use Yxd\Services\UserService;
use Illuminate\Support\Facades\Input;

use Yxd\Services\RelationService;

class RelationController extends BaseController
{    	
	/**
	 * 添加好友
	 * @deprecated
	 */
	public function postFriendCreate()
	{
	    $fuid = Input::get('fuid');
		$uid  = $this->getCurrentUid();
		if(!$uid){
			$this->send(500,null,'','');
		}
		$result = RelationService::addFriend($uid,$fuid);
		if($result==-1){
			//已经是好友
		}elseif($result>0){
			//添加成功
		}else{
			//添加失败
		}
	}
	
	/**
	 * 解除好友
	 * @deprecated
	 */
    public function postFriendDestroy()
	{
	    $fuid = Input::get('fuid');
		$uid  = $this->getCurrentUid();
		if(!$uid){
			$this->send(500,null,'','');
		}
		$result = RelationService::deleteFriend($uid,$fuid);
		if($result==-1){
			//不是好友
		}elseif($result>0){
			//解除成功
		}else{
			//解除失败
		}
	}
	
	/**
	 * 获取用户好友列表
	 * 
	 */
	public function getFriends()
	{
	    //$uid  = $this->getCurrentUid();
	    $uid = Input::get('uid');
		if(!$uid){
			$this->out(null,'',array('totalCount'=>0));
		}
		$page = Input::get('page',1);
		$pagesize = Input::get('pagesize',20);
		$result = RelationService::getFollowList($uid,$page,$pagesize);
		$out = array();
		$users = array_values($result['users']);
		foreach($users as $index=>$user){
			$out[$index]['userID'] = $user['uid'];
			$out[$index]['userName'] = $user['nickname'];
			$out[$index]['userAvator'] = RelationService::joinImgUrl($user['avatar']);
			$out[$index]['userLevel'] = $user['level_name'];
		}
		$result = array('result'=>$out,'totalCount'=>$result['total']);
		return $this->success($result);
	}
	
    /**
	 * 获取共同的好友
	 */
	public function getFriendsCommon()
	{
		
	}		    
	
	public function getFollowCreate()
	{
		$uid = Input::get('uid');
		$fuid = Input::get('targetID');
		
		if(!$uid){
			return $this->fail(11211,'您尚未登录');
		}
		
		if(!$fuid){
			return $this->fail(11211,'您没有选择关注对象');
		}
		
		if($uid==$fuid){
			return $this->fail(11211,'不能关注自己');
		}
		$result = RelationService::addFollow($uid,$fuid);
		if($result['status']==200){
			$out = array();
			$user = UserService::getUserInfo($fuid);
			$out['userBase']['userID'] = $user['uid'];
			$out['userBase']['userName'] = $user['nickname'];
			$out['userBase']['userAvatar'] = RelationService::joinImgUrl($user['avatar']);
			$out['userBase']['userLevel'] = $user['level_name'];
			return $this->success(array('result'=>$out));
		}else{
			return $this->fail(11211,'已经关注过了');
		}
		
	}
	
	public function getFollowDestroy()
	{
	    $uid = Input::get('uid');
		$fuid = Input::get('targetID');
		$result = RelationService::deleteFollow($uid,$fuid);
		if($result['status']==200){
			$out = array();
			$user = UserService::getUserInfo($fuid);
			$out['userBase']['userID'] = $user['uid'];
			$out['userBase']['userName'] = $user['nickname'];
			$out['userBase']['userAvatar'] = RelationService::joinImgUrl($user['avatar']);
			$out['userBase']['userLevel'] = $user['level_name'];
			return $this->success(array('result'=>$out));
		}else{
			return $this->fail(11211,'尚未关注该用户');
		}
	}		
	
	/**
	 * 获取用户粉丝列表
	 */
	public function getFollowers()
	{
		$out = array();
		//$uid  = $this->getCurrentUid();
		$uid = Input::get('uid');
		$muid = Input::get('muid',0);
		if(!$uid){
			$this->out(null,'',array('totalCount'=>0));
		}
		$page = Input::get('page',1);
		$pagesize = Input::get('pagesize',20);
		$result = RelationService::getFollowerList($uid,$page,$pagesize);
		$out = array();
		$follows = RelationService::getFollowUids($muid ? $muid : $uid);
		//$users = array_values($result['users']);
		foreach($result['users'] as $index=>$user){
			$tmp = array();
			$tmp['uid'] = $user['uid'];
			$tmp['nick'] = $user['nickname'];
			$tmp['avatar'] = RelationService::joinImgUrl($user['avatar']);
			$tmp['levelImg'] = UserService::joinImgUrl($user['level_icon']);
			$tmp['gender'] = $user['sex'];
			$tmp['signature'] = $user['summary'];
			$tmp['date'] = date('Y-m-d H:i:s',$user['dateline']);
			$tmp['attention'] = in_array($user['uid'],$follows) ? 1 : 0;			
			$out[] = $tmp;
		}
		$result = array('result'=>$out,'totalCount'=>$result['total']);
		return $this->success($result);
	}
	
	/**
	 * 获取用户关注列表
	 */
	public function getFollows()
	{
		//$uid  = $this->getCurrentUid();
		$uid = Input::get('uid');
		$muid = Input::get('muid',0);
		if(!$uid){
			return $this->fail(11211,'uid参数不能为空');
		}
		$page = Input::get('page',1);
		$pagesize = Input::get('pagesize',20);
		$result = RelationService::getFollowList($uid,$page,$pagesize);
		$out = array();
		$follows = RelationService::getFollowUids($muid ? $muid : $uid);
		$users = array_values($result['users']);
		foreach($users as $index=>$user){
			$tmp = array();
			$tmp['uid'] = $user['uid'];
			$tmp['nick'] = $user['nickname'];
			$tmp['avatar'] = RelationService::joinImgUrl($user['avatar']);
			$tmp['levelImg'] = UserService::joinImgUrl($user['level_icon']);
			$tmp['gender'] = $user['sex'];
			$tmp['signature'] = $user['summary'];
			$tmp['date'] = date('Y-m-d H:i:s',$user['dateline']);
			$tmp['attention'] = in_array($user['uid'],$follows) ? 1 : 0;	
			$out[] = $tmp;
		}
		$result = array('result'=>$out,'totalCount'=>$result['total']);
		return $this->success($result);
	}	
	
    /**
	 * 获取共同关注列表
	 */
	public function getFollowsCommon()
	{
		
	}
}