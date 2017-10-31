<?php
namespace modules\system\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;
use modules\system\models\GradeModel;


class GradeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		$data = array();
		$data['datalist'] = GradeModel::getList();
		return $this->display('grade-list',$data);
	}
	
	public function getEdit($id)
	{
	    $data = array();
	    if($id){
		    $data['level'] = GradeModel::getInfo($id);
		}
		return $this->display('grade-edit',$data);
	}
	
	public function postSave()
	{
		$data = Input::only('id','name','img','start','end');
		$result = GradeModel::save($data);
		if($result){
			$tips = '操作成功';
		}else{
			$tips = '操作失败';
		}
		return $this->back()->with('global_tips',$tips);
	}
}