<?php
namespace modules\zhuanqu\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\a_yiyuan\controllers\HelpController;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'zhuanqu';
    }

    public function getList()
    {
        $data = $search = $input = array();
        $search = Input::get();
        $search['pageSize'] = 10;
        $search['pageNow'] = (int)Input::get('page',1);
        $res = AllService::excute2("8338",$search,"search/search_ByGroup");
        $data['search'] = $search;
        if($res['success']){
            $data['list'] = $res['data'];
            $total = $res['count'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(),$total,$search['pageSize'],$search);
        }
        return $this->display('list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('id',"");
        if($id){
            $res = AllService::excute2("8338",$input,"search/search_ById");
            if($res['data']){
                isset($res['data']['data']) && $res['data']['data']=json_decode($res['data']['data'],true);
                $data['data'] = $res['data'];
            }
        }
//        print_r($data);
        $data['eventType'] = array('0'=>"积分");
        return $this->display('add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
//        print_r($input);
        $arr = array();
        foreach(Input::get('tag',array()) as $k=>$v){
            $arr[$k]['tag'] = $v;
            $arr[$k]['size'] = $input['size'][$k];
            $arr[$k]['map'] = $input['map'][$k];
            $arr[$k]['type'] = $input['type'][$k];
        }
        $input['data'] = json_encode($arr);
        if($id){
            $res= AllService::excute("8338",$input,"update/update_model",false);
        }else{
            unset($input['id']);
            $res= AllService::excute("8338",$input,"add/add_model",false);
        }
        if($res['success']){
            return $this->redirect('zhuanqu/home/list','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }
}