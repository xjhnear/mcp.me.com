<?php
namespace modules\v4message\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class FilterController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4message';
	}
	
    public function getList()
	{
		$data = array();
        $input = Input::get();
        $total = 0;
        $pageSize = 10;
        $input['pageIndex'] = (int)Input::get('page',1);
        $input['pageSize'] = $pageSize;
		$res = AllService::excute('28888',$input,"get_key_filter_list");
        if($res['success']){

            $data['list'] = $res['data'];
            $total = $res['count'];
        }
        $data['pagelinks'] = MyHelpLx::pager_new(array(),$total,$pageSize,$input);
		return $this->display('filter/tpl-list',$data);
	}
	
    public function getSave()
	{
		$data = array();
        $id = Input::get("id","");
        if($id){
            $res = AllService::excute('28888',array('id'=>$id),"get_key_filter_list");
            if(isset($res['data'][0])){
                $data['data'] = $res['data'][0];
            }
        }
		return $this->display('filter/tpl-info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','content');
        if($input['id']){
            $result = AllService::excute('28888',$input,"save_update_key_filter");
        }else{
            unset($input['id']);
            $result = AllService::excute('28888',$input,"save_update_key_filter");
        }

		if($result['success']){
			return $this->redirect('v4message/filter/list')->with('global_tips','保存成功');
		}else{
			return $this->back($result['error']);
		}

	}
}