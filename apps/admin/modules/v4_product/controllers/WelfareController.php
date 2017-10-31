<?php

/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/9/24
 * Time: 下午1:39
 */
namespace modules\v4_product\controllers;
use Illuminate\Support\Facades\Config;
use Youxiduo\MyService\SuperController;
use Youxiduo\Cache\CacheService;
use Illuminate\Support\Facades\Input;
class WelfareController extends SuperController
{
    const MALL_MML_API_URL = 'app.mall_mml_api_url';
    /**
     * 初始化
     */
    public function __construct()
    {
        $this->current_module = 'v4_product';

        //福利列表
        //http://test.open.youxiduo.com/doc/interface-info/902
        //http://121.40.78.19:8080/module_mall/productWelfare/get_product_welfare_list
        $this->url_array['list_url']=Config::get(self::MALL_MML_API_URL).'productWelfare/get_product_welfare_list';

        $this->url_array['set']['add']=Config::get(self::MALL_MML_API_URL).'productWelfareS/save_product_welfare_kind_relevance';

        $this->url_array['post_edit']=Config::get(self::MALL_MML_API_URL).'productWelfareS/update_product_welfare_kind';

        $this->url_array['set']['editWelfare']=Config::get(self::MALL_MML_API_URL).'productWelfareS/update_product_welfare_kind_relevance';

        parent::__construct($this);


    }


    protected function AfterViewAdd($data)
    {
        $data['view']=array('福利三','福利四');
        return $data;
    }


    protected function AfterViewEdit($data){
        $data['result']=$data;
        $data['view']=array('福利三','福利四');
        return $data;
    }










}