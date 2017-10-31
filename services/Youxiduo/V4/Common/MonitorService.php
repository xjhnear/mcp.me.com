<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\Common;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;

class MonitorService extends BaseService
{
	const SERVER_URL = 'http://youxiduo-java-0:8080/service_download_stats/';
	
	protected static function getServerURI()
	{
		return Config::get('app.monitor_api_url',self::SERVER_URL);
	}
	
	public static function isExistsChannel($channel_id)
	{
		$exists = self::http(self::getServerURI() . 'channel',array('channel_id'=>$channel_id));
		return $exists===false ? false : true;
	}
	
	public static function createChannel($channel_id,$channel_name,$is_active=true)
	{
		$res = self::http(self::getServerURI() . 'channel',array('channel_id'=>$channel_id,'channel_name'=>$channel_name,'is_active'=>$is_active),'POST');
		if($res !== false) return true;
		return false;
	}
	
	public static function isExistsConfig($config_id)
	{
		$exists = self::http(self::getServerURI() . 'config',array('config_id'=>$config_id));
		return $exists===false ? false : true;
	}
	
	public static function createConfig($config_id,$config_name,$config_os,$redirect_url,$channel_id,$click_callback_url='',$is_active=true)
	{
		$params = array(
		    'config_id'=>$config_id,
		    'config_name'=>$config_name,
		    'config_os'=>$config_os,
		    'redirect_url'=>$redirect_url,
		    'channel_id'=>$channel_id,
		    'is_active'=>$is_active,
		    'click_call_back'=>$click_callback_url
		);
		$res = self::http(self::getServerURI() . 'config',$params,'POST');
		if($res !== false) return true;
		return false;
		
	}
	
	protected static function http($url,$params=array(),$method='GET',$format='text',$multi = false, $extheaders = array())
	{
    	if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);        
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        $params_str = $format=='json' ? json_encode($params) : http_build_query($params);
                        //$headers[] = 'Content-Type: application/json; charset=utf-8';
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params_str);
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        $status_code = curl_getinfo($ci,CURLINFO_HTTP_CODE);
        curl_close ($ci);        
        if($status_code==200) return json_decode($response,true);
        Log::error($response);
        return false;
	}
}
