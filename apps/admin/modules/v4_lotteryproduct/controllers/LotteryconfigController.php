<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/7
 * Time: 上午10:58
 */

namespace modules\v4_lotteryproduct\controllers;

use Config;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;

class LotteryconfigController extends SuperController
{
    public function __construct()
    {
        $this->url_array['post_add']=Config::get('app.module_wheel_api_url').'wheel/updateConfig';
        $this->current_module = 'v4_lotteryproduct';
        //$this->_config['lookLog']=true;
        parent::__construct($this);
    }


    /**
     * @return bool|mixed|string
     */
    public function AfterViewAdd()
    {
        return MyHelp::getdata(Config::get('app.module_wheel_api_url').'wheel/getConfig',array('configKey'=>'iosCost'));
    }

    public function AfterPostAdd($res)
    {
        $this->callback_url['add']=$this->redirect('v4lotteryproduct/lotteryconfig/add')->with('global_tips','操作成功');
        return true;
    }




}