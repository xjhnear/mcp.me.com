<?php
namespace modules\IOS_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\IOS_yiyuan\controllers\HelpController;


class AddressController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'IOS_yiyuan';
    }

    public function getList()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $search = Input::get();
        $search['pageSize'] = $pageSize;
        $pageIndex = (int)Input::get('page',1);
        $search['offset'] = ($pageIndex-1)*10;
        $search['channelId'] = HelpController::$channelId;
        if(Input::get('userPhone')){
            $userId = UserService::getUserIdByMobile(Input::get('userPhone'));
            if($userId){
                $search['userId'] = $userId;
            }
        }
        $res = AllService::excute2("8089",$search,"luckyDraw/QueryReceiveAddressForPage");
        $data['search'] = $search;
        if($res['success']){
            $data['list'] = MyHelpLx::insertUserhtmlIntoRes($res['data']['list']);
            foreach($data['list'] as &$v){
                if(isset($v['userAddress'])){
                    $v['userAddress'] = str_replace(',','<br>',$v['userAddress']);
                    $v['userAddress'] = str_replace(array('{','}','"'),'',$v['userAddress']);
                }
            }

            $total = $res['data']['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(),$total,$search['pageSize'],$search);
        }
        return $this->display('address-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('eventId',"");
        $input['channelId'] = HelpController::$channelId;
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


}