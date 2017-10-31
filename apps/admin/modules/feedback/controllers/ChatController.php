<?php
namespace modules\feedback\controllers;
use Yxd\Services\ChatService;

use modules\feedback\models\ChatModel;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

class ChatController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'feedback';
	}
	
	public function getUsers()
	{
		$data = array();
		$page = Input::get('page',1);
		$search = Input::only('keytype','keyword');
		$pagesize = 10;
		$uid = 1;
		$result = ChatModel::getChatUserList($uid,$search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['cond'] = $search;
		return $this->display('chat-users',$data);
	}
	
    public function getList($from_uid)
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$uid = 1;
		$data['from_user'] = UserService::getUserInfo($from_uid);
		$result = ChatModel::getChatList($from_uid,$uid,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		return $this->display('chat-list',$data);
	}
	
	public function postSend()
	{
		$from_uid = 1;
		$to_uid = Input::get('to_uid');
		$message = Input::get('message');		
		ChatService::sendChatMessage($from_uid, $to_uid, $message);
		return $this->redirect('feedback/chat/list/' . $to_uid);
	}
}