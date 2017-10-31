<?php
/**
 * 
 * User: fujiajun
 * Date: 2015/9/1
 * Time: 17:26
 */
namespace Youxiduo\V4\Lotteryproduct;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
class LotteryproductService extends BaseService{
    const API_URL_LOTTERY = 'app.module_lottery_api_url';//http://121.40.78.19:8080/module_lottery/
    const API_URL_WHEEL = 'app.module_wheel_api_url'; //http://121.40.78.19:8080/module_wheel

    const API_URL_account = 'app.module_account_api_url';//http://121.40.78.19:48080

   
    //查询彩票信息 http://test.open.youxiduo.com/doc/interface-info/812
   	public static function  query_lottery($inputinfo,$params=array())
   	{
    	return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::API_URL_LOTTERY).'admin/query_lottery');
   	}

   	//查询彩票奖励不带分页 http://121.40.78.19:8080/module_lottery/admin/query_prize
   	public static function query_prize()
    {
    	return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/query_prize',array(),'GET');
	  }

	//查询用户购买记录 //http://test.open.youxiduo.com/doc/interface-info/818
	public static function query_record($inputinfo,$params=array())
    {
    	return Utility::preParamsOrCurlProcess($inputinfo,$params,Config::get(self::API_URL_LOTTERY).'admin/query_record');
	}

	//大转盘方案查询接口 http://test.open.youxiduo.com/doc/interface-info/804
	public static function detail_query($params=array())
    {
		return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/detail',$params,'GET');
	}

  //大转盘方案查询接口 http://test.open.youxiduo.com/doc/interface-info/804
  public static function wheel_query($params=array())
  {
    return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/query',$params,'GET');
  }



  //获取当期彩票信息 http://test.open.youxiduo.com/doc/interface-info/823
  public static function current_lottery()
  {
      return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/current_lottery',array(),'GET');
  }
  //http://test.open.youxiduo.com/doc/interface-info/811  查询常量字典
  public static function query_dic() 
  {
      return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/query_dic',array(),'GET');
  }
  //发布彩票号码 http://test.open.youxiduo.com/doc/interface-info/814
  public static function publish_lottery_number($input){
     return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/publish_lottery_number',$input,'HTMLFROM');
  }
  //更新奖励 http://test.open.youxiduo.com/doc/interface-info/817   
  public static function update_prize($input){
     return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/update_prize',$input,'HTMLFROM');
  }  
  //更新字典常量 http://test.open.youxiduo.com/doc/interface-info/810 
  public static function update_dic($input){
     return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/update_dic',$input,'POST');
  }
  //大转盘获奖名单
  public static function querywin($input){
      return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/querywin',$input,'GET');
  }
    //大转盘获奖名单导出
    public static function export($input){
        return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/export',$input,'GET');
    }
  //删除大转盘奖品
  public static function delectDetail($input)
  {
       return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/deleteDetail',$input,'GET');
  }
  //删除方案
  public static function deleteScheme($input)
  {
       return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/deleteScheme',$input,'GET');
  }
  //增加转盘方案 http://test.open.youxiduo.com/doc/interface-info/806
  public static function wheel_add($input)
  {
       return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/add',$input,'POST');
  }

  public static function wheel_update($input)
  {
       return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'wheel/update',$input,'POST');

  }

  //获奖用户补发奖 http://test.open.youxiduo.com/doc/interface-info/815
  public static function run_lottery($input)
  {
      return Utility::loadByHttp(Config::get(self::API_URL_LOTTERY).'admin/run_lottery',$input,'GET');
  }



//天天彩游币管理, 大转盘游币管理   http://test.open.youxiduo.com/doc/interface-info/1403
    //http://121.40.78.19:48080/module_account/account/statistics_yb


  public static function statistics_yb($input)
 {
     return Utility::loadByHttp(Config::get(self::API_URL_account).'module_account/account/statistics_yb',$input,'GET');
     

   }


    //钻石发放  http://121.40.78.19:48080/module_diamond//diamond/statistics_diamond
    public static function statistics_diamond($input)
    {
        return Utility::loadByHttp(Config::get(self::API_URL_account).'module_diamond//diamond/statistics_diamond',$input,'GET');


    }

    public static function getConfig($input)
    {
        return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'/wheel/getConfig',$input,'GET');


    }

    public static function updateConfig($input)
    {
        return Utility::loadByHttp(Config::get(self::API_URL_WHEEL).'/wheel/updateConfig',$input,'POST');

    }
 }

