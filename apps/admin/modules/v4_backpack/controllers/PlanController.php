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

class PlanController extends BackendController
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
        $distributeTypeList = array(''=>'全部','1'=>'直接发放','2'=>'注册发放','3'=>'登录发放');
        $targettypeList = array('1'=>'全部用户','2'=>'指定UID用户','3'=>'指定注册时间用户');
        $numbertypeList = array('1'=>'一次','2'=>'每日一次');

        $arr_=array('goodsname','distributeType');
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
        $result=ProductService::product_plan($params);
//         print_r($params);exit;
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                if(isset($item['distributeGoodsId']) && $item['distributeGoodsId']){
                    $pro_res = ProductService::searchProductList(array('goodsid'=>$item['distributeGoodsId']));
                    if($pro_res['errorCode'] || !$pro_res['result']) {
                        $item['distributeGoodsName'] = '无效物品';
                    } else {
                        $pro_info = $pro_res['result'][0];
                        $item['distributeGoodsName'] = $pro_info['goodsname'];
                    }
                }
                if($item['distributeType']==1){
                    $item['distributeTimeStr'] = $item['distributeTime'];
                } else {
                    $item['distributeTimeStr'] = $item['distributeStartTime'].'--'.$item['distributeEndTime'];
                }
                if($item['distributeTime']>date("Y-m-d H:i:s",time()) || $item['distributeStartTime']>date("Y-m-d H:i:s",time())){
                    $item['canEdit'] = 1;
                } else {
                    $item['canEdit'] = 0;
                }
            }
            $data=self::processingInterface($result,$params);
//             print_r($data['datalist']);exit;
            $data['distributeTypeList'] = $distributeTypeList;
            $data['targettypeList'] = $targettypeList;
            $data['numbertypeList'] = $numbertypeList;
            return $this->display('plan-list',$data);
        }
        $data['distributeTypeList'] = $distributeTypeList;
        $data['targettypeList'] = $targettypeList;
        $data['numbertypeList'] = $numbertypeList;
        return $this->display('plan-list',$data);
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
    
    public function getGoodsdelete($id=0)
    {
        if(empty($id)){
            return $this->json(array('error'=>1));
        }
        $result=ProductService::delete_plan(array('distributePlanId'=>$id));
        if($result['errorCode']==0){
            //return $this->redirect('v4backpack/goods/list')->with('global_tips','删除成功');
            return $this->json(array('error'=>0));
        }
        return $this->json(array('error'=>1));
    }
    
    public function getEdit($planId='')
    {
        $input = Input::all();
        
        $params['distributePlanId'] = $planId;
        $params['distributeType'] = $input['ff_type'];
        $params['distributeTargetType'] = $input['ff_boject'];
        $params['distributeNumberType'] = $input['ff_count'];
        $params['distributeGoodsId'] = $input['goodsid'];
        if ($params['distributeType'] == 1) {
            $params['distributeTime'] = $input['ff_time'];
        } else {
            $params['distributeStartTime'] = $input['ff_starttime'];
            $params['distributeEndTime'] = $input['ff_endtime'];
        }
        if ($params['distributeTargetType'] == 2) {
            $params['distributeUids'] = $input['uids'];
        } elseif ($params['distributeTargetType'] == 3) {
            $u_search['startdate'] = $input['cc_starttime'];
            $u_search['enddate'] = $input['cc_endtime'];
            $u_result = UserModel::SearchUids($u_search);
            $params['distributeUids'] = implode(',', $u_result);
            $params['distributeTargetType'] = 2;
        }
    
        //判断礼包库存
        $pro_res = ProductService::searchProductList(array('goodsid'=>$input['goodsid']));
        if($pro_res['errorCode'] || !$pro_res['result']) return $this->json(array('errorCode'=>1,'errortxt'=>'无效物品!'));
        $pro_info = $pro_res['result'][0];
        if ($pro_info['goodstype'] == '3') {
            if (isset($params['distributeUids']) && isset($pro_info['storeNum'])) {
                $uids_arr = explode(',', $params['distributeUids']);
                if (count($uids_arr)>$pro_info['storeNum']) {
                    return $this->json(array('errorCode'=>1,'errortxt'=>'礼包库存不足!'));
                }
            }
        }
    
    
        $result=ProductService::update_plan($params);
        //         print_r($input);
        //         print_r($result);die;
        if($result['errorCode']==0)
        {
            return $this->json(array('errorCode'=>2,'errortxt'=>''));
        }
        return $this->json(array('errorCode'=>1,'errortxt'=>'数据访问失败!'));
    }
    
}