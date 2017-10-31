<?php
namespace modules\v4_message\controllers;

use Youxiduo\Message\PushService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Yxd\Modules\Message\NoticeService;

use ApnsPHP\Push;
use ApnsPHP\AbstractClass;
use ApnsPHP\Message\BaiduCustom;



class PushController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4_message';
	}
	
	public function getList()
	{
		$data = $params = $search = array();
		
		$page = Input::get('page',1);
		$pagesize = 10;
		$beginTime = Input::get('beginTime');
		$endTime = Input::get('endTime');
		$params['pageIndex'] = $page; 
		$params['pageSize'] = $pagesize;
		if(!empty($beginTime)){
			$params['beginTime'] = date('Y-m-d H:i:s',strtotime($beginTime)); 
			$search['beginTime'] = $beginTime;
		}
		if(!empty($endTime)){
			$params['endTime'] = date('Y-m-d H:i:s',strtotime($endTime));
			$search['endTime'] = $endTime;
		}
		$result = PushService::getMessageList($params);	
		$total = ceil($result['total']/$pagesize);
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		return $this->display('push-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$data['linkTypeList'] = NoticeService::$LinkTypeList;
		return $this->display('push-info',$data);
	}		
	
	public function postSave()
	{	
		$params = array();
		$params['title'] = Input::get('title');
		$params['content'] = Input::get('content');
		$params['sendTime'] = date('Y-m-d H:i:s',time());
		$params['linkType'] = Input::get('linktype');
		$params['link'] = Input::get('link');
		$params['isTop'] = Input::get('is_top');
		$params['type'] = 1;
		$ispush = Input::get('is_push');
		empty($ispush) ? : $params['isPush'] = 'true';
		$msgtype = Input::get('msgtype');
		if($msgtype == 2){
			$params['toUid'] = 0;
			$params['allUser'] = 'true';
		}elseif($msgtype == 1){
			$to_uids = Input::get('to_uids','');
			if(empty($to_uids)){
				return $this->back()->with('global_tips','定向发送方式下，用户UID不能为空');
			}else{
				$params['toUid'] = $to_uids;
			}
			
		}
	    $validator = Validator::make(array(
	        'title'=>$params['title'],
	        'content'=>$params['content'],
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
		
		$result = PushService::addMessage($params);
		return $this->redirect('v4_message/push/list','发送成功');
	}
}