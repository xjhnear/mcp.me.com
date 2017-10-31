<?php
namespace modules\system\controllers;

use modules\system\models\NoticeModel;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;



class NoticeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		$data = array();
		$data['datalist'] = NoticeModel::getList();
		return $this->display('notice-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['setting'] = NoticeModel::getInfo($id);
		}		
		return $this->display('notice-edit',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('id','app','appinfo','module','send_email','send_message');
		NoticeModel::save($input);
		return $this->redirect('system/notice/index')->with('global_tips','操作成功');
	}
}