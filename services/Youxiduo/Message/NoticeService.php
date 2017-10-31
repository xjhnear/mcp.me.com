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

use Youxiduo\Android\BaiduPushService;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Yxd\Services\UserService;
use Youxiduo\Android\Model\UserDevice;

class NoticeService extends BaseService
{
	/**
	 * 
	 */
	public static function search($search,$pageIndex=1,$pageSize=15)
	{
		$start = date('Y-m-d H:i:s',mktime(0,0,0,'10','01','2014'));
		
		$params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
		$params['uid'] = isset($search['uid']) ? $search['uid'] : null;
		$params['beginTime'] = $start;
		$params['endTime'] = date('Y-m-d H:i:s');
		
		$out = array('result'=>array(),'total'=>0);		
		$result = Utility::loadByHttp('module_message/message/system_list',$params);
		$total  = Utility::loadByHttp('module_message/message/system_totalnum',$params);
		$out['result'] = $result;
		$out['total'] = $total['messageNum'];
		return $out;
	}
	
	public static function sendMessage($title,$content,$linkType,$link,array $toUids,$isPush,$isAllUser=false,$type=0,$pushPlatform=1,$version="")
	{
		if(!is_array($toUids) || !$toUids) return false;
		$toUids = array_unique($toUids);
		/*
		foreach($toUids as $toUid){
			self::sendOneMessage($title, $content, $linkType, $link, $toUid, $isPush,$isAllUser);
		}
		*/
		$channel_ids = UserDevice::db()->whereIn('uid',$toUids)->lists('channel_id');
		//print_r($toUids);
		//echo implode(',',$toUids);
		BaiduPushService::pushUnicastMessage($title,'',$type,$linkType,$link,implode(',',$toUids),implode(',',$channel_ids),'',$content,false,$isPush,false,$pushPlatform,$version);
		//exit;
		//BaiduPushService::pushTagMessage($title,'',$type,$linkType,$link,'test',$content,false,$isPush,false,implode(',',$toUids));
		return true;
	}
	
	public static function sendOneMessage($title,$content,$linkType,$link,$toUid,$isPush,$isAllUser=false,$type=0,$pushPlatform=1,$version="")
	{
//		$result = Utility::loadByHttp('module_message/message/system_send',$params,'POST');
        if($isAllUser){
            $res = BaiduPushService::androidPushMessage($title,'',$type,$linkType,$link,0,$content,false,$isPush,$isAllUser,$pushPlatform,$version);
            if($res['errorCode']==0)    return true;
            return false;
        }else{
            $device_info = UserDevice::getNewestInfoByUid($toUid);
            $res = BaiduPushService::pushUnicastMessage($title,'',$type,$linkType,$link,$toUid,
                $device_info['channel_id'],$device_info['device_id'],$content,false,$isPush,false,$pushPlatform,$version);
            if($res['errorCode']==0)    return true;
            return false;
        }
	}
	
	/**
	 * 获取未读消息数
	 * @param int $uid
	 * @param int $type
	 * @param string $hashValue
	 * @return boolean|mixed
	 */
	public static function getUnreadMsgNum($uid,$type,$hashValue=null){
		if(!$uid) return false;
		$uinfo = UserService::getUserInfo($uid);
		if(!$uinfo) return false;
		$register_time = date("Y-m-d H:i:s",$uinfo['dateline']);
		$params = array(
			'uid' => $uid,
			'type' => $type,
			'registerTime' => $register_time
		);
		if($hashValue) $params['hashValue'] = $hashValue;
		$result = Utility::loadByHttp('module_message/message/system_unreadnum',$params);
		return $result;
	}
	
	/**
	 * 获取系统消息列表
	 * @param string $uid
	 * @param number $pageIndex
	 * @param number $pageSize
	 * @param string $registerTime
	 * @param string $beginTime
	 * @param string $endTime
	 * @param string $hashValue
	 * @return mixed
	 */
	public static function getSystemMsgList($uid=null,$pageIndex=1,$pageSize=10,$registerTime=null,$beginTime=null,$endTime=null,$hashValue=null){
		if($uid && !$registerTime){
			$uinfo = UserService::getUserInfo($uid);
			if($uinfo) $registerTime = date("Y-m-d H:i:s",$uinfo['dateline']);
		} 
		
		$params = array(
			'uid' => $uid,
			'pageIndex' => $pageIndex,
			'pageSize' => $pageSize,
			'registerTime' => $registerTime,
			'beginTime' => $beginTime,
			'endTime' => $endTime
		);
		if($hashValue) $params['hashValue'] = $hashValue;
		$result = Utility::loadByHttp('module_message/message/system_list',$params);
		return $result;
	}
	
	/**
	 * 获取系统信息详情
	 * @param string $msgid
	 * @param string $hashValue
	 * @return boolean|mixed
	 */
	public static function getSystemMsgDetail($uid,$msgid,$hashValue=null){
		if(!$uid || !$msgid) return false;
		$params['msgid'] = $msgid;
		if($hashValue) $params['hashValue'] = $hashValue;
		
		$result = Utility::loadByHttp('module_message/message/system_detail',$params);
		Utility::loadByHttp('module_message/message/system_read',array('uid'=>$uid,'msgid'=>$msgid,'readType'=>1));
		return $result;
	}
	
	
}