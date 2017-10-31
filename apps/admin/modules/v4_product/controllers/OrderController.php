<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/10/20
 * Time: 下午6:00
 */

namespace modules\v4_product\controllers;
use Illuminate\Support\Facades\Config;
use Yxd\Modules\Core\SuperController;
use Youxiduo\Helper\MyHelp;
use Illuminate\Support\Facades\Session;
class OrderController extends SuperController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';
    const MALL_MML_API_URL = 'app.mall_mml_api_url';

    public function _initialize()
    {
        $this->current_module = 'v4_product';
        $this->controller='order';
        $this->getListurl=Config::get(self::MALL_MML_API_URL).'order/query_order';
        $this->url['edit']=Config::get(self::MALL_MML_API_URL).'order/modify_order';
        //http://test.open.youxiduo.com/doc/interface-info/340
        $this->url['update_order']=Config::get(self::MALL_MML_API_URL).'order/update_orderdeliver';
    }


    protected function _setInputinfo($inputinfo=array())
    {

        if(!empty($inputinfo['biller'])){
            $inputinfo['billerName']=$inputinfo['biller'];
            $inputinfo['biller']=MyHelp::searchUser($inputinfo['biller']);
        }
        $inputinfo['orderDesc']='mall';
        if(!empty($inputinfo['billTimeBegin'])){
            $inputinfo['billTimeBegin']=date('Y-m-d H:i:s',strtotime($inputinfo['billTimeBegin']));
        }
        if(!empty($inputinfo['billTimeEnd'])){
            $inputinfo['billTimeEnd']=date('Y-m-d H:i:s',strtotime($inputinfo['billTimeEnd']));
        }
        return $inputinfo;
    }

    protected function _setParams($inputinfo=array())
    {
        if($inputinfo['type'] == 'edit' && empty($inputinfo['active'])){
            $arr=array('orderId'=>$inputinfo['orderId']);
            unset($inputinfo['orderId']);
            $arr['address']=json_encode($inputinfo);
            return $arr;
        }
        return $inputinfo;
    }

    protected function _getGlobalData($data=array()){
        //$data['userinfo2']=MyHelp::getUser($data['datalist'],'modifier');print_r($data['userinfo2']);exit;
        $data['userinfo']=MyHelp::getUser($data['datalist'],'biller');
        $youxiduo_admin=Session::get('youxiduo_admin');//print_r($youxiduo_admin);exit;
        $data['uid']=$youxiduo_admin['username'];
        return $data;
    }
}