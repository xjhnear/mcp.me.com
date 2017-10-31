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

use Youxiduo\V4\Common\Model\ShareAdv;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Common\Model\ShareTpl;
use Youxiduo\V4\User\UserService;

use Youxiduo\V4\Activity\Model\ChannelClick;
use Youxiduo\V4\Activity\Model\DownloadChannel;
use Youxiduo\V4\Activity\Model\StatisticConfig;
use Youxiduo\V4\Common\MonitorService;

class ShareService extends BaseService
{
	public static function searchTpl($search)
	{
		return ShareTpl::db()->where('platform','=',$search['platform'])->orderBy('id','asc')->get();
	}
	
	public static function getTplToKV($platform)
	{
		return ShareTpl::db()->where('platform','=',$platform)->orderBy('id','asc')->lists('title','ename');
	}
	
	public static function getTplInfoById($id)
	{
		return ShareTpl::db()->where('id','=',$id)->first();
	}
	
	public static function getTplInfoByEname($ename)
	{
		return ShareTpl::db()->where('ename','=',$ename)->first(); 
	}
	
	public static function addTplInfo($data)
	{
		return ShareTpl::db()->insertGetId($data);
	}
	
	public static function updateTplInfo($ename,$data)
	{
		return ShareTpl::db()->where('ename','=',$ename)->update($data);
	}
	
	public static function searchAdv($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearchAdv($search)->count();
		$result = self::buildSearchAdv($search)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearchAdv($search)
	{
		$tb = ShareAdv::db();
		if(isset($search['target_id']) && $search['target_id']){
			$tb = $tb->where('target_id','=',$search['target_id']);
		}
		
	    if(isset($search['tpl_ename']) && $search['tpl_ename']){
			$tb = $tb->where('tpl_ename','=',$search['tpl_ename']);
		}
		
		return $tb;
	}
	
	public static function getAdvInfoById($id)
	{
		return ShareAdv::db()->where('id','=',$id)->first();
	}
	
	/**
	 * 保存数据
	 */
	public static function saveAdvInfo($data)
	{
	    if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			$data['update_time'] = date('Y-m-d H:i:s');
			return ShareAdv::db()->where('id','=',$id)->update($data);
		}else{
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['update_time'] = date('Y-m-d H:i:s');
			return ShareAdv::db()->insertGetId($data);
		}
	}
	
	/**
	 * 保存分享信息
	 * @param string $target_id
	 * @param string $target_title
	 * @param string $platform
	 * @param string $tpl_ename
	 * @param string $title
	 * @param string $icon
	 * @param string $content
	 * @param string $redirect_url
	 * @param int $start_time
	 * @param int $end_time
	 * @param int $is_show
	 */
	public static function saveAdvInfoByTargetId($target_id,$target_title,$platform,$tpl_ename,$title,$icon,$content,$redirect_url,$start_time,$end_time,$is_show)
	{
		$exists = ShareAdv::db()->where('target_id','=',$target_id)->where('tpl_ename','=',$tpl_ename)->where('platform','=',$platform)->first();
		if($exists){
			$id = $exists['id'];
			$data = array();
			$data['title'] = $title;
			$data['weixin'] = $content;
			$data['weibo'] = $content;
			$icon && $data['icon'] = $icon;
			$data['redirect_url'] = $redirect_url;
			$data['start_time'] = $start_time;
			$data['end_time'] = $end_time;
			$data['is_show'] = $is_show;
			
			return ShareAdv::db()->where('id','=',$id)->update($data);
		}else{
			$data = array();
			$data['target_id'] = $target_id;
			$data['target_title'] = $target_title;
			$data['platform'] = $platform;
			$data['tpl_ename'] = $tpl_ename;
			$data['title'] = $title;
			$data['weixin'] = $content;
			$data['weibo'] = $content;
			$icon && $data['icon'] = $icon;
			$data['redirect_url'] = $redirect_url;
			$data['start_time'] = $start_time;
			$data['end_time'] = $end_time;
			$data['is_show'] = $is_show;
			return ShareAdv::db()->insertGetId($data);
		}
	}
	
	public static function deleteAdvInfo($id)
	{
		return ShareAdv::db()->where('id','=',$id)->delete();
	}
	
	/**
	 * 解析模板
	 */
	public static function parseTplToContent($ename,$data,$icon,$url,$target_id=null,$monitor=false)
	{
		$tpl = self::getTplInfoByEname($ename);
		if(!$tpl) return false;		
		$vars_json = json_decode($tpl['var_json'],true);		
		$template = json_decode($tpl['content'],true);
		$weixin = $template['weixin'];
		$weibo = $template['weibo'];
		$title = $tpl['title'];		
		$vars = array();
		if(is_array($vars_json)) $vars = array_keys($vars_json);
		if($vars && is_array($vars)){
			foreach($vars as $var){
				if(isset($data[$var])){
					$weixin = str_replace($var,$data[$var],$weixin);
					$weibo = str_replace($var,$data[$var],$weibo);
					$title = str_replace($var,$data[$var],$title);
				} 				
			}
		}
		$session_id = Input::get('session_id');
		$uid = $session_id ? UserService::getUidFromSession($session_id) : 0;
		$append_params = array('session_id'=>$session_id,'uid'=>$uid);
		$url = self::appendParams($url,$append_params);
		$out = array();
		$out['title'] = $title;
		$out['pic'] = Utility::getImageUrl($icon);
		$out['weixin'] = $weixin;
		$out['weibo'] = $weibo;
		$out['url'] = $url ? self::getShortUrl($url) : '';
		if($target_id){
			$adv = ShareAdv::db()
				->where('platform','=',$tpl['platform'])
				->where('tpl_ename','=',$ename)
				->where('target_id','=',$target_id)
				->where('start_time','<=',time())
				->where('end_time','>=',time())
				->where('is_show','=',1)
				->orderBy('id','desc')
				->first();

			if($adv){
				if(!empty($adv['title'])) $out['title'] = $adv['title'];
				if(!empty($adv['icon'])) $out['pic'] = Utility::getImageUrl($adv['icon']);
				if(!empty($adv['weixin'])) $out['weixin'] = $adv['weixin'];
				if(!empty($adv['weibo'])) $out['weibo'] = $adv['weibo'];
				if(!empty($adv['redirect_url'])) {					
					$redirect_url = $adv['redirect_url'];
					//追加用户参数
					$redirect_url = self::appendParams($redirect_url,$append_params);
					if($monitor==true){//替换为监控链接
						$channel_id = 'channel_' . $adv['id'] . '_' . $target_id;
						$channel_name = $title;
						$config_id = 'config_' . $adv['id'] . '_' . $target_id . '_' . $uid;
						$config_name = $title;
						$redirect_url = self::makeMonitorUrl($channel_id,$channel_name,$config_id,$config_name,$redirect_url);						
					}
					
					$out['url'] = self::getShortUrl($redirect_url);
				}
			}
		}
		return $out;
	}
	
	public static function makeMonitorUrl($channel_id,$channel_name,$config_id,$config_name,$redirect_url,$callback_url='')
	{
		return self::makeMonitorUrlByDb($channel_id,$channel_name,$config_id,$config_name,$redirect_url,$callback_url);
		$channel_exists = MonitorService::isExistsChannel($channel_id);		
		if($channel_exists===false){
            $channel_exists = MonitorService::createChannel($channel_id,$channel_name,true);
		}		
		$config_os = 'ANDROID';						
		$config_exists = MonitorService::isExistsConfig($config_id);
		if($config_exists===false){
            $success = MonitorService::createConfig($config_id,$config_name,$config_os,$redirect_url,$channel_id,$callback_url,true);
            if($success){            	
               	$redirect_url = 'http://h5.youxiduo.com/statistic/click/' . $config_id;
            }            
		}else{
			$redirect_url = 'http://h5.youxiduo.com/statistic/click/' . $config_id;
		}
		return $redirect_url;
	}
	
	public static function makeMonitorUrlByDb($channel_id,$channel_name,$config_id,$config_name,$redirect_url,$callback_url='')
	{
		$is_active = 1;		
		$exists = DownloadChannel::db()->where('CHANNEL_ID','=',$channel_id)->first();	
		if(!$exists){
			$data = array('CHANNEL_ID'=>$channel_id,'CHANNEL_NAME'=>$channel_name,'IS_ACTIVE'=>$is_active,'CREATE_TIME'=>date('Y-m-d H:i:s'));
            DownloadChannel::db()->insert($data);
		}
		$config_os = 'ANDROID';
		$exists = StatisticConfig::db()->where('CONFIG_ID','=',$config_id)->first();
		if(!$exists){
			$data = array('CONFIG_ID'=>$config_id,'CONFIG_NAME'=>$config_name,'CONFIG_OS'=>$config_os,'CHANNEL_ID'=>$channel_id,'REDIRECT_URL'=>$redirect_url,'CREATE_TIME'=>date('Y-m-d H:i:s'));
		    $data['CLICK_CALL_BACK_URL'] = $callback_url;
            $success = StatisticConfig::db()->insert($data);
             if($success){  
             	$redirect_url = 'http://h5.youxiduo.com/statistic/click/' . $config_id;
             }
		}else{
			$redirect_url = 'http://h5.youxiduo.com/statistic/click/' . $config_id;
		}
		return $redirect_url;
	}
	
	protected static function appendParams($url,$params=array())
	{
		if($params && is_array($params)){
			$query = http_build_query($params);
			if(strpos($url,'?')===false){
				$url = $url . '?' . $query;
			}else{
				$url = $url . '&' . $query;
			}
		}
		return $url;
	}
	
	/**
	 * 生成短网址
	 * @param string $url 网址
	 * 
	 * @return string 短网址
	 */
	public static function getShortUrl($url)
	{
		try{
			$appkey = '1866242735';
			$api_url = 'https://api.weibo.com/2/short_url/shorten.json?source='.$appkey.'&url_long='.urlencode($url);
			$response = file_get_contents($api_url);
	        $json = json_decode($response, true);
	        $short_url = isset($json['urls'][0]['url_short']) ? $json['urls'][0]['url_short'] : $url;
	        return $short_url;
		}catch(\Exception $e){
			return $url;
		}
	}
}