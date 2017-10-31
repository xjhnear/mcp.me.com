<?php
namespace modules\wxshare\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Share\GiftbagService;
use Youxiduo\Activity\Share\GoodsService;
use Youxiduo\Activity\Share\RechargeService;

class GoodsController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'wxshare';
	}
	
    public function getList()
	{
		$search = array();
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = GoodsService::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		return $this->display('goods-list',$data);
	}
	
	public function getEdit($goods_id=0)
	{
		$data = array();
		if($goods_id){
			$data['goods'] = GoodsService::getInfo($goods_id);
		}
		return $this->display('goods-info',$data);
	}
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['summary'] = Input::get('summary');
		$input['price'] = (int)Input::get('price',0);
		$input['total_num'] = (int)Input::get('total_num',0);
		$input['sort'] = (int)Input::get('sort',50);
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('litpic')){	    	
			$file = Input::file('litpic');			
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();		
			$file->move($path,$new_filename . '.' . $mime );
			$input['litpic'] = $dir . $new_filename . '.' . $mime;
		}
		$id = GoodsService::saveInfo($input);
	    if($id){
			return $this->redirect('wxshare/goods/list')->with('global_tips','礼包保存成功');
		}else{
			return $this->back('保存失败');
		}
	}
}