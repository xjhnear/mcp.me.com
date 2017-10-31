<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\ESports\ESportsService;


class VltvLiveController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'yxvl_eSports';
	}

    public function getIndex()
    {
        $data = array();
        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $totalPage = 1;
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex);
        $res = ESportsService::excute($search,"GetLiveInfoList",true);
        if($res['data']&&isset($res['data']['list'])&&isset($res['data']['totalPage'])){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
//        print_r($res);
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage,$pageSize,$search);
        return $this->display('vltv-live-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $id = Input::get('id',"");
        if($id){
            $res = ESportsService::excute(array('id'=>$id),"GetLiveInfo",true);
//            print_r($res);
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        return $this->display('vltv-live-add',$data);
    }

    public function postSave()
    {
        $id = Input::get("id");
        $input = Input::all();
        switch($input['type']){
            case "1":
                if($input['picUrl']){
                    $img = MyHelpLx::save_img($input['picUrl']);
                }else{
                    $img = $input['img'];
                }
                unset($input['picUrl']);unset($input['img']);
                $input['imgMax'] = $img;
                break;
            case "2":
                break;
            case "3":
                break;
            default:
        }

        if($input['picUrl2']){
            $img2 = MyHelpLx::save_img($input['picUrl2'],true);
        }else{
            $img2 = $input['img2'];
        }
        unset($input['picUrl2']);unset($input['img2']);
        $input['shareImg'] = $img2;

        if($input['list_img_file']){
            $list_img = MyHelpLx::save_img($input['list_img_file'],true);
        }else{
            $list_img = $input['list_img'];
        }
        unset($input['list_img_file']);unset($input['list_img']);
        $input['imgUrl'] = $list_img;



        if($input['startTime']){
            $input['startTime'] = strtotime($input['startTime']);
        }
        if($input['endTime']){
            $input['endTime'] = strtotime($input['endTime']);
        }
        if(isset($input['enable'])&&$input['enable']=="on"){
            $input['enable'] = "1";
        }else{
            $input['enable'] = "0";
        }
        if($id){
            $res= ESportsService::excute2($input,"UpdateLiveInfo",false);
        }else{
            unset($input['id']);
            $res= ESportsService::excute2($input,"CreateLiveInfo",false);
        }
//        print_r($input);
//        print_r($res);die;
        if($res['success']){
            return $this->redirect('yxvl_eSports/VltvLive/index','保存成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getLive()
    {
        $data = array();
        $liveId = Input::get('liveId',1);
        $data['liveId'] = $liveId;

        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $totalPage = 1;
        $search = array('liveId'=>$liveId,'pageSize'=>$pageSize,'pageIndex'=>$pageIndex);
        $res = ESportsService::excute($search,"GetLiveGraphicalList",true);
        if($res['data']&&isset($res['data']['list'])&&isset($res['data']['totalPage'])){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
//        print_r($search);
//        print_r($res);
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage,$pageSize,$search);
        return $this->display('vltv-live-live-list',$data);
    }

    public function getLiveAdd()
    {
        $data = array();
        $input = Input::all();
        $liveId = Input::get("liveId");
        $data['liveId'] = $liveId;
        $id = Input::get('id',"");

        if($id){
            $res = ESportsService::excute(array('id'=>$id),"GetLiveGraphical",true);
//            print_r($res);
            if($res['data']){
                $data['data'] = $res['data'];
                $data['imgs'] = isset($res['data']['imgUrl']) ? explode(',',$res['data']['imgUrl']) : array();
                $data['smallImgs'] = isset($res['data']['smallImgUrl']) ? explode(',',$res['data']['smallImgUrl']) : array();
            }
        }

        return $this->display('vltv-live-live-add',$data);
    }

    public function postLiveSave()
    {
        $id = Input::get("id");
        $input = Input::all();

        $img_arr = array();
        $input['imgUrl_0'] = null;
        $input['imgUrl_1'] = null;
        $input['imgUrl_2'] = null;
        $input['smallImgUrl_0'] = null;
        $input['smallImgUrl_1'] =null;
        $input['smallImgUrl_2'] = null;

        if(isset($input['picFile'])){
            foreach($input['picFile'] as $k=>$v){
                if(empty($v)){
                    $img_arr[] = $input['img'][$k];
                }else{
                    $img_arr[] = MyHelpLx::save_img($v);
                }
            }
            unset($input['picFile']);
            unset($input['img']);
//        $img_arr = MyHelpLx::save_imgs($input['picFile']);unset($input['picFile']);
        }

        //$input['imgUrl'] = implode(',',$img_arr);
        isset($img_arr[0]) && $input['imgUrl_0'] = $img_arr[0];
        isset($img_arr[2]) && $input['imgUrl_1'] = $img_arr[2];
        isset($img_arr[4]) && $input['imgUrl_2'] = $img_arr[4];
        isset($img_arr[1]) && $input['smallImgUrl_0'] = $img_arr[1];
        isset($img_arr[3]) && $input['smallImgUrl_1'] = $img_arr[3];
        isset($img_arr[5]) && $input['smallImgUrl_2'] = $img_arr[5];

        if($id){
            $res= ESportsService::excute2(array_filter($input),"UpdateLiveGraphical",false);
        }else{
            unset($input['id']);
            $res= ESportsService::excute2(array_filter($input),"CreateLiveGraphical",false);
        }
        print_r(array_filter($input));
        print_r($res);die;
        if($res['success']){
            return $this->redirect('yxvl_eSports/VltvLive/live?liveId='.$input['liveId'],'保存成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getUser()
    {
        $data = array();

        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $totalPage = 0;
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex);
        $res = ESportsService::excute($search,"GetAppAccountList",true);
        if($res['data']&&isset($res['data']['list'])&&isset($res['data']['totalPage'])){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
//        print_r($search);
//        print_r($res);
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage,$pageSize,$search);
        return $this->display('vltv-live-user-list',$data);
    }

    public function getUserAdd()
    {
        $data = array();
        $input = Input::all();
        $id = Input::get('accountId',"");

        if($id){
            $res = ESportsService::excute(array('accountId'=>$id),"GetAppAccount",true);
            if($res['data']){
                $data['data'] = $res['data'];
            }
//            print_r($res);
        }

        return $this->display('vltv-live-user-add',$data);
    }

    public function postUserSave()
    {
        $id = Input::get("accountId");
        $input = Input::all();

        if(isset($input['status'])&&$input['status']=="on"){
            $input['status'] = "0";
        }else{
            $input['status'] = "1";
        }
        if(!$input['password']){
            unset($input['password']);
        }
        if($id){
            $res= ESportsService::excute2($input,"UpdateAppAccount",false);
        }else{
            unset($input['accountId']);
            $res= ESportsService::excute2($input,"CreateAppAccount",false);
        }
//        print_r($input);
//        print_r($res);die;
        if($res['success']){
            return $this->redirect('yxvl_eSports/VltvLive/user','保存成功');
        }else{
            return $this->back($res['error']);
        }
    }



    public function postAjaxUpdate()
    {
        $data = Input::all();
        $res = ESportsService::excute2($data,$data['api'],false);
//        print_r($data);
//        print_r($res);die;
        echo json_encode($res);
    }

    public function postAjaxDel()
    {
        $data = Input::all();
        $res = ESportsService::excute($data,$data['api'],false);
//        print_r($data);print_r($res);die;
        echo json_encode($res);
    }
}