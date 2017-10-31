<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/4/26
 * Time: 17:54
 */
namespace Youxiduo\Activity\Duang;

use Youxiduo\Activity\Model\Variation\VariationMoney;
use Youxiduo\Activity\Model\Variation\VariationSelect;
use Youxiduo\Android\BaiduPushService;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\User\UserService;
use Youxiduo\User\Model\Account;
use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Model\Variation\VariationMain;
use Youxiduo\Activity\Model\Variation\VariationActivity;
use Youxiduo\Activity\Model\Variation\GiftbagList;
use Youxiduo\Activity\Model\Variation\GiftbagDepot;
use Youxiduo\Activity\Model\Variation\ActDepRelate;
use Youxiduo\Activity\Model\Variation\ShareRecord;
use Youxiduo\V4\User\MoneyService;
use Youxiduo\V4\Common\ShareService;
use Illuminate\Support\Facades\Log;

class VariationService{
    private static function getUnexpiredActivity(){
        $all = VariationActivity::getIsShowAllInfo();
        if(!$all) return false;
        $result = array();
        $now = time();
        foreach($all as $row){
            if($now - $row['endtime'] > 10800) continue; //活动已结束超过3小时
            $result[] = $row['activity_id'];
        }
        return $result;
    }

    public static function autoSendCard(){
        $targetids = self::getUnexpiredActivity();
        if(!$targetids) return false;
        $pre_list = VariationMain::getValidAutoSendList($targetids);
        if(!$pre_list) return;
        foreach($pre_list as $list){
            $user_info = Account::getUserInfoByField($list['phone'],'mobile');
            $act_info = VariationActivity::getInfo($list['activity_id']);
            if($user_info && $act_info){
                $depot_info = GiftbagDepot::getInfo($list['depot_id']);
                $des = $list['depot_id'] == -1 ? $act_info['money'].'游币奖励' : $depot_info['name'];
                //判断是否过期
                if($list['expiretime'] < time()){
                    $msg_arr = array(
                        'content' => '很抱歉，由于您没有在规定的时间内完成注册登录，本次（'.$act_info['title'].'）活动的'.$des.'已过期失效',
                        'giftcard' => '。'
                    );
                    $res = ActivityService::sendAndroidMsg($user_info['uid'],$msg_arr,'users');
                    if($res && $res['errorCode']==0){
                        VariationMain::update($list['main_id'],array('updatetime'=>time(),'send_msg'=>1));
                    }
                }else{
                    if($list['depot_id'] == -1 && $act_info['money']){
                        $up_result = VariationMain::update($list['main_id'],array('updatetime'=>time(),'received'=>1,'send_msg'=>1,'uid'=>$user_info['uid']));
                        $money_data = array(
                            'activity_id' => $act_info['activity_id'],
                            'user_id' => $user_info['uid'],
                            'money' => $act_info['money'],
                            'addtime' => time(),
                            'type' => 'newer'
                        );
                        $add_id = VariationMoney::add($money_data);
                        if($up_result && $add_id){
                            if(MoneyService::doAccount($user_info['uid'],$act_info['money'],'activity_consume','分享活动奖励游币')){
                                $msg_arr = array(
                                    'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$des.',请于“商城”内查看',
                                    'giftcard' => '。'
                                );
                                ActivityService::sendAndroidMsg($user_info['uid'],$msg_arr,'users');
                            }else{
                                VariationMain::update($list['main_id'],array('updatetime'=>time(),'received'=>0,'send_msg'=>0));
                                VariationMoney::delete($add_id);
                            }
                        }
                    }else{
                        if(!$depot_info) continue;
//                        $msg_arr = array(
//                            'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$des.'礼包一份。请查看我的礼包箱',
//                            'giftcard' => '。'//$list['giftcard']
//                        );
                        $depot_info['uid'] = $user_info['uid'];
                        $depot_info['card_no'] = $list['giftcard'];
                        $up_result = VariationMain::update($list['main_id'],array('updatetime'=>time(),'received'=>1,'send_msg'=>1,'uid'=>$user_info['uid']),$depot_info);
                        if($up_result){
//                            ActivityService::sendAndroidMsg($user_info['uid'],$msg_arr,'users');
                            $res = BaiduPushService::sendVariationGiftbagMessage($depot_info['m_giftbag_id'],$user_info['uid']);
//                            if($res && !$res['errorCode']){
                                $cardinfo = GiftbagList::getValidCardInfoByCardno($list['depot_id'],$list['giftcard']);
                                if($cardinfo) {
                                    GiftbagList::update($cardinfo['list_id'],array('is_send'=>1,'updatetime'=>time(),'user_id'=>$user_info['uid']));
                                    GiftbagDepot::initCardNumber($list['depot_id']);
                                }
//                            }
                        }
                    }
                }
            }
        }
    }

    public static function autoSendSharemanAward(){
        $targetids = self::getUnexpiredActivity();
        if(!$targetids) return false;
        $pre_list = VariationMain::getAutoSendSharemanValidInfo($targetids);
        if(!$pre_list) return;
        foreach($pre_list as $list){
            $uinfo = UserService::getUserInfoByUid($list['from_uid']);
            if(!is_array($uinfo)) continue;
            $act_info = VariationActivity::getInfo($list['activity_id']);
            if(!$act_info) continue;
            if($list['num'] < $act_info['need_times']) continue;
            $select_info = VariationSelect::getInfo($list['activity_id'],$uinfo['uid']);
            if(!$select_info){
                if($act_info['s_money']){
                    //游币
                    $getted = VariationMoney::getInfo($act_info['activity_id'],$uinfo['uid'],'sharer');
                    if($getted) continue;
                    $money_data = array(
                        'activity_id' => $act_info['activity_id'],
                        'user_id' => $uinfo['uid'],
                        'money' => $act_info['s_money'],
                        'addtime' => time(),
                        'type' => 'sharer'
                    );
                    $add_id = VariationMoney::add($money_data);
                    if($add_id){
                        if(MoneyService::doAccount($uinfo['uid'],$act_info['s_money'],'activity_consume','分享活动奖励游币')){
                            $msg_arr = array(
                                'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$act_info['s_money'].'游币奖励,请于“商城”内查看',
                                'giftcard' => '。'
                            );
                            ActivityService::sendAndroidMsg($uinfo['uid'],$msg_arr,'users');
                            self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
                        }else{
                            VariationMoney::delete($add_id);
                        }
                    }
                }else{
                    $relate = ActDepRelate::getTargetList('variation',$list['activity_id'],'','sharer');
                    if(!$relate) continue;
                    $depot_id = $relate[0]['depot_id'];
                    //礼包
                    $depot_info = GiftbagDepot::getInfo($depot_id);
                    if(!$depot_info) continue;
                    $getted = GiftbagList::getSharedCardRecord($depot_id,$list['from_uid']);
                    if($getted) continue;
                    $has_cards = GiftbagList::getLastValidCard($depot_id);
                    if(!$has_cards){
                        if($list['share_send_msg']) continue;
                        $msg_arr = array(
                            'content' => '很遗憾，您在 '.$act_info['title'].' 活动中的分享礼包已经被抢光了,请继续关注其他活动，下次再接再厉哦~',
                            'giftcard' => ''
                        );
                        $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                        if($res && $res['errorCode']==0){
                            VariationMain::update($list['main_id'],array('updatetime'=>time(),'share_send_msg'=>1));
                            self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
                        }
                    }else{
                        //更新礼包
                        $depot_info['uid'] = $list['from_uid'];
                        $list_id = GiftbagList::updateOneRecord($depot_id,$list['from_uid'],$depot_info);
                        if($list_id){
                            $des = $depot_info['name'];
                            $msg_arr = array(
                                'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$des.'礼包一份。请查看我的礼包箱',
                                'giftcard' => '。'//$cardinfo['cardno']
                            );
                            $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                            BaiduPushService::sendVariationGiftbagMessage($depot_info['m_giftbag_id'],$list['from_uid']);
                            //if($res && $res['errorCode']==0){
                                VariationMain::update($list['main_id'],array('updatetime'=>time(),'share_send_msg'=>1));
                                GiftbagList::update($list_id,array('is_get'=>1,'is_send'=>1,'updatetime'=>time()));
                                GiftbagDepot::initCardNumber($depot_id);
                                self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
                            //}
                        }
                    }
                }
            }else{
                $select_info = current($select_info);
                if($select_info['depot_id'] == -1){
                    //游币
                    $getted = VariationMoney::getInfo($act_info['activity_id'],$uinfo['uid'],'sharer');
                    if($getted) continue;
                    $money_data = array(
                        'activity_id' => $act_info['activity_id'],
                        'user_id' => $uinfo['uid'],
                        'money' => $act_info['s_money'],
                        'addtime' => time(),
                        'type' => 'sharer'
                    );
                    $add_id = VariationMoney::add($money_data);
                    if($add_id){
                        if(MoneyService::doAccount($uinfo['uid'],$act_info['s_money'],'activity_consume','分享活动奖励游币')){
                            $msg_arr = array(
                                'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$act_info['s_money'].'游币奖励,请于“商城”内查看',
                                'giftcard' => '。'
                            );
                            VariationSelect::update($select_info['select_id'],array('is_get'=>1));
                            ActivityService::sendAndroidMsg($uinfo['uid'],$msg_arr,'users');
                            self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
                        }else{
                            VariationMoney::delete($add_id);
                        }
                    }
                }else{
                    $depot_id = $select_info['depot_id'];
                    //礼包
                    $depot_info = GiftbagDepot::getInfo($depot_id);
                    if(!$depot_info) continue;
                    $getted = GiftbagList::getSharedCardRecord($depot_id,$list['from_uid']);
                    if($getted) continue;
                    $has_cards = GiftbagList::getLastValidCard($depot_id);
                    if(!$has_cards){
                        if($list['share_send_msg']) continue;
                        $msg_arr = array(
                            'content' => '很遗憾，您在 '.$act_info['title'].' 活动中的分享礼包已经被抢光了,请继续关注其他活动，下次再接再厉哦~',
                            'giftcard' => ''
                        );
                        $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                        if($res && $res['errorCode']==0){
                            VariationMain::update($list['main_id'],array('updatetime'=>time(),'share_send_msg'=>1));
                            self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
                        }
                    }else{
                        //更新礼包
                        $depot_info['uid'] = $list['from_uid'];
                        $list_id = GiftbagList::updateOneRecord($depot_id,$list['from_uid'],$depot_info);
                        if($list_id){
//                            $des = $depot_info['name'];
//                            $msg_arr = array(
//                                'content' => '恭喜您，您在 '.$act_info['title'].' 活动中获得了'.$des.'礼包一份。请查看我的礼包箱',
//                                'giftcard' => '。'//$cardinfo['cardno']
//                            );
//                            $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                            $res = BaiduPushService::sendVariationGiftbagMessage($depot_info['m_giftbag_id'],$list['from_uid']);
//                            if($res && !$res['errorCode']){
                                VariationSelect::update($select_info['select_id'],array('is_get'=>1,'update_time'=>time()));
                                VariationMain::update($list['main_id'],array('updatetime'=>time(),'share_send_msg'=>1));
                                GiftbagList::update($list_id,array('is_get'=>1,'is_send'=>1,'updatetime'=>time()));
                                GiftbagDepot::initCardNumber($depot_id);
                                self::notifyThirdTask($act_info['taskId'],$uinfo['uid']);
//                            }
                        }
                    }
                }
            }

        }
    }
    
    public static function makeShareUrl($redirect_url,$act_id,$uid,$title)
	{
	    $channel_id = 'channel_shareactivity_' . $act_id;
		$channel_name = $title;
		$config_id = 'config_shareactivity_' . $act_id . '_' . $uid;
		$config_name = $title;
		$redirect_url = ShareService::makeMonitorUrl($channel_id,$channel_name,$config_id,$config_name,$redirect_url);
		return ShareService::getShortUrl($redirect_url);
	}	

    public static function getV3NeedInfo($act_id,$uid=''){
        $actinfo = VariationActivity::getInfoByArticleid($act_id);
        if(!$actinfo) return false;
        $data = array();
        $data['v3_actid'] = $actinfo['activity_id'];
        $data['share_title'] = $actinfo['share_title'];
        $data['share_des'] = $actinfo['share_des'];
        $data['share_pic'] = Utility::getImageUrl($actinfo['share_pic']);
        $data['url'] = self::makeShareUrl('http://share.youxiduo.com/android/share/home?hashcode='.$actinfo['hashcode'].'&uid='.$uid,$act_id,$uid,$actinfo['share_title']);
        $data['is_over'] = 0;
        $share_giftbag = ActDepRelate::getTargetList('variation',$actinfo['activity_id'],'','sharer');
        if($share_giftbag){
            $depot_ids = array();
            foreach ($share_giftbag as $row) {
                $depot_ids[] = $row['depot_id'];
            }
            $dptinfos = GiftbagDepot::getInfo($depot_ids);
            if($dptinfos){
                if($uid){
                    $selected = VariationSelect::getInfo($actinfo['activity_id'],$uid);
                    $selected = $selected ? current($selected) : false;
                    $selected['is_get'] && $data['is_over'] = 1;
                }
                foreach($dptinfos as $item){
                    if(!$item['last_num']) continue;
                    $data['giftbags'][] = array(
                        'depot_id' => $item['depot_id'],
                        'name' => $item['name'],
                        'icon' => Utility::getImageUrl($item['icon']),
                        'des' => $item['description'],
                        'checked' => isset($selected) && $selected['depot_id'] == $item['depot_id'] ? 1 : 0
                    );
                }
            }
        }
        if($actinfo['s_money']){
            $data['giftbags'][] = array(
                'depot_id'=>-1,
                'name'=>$actinfo['s_money'].'游币',
                'icon'=>'http://img.youxiduo.com/userdirs/duang/share/static/img/money.png',
                'des'=>$actinfo['s_money'].'游币奖励',
                'checked' => isset($selected) && $selected['depot_id'] == -1 ? 1 : 0
            );
        }
        return $data;
    }

    public static function notifyThirdTask($taskId,$uid,$deviceCode='')
    {
        \Log::info('exec callback start taskId:' . $taskId . 'uid:' . $uid);
        if(!$taskId || !$uid) return false;
        $apiurl = 'http://youxiduo-java-slb-5:58080/module_task/task/finish_task';
        $params = array('taskId'=>$taskId,'uid'=>$uid,'deviceId'=>$deviceCode);
        $url = $apiurl . '?' . http_build_query($params);
        $result = file_get_contents($url);
        \Log::info('callback:'.$url.$result);
    }

    /**
     * @param $aid
     * @param $uid
     * @param $sharetype
     * @param $taskId
     * @return string
     */
    public static function getV3ShareContent($aid,$uid,$sharetype,$taskId=null)
    {
        $actinfo = null;
        if($sharetype=='activity'){
            $actinfo = VariationActivity::getInfoByArticleid($aid);
        }elseif($sharetype=='task'){
            $actinfo = VariationActivity::getInfo($aid);
        }

        if($actinfo){
            $sharecode = md5($aid . '-' . $uid);
            $codesource = json_encode(array('aid'=>$aid,'uid'=>$uid,'sharetype'=>$sharetype,'taskId'=>$taskId));
            $success = ShareRecord::saveData($sharecode,$codesource);
            if($success){
                return '#' . $sharecode . '#';
            }
        }
        return '';
    }

    public static function getV3ActivityUrlByShareCode($sharecode)
    {
        $info = ShareRecord::getInfo($sharecode);
        if(!$info) return '';
        $codesource = json_decode($info['codesource'],true);
        $aid = $codesource['aid'];
        $uid = $codesource['uid'];
        $sharetype = $codesource['sharetype'];
        if($sharetype=='activity'){
            $actinfo = VariationActivity::getInfoByArticleid($aid);
        }elseif($sharetype=='task'){
            $actinfo = VariationActivity::getInfo($aid);
        }

        $shareurl = 'http://share.youxiduo.com/android/share/home?hashcode=';
        $url = self::makeShareUrl($shareurl . $actinfo['hashcode'].'&uid='.$uid,$aid,$uid,$actinfo['share_title']);
        $out = array();
        $out['url'] = $url;
        $out['v3share'] = $aid;
        $out['sharetype'] = $sharetype;
        $out['taskId'] = $codesource['taskId'];
        return $out;
    }
}
