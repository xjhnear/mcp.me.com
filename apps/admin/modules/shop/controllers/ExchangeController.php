<?php
namespace modules\shop\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;

use modules\shop\models\ExchangeModel;
use Illuminate\Support\Facades\Response;

class ExchangeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'shop';
	}
	
	public function getList($goods_id=0)
	{
		$search = array();
		if($goods_id){
			$search['goods_id'] = $goods_id;
		}
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$result = ExchangeModel::search($search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['list'];
		return $this->display('exchange-list',$data);
	}
	
	 public function getChangeStatus(){
	 	$id = Input::get('id');
	 	$status = Input::get('status');
	 	if(empty($id)) {
	 		Response::json(array('status'=>0,'msg'=>'数据错误，请重试！'));
	 	}
	 	if(ExchangeModel::update($id,array('status'=>$status))){
	 		return Response::json(array('status'=>1,'msg'=>'更新成功！'));
	 	}else{
	 		return Response::json(array('status'=>0,'msg'=>'更新失败，请重试！'));
	 	}
	 }
}