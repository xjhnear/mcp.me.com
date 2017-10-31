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
    const MALL_DIAMOND_API_URL = 'app.mall_module_diamond';
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
        $result=MyHelp::getdata(Config::get(self::MALL_DIAMOND_API_URL).'/diamond/rate',array());
        if($result['errorCode'] == 0){
            $data['rmb']['diamondRate']=$result['result'];
        }
        return $this->display('rmb/rmb-list',$data);
    }


    public function postAdd()
    {
        $input['rate']=Input::get('gameRate');
        MyHelp::getdata(Config::get(self::ACCOUNT_API_URL).'rate/update',$input);
        $input['rate']=Input::get('diamondRate');
        $result=MyHelp::getdata(Config::get(self::MALL_DIAMOND_API_URL).'/rate/update',$input);

        return $this->redirect('v4product/rmb/list')->with('global_tips','更改成功');
    }







}