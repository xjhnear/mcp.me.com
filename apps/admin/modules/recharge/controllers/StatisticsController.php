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


class StatisticsController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'recharge';
    }

    public function getList()
    {
        $data = $search = array();
        $pageSize = 10;
        $search = Input::get();
        if(isset($search['startDate'])){
            $search['startDate'] =str_replace('-',"",$search['startDate']);
            $search['endDate'] = str_replace('-',"",$search['endDate']);
        }
        $search = array_filter($search);
        $search['pageSize'] = $pageSize;
        $search['channelName'] = "android-youbi";
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex - 1) * $pageSize;
        $res = AllService::excute2("11105", $search, "recharge.youbi/GetDailyAggregateList");
        if ($res['success']) {
            $data['list'] = $res['data']['list'];
            foreach( $data['list'] as &$v){
                $a = substr($v['date'],0,4);
                $b = substr($v['date'],4,2);
                $c = substr($v['date'],-2,2);
                $v['date'] = $a."-". $b ."-". $c;
            }
            $total = $res['data']['totalCount'];
            $search['startDate'] = Input::get('startDate');
            $search['endDate'] = Input::get('endDate');
            $data['pagelinks'] = MyHelpLx::pager_new(array(), $total, $search['pageSize'], $search);
            $data['search'] = $search;
            return $this->display('statistics-list', $data);
        }

    }



}