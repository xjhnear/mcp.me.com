<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Mall;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Youxiduo\V4\Game\GameService;
use Illuminate\Support\Facades\Session;
use modules\system\models\SystemSettingModel;

class ProductService extends BaseService{
    const API_URL_CONF = 'app.mall_api_url';
    const RLT_URL_CONF = 'app.mall_rlt_api_url';
    const MALL_MML_API_URL ='app.mall_mml_api_url';//http://121.40.78.19:8080/module_mall/
    const BBS_API_URL = 'app.bbs_api_url';
    const VIRTUAL_CARD_URL = 'app.virtual_card_url'; //http://121.40.78.19:8080/module_virtualcard/
    const MALL_API_ACCOUNT = 'app.account_api_url';  //http://121.40.78.19:8080/module_account/
    const MATERIAL_API_ACCOUNT =   'app.material_api_url';//'http://121.40.78.19:8080/module_material/';
    public static function Carddelete($cardinfoId)
    {
        $params['cardinfoId']=$cardinfoId;
        return Utility::preParamsOrCurlProcess($params,array('cardinfoId'),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/list_update');
    }

        

    public static function hide_category($id)
    {   
        $params['categoryId']=$id;
        return Utility::preParamsOrCurlProcess($params,array('categoryId'),Config::get(self::MALL_MML_API_URL).'product/hide_category');
    }
    
    public static function update_orderdeliver($params)
    {
        return Utility::preParamsOrCurlProcess($params,array('orderId'),Config::get(self::MALL_MML_API_URL).'order/update_orderdeliver');
    }


    public static function getExportUrl()
    {
        return Config::get(self::MALL_MML_API_URL).'product/export';
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
    
    public static function getExportquery($params)
    {   
        $params_=array('timeBegin','timeEnd','onOrOff','productName','pageIndex','pageSize');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/exportquery');
    }

    public static function getexport($params)
    {
        $params_=array('timeBegin','timeEnd','onOrOff','productName');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/export');
    }  

    public static function DeleteProduct($params)
    {   $params_=array('productCode');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/delete_product');
    }

    /**
     * @param $params
     * @param bool $genre
     * @return array|bool|mixed|null|string
     */
    public static function searchProductList($params,$genre=false){
        $params_ = array('id','pageIndex','pageSize','categoryId','productCode','gameId','productName','isOnshelf','sortType','gamePriceBegin','gamePriceEnd','productType','inventedType','isBelongUs','isNotice','createTimeBegin','createTimeEnd','isExclusive','hashValue','productStock','ascOrDesc','active');

        $p_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');

        return array('errorCode'=>$p_result['errorCode'],'totalCount'=>$p_result['totalCount'],'result'=>$p_result['result']);
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

    /**
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function addProduct($params){

        $params_ = array('gid','gname','detailPic','productName','productCode','categoryId','productGamePrice','productPrice','isExclusive','productType','isNeedTemplate','templateId','isBelongUs','creator','isNotice','productStock','productCost','exclusiveAccount','singleLimit','inventedType','cardCode','productSummary','productInstruction','productDesc','productImgpath','lightDelivery','productSort','hashValue','isOnshelf','isTop','isHot','isNewest','isRecommend','creator','cardCode','inventedType','extraReq','startTime','endTime');
        $datainfo=array(
                    'params'=>$params,//必填数组
                    'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
                    'url'=>Config::get(self::API_URL_CONF).'product/add_product',
                    'isadd'=>1
        );
        return self::addEdit($datainfo);
        
    }

     /**
     * 商品修改接口
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function modifyProduct($params){ 
             $params_ = array('detailPic','productId','productName','productCode','categoryId','productGamePrice','productPrice','productType','gname','gid','isBelongUs','isNotice','productStock','productCost','singleLimit','lightDelivery','exclusiveAccount','isExclusive','inventedType','productSummary','productInstruction','productDesc','productImgpath','isNeedTemplate','templateId','productSort','hashValue','isOnshelf','cardCode','startTime','endTime','extraReq');
             $datainfo=array(
                    'params'=>$params,//必填数组
                    'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
                    'url'=>Config::get(self::API_URL_CONF).'product/update_product_reform',
                    'isadd'=>0
             );

            return self::addEdit($datainfo);
    }


    /**
     * 商品修改接口
     * @param $params
     * @return array|bool|mixed|null|string
     */
    public static function editProduct($params){
        $params_ = array('productName','productCode','categoryId','gname','gid','productStock','productUsedStock','productGamePrice','productPrice',
            'productCost','isExclusive','singleLimit','cardCode','productSummary','productInstruction','productDesc','exclusiveAccount',
            'productImgpath','isBelongUs','productSort','isNotice','hashValue','active','startTime','endTime','extraReq');
        $datainfo=array(
                    'params'=>$params,//必填数组
                    'params_'=>$params_ ,//input:only 中获取的NAME值加上一些添加的name中没有的
                    'url'=>Config::get(self::API_URL_CONF).'product/update_product_reform',
        );
        return Utility::preParamsOrCurlProcess($datainfo['params'],$datainfo['params_'],$datainfo['url'],'POST');
    }


    /**
     * 生成code
     * @param string $prefix
     * @return string
     */
    public  function getCode($prefix = '' , $method = 'uniqid'){
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
     * @param string $url
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
    public static function addEditCate($input,$url){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $input['creator']=!empty($youxiduo_admin['id']) ? $youxiduo_admin['id'] : '';
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).$url,$input,'POST');
    }
    
  /**
    * 修改商品属性
    * @param $params
    * @return array
    **/
    public static function update_productextra($params=array()){

         return Utility::preParamsOrCurlProcess($params,array('productCode','top','hot','newest','recommend','lightDelivery'),Config::get(self::MALL_MML_API_URL).'extra/update_productextra','POST');   
    }
    
    /**
     * 上架商品
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function onsaleProduct($params=array()){

        return Utility::preParamsOrCurlProcess($params,array('productCode','hashValue'),Config::get(self::API_URL_CONF).'product/onsale_product');
    }

    /**
     * 下架商品
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function offsaleProduct($params=array()){
        return Utility::preParamsOrCurlProcess($params,array('productCode','hashValue'),Config::get(self::API_URL_CONF).'product/offsale_product');
    }

    /**
     * 查询商品种类接口
     * @param $params 参数数组
     * @return bool|mixed|string
     */
    public static function queryCategory($params){
        $params_=array('createTimeEnd','pageIndex','pageSize','hashValue','parentId','isBelongUs','categoryName','categoryId','createTimeBegin');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'product/query_category');
    }

    /**
    * 查询商品种类接口
    * @param $params 参数数组
    * @return bool|mixed|string
    **/
    public static function showCategory($params){
         return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::API_URL_CONF).'product/show_category');
    }

    /**查询卡编码接口
     * @param $params 参数数组
     * @return bool|mixed|string
     **/
    public static function getvirtualcardlist($params,$type=0){
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/list');
    }

    /**查询实物借口
     * @param $params 参数数组
     * @return bool|mixed|string
     **/
    public static function getmateriallist($params){

        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MATERIAL_API_ACCOUNT).'material/list');
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

    /**
     * 修改商品属性
     * @param $params
     * @return array|bool|mixed|string
     */
    public static function updateProductextra($params){
        $params_ = array('top','hot','newest','recommend','lightDelivery','productCode');
        return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'extra/update_productextra','POST');
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
   			$params_ = array('hasAddress','pageIndex','orderDesc','pageSize','orderId','biller','receiveAccount','payType','payer','billTimeBegin','billTimeEnd','orderStatus'
   										 ,'payTimeBegin','payTimeEnd','active');
   			return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_MML_API_URL).'order/query_order');
   	
   }
   
   //修改订单
   public static function modifyOrder($params){
   		return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'order/modify_order','POST');
   	}
    //导入卡密接口
    public static function importcard($params,$file){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['editor']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        return Utility:: loadByHttp(Config::get(self::VIRTUAL_CARD_URL).'virtualcard/importcard',$params,'POST', 'json','android' ,$file);
    }

    public static function getcardurl(){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['editor']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        //$params['url']=Config::get(self::VIRTUAL_CARD_URL).'virtualcard/importcard';
        return $params;
    }
    //卡密种类添加 修改
    public static function addeditcard($params,$url){
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($params['id'])){
            $type='GET';
            $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }else{
            $type='POST';
            $params['creator']=(!empty($youxiduo_admin['id'])) ? $youxiduo_admin['id'] : '';
            $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }//print_r($params);exit;
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).$url,$type);
    }

    //实物添加
    public static function addMaterial($params){
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($params['id'])){
            $type='GET';
            $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }else{
            $type='POST';
            $params['creator']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }//print_r($params);exit;
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MATERIAL_API_ACCOUNT).'material/add','POST');
    }

    //实物修改
    public static function updateMaterial($params){
        $youxiduo_admin=Session::get('youxiduo_admin');
        if(!empty($params['materialCode'])){
            $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }else{
            $params['creator']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        }//print_r($params);exit;
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MATERIAL_API_ACCOUNT).'material/update','POST');
    }


    //开启关闭商品活动
    public static function OpenOrCloseProductactivity($params,$op=true){
        //print_r(Config::get(self::MALL_MML_API_URL));exit;
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
        $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';   
        return Utility::preParamsOrCurlProcess($params,array('id','onOrOff','modifier','isActive'),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/list_update');
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
    
    //实物状态上架  下架
    public static function changestatus($params){
        $youxiduo_admin=Session::get('youxiduo_admin');
        $params['modifier']=(!empty($youxiduo_admin['username'])) ? $youxiduo_admin['username'] : '';
        return Utility::preParamsOrCurlProcess($params,array('id','onOrOff','modifier','isActive'),Config::get(self::MATERIAL_API_ACCOUNT).'material/changestatus',"POST");
    }
    //获取商城表单列表
    public static function  getFormList($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/getForm');
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

    //增加商品推荐位
    public static function addrecommend($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/add_recommend','POST');
    }
    //修改商品推荐位
    public static function modifyrecommend($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/modify_recommend','POST');
    }
    //查询用户交易记录
    public static function query($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
    }
    //礼包库 标记移除添加接口
    public static function getSign($uid,$cardcode='',$sign=0)
    {   
        $params['uid']=$uid;
        $params['cardCode']=$cardcode;   
        $params['sign']=$sign;
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::VIRTUAL_CARD_URL).'virtualcard/sign');
    }

    //解绑
    public static function release($params)
    {
        return Utility::preParamsOrCurlProcess($params,array_keys($params),Config::get(self::MALL_MML_API_URL).'product/release','GET');
    }

}
