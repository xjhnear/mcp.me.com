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

class GoodsController extends BackendController
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
//         $params['platform'] = Input::get('platform','ios');
        $params['pageSize'] =10;
        $goodstypeList = array(''=>'全部','1'=>'钻石','2'=>'任务物品','3'=>'礼包','7'=>'组合道具');

        $arr_=array('goodsname','goodstype');
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
        $result=ProductService::searchProductList($params);
//         print_r($params);exit;
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                if(isset($item['picurl']) && $item['picurl']){
                    $item['picurl'] = Utility::getImageUrl($item['picurl']);
                }
            }
            $data=self::processingInterface($result,$params);
            $data['goodstypeList'] = $goodstypeList;
            return $this->display('goods-list',$data);
        }
        $data['goodstypeList'] = $goodstypeList;
        return $this->display('goods-list',$data);
    }
    //发放商品
    public function getFafang($productCode='')
    {
        $input = Input::all();
        
        $params['distributeType'] = $input['ff_type'];
        $params['distributeTargetType'] = $input['ff_boject'];
        $params['distributeNumberType'] = $input['ff_count'];
        $params['distributeGoodsId'] = $input['goodsid'];
        if ($params['distributeType'] == 1) {
            $params['distributeTime'] = $input['ff_time'];
        } else {
            if ($params['distributeType'] == 3) {
                $params['appVersion'] = 'v'.$input['appversion'];
            }
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

        $result=ProductService::grant_product($params);
//         print_r($input);
//         print_r($result);die;
        if($result['errorCode']==0)
        {
             return $this->json(array('errorCode'=>2,'errortxt'=>''));
        }
        return $this->json(array('errorCode'=>1,'errortxt'=>'数据访问失败!'));
    }

    public function getProductAdd(){
       $data['goodstypeList'] = array('1'=>'钻石','2'=>'任务物品','7'=>'组合道具');
       return $this->display('product-add',$data);
    }

    public function postProductAdd(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $params = array(
            'goodstype' => $input['goodstype'] ? $input['goodstype'] : 1,
            'goodsname' => $input['goodsname'],
            'goodsshortname' => $input['goodsshortname'],
            'goodscontent' => isset($input['goodscontent']) ? $input['goodscontent'] : '',
            'gid' => $input['gid'] ? $input['gid'] : 0,
            'gname' => $input['gname'] ? $input['gname'] : 0,
            'diamondtype' => $input['diamondtype'],
            'diamondnum' => $input['diamondnum'],
            'diamondmin' => $input['diamondmin'],
            'diamondmax'=>$input['diamondmax'],
            'taskid' => $input['taskid'],
            'taskname' => $input['taskname'],
            'mutexTaskId' => $input['mutexTaskId'],
            'isLine' => $input['task_isLine'] ? "true" : "false",
            'endtime' => date('Y-m-d H:i:s',strtotime($input['endtime'])),
            'sortvalue' => isset($input['sortvalue']) ? $input['sortvalue'] : 0,
            'operator' => $uid['username'],
            'createtime' => date("Y-m-d H:i:s",time()),
            'updatetime' => date("Y-m-d H:i:s",time()),
            'platform' => 'ios',
        );
        
        if ($params['diamondtype'] == '2') {
            unset($params['diamondnum']);
        } else {
            unset($params['diamondmin']);unset($params['diamondmax']);
        }
        
        if ($input['goodstype'] == '3' && isset($input['card_code'])) {
            $params_o['cardCode'] = $input['card_code'];
            $params_o['cardNumber'] = $input['totalCount'];
            $params_o['requestFrom'] = Utility::getUUID();
            $result_o=IProductService::distributioncard($params_o);
            if($result_o['errorCode']!=0){
                return $this->back($result_o['errorDescription']);
            }
            $params['giftId'] = $params_o['requestFrom'];
            $params['giftName'] = $input['card_des'];
            $params['storeNum'] = $input['totalCount'];
        } elseif ($input['goodstype'] == '2') {
            if($input['subtaskid']){
                $params['subTaskId'] = $input['subtaskid'];
            }
            if($input['subtaskname']){
                $params['subTaskName'] = $input['subtaskname'];
            }
        } elseif ($input['goodstype'] == '7') {
            if($input['ids']){
                $params['subGoodsIds'] = $input['ids'];
            }
        }

        if(!empty($input['picurl'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['picurl']);
            $params['picurl'] = $path;
        }
        
        $result = ProductService::addProduct($params,false);
        if($result['errorCode']==0){
            $data =array();
            if(empty($input['gid']) || $input['gid'] == ''){
                $input['gid'] = '0';
            }
            return $this->redirect('v4backpack/goods/list')->with('global_tips','添加成功');
        }else{
            return $this->back()->withInput()->with('global_tips','添加失败');
        }
    }

    public function getProductEdit($p_code=''){
        if(!$p_code) return $this->back('数据错误');
        $pro_res = ProductService::searchProductList(array('goodsid'=>$p_code));
        if($pro_res['errorCode'] || !$pro_res['result']) return $this->back('无效物品');
        $pro_info = $pro_res['result'][0];
        if(isset($pro_info['picurl']) && $pro_info['picurl']){
            $pro_info['picurl'] = Utility::getImageUrl($pro_info['picurl']);
        }
        if($pro_info['goodstype'] == "7"){
            $subGoodsIds = explode(',', $pro_info['subGoodsIds']);
            $pro_info['backpack_children'] = array();
            foreach ($subGoodsIds as $item) {
                if ($item == "") continue;
                $res_children = ProductService::searchProductList(array('goodsid'=>$item));
                if(!$res_children['errorCode']&&$res_children['result']){
                    $pro_info['backpack_children'][] = $res_children['result'][0];
                }
            }
        }
        return $this->display('product-edit',array('info'=>$pro_info));
    }

    public function postProductEdit(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $params = array(
            'goodsid' => $input['goodsid'],
            'goodstype' => $input['goodstype'] ? $input['goodstype'] : 1,
            'goodsname' => $input['goodsname'],
            'goodsshortname' => $input['goodsshortname'],
            'goodscontent' => isset($input['goodscontent']) ? $input['goodscontent'] : '',
            'gid' => $input['gid'] ? $input['gid'] : 0,
            'gname' => $input['gname'] ? $input['gname'] : 0,
            'diamondtype' => $input['diamondtype'] ? $input['diamondtype'] : 1,
            'diamondnum' => $input['diamondnum'],
            'diamondmin' => $input['diamondmin'],
            'diamondmax'=>$input['diamondmax'],
            'giftId' => $input['card_code'],
            'giftName' => $input['card_des'],
            'storeNum'=>$input['totalCount'],
            'taskid' => $input['taskid'],
            'taskname' => $input['taskname'],
            'mutexTaskId' => $input['mutexTaskId'],
            'isLine' => $input['task_isLine'] ? "true" : "false",
            'endtime' => date('Y-m-d H:i:s',strtotime($input['endtime'])),
            'sortvalue' => isset($input['sortvalue']) ? $input['sortvalue'] : 0,
            'operator' => $uid['username'],
            'updatetime' => date("Y-m-d H:i:s",time()),
            'platform' => 'ios',
        );

        if ($input['goodstype'] == '3' && isset($input['card_code'])) {
            if (isset($input['card_code_old']) && $input['card_change']) {
                $params_o1['requestFrom'] = $input['card_code_old'];
                $result_o1=IProductService::release_distributioncard($params_o1);
                if($result_o1['errorCode']!=0){
                    return $this->back($result_o1['errorDescription']);
                }
            }
            if ($input['card_code'] && $input['card_change']) {
                $params_o['cardCode'] = $input['card_code'];
                $params_o['cardNumber'] = $input['totalCount'];
                $params_o['requestFrom'] = Utility::getUUID();
                $result_o=IProductService::distributioncard($params_o);
                if($result_o['errorCode']!=0){
                    return $this->back($result_o['errorDescription']);
                }
                $params['giftId'] = $params_o['requestFrom'];
                $params['giftName'] = $input['card_des'];
                $params['storeNum'] = $input['totalCount'];
            }
       } elseif ($input['goodstype'] == '2') {
            if($input['subtaskid']){
                $params['subTaskId'] = $input['subtaskid'];
            }
            if($input['subtaskname']){
                $params['subTaskName'] = $input['subtaskname'];
            }
       } elseif ($input['goodstype'] == '7') {
            if($input['ids']){
                $params['subGoodsIds'] = $input['ids'];
            }
        }
        
        if($input['picurl']){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['picurl']);
            $params['picurl'] = $path;
        }

        $result = ProductService::editProduct($params);
        if($result['errorCode']==0){
            return $this->redirect('v4backpack/goods/list')->with('global_tips','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }


    /****商城列表删除***/
    public function getGoodsdelete($id=0)
    {
        if(empty($id)){
            return $this->json(array('error'=>1));
        }
        $result=ProductService::DeleteProduct(array('goodsid'=>$id));
        if($result['errorCode']==0){
            //return $this->redirect('v4backpack/goods/list')->with('global_tips','删除成功');
            return $this->json(array('error'=>0));
        }
        return $this->json(array('error'=>1));
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

    /**视图 获取物品列表**/
    public function getCateBackpackSelect()
    {
        $data = $params = array();
        $params['pageNow'] = Input::get('page',1);
        $params['pageSize'] =6;
        if(Input::get('keyword')){
            $data['keyword']=$params['goodsname']=Input::get('keyword');
        }
        if(Input::get('goodstype')){
            $data['goodstype']=$params['goodstype']=Input::get('goodstype');
        }
        //        print_r($params);
        $result=ProductService::searchProductList($params);
        //        print_r($result);
        if($result['errorCode'] !==null ){
            
            $data=self::processingInterface($result,$data,$params['pageSize']);
    
            $html = $this->html('pop-backpack-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }

}