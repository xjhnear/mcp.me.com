<?php
namespace modules\chat\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use libraries\Helpers;
use Yxd\Services\UserService;
use Youxiduo\Helper\Utility;

class ChatActivityController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'chat';
	}
	
	public function getList()
	{
		$pageIndex = Input::get('page',1);
		$pageSize=10;
		$data = array();
		$total = $this->loadCountData();
		$data['datalist'] = $this->loadListData($pageIndex,$pageSize);
		$pager = Paginator::make(array(),$total,$pageSize);
		$data['pagelink'] = $pager->links();
		
		return $this->display('activity-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
		    $data['activity'] = $this->loadData($id);
		}
		return $this->display('activity-info',$data);
	}
	
	public function postEdit()
	{
		$id = Input::get('id');
		$input['name'] = Input::get('name');
		$input['aid'] = Input::get('aid');
		$input['gid'] = Input::get('gid');
		$input['giftbagId'] = Input::get('giftbagId');
		$input['keyword'] = Input::get('keyword');
		$input['chance'] = Input::get('chance');
		$input['startTime'] = Input::get('startTime');
		$input['endTime'] = Input::get('endTime');
		$input['onOrOff'] = Input::get('onOrOff') ? true : false;
		
		
		$input['startTime'] = $input['startTime'].':00';
		$input['endTime'] = $input['endTime'].':00';
		if($id){
			$input['id'] = $id;
			$success = $this->updateData($input);
		}else{
			$success = $this->addData($input);
		}
		if($success===true){
		    return $this->redirect('chat/chatactivity/list','保存成功');
		}else{			
			return $this->back($success);
		}
	}
	
	protected function addData($data)
	{
		$url = Config::get('app.android_chat_api_url') . 'add-activity';
		$res = Utility::loadByHttp($url,$data,'POST');
		if($res['errorCode']>0) return $res['errorDescription'];
		return true;
	}
	
	protected function updateData($data)
	{
		
		$url = Config::get('app.android_chat_api_url') . 'update-activity';
		$res = Utility::loadByHttp($url,$data,'POST','json');
		if($res['errorCode']>0) return $res['errorDescription'];
		return true;
	}
	
    protected function loadData($id)
	{
		$url = Config::get('app.android_chat_api_url') . 'activity-list';
		$params = array('id'=>$id);
		$res = \CHttp::request($url,$params);
		//print_r($res['result'][0]);
		$result = isset($res['result'][0]) ? $res['result'][0] : array();

		return $result;
	}
	
	protected function loadCountData()
	{
		$url = Config::get('app.android_chat_api_url') . 'activity-number';
		$params = array();
		$res = \CHttp::request($url,$params);
		
		return $res['totalCount'];
	}
	
	protected function loadListData($pageIndex=1,$pageSize=20)
	{
		$url = Config::get('app.android_chat_api_url') . 'activity-list';
		$params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
		$res = \CHttp::request($url,$params);
		//print_r($result);
		$result = $res['result'];

		return $result;
	}
}