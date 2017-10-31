<?php
namespace modules\IOS_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class LotteryController extends HelpController
{
    public function _initialize()
    {
        $this->current_module = 'IOS_yiyuan';
    }

    public function getList()
    {
        $data = $search = array();
        $startDate                  = Input::get('startDate');
        $endDate                    = Input::get('endDate');

        $search['channelInfo']      = self::$channelId;
        $search['startDate']        = Input::get('startDate');
        $search['endDate']          = Input::get('endDate');
        $search['activityNum']      = Input::get('activityNum');
        $search['merchandiseName']  = Input::get('merchandiseName');
        $search['luckyNum']         = Input::get('luckyNum');
        $search['luckyUserUid']     = Input::get('luckyUserUid');
        $search['openDrawStatus']   = Input::get('openDrawStatus');

        $search['pageSize'] = 10;
        $total = 0;
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex-1) * $search['pageSize'];
        if ($search['startDate']) $search['startDate'] = strtotime($search['startDate'])*1000;
        if ($search['endDate']) $search['endDate'] = strtotime($search['endDate'])*1000;
        $search = array_filter($search);
        $res = AllService::excute2("8089",$search,"luckyDraw/QueryDrawNum");
        $data['list'] = array();
        if($res['success']){
            $data['list'] = $res['data']['list'];
            $data['list'] = MyHelpLx::insertUserhtmlIntoRes($data['list']);

            foreach($data['list'] as $key => $value){
                $tempId['id'] = $value['goodId'];
                $tempRes = AllService::excute2("8089",$tempId,"luckyDraw/QueryMerchandiseDetail");
                $data['list'][$key]['boughtCount'] = $data['list'][$key]['totalCount'] - $data['list'][$key]['boughtCount'];
                $data['list'][$key]['openTime'] = $data['list'][$key]['openTime']>0 ? floor($data['list'][$key]['openTime']/1000) : '';
                if ($tempRes['success']) {
                    $tempGoodsRes = json_decode($tempRes['data'], true);
                    $data['list'][$key]['goodsStatus'] = $tempGoodsRes['status'];
                    $data['list'][$key]['worth'] = $tempGoodsRes['worth'];
                }
            }

            $total = $res['data']['totalCount'];
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        $search['startDate'] = $startDate;
        $search['endDate'] = $endDate;
        $data['search'] = $search;
        return $this->display('lottery-list',$data);
    }

    public function getUserList()
    {
        $data = $search = array();
        $startTime = Input::get('startTime');
        $endTime   = Input::get('endTime');

        $search['drawId']       = Input::get('drawId');
        $search['startTime']    = Input::get('startTime');
        $search['endTime']      = Input::get('endTime');
        $search['partakeNum']   = Input::get('partakeNum');
        $search['userId']       = Input::get('userId');

        $search['pageSize'] = 10;
        $total = 0;
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex-1) * $search['pageSize'];
        $search['startTime'] && $search['startTime'] = strtotime($search['startTime']) * 1000;
        $search['endTime'] && $search['endTime'] = strtotime($search['endTime']) * 1000;

        $res = AllService::excute2("8089",$search,"luckyDraw/QueryDrawNumAndUser");
        $data['list'] = array();
        if($res['success']){
            $data['list'] = $res['data']['list'];
            $total = $res['data']['totalCount'];
            $data['list'] = MyHelpLx::insertUserhtmlIntoRes($data['list']);
            foreach ($data['list'] as $key => $value) {
                $partakeTime = $value['partakeTime'];
                $partakeTime && $partakeTime = $partakeTime/1000;
                $data['list'][$key]['partakeTime'] = MyHelpLx::microtime_format('Y-m-d H:i:s x', $partakeTime);
            }
        }

        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        $search['startTime'] = $startTime;
        $search['endTime'] = $endTime;
        $data['search'] = $search;

        return $this->display('lottery-user-list',$data);
    }

    public function getCompulsoryEnd () {
        $input['drawId'] = Input::get('drawId');
        if (!$input['drawId']) {
            echo json_encode(array('success'=>400, 'msg'=>'请传drawId'));
        }
        $res = AllService::excute2("8089", $input, "luckyDraw/ForceCancelDraw");

        if($res['success']){
            echo json_encode(array('success'=>200, 'msg'=>'强制结束成功'));
        } else {
            echo json_encode(array('success'=>400, 'msg'=>'强制结束失败'));
        }
    }

}