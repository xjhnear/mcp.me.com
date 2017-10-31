<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/11
 * Time: 14:00
 */
namespace Youxiduo\Activity\Duang;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Youxiduo\Activity\Model\DuangGiftbag;
use Youxiduo\Activity\Model\DuangGiftbagCard;
use Youxiduo\Activity\Model\DuangShareGiftbagCard;
use Youxiduo\Activity\Model\DuangMain;
use Youxiduo\Helper\Utility;
use Youxiduo\User\Model\Account;
use Youxiduo\Activity\Share\ActivityService;
use Yxd\Services\UserService;

class DuangService{

    public static function robGiftbag($activity_id,$from_uid,$phone){
        //判断礼包还有没有
        $card_info = DuangGiftbagCard::getCardInfo($activity_id,0);
        if($card_info){
            //1.有就直接抢
            $up_result = DuangGiftbagCard::robUpdateCard($card_info['id'],array('is_get'=>1,'gettime'=>time()));
            if($up_result){
                if(self::addMainInfo($activity_id,$phone,$card_info['cardno'],$from_uid)){
                    return array('status'=>1,'msg'=>'抢到啦');
                }else{
                    return array('status'=>0,'msg'=>'很遗憾，礼包溜走了');
                }
            }else{
                return array('status'=>0,'msg'=>'手慢了，没抢到，再试一次吧~');
            }
        }else{
            //2.没有继续判断过期未领取的礼包
            $expired_record = Duangmain::getExpiredInfo($activity_id,time()-Config::get('duang.FAILOVER_TIME'));
            if($expired_record){
                //有过期未领取的
                $up_result = DuangMain::robUpdateInfo($expired_record['id'],array('updatetime'=>time(),'destroy'=>1));
                if($up_result){
                    if(self::addMainInfo($activity_id,$phone,$expired_record['giftcard'],$from_uid)){
                        return array('status'=>1,'msg'=>'抢到啦');
                    }else{
                        return array('status'=>0,'msg'=>'很遗憾，礼包溜走了');
                    }
                }else{
                    return array('status'=>0,'msg'=>'手慢了，没抢到，再试一次吧~');
                }
            }else{
                return array('status'=>0,'msg'=>'本次活动礼包已被抢光，下次请赶早哦~');
            }
        }
    }

    public static function addMainInfo($giftbag_id,$phone,$cardno,$fromuid){
        $now_time = time();
        $expire_time = $now_time + Config::get('duang.EXPIRE_TIME');
        $main_data = array(
            'giftbag_id'=>$giftbag_id,
            'phone'=>$phone,
            'giftcard'=>$cardno,
            'from_uid'=>$fromuid,
            'addtime'=>$now_time,
            'expiretime'=>$expire_time,
            'ip_address'=>Utility::getIp(),
            'received'=>0,
            'destroy'=>0
        );
        return DuangMain::addInfo($main_data);
    }

    public static function getShowList($giftbag_id,$from_uid){
        $list = DuangMain::getValidShowList($giftbag_id,$from_uid);
        $giftbag_info = DuangGiftbag::getInfo($giftbag_id);
        if($giftbag_info && $list){
            foreach ($list as &$row) {
                $row['game_name'] = $giftbag_info['game_name'];
                $row['addtime'] = date("m/d",$row['addtime']).'&nbsp;'.date("H:i",$row['addtime']);
                $uinfo = Account::getUserInfoByField($row['phone'],'mobile');
                if($uinfo) {
                    $row['avatar'] = Utility::getImageUrl($uinfo['avatar']);
                }
                $row['phone'] = substr($row['phone'],0,3).'****'.substr($row['phone'],7,4);
            }
        }
        return $list;
    }

    private static function getUnexpiredActivity(){
        $all = DuangGiftbag::getIsShowAllInfo();
        if(!$all) return false;
        $result = array();
        $now = time();
        foreach($all as $row){
            if($now - $row['endtime'] > 10800) continue; //活动已结束超过3小时
            $result[] = $row['id'];
        }
        return $result;
    }

    public static function autoSendCard(){
        $targetids = self::getUnexpiredActivity();
        if(!$targetids) return false;
        $pre_list = DuangMain::getAutoSendValidInfo($targetids);
        if($pre_list){
            foreach($pre_list as $list){
                $user_info = Account::getUserInfoByField($list['phone'],'mobile');
                $giftbag_info = DuangGiftbag::getInfo($list['giftbag_id']);
                if($user_info && $giftbag_info){
                    $des = $giftbag_info['share_prize_des'] ? $giftbag_info['share_prize_des'] : $giftbag_info['game_name'].'游戏';
                    //判断是否过期或者已经被人领取
                    if($list['expiretime'] < time() || $list['destroy'] == 1){
                        $msg_arr = array(
                            'content' => '很抱歉，由于您没有在规定的时间内完成注册登录，本次（'.$giftbag_info['title'].'）活动的'.$des.'礼包已过期失效',
                            'giftcard' => '。'
                        );
                        $up_result = DuangMain::updateInfo($list['id'],array('updatetime'=>time(),'send_msg'=>1));
                        if($up_result){
                            ActivityService::sendAndroidMsg($user_info['uid'],$msg_arr,'users');
                        }
                    }else{
                        $msg_arr = array(
                            'content' => '恭喜您，您在 '.$giftbag_info['title'].' 活动中获得了'.$des.'礼包一份。礼包码为：',
                            'giftcard' => $list['giftcard']
                        );
                        $res = ActivityService::sendAndroidMsg($user_info['uid'],$msg_arr,'users');
                        if($res && $res['errorCode']==0){
                            DuangMain::updateInfo($list['id'],array('updatetime'=>time(),'received'=>1,'send_msg'=>1));
                            $cardinfo = DuangGiftbagCard::getValidCardInfoByCardno($list['giftcard']);
                            if($cardinfo) {
                                DuangGiftbagCard::updateCard($cardinfo['id'],array('is_send'=>1,'gettime'=>time(),'user_id'=>$user_info['uid']));
                                DuangGiftbagCard::initCardNoNumber($cardinfo['giftbag_id']);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function autoSendSharemanAward(){
        $targetids = self::getUnexpiredActivity();
        if(!$targetids) return false;
        $pre_list = DuangMain::getAutoSendSharemanValidInfo($targetids);
        if($pre_list){
            foreach($pre_list as $list){
                $uinfo = UserService::getUserInfo($list['from_uid']);
                if(!$uinfo) continue;
                //是否已拿过
                $getted = DuangShareGiftbagCard::getShareGiftbagInfo($list['giftbag_id'],$list['from_uid'],1);
                if($getted) continue;
                $giftbag_info = DuangGiftbag::getInfo($list['giftbag_id']);
                if(!$giftbag_info) continue;
                if($list['num'] < $giftbag_info['need_times']) continue;
                $has_cards = DuangShareGiftbagCard::getLastValidCard($list['giftbag_id']);
                if(!$has_cards){
                    if($list['share_send_msg']) continue;
                    $msg_arr = array(
                        'content' => '很遗憾，您在 '.$giftbag_info['title'].' 活动中获得的礼包被手快的抢走了,下次再接再厉哦~',
                        'giftcard' => ''
                    );
                    $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                    if($res && $res['errorCode']==0){
                        DuangMain::updateInfo($list['id'],array('updatetime'=>time(),'share_send_msg'=>1));
                    }
                }else{
                    //更新礼包
                    $up_result = DuangShareGiftbagCard::updateOneRecord($list['giftbag_id'],$list['from_uid']);
                    if($up_result){
                        $cardinfo =  DuangShareGiftbagCard::getShareGiftbagInfo($list['giftbag_id'],$list['from_uid'],1);
                        if($cardinfo){
                            $des = $giftbag_info['share_prize_des'] ? $giftbag_info['share_prize_des'] : $giftbag_info['game_name'].'游戏';
                            $msg_arr = array(
                                'content' => '恭喜您，您在 '.$giftbag_info['title'].' 活动中获得了'.$des.'礼包一份。礼包码为：',
                                'giftcard' => $cardinfo['cardno']
                            );
                            $res = ActivityService::sendAndroidMsg($list['from_uid'],$msg_arr,'users');
                            if($res && $res['errorCode']==0){
                                DuangMain::updateInfo($list['id'],array('updatetime'=>time(),'share_send_msg'=>1));
                                DuangShareGiftbagCard::updateInfo($cardinfo['id'],array('is_send'=>1));
                                DuangShareGiftbagCard::initCardNoNumber($cardinfo['giftbag_id']);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function duangFixAuto(){
        set_time_limit(0);
        $db = DB::connection('share_activity');
        $wrong_result = $db->table('duang_main')
            ->select('duang_main.id', 'duang_giftbag_card.giftbag_id', 'duang_main.giftcard', 'duang_giftbag_card.user_id')
            ->leftJoin('duang_giftbag_card', 'duang_main.giftcard', '=', 'duang_giftbag_card.cardno')
            ->where('duang_main.giftbag_id', 13)
            ->where('duang_main.received', 1)
            ->where('duang_giftbag_card.giftbag_id', '<>', 13)
            ->get();
        if ($wrong_result) {
            foreach ($wrong_result as $list) {
                $new_card = $db->table('duang_giftbag_card')->where('giftbag_id', 13)->where('is_send', 0)->orderBy('id', 'desc')->first();
                if ($new_card) {
                    //发小秘书
                    $msg_arr = array(
                        'content' => '您之前中奖的刀塔传奇108钻礼包码为：',
                        'giftcard' => $new_card['cardno'] . '（对您带来的不便深感抱歉，敬请谅解）'
                    );
                    $res = ActivityService::sendAndroidMsg($list['user_id'], $msg_arr, 'users');
                    if ($res && $res['errorCode'] == 0) {
                        DuangMain::updateInfo($list['id'],array('giftcard'=>$new_card['cardno'],'updatetime'=>time()));
                        DuangGiftbagCard::updateCard($new_card['id'], array('is_send' => 1, 'gettime' => time(), 'user_id' => $list['user_id']));
                        DuangGiftbagCard::initCardNoNumber(13);
                        $fix_card = $db->table('duang_giftbag_card')->where('cardno', $list['giftcard'])->first();
                        if ($fix_card) {
                            $db->table('duang_giftbag_card')->where('id', $fix_card['id'])->update(array('is_send' => 0, 'is_get' => 0, 'gettime' => '', 'user_id' => null));
                            DuangGiftbagCard::initCardNoNumber($list['giftbag_id']);
                        }
                    }
                }
            }
        }
    }
}