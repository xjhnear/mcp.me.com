<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/4/1
 * Time: 14:24
 */
namespace Youxiduo\Activity\Lottery;

use Youxiduo\Activity\Model\DcActivity;
use Youxiduo\Activity\Model\DcJoin;
use Youxiduo\Activity\Model\DcLottery;
use Youxiduo\Activity\Model\DcPrize;
use Youxiduo\Android\PushService;
use Youxiduo\Helper\Utility;

class LotteryService{

    public static function validDcActivity($actcommand){
        if(!$actcommand) return false;
        $actinfo = DcActivity::getInfoByCommand($actcommand);
        if(!$actinfo || !$actinfo['is_open']) return false;
        $lotinfo = DcLottery::getInfo($actinfo['lottery_id']);
        if(!$lotinfo) return false;
        $przinfo = DcPrize::getInfoByIds(array($lotinfo['lottery_id']));
        if(!$przinfo) return false;
        return array('actinfo'=>$actinfo,'lotinfo'=>$lotinfo,'przinfo'=>$przinfo);
    }

    public static function validJoinTimes($uid,$actid,$lot_type,$limit){
        if(!$uid || !$actid || !$lot_type) return false;
        if($lot_type == 1 && !$limit) return false;
        $join_info = DcJoin::getInfo($uid,$actid);
        $result = true;
        if($lot_type == 1){
            if(count($join_info) >= $limit) $result = false;
        }else{
            if($join_info) $result = false;
        }
        return $result;
    }

    public static function doLottery($actid,$przinfo,$uid,$lot_type){
        if(!$uid || !$actid || !$przinfo || !$lot_type) return false;
        $data = array(
            'user_id' => $uid,
            'activity_id' => $actid,
            'lot_type' => $lot_type,
            'add_time' => time(),
            'if_win' => 0,
            'ip' => Utility::getIp()
        );
        if($lot_type == 1){
            $przids = array();
            $min = false;
            $total = 0;
            foreach($przinfo as &$prz){
                $prz['probab'] = $prz['probab']/100;
                $przids[] = $prz['prize_id'];
                if(!$min) $min = $prz['probab'];
                if($prz['probab'] < $min) $min = $prz['probab'];
            }
            $min_str = explode('.',$min);
            $beishu = isset($min_str[1]) ? pow(10,strlen($min_str[1])) : 1;
            $beishu = $beishu < 100 ? 100 : $beishu;
            foreach($przinfo as &$prz){
                $prz['probab'] *= $beishu;
                $total += $prz['probab'];
            }
            $przinfo[] = array(
                'title' => '未中奖',
                'probab' => $beishu - $total
            );
            $wininfo = DcJoin::getWinPrizeInfo($actid,$przids);
            if($wininfo){
                foreach($przinfo as &$prz){
                    if(isset($prz['prize_id']) && array_key_exists($prz['prize_id'],$wininfo) && $wininfo[$prz['prize_id']] >= $prz['number']) $prz = null;
                }
            }
            $przinfo = array_filter($przinfo);
            $pro_arr = array();
            foreach($przinfo as $row){
                $key = isset($row['prize_id']) ? $row['prize_id'] : -1;
                $pro_arr[$key] = $row['probab'];
            }
            //终于tmd抽奖了
            if($pro_arr['-1'] <= 0) $pro_arr['-1'] = 1;
            $lot_result = self::get_rand($pro_arr);
            if($lot_result > 0){  //中奖了卧槽
                $data['if_win'] = 1;
                foreach($przinfo as $row){
                    if($row['prize_id'] == $lot_result){
                        $data['prize_id'] = $row['prize_id'];
                        $data['prize_name'] = $row['title'];
                        $data['prize_des'] = $row['des'];
                        break;
                    }
                }
            }
        }
        if(DcJoin::add($data)){
            if($lot_type == 1){ //随时
                if($lot_result < 0){
                    return array('status'=>0,'msg'=>'很遗憾，您本次没有中奖');
                }else{
                    return array('status'=>1,'msg'=>'恭喜您，您抽中了 '.$data['prize_des'].' !');
                }
            }else{  //定时
                return array('status'=>2,'msg'=>'参与成功！请等待开奖');
            }
        }else{
            return array('status'=>-1,'msg'=>'系统繁忙，请稍后重试');
        }
    }

    public static function autoLottery(){
        $autolotinfos = DcLottery::getAutoLotInfo();
        if(!$autolotinfos) return false;
        $lotids = array();
        foreach($autolotinfos as $lot){
            $lotids[] = $lot['lottery_id'];
        }
        $actinfo = DcActivity::getInfoByLotids($lotids);
        if(!$actinfo) return false;
        //获取抽奖人数
        $win_num = false;
        foreach($autolotinfos as $row){
            if($actinfo['lottery_id'] == $row['lottery_id']) {
                $win_num = $row['winner_num'];
                break;
            }
        }
        if($win_num){
            //随机抽取获奖者
            $winners = DcJoin::getRandWinner($actinfo['activity_id'],$win_num);
            //获取奖项
            $prizes = DcPrize::getInfo('',$actinfo['lottery_id']);
            $tmplimit = $winids = array();

            $bigtitle = $actinfo['name'].'代充信息填写通知';
            $sub_info = unserialize($actinfo['sub_info']);
            if($sub_info){
                foreach($sub_info as &$row){
                    $row = urldecode($row);
                }
            }
            $appends = $sub_info;
            foreach($winners as $item){
                $gift = current($prizes);
                isset($tmplimit[$gift['prize_id']]) ? $tmplimit[$gift['prize_id']]++ : $tmplimit[$gift['prize_id']] = 1;
                $winids[$item['user_id']] = $gift['title'];
                if($tmplimit[$gift['prize_id']] >= $gift['number']) array_shift($prizes);
                if(PushService::sendLotterySubscribeMessage(array($item),$actinfo['activity_id'],$bigtitle,$actinfo['icon_path'],$appends)){
                    DcJoin::update($item['join_id'],array('if_win'=>1,'prize_id'=>$gift['prize_id'],'prize_name'=>$gift['title'],
                    'prize_des'=>$gift['des'],'update_time'=>time(),'msg_send'=>1));
                }
            }
        }
    }

    public static function updateUserSubinfo($uid,$info){
        if(!$uid || !$info) return false;
        $target = DcJoin::getLastValidJoinInfo($uid);
        if($target){
            return DcJoin::update($target['join_id'],array('update_time'=>time(),'sub_info'=>$info));
        }else{
            return false;
        }
    }

    public static function updateCommandsCache($commands){
        return Utility::loadByHttp('http://chat.youxiduo.com/set-commands-list',array('commands'=>$commands));
    }

    public static function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);             //抽取随机数
            if ($randNum <= $proCur) {
                $result = $key;                         //得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
}