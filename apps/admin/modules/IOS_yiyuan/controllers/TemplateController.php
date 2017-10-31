<?php
namespace modules\IOS_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\IOS_yiyuan\controllers\HelpController;


class TemplateController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'IOS_yiyuan';
    }

    public function getList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $search['pageSize'] = $pageSize;
        $pageIndex = (int)Input::get('page',1);
        $search['offset'] = ($pageIndex-1)*$pageSize;
        $search['channelId'] = HelpController::$channelId;
        $res_moren = AllService::excute2("8089",$search,"luckyDraw/QueryDefaultReceiveTemplate");
        if($res_moren['success']){
            $moren = json_decode($res_moren['data'],true);
            $data['moren'] = $moren;
        }
        print_r($data);
        $res = AllService::excute2("8089",$search,"luckyDraw/QueryReceiveTemplateForPage");
        if($res['success']){
//            $list = json_decode($res['data'],true);
            $data['list'] = $res['data']['list'];
            $total = $res['data']['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(),$total,$search['pageSize'],$search);
        }
        return $this->display('template-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('templateId',"");
        $input['channelId'] = HelpController::$channelId;
        if($id){
            $res = AllService::excute2("8089",$input,"luckyDraw/QueryReceiveTemplateDetail");
            if($res['data']){
                $data['data'] = $res['data'];
                if(isset($data['data']['templateAttribute'])){
                    $data['data']['templateAttribute'] = json_decode($data['data']['templateAttribute'],true);
                }

            }

        }
        return $this->display('template-form',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        $arr = array();
        foreach(Input::get('title',array()) as $k=>$v){
            $arr[$k]['title'] = $v;
            $arr[$k]['isnull'] = $input['isnull'][$k]?"1":"0";
            $arr[$k]['is_encryption'] = $input['is_encryption'][$k];
            $arr[$k]['order'] = $input['order'][$k];
        }
        $input['templateAttr'] = json_encode($arr);
        $input['channelId'] = HelpController::$channelId;
        if($id){
            $res= AllService::excute2("8089",$input,"luckyDraw/CreateReceiveTemplate",false);
        }else{
            unset($input['id']);
            $res= AllService::excute2("8089",$input,"luckyDraw/CreateReceiveTemplate",false);
        }
        if($res['success']){
            return $this->redirect('IOS_yiyuan/template/list','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }


}