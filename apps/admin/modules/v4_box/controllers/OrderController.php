<?php

namespace modules\v4_box\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\Box\BoxService;
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

class OrderController extends BackendController
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
        $prizeTypeList = array('1'=>"礼包",'2'=>"实物",'3'=>"谢谢参与");
        $statusList = array('0'=>"失败",'1'=>"未发放",'2'=>"成功");
        $arr_=array('id','uid','prizeTitle','startTime','endTime','status');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        $result=BoxService::record_query($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            $data['userinfo']=MyHelp::getUser($data['datalist'],'uid');
            $data['prizeTypeList'] = $prizeTypeList;
            $data['statusList'] = $statusList;
            return $this->display('order/order-list',$data);
        }
        $data['prizeTypeList'] = $prizeTypeList;
        $data['statusList'] = $statusList;
        return $this->display('order/order-list',$data);
    }
    
    
    public function getSet($type)
    {
        $input = Input::all();
        $uid=$this->getSessionData('youxiduo_admin');
        $params['id']=$input['id'];
        switch ($type) {
            case 'fafang':
                $params['status']=2;
                break;
//             case 'del':
//                 $params['active']='false';
            default:
                break;
        }
        
        $result=BoxService::record_save($params);
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'操作成功','val'=>$input,'result'=>!empty($result['result'])?$result['result']:array()) : array('errorCode'=>1,'msg'=>$result['errorDescription'],'val'=>$input,'result'=>!empty($result['result'])?$result['result']:array());
        echo  json_encode($result);
        exit;
    }
    
    public function getFafangAll(){
        $input=Input::all();
        if(empty($input))  return $this->back()->with('global_tips','调用失败');
        foreach ($input as $key => $value) {
            $inputinfo = json_decode($value,true);
            if(empty($inputinfo['id'])){ return $this->back()->with('global_tips','订单号失败'); }
            $params['id']=$inputinfo['id'];
            $params['status']=2;
            $result=BoxService::record_save($params);
            if($result['errorCode']!=0) return $this->back()->with('global_tips','订单号：'.$inputinfo['id'].' 请求失败');
        }
        return $this->redirect('v4box/order/list')->with('global_tips','操作成功');
    }
    
    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){
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
 
}