<?php
namespace modules\liansai\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
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
        $search['gameId'] = $data['gameId'] =  Input::get("gameId",'');
        $search['pageSize'] = $pageSize;
        $input = Input::get();
        $pageIndex = (int)Input::get('page',1);
        $res = AllService::excute("LS",$search,"event/list");
        if($res['success']){
            $data['list'] = $res['data'];
        }
        $data['eventType'] = array('0'=>'积分','1'=>'淘汰');
        $data['matchTypeStr'] = array('0'=>"团体",'1'=>'个人');
        return $this->display('liansai-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('eventId',"");
        $data['gameId'] = Input::get("gameId");
        if($id){
            $res = AllService::excute("LS",$input,"event/getEventInfoById");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        $data['eventType'] = array(
            '0'=>'积分'
//           ,'1'=>'淘汰'
        );
        $data['matchTypeStr'] = array('0'=>"团体",'1'=>'个人');

        return $this->display('liansai-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("eventId");
        $input = Input::all();
        $img = MyHelpLx::save_img($input['pic']);unset($input['pic']);
        $input['eventCoverFile'] =$img ? $img:$input['img'];unset($input['img']);
        $img = MyHelpLx::save_img($input['pic2']);unset($input['pic2']);
        $input['eventImgDesc'] =$img ? $img:$input['img2'];unset($input['img2']);
        $img = MyHelpLx::save_img($input['userCenter_pic']);unset($input['userCenter_pic']);
        $input['userCenter'] =$img ? $img:$input['userCenter_img'];unset($input['userCenter_img']);
        $img = MyHelpLx::save_img($input['myTeam_pic']);unset($input['myTeam_pic']);
        $input['myTeam'] =$img ? $img:$input['myTeam_img'];unset($input['myTeam_img']);
        $img = MyHelpLx::save_img($input['allTeam_pic']);unset($input['allTeam_pic']);
        $input['allTeam'] =$img ? $img:$input['allTeam_img'];unset($input['allTeam_img']);
        $img = MyHelpLx::save_img($input['rankTeam_pic']);unset($input['rankTeam_pic']);
        $input['rankTeam'] =$img ? $img:$input['rankTeam_img'];unset($input['rankTeam_img']);
        $img = MyHelpLx::save_img($input['eventInfo_pic']);unset($input['eventInfo_pic']);
        $input['eventInfo'] =$img ? $img:$input['eventInfo_img'];unset($input['eventInfo_img']);
        $img = MyHelpLx::save_img($input['joinEvent_pic']);unset($input['joinEvent_pic']);
        $input['joinEvent'] =$img ? $img:$input['joinEvent_img'];unset($input['joinEvent_img']);
        $img = MyHelpLx::save_img($input['inviteFriend_pic']);unset($input['inviteFriend_pic']);
        $input['inviteFriend'] =$img ? $img:$input['inviteFriend_img'];unset($input['inviteFriend_img']);
        $img = MyHelpLx::save_img($input['beginMatch_pic']);unset($input['beginMatch_pic']);
        $input['beginMatch'] =$img ? $img:$input['beginMatch_img'];unset($input['beginMatch_img']);
        $img = MyHelpLx::save_img($input['eventDetail_pic']);unset($input['eventDetail_pic']);
        $input['eventDetail'] =$img ? $img:$input['eventDetail_img'];unset($input['eventDetail_img']);
        $img = MyHelpLx::save_img($input['cancelMatch_pic']);unset($input['cancelMatch_pic']);
        $input['cancelMatch'] =$img ? $img:$input['cancelMatch_img'];unset($input['cancelMatch_img']);
        $img = MyHelpLx::save_img($input['joinTeam_pic']);unset($input['joinTeam_pic']);
        $input['joinTeam'] =$img ? $img:$input['joinTeam_img'];unset($input['joinTeam_img']);
        $img = MyHelpLx::save_img($input['sharePage_pic']);unset($input['sharePage_pic']);
        $input['sharePage'] =$img ? $img:$input['sharePage_img'];unset($input['sharePage_img']);

        $img = MyHelpLx::save_img($input['victoryBtn_pic']);unset($input['victoryBtn_pic']);
        $input['victoryBtn'] =$img ? $img:$input['victoryBtn_img'];unset($input['victoryBtn_img']);
        $img = MyHelpLx::save_img($input['defeatBtn_pic']);unset($input['defeatBtn_pic']);
        $input['defeatBtn'] =$img ? $img:$input['defeatBtn_img'];unset($input['defeatBtn_img']);
        $img = MyHelpLx::save_img($input['myTeam2Btn_pic']);unset($input['myTeam2Btn_pic']);
        $input['myTeam2Btn'] =$img ? $img:$input['myTeam2Btn_img'];unset($input['myTeam2Btn_img']);
        $img = MyHelpLx::save_img($input['inviteFriend2Btn_pic']);unset($input['inviteFriend2Btn_pic']);
        $input['inviteFriend2Btn'] =$img ? $img:$input['inviteFriend2Btn_img'];unset($input['inviteFriend2Btn_img']);
        $img = MyHelpLx::save_img($input['joinSuccessBack_pic']);unset($input['joinSuccessBack_pic']);
        $input['joinSuccessBack'] =$img ? $img:$input['joinSuccessBack_img'];unset($input['joinSuccessBack_img']);

        $input['evenSignOpen'] = Input::get("evenSignOpen")?"true":"false";
        $input['matchSwitch'] = Input::get("matchSwitch")?"true":"false";
        if(Input::get("teamNumLimit")){
            $input['teamNumLimit'] = "0";
        }else{
            $input['teamNumLimit'] = "1";
        }
        if($id){
            $res= AllService::excute("LS",$input,"event/update",false);
        }else{
            unset($input['id']);
            $res= AllService::excute("LS",$input,"event/add",false);
        }
        if($res['success']){
            return $this->redirect('liansai/home/list?gameId='.$input['gameId'],'添加成功');
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
        $search['eventId'] = Input::get("eventId");
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
        $input['eventId'] = Input::get("eventId");
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
        $search['eventId'] = Input::get("eventId");
        $res = AllService::excute("LS",$search,"match/listByEventId");
        if($res['success']){
            $data['list'] = $res['data']['matchs'];
            $totalPage = $res['data']['count'];
        }
        $data['pagelinks'] = MyHelpLx::pager_new(array(),$totalPage,$pageSize,$search);
        $data['resultArr'] = self::$resultArr;
        return $this->display('match-list',$data);
    }

    public function getGameAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('gameId',"");
        if($id){
            $res = AllService::excute("LS",$input,"game/findById");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        return $this->display('game-add',$data);
    }

    public function postGameAdd()
    {
        $id = Input::get("gameId");
        $input = Input::all();
        $img = MyHelpLx::save_img($input['pic']);unset($input['pic']);
        $input['gameIcon'] =$img ? $img:$input['img'];unset($input['img']);
//        $input['editor'] = $this->current_user['authorname'];
        if($id){
            $res= AllService::excute("LS",$input,"game/updateInfo",false);
        }else{
            unset($input['gameId']);
            $res= AllService::excute("LS",$input,"game/add",false);
        }
        if($res['success']){
            return $this->redirect('liansai/home/game-list','保存成功');
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
        $search['platform'] = Input::get('platform');
        if(!$search['platform']){
            $search['platform'] = Session::get('platform','both');
        }

        Session::put('platform',$search['platform']);
        $res = AllService::excute("LS",$search,"game/listForWeb");
        if($res['success']){
            $data['list'] = $res['data'];
            $totalPage = $res['count'];
        }
        $data['pagelinks'] = MyHelpLx::pager_new(array(),$totalPage*$pageSize,$pageSize,$search);
        $data['resultArr'] = self::$resultArr;
        $data['search'] = $search;
        return $this->display('game-list',$data);
    }



    public function postAjaxDo()
    {
        $data = Input::get();
        $url = $data['url'];unset($data['url']);
        $res = AllService::excute("LS",$data,$url,false);
        echo json_encode($res);
    }

}