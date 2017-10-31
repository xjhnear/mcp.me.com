<?php

namespace modules\v4_shareaccount\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Shareaccount\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Cache\CacheService;
use Youxiduo\Helper\DES;
use Youxiduo\V4\User\UserService;
use modules\v4user\models\UserModel;

/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class AccountController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_shareaccount';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $params = $data = array();
        $params['pageNow'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['type'] = Input::get('type',1);
        
        $typeList = array('1'=>'中国','2'=>'海外');

        $result=ProductService::searchProductList($params);
//         print_r($result);exit;
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                $item['data'] = json_decode($item['data'],true);
                if(isset($item['data']['sharedimg']) && $item['data']['sharedimg']){
                    $item['data']['sharedimg'] = Utility::getImageUrl($item['data']['sharedimg']);
                }
            }
            $data=self::processingInterface($result,$params);
            $data['typeList'] = $typeList;
            return $this->display('list',$data);
        }
        $data['typeList'] = $typeList;
        return $this->display('list',$data);
    }

    public function getAdd($id=''){
        if($id){
            $pro_res = ProductService::searchProductList(array('id'=>$id));
            if($pro_res['errorCode'] || !$pro_res['result']) return $this->back('无效数据');
            $pro_info = $pro_res['result'][0];
            $pro_info['data'] = json_decode($pro_info['data'],true);
            if(isset($pro_info['data']['sharedimg']) && $pro_info['data']['sharedimg']){
                $pro_info['data']['sharedimg'] = Utility::getImageUrl($pro_info['data']['sharedimg']);
            }
            $pro_info['typeList'] = array('1'=>'中国','2'=>'海外');
            return $this->display('add',array('info'=>$pro_info));
        } else {
            $pro_info['typeList'] = array('1'=>'中国','2'=>'海外');
            return $this->display('add',array('info'=>$pro_info));
        }
    }
    
    public function postAdd(){
        $input = Input::all();
        $typeList = array('1'=>'中国','2'=>'海外');
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');

        if(!empty($input['id'])){
            $params['id'] = $input['id'];
        }
        $params['type'] = $input['type'] ? $input['type'] : 1;
        $params['useFor'] = $typeList[$params['type']];
        $data = array(
            'sharedname' => $input['sharedname'],
            'sharedusername' => $input['sharedusername'],
            'sharedpassword' => $input['sharedpassword'],
            'gid' => $input['gid'],
            'sharedimg' => $input['old_sharedimg'],
        );
        
        if(!empty($input['sharedimg'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['sharedimg']);
            $data['sharedimg'] = $path;
        }
        $params['data'] = json_encode($data);
        
        $result = ProductService::addProduct($params,false);
        if($result['errorCode']==0){
            return $this->redirect('v4shareaccount/account/list')->with('global_tips','编辑成功');
        }else{
            return $this->back()->withInput()->with('global_tips','编辑失败');
        }
    }
    
    public function getAjaxDel($id=''){
        if(!$id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $result = ProductService::addProduct(array('id'=>$id,'active'=>'false'));
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    //编辑活动分享设置
    public function getInfo()
    {
        $result=ProductService::searchProductList(array('type'=>3));

        if($result['errorCode']==0 && count($result['result'])>0){
            $pro_info = $result['result'][0];

            return $this->display('info',array('info'=>$pro_info));
        } else {
            return $this->display('info',array('info'=>array()));
        }
    }
    
    //编辑活动修改
    public function postInfo()
    {
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');

        if(!empty($input['id'])){
            $params['id'] = $input['id'];
        }
        $params['type'] = 3;
        $params['useFor'] = '使用说明';
        $params['data'] = $input['data'] ? $input['data'] : '';
        
        $result = ProductService::addProduct($params,false);
        if($result['errorCode']==0){
            return $this->redirect('v4shareaccount/account/info')->with('global_tips','编辑成功');
        }else{
            return $this->back()->withInput()->with('global_tips','编辑失败');
        }
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