<?php

namespace modules\v4_backpack\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Backpack\ProductService;
use Youxiduo\Imall\ProductService as IProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Cache\CacheService;
use Youxiduo\Helper\DES;
use Youxiduo\V4\User\UserService;
use modules\web_forum\controllers\TopicController;
use modules\v4_adv\models\Core;
use modules\v4user\models\UserModel;

/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class RecordController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_backpack';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $params = $data = array();
        $params['pageNow'] = Input::get('page',1);
        $params['pageSize'] =10;
        $distributeStatusList = array('1'=>'发放成功','0'=>'发放失败');
        
        $arr_=array('knapsackGoodsId','distributePlanId','uid','distributeStatus');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        if(Input::get('startTime')){
            $params['startTime']=Input::get('startTime');
        } else {
            $params['startTime']=date("Y-m-d H:i:s",strtotime("-30 day"));
        }
        if(Input::get('endTime')){
            $params['endTime']=Input::get('endTime');
        } else {
            $params['endTime']=date("Y-m-d H:i:s",time());
        }
        $result=ProductService::searchRecordList($params);
//         print_r($result);exit;
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            $data['distributeStatusList'] = $distributeStatusList;
            return $this->display('record-list',$data);
        }
        $data['distributeStatusList'] = $distributeStatusList;
        return $this->display('record-list',$data);
    }

    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){ //echo $result['totalCount'];exit;
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        //print_r($pager);
        unset($data['pageIndex']);
        $pager->appends($data);

        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }

    /**错误输出 **/
    private function errorHtml($result=array(),$str=''){
        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips',$str.'出错拉');
        exit;
    }

}