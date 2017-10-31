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
use Youxiduo\Base\BaseService;
use Youxiduo\Message\Model\MessageTpl;
use Youxiduo\Helper\Utility;

class PushService extends BaseService
{
	public static function sendOneMessage()
	{
		
	}
	
	public static function sendGiftbagSubscribeMessage($giftbag_id,$continue=false)
	{
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
//		$content = array(
//		    'gid'=>$game_id,
//		    'gfid'=>$giftbag_id,
//		    'gname'=>$game['shortgname'],
//		    'img'=>Utility::getImageUrl($game['ico']),
//		    'title'=>$title,
//		    'giftTotalCount'=>$giftbag['total_num'],
//		    'giftLeftCount'=>$giftbag['last_num']
//		);
//
//		$params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
//		$params['title'] = $title;
//		$params['content'] = json_encode($content);
//		$params['linkType'] = 4;//礼包详情
//		$params['link'] = $giftbag_id;
//		$params['isTop'] = false;
//		$params['type'] = '11';
//		//$params['toUid'] = 5386037;
//		$params['isPush'] = true;
//		$params['allUser'] = false;
//		$params['sendTime'] = date('Y-m-d H:i:s');
//		$params['version'] = '';
//		$params['hashValue'] = '';
		
//		$url = Config::get('app.push_api_url').'android_push_message';
//		$uids = Reserve::db()->where('game_id','=',$game_id)->where('is_send','=',0)->lists('uid');
//		$uids = array_unique($uids);
//		$pages = array_chunk($uids,100);
//		$success = true;
//		foreach($pages as $sec){
//			$params['toUid'] = implode(',',$sec);
//			$result = Utility::loadByHttp($url,$params,'POST');

        $content = array(
		    'gid'=>$game_id,
		    'gfid'=>$giftbag_id,
		    'gname'=>$game['shortgname'],
		    'giftTotalCount'=>$giftbag['total_num'],
		    'giftLeftCount'=>$giftbag['last_num']
		);

        $tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$game_id;
        $send_res = BaiduPushService::pushTagMessage($title,Utility::getImageUrl($game['ico']),11,4,
        $giftbag_id,$tag_name,$content,false,true,true);
        if($send_res){
            $uids = Reserve::db()->where('game_id','=',$game_id)->where('is_send','=',0)->lists('uid');
            $uids = array_unique($uids);
            Reserve::db()->where('game_id','=',$game_id)->whereIn('uid',$uids)->update(array('is_send'=>1));
            return true;
        }else{
            return false;
        }
	}

    public static function sendLotterySubscribeMessage($wininfos,$actid,$bigtitle,$img,$appends=array())
	{
        if(!$wininfos || !$actid) return false;

        foreach($wininfos as $row){
            if($row['msg_send']) continue;
            $content = array(
                'title' => $bigtitle.'（'.$row['prize_name'].'）',
                'img' => Utility::getImageUrl($img),
                'msg' => $row['prize_des']
            );

            if($appends){
                foreach($appends as $k=>$v){
                    $content[$k] = $v;
                }
            }

            $params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
            $params['title'] = $bigtitle;
            $params['content'] = json_encode($content);
            $params['linkType'] = 0;//代充表单
            $params['link'] = '0';
            $params['isTop'] = false;
            $params['type'] = '14';
            $params['isPush'] = true;
            $params['allUser'] = false;
            $params['sendTime'] = date('Y-m-d H:i:s');
            $params['version'] = '';
            $params['hashValue'] = '';

            $url = Config::get('app.push_api_url').'android_push_message';
            $params['toUid'] = $row['user_id'];
            $result = Utility::loadByHttp($url,$params,'POST');
            if($result && $result['errorCode']=='0'){
                DcJoin::updateByActAndUser(array($row['user_id']),$actid,array('msg_send'=>1,'update_time'=>time()));
            }else{
                return false;
            }
        }
        return true;
	}

    public static function sendSubscribeMessage($uids,$bigtitle,$des,$img,$appends=array())
	{
        if(!$uids) return false;

        foreach($uids as $row){
            $content = array(
                'title' => $bigtitle,
                'img' => $img,
                'msg' => $des
            );

            if($appends){
                foreach($appends as $k=>$v){
                    $content[$k] = $v;
                }
            }

            $params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
            $params['title'] = $bigtitle;
            $params['content'] = json_encode($content);
            $params['linkType'] = 0;//代充表单
            $params['link'] = '0';
            $params['isTop'] = false;
            $params['type'] = '15';
            $params['isPush'] = true;
            $params['allUser'] = false;
            $params['sendTime'] = date('Y-m-d H:i:s');
            $params['version'] = '';
            $params['hashValue'] = '';

            $url = Config::get('app.push_api_url').'android_push_message';
            $params['toUid'] = $row;
            Utility::loadByHttp($url,$params,'POST');
        }
        return true;
	}

    public static function sendVariationGiftbagMessage($giftbag_id,$uid)
	{
        if(!$giftbag_id || !$uid) return false;
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
		    'img'=>Utility::getImageUrl($game['ico']),
		    'title'=>$title
		);

		$params['msgId'] = 'pushmessage'.date('YmdHis').Str::random(3);
		$params['title'] = $title;
		$params['content'] = json_encode($content);
		$params['linkType'] = 4;//礼包详情
		$params['link'] = $giftbag_id;
		$params['isTop'] = false;
		$params['type'] = '12';
		$params['isPush'] = true;
		$params['allUser'] = false;
		$params['sendTime'] = date('Y-m-d H:i:s');
		$params['version'] = '';
		$params['hashValue'] = '';

		$url = Config::get('app.push_api_url').'android_push_message';
		$params['toUid'] = $uid;
        $result = Utility::loadByHttp($url,$params,'POST');
        if($result && $result['errorCode']=='0'){
            return true;
        }else{
            return false;
        }
	}
}