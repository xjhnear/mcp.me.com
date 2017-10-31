<?php
namespace modules\system\controllers;

use modules\system\models\SystemSettingModel;

use modules\forum\models\TopicModel;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;
use modules\system\models\CreditModel;

class CreditController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		$data['creditlist'] = CreditModel::getList();
		$data['crcletype'] = array('0'=>'永久','1'=>'每日','2'=>'每周','3'=>'每月');
		return $this->display('credit-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['credit'] = CreditModel::getInfo($id);
		}else{
		}
		return $this->display('credit-edit',$data);
	}
	
	public function postSave()
	{
		$data = Input::only('id','name','alias','type','crcletype','rewardnum','info','score','experience');
		$result = CreditModel::save($data);
		if($result){
			$tips = '操作成功';
		}else{
			$tips = '操作失败';
		}
		return $this->back()->with('global_tips',$tips);
	}
	
	public function getRule()
	{
		$data = array();		
		$data['topic'] = CreditModel::getRuleInfo();
		return $this->display('credit-rule',$data);
	}
	
	public function postSaveRule()
	{
		$tid = (int)Input::get('tid');
		$subject = Input::get('subject');
		$message = Input::get('format_message','');
		$uid = 1;
		$res = CreditModel::saveRule($tid, $subject, $message, $uid);
		if($res){
			return $this->redirect('system/credit/rule')->with('global_tips','积分规则保存成功');
		}else{
			return $this->back()->with('global_tips','积分规则保存失败');
		}
	}
}