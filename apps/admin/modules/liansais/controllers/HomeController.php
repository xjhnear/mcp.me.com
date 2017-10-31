<?php
namespace modules\liansais\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'liansais';
    }

    public function getList()
    {

        $data = $search = $input = array();
        $input =Input::get();
        $pageSize = 10;
        $totalPage = 0;
        $search['pageSize'] = $pageSize;
        $pageIndex = Input::get('page',1);
        $search['pageIndex']=$pageIndex;
        $res = AllService::excute("8484", $search, "activity/QueryGame");
        if ($res['success']) {
            $data['list'] = $res['data']['list'];
            $totalPage=$res['data']['totalPage'];
        }
        $data['pagelinks'] = MyHelpLx::pager_new(array(),$totalPage*$pageSize,$pageSize,$search);
        return $this->display('liansai-list', $data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('id', "");

        if ($id) {
            $res = AllService::excute("8484", $input, "activity/QueryGame");
            if ($res['data']) {
                $data['data'] = $res['data']['list'][0];
                $data['data']['downloadUrl'] = json_decode($res['data']['list'][0]['downloadUrl'], true);


            }
        }
        return $this->display('liansai-add', $data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        unset($input['gameId']);
        $data = array();
        $input['iOS'] = $input['iOS_downloadUrl'];unset($input['iOS_downloadUrl']);
        $input['android'] = $input['android_downloadUrl'];unset($input['android_downloadUrl']);
        //print_r($data);
//        $input['downloadUrl'] = json_encode($data);
//        $input['downloadUrl'] = str_replace( "\\","",json_encode($data));
        $img = MyHelpLx::save_img($input['pic']);
        unset($input['pic']);
        $input['icon'] = $img ? $img : $input['img'];
        unset($input['img']);
        // $img2 = MyHelpLx::save_img($input['pic2']);unset($input['pic2']);
        //$input['eventImgDesc'] =$img ? $img:$input['img2'];unset($input['img2']);
        if ($id) {
            $res = AllService::excute2("8484", $input, 'activity/UpdateGame', false);
        } else {
            unset($input['id']);
            $res = AllService::excute2("8484", $input, 'activity/CreateGame', false);
        }
        if ($res['success']) {
            return $this->redirect('liansais/home/list', '保存成功');
        } else {
            return $this->back($res['error']);
        }
    }

    public function postDel()
    {
        $input= Input::get();
        $res = AllService::excute2("8484", $input, 'activity/RemoveGame', false);
        return json_encode($res);
    }
    public function postDo()
    {
        $data = Input::get();
        //print_r($data);
        $res = AllService::excute2("8484",$data,'activity/UpdateGame',false);
        return json_encode($res);
    }
}