<?php
use Yxd\Services\UserService;
use Yxd\Services\LikeService;
use Illuminate\Support\Facades\Input;
/**
 * 赞
 */
class LikeController extends BaseController
{
	public function dolike()
	{
		$id = Input::get('linkid');
		$type = Input::get('typeID');
		$uid = Input::get('uid');
		if(!$id || !$uid){
			return $this->fail(1121,'接口参数丢失');
		}
		$result = LikeService::doLike($id, $type, $uid);
		if($result===true){
			$out = array();
			$user = UserService::getUserInfo($uid);
			$out['userBase']['userID'] = $user['uid'];
			$out['userBase']['userName'] = $user['nickname'];
			$out['userBase']['userAvatar'] = UserService::joinImgUrl($user['avatar']);
			$out['userBase']['userLevel'] = $user['level_name'];
			return $this->success(array('result'=>$out));
		}elseif($result===-1){
			
		}
		return $this->fail(1121,'已经赞过了');
	}
	
	public function users()
	{
		$id = Input::get('aid');
		$type = Input::get('type');
		$uid = Input::get('uid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		
		$result = LikeService::getLikeList($id,$type,$uid,$page,$pagesize);
		
		$total = $result['total'];
		$out = array();
		foreach($result['likes'] as $index=>$row){
			$out[$index]['userID'] = $row['uid'];
			$out[$index]['userName'] = $row['nickname'];
			$out[$index]['userAvatar'] = LikeService::joinImgUrl($row['avatar']);
			$out[$index]['userAvator'] = LikeService::joinImgUrl($row['avatar']);
			$out[$index]['userLevel'] = $row['level_name'];
			$out[$index]['userLevelImage'] = self::joinImgUrl($row['level_icon']);
			$out[$index]['signature'] = $row['summary'];
			$out[$index]['attention'] = $row['attention'];
		}
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
}