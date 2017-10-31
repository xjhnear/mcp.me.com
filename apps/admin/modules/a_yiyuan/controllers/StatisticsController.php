<?php
namespace modules\a_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;

class StatisticsController extends HelpController
{
    public function _initialize()
    {
        $this->current_module = 'a_yiyuan';
    }
    public  function  getNumberList()
    {
        $search = $data = array();
        $search = Input::get();
        $pagesize = 10;
        $pageIndex = Input::get('page', 1);
        $search['pagsize'] = $pagesize;
        $search['offset'] = (int)($pageIndex - 1) *10;
        $search['channelId'] = HelpController::$channelId;
        $res = AllService::excute2('8089',$search,'luckyDraw/ParticipateInfoReport');
        if ($res['success']) {
            $resData = $res['data'];
            $data['list'] = $resData['list'];
            $total = $resData['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(), $total, $search['pagsize'], $search);
            $data['search'] = $search;
        }
        return $this->display('statistics-number-list', $data);
    }

    public  function  getCopiesList()
    {
        $search = $data = array();
        $search = Input::get();
        $pagesize = 10;
        $pageIndex = Input::get('page', 1);
        $search['pagsize'] = $pagesize;
        $search['offset'] = (int)($pageIndex - 1) *10;
        $search['channelId'] = HelpController::$channelId;
        $res = AllService::excute2('8089',$search,'luckyDraw/ParticipateInfoReport');
//        print_r($res);
        if ($res['success']) {
            $resData = $res['data'];
            $data['list'] = $resData['list'];
            $total = $resData['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(), $total, $search['pagsize'], $search);
            $data['search'] = $search;
        }
        return $this->display('statistics-copies-list', $data);
    }

    public  function  getCoinList()
    {
        $search = $data = array();
        $search = Input::get();
        $pagesize = 10;
        $pageIndex = Input::get('page', 1);
        $search['pagsize'] = $pagesize;
        $search['offset'] = (int)($pageIndex - 1) *10;
        $search['channelId'] = HelpController::$channelId;
        $res = AllService::excute2('8089',$search,'luckyDraw/ParticipateInfoReport');
//        print_r($res);
        if ($res['success']) {
            $resData = $res['data'];
            $data['list'] = $resData['list'];
            $total = $resData['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager_new(array(), $total, $search['pagsize'], $search);
            $data['search'] = $search;
        }
        return $this->display('statistics-coin-list', $data);
    }


}