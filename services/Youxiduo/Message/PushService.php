<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Message;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;

class PushService extends BaseService
{
	const API_URL_CONF = 'app.message_api_url';
	/**
	 * 获取消息列表
	 * @param array $params
	 * @return array
	 */
	
	public static function getMessageList($params = array()){
		$params_=array('uid','pageIndex','pageSize','registerTime','beginTime','endTime','hashValue');
		$result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'message/system_list');
		$total = self::getCount($params);
		$result['total'] = empty($total['totalCount']) ? 0 : $total['totalCount'];
		return $result;
	}
	
	/**
	 * @param array $params
	 * 获取消息条数
	 * @return int
	 */
	public static function getCount($params = array()){
		$params_=array('uid','registerTime','beginTime','endTime','hashValue');
		return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'message/system_totalnum');
		
	}
	
	/**
	 * 添加消息
	 * @param array $params
	 * @return Ambigous <NULL, multitype:unknown , mixed>
	 */
	public static function addMessage($params){
		$params_=array('title','content','toUid','sendTime','linkType','link','isTop','type','isPush','hashValue','allUser');
		return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'message/system_send','POST');
	}
	
	
	
	
	
}