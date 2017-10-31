<?php
namespace modules\v4message\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use modules\v4message\models\Tpl;



class TplController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4message';
	}
	
    public function getList()
	{
		$data = array();
		$data['datalist'] = Tpl::getList();
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
		$tpl = Tpl::getInfo($id);
		$data['tpl'] = $tpl;
		return $this->display('tpl-info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','title','messageType','content');
		$result = Tpl::save($input);
		if($result){
			return $this->redirect('v4message/tpl/list')->with('global_tips','保存成功');
		}else{
			return $this->back('保存失败');
		}

	}
}