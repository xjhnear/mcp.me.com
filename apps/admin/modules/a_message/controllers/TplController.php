<?php
namespace modules\a_message\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Message\Model\MessageTpl;



class TplController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_message';
	}
	
    public function getList()
	{
		$data = array();
		$data['datalist'] = MessageTpl::getList();
		return $this->display('tpl-list',$data);
	}
	
    public function getAdd()
	{
		$data = array();
		return $this->display('tpl-info',$data);
	}
	
    public function getEdit($id)
	{
		$data = array();
		$tpl = MessageTpl::getInfo($id);
		$data['tpl'] = $tpl;
		$varlist = !empty($tpl['var_json']) ? json_decode($tpl['var_json'],true) : array();
		$data['varlist'] = $varlist;
		return $this->display('tpl-info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','ename','name','content','var_json');
		MessageTpl::save($input);
		return $this->redirect('a_message/tpl/list')->with('global_tips','保存成功');
	}
}