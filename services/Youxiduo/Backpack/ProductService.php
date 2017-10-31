<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Backpack;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;


class ProductService extends BaseService{
    const API_URL_CONF = 'app.backpack_api_url';


    public static function DeleteProduct($params)
    {   $params_=array('goodsid');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'config_knapsack/delete_knapsack');
    }

    //加入物品发放计划
    public static function grant_product($params)
    {   $params_=array('distributeType','distributeTargetType','distributeGoodsId','distributeUids','distributeNumberType','distributeTime','distributeStartTime','distributeEndTime','appVersion');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'knapsack_distribute_plan/insert_distribute_plan','POST');
    }
    
    //物品发放计划
    public static function product_plan($params)
    {   $params_=array('distributeType','interTime');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'knapsack_distribute_plan/find_distribute_plan');
    }
    
    //编辑发放计划
    public static function update_plan($params)
    {   $params_=array('distributePlanId','distributeType','distributeTargetType','distributeGoodsId','distributeUids','distributeNumberType','distributeTime','distributeStartTime','distributeEndTime');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'knapsack_distribute_plan/update_distribute_plan','POST');
    }
    
    //删除发放计划
    public static function delete_plan($params)
    {   $params_=array('distributePlanId');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'knapsack_distribute_plan/delete_distribute_plan');
    }
    
    //发放物品
    public static function send_product($params)
    {   $params_=array('uid','knapsackGoodsId','planId','goodsSourceType','goodsSourceInfo');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'user_knapsack_goods/insert_user_knapsack_goods','POST');
    }
    
    //物品发放记录
    public static function send_product_record($params)
    {   $params_=array('distributePlanId','uid','distributeStatus');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'user_distribute_record/find_distribute_record');
    }
    
    /**
     * @param $params
     * @param bool $genre
     * @param string $type goods giftbag
     * @return array|bool|mixed|null|string
     */
    public static function searchProductList($params,$genre=false,$type='goods'){
        $params_ = array('goodsid','goodsname','goodstype','startTime','endTime','pageNow','pageSize','platform');

        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'config_knapsack/findlist_knapsack');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }

    public static function searchRecordList($params){
        $params_ = array('knapsackGoodsId','distributePlanId','uid','startTime','endTime','distributeStatus','pageNow','pageSize','platform');
    
        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'user_distribute_record/find_distribute_record');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }
    
    /**
     * @param $params
     * @param bool $is_libao
     * @return array|bool|mixed|null|string
     */
    public static function addProduct($params,$is_libao=false,$geren=1){

        //调用增加商品接口
        $params_ = array('goodstype','goodsname','goodsshortname','goodscontent','gid','gname','diamondtype','diamondnum','diamondmin','diamondmax','taskid','taskname','endtime','sortvalue','operator','createtime','updatetime','picurl','platform','giftId','giftName','storeNum','isLine','mutexTaskId','subGoodsIds','subTaskId','subTaskName');

        $datainfo=array(
            'params'=>$params,//必填数组
            'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
            'url'=>Config::get(self::API_URL_CONF).'config_knapsack/insert_knapsack',
            'isadd'=>1
        );
        $result=self::addEdit($datainfo);
//         print_r($datainfo);exit;
        return $result;
    }

    /**
     * 商品修改接口(最新的！！目前只有IOS的在调用)
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function editProduct($params){
        $params_ = array('goodsid','goodstype','goodsname','goodsshortname','goodscontent','gid','gname','diamondtype','diamondnum','diamondmin','diamondmax','taskid','taskname','endtime','sortvalue','operator','createtime','updatetime','picurl','platform','isLine','mutexTaskId','subGoodsIds','subTaskId','subTaskName');

        $datainfo=array(
            'params'=>$params,
            'params_'=>$params_,
            'url'=>Config::get(self::API_URL_CONF).'config_knapsack/update_knapsack',
        );
        return Utility::preParamsOrCurlProcess($datainfo['params'],$datainfo['params_'],$datainfo['url'],'POST');
    }

    /**
     * 添加修改接口
     * @param $params
     * @return array|bool|mixed|null|string
     */
    private static function addEdit($datainfo){
        if($datainfo['isadd'] == 1){
            $youxiduo_admin=Session::get('youxiduo_admin');
            $datainfo['params']['creator']=$youxiduo_admin['username'];
        };
        return Utility::preParamsOrCurlProcess($datainfo['params'],$datainfo['params_'],$datainfo['url'],'POST');
    }

}
