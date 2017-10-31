<?php
namespace modules\IOS_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\IOS_yiyuan\controllers\HelpController;


class OrderController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'IOS_yiyuan';
    }

    public $statusArr = array('0'=>"未发货",'1'=>'已发货','2'=>'已收货');
    public function getList()
    {
        $data = $search = $input = $addArr = array();
        $pageSize = 10;
        $search = Input::get();
        $search['pageSize'] = $pageSize;
        $search['startTime'] =isset($search['startDate'])?strtotime($search['startDate'])*1000:0;
        $search['endTime'] = isset($search['endDate'])?strtotime($search['endDate'])*1000:0;
        $pageIndex = (int)Input::get('page',1);
        $search['offset'] = ($pageIndex-1)*$pageSize;
        $search['channelId'] = HelpController::$channelId;
        $search = array_filter($search);
        $search['status'] = Input::get('status');
        $res = AllService::excute2("8089",$search,"luckyDraw/QueryOrderInfo");
        if($res['success']){
            $list = json_decode($res['data'],true);
            $addArr = array();
            foreach($list['list'] as &$v){
                if(isset($v['addressInfo'])){
                    $v['addressInfo'] = str_replace(array('"','{','}'),"",$v['addressInfo']);
                    $str_arr1 = array('receiveAdd','phone','city','receiveUser','orderId','tips');
                    $str_arr2 = array('收件地址','电话','城市','收货人','订单号','备注');
                    $v['addressInfo'] = str_replace($str_arr1,$str_arr2,$v['addressInfo']);
                    $arr1 = explode(',',$v['addressInfo']);
                    foreach($arr1 as &$a){
                        $a = explode(':',$a);
                    }
                    $v['addressArr'] = $arr1;
                    $addArr[$v['id']] = $arr1;
                    $v['createTime'] = substr($v['createTime'],0,-3);
                }
            }
            $list['list'] = MyHelpLx::insertUserhtmlIntoRes($list['list']);
            $data['list'] = $list['list'];
            $total = $list['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(),$total,$search['pageSize'],$search);
        }
        Session::set('addArr',$addArr);
        $data['search'] = $search;

        $data['statusArr'] = $this->statusArr;
        return $this->display('order-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $data['data'] = $input;
        $addArr = Session::get('addArr');
        if($addArr&&isset($addArr[$input['id']])){
            $data['address'] = $addArr[$input['id']];
        }else{
            $data['address'] = array();
        }
        return $this->display('order-add',$data);
    }




}