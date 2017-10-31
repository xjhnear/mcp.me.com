<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/21
 * Time: 下午3:09
 */

namespace modules\v4a_lotteryproduct\controllers;

use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;
use Config,Input;
//http://test.open.youxiduo.com/doc/interface-info/808
class RewardsController extends SuperController
{

    const MALL_MML_API_URL ='app.mall_mml_api_url';//http://121.40.78.19:8080/module_mall/
    public function __construct()
    {
        $this->url_array['list_url']=Config::get(self::MALL_MML_API_URL).'order/query_order';
        $this->current_module = 'v4a_lotteryproduct';
        //$this->_config['lookLog']=true;
        parent::__construct($this);
    }

    public function BeforeList($inputinfo){
        $inputinfo['hasAddress']='true';
        $inputinfo['orderStatus']=1;
        if(!empty($inputinfo['orderDesc']) and $inputinfo['orderDesc'] == 'wheel_consume'){
            $inputinfo['orderDesc']='wheel_consume';
        }else{
            $inputinfo['orderDesc']='task_consume';
        }

        if(!empty($inputinfo['billTimeBegin'])){
            $inputinfo['billTimeBegin'] = date('Y-m-d H:i:s',strtotime($input['billTimeBegin']));
        }
        if(!empty($inputinfo['billTimeEnd'])){
            $inputinfo['billTimeEnd'] = date('Y-m-d H:i:s',strtotime($input['billTimeEnd']));
        }
        return $inputinfo;
    }

    public function AfterList($result)
    {
        $result['userinfo']=MyHelp::getUser($result['datalist'],'biller');

        return $result;
    }

    public function getDeliveryAll()
    {
        $input=Input::all();
        if(empty($input))  return $this->back()->with('global_tips','调用失败');
        foreach ($input as $key => $value){
            $arr=explode("-",$value);
            if(empty($arr['0'])){ return $this->back()->with('global_tips','订单号失败'); }
            $result=MyHelp::curldata(Config::get(self::MALL_MML_API_URL).'order/update_orderdeliver',array('orderId'=>$arr['0']),'GET');
            if($result['errorCode']!=0) return $this->back()->with('global_tips','订单号：'.$arr['0'].' 请求失败');
        }
        return $this->redirect('v4alotteryproduct/rewards/list')->with('global_tips','操作成功');
    }

    public function getDelivery($id='')
    {
        if(empty($id)){
            return $this->json(array('error'=>0,'errortxt'=>'参数缺失'));
        }
        $result=MyHelp::curldata(Config::get(self::MALL_MML_API_URL).'order/update_orderdeliver',array('orderId'=>$id),'GET');
        if($result['errorCode']==0 ){
            return $this->json(array('errorCode'=>1,'errortxt'=>'操作成功'));
        }
        return $this->json(array('errorCode'=>0,'errortxt'=>$result['errorDescription']));
    }
















}