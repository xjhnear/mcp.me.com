<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/11/9
 * Time: 下午3:51
 */

namespace modules\v4_product\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
class RmbController  extends BackendController
{
    const MALL_DIAMOND_API_URL = 'app.48080_api_url';
    const ACCOUNT_API_URL = 'app.account_api_url';
    public function _initialize()
    {
        $this->current_module = 'v4_product';
    }

    public function  getList()
    {
        $result=MyHelp::getdata(Config::get(self::ACCOUNT_API_URL).'account/rate',array());
        if($result['errorCode'] == 0){
            $data['rmb']['gameRate']=$result['result'];
        }
        $input['confKey']='diamondRate';
        $result=MyHelp::getdata(Config::get(self::MALL_DIAMOND_API_URL).'module_rmb/account/configlist',$input);
        if($result['errorCode'] == 0){
            $data['rmb']['diamondRate']=$result['result'];
        }
        return $this->display('rmb/rmb-list',$data);
    }


    public function postAdd()
    {
        $input['rate']=Input::get('gameRate');
        MyHelp::getdata(Config::get(self::ACCOUNT_API_URL).'rate/update',$input);
        $input['confKey']='diamondRate';
        $input['confValue']=Input::get('diamondRate');
        $result=MyHelp::getdata(Config::get(self::MALL_DIAMOND_API_URL).'module_rmb/account/setconfig',$input);

        return $this->redirect('v4product/rmb/list')->with('global_tips','更改成功');
    }







}