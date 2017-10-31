<?php
namespace modules\tianyu\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\tianyu\controllers\HelpController;


class GameController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'tianyu';
    }

    public function getList()
    {
        $data = $search = array();
        $total = '';
        $pageSize = 10;
        $search = Input::get();
        $search['pageSize'] = $pageSize;
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex - 1) * 10;
        $res = AllService::excute2("8338", $search, "tianyu_lottery/search/search_games");
        $data['list'] = array();
        if ($res['success']) {
            $data['list'] = $res['data'];
//            $total = $res['totleCount'];
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        return $this->display('game-list', $data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $input['id'] = Input::get('id');
        if($input['id']){
            $res = AllService::excute2("8338",$input,"tianyu_lottery/search/search_games");
            if (!$res['success']) return $this->back()->with('global_tips','详情接口错误，请重试或联系开发人员');
            if(!empty($res['data'])){
                $data['data'] = $res['data'][0];
            }
        }
        return $this->display('game-add',$data);
    }

    public function postAdd()
    {
        $input = Input::all();
        $input['priceId']           = Input::get("priceId");
        $input['type']               = Input::get('type');
        $input['id']                 = Input::get('id');
        $input['num']                = Input::get('num');
        $input['gameTitle']          = Input::get('gameTitle');
        $input['gameDescribe']       = Input::get('gameDescribe');
        if(Input::file('gamePic')){
            $input['gamePic'] = Input::file('gamePic');
            if($input['gamePic']){
                $input['gamePic'] = MyHelpLx::save_img($input['gamePic']);
            }
        }
        if(Input::file('giftPic')){
            $input['giftPic'] = Input::file('giftPic');
            if($input['giftPic']){
                $input['giftPic'] = MyHelpLx::save_img($input['giftPic']);
            }
        }
        if($input['id']){
            $res= AllService::excute2("8338",$input,"tianyu_lottery/update/update_game",false);
        }else{
            unset($input['id']);
            $res= AllService::excute2("8338",$input,"tianyu_lottery/add/add_gamePrice",false);
        }
        if($res['success']){
            if ($input['id']) {
                return $this->redirect('tianyu/game/list','修改成功');
            } else {
                return $this->redirect('tianyu/game/list','添加成功');
            }
        }else{
            return $this->back($res['error']);
        }
    }


}