<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/8
 * Time: 下午4:29
 */

namespace modules\v4_lotteryproduct\controllers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;
use Config;
class ExplainController extends SuperController
{
    public function __construct()
    {
        $this->url_array['post_add']=Config::get('app.module_lottery_api_url').'admin/update_dic';
        $this->current_module = 'v4_lotteryproduct';
        //$this->_config['lookLog']=true;
        $this->curltype='POST';
        parent::__construct($this);
    }

    /**
     * @return bool|mixed|string
     */
    public function AfterViewAdd()
    {

        return MyHelp::getdata(Config::get('app.module_lottery_api_url').'admin/query_dic',array('dicType'=>'play_description'));
    }

    public function AfterPostAdd()
    {
        $this->callback_url['add']=$this->redirect('v4lotteryproduct/explain/add')->with('global_tips','操作成功');
        return true;
    }



}