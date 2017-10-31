<?php
namespace modules\v4system\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;

use Youxiduo\V4\Game\Model\GameRecharge;
use Yxd\Modules\System\SettingService;
use Youxiduo\V4\Game\GameAreaService;
use Youxiduo\V4\Game\Model\IosGame;
use modules\web_forum\controllers\TopicController;

class GameController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4system';
    }

    public function getRechargeList()
    {
        $data = array();
        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $search = array();

        $totalCount = GameRecharge::searchCount($search);
        $data['datalist'] = GameRecharge::searchList($search,$pageIndex,$pageSize);
        $pager = Paginator::make(array(),$totalCount,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $config = SettingService::getConfig('game-recharge-default:url');
        $data['defaulturl'] = isset($config['data'])?$config['data']:"http://www.youxiduo.com";
        $data['pagelinks'] = $pager->links();

        return $this->display('game-recharge-list',$data);
    }

    public function getRechargeEdit($game_id=0)
    {
        $data = array();
        if($game_id){
            $data['setting'] = GameRecharge::getInfo($game_id);
        }
        return $this->display('game-recharge-edit',$data);
    }

    public function postRechargeEdit()
    {
        $game_id = Input::get('game_id');
        $url = Input::get('url');
        $game_name = Input::get('game_name');
        $linkType = Input::get('linkType');
        $isAutoLogin = Input::get('isAutoLogin');
        $result = GameRecharge::SaveInfo($game_id,$game_name,$url,$linkType,$isAutoLogin);
        if($result){
            return $this->redirect('v4system/game/recharge-list');
        }else{
            return $this->back('保存失败');
        }
    }

    public function getRechargeDelete($game_id)
    {
        $result = GameRecharge::DeleteInfo($game_id);
        return $this->back('');
    }

    public function getRechargeDefault()
    {
        $data = array();
        $config = SettingService::getConfig('game-recharge-default:url');
        $data['url'] = $config['data'];
        return $this->display('game-recharge-default',$data);
    }

    public function postRechargeDefault()
    {
        $url = Input::get('url');
        $result = SettingService::setConfig('game-recharge-default:url',$url);
        if($result){
            return $this->redirect('v4system/game/recharge-list');
        }
        return $this->back('保存失败');
    }

    public function getUserAsList()
    {
        $data = array();
        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $search = array('is_open'=>0);
        $search['nickname'] = Input::get('nickname');
        $search['gname'] = Input::get('gname');
        $search['dealType'] = (int)Input::get('dealType',0);
        if($search['dealType']==-1) unset($search['dealType']);
        $totalCount = GameAreaService::searchCount($search);
        $data['datalist'] = GameAreaService::searchList($search,$pageIndex,$pageSize);
        $pager = Paginator::make(array(),$totalCount,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('game-user-as-list',$data);
    }

    public function getDoUserAs($type,$id,$_gid=0)
    {
        if($type=='add'){
            $result = GameAreaService::updateInfo($id,array('is_open'=>1,'dealType'=>1));
            if($result){
                $input = Input::get();
                $input['type'] = '1';
                $input['linkType'] = '2';
                TopicController::system_send($input);

                return $this->redirect('v4system/game/as-list/'.$_gid);
            }
            return $this->back('操作失败');
        }else{
            GameAreaService::updateInfo($id,array('dealType'=>1));
            return $this->back('');
        }
    }

    public function getAsList($_gid=0)
    {
        $data = array();
        $pageIndex = (int)Input::get('page',1);
        $game_id = Input::get('game_id',$_gid);
        $pageSize = 10;
        $search = array('is_open'=>1);
        if($game_id) $search['game_id'] = $game_id;
        $totalCount = GameAreaService::searchCount($search);
        $data['datalist'] = GameAreaService::searchList($search,$pageIndex,$pageSize);
        $pager = Paginator::make(array(),$totalCount,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('game-as-list',$data);
    }

    public function getAsAdd($game_id=0)
    {
        $data = array();
        $data['channels'] = GameAreaService::getGameChannelList(true);
        if($game_id){
            $game = IosGame::getInfoById($game_id);
            if($game){
                $data['game'] = $game;
            }
        }
        return $this->display('game-as-add',$data);
    }

    public function postAsAdd()
    {
        $optype = (int)Input::get('optype');
        $type = Input::get('type');
        $game_id = Input::get('game_id');
        $gname = Input::get('game_name');
        $area_name = Input::get('area_name');
        $server_name = Input::get('server_name');
        $start_area = (int)Input::get('start_area');
        $end_area = (int)Input::get('end_area');
        $start_server = (int)Input::get('start_server');
        $end_server = (int)Input::get('end_server');
        $datatype = (int)Input::get('datatype');
        //var_dump(Input::all());exit;
        $channels = GameAreaService::getGameChannelList(true);
        $typename = isset($channels[$type]) ? $channels[$type] : '';
        if($optype==1){
            $data = array();
            foreach($area_name as $key=>$value){
                if(!$value) continue;
                $data[] = array('type'=>$type,'typename'=>$typename,'game_id'=>$game_id,'gname'=>$gname,'area_name'=>$value,'server_name'=>$server_name[$key],'is_open'=>1);
            }
            if($data){
                if($datatype==2) GameAreaService::clearGameAreaServer($game_id);
                $result = GameAreaService::addBatchInfo($data);
                if($result) return $this->redirect('v4system/game/as-list/'.$game_id);
            }
        }elseif($optype==2){
            if($start_area > $end_area) return $this->back('开始区不能大于结束区');
            if($start_server > $end_server) return $this->back('开始服不能大于结束服');
            $data = array();
            for($i = $start_area;$i <= $end_area;$i++){
                for($k = $start_server;$k <= $end_server;$k++){
                    $data[] = array(
                        'type'=>$type,
                        'typename'=>$typename,
                        'game_id'=>$game_id,
                        'gname'=>$gname,
                        'area_name'=>$i.'区',
                        'server_name'=> $k > 0 ? $k . '服' : '',
                        'is_open'=>1
                    );
                }
            }
            //print_r($data);exit;
            if($data){
                if($datatype==2) GameAreaService::clearGameAreaServer($game_id);
                $result = GameAreaService::addBatchInfo($data);
                if($result) return $this->redirect('v4system/game/as-list/'.$game_id);
            }
        }
        return $this->back('添加失败');
    }

    public function getDoAsDelete($id)
    {
        $result = GameAreaService::deleteGameArea($id);
        return $this->back('删除成功');
    }

    public function getChannelList()
    {
        $data = array();
        $data['datalist'] = GameAreaService::getGameChannelList(false);
        return $this->display('game-channel-list',$data);
    }

    public function getDoChannel($op,$id=0)
    {
        if($op=='add'){

            return $this->display('game-channel-edit');
        }elseif($op=='edit'){
            $data = array();
            $data['channel'] = GameAreaService::getGameChannelInfo($id);
            return $this->display('game-channel-edit',$data);
        }elseif($op=='delete'){
            GameAreaService::deleteGameChannel($id);
            return $this->back('删除成功');
        }
        return $this->back();
    }

    public function postDoChannel()
    {
        $channel_id = Input::get('channel_id');
        $channel_name = Input::get('channel_name');

        $result = false;
        if($channel_id){
            $result = GameAreaService::updateGameChannel($channel_id,$channel_name);
        }else{
            $result = GameAreaService::addGameChannel($channel_name);
        }

        if($result){
            return $this->redirect('v4system/game/channel-list');
        }
        return $this->back('保存失败');
    }

}