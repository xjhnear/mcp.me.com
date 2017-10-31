<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/10/20
 * Time: 下午6:00
 */

namespace modules\v4_product\controllers;
use Illuminate\Support\Facades\Config;
use Youxiduo\MyService\SuperController;
use Youxiduo\Helper\MyHelp;
use Illuminate\Support\Facades\Session;
use modules\web_forum\controllers\TopicController;
use Illuminate\Support\Facades\Input;
use Youxiduo\Imall\ProductService;

class OrderController extends SuperController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';
    const MALL_MML_API_URL = 'app.mall_mml_api_url';
    const MESSAGE_API_URL = 'app.message_api_url';

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->current_module = 'v4_product';
        $this->url_array['list_url']=Config::get(self::MALL_MML_API_URL).'order/query_order';

        $this->url_array['set']['edit']=Config::get(self::MALL_MML_API_URL).'order/modify_order';

        $this->url_array['set']['update_order']=Config::get(self::MALL_MML_API_URL).'order/update_orderdeliver';
        $this->_config['lookLog']=false;
        $this->_config['isFirePHP']=false;
        parent::__construct($this);
    }

    protected function BeforeList($inputinfo=array())
    {
        if(empty($inputinfo['orderStatus'])){
            $inputinfo['orderStatus']=1;
        }
        if(!empty($inputinfo['biller'])){
            $inputinfo['billerName']=$inputinfo['biller'];
            $inputinfo['biller']=MyHelp::searchUser($inputinfo['biller']);
        }
        $inputinfo['orderDesc']='mall';
        $inputinfo['sortField']='BillTime';
        if ($inputinfo['orderStatus']==1) {
            $inputinfo['sortType']='asc';
        } else {
            $inputinfo['sortType']='desc';
        }
        if(!empty($inputinfo['billTimeBegin'])){
            $inputinfo['billTimeBegin']=date('Y-m-d H:i:s',strtotime($inputinfo['billTimeBegin']));
        } else {
            $inputinfo['billTimeBegin']=date('Y-m-d H:i:s',strtotime("-7 day"));
        }
        if(!empty($inputinfo['billTimeEnd'])){
            $inputinfo['billTimeEnd']=date('Y-m-d H:i:s',strtotime($inputinfo['billTimeEnd']));
        } else {
            $inputinfo['billTimeEnd']=date('Y-m-d H:i:s',time());
        }
        return $inputinfo;
    }

    protected function set_inputinfo($inputinfo,$type)
    {
        if($type == 'edit' && empty($inputinfo['active'])){
            $arr=array('orderId'=>$inputinfo['orderId']);
            unset($inputinfo['orderId']);
            if(empty($inputinfo['is_del'])){
                $arr['address']=json_encode($inputinfo);
            }else{
                $arr['Active']='false';
            }

            return $arr;
        }
        return $inputinfo;
    }

    protected function AfterList($data=array()){
        //$data['userinfo2']=MyHelp::getUser($data['datalist'],'modifier');print_r($data['userinfo2']);exit;
        $youxiduo_admin=Session::get('youxiduo_admin');//print_r($youxiduo_admin);exit;
        $data['uid']=$youxiduo_admin['username'];
        $data['kuai_di']='';
        foreach($data['datalist'] as $key=>&$val){
            //$val['modifyTimeStr']=date('Y-m-d H:i:s',$val['modifyTime']/1000);
            if(!empty($val['address'])){
                $data['kuai_di']=json_decode($val['address'],true);
                $val['kuai_di']='';
                if(isset($data['kuai_di']['快递'])){
                    $val['kuai_di']=$data['kuai_di']['快递'];
                }
                $val['json_address']=json_encode($data['kuai_di']);
            }
            $val['biller']=str_replace('ios', '', $val['biller']);
            if (isset($val['items'][0]['productCode']) && $val['items'][0]['productCode']) {
                $pro_res = ProductService::searchProductList(array('productCode'=>$val['items'][0]['productCode']));
                if ($pro_res['errorCode'] == 0 && $pro_res['result']) {
                    $val['items'][0]['platform'] = $pro_res['result'][0]['platform'];
                } else {
                    $val['items'][0]['platform'] = 'unknown';
                }
            }
        }
        $uid=$this->getSessionData('youxiduo_admin');
        $data['platformList']=array('ios'=>'IOS','glwzry'=>'攻略','unknown'=>'商品不存在');
        $data['modifier']=$uid['username'];
        $data['userinfo']=MyHelp::getUser($data['datalist'],'biller');
        return $data;
    }

    protected  function set_result($result,$type,$inputinfo)
    {    
        if($type == 'update_order' && $result['errorCode'] == 0){
//             $params['toUid']=$inputinfo['modifier'];
//             $params['sendTime']=date('Y-m-d H:i:s',time());
//             if($inputinfo['productType'] == 0){
//                 $params['linkType']=5;
//                 $params['content']=$inputinfo['productName'];
//             }else{
//                 $params['content']=$inputinfo['productName'].','.'快递'.','.$inputinfo['kuai_di'];
//                 $params['linkType']=4;
//             }
//             $params['isTop']='false';
//             $params['type']='2011';
//             $params['isPush']='false';
//             $params['link']=0;
//             $params['allUser']='false';
//             MyHelp::curldata(Config::get(self::MESSAGE_API_URL).'message/system_send',$params);
            $input['type'] = '2011';
            $input['uid'] =  $inputinfo['modifier'];
            if($inputinfo['productType'] == 1){
                $input['linkType'] = '5';
                $input['content'] = $inputinfo['productName'].','.$inputinfo['kuai_di'];
                $input['link'] = $inputinfo['productCode'];
            }elseif($inputinfo['productType'] == 4){
                $input['linkType'] = '4';
                $input['content'] = $inputinfo['productName'];
                $input['link'] = $inputinfo['productCode'];
            }elseif($inputinfo['productType'] == 0){
                $input['linkType'] = '6';
                $input['content'] = $inputinfo['productName'];
                $input['link'] = $inputinfo['productCode'];
            }
            if(isset($input['linkType'])&&isset($input['content'])&&isset($input['link'])){
                TopicController::system_send($input);
            }
        }
    }

    public function getFafangAll(){
        $input=Input::all();
        if(empty($input))  return $this->back()->with('global_tips','调用失败');
        foreach ($input as $key => $value) {
            $inputinfo = json_decode($value,true);
            if(empty($inputinfo['orderId'])){ return $this->back()->with('global_tips','订单号失败'); }
            $result=MyHelp::curldata($this->url_array['set']['update_order'],$inputinfo,'get',$this->_config['lookLog']);
            $result=self::set_result($result,'update_order',$inputinfo);
            if($result['errorCode']!=0) return $this->back()->with('global_tips','订单号：'.$inputinfo['orderId'].' 请求失败');
        }
        return $this->redirect('v4product/order/list')->with('global_tips','操作成功');
    }


}