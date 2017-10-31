<?php

namespace modules\v4_box\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\Box\BoxService;
use Youxiduo\Imall\BoxService as IBoxService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\V4\User\UserService;
use modules\web_forum\controllers\TopicController;
use modules\v4user\models\UserModel;
use modules\game\models\GameModel;

/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class PlanController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_box';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $params = $data = array();
        $params['page'] = Input::get('page',1);
//         $params['platform'] = Input::get('platform','ios');
        $params['size'] =10;
        $params['active'] ='true';
        $params['platform'] ='ios';

        $arr_=array('platform','isOn','active');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        $result=BoxService::config_query($params);
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                if(isset($item['titlePic']) && $item['titlePic']){
                    $item['titlePic'] = Utility::getImageUrl($item['titlePic']);
                }
                if(isset($item['mastheadPic']) && $item['mastheadPic']){
                    $item['mastheadPic'] = Utility::getImageUrl($item['mastheadPic']);
                }
                if(isset($item['introduce']) && $item['introduce']){
                    $item['introduce'] = Utility::getImageUrl($item['introduce']);
                }
            }
//             print_r($result);exit;
            $data=self::processingInterface($result,$params);
            return $this->display('plan-list',$data);
        }
        return $this->display('plan-list',$data);
    }

    public function getPlanAdd(){
       for ($i=1;$i<=14;$i++) {
           $data['locationList'][$i] = $i;
       }
       return $this->display('plan-add',$data);
    }

    public function postPlanAdd(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $params = array(
            'title' => $input['title'],
            'location' => $input['location'],
            'introduce' => $input['introduce'],
            'regulation' => $input['regulation'],
            'platform' => $input['platform'],
            'limitNum' => $input['limitNum'],
            'cost' => $input['cost'],
            'isOn' => 'false',
            'active' => 'true',
        );
        
        if(!empty($input['titlePic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['titlePic']);
            $params['titlePic'] = $path;
        }
        if(!empty($input['mastheadPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['mastheadPic']);
            $params['mastheadPic'] = $path;
        }
        if(!empty($input['logos'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['logos']);
            $params['logos'] = $path;
        }
        
        $gameContext = array();
        $gameContext['gid'] = $input['game_id'];
        $gameContext['gname'] = $input['game_name'];
        //$gameContext['icon'] = $input['gicon'];
        if(!empty($input['gicon'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['gicon']);
            $gameContext['icon'] = $path;
        }
        $gameContext['desc'] = $input['gdesc'];
        $game = GameModel::getInfo($input['game_id']);
        $gameContext['url'] = $game['downurl'];
        $params['gameContext'] = json_encode($gameContext);
        
        $shareContext = array();
        $shareContext['title'] = $input['sharetitle'];
        $shareContext['content'] = $input['sharecontent'];
        if(!empty($input['shareicon'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['shareicon']);
            $shareContext['icon'] = $path;
        }
        $params['shareContext'] = json_encode($shareContext);

        $result = BoxService::config_save($params,false);
        if($result['errorCode']==0){
            return $this->redirect('v4box/plan/list')->with('global_tips','添加成功');
        }else{
            return $this->back()->withInput()->with('global_tips','添加失败');
        }
    }

    public function getPlanEdit($id=''){
        if(!$id) return $this->back('数据错误');
        $pro_res = BoxService::config_query(array('id'=>$id));
        if($pro_res['errorCode'] || !$pro_res['result']) return $this->back('无效数据');
        $pro_info = $pro_res['result'][0];
        if(isset($pro_info['titlePic']) && $pro_info['titlePic']){
            $pro_info['titlePic'] = Utility::getImageUrl($pro_info['titlePic']);
        }
        if(isset($pro_info['mastheadPic']) && $pro_info['mastheadPic']){
            $pro_info['mastheadPic'] = Utility::getImageUrl($pro_info['mastheadPic']);
        }
        if(isset($pro_info['logos']) && $pro_info['logos']){
            $pro_info['logos'] = Utility::getImageUrl($pro_info['logos']);
        }
        if(isset($pro_info['gameContext']) && $pro_info['gameContext']){
            $pro_info['gameContext'] = json_decode($pro_info['gameContext'],true);
            $pro_info['gicon'] = Utility::getImageUrl($pro_info['gameContext']['icon']);
            $pro_info['gdesc'] = $pro_info['gameContext']['desc'];
            $pro_info['game_name'] = isset($pro_info['gameContext']['gname'])?$pro_info['gameContext']['gname']:"";
            $pro_info['game_id'] = $pro_info['gameContext']['gid'];
        }
        if(isset($pro_info['shareContext']) && $pro_info['shareContext']){
            $pro_info['shareContext'] = json_decode($pro_info['shareContext'],true);
            $pro_info['shareicon'] = $pro_info['shareContext']['icon'];
            $pro_info['sharetitle'] = $pro_info['shareContext']['title'];
            $pro_info['sharecontent'] = $pro_info['shareContext']['content'];
        }
        for ($i=1;$i<=14;$i++) {
            $pro_info['locationList'][$i] = $i;
        }
        return $this->display('plan-edit',array('data'=>$pro_info));
    }

    public function postPlanEdit(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $params = array(
            'id' => $input['id'],
            'title' => $input['title'],
            'location' => $input['location'],
            'introduce' => $input['introduce'],
            'regulation' => $input['regulation'],
            'platform' => $input['platform'],
            'limitNum' => $input['limitNum'],
            'cost' => $input['cost'],
            'isOn' => 'false',
            'active' => 'true',
            'titlePic' => $input['old_titlePic'],
            'mastheadPic' => $input['old_mastheadPic'],
            'logos' => $input['old_logos'],
        );

        if(!empty($input['titlePic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['titlePic']);
            $params['titlePic'] = $path;
        }
        if(!empty($input['mastheadPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['mastheadPic']);
            $params['mastheadPic'] = $path;
        }
        if(!empty($input['logos'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['logos']);
            $params['logos'] = $path;
        }
        
        $gameContext = array();
        $gameContext['gid'] = $input['game_id'];
        $gameContext['gname'] = $input['game_name'];
        $gameContext['icon'] = $input['old_gicon'];
        if(!empty($input['gicon'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['gicon']);
            $gameContext['icon'] = $path;
        }
        $gameContext['desc'] = $input['gdesc'];
        $game = GameModel::getInfo($input['game_id']);
        $gameContext['url'] = $game['downurl'];
        $params['gameContext'] = json_encode($gameContext);
        
        $shareContext = array();
        $shareContext['title'] = $input['sharetitle'];
        $shareContext['content'] = $input['sharecontent'];
        $shareContext['icon'] = $input['old_shareicon'];
        if(!empty($input['shareicon'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['shareicon']);
            $shareContext['icon'] = $path;
        }
        $params['shareContext'] = json_encode($shareContext);

        $result = BoxService::config_save($params,false);
        if($result['errorCode']==0){
            return $this->redirect('v4box/plan/list')->with('global_tips','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }

    public function getPlanDel($id=0)
    {
        if(empty($id)){
            return $this->json(array('error'=>1));
        }
        $result=BoxService::config_save(array('id'=>$id,'active'=>'false'));
        if($result['errorCode']==0){
            return $this->json(array('error'=>0));
        }
        return $this->json(array('error'=>1));
    }

    public function getStatus($id,$status,$platform,$location)
    {
        $params=array('id'=>$id);
        $uid=$this->getSessionData('youxiduo_admin');
        $params['modifier']=$uid['username'];
        $params['platform']=$platform;
        $params['location']=$location;
        if(!$status){
            $params['isOn']='false';
            $result=BoxService::config_save($params);
        } else {
            $params['isOn']='true';
            $result=BoxService::config_save($params);
        }
        if($result['errorCode']==0){
            return $this->redirect('v4box/plan/list')->with('global_tips','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }
    
    
    public function getPrizeInfo($id=''){
        if(!$id) return $this->back('数据错误');
        $pro_res = BoxService::prize_query(array('configId'=>$id));
        if($pro_res['errorCode']) return $this->back('无效数据');
        $pro_info = $pro_res['result'];
        $out = array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>'','6'=>'');
        $i = 1;
        foreach ($pro_info as $item) {
            if(isset($item['pic']) && $item['pic']){
                $item['pic'] = Utility::getImageUrl($item['pic']);
            }
            if(isset($item['model']) && $item['model']){
                $model_arr = json_decode($item['model'],true);
                $item['model'] = $model_arr;
            } else {
                $item['model'] = array();
            }
            $out[$i] = $item;
            $i++;
        }
        return $this->display('prize-info',array('datalist'=>$out,'configId'=>$id));
    }

    public function postPrizeSave(){
        $input = Input::all();
        $rule = array();
        $prompt = array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        for ($i=1;$i<=6;$i++) {
            $params = array(
                'id' => $input['id'][$i],
                'configId' => $input['configId'][$i],
                'title' => $input['title'][$i],
                'pic' => $input['old_pic'][$i],
                'chance' => $input['chance'][$i],
                'type' => $input['type'][$i],
                'count' => $input['count'][$i],
                'active' => 'true',
            );
            if(isset($input['pic'][$i]) && $input['pic'][$i]){
                $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
                $path = Helpers::uploadPic($dir,$input['pic'][$i]);
                $params['pic'] = $path;
            }
            
            $model_arr = array();
            if(isset($input['detailKey'][$i]) && $input['detailKey'][$i]){
                foreach ($input['detailKey'][$i] as $j => $detailkey) {
                    $model_item = array();
                    $model_item['key'] = $detailkey;
                    if(isset($input['detailValue'][$i][$j]) && $input['detailValue'][$i][$j]){
                        $model_item['value'] = $input['detailValue'][$i][$j];
                    } else {
                        $model_item['value'] = "";
                    }
                    $model_arr[] = $model_item;
                }
            }
            if($params['id'] == "")unset($params['id']);
            $params['model'] = json_encode($model_arr);
            
            if ($params['title']<>"") {
                $result = BoxService::prize_save($params,false);
            }
            
        }

        if($result['errorCode']==0){
            return $this->redirect('v4box/plan/list')->with('global_tips','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }
    
    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){ //echo $result['totalCount'];exit;
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        //print_r($pager);
        unset($data['page']);
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
        $params['pageSize'] =10;
        if(Input::get('keyword')){
            $data['keyword']=$params['goodsname']=Input::get('keyword');
        }
        //        print_r($params);
        $result=BoxService::searchProductList($params);
        //        print_r($result);
        if($result['errorCode'] !==null ){
            
            $data=self::processingInterface($result,$data,$params['pageSize']);
    
            $html = $this->html('pop-backpack-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }    
}