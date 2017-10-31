<?php
namespace modules\v4message\models;

use Illuminate\Support\Facades\Config;
use Youxiduo\Helper\Utility;

class Push extends BaseHttp
{
    public static $LinkTypeList   = array(
        '0'=>'不跳转',
        //'1'=>'游戏详情','2'=>'美女视频详情','3'=>'专题','4'=>'新游预告详情','5'=>'攻略列表',
        //'6'=>'评测列表','7'=>'新闻列表',
        //'10'=>'攻略详情','9'=>'评测详情','8'=>'新闻详情',
        //'11'=>'活动(1,2,3)',
        //'12'=>'礼包详情',
        //'16'=>'论坛首页','17'=>'帖子详情','18'=>'资料大全','19'=>'有奖问答详情','20'=>'商城详情','21'=>'社区广场(1,2,3)',
        '1001'=>'游戏详情',
        '1037'=>'任务详情',
        '1012'=>'礼包详情',
        //'1020'=>'商城详情',
        '1058'=>'活动详情',
        '1024'=>'视频详情',
        '1017'=>'帖子详情',
        '1008'=>'手游新闻详情',
        '1004'=>'新游预告详情',
        '1019'=>'商品详情',
        'webview'=>'内置浏览器跳转','outredirect'=>'外置浏览器跳转'

    );

    public static function search($search,$pageIndex=1,$pageSize=10)
    {
        $pushType = isset($search['pushType']) ? $search['pushType'] : 0;
        $beginTime = isset($search['startdate']) ? $search['startdate'] : null;
        $endTime = isset($search['enddate']) ? $search['enddate'] : null;
        $toUid = isset($search['toUid']) ? $search['toUid'] : null;
        $deviceType = isset($search['deviceType']) ? $search['deviceType'] : null;

        $apiurl = Config::get(self::HOST_URL_NEW) . 'user_push_switch/findPushMsgLog';
        $params = array(
            //'pushType'=>$pushType,
            'deviceType'=>$deviceType
        );
        $beginTime && $params['beginTime'] = $beginTime;
        $endTime && $params['endTime'] = $endTime;
        $toUid && $params['toUid'] = $toUid;
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;

        $result = self::http($apiurl,$params);
        if($result['errorCode']==0 && $result['result']){
            foreach($result['result'] as $key=>$row){
                $row['messages'] = json_decode($row['messages'],true);
                $result['result'][$key] = $row;
            }
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
        return array('result'=>array(),'totalCount'=>0);
    }

    public static function sendMessage($content,$linkType,$linkId,$linkValue,$allUser=false,$tokens,$gameId,$other,$appname='')
    {
        $apiurl = Config::get(self::HOST_URL_NEW) . 'user_push_switch/push';
        $params = array(
            'alert'=>$content,
            'linkType'=>$linkType,
            'linkId'=>$linkId,
            'linkValue'=>$linkValue,
            'allUser'=>$allUser ? : '',
            'toUid'=>$tokens,
            'gid' => $gameId,
            'other'=>$other,
            'platform'=>'ios',
            'appname'=>$appname,
        );

        //$result = self::http($apiurl,$params,'POST','json');
        $result = Utility::loadByHttp($apiurl,$params,'POST');
        if($result['errorCode']==0){
            return true;
        }
        var_dump($result);exit;
        return false;
    }
}