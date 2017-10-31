<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/26
 * Time: 15:16
 */

namespace Youxiduo\Activity\Duang;


use Youxiduo\Activity\Model\CashGame;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Game\Model\GamesApk;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\Game\Model\GameTag;

class CashGameService{

    //获取列表
    public static function getList($search,$pageIndex=1,$pageSize=10,$sort=array(),$device='ios'){
        $ids = $out['result'] = array();
        $out['totalCount'] = 0;
        if(isset($search['gid']) && $search['gid']){
            //带游戏名称查询先查游戏表 再查
            $games = GameService::getMultiInfoById($search['gid'],$device);
            if($games) {
                $c_device = $device == 'ios' ? 1 : 2;
                $gids = $newgames = array();
                foreach($games as $g){
                    $newgames[$g['gid']] = $g;
                    $gids[] = $g['gid'];
                }
                $cg = CashGame::getList(array('gid'=>$gids,'device'=>$c_device),1,10);
                if($cg['result']){
                    foreach($cg['result'] as $row){
                        if(!array_key_exists($row['gid'],$newgames)) continue;
                        $newgames[$row['gid']]['ico'] = Utility::getImageUrl($newgames[$row['gid']]['ico']);
                        $newgames[$row['gid']]['discount'] = intval($row['discount']) === $row['discount'] ? $row['discount'] : intval($row['discount']);
                        $out['result'][$row['gid']] = $newgames[$row['gid']];
                        $out['totalCount']++;
                    }
                }
            }
        }else{
            $cashgame = CashGame::getList($search,$pageIndex,$pageSize,$sort);
            if($cashgame['result']){
                foreach($cashgame['result'] as $v){
                    $v['discount'] = (double)$v['discount'];
                    $out['result'][$v['gid']] = $v;
                    $ids[] = $v['gid'];
                }
                $gids = array();
                if($device == 'ios'){
                    $game = GameService::getMultiInfoById($ids,$device,'full');
                    foreach($game as $v){
                        $v['ico'] = Utility::getImageUrl($v['ico']);
                        $out['result'][$v['gid']] += $v;
                    }
                }else{
                    $game = GamesApk::getGamePassIDs($ids);
                    foreach($game as $v){
                        $v['ico'] = Utility::getImageUrl($v['ico']);
                        $out['result'][$v['id']] += $v;
                        $gids[] = $v['id'];
                        //游戏下载地址
                        $urlArr = GamePlat::getDownload($v['id']);
                        if($urlArr) $out['result'][$v['id']] += $urlArr;
                    }
                }

                //获取游戏tag
                $tagArr = GameTag::getGameTagsByGameIds($device,$gids);
                foreach($tagArr as $k=>$v){
                    if(empty($out['result'][$k]['phrase'])){
                        $out['result'][$k]['phrase'] = implode($v);
                    }
                }
                $out['totalCount'] = $cashgame['totalCount'];
                return $out;
            }

        }
        return $out;
    }

    public static function getDetail($id){
        if(!$id) return array();
        $cashgame = CashGame::getDetail($id);
        if($cashgame){
            $platform = $cashgame['device'] == 1 ? 'ios' : 'android';
            $game = GameService::getOneInfoById($cashgame['gid'],$platform);
            $cashgame['gname'] = $game ? $game['gname'] : '';
            $cashgame['ico'] = $game ? Utility::getImageUrl($game['ico']) : '';
        }
        return $cashgame;
    }
}