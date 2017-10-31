<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Imall;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Youxiduo\V4\Game\GameService;
use Illuminate\Support\Facades\Session;
use modules\system\models\SystemSettingModel;

class ProductService extends BaseService{
    const API_URL_CONF = 'app.mall_api_url';
    const RLT_URL_CONF = 'app.mall_rlt_api_url';
    const BBS_API_URL = 'app.ios_bbs_api_url';
    const VIRTUAL_CARD_URL = 'app.virtual_card_url'; //http://121.40.78.19:18080/module_virtualcard/
    const MALL_MML_API_URL ='app.mall_mml_api_url';//http://121.40.78.19:8080/module_mall/
    const MALL_API_ACCOUNT = 'app.account_api_url';  //http://121.40.78.19:8080/module_account/
    //隐藏商品种类
    public static function hide_category($id)
    {   
        $params['categoryId']=$id;
        return Utility::preParamsOrCurlProcess($params,array('categoryId'),Config::get(self::MALL_MML_API_URL).'product/hide_category');
    }
    //商品种类置顶
    public static function top_category($id)
    {   
        $params['categoryId']=$id;
        return Utility::preParamsOrCurlProcess($params,array('categoryId'),Config::get(self::MALL_MML_API_URL).'product/top_category');
    }
    
    public static function grant_product($params=array())
    {
        $params_=array(
                'uids'//用户ID串，用逗号隔开
                ,'productCode'//商品代码
                ,'number'//数量
                ,'platform'
                );
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/grant_product');
    }

    public static function getExportUrl()
    {
        return Config::get(self::API_URL_CONF).'product/export';
    }

    public static function getReturnUrl()
    {
        return Config::get(self::VIRTUAL_CARD_URL).'virtualcard/exportcard';
    }

    public static function deleteCard($params)
    {
        $params_=array('cardinfoId');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::VIRTUAL_CARD_URL).'virtualcard/delete');
    }
    /**
    * 修改商品属性
    * @param $params
    * @return array
    **/
    public static function update_productextra($params){ 
         return Utility::preParamsOrCurlProcess($params,array('productCode','top','hot','newest','recommend','lightDelivery','modifier'),Config::get(self::MALL_MML_API_URL).'extra/update_productextra','POST');   
    }

    public static function update_product_reform($params,$key=array())
    {
        return Utility::preParamsOrCurlProcess($params,!empty($key)?$key:array('productCode','isTop','modifier','productStock'),Config::get(self::MALL_MML_API_URL).'product/update_product_reform','POST');
    }


    public static function getExportquery($params)
    {
        $params_=array('timeBegin','timeEnd','onOrOff','productName','pageIndex','pageSize');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/exportquery');
    }

    public static function getexport($params)
    {
        $params_=array('timeBegin','timeEnd','onOrOff','productName');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/export');
    }
    public static function DeleteProduct($params)
    {   $params_=array('productCode','platform','modifier');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/delete_product');
    }

    /**
     * @param $params
     * @param bool $genre
     * @param string $type goods giftbag
     * @return array|bool|mixed|null|string
     */
    public static function searchProductList($params,$genre=false,$type='goods'){
        $params_ = array('id','pageIndex','signer','pageSize','categoryId','productCode','gameId','productName','testCode','isOnshelf','sortType','gids','gamePriceBegin','gamePriceEnd','productType','inventedType','isBelongUs','isNotice','productStock','createTimeBegin','createTimeEnd','isExclusive','hashValue',"ascOrDesc",'signer','sign','active','isAdd','isTop','currencyType','platform','appname');

        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }

    /**
     * 通过商品code获取游戏id
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function getGameIdsByProductCode($params){
        $params_ = array('productCode','genre'); //商品code为string，多个用逗号隔开
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::RLT_URL_CONF).'get_game_list_by_mall');
    }

    /**
     * 获取商品和游戏关系列表
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function getMallGameRelation($params){
        $params_ = array('productCode','gid','genre','isActive','pageIndex','pageSize');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::RLT_URL_CONF).'get_mall_game_list');
    }

    //获取商城表单列表
    public static function  getFormList($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/getForm');
    }


    //解绑
    public static function release($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/release','GET');
    }



    /**
     * 获取礼包和游戏关联
     * @param $params
     * @return array|bool|mixed|string
     */
    public static function getGiftGameRelation($params){
        $params_ = array('gid','gfid','genre','isActive','pageIndex','pageSize');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::RLT_URL_CONF).'get_gift_game_list');
    }

    /**
     * 删除礼包和游戏关联
     * @param $gid
     * @param $gfid
     * @param $genre
     * @return bool|mixed|string
     */
    public static function delGiftGameRelation($gid,$gfid,$genre){
        $params = array(
            'gid' => $gid,
            'gfid' => $gfid,
            'genre' => $genre
        );
        return Utility::loadByHttp(Config::get(self::RLT_URL_CONF).'del_gift_game',$params);
    }

    /**
     * @param $params
     * @param bool $is_libao
     * @return array|bool|mixed|null|string
     */
    public static function addProduct($params,$is_libao=false,$geren=1){

        //调用增加商品接口 isDraw  drawConf afterTime  freeNumber cost draw_radio beginTime dataValue dataType
        $params_ = array('linkType','linkId','contactInfo','lightDelivery','tagRelList','currencyType','productName','productCode','categoryId','productGamePrice','productPrice','isExclusive','productType','isNeedTemplate','templateId','gname','gid','isBelongUs','creator','isNotice','productStock','productCost','exclusiveAccount','singleLimit','productDesc','isNewUser','inventedType','cardCode','productSummary','productInstruction','productDesc','productImgpath','lightDelivery','productSort','hashValue','isOnshelf','isTop','isHot','isNewest','isRecommend','creator','cardCode','inventedType','startTime','endTime','timeType','timeValue','ruleLimit','isDiscount','productDisplay','discountGamePrice','spUrl','limitType','is','extraReq','isDraw','drawConf','isOffTogether','tagId','platform','appname');

        $datainfo=array(
            'params'=>$params,//必填数组
            'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
            'url'=>Config::get(self::API_URL_CONF).'product/add_product',
            'isadd'=>1
        );
        $result=self::addEdit($datainfo);
        return $result;
    }

    /**
     * 商品修改接口
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function modifyProduct($params){

        $params_ = array('linkType','linkId','contactInfo','productId','productName','productCode','categoryId','gameId','gid','productGamePrice','productPrice','productType','isNeedTemplate','templateId','gname',
            'isBelongUs','isNotice','productStock','productCost','singleLimit','lightDelivery','exclusiveAccount','isExclusive','inventedType','productSummary','productInstruction','productDesc','productImgpath','productDesc','productSort','hashValue','isOnshelf','cardCode','startTime','endTime','timeType','timeValue','ruleLimit','isDiscount','productDisplay','discountGamePrice','spUrl','limitType','isOffTogether','platform');
        $datainfo=array(
            'params'=>$params,//必填数组
            'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
            'url'=>Config::get(self::API_URL_CONF).'product/update_product_reform',
            'isadd'=>0
        );

        return self::addEdit($datainfo);
    }


    /**
     * 商品修改接口(最新的！！目前只有IOS的在调用)
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function editProduct($params){
        $params_ = array('linkType','linkId','spUrl','isNeedTemplate','contactInfo','templateId','lightDelivery','tagRelList','currencyType','productName','productCode','categoryId','productStock','productGamePrice','productPrice','tagId',
            'productCost','isExclusive','singleLimit','cardCode','productSummary','productInstruction','productDesc','gname','gid',
            'productImgpath','isBelongUs','productSort','isNotice','hashValue','active','isHot','isOnshelf','isTop','isNewest',
            'isRecommend','startTime','endTime','inventedType','exclusiveAccount','isNewUser','limitType','singleLimit','isDraw','drawConf',
            'timeType','timeValue','ruleLimit','modifier','extraReq','isOffTogether','discountGamePrice','isDiscount','platform','appname');
        $datainfo=array(
            'params'=>$params,
            'params_'=>$params_,
            'url'=>Config::get(self::API_URL_CONF).'product/update_product_reform',
        );
        return Utility::preParamsOrCurlProcess($datainfo['params'],$datainfo['params_'],$datainfo['url'],'POST');
    }


    /**
     * 批量授权礼包
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function batchExclusive($params){
        $params_ = array('linkType','linkId','spUrl','isNeedTemplate','contactInfo','templateId','lightDelivery','tagRelList','currencyType','productName','productCode','categoryId','productStock','productGamePrice','productPrice','tagId',
            'productCost','isExclusive','singleLimit','cardCode','productSummary','productInstruction','productDesc','gname','gid',
            'productImgpath','isBelongUs','productSort','isNotice','hashValue','active','isHot','isOnshelf','isTop','isNewest',
            'isRecommend','startTime','endTime','inventedType','exclusiveAccount','isNewUser','limitType','singleLimit','isDraw','drawConf',
            'timeType','timeValue','ruleLimit','modifier','extraReq','isOffTogether','discountGamePrice','isDiscount','platform');
        $datainfo=array(
            'params'=>$params,
            'params_'=>$params_,
            'url'=>Config::get(self::API_URL_CONF).'product/batchExclusive',
        );

        $res = Utility::preParamsOrCurlProcess($datainfo['params'],$datainfo['params_'],$datainfo['url'],'POST');
//        print_r($datainfo);
//        print_r($res);
//        die;
        return $res;
    }

    /**
     * 生成code
     * @param string $prefix
     * @return string
     */
    public static function getCode($prefix = '' , $method = 'uniqid'){
        switch($method){
            case 'md5OrUniqid':
                $result = md5(uniqid($prefix));
                break;
            case '':
                $result = uniqid($prefix);
                break;
            default:
                $result = uniqid($prefix);
        }
        return $result;
    }

    /**
     * 添加商品活动接口
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function addProductActivity($params,$url='product/add_productactivity'){
        //调用增加商品活动接口
        $params_ = array('productCode','isDiscount','discountPrice','startTime','endTime','isProductLimit','totalNumber','sort',
            'creator','hashValue','limitMode','onOrOff','updateRestTime','discountGamePrice','description', 'offProduct','activityId');
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($params['activityId'])){
            $params['modifier']=$youxiduo_admin['username'];
            $params_=$params_+array('activityId');
        }else{
            $params['creator']=$youxiduo_admin['username'];
        }
        //print_r($params);exit;
        return $result=Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).$url,'POST');

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


    /**
     * 商品种类添加/修改接口
     * @param $params
     * @return array
     **/
    public static function addEditCate($input,$url,$isadd=1){
        $datainfo=array(
            'params'=>$input,//必填数组
            'params_'=>array_keys($input),//input:only 中获取的NAME值加上一些添加的name中没有的
            'url'=>Config::get(self::API_URL_CONF).$url,
            'isadd'=>$isadd
        );
        return self::addEdit($datainfo);
    }

    /**
     * 上架商品
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function onsaleProduct($params=array()){

        return Utility::preParamsOrCurlProcess($params,array('productCode','hashValue','modifier','platform'),Config::get(self::API_URL_CONF).'product/onsale_product');
    }

    /**
     * 下架商品
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function offsaleProduct($params=array()){
        return Utility::preParamsOrCurlProcess($params,array('productCode','hashValue','modifier','platform'),Config::get(self::API_URL_CONF).'product/offsale_product');
    }

    /**
     * 查询商品种类接口
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function queryCategory($params){
        $params_=array('createTimeEnd','pageIndex','pageSize','hashValue','parentId','isBelongUs','categoryName','categoryId','createTimeBegin','currencyType');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_category');
    }

    /**
     * 查询商品种类接口
     * @param $params 参数数组
     * @return bool|mixed|string
     **/
    public static function showCategory($params){
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::API_URL_CONF).'product/show_category');
    }

    //查询用户交易记录
    public static function query($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
    }

    /**查询卡编码接口
     * @param $params 参数数组
     * @return bool|mixed|string
     **/
    public static function getvirtualcardlist($params){
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/list');
    }
    /**
     * 查询商品活动接口
     * @param $params 参数数组
     * @return bool|mixed|string
     **/
    public static function searchProductActivityList($params){
        $params_ = array('pageIndex','pageSize','activityId','productCode','isDiscount','createTimeBegin','createTimeEnd','creator',
            'hashValue','isProductLimit','weekFlag','limitMode','description');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/productactivity_list');
    }

    

    //获取许愿帖
    public static function getrule(){
        $rule = SystemSettingModel::getConfig('product_wish_rule');
        if(!empty($rule['data']['rule_id']) &&  $rule['keyname'] == 'product_wish_rule'){
            $params['tid']=$rule['data']['rule_id'];
            return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::BBS_API_URL).'topic_detail');
        }
    }


    //发布许愿帖  Array ( [errorCode] => 0 [result] => forumtopicabcdid0000000000000002 ) 
    public static function saverule($params){
        $result=array();
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($youxiduo_admin['id'])){
            $params['uid']=$youxiduo_admin['id'];
            $url=!empty($params['tid']) ? 'modify_topic' : 'post_topic';
            $result=Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::BBS_API_URL).$url,'POST');
            if($result['errorCode'] == 0){
                return SystemSettingModel::setConfig('product_wish_rule',array('rule_id'=>$result['result']));
            }else{
                self::errorHtml();
            }
        }
    }

    //查询订单接口
    public static function ProductOrderList($params){
        $params_ = array('pageIndex','pageSize','orderId','biller','receiveAccount','payType','payer','billTimeBegin','billTimeEnd','orderStatus'
        ,'payTimeBegin','payTimeEnd','active');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'order/query_order');

    }

    //修改订单
    public static function modifyOrder($params){
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::API_URL_CONF).'order/modify_order','POST');
    }
    //导入卡密接口
    public static function importcard($params,$file,$platform='android'){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['editor']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
        $params['cardAmountStr'] = 0;
        return Utility::loadByHttp(Config::get(self::VIRTUAL_CARD_URL).'virtualcard/importcard',$params,'POST', 'json',$platform,$file);
    }

    public static function getcardurl(){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['editor']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
        //$params['url']=Config::get(self::VIRTUAL_CARD_URL).'virtualcard/importcard';
        return $params;
    }
    //卡密种类添加 修改
    public static function addeditcard($params,$url){
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($params['id'])){
            $type='GET';
            $params['modifier']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
        }else{
            $type='POST';
            $params['creator']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
        }//print_r($params);exit;
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).$url,$type);
    }

    //开启关闭商品活动
    public static function OpenOrCloseProductactivity($params,$op=true){
        //print_r(Config::get(self::API_URL_CONF));exit;
        if($op){
            return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::API_URL_CONF).'product/close_productactivity');
        }else{
            return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::API_URL_CONF).'product/open_productactivity');
        }
    }
    //查询卡密列表
    public static  function  getvirtualcardcodelist($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/info_list');
    }
    //卡密状态上架  下架
    public static function changestatuscard($params){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['modifier']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
        return Utility::preParamsOrCurlProcess($params,array('id','onOrOff','modifier'),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/changestatus','POST');
    }
    
    //卡密库存分配
    public static function distributioncard($params)
    {
        return Utility::preParamsOrCurlProcess($params,array('cardCode','cardNumber','requestFrom'),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/distribution');
    }
    
    //卡密库存释放
    public static function release_distributioncard($params)
    {
        return Utility::preParamsOrCurlProcess($params,array('cardNumber','requestFrom'),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/release_distribution');
    }
    

    /**
     * 标记
     * @param $uid
     * @param $productCode
     * @param $sign
     * @return bool|mixed|string
     */
    public static function setSign($uid,$productCode,$sign,$platform='ios'){
        $params = array('uid'=>$uid,'productCode'=>$productCode,'sign'=>$sign,'platform'=>$platform);
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'product/sign',$params);
    }

    public static function is_top($params)
    {
        $params_ = array('isTop','productCode','modifier','platform');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/update_product_reform','POST');
    }


    /**
     * 修改商品属性
     * @param $params
     * @return array|bool|mixed|string
     */
    public static function updateProductextra($params){
        $params_ = array('top','hot','newest','recommend','lightDelivery','productCode');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'extra/update_productextra','POST');
    }



    //商城填写表单展示模板
    public static function getTemplate($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/template');
    }
    //修改商品收货信息表单模板的接口
    public static function modify_form($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/modify_form','POST');
    }
    //增加商品收货信息表单模板的接口
    public static function add_form($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/add_form','POST');
    }
    //删除商城表单列表
    public static function deleteForm($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/deleteForm');
    }
    //获取商品推荐位
    public static function RecommendList($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/recommend');
    }
}
