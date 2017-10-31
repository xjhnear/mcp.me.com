<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/1
 * Time: 14:49
 */

namespace Youxiduo\Game\Plat;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Cms\GameInfo;
use Youxiduo\Cms\Model\Addongame;
use Youxiduo\Cms\Model\Archives;
use Youxiduo\Game\Model\GamesApk;
use Youxiduo\Game\Model\Plat360Game;
use Youxiduo\Game\Model\Plat360Log;
use Youxiduo\Game\Model\Plat360Sync;
use Youxiduo\Game\Model\Plat360SyncLog;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\Game\Model\IosGame;

class Game360PlatService{
    public static function getGame($params=array()){
        $path = Config::get('app.plat360_path');
        $from = Config::get('app.from360');
        $SecretKey = Config::get('app.SecretKey');
        $params['from'] = $from;
        if(empty($params['type'])) $params['type'] = 2;
        if(empty($params['pagesize'])) $params['pagesize'] = 300;
        if(empty($params['page'])) $params['page'] = 1;
        ksort($params);

        $questring = "";
        if(!empty($params)){
            foreach($params as $k => $v){
                if($questring == ''){
                    $questring .= "$k=$v";
                }else{
                    $questring .= "&$k=$v";
                }
            }
        }
        $sign = md5($questring.$SecretKey);
        $path = $path."/app/list?$questring&sign=$sign";
        $time = date('Y-m-d H:i:s');
        //$message = "执行时间{$time} 地址为:{$path}";
        //self::writeSuccessLog($message);
        $data = $params;
        $data['url'] = $path;
        $result = Utility::preParamsOrCurlProcess(array(),array('id'),$path);
        if(isset($result['items'])) $data['result'] = 1;
        $logid = self::writeLog($data);
        $result['logid'] = $logid;
        return $result;
    }

    /**
     * 获取单个游戏信息
     * @param array $params
     * @return array|bool|mixed|string
     */
    public static function getSingleGame($params=array()){
        $path = Config::get('app.plat360_path');
        $from = Config::get('app.from360');
        $begin = array('appid','from');
        $SecretKey = Config::get('app.SecretKey');
        $params['from'] = $from;
        $questring = "";
        foreach($begin as $v){
            if(isset($params[$v]) && !empty($params[$v])){
                if($questring == ''){
                    $questring .= "$v=$params[$v]";
                }else{
                    $questring .= "&$v=$params[$v]";
                }
            }
        }
        $sign = md5($questring.$SecretKey);
        $path = $path."/app/get?$questring&sign=$sign";
        $data = $params;
        $data['url'] = $path;
        $result = Utility::preParamsOrCurlProcess(array(),array('id'),$path);
        return $result;
    }

    /**
     * 执行抓取数据
     * @param bool $mark
     */
    public static function syncGame($mark = true){
        $sort = $mark ? 'desc' : 'asc';
        //查询开始
        $maxTimeGame = Plat360Game::getFirstGame($sort);
        $now = time();
        if(!empty($maxTimeGame['updateTime'])){
            $updateTime = strtotime($maxTimeGame['updateTime']);
        }else{
            $updateTime = $now;
        }
        if($mark){
            if($updateTime > $now){
                $params['starttime'] = $updateTime;
                $params['endtime'] = $now;
            }
        }else{
            $params['endtime'] = $updateTime;
        }

        //获取游戏总条数
        set_time_limit (0);
        $params['pagesize'] = 300;
        $game = self::getGame($params);
        $t = 0;
        if(!isset($game['total'])) return false;
        if($game['total'] > $params['pagesize']){
            $page = ceil($game['total']/$params['pagesize']);
            for($i = 1;$i<=$page;$i++){
                $params['page'] = $i;
                sleep(5);
                $game_ = self::getGame($params);
                if(!isset($game_['items'])){
                    $i = $i+1;
                    sleep(5);
                    //print_r($game_);exit;
                }
                self::checkGame($game_['items']);
                $t += Plat360Game::addGame($game_['items']);

            }
        }else{
            self::checkGame($game['items']);
            $t += Plat360Game::addGame($game['items']);
        }
        print_r($t);exit;
    }

    /**
     * 检查是否已存在此游戏
     * @param $data
     * @param bool $mark
     * @return array
     */
    private static function checkGame(&$data,$mark=false){
        $arr = $params_get = $result = array();
        foreach($data as $k => $v){
            $arr[$v['id']] = $k;
            $params_get[] = $v['id'];
        }
        $result_me = Plat360Game::getMeGame($params_get);
        if(!empty($result_me)){
            foreach($result_me as $v){
                if($mark) $result[] = $data[$arr[$v['id']]];
                unset($data[$arr[$v['id']]]);
            }
        }
        return $result;
    }

    /**
     * 获取游戏列表信息
     */
    public static function minSyncGame(){
        set_time_limit (0);
        $params['pagesize'] = 300;
        //$params['endtime'] = strtotime('2015-4-2 23:59:59');
        $params['strattime'] = strtotime('2015-4-2 23:59:59');
        $total = Plat360Log::getEndLog($params,array('page'));
        $page = isset($total['page']) ? $total['page']+1 : 1 ;
        $params['page'] = $page;
        $game = self::getGame($params);
        $t = 0;
        if(!isset($game['total'])) return false;
        //总页码
        $totalpage = ceil($game['total']/$params['pagesize']);
        $i = $page;
        do{
            $datas = self::checkGame($game['items'],true);
            self::upGames($datas);
            $result = Plat360Game::addGame($game['items']);
            if($result > 0 && isset($game['logid'])){
                //更新Log状态
                Plat360Log::upLog($game['logid']);
            }
            $t += $result;
            $i++;
            $params['page'] = $i;
            if($i>$totalpage) return false;
            sleep(5);
            if($i!=$page+10){
                $game = self::getGame($params);
                if(!isset($game['items'])){
                    $i--;
                    sleep(5);
                    print_r($game);exit;
                }
            }
        }while($i<$page+10);
        print_r($t);exit;

    }

    /**
     * 更新www库里面游戏链接
     * @param $data
     * @param bool $make  为 false 执行回滚操作
     * @return bool
     */
    public static function upWwwGames($data,$make = true){
        //查找old 链接
        $old_result = GamePlat::getDownload($data['id']);
        if($old_result && $old_result['downurl'] != $data['downurl']){
            try{
                DB::transaction(function()use($data,$make,$old_result) {
                    $id = $old_result['id'];
                    //回滚的时候不记录日志表
                    if ($make) {
                        //添加修改日志
                        $result_log = Plat360SyncLog::addLog(array('oldurl' => $old_result['downurl'], 'newurl' => $data['downurl'], 'plat' => 1, 'wid' => $data['id']));
                    }else{
                        //修改状态
                        $destroy = !isset($data['destroy']) ? 1 : $data['destroy'];
                        $result_log = Plat360SyncLog::save(array('destroy'=>$destroy,'id'=>$data['logid']));
                    }
                    return GamePlat::setDownload($id, array('downurl'=>$data['downurl']));
                });

            }catch (\Exception $e){
                return false;
            }
        }
        return true;
    }

    /**
     * 更新 mobile 库里面游戏链接
     * @param $data
     * @param bool $make   为 false 执行回滚操作
     * @return bool
     */
    public static function  upMobileGames($data,$make = true){
        //查找old 链接
        $old_result = Addongame::getMobileGame($data['aid']);
        if($old_result && $old_result['apkurl'] != $data['apkurl']){
            try{
                DB::transaction(function()use($data,$make,$old_result){
                    //回滚的时候不记录日志表
                    if($make){
                        //添加修改日志
                        $result_log = Plat360SyncLog::addLog(array('oldurl'=>$old_result['apkurl'],'newurl'=>$data['apkurl'],'plat'=>2,'mid'=>$data['aid']));
                    }else{
                        //修改状态
                        $destroy = isset($data['destroy']) ? $data['destroy'] : 1;
                        $result_log = Plat360SyncLog::save(array('destroy'=>$destroy,'id'=>$data['logid']));
                    }
                    return Addongame::save(array('aid'=>$data['aid'],'apkurl'=>$data['apkurl']));
                });

            }catch (\Exception $e){
                return false;
            }

        }
        return false;
    }

    //更新数据
    private  static function upGames($datas){
        $t = 0;
        if(!empty($datas)){
            foreach($datas as $data){
                $data['isSync'] = 1;
                $t = Plat360Game::upGame($data);
            }
        }
        return $t;
    }
    //回滚
    public static function goBack($make,$starttime = 0 , $endtime = 0){
        set_time_limit (0);
        $whereAddtime = array();
        if($starttime != 0 && $endtime != 0 && $starttime < $endtime ){
            $whereAddtime = array($starttime,$endtime);
        }
        $result = Plat360SyncLog::getlist($make,$whereAddtime);

        if(is_array($result)){
            foreach($result as $v){
                switch($make){
                    case 'mid':
                        $data = array('aid'=>$v['mid'],'apkurl'=>$v['oldurl'],'logid'=>$v['id']);
                        self::upMobileGames($data,false);
                        break;
                    case 'wid':
                        $data = array('id'=>$v['wid'],'downurl'=>$v['oldurl'],'logid'=>$v['id']);
                        self::upWwwGames($data,false);
                        break;
                    default:
                        return false;
                }
            }
        }

    }



    //循环执行同步
    public static function startSync(){
        set_time_limit (0);
        $result = Plat360Sync::getList();
        if(is_array($result)){
            foreach($result as $v){
                if(!$v['qid']) continue;
                //访问接口进行替换
                $basicGame = self::getSingleGame(array('appid'=>$v['qid']));

                if(!isset($basicGame['items'][0])) continue;
                $basicGame = $basicGame['items'][0];
                if($v['wid']){
                    $data['id'] = $v['wid'];
                    $data['downurl'] = $basicGame['rDownloadUrl'];
                    $result_www = self::upWwwGames($data);
                }
                if($v['mid']){
                    $data2['aid'] = $v['mid'];
                    $data2['apkurl'] = $basicGame['rDownloadUrl'];
                    $result_mobile = self::upMobileGames($data2);
                }
            }
        }
        return false;
    }

    /**
     * www 与 360 游戏ID 对应关系映射
     * @return bool
     */
    public static function associateGame(){
        //查www
        set_time_limit (0);
        //查询同步表所有信息
        $wid = Plat360Sync::getWid();
        $result = GamesApk::getGameInfos(array('id','igid','gname','shortgname'),$wid);
        if(!empty($result)){
            foreach($result as $v){
                //判断是否已同步过
                $sync_data = array();
                $time = time();
                $is_sync = Plat360Sync::getState(array('wid'=>$v['id']));
                if(empty($is_sync)){
                    //查找360平台里面是否存在此游戏并且该游戏是否同步
                    $besicGame = Plat360Game::getNameGame($v['gname']);
                   // print_r($besicGame);exit;
                    //获取www 游戏 360平台下载地址
                    $result_down = GamePlat::getDownload($v['id']);

                    if($besicGame && $result_down && $result_down['pid'] == 1){
                        $sync_data['wid'] = $v['id'];
                        $sync_data['gid'] = $v['igid'];
                        $sync_data['gname'] = $v['gname'];
                        $sync_data['qid'] = $besicGame[0]['id'];
                        $sync_data['addtime'] = $time;
                        //查找mobile 里面对应的游戏
                        $mobile_game = GameInfo::getMobileNameGame($v['gname'], $v['igid'], $v['id']);
                        $sync_data['mid'] = empty($mobile_game) ? 0 : $mobile_game['id'];
                        //修改同步日志表
                        $result_sync = Plat360Sync::save($sync_data);
                    }
                }
            }
        }
        return true;
    }

    /**
     * mobile 与 360 游戏对应关系映射
     *
     * 通过mobile库关联游戏
     *
     * 映射表中存在 就并且 mid 不存在 尝试 去映射
     *
     * 1.查映射表  得到 www里面的 apk id 和游戏名称
     * 2.查mobile 里面游戏存不存在  没有查到 获取 www 里面ios 游戏Id 再次 尝试查找
     * 3.查到执行写入操作
     */
    public static function associateGame2(){
        set_time_limit (0);
        //需要映射的游戏
        $result = Plat360Sync::getMid();
        if($result){
            foreach($result as $v){
                $data['id'] = $v['id'];
                $arr = Archives::getGamesPassYxdid('apk_'.$v['wid'],$v['gname']);
                //$arr = explode('_',$v['yxdid']);
                if(!$arr) {
                    if($v['gid']){
                        $gid = $v['gid'];
                    }else{
                        //没有查到
                        $re = AndroidGame::getInfoById($v['wid']);
                        $gid = isset($re['igid']) ? $v['gid'] : 0;
                    }

                    if($gid){
                        $arr2 = Archives::getGamesPassYxdid('g_'.$gid,$v['gname']);
                        if($arr2){
                            $data['mid'] = $arr2['id'];
                        }else{
                            continue;
                        }
                    }else{
                        $result_ios = IosGame::getInfoPassName($v['gname']);
                        if($result_ios && count($result_ios) == 1){
                            $arr2 = Archives::getGamesPassYxdid('g_'.$result_ios[0]['id'],$v['gname']);
                            if($arr2){
                                $data['mid'] = $arr2['id'];
                            }else{
                                continue;
                            }
                        }else{
                            continue;
                        }
                    }
                }else{
                    //查到
                    $data['mid'] = $arr['id'];
                }
                //检测mid 是否存在 存在时不执行修改
                $result_sync = Plat360Sync::getDetail(array('mid'=>$data['mid']));
                if(!$result_sync){
                    //print_r($data);exit;
                    //修改同步日志表
                    $result_sync = Plat360Sync::save($data);
                }else{
                    continue;
                }
            }
        }
        return true;

    }

    /**
     * 列表请求链接 记录
     * @param $data
     * @return bool
     */
    private static function writeLog($data){
        if(!$data) return false;
        $begin = array('page','addtime','url','strattime','endtime','pagesize');
        foreach($begin as $v){
            if(isset($data[$v])){
                $data2[$v] = $data[$v];
            }
        }
        if(!isset($data2['addtime'])) $data2['addtime'] = time();
        return Plat360Log::addLog($data2);
    }

}