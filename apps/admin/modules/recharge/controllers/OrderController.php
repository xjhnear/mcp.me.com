<?php
namespace modules\recharge\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\a_yiyuan\controllers\HelpController;
use Youxiduo\V4\User\UserService;
class OrderController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'recharge';
    }


    public function getList()
    {
        $data = $search = array();
        $data['keys'] = array(
            'uid' => "UID",
            'uname' => "用户名",
            'umobile' => '手机号'
        );
        $data['au'] = array(
            '' => "交易状态",
            'U' => "未发货",
            'I' => '发货中',
            'F' => "发货失败",
            'S' => '发货成功',
        );
        $pageSize = 10;
        $search = Input::get();
        $search['key'] = Input::get('key',"uname");
        if(Input::get('val')){
            if($search['key'] == "uid"){
                $uid =  Input::get('val');
            }elseif($search['key'] == "uname"){
                $uid = UserService::getUserIdByNickname(Input::get('val'));
            }elseif($search['key'] == "umobile"){
                $uid = UserService::getUserIdByMobile(Input::get('val'));
            }
        }else{
            $uid = "";
        }
        if(isset($search['deliveryStatus']) && $search['deliveryStatus']=="F"){
            $search['payStatus'] = "P";
        }
        if(isset($search['deliveryStatus']) && $search['deliveryStatus']=="U"){
            $search['payStatus'] = "P";
        }
        $search['uid'] =$uid;
        $search['channelName'] = "android-youbi";
        $search['createTimeAfter'] =isset($search['createTimeAfter'])?strtotime($search['createTimeAfter'])*1000:0;
        $search['createTimeBefore'] = isset($search['createTimeBefore'])?strtotime($search['createTimeBefore'])*1000:0;
        $search = array_filter($search);
        $search['pageSize'] = $pageSize;
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex - 1) * $pageSize;
        $res = AllService::excute2("11105", $search,"recharge.youbi/GetRechargeList");
        if ($res['success']) {
            $data['list'] = MyHelpLx::insertUserhtmlIntoRes($res['data']['list']);
            foreach( $data['list'] as &$v){
                $v['createTime'] = substr($v['createTime'],0,-3);
            }
            $total =  $res['data']['totalCount'];
            $search['createTimeAfter'] = Input::get('createTimeAfter');
            $search['createTimeBefore'] = Input::get('createTimeBefore');
//            isset($search['createTimeAfter']) && $search['createTimeAfter'] =  date('Y-m-d', $search['createTimeAfter']/1000);
//            isset($search['createTimeBefore']) && $search['createTimeBefore'] = date('Y-m-d', $search['createTimeBefore']/1000);
            $data['search'] = $search;
            $data['pagelinks'] = MyHelpLx::pager_new(array(), $total, $search['pageSize'], $search);
            return $this->display('order-list', $data);
        }
    }
}

