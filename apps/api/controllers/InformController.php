<?php
use Yxd\Services\InformService;
use Illuminate\Support\Facades\Input;

class InformController extends BaseController 
{
	/**
	 * 举报主题
	 */
	public function topic()
	{
		//主题帖ID
		$tid = Input::get('linkid');
		//举报人UID
		$uid = Input::get('uid');
		if(!$tid || !$uid){
			return $this->fail(11211,'参数丢失,举报失败');
		}
		InformService::reportTopic($tid, $uid);
		return $this->success(array('result'=>null));
	}
	
	/**
	 * 举报评论
	 */
	public function comment()
	{
		$cid = Input::get('cid');
		$uid = Input::get('uid');
		$typeID = Input::get('typeID');
		
	    if(!$cid || !$uid){
			return $this->fail(11211,'参数丢失,举报失败');
		}
		
		InformService::reportComment($cid, $typeID, $uid);
		return $this->success(array('result'=>null));
	}
}