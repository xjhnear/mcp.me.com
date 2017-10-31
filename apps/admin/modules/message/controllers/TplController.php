<?php
namespace modules\message\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use modules\message\models\TplModel;



class TplController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'message';
	}
	
    public function getList()
	{
		$data = array();
		$data['datalist'] = TplModel::getList();
		$data['pushType'] = TplModel::$AUTO_NOTICE_TYPES;
		return $this->display('tpl-list',$data);
	}
	
    public function getAdd()
	{
		$data = array();
		$data['pushType'] = TplModel::getNotExistsKeys();
		return $this->display('tpl-info',$data);
	}
	
    public function getEdit($id)
	{
		$data = array();
		$data['tpl'] = TplModel::getInfo($id);
		$data['pushType'] = TplModel::$AUTO_NOTICE_TYPES;
		$varlist = TplModel::$AUTO_NOTICE_TPL_VARS[$data['tpl']['ename']];
		$data['varlist'] = $varlist;
		return $this->display('tpl-info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','ename','content');
		TplModel::save($input);
		return $this->redirect('message/tpl/list')->with('global_tips','保存成功');
	}
}