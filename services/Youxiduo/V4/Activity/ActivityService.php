<?php
/**
 * 
 * User: fujiajun
 * Date: 2015/8/13
 * Time: 11:20
 */
namespace Youxiduo\V4\Activity;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
class ActivityService extends BaseService{
    const API_URL_CONF = 'app.mall_api_url';
    const RLT_URL_CONF = 'app.mall_rlt_api_url';//http://121.40.78.19:8080/module_relevance
   	const ACT_URL_CONF= 'app.module_activity_api_url';//http://121.40.78.19:8080/module_activity
   	const CONFIG_WELFAREGAME_URL_CONF= 'app.config_welfaregame_api_url';//http://112.124.121.34:20160/config_welfaregame
    //游戏于商品的关联
    public static function get_activity_list_by_gid($gid,$genre){
    	return Utility::loadByHttp(Config::get(self::RLT_URL_CONF).'get_activity_list_by_gid',array('genre'=>$genre,'gid'=>$gid));
    }
    //查询活动信息接口 http://test.open.youxiduo.com/doc/interface-info/779
    public static function get_activity_info_list_back_end($inputinfo,$params)
    {
    	return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::ACT_URL_CONF).'get_activity_info_list_back_end');
    }

    //查询活动游戏接口 http://test.open.youxiduo.com/doc/interface-info/786
    public static function get_activity_game_list($info=array())
    {
    	return Utility::loadByHttp(Config::get(self::RLT_URL_CONF).'get_activity_game_list',$info);

    }
   	//保存活动任务接口 http://test.open.youxiduo.com/doc/interface-info/778
   	public static function save_activity_info($inputinfo,$params)
   	{
   		return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::ACT_URL_CONF).'save_activity_info','POST',"IOS");
   	}
   	//保存游戏活动关联 http://test.open.youxiduo.com/doc/interface-info/785
   	public static function save_activity_game($rel_data)
   	{
   		return Utility::preParamsOrCurlProcess($rel_data,array('gid','activityId','genre','createTime'),Config::get(self::RLT_URL_CONF).'save_activity_game','POST');
   	}

	//更新活动任务接口 http://test.open.youxiduo.com/doc/interface-info/781
    public static function update_activity_info($inputinfo,$params)
    {
        //var_dump($inputinfo);die;
    	return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::ACT_URL_CONF).'update_activity_info','POST');
    }

    //更新活动游戏接口 http://test.open.youxiduo.com/doc/interface-info/788
    public static function update_activity_game($inputinfo,$params=array('gid','id','createTime'))
    {
        if(!$inputinfo['gid']){
            unset($inputinfo['gid']);
            unset($params[0]);
        }
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::RLT_URL_CONF).'update_activity_game','POST');
    }
    
    //查询H5游戏信息接口
    public static function get_h5_info_list($inputinfo,$params)
    {
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_welfaregame/find_welfaregame');
    }
    
    //查询H5游戏详情接口
    public static function get_h5_info_detail($inputinfo,$params)
    {
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_welfaregame/findbyid_welfaregame');
    }
    
    //保存H5游戏接口
    public static function save_h5_info($inputinfo,$params)
    {
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_welfaregame/add_welfaregame','POST');
    }
    
    //更新H5游戏接口
    public static function update_h5_info($inputinfo,$params)
    {
        //var_dump($inputinfo);die;
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_welfaregame/update_welfaregame','POST');
    }
    
    //删除H5游戏接口
    public static function del_h5_info($inputinfo,$params)
    {
        //var_dump($inputinfo);die;
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_welfaregame/delete_welfaregame');
    }
    
    //查询H5分享设置接口
    public static function get_h5_share_list($inputinfo,$params)
    {
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_share/share');
    }
    //保存H5游戏接口
    public static function save_h5_share($inputinfo,$params)
    {
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_share/add_share','POST');
    }
    
    //更新H5游戏接口
    public static function update_h5_share($inputinfo,$params)
    {
        //var_dump($inputinfo);die;
        return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::CONFIG_WELFAREGAME_URL_CONF).'config_share/update_share','POST');
    }

}