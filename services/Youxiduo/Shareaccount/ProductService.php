<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Shareaccount;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;


class ProductService extends BaseService{
    const API_URL_CONF = 'app.28888_api_url';

    /**
     * @param $params
     * @param bool $genre
     * @param string $type goods giftbag
     * @return array|bool|mixed|null|string
     */
    public static function searchProductList($params,$genre=false,$type='goods'){
        $params_ = array('id','type','platform');

        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'module_adapter_other/commondata/get_commondata_list');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }
    
    /**
     * @param $params
     * @param bool $is_libao
     * @return array|bool|mixed|null|string
     */
    public static function addProduct($params,$is_libao=false,$geren=1){

        //调用增加商品接口
        $params_ = array('id','type','data','platform','useFor','active');

        $datainfo=array(
            'params'=>$params,//必填数组
            'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
            'url'=>Config::get(self::API_URL_CONF).'module_adapter_other/commondata/save_update_commondata',
            'isadd'=>1
        );
        $result=self::addEdit($datainfo);
//         print_r($datainfo);exit;
        return $result;
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
