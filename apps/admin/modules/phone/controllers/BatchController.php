<?php
namespace modules\phone\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\Phone\Model\PhoneBatch;

class BatchController extends BackendController
{
	public function _initialize(){
		$this->current_module = 'phone';
	}
	
	public function getList()
	{
		$pageIndex = Input::get('page',1);
		$search = Input::only('batch_code');
		$pageSize = 10;
		$data = array();
		$data['datalist'] = PhoneBatch::getList($search,$pageIndex,$pageSize);
		$data['search'] = $search;
		$total = PhoneBatch::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		return $this->display('batch_list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		return $this->display('batch_info',$data);
	}
	
	public function getEdit($batch_id)
	{
		$data = array();
		$data['info'] = PhoneBatch::getInfo($batch_id);
		return $this->display('batch_info',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('batch_id','batch_code');

		$result = PhoneBatch::save($input);
		if($result){
			return $this->redirect('phone/batch/list','批次保存成功');
		}else{
			return $this->back('批次保存成功');
		}
	}

	public function getDel($batch_id=0)
	{
		if($batch_id){
			PhoneBatch::del($batch_id);
		}
		return $this->redirect('phone/batch/list','批次删除成功');
	}

}