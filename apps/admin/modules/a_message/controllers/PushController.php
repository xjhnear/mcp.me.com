<?php
namespace modules\a_message\controllers;


use Youxiduo\Android\Model\UserDevice;
use Youxiduo\Helper\Utility;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Message\NoticeService;
use Youxiduo\Android\BaiduPushService;
use Youxiduo\Message\Model\MessageType;
use Youxiduo\Message\YouPushService;


class PushController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_message';
	}
	
	public function getList()
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
        $type = 3;
		$pushType = null;
        $begin_time = Input::get('begin_time',null);
        $end_time = Input::get('end_time',null);
		$pushPlatform = Input::get('pushPlatform',1);
        $begin_time && $begin_time = date('Y-m-d H:i:s',strtotime($begin_time));
        $end_time && $end_time = date('Y-m-d H:i:s',strtotime($end_time));
        $search = array('begin_time'=>$begin_time,'end_time'=>$end_time,'pushPlatform'=>$pushPlatform);
		$result = YouPushService::searchMessageList($pushType,$begin_time,$end_time,$page,$pagesize,3,$pushPlatform);
		foreach($result['result'] as $key=>$row){
			$row['messages'] = json_decode($row['messages']);
			$result['result'][$key] = $row;
		}
		//print_r($result);exit;
		$pager = Paginator::make(array(),$result['totalCount'],$pagesize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
        $data['search'] = $search;
		return $this->display('push-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$result = MessageType::getList();
		$linkTypeListDesc = array();
		foreach($result as $key=>$row){
			$linkTypeList[$key] = $row['name'];
			$linkTypeListDesc[$key] = $row['description'];
		}
		
		$data['linkTypeList'] = $linkTypeList;
		$data['descs'] = json_encode($linkTypeListDesc);
		$token = csrf_token();
		Session::put('csrf_token',$token);
		$data['csrf_token'] = $token;
		return $this->display('push-info',$data);
	}		
	
	public function postSave()
	{		
		$push = Input::get('is_push',0);
		$linkType = Input::get('linktype',0);
		$link = Input::get('link',0);
		$title = Input::get('title');
		$content = Input::get('content');
		$to_uids = Input::get('to_uids','');
		$msgtype = Input::get('msgtype');
		$csrf_token = Input::get('csrf_token');
		$session_token = Session::get('csrf_token');
		$pushPlatform = Input::get('pushPlatform',1);
		if($csrf_token != $session_token){
			return $this->back('令牌验证失败，重复或无效提交');
		}
		
		if($to_uids){
			$uids = explode(',',$to_uids);
		}else{
			$uids = null;
		}
	    $validator = Validator::make(array(
	        'title'=>$title,
	        'content'=>$content,
	    	'msgtype'=>$msgtype
	    ),
	    array(
		    'title'=>'required',
		    'content'=>'required',
	    	'msgtype'=>'required'
		));
		if($validator->fails()){
			if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
		    if($validator->messages()->has('content')){
				return $this->back()->with('global_tips','内容不能为空');
			}
			if($validator->messages()->has('msgtype')){
				return $this->back()->with('global_tips','发送方式不能为空');
			}
		}

		Session::forget('csrf_token');
		if($msgtype == 1){
			if(!$uids) return $this->back()->with('global_tips','定向发送方式下，用户UID不能为空');
			$toUids = explode(',',$to_uids);
			$isPush = $push==1 ? true : false;
            if(!$link) $link = 0;
			NoticeService::sendMessage($title, $content, $linkType, $link, $toUids, $isPush,false,0,$pushPlatform);
		}elseif($msgtype == 2){
			$isPush = $push==1 ? true : false;
			NoticeService::sendOneMessage($title, $content, $linkType, $link, 0, $isPush,true,0,$pushPlatform);
		}
				
		return $this->redirect('a_message/push/list');
	}
	
	public function getDemo()
	{
		$toUid = array('5386037','5346005','5705584','5362662','100240');
		$tag_name = 'tag_name_test_demo';
		BaiduPushService::createTag($tag_name);
		foreach($toUid as $uid){
			$channel_id = UserDevice::db()->where('uid','=',$uid)->pluck('channel_id');
		    BaiduPushService::addTagDevice($tag_name,$channel_id);
		}
		$title = '组推测试';
		$icon = Utility::getImageUrl('/u/gameico/201507/5qcutj.jpg');
		$giftbag_id = '2928';
		$content = array('gid'=>'3389','gfid'=>'2928','gname'=>'测试的游戏','giftTotalCount'=>100,'giftLeftCount'=>5);
		$send_res = BaiduPushService::pushTagMessage($title,$icon,11,4,
                                                    $giftbag_id,$tag_name,$content,false,true,true,implode(',',$toUid));
	}
}