<?php
namespace modules\v4_statistics\controllers;
//use Youxiduo\MyService\SuperController;
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
use modules\v4user\models\UserModel;
use Youxiduo\V4share\V4shareService;
use Youxiduo\Task\TaskV3Service;

class AppAccountController extends BackendController
{
    public function _initialize()
    {

        $this->current_module = 'v4_statistics';
    }
    //游币发放与消耗
    public function getMoneyConsume()
    {
        $data = $search = array();
        $data['give'] = '系统发放';
        $data['use'] = '系统消耗';
        $data['person'] = '人工发放';
        $data['consume'] = '人工消耗';
        $search = Input::get();
        $search['platform'] = 'ios';
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search = array_filter($search);
        $search['currencyType'] = '0';
        $search['operationType'] = 'sys_operation';
        $search['isPositive'] = 'true';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['xt'] = $res['data'];
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['xh'] = $res['data'];
        unset($search['operationType']);
        $search['operationType'] = 'manage ';
        $search['isPositive'] = 'true';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['rg'] = $res['data'];
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['rgxh'] = $res['data'];
        $data['search'] = $search;
        return $this->display('appaccount-ybf-list',$data);
    }
    
    //钻石发放与消耗
    public function getDiamondsConsume()
    {
        $data = $search = array();
        $data['give'] = '系统发放';
        $data['use'] = '系统消耗';
        $data['person'] = '人工发放';
        $data['consume'] = '人工消耗';
        $search = Input::get();
        $search['platform'] = 'ios';
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search = array_filter($search);
        $search['currencyType'] = '1';
        $search['operationType'] = 'sys_operation';
        $search['isPositive'] = 'true';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['xt'] = $res['data'];
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['xh'] = $res['data'];
        unset($search['operationType']);
        $search['operationType'] = 'manage ';
        $search['isPositive'] = 'true';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['rg'] = $res['data'];
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
        $data['rgxh'] = $res['data'];
        $data['search'] = $search;
        return $this->display('appaccount-zsf-list',$data);
    }
    //礼包领取消耗
    public function getPackageConsume()
    {
        $data = $search = array();
        $data['use'] = '领取礼包消耗';
        $search = Input::get();
        $search['operationType'] = 'gift_consume';
        $search['platform'] = 'ios';
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search = array_filter($search);
        $search['currencyType'] = '1';
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['data'] = $res['data'];
        $data['search'] = $search;
        return $this->display('appaccount-lbxh-list',$data);
    }
    //礼包领取

    public function getPackage()
    {
        $data = $search = array();
        $data['give'] = '所有礼包';
        $search = Input::get();
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search['productName'] = Input::get('productName');
        $search['operationType'] = 'gift_consume';
        $search['platform'] = 'ios';
        $search = array_filter($search);
        $res = AllService::excute2("48080",$search,"module_mall/product/countProduct");
        if($res['success'])
            if(isset($search['productName'])) {
                $data['list'] = $res['data'];
            }else{
                $data['data'] = $res['data'];
            }
                $data['search'] = $search;

        return $this->display('appaccount-package-list',$data);
    }
     //商品兑换
    public function getGood()
    {
        $data = $search = array();
        $data['give'] = '所有商品';
        $search = Input::get();
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search['productName'] = Input::get('productName');
//        $search['pageSize'] = 10;
        $search['operationType'] = 'mall_consume';
        $search['platform'] = 'ios';
        $search = array_filter($search);
        $res = AllService::excute2("48080",$search,"module_mall/product/countProduct");
//        print_r($res);
        if($res['success'])
            if(isset($search['productName'])) {
                $data['list'] = $res['data'];
            }else{
                $data['data'] = $res['data'];
            }
        $data['search'] = $search;
        return $this->display('appaccount-good-list',$data);
    }
    //商品领取消耗
    public function getGoodConsume()
    {
        $data = $search = array();
        $data['use'] = '商品兑换消耗';

        $search = Input::get();
        $search['operationType'] = 'mall_consume';
        $search['platform'] = 'ios';
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search['productName'] = Input::get('productName');
        $search = array_filter($search);
        $search['currencyType'] = '1';
        $search['isPositive'] = 'false';
        $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        if($res['success'])
            $data['data'] = $res['data'];
        $data['search'] = $search;
        return $this->display('appaccount-goodconsume-list',$data);
    }
    //任务发放钻石
    public function getTaskDiamonds()
    {
        $data = $search = array();
        $data['give'] = '所有任务奖励钻石';
        $search = Input::get();
        $search['startTime'] = isset($search['startDate'])?$search['startDate']:date('Y-m-d 00:00:00');
        $search['endTime'] = isset($search['endDate'])?$search['endDate']:date('Y-m-d 23:59:59');
        $search['taskName'] = Input::get('taskName');
        $search['gameId'] = Input::get('game_id');
        $search = array_filter($search);
        $res = AllService::excute2("58080",$search,"module_task/prize/query_diamond_prize_list");
        if($res['success'])
            $res['data'] = json_decode($res['data'],true);
        if(isset($search['taskName']) || isset($search['gameId'])) {
            $data['list'] = $res['data']['list'];
            $data['total'] = $res['data']['totalAmount'];
            $data['number'] = $res['data']['totalNumber'];
        }else {
            $data['total'] = $res['data']['totalAmount'];
            $data['number'] = $res['data']['totalNumber'];
        }
        $data['search'] = $search;
        return $this->display('appaccount-rwzs-list',$data);
    }
    
    //任务钻石排行榜
    public function getTaskDiamondsRank()
    {
        $data = $search = array();
        $search = Input::get();
        $search['startTime'] = isset($search['startDate'])?$search['startDate']:0;
        $search['endTime'] = isset($search['endDate'])?$search['endDate']:0;
        $search = array_filter($search);
        $res = AllService::excute2("58080",$search,"module_task/prize/query_diamond_rank_list");
        if($res['success'])
            $data['data'] = json_decode($res['data'],true);
        $data['search'] = $search;
        return $this->display('appaccount-rwzs-rank',$data);
    }

    //一元夺宝钻石统计
    public function getCountYiyuan()
    {
        $data = $search = array();
        $search = Input::get();
        $search['timeBegin'] = isset($search['startDate']) ? $search['startDate'] : 0;
        $search['timeEnd'] = isset($search['endDate']) ? $search['endDate'] : 0;
        $search = array_filter($search);
        $search['currencyType'] = 1;
        $search['platform'] = 'ios';
        $search['operationType'] = 'luckyDraw_consume';
        $res = AllService::excute2("48080", $search, "module_rmb/account/currencyStatics");
        if ($res['success']){
            $data['data'] = $res['data'];
        }
        $data['search'] = $search;
        //var_dump($res,$data);die;
        return $this->display('appaccount-count-yiyuan',$data);
    }

    //挖宝活动钻石统计
    public function getCountDig()
    {
        $data = $search = array();
        $search = Input::get();
        $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
        $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
        $search = array_filter($search);
        $search['currencyType'] = 1;
        $search['platform'] = 'ios';
        $search['operationType'] = 'ios_box_activity_output_dim';//消耗
        $res_out = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        $search['operationType'] = 'ios_box_activity_income_dim';//发放
        $res_in = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
        unset($search['operationType']);
        if ($res_out['success']){
            $data['out'] = $res_out['data'];//消耗
        }
        if ($res_in['success']){
            $data['in'] = $res_in['data'];//发放
        }
        $data['search'] = $search;
        //var_dump($data);die;
        return $this->display('appaccount-count-dig',$data);
    }
    
    //新用户任务参与度
    public function getExcelCompare()
    {
        $data = $search = array();
        return $this->display('excel-compare',$data);
    }
    
    public function postExcelCompare()
    {
        $data = $search = $u_result = $s_result = $t_result = array();
        $search = Input::get();
        $u_search['startdate'] = $search['startdate'];
        $u_search['enddate'] = $search['enddate'];
        $u_result = UserModel::SearchUids($search);
        
        $s_search['startTime'] = $search['startdate'];
        $s_search['endTime'] = $search['enddate'];
        $res = V4shareService::excute3($s_search,"get_new_uid_list");
        if($res['success']){
            $s_result = $res['data'];
        }
        
        switch ($search['UType']) {
            case 1:
                $c_uids = array_diff($u_result,$s_result);
                break;
            case 2:
                $c_uids = $s_result;
                break;
            default:
                $c_uids = $u_result;
                break;
        }
        
        $t_search['startTime'] = $search['startdate'];
        $t_search['endTime'] = $search['enddate'];
        $t_search['uids'] = implode(',', $c_uids);
        if ($search['TType']==1) {
            $t_search['taskId'] = $search['linkValue_t'];
        }
        $res_t = TaskV3Service::query_new_users(array_filter($t_search));
        if(!$res_t['errorCode']&&$res_t['result']){
            $t_result = $res_t['result'];
        }
        
        $data['task_count'] = count($t_result);
        $data['user_count'] = count($c_uids);
        $data['percent'] = count($c_uids)>0?round(($data['task_count']/$data['user_count'])*100,2).'%':'N/A';
        $data['search'] = $search;
        $data['platforms'] = array('yxdjqb' => 'IOS','glwzry'=>'攻略');
        return $this->display('excel-compare',$data);
    }
    

}



