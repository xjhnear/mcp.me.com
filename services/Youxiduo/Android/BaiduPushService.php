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
namespace Youxiduo\Android;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Config;
use Youxiduo\Activity\Model\DcJoin;
use Youxiduo\Android\Model\Reserve;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\UserDevice;
use Youxiduo\Base\BaseService;
use Youxiduo\Message\Model\MessageTpl;
use Youxiduo\Helper\Utility;
use Youxiduo\Message\YouPushService;

class BaiduPushService extends BaseService
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
     * 消息推送
     * @param string $title
     * @param string $content
     * @param string $linkType
     * @param string $link
     * @param string $type
     * @param string $toUid
     * @param string $isPush
     * @param string $allUser
     * @param string $version
     * @param string $pushType 推送类型[0:广播][1:单播][2:组播]
     * @param string $channel_id
     * @param string $tag_name
     *
     * @return bool
     */
	public static function sendMessage($title,$content,$linkType,$link,$type,$toUid,$isPush,$allUser,$version,$pushType,$channel_id,$tag_name,$pushPlatform=1,$version)
	{
        return YouPushService::sendMessage($title,$content,$type,$linkType,$link,$isPush,$version,$toUid,$tag_name,$allUser,$pushPlatform,$version);
        $params['title'] = $title;
        $params['content'] = $content;
        $params['linkType'] = $linkType;
        $params['link'] = $link;
        $params['type'] = $type;
        $params['toUid'] = $toUid;
        $params['isPush'] = $isPush;
        $params['allUser'] = $allUser;
        $params['version'] = $version;
        $params['tagName'] = $tag_name;
        $params['channelId'] = $channel_id;
        $params['pushType'] = $pushType;

	    $url = Config::get('app.push_api_url').'android/push_message';
        $result = Utility::loadByHttp($url,$params,'POST');
        if($result && $result['errorCode']=='0' && $result['result']){
            return true;
        }else{
            print_r($result);exit;
            return false;
        }
	}
	
	public static function sendGiftbagSubscribeMessage($giftbag_id,$continue=false){
        $giftbag = Giftbag::db()->where('id','=',$giftbag_id)->first();
		if(!$giftbag) return false;
		$game_id = $giftbag['game_id'];
		$game = Game::db()->where('id','=',$game_id)->first();
		if(!$game) return false;
		$continue==false && Reserve::db()->where('game_id','=',$game_id)->update(array('gift_id'=>$giftbag_id,'is_send'=>0));
		$tpl = MessageTpl::db()->where('ename','=','subscribe_giftbag_update')->first();
		if(!$tpl) return false;
		$message = str_replace('{game_name}',$game['shortgname'],$tpl['content']);
		$title = $message;
        $toUid = Reserve::db()->where('game_id','=',$game_id)->lists('uid');
        $content = array(
		    'gid'=>$game_id,
		    'gfid'=>$giftbag_id,
		    'gname'=>$game['shortgname'],
		    'giftTotalCount'=>$giftbag['total_num'],
		    'giftLeftCount'=>$giftbag['last_num']
		);

        $tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$game_id;
        $send_res = BaiduPushService::pushTagMessage($title,Utility::getImageUrl($game['ico']),11,4,
                                                    $giftbag_id,$tag_name,$content,false,true,true,implode(',',$toUid));
        if($send_res){
            Reserve::db()->where('game_id','=',$game_id)->update(array('is_send'=>1));
            return true;
        }else{
            return false;
        }
	}

    public static function sendLotterySubscribeMessage($wininfos,$actid,$bigtitle,$img,$appends=array()){
        if(!$wininfos || !$actid) return false;

        foreach($wininfos as $row){
            if($row['msg_send']) continue;

            $title = $bigtitle.'（'.$row['prize_name'].'）';
            $appends['msg'] = $row['prize_des'];

            $result = self::androidPushMessage($title,Utility::getImageUrl($img),14,0,0,$row['user_id'],$appends,false,true);
            if($result && $result['errorCode']=='0'){
                DcJoin::updateByActAndUser(array($row['user_id']),$actid,array('msg_send'=>1,'update_time'=>time()));
            }else{
                return false;
            }
        }
        return true;
	}

    public static function sendVariationGiftbagMessage($giftbag_id,$uid)
	{
        if(!$giftbag_id || !$uid) return false;
        //$device_info = UserDevice::getNewestInfoByUid($uid);
        //if(!$device_info) return false;
		$giftbag = Giftbag::db()->where('id','=',$giftbag_id)->first();
		if(!$giftbag) return false;
		$game_id = $giftbag['game_id'];
		$game = Game::db()->where('id','=',$game_id)->first();
		if(!$game) return false;
		$title = $giftbag['title'];
		$content = array(
		    'gid'=>$game_id,
		    'gfid'=>$giftbag_id,
		    'gname'=>$game['shortgname'],
		);

        return self::pushUnicastMessage($title,Utility::getImageUrl($game['ico']),12,4,$giftbag_id,$uid,null,
            null,$content,false,true);
	}

    /*-------------------------   百度推送新接口  --------------------------*/
    /*---------------    仅接口实现     -----------*/
    /**
     * 接口重要参数说明
     *
     * 1.预约礼包
     * type : 11
     * linkType : 4
     * link : $giftbag_id
     *
     * 2.礼包卡（变种分享）
     * type : 12
     * linkType : 4
     * link : $giftbag_id
     *
     * 3.抽奖表单
     * type : 14
     * linkType : 0
     * link : 0
     *
     * 4.代充表单
     * type : 15
     * linkType : 0
     * link : 0
     * 
     * 5.代充表单
     * type : 16
     * linkType : 0
     * link : 0
     * 
     * 6.通用格式
     * type : 17
     * appends array('title'=>'','describe'=>'','img'=>'','linkType'=>'','link'=>'')
     */	

    /**
     * Android广播消息接口
     * @param $title
     * @param $img
     * @param $type
     * @param $linktype
     * @param $link
     * @param int $toUid
     * @param bool $isTop
     * @param bool $isPush
     * @param bool $allUser
     * @param array $appends
     * @return bool
     */
    public static function androidPushMessage($title,$img,$type,$linktype,$link,$toUid=0,
                                              $appends=array(),$isTop=false,$isPush=false,$allUser=false,$pushPlatform=1){
        if($type){
            $content = array(
                'title' => $title,
                'img' => Utility::getImageUrl($img),
            );

            if($appends){
                foreach($appends as $k=>$v){
                    $content[$k] = $v;
                }
            }
            $content = json_encode($content);
        }else{
            $content = $appends;
        }
        return self::sendMessage($title,$content,$linktype,$link,$type,0,$isPush,$allUser,'',self::PUSH_TYPE_BROADCAST,'','',$pushPlatform);
        /*
        $params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
        $params['title'] = $title;
        $params['content'] = $content;
        $params['linkType'] = $linktype;
        $params['link'] = $link;
        $params['isTop'] = $isTop;
        $params['type'] = $type;
        $params['isPush'] = $isPush;
        $params['allUser'] = $allUser;
        $params['sendTime'] = date('Y-m-d H:i:s');
        $params['version'] = '';
        $params['hashValue'] = '';

        $url = Config::get('app.push_api_url').'android_push_message';
        $params['toUid'] = $toUid;
        $result = Utility::loadByHttp($url,$params,'POST');
        if($result && $result['errorCode']=='0'){
            return true;
        }else{
            return false;
        }
        */
    }

    /**
     * Android单播消息接口
     * @param $title
     * @param $img
     * @param $type
     * @param $linktype
     * @param $link
     * @param $toUid
     * @param $channelId
     * @param $userId
     * @param bool $isTop
     * @param bool $isPush
     * @param bool $allUser
     * @param array $appends
     * @return bool
     */
    public static function pushUnicastMessage($title,$img,$type,$linktype,$link,$toUid,$channelId,$userId,
                                              $appends=array(),$isTop=false,$isPush=false,$allUser=false,$pushPlatform=1,$version=""){
        //if(!$toUid || !$channelId) return false;

        if($type){
            $content = array(
                'title' => $title,
                'img' => Utility::getImageUrl($img),
            );

            if($appends){
                foreach($appends as $k=>$v){
                    $content[$k] = $v;
                }
            }
            $content = json_encode($content);
        }else{
            $content = $appends;
        }

        return self::sendMessage($title,$content,$linktype,$link,$type,$toUid,$isPush,$allUser,'',self::PUSH_TYPE_UNICAST,$channelId,'',$pushPlatform,$version);
        
        /*
        $params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
        $params['toUid'] = $toUid;
        $params['title'] = $title;
        $params['content'] = $content;
        $params['linkType'] = $linktype;
        $params['link'] = $link;
        $params['isTop'] = $isTop;
        $params['type'] = $type;
        $params['isPush'] = $isPush;
        $params['allUser'] = $allUser;
        $params['sendTime'] = date('Y-m-d H:i:s');
        $params['version'] = '';
        $params['channelId'] = $channelId;
        $params['userId'] = $userId;

        $url = Config::get('app.push_api_url').'android/push_unicast_message';
        $result = Utility::loadByHttp($url,$params,'POST');
        if($result && $result['errorCode']=='0'){
            return true;
        }else{
            return false;
        }
        */
    }


    public static function createTag($tag_name)
    {
    	if(!$tag_name) return false;
        $params = array('tagName'=>$tag_name);
        $url = Config::get('app.push_api_url').'tag/create_tag';
        $result = Utility::loadByHttp($url,$params);
        if($result && !$result['errorCode']){
            return true;
        }else{
            return false;
        }
    }

    public static function removeTag($tag_name)
    {
    	if(!$tag_name) return false;
        $params = array('tagName'=>$tag_name);
        $url = Config::get('app.push_api_url').'tag/delete_tag';
        $result = Utility::loadByHttp($url,$params);
        if($result && !$result['errorCode']){
            return true;
        }else{
            return false;
        }
    }

    public static function addTagDevice($tag_name,$channel_id)
    {
    	if(!$tag_name || empty($channel_id)) return false;
        $params = array('tagName'=>$tag_name);
    	if(is_array($channel_id)){
    		$params['channelId'] = implode(',',$channel_id);
    	}else{
    		$params['channelId'] = $channel_id;
    	}
        $url = Config::get('app.push_api_url').'tag/add_devices';
        $result = Utility::loadByHttp($url,$params);
        if($result && !$result['errorCode']){
            return true;
        }else{
            return false;
        }
    }

    public static function setTag($tag_name,$device_id=''){
        if(!$tag_name) return false;
        $params = array('tagName'=>$tag_name);
        if($device_id){
            if(is_array($device_id)){
                $device_id = implode(',',$device_id);
            }
            $params['userId'] = $device_id;
        }
        $url = Config::get('app.push_api_url').'set_tag';
        $result = Utility::loadByHttp($url,$params);
        if($result && !$result['errorCode']){
            return true;
        }else{
            return false;
        }
    }

    public static function pushTagMessage($title,$img,$type,$linktype,$link,$tag_name,$appends=array(),$isTop=false,$isPush=false,$allUser=false,$toUid,$pushPlatform=1){
        if(!$tag_name) return false;

        if($type){
            $content = array(
                'title' => $title,
                'img' => Utility::getImageUrl($img),
            );

            if($appends){
                foreach($appends as $k=>$v){
                    $content[$k] = $v;
                }
            }
            $content = json_encode($content);
        }else{
            $content = $appends;
        }
        if(strpos($toUid,',') !== false){
        	$a = explode(',',$toUid);
        	$limit = 300;
        	if(count($a)>$limit){
        		$groups = array_chunk($a,$limit);
        		foreach($groups as $group){
        			$groupToUid = implode(',',$group);
        			self::sendMessage($title,$content,$linktype,$link,$type,$groupToUid,$isPush,$allUser,'',self::PUSH_TYPE_MULTICAST,'',$tag_name,$pushPlatform);
        		}
        	}else{
        		return self::sendMessage($title,$content,$linktype,$link,$type,$toUid,$isPush,$allUser,'',self::PUSH_TYPE_MULTICAST,'',$tag_name,$pushPlatform);
        	}
        }else{
        	return self::sendMessage($title,$content,$linktype,$link,$type,$toUid,$isPush,$allUser,'',self::PUSH_TYPE_MULTICAST,'',$tag_name,$pushPlatform);
        }
        return true;
        /*
        $params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
        $params['toUid'] = 0;
        $params['title'] = $title;
        $params['content'] = $content;
        $params['linkType'] = $linktype;
        $params['link'] = $link;
        $params['isTop'] = $isTop;
        $params['type'] = $type;
        $params['isPush'] = $isPush;
        $params['allUser'] = $allUser;
        $params['sendTime'] = date('Y-m-d H:i:s');
        $params['version'] = '';
        $params['tagName'] = $tag_name;

        $url = Config::get('app.push_api_url').'android/push_tag_message';
        $result = Utility::loadByHttp($url,$params,'POST');
        if($result && $result['errorCode']=='0' && $result['result']){
            return true;
        }else{
            return false;
        }
        */
    }

    public static function deleteTag($tag_name,$device_id=''){
        if(!$tag_name) return false;
        $params = array('tagName'=>$tag_name);
        $device_id && $params['userId'] = $device_id;
        $url = Config::get('app.push_api_url').'delete_tag';
        $res = Utility::loadByHttp($url,$params);
        if($res && !$res['errorCode'] && $res['result']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 消息列表
     * @param string $pushType
     * @param int $pageIndex
     * @param int $pageSize
     * @param string $beginTime
     * @param string $endTime
     * @return bool|mixed|string
     */
    public static function getMsgList($pushType='',$pageIndex=1,$pageSize=10,$beginTime='',$endTime=''){
        $params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
        $pushType !=='' && $params['pushType'] = $pushType;
        $beginTime && $params['beginTime'] = $beginTime;
        $endTime && $params['endTime'] = $endTime;
        $url = Config::get('app.push_api_url').'backend/query_msg_log';
        return Utility::loadByHttp($url,$params);
    }
}