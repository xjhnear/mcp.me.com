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

class YouPushService
{
    /**
     * 广播
     */
    const PUSH_TYPE_BROADCAST = 0;
    /**
     * 单播
     */
    const PUSH_TYPE_UNICAST = 1;
    /**
     * 组播
     */
    const PUSH_TYPE_MULTICAST = 2;

    /**
     * Android
     */
    const PUSH_PLATFORM_ANDROID = '3';

    /**
     * IOS
     */
    const PUSH_PLATFORM_APPLE = '4';

    const HOST_URL = 'http://youxiduo-java-slb-5:58080/service_push/';
    //const HOST_URL = 'http://test.youxiduo.com:8080/service_push/';

    /**
     * @param $tagName
     * @param $platform
     * @return bool
     */
    public static function CreateTag($tagName,$platform)
    {
        $apiurl = self::HOST_URL . 'tag/create_tag';
        $params = array('tagName'=>$tagName,'deviceType'=>$platform);
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $tagName
     * @param $platform
     * @return bool
     */
    public static function DeleteTag($tagName,$platform)
    {
        $apiurl = self::HOST_URL . 'tag/delete_tag';
        $params = array('tagName'=>$tagName,'deviceType'=>$platform);
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $tagName
     * @param $platform
     * @param int $pageIndex
     * @param int $pageSize
     * @return array
     */
    public static function QueryTag($tagName,$platform,$pageIndex=1,$pageSize=10)
    {
        $apiurl = self::HOST_URL . 'tag/query_tags';
        $params = array(
            'tagName'=>$tagName,
            'deviceType'=>$platform
        );
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;

        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
        return array('result'=>array(),'totalCount'=>0);
    }

    /**
     * @param $uid
     * @param $tagName
     * @param $platform
     * @return bool
     */
    public static function bindUserToTag($uid,$tagName,$platform)
    {
        if(is_array($uid)) $uid = implode(',',$uid);
        $apiurl = self::HOST_URL . 'backend/add_users_to_tag';
        $params = array('tagName'=>$tagName,'uid'=>$uid,'deviceType'=>$platform);
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $uid
     * @param $tagName
     * @param $platform
     * @return bool
     */
    public static function unbindUserFromTag($uid,$tagName,$platform)
    {
        if(is_array($uid)) $uid = implode(',',$uid);
        $apiurl = self::HOST_URL . 'backend/del_users_from_tag';
        $params = array('tagName'=>$tagName,'uid'=>$uid,'deviceType'=>$platform);
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $pushType
     * @param $beginTime
     * @param $endTime
     * @param int $pageIndex
     * @param int $pageSize
     * @param int $deviceType
     * @param int $pushPlatform
     * @return array
     */
    public static function searchMessageList($pushType,$beginTime,$endTime,$pageIndex=1,$pageSize=10,$deviceType = 3,$pushPlatform=1)
    {
        $apiurl = self::HOST_URL . 'backend/query_msg_log';
        $params = array();
        $pushType && $params['pushType'] = $pushType;
        $beginTime && $params['beginTime'] = $beginTime;
        $endTime && $params['endTime'] = $endTime;
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;
        $params['deviceType'] = $deviceType;
        $params['pushPlatform'] = $pushPlatform;

        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
        return array('result'=>array(),'totalCount'=>0);
    }

    /**
     * @param $title
     * @param $content
     * @param $type
     * @param $linkType
     * @param $link
     * @param $isPush
     * @param $version
     * @param $toUid
     * @param $tagName
     * @param $allUser
     * @param $pushPlatform
     * @return bool
     */
    public static function sendMessage($title,$content,$type,$linkType,$link,$isPush,$version,$toUid,$tagName,$allUser=false,$pushPlatform=1)
    {

        if($toUid && is_array($toUid)){
            $toUid = implode(',',$toUid);
        }
        $apiurl = self::HOST_URL . 'android/push_message';
        $params = array(
            'title'=>$title,
            'content'=>$content,
            'type'=>$type,
            'linkType'=>$linkType,
            'link'=>$link,
            'isPush'=>$isPush,
            'version'=>$version,
            'allUser'=>$allUser,
            'pushPlatform'=>$pushPlatform
        );

        if($toUid){
            $params['toUid'] = $toUid;
        }
        if($tagName){
            $params['tagName'] = $tagName;
        }

        $result = self::http($apiurl,$params,'POST','json');
        //print_r($result);exit;
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function recordUserDevice($uid,$channelId)
    {
        $apiurl = self::HOST_URL .'backend/update_user_channel';
        $params = array('uid'=>$uid,'channelId'=>$channelId,'deviceType'=>3);
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function http($url,$params=array(),$method='GET',$format='text',$multi = false, $extheaders = array())
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
                        $params_str = $format=='json' ? json_encode($params) : self::buildHttpQuery($params);
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
                        . (is_array($params) ? self::buildHttpQuery($params) : $params);
                }
                break;
        }
        //exit($url);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        //var_dump($response);exit();
        $status_code = curl_getinfo($ci,CURLINFO_HTTP_CODE);
        curl_close ($ci);
        if($status_code==200) return json_decode($response,true);
        //\Log::error($response);
        var_dump($response);exit();
        return false;
    }

    public static function buildHttpQuery($params)
    {
        $query_attr = array();
        foreach($params as $key=>$val){
            if(is_array($val)){
                foreach($val as $one){
                    $query_attr[] = $key . '=' . urlencode($one);
                }
            }else{
                $query_attr[] = $key . '=' . urlencode($val);
            }
        }
        return implode('&',$query_attr);
    }

    /**
     * 预约游戏礼包
     * @param $tagName
     * @param $uid
     * @return bool
     */
    public static function createGiftbagReserveNotice($tagName,$uid)
    {
        //
        if(is_array($tagName)){
            foreach($tagName as $tag){
                self::CreateTag($tag,self::PUSH_PLATFORM_ANDROID);
            }

        }else{
            self::CreateTag($tagName,self::PUSH_PLATFORM_ANDROID);
            self::bindUserToTag($uid,$tagName,self::PUSH_PLATFORM_ANDROID);
        }
        //
        return true;
    }

    /**
     * 取消游戏礼包预约
     * @param $tagName
     * @param $uid
     * @return bool
     */
    public static function removeGiftbagReserveNotice($tagName,$uid)
    {
        if(is_array($tagName)){
            foreach($tagName as $tag){
                //self::DeleteTag($tag,self::PUSH_PLATFORM_ANDROID);
            }

        }else{
            //self::DeleteTag($tagName,self::PUSH_PLATFORM_ANDROID);
            self::unbindUserFromTag($uid,$tagName,self::PUSH_PLATFORM_ANDROID);
        }
        return true;
    }
}
