<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/28
 * Time: 下午2:19
 */

namespace modules\v4a_giftbag\controllers;


use Youxiduo\MyService\SuperController;
use Config;
class BookingController extends  SuperController
{
    const MALL_MML_API_URL = 'app.mall_mml_api_url';

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->current_module = 'v4a_giftbag';
        $this->url_array['list_url']=Config::get(self::MALL_MML_API_URL).'product/reserve_statistics';
        parent::__construct($this);
    }


}