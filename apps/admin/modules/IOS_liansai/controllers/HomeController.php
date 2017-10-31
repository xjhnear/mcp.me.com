<?php
namespace modules\liansai\controllers;

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
    public static $resultArr = array(
        '1' => "胜利",
        '2' => "失败",
        '3' => "弃权",
        '4' => "比赛中",
        '5' => "争议",
        '6' => "未知",
    );
    public function _initialize()
    {
        $this->current_module = 'liansai';
    }

    public function getList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $totalPage = 0;
        $search['gameId'] = $data['gameId'] =  Input::get("gameId",'1b6d6f35690349bdb000dad74dfd4a93');
        $search['pageSize'] = $pageSize;
        $input = Input::get();
        $pageIndex = (int)Input::get('page',1);
        $res = AllService::excute("LS",$search,"event/list");
        if($res['success']){
            $data['list'] = $res['data'];
        }
        return $this->display('liansai-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('eventId',"");
        if($id){
            $res = AllService::excute("LS",$input,"event/getEventInfoById");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        $data['eventType'] = array('0'=>"积分");
        $data['gameId'] = Input::get("gameId");
        return $this->display('liansai-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        print_r($input);
        $img = MyHelpLx::save_img($input['pic']);unset($input['pic']);
        $input['eventCoverFile'] =$img ? $img:$input['img'];unset($input['img']);
        $img2 = MyHelpLx::save_img($input['pic2']);unset($input['pic2']);
        $input['eventImgDesc'] =$img ? $img:$input['img2'];unset($input['img2']);
        $input['evenSignOpen'] = Input::get("evenSignOpen")?"true":"false";
        $input['matchSwitch'] = Input::get("matchSwitch")?"true":"false";
        if(Input::get("teamNumLimit")){
            $input['teamNumLimit'] = "0";
        }
        if($id){
            $res= AllService::excute("LS",$input,"event/add",false);
        }else{
            unset($input['id']);
            $res= AllService::excute("LS",$input,"event/add",false);
        }
        print_r($input);
        print_r($res);die;
        if($res['success']){
            return $this->redirect('yxvl_eSports/sports/index','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getTeamList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $totalPage = 0;
        $search = Input::get();
        $search['pageNow'] = (int)Input::get('page',1);
        $search['pageSize'] = $pageSize;
        $search['eventId'] = "7d8d64535a764767b93ada26b18a34c5";
        $res = AllService::excute("LS",$search,"team");
        if($res['success']){
            $data['list'] = $res['data']['teams'];
            $totalPage = $res['data']['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['result'] = array('0'=>"正常",'1'=>"异常");
        $data['search'] = $search;
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage,$pageSize,$search);

        return $this->display('team-list',$data);
    }

    public function getTeamAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('teamId',"");
        $input['eventId'] = "7d8d64535a764767b93ada26b18a34c5";
        if($id){
            $res = AllService::excute("LS",$input,"team/findById");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        return $this->display('team-add',$data);
    }



    public function getMatchList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $totalPage = 0;
        $search['pageNow'] = (int)Input::get('page',1);
        $search['pageSize'] = $pageSize;
        $search['pageSize'] = $pageSize;
        $search['eventId'] = "7d8d64535a764767b93ada26b18a34c5";
        $res = AllService::excute("LS",$search,"match/listByEventId");
        if($res['success']){
            $data['list'] = $res['data'];
            $totalPage = $res['count'];
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage*$pageSize,$pageSize,$search);
        $data['resultArr'] = self::$resultArr;
        return $this->display('match-list',$data);
    }

    public function getGameAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('id',"");
        if($id){
            $res = AllService::excute("LS",$input,"game/add");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        return $this->display('game-add',$data);
    }

    public function postGameAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        $img = MyHelpLx::save_img($input['pic']);unset($input['pic']);
        $input['gameIcon'] =$img ? $img:$input['img'];unset($input['img']);
//        $input['editor'] = $this->current_user['authorname'];
        if($id){
            $res= AllService::excute("LS",$input,"UpdateSaiShi",false);
        }else{
            unset($input['id']);
            $res= AllService::excute("LS",$input,"game/add",false);
        }
        print_r($input);
        print_r($res);die;
        if($res['success']){
            return $this->redirect('yxvl_eSports/sports/index','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getGameList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $totalPage = 0;
        $search['pageNow'] = (int)Input::get('page',1);
        $search['pageSize'] = $pageSize;
        $res = AllService::excute("LS",$search,"game/listForWeb");
        print_r($res);die;
        if($res['success']){
            $data['list'] = $res['data'];
            $totalPage = $res['count'];
        }
        $data['pagelinks'] = MyHelpLx::pager_new(array(),$totalPage*$pageSize,$pageSize,$search);
        $data['resultArr'] = self::$resultArr;
        return $this->display('game-list',$data);
    }



    public function postAjaxDo()
    {
        $data = Input::get();
        $res = AllService::excute("LS",$data,$data['url'],false);
        echo json_encode($res);
    }

}