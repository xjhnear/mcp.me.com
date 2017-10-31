<?php

namespace modules\v4_product\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Imall\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;

use Youxiduo\Helper\DES;
use Youxiduo\V4\User\UserService;
/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class GoodsController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_product';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $arr=array('sign');
        $uid=$this->getSessionData('youxiduo_admin');
        foreach($arr as $v){
            if(Input::get($v) && Input::get($v) != 'false'){
                $params[$v]='true';
                $params['signer']= $v=='sign' ? $uid['id'] : '';
            }else{
                $params[$v]='false';
            }
        }
        //$params['sortType']="Create_Time";
        $params['signer']=$uid['id'];
        $arr_=array('gameId','productName','categoryId');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }
        if(Input::get("isOnshelf") == 3){
            $params['isOnshelf'] = "false";
        }else if(Input::get("isOnshelf") == 2){
            $params['isOnshelf'] = "true";
        }

        if(Input::get('productStock')){
            $params['productStock'] = 0;
        }

        if(Input::get('createTimeBegin')){
            $params['createTimeBegin']=Input::get('createTimeBegin').' 00:00:00';
        }
        if(Input::get('createTimeEnd')){
            $params['createTimeEnd']=Input::get('createTimeEnd').' 23:59:59';
        }
        $params['productType']='0,1,3,4';
        if(!empty($params['gameId'])){
            //根据游戏ID查询关联活动
            $data=ProductService::getMallGameRelation(array('isActive'=>1,'gid'=>$params['gameId'],'genre'=>self::GENRE));//print_r($data);
            if($data['errorCode']==0){
                    //根据游戏ID获取活动ID
                    $ids=MyHelp::get_Ids($data['result'],'productCode');
                    if(!empty($ids)) $params['productCode']=$ids;
            }
        }

        $result=ProductService::searchProductList($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            $arr=array();
            foreach($data['datalist'] as $key=>$value){
                if(!empty($value['img'])){
                    $img=json_decode($value['img'],true);
                    $data['datalist'][$key]['listpic']=!empty($img['listPic']) ? $img['listPic'] :'';
                }
                $arr[]=$value['productCode'];
            }
            $ids=join(',',$arr);
            if(!empty($ids)){
                $arr['productCode']=$ids;
                $arr['genre']=1;
                $arr['isActive']=1;
                $result=ProductService::getMallGameRelation($arr);
                if($result['errorCode']==0){
                    $arr=array();
                    foreach($result['result'] as $val_){
                        $arr[$val_['productCode']]=$val_['gid'];
                    }
                    foreach($data['datalist'] as $key=>&$value)
                    {
                        if(!empty($arr[$value['productCode']])){
                             $gameName=GameService::getOneInfoById($arr[$value['productCode']],'ios');
                             $value['gname']=!empty($gameName['gname']) && $gameName['gname']!='g'  ? $gameName['gname'] : '';
                        }
                        if(!empty($value['totalCount']) && !empty($value['restCount'])){
                              $value['tr']=$value['totalCount']-$value['restCount'];
                        }
                    }

                }else{
                    $this->errorHtml($result);
                }
            }
            if(Input::get("categoryName")){
                $data['categoryName'] = Input::get("categoryName");
            }

            if(Input::get('productStock')){
                 $data['myproductStock']='true';
            }

            return $this->display('goods-list',$data);
        }
        return $this->display('goods-list',$data);
    }
    //发放商品
    public function getFafang($productCode='')
    {
        $input=Input::only('productCode',"uids");
        if(empty($input['uids'])){
            return $this->json(array('errorCode'=>1,'errortxt'=>'抱歉，发放失败用户缺失!'));
        }
        $result=ProductService::grant_product($input);
        if($result['errorCode']==0)
        {
             return $this->json(array('errorCode'=>2,'errortxt'=>''));
        }
        return $this->json(array('errorCode'=>1,'errortxt'=>'数据访问失败!'));
    }


    public function getQuery($productCode='')
    {
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;
        $data=array("accountId","productCode","addTimeBegin","addTimeEnd","productPriceBegin","productPriceEnd");
        foreach($data as $value){
            if(Input::get($value)){
                $params[$value]=Input::get($value);
            }
        }
        $uid=$this->getSessionData('youxiduo_admin');
        $params['signer']=$uid['id'];
        if(empty($params['productCode'])) $params['productCode']=$productCode;
        $result=ProductService::query($params);
        if($result['errorCode']==0){
            $params=array();
            if(!empty($result['result'])){
                foreach($result['result'] as $key=>&$val){
                    $params[]=$val['accountId'];
                    $val['productInfo']=!empty($val['productInfo'])?DES::decrypt($val['productInfo'],11111111):'';
                }
            }
            $data=self::processingInterface($result,$params);
            if(!empty($params)){
                $params=UserService::getMultiUserInfoByUids(array_flip(array_flip($params)),'full');//print_r($params);exit;
                if(!empty($params)){
                    $data['userinfo']=array();
                    foreach($params as $val_){
                        $data['userinfo'][$val_['uid']]['nickname']=$val_['nickname'];
                    }
                }
            }
            $data['productCode']=!empty($productCode) ? $productCode : Input::get('productCode');
            return $this->display('query-list',$data);
        }

    }


    public function getTemplateSelectList()
    {
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =7;
        if(Input::get("templateName")){
            $params['templateName']=Input::get("templateName");
        }
        $result=ProductService::getFormList($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params,$params['pageSize']);
            $data['inputinfo']=$params;
            $html = $this->html('pop-template-list',$data);
            return $this->json(array('html'=>$html));
        }
    }
    /**
     * 标记
     * @param string $id
     * @param int $sign
     * @return mixed
     */
    public function getSign($id='',$sign=0){
        $uid=$this->getSessionData('youxiduo_admin');
        if(empty($id)  || empty($uid)){
             return $this->back()->with('global_tips','操作失败->参数缺失');
        }
        $result=ProductService::setSign($uid['id'],$id,$sign);

        if($result['errorCode']==0){
             return $this->redirect('v4product/goods/list')->with('global_tips','操作成功');
        }
        return $this->redirect('v4product/goods/list')->with('global_tips','操作失败');
    }

    //商品开启优惠
    public function getOpenproductactivity()
    {
        $input = Input::only('discountGamePrice','startTime','endTime','productCode');
        $biTian=array('productCode'=>'required','startTime'=>'required|date','endTime'=>'required|date');
        $message = array(
            'required' => '不能为空',
            'date' => '必须为日期',
        );
        $validator = Validator::make($input,$biTian,$message);
        if($validator->fails()){
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                $strerror[]=$message;
            }
            return $this->json(array('error'=>0,'errortxt'=>'参数错误'));
        }
        $input['discountPrice']=0;
        $input['onOrOff']='true';
        $input['isDiscount']='true';
        $url='product/add_productactivity';
        $result=ProductService::addProductActivity($input,$url);
        if($result['errorCode'] == 0){
             return $this->json(array('error'=>1,'errortxt'=>'操作成功'));
        }else{
             return $this->json(array('error'=>0,'errortxt'=>$result['errorDescription']));
        }
    }

    public function getProductAdd(){
       return $this->display('product-add');
    }

    public function postProductAdd(){
        $input = Input::all();
        print_r($input);
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        if($input['product_type']==0 && empty($input['card_code']))
        {
            return $this->back()->withInput()->with('global_tips','添加失败~如果选择卡密产品 请上传卡密');
        }
        $params = array(
            'productCode' => ProductService::getCode('p-code-','md5OrUniqid'),
            'productName' => $input['product_name'],
            'gameId' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
            'gid' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
            'gname' => $input['game_name'] ? $input['game_name'] : '',
            'categoryId' => $input['category_id'],
            'productType' => $input['product_type'],
            'currencyType'=>$input['currencyType'],
            'spUrl' => $input['sp_url'],
            'cardCode' => $input['card_code'],
            'limitType' => $input['limit_type'],
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'timeType' => $input['time_type'],
            'ruleLimit' => $input['rule_limit'],
            'discountGamePrice' => $input['discount_game_price'],
            'productGamePrice' => isset($input['product_display']) ? $input['product_game_price'] : false,
            'productDisplay' => isset($input['product_display']) ? 1 : 0,
             //'isDiscount' => isset($input['is_discount']) ? 'true' : false,
            'productSort' => $input['product_sort'],
            'productStock' => $input['product_stock'],
            'startTime' => date('Y-m-d H:i:s',strtotime($input['start_time'])),
            'endTime' => date('Y-m-d H:i:s',strtotime($input['end_time'])),
            'isOffTogether' => isset($input['off_together']) ? 'true' : 'false',
            'isTop' => !empty($input['isTop'])? 'true':'false',
            'productDesc' => !empty($input['productDesc'])? $input['productDesc']:'',
            'productSummary' => !empty($input['productSummary'])? $input['productSummary']:'',
            'productInstruction' => !empty($input['productInstruction'])? $input['productInstruction']:'',
            'isOnshelf' => !empty($input['isOnshelf'])? 'true':'false',
            'lightDelivery'=>!empty($input['lightDelivery']) ? 'true' : 'false',
            'tagId'=>!empty($input['label_id']) ? $input['label_id'] : false,
            'tagName'=>!empty($input['label_name']) ? $input['label_name'] : false,
            'templateId'=>!empty($input['templateId']) ? $input['templateId'] : false,
            'contactInfo'=>!empty($input['contactInfo']) ? $input['contactInfo'] : false,
            'biaoqianType' => $input['biaoqianType']
        );
        $params['isNeedTemplate']=!empty($params['templateId'])?'true':'false';
        $params['isDiscount']='false';
        $params['isNewest']='false';
        $params['isHot']='false';
        $params['isRecommend']='false';
        switch ($params['biaoqianType']) {
            case 1:
                # code...
                $params['isDiscount']='true';
                break;
            case 2:
                # code...
                $params['isNewest']='true';
                break;
            case 3:
                # code...
                $params['isHot']='true';
                break;
            case 4:
                # code...
                $params['isRecommend']='true';
                break;

        }
        unset($params['biaoqianType']);
        //没用的必传字段
        $params['productPrice'] = 0;
        $params['isExclusive'] = 'false';
        $params['inventedType'] = 0;
        $params['isBelongUs'] = 'true';
        $params['isNotice'] = 'false';
        //如果有折扣 就为折扣前的价格
        /***
        if(!empty($params['productGamePrice'])){
             $params['productGamePrice']=sprintf("%d",$params['discountGamePrice']/($params['productGamePrice']/100));
        }else{
             $params['productGamePrice']=$params['discountGamePrice'];
        }
         * ***/
        if(!empty($params['productGamePrice'])){
            $params['productGamePrice']=sprintf("%.1f",$params['discountGamePrice']/($params['productGamePrice']/100));
        }else{
            $params['productGamePrice']=$params['discountGamePrice'];
        }
        if(!empty($input['smallpic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['smallpic']);
            $params['productImgpath']['listPic'] = $path;
        }

        if(!empty($input['bigpic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['bigpic']);
            $params['productImgpath']['detailPic'] = $path;
        }
        $uid=$this->getSessionData('youxiduo_admin');
        $params['modifier']=$uid['username'];
        $params['modifyTime']=date('Y-m-d H:i:s');
        if(!empty($params['tagId']) && !empty($params['tagName']) ){
            $params['tagId']=explode(',',$params['tagId']);
            $params['tagName']=explode(',',$params['tagName']);
            $arr_=array();
            foreach($params['tagId'] as $key=>$val){
                $arr_[$key]['tagId']=$val;
                $arr_[$key]['tagName']=$params['tagName'][$key];
                $arr_[$key]['productCode']=$params['productCode'];
            }
            $params['tagRelList']=$arr_;
        }

        $result = ProductService::addProduct($params,false);

        if(!$result['errorCode']){
            return $this->redirect('v4product/goods/list')->with('global_tips','添加成功');
        }else{
            return $this->back()->withInput()->with('global_tips','添加失败');
        }
    }

    public function getProductEdit($p_code=''){
        if(!$p_code) return $this->back('数据错误');
        $pro_res = ProductService::searchProductList(array('productCode'=>$p_code));
        if($pro_res['errorCode'] || !$pro_res['result']) return $this->back('无效商品');
        $pro_info = $pro_res['result'][0];
        if(isset($pro_info['img']) && $pro_info['img']){
            $pro_info['img'] = json_decode($pro_info['img'],true);
            foreach($pro_info['img'] as &$row){
                $row = Utility::getImageUrl($row);
            }
        }
        $arr=array('','isDiscount','isNewest','isHot','isRecommend');
        $pro_info['biaoqianType']=0;
        foreach ($arr as $key => $value) {
            # code...
            if(!empty($pro_info[$value])) $pro_info['biaoqianType']=$key;

        }

        if(!empty($pro_info['tagRelList'])){
            $arr_=$arr_1=array();
            //print_r($pro_info['tagRelList']);
            foreach($pro_info['tagRelList'] as $value){
                $arr_[]=$value['tagId'];
                $arr_1[]=$value['tagName'];
            }
            //$pro_info['tagRelListValue']=join(',',$arr_);
            $pro_info['label_id']=join(',',$arr_);
            $pro_info['label_name']=join(',',$arr_1);
        }
        if(!empty($pro_info['templateId'])){
            $t=ProductService::getFormList(array('templateId'=>$pro_info['templateId']));
            if($t['errorCode'] == 0){
                $pro_info['templateName']=$t['result']['0']['templateName'];
            }
        }
        //exit;
        //如果有优惠价格算折扣
        if(!empty($pro_info['productDisplay']) && $pro_info['productDisplay']==1){
            $pro_info['productGamePrice']=sprintf("%.2f",$pro_info['discountGamePrice']/$pro_info['productGamePrice'])*100;
        }

        $rel_res = ProductService::getMallGameRelation(array('productCode'=>$pro_info['productCode'],'genre'=>self::GENRE,'isActive'=>1));
        $game_info = array();
        if(!$rel_res['errorCode'] && $rel_res['result']){
            $game_info = GameService::getOneInfoById($rel_res['result'][0]['gid'],self::GENRE_STR);
        }
        return $this->display('product-edit',array('info'=>$pro_info,'game'=>$game_info));
    }

    public function postProductEdit(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());

        $params = array(
            'productCode' => $input['productCode'],
            'productName' => $input['product_name'],
            'gameId' => $input['game_id'],
            'gid' => $input['game_id'],
            'gname' => $input['game_name'] ? $input['game_name'] : '',
            'categoryId' => $input['category_id'],
            'currencyType'=>$input['currencyType'],
            'productType' => !empty($input['product_type'])?$input['product_type']:0,
            'spUrl' => $input['sp_url'],
            'cardCode' => $input['card_code'],
            'limitType' => $input['limit_type'],
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'timeType' => $input['time_type'],
            'ruleLimit' => $input['rule_limit'],
            'discountGamePrice' => $input['discount_game_price'],
            'productGamePrice' => isset($input['product_display']) ? $input['product_game_price'] : false,
            'productDisplay' => isset($input['product_display']) ? 1 : 0,
            //'isDiscount' => isset($input['is_discount']) ? 'true' : 'false',
            'productSort' => $input['product_sort'],
            'productStock' => !empty($input['product_stock'])?$input['product_stock']:0,
            'startTime' => date('Y-m-d H:i:s',strtotime($input['start_time'])),
            'endTime' => date('Y-m-d H:i:s',strtotime($input['end_time'])),
            'isOffTogether' => isset($input['off_together']) ? 'true' : 'false',
            'isTop' => !empty($input['isTop'])? 'true':'false',
            'productDesc' => !empty($input['productDesc'])? $input['productDesc']:'',
            'productSummary' => !empty($input['productSummary'])? $input['productSummary']:'',
            'productInstruction' => !empty($input['productInstruction'])? $input['productInstruction']:'',
            'biaoqianType' => $input['biaoqianType'],
            'lightDelivery'=>!empty($input['lightDelivery']) ? 'true' : 'false',
            'templateId'=>!empty($input['templateId']) ? $input['templateId'] : false,
            'contactInfo'=>!empty($input['contactInfo']) ? $input['contactInfo'] : false,
            'tagId'=>!empty($input['label_id']) ? $input['label_id'] : false,
            'tagName'=>!empty($input['label_name']) ? $input['label_name'] : false,
        );
        $list_pic = $input['old_small_pic'];
        $detail_pic = $input['old_big_pic'];
        //如果有折扣 就为折扣前的价格
        if(!empty($params['productGamePrice'])){
             $params['productGamePrice']=sprintf("%.1f",$params['discountGamePrice']/($params['productGamePrice']/100));
        }else{
             $params['productGamePrice']=$params['discountGamePrice'];
        }

        if($input['smallpic']){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['smallpic']);
            $list_pic = $path;
        }

        if($input['bigpic']){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['bigpic']);
            $detail_pic = $path;
        }

        $params['productImgpath'] = array('listPic'=>$list_pic,'detailPic'=>$detail_pic);
        $params['productStock']=0;
        $uid=$this->getSessionData('youxiduo_admin');
        $params['modifier']=$uid['username'];
        $params['modifyTime']=date('Y-m-d H:i:s');
        $params['isDiscount']='false';
        $params['isNewest']='false';
        $params['isHot']='false';
        $params['isRecommend']='false';
        switch ($params['biaoqianType']) {
            case 1:
                # code...
                $params['isDiscount']='true';
                break;
            case 2:
                # code...
                $params['isNewest']='true';
                break;
            case 3:
                # code...
                $params['isHot']='true';
                break;
            case 4:
                # code...
                $params['isRecommend']='true';
                break;

        }
        unset($params['biaoqianType']);
        /***
        if(!empty($params['tagRelList'])){
            $params['tagRelList']=explode(',',$params['tagRelList']);
            $arr_=array();
            foreach($params['tagRelList'] as $key=>&$value){
                list($id,$val)=explode('-',$value);
                $arr_[$key]['tagName']=$val;
                $arr_[$key]['tagId']=$id;
                $arr_[$key]['productCode']=$params['productCode'];
            }
            $params['tagRelList']=$arr_;
        }
         * **/
        if(!empty($params['tagId']) && !empty($params['tagName']) ){
            $params['tagId']=explode(',',$params['tagId']);
            $params['tagName']=explode(',',$params['tagName']);
            $arr_=array();
            foreach($params['tagId'] as $key=>$val){
                $arr_[$key]['tagId']=$val;
                $arr_[$key]['tagName']=$params['tagName'][$key];
                $arr_[$key]['productCode']=$params['productCode'];
            }
            $params['tagRelList']=$arr_;

        }
        unset($params['tagId'],$params['tagName']);
        $params['isNeedTemplate']=!empty($params['templateId'])?'true':'false';

        $result = ProductService::editProduct($params);
        if(!$input['old_game_id'] && $params['gameId']) {
            //添加关系
            $rel_data = array('categoryId'=>$params['categoryId'],'createTime'=>date('Y-m-d H:i:s',time()),'gid'=>$params['gameId'],'productCode'=>$params['productCode'],'genre'=>self::GENRE);
            $relat_res = Utility::preParamsOrCurlProcess($rel_data,array('gid','productCode','genre','createTime'),Config::get(ProductService::RLT_URL_CONF).'save_mall_game','POST');

        }elseif($input['old_game_id'] && $params['gameId']) {
            //更新
            $rel_data = array('productCode'=>$params['productCode'],'gid'=>$params['gameId'],'genre'=>self::GENRE);
            $relat_res = Utility::preParamsOrCurlProcess($rel_data,array('gid','productCode','genre'),Config::get(ProductService::RLT_URL_CONF).'update_gid_by_mall','POST');
        }

        if($result['errorCode']==0 && $result['result'] && $relat_res['errorCode']==0){
            return $this->redirect('v4product/goods/list')->with('global_tips','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }

    //商品开启关闭
    public function getCloseproductactivity($aid='')
    {
        if(empty($aid)){
            return $this->redirect('v4product/goods/list')->with('global_tips','抱歉，活动编号缺失');
        }
        $params['activityId']=$aid;
        $result=ProductService::OpenOrCloseProductactivity($params,false);
        if($result['errorCode'] == 0){
            return $this->redirect('v4product/goods/list')->with('global_tips','操作成功');
        }else{
            return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
        }
    }
    //追加数量
    public function getZjsl(){
        $input = Input::all();
        $rule = array('productCode'=>'required','productStock'=>'required');
        $message = array(
            'productCode.required' => 'productCode不能为空',
            'productStock.required' => 'productStock不能为空',
        );
        $valid = Validator::make($input,$rule,$message);
        if($valid->fails()) return $this->json(array('errorCode'=>0,'msg'=>$valid->messages()->first()));
        $uid=$this->getSessionData('youxiduo_admin');
        $input['modifier']=$uid['username'];
        $input['modifyTime']=date('Y-m-d H:i:s');
        $result = ProductService::editProduct($input);
        if($result['errorCode']!=0){
            return $this->json(array('errorCode'=>0,'msg'=>'接口调用失败~!'));
        }
        return $this->json(array('errorCode'=>1,'msg'=>'成功'));
    }


    //商品上架 下架方法
    /**
     * @param $goods_id
     * @param $status
     * @return mixed
     */
    public function getStatus($goods_id,$status)
    {
        $params=array('productCode'=>$goods_id);
        $uid=$this->getSessionData('youxiduo_admin');
        $params['modifier']=$uid['username'];

        if(!$status){
            # code...
            $result=ProductService::offsaleProduct($params);
        } else {
            # code...
            $result=ProductService::onsaleProduct($params);
        }
        return $this->redirect('v4product/goods/list')->with('global_tips','商品属性修改成功');
    }

    /****商城列表删除***/
    public function getGoodsdelete($id=0)
    {
        if(empty($id)){
            //return $this->redirect('v4aproduct/goods/list')->with('global_tips','删除出错-Code丢失');
            return $this->json(array('error'=>1));
        }
        $result=ProductService::DeleteProduct(array('productCode'=>$id));
        if($result['errorCode']==0){
            //return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品删除成功');
            return $this->json(array('error'=>0));
        }
        return $this->json(array('error'=>1));
    }



    /**视图：商品种类列表**/
    public function getCateList()
    {
        $data = array('pageIndex'=>Input::get('page'),'pageSize'=>15);
        $result=ProductService::queryCategory($data);//print_r($result);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data,15);
            return $this->display('cate-list',$data);
        }
        $this->errorHtml($result);
    }

    /** 视图： 商品订单列表**/
    public function getProductOrderList()
    {
        $input=Input::only('biller','orderStatus');
        $data=array('pageIndex'=>Input::get('page',1),'pageSize'=>10,'active'=>'true');
        if(!empty($input['biller'])){
            $data['biller'] =$input['biller'];
        }
        if(!empty($input['orderStatus']) || is_numeric($input['orderStatus'])){
            $data['orderStatus'] =$input['orderStatus'];
        }
        $result=ProductService::ProductOrderList($data);//print_r($result);exit;
        if($result['errorCode']==0 ){
            $data=self::processingInterface($result,$data);
            return $this->display('order-list',$data);
        }
        $this->errorHtml($result);
    }

    /**视图：商品订单修改**/
    public function getOrderAddEdit($orderid="")
    {
        if(!empty($orderid)){
            $data=array('orderId'=>$orderid);
            $result=ProductService::ProductOrderList($data);

            if($result['errorCode']==0 ){
                $data['goods']=$result['result']['0'];
                return $this->display('order-edit',$data);
            }
           $this->errorHtml($result);
        }
    }




    /**视图：父商品种类列表的查询**/
    public function  getCateListSelect($currencyType=0)
    {
        $params = array('pageIndex'=>Input::get('page',1));
        $params['categoryName']=Input::get('keyword','');
        $params['currencyType']=$currencyType;
        if($currencyType==1){
            $params['currencyType']=1;
        }
        $params['pageSize']=6;
        if(Input::get('type')){
            $params['type']=Input::get('type');
        }
        $result=ProductService::queryCategory($params);//print_r($result);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params,6);
            if(Input::get('type')){
                $data['cate']['type']=Input::get('type');
            }else{
                foreach ($data['datalist'] as $key => $value) {
                    # code...
                    if($value['id']==0){
                        unset($data['datalist'][$key]);
                    }
                }
            }
            $data['inputinfo']=$params;
            $html = $this->html('pop-cate-list',$data);
            return $this->json(array('html'=>$html));
        }
        $this->errorHtml($result);
    }

    /**视图：添加/修改 商品种类  **/
    public function getCateAddEdit($cate_id=0)
    {

        $data=$datainfo= array();
        $datainfo['isBelongUs']='true';
        if(!empty($cate_id)){
            $data['categoryId']=$cate_id;
            $result=ProductService::queryCategory($data);
            if($result['errorCode'] == 0 ){//不为0就在查询一次

                $datainfo['cate']=$result['result']['0'];
                if(!empty($datainfo['cate']['categoryImgpath'])){
                    $datainfo['cate']['xcategoryImgpath']=strstr($datainfo['cate']['categoryImgpath'], '/userdirs/');
                }
                $datainfo['cate']['isBelongUs']=!empty($datainfo['isBelongUs']) ? 'true' : 'false';

                if(!empty($datainfo['cate']['parentId']) && $datainfo['cate']['parentId'] != 0){
                    $data['categoryId']=$datainfo['cate']['parentId'];
                    $result=ProductService::queryCategory($data);

                    if($result['errorCode'] != 0){
                        self::error_html($result);
                    }
                    $datainfo['cate']['pcategoryName']=$result['result']['0']['categoryName'];
                }else{
                    //为0就是自身根节点
                    $datainfo['cate']['pcategoryName']=$datainfo['cate']['categoryName'];
                }
            }else{
               $this->errorHtml($result);
            }

        }

        return $this->display('cate-edit',$datainfo);
    }



    //$params,array('productCode','top','hot','newest','recommend','lightDelivery')
    /**视图上修改该商品属性 **/
    public function getProductOperator($id,$type,$val=0){
        $val = (!empty($val)) ? 'false' : 'true' ;
        $data=array('productCode'=>$id);
        switch ($type) {
            case 'isrecommend':
                # code...
                $data['recommend']=$val;
                break;
            case 'ishot':
                # code...
                $data['hot']=$val;
                break;
            case 'isnewest':
                # code...
                $data['newest']=$val;
                break;
            case 'top':
                # code...
                $data['isTop']=$val;
                break;
            case 'lightDelivery':
                $data['lightDelivery']=$val;
                break;
            default:
                # code...
                return $this->back()->with('global_tips','商品修改属性失败');
                break;
        }
        $uid=$this->getSessionData('youxiduo_admin');
        $data['modifier']=$uid['username'];
        $result=ProductService::update_product_reform($data);
        if($result['errorCode']==0){
            return $this->redirect('v4product/goods/list')->with('global_tips','商品类型修改成功');
        }
      return $this->redirect('v4product/goods/list')->with('global_tips','商品类型修改失败');
    }

    /** 增加/修改 商品种类  **/
    public function postCateAddEdit($cate_id=0){
        $input = Input::all();
        $biTian=array('categoryName');
        $url='product/add_category';
        //如果是数据修改
        if(!empty($_POST['id'])){
            $input  = $input + array('Id'=>Input::get('id'));
            $biTian = $biTian + array('Id');
            $url='product/update_category';
            $uid=$this->getSessionData('youxiduo_admin');
            $input['modifier']=$uid['username'];
            $input['modifyTime']=date('Y-m-d H:i:s');
            //if(Input::get('xcategoryImgpath')) $input['categoryImgpath']=Input::get('xcategoryImgpath');
        }
        foreach($biTian as $key => $value){
            if(empty($input[$value]) && !is_numeric($input[$value])){
                return $this->back()->with('global_tips','商品种类（增加/修改）失败');
            }
        }
        $pic=!empty($input['old_categoryImgpath'])?$input['old_categoryImgpath']:'';
        if(Input::hasFile('categoryImgpath')){
            $dir = '/userdirs/mall/category/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,Input::file('categoryImgpath'));
            $pic= $path;
        }
        $input['categoryImgpath']=$pic;
        unset($input['old_categoryImgpath']);
        $input['parentId']=0;
        $uid=$this->getSessionData('youxiduo_admin');
        $input['modifier']=$uid['username'];
        $input['modifyTime']=date('Y-m-d H:i:s');
        $result=ProductService::addEditCate($input,$url);
        if($result['errorCode'] == 0){
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','商品种类（增加/修改）成功');
        }else{
            $this->errorHtml($result);
        }
    }

    public function getCateDelect($id=0)
    {
        if(empty($id)){
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','参数丢失');
        }
        $result=ProductService::hide_category($id);
        if($result['errorCode'] == 0){
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','操作失败');
        }
    }

    public function getCateTop($id)
    {
        if(empty($id)){
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','参数丢失');
        }
        $result=ProductService::top_category($id);
        if($result['errorCode'] == 0){
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4product/goods/cate-list')->with('global_tips','操作失败');
        }
    }



    public function postImport()
    {
        if(!Input::get('tmp'))  return json_encode(array('state'=>0,'msg'=>'卡密文件不存在'));
        $input['cardCode']=Input::get('datacode');
        if(empty($input['cardCode'])){
            $input['cardCode']= md5(uniqid('cardCode'));
            $input['cardType']=0;
            if(Input::get('cdesc')) $input['cardDesc']=Input::get('cdesc').' -- 商品卡密';
            $result=ProductService::addeditcard($input,'virtualcard/add');
            if($result['errorCode']) return json_encode(array('state'=>0,'msg'=>'上传出错'));
        }
        if(!empty($input['cardCode'])){
            unset($input['cardType']);
            $input['cardAmountStr']=0;
            if(Input::get('dataid') != ''){
                $input['needQuota']='true';
                $input['requestFrom']=Input::get('dataid');
            }

            $input['expTimeStr']= date('Y',time()) + 20 . '-' . date('m-d H:i:s'); //50年后日期
            $filename=Input::get('filename');
            $type=explode("." , $filename);
            $type=end($type);
            if($type == 'txt')
                $input['type']=$type;
            $file['importFile']=array('tmp_name'=>Input::get('tmp'),'type'=>$type,'name'=>$filename);

            $result=ProductService::importcard($input,$file);
            if(!$result['errorCode']){
                if(Input::get('type') == 1){
                    $input_['productStock']=Input::get("productStock");
                    $input_['productCode']=Input::get("productCode");
                    $result = ProductService::editProduct($input_); //print_r($result);
                    if($result['errorCode'] == 0){
                         return json_encode(array("state"=>1,'msg'=>'上传成功','cardCode'=>$input['cardCode']));
                    }
                }
                return json_encode(array("state"=>1,'msg'=>'上传成功','size'=>$result['result'],'cardCode'=>$input['cardCode']));
            }else{
                return json_encode(array('state'=>0,'msg'=>'上传失败'));
            }
        }
        return json_encode(array('state'=>0,'msg'=>'上传出错'));
    }

    public function postAjaxCheckFile(){
        if(!Input::hasFile('import_file')) return json_encode(array('state'=>0,'msg'=>'文件不存在'));
        $file = Input::file('import_file');
        $ext = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        if($ext != 'txt' && $ext != 'csv') return json_encode(array('state'=>0,'msg'=>'文件格式错误'));
        $dir = '/userdirs/filecount/';
        $path = storage_path() . $dir;
        $this->createFolder($path);
        $new_filename = date('YmdHis') . str_random(4);
        $file_path =$file->move($path,$new_filename . '.' . $ext);
        if(empty($file_path)){
            echo json_encode(array('state'=>0,'msg'=>'上传失败!'));
            exit;
        }
        $str = file_get_contents($file_path);//获得内容
        if($ext == 'txt'){
            $arr=array_filter(explode("\r\n",trim($str)));
        }else{
            //mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $arr=array_filter(explode(",",trim($str)));
        }
        echo  json_encode(array('state'=>1,'msg'=>'读取成功','line'=>count($arr),'file'=>array('tmp'=>$path.$new_filename.'.'.$ext,'filename'=>$filename)));
        exit;
    }

    private function createFolder($path)
    {
        if (!file_exists($path))
        {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }


    /***商城数据*****/
    public function getProductDataList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =17;
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value) {
             $val=Input::get($value);
             if(!empty($val) && $value == 'timeBegin'){
                $val.=' 00:00:00';
             }elseif(!empty($val) &&  $value == 'timeEnd'){
                $val.=' 23:59:59';
             }
             $data[$value]=$params[$value]=$val;
        }
        $result=ProductService::getExportquery($params);
        if($result['errorCode']==0){
            $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$params['pageSize']);
            unset($params['pageIndex']);
            $pager->appends($params);
            $data['pagelinks'] = $pager->links();
            $data['datalist'] = !empty($result['result'])?$result['result']:array();
            $data['url']=ProductService::getExportUrl();
            return $this->display('product-data',$data);
        }
        $this->errorHtml($result);
    }
    public function getExport()
    {
        $params=array();
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value) {
             $val=Input::get($value);
             if(!empty($val) && $value == 'timeBegin'){
                $val.=' 00:00:00';
             }elseif(!empty($val) &&  $value == 'timeEnd'){
                $val.=' 23:59:59';
             }
             $data[$value]=$params[$value]=$val;
        }
        $result=ProductService::getexport($params);
        if($result['errorCode']==0){
            return $this->json($result);
        }
        return $this->json($result);
    }

    public function getCardDownload()
    {
        $status=Input::get('status');
        $data['url']=ProductService::getReturnUrl();
        $uid=$this->getSessionData('youxiduo_admin');
        if(!empty($status)){
            $data['url']=ProductService::getReturnUrl().'?status='.$status.'&modifier='.$uid['username'];
        }
        $str=date("YmdHis").'卡密数据提取.txt';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str");
        readfile($data['url']);
        exit;
    }

    public function getProductDataDownload()
    {
        $params='';
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value){
             $val=Input::get($value);
             if(!empty($val) && $value == 'timeBegin'){
                $val.=' 00:00:00';
             }elseif(!empty($val) &&  $value == 'timeEnd'){
                $val.=' 23:59:59';
             }
             if(!empty($val)){
                 $params.=$value.'='.$val.'&';
             }

        }
        $params=rtrim($params,'&');
        $data['url']=ProductService::getExportUrl();
        if(!empty($params)){
            $data['url']=ProductService::getExportUrl().'?'.$params;
        }
        $str=date("YmdHis").'商城数据提取.xls';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str");
        readfile($data['url']);
        exit;
    }

    //用于生成符合前台页面SELECT标签的数组
    private static function array_select($result,$id,$val)
    {
        if($result){
            $selectInfo=array();
            foreach($result as $key=>$value){
                $selectInfo[$value[$id]]=$value[$val];
            }
            return $selectInfo;
        }
        return $result;
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