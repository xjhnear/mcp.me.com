<?php
namespace Youxiduo\Task;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;

class TaskV3Service extends BaseService{

    const API_URL_CONF = 'app.task_api_url';//"http://youxiduo-java-slb-5:58080/module_task/";
//    const API_URL_CONF = "http://192.168.2.64:8080/module_task/";


    public static function task_add($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/create_task",$data,'POST');
        return $res;
    }

    public static function task_edit($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/update_task_base_info",$data,'POST');
        return $res;
    }

    public static function task_list($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/query_task_list",$data,'GET');
        return $res;
    }
    public static function task_get($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/task_detail",$data,'GET');
        return $res;
    }

//    public static function update_task_base_info($data=array())
//    {
//        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/update_task_base_info",$data,'GET');
//        return $res;
//    }

    public static function task_close($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/close_task",$data,'GET');
        return $res;
    }

    public static function task_del($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/del_task",$data,'GET');
        return $res;
    }
    //强制下线
    public static function offline_task($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/offline_task",$data,'GET');
        return $res;
    }

    public static function query_screenshot_list($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/query_user_task_info_list",$data,'GET');
        return $res;
    }
    public static function query_screenshot_list_IOS($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/query_task_list_for_ios",$data,'GET');
        return $res;
    }

    public static function approval_screenshot($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."screenshot/approval_task_screenshot",$data,'GET');
        return $res;
    }
    public static function approval_task_all_screenshot($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."screenshot/approval_task_all_screenshot",$data,'GET');
        return $res;
    }

    public static function reset_award_prize($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/reset_award_prize",$data,'GET');
        return $res;
    }

    public static function approval_step_screenshot($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."screenshot/approval_step_screenshot",$data,'GET');
        return $res;
    }

    public static function approval_step_all_screenshot($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."screenshot/approval_step_all_screenshot",$data,'GET');
        return $res;
    }

    public static function query_user_step_info_list($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."step/query_user_step_info_list",$data,'GET');
        return $res;
    }

    public static function release_task_stock($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."task/release_task_stock",$data,'GET');
        return $res;
    }

    public static function insert_step($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."step/insert_step",$data,'POST');
        return $res;
    }

    public static function update_step_base_info($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."step/update_step_base_info",$data,'POST');
        return $res;
    }

    public static function delete_step($data=array())
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."step/delete_step",$data,'GET');
        return $res;
    }
  }