<?php

use Yxd\Services\NoticeService;
use Illuminate\Support\Facades\Input;

use Yxd\Models\YxdHelper;

class MessageController extends BaseController
{
	/**
	 * 系统消息
	 */
	public function notice()
	{
		$uid = Input::get('uid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$result = NoticeService::getSystemMessageList($uid,$page,$pagesize);
		$readall = NoticeService::getReadSystemMsg($uid);
		$out = array();
		foreach($result['messages'] as $row){
			$msg = array();
			$msg['mid'] = $row['id'];
			$msg['systemInfo'] = $row['content'];
			$msg['addTime'] = date('Y-m-d H:i:s',$row['sendtime']);
			$msg['nick'] = YxdHelper::NickName();
			$msg['avatar'] = NoticeService::joinImgUrl(YxdHelper::Avatar());
			$msg['systemTitle'] = $row['title'];
			$msg['linktype'] = $row['linktype'];
			$msg['link'] = $row['link'];
			$msg['badege'] = (is_array($readall) && in_array($row['id'],$readall)) ? 0 : 1;
			$out[] = $msg;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 删除消息
	 * 3.1版新增接口
	 */
	public function delete()
	{
		$mid = Input::get('mid');
		$uid = Input::get('uid');
		if(!$mid || !$uid){
			return $this->fail(11211,'参数错误');
		}
		NoticeService::deleteNotice($mid, $uid);
		NoticeService::resetSystemMsgNum($uid, $mid);
		return $this->success(array('result'=>array()));
	}
	
	public function read()
	{
		$uid = Input::get('uid');
		$sid = Input::get('sid');
		NoticeService::resetSystemMsgNum($uid, $sid);
		return $this->success(array('result'=>''));
	}
	
	public function msgNumber()
	{
		$uid = Input::get('uid');
		$type = Input::get('typeID',0);
		//$last = Input::get('lastDate',date('Y-m-d'));
		//$lastTime = strtotime($last);
		$out = NoticeService::getMyMessageNumber($uid,$type);
		return $this->success(array('result'=>$out));
	}
}