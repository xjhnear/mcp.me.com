<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Box;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;


class BoxService extends BaseService{
    const API_URL_CONF = 'app.box_api_url';

    //百宝箱配置列表
    public static function config_query($params)
    {   $params_=array('id','platform','isOn','active','page','size');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/getConfigList');
    }
    
    //百宝箱奖品查询
    public static function prize_query($params)
    {   $params_=array('id','configId','title','active');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/getPrizeList');
    }
    
    //百宝箱配置提交
    public static function config_save($params)
    {   $params_=array('id','title','titlePic','location','mastheadPic','introduce','logos','gameContext','regulation','platform','limitNum','cost','shareContext','isOn','active');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/updateConfig','POST');
    }
    
    //百宝箱奖品提交
    public static function prize_save($params)
    {   $params_=array('id','configId','title','pic','chance','type','targetId','count','worth','model','active');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/updatePrize','POST');
    }
    
    //百宝箱配置操作
    public static function config_operate($params)
    {   $params_=array('id','location','platform','isOn','active');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/updateConfig','POST');
    }

    //百宝箱记录列表
    public static function record_query($params)
    {   $params_=array('id','configId','prizeId','uid','platform','status','cost','startTime','endTime','prizeTitle','page','size');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/getRecordList');
    }
    
    //百宝箱记录提交
    public static function record_save($params)
    {   $params_=array('id','status','model');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'chest/updateRecord','POST');
    }
    
}
