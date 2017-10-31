<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\ESports\ESportsService;


class CategoryController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'yxvl_eSports';
	}
	
	public function getIndex()
	{
		$data = array('datalist'=>array());
        $channel = Input::get("channel","Article");
		$catalogs = ESportsService::excute(array(),"Get".$channel."Catalogs",true);
        if($catalogs['success']){
            $data['datalist'] = $catalogs['data'];
        }
        $data['channel'] = $channel;
		return $this->display('category-list',$data);
	}
	
	public function getAdd()
	{
        $data = Input::all();
		return $this->display('category-add',$data);
	}

    public function postAdd(){
        $data = Input::get();unset($data['id']);
        $channel = Input::get("channel","Article");unset($data['channel']);
        if(Input::get('id')){
            $res = ESportsService::excute($data,"Update".$channel."Catalog",false);
        }else{
//            unset($data['idx']);
            $res = ESportsService::excute($data,"Create".$channel."Catalog",false);
        }
        if($res['success']){
            return $this->redirect('yxvl_eSports/category/index?channel='.$channel,'添加分类成功');
        }else{
            return $this->back('添加文章失败');
        }
    }

    public function getEdit()
	{
		return $this->display('category-edit');
	}

    public function postAjaxExcute(){
        $data = Input::get();
        $res = ESportsService::excute($data,"UpdateArticleCataLog",false);
        echo json_encode($res);
    }
}