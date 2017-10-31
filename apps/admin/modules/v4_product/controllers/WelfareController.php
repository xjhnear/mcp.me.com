<?php

/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/9/24
 * Time: 下午1:39
 */
namespace modules\v4_product\controllers;
use libraries\Helpers;
use Illuminate\Support\Facades\Config;
use Yxd\Modules\Core\SuperController;
class WelfareController extends SuperController
{
    const MALL_MML_API_URL = 'app.mall_mml_api_url';
    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->current_module = 'v4_product';
        $this->controller='welfare';
        //更新新种类
        //http://test.open.youxiduo.com/doc/interface-info/906
        //http://121.40.78.19:8080/module_mall/productWelfareS/update_product_welfare_kind
        $this->getEditurl=Config::get(self::MALL_MML_API_URL).'productWelfareS/update_product_welfare_kind';
        //福利列表
        //http://test.open.youxiduo.com/doc/interface-info/902
        //http://121.40.78.19:8080/module_mall/productWelfare/get_product_welfare_list
        $this->url['list']=$this->getListurl=Config::get(self::MALL_MML_API_URL).'productWelfare/get_product_welfare_list';

        $this->url['add']=Config::get(self::MALL_MML_API_URL).'productWelfareS/save_product_welfare_kind_relevance';

        $this->url['editWelfare']=Config::get(self::MALL_MML_API_URL).'productWelfareS/update_product_welfare_kind_relevance';



    }

    protected function _getGlobalData($data=array())
    {
        $data['view']=array('福利三','福利四');
        return $data;
    }

    protected function _setInputinfo($input=array())
    {
        if(empty($input['type'])) return $input;
        switch($input['type']){
            case '1':
                $rule=array();
                $prompt=array();
                $valid = Validator::make($input,$rule,$prompt);
                if($valid->fails())
                    echo json_encode(array('errorCode'=>1,'msg'=>$valid->messages()->first()));
                    exit;
            break;
        }

        unset($input['type'],$input['currencyType']);
        return $input;
    }



}