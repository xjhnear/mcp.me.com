<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/7/22
 * Time: 20:56
 */
namespace Youxiduo\Activity;
use Youxiduo\Activity\Model\ActivityGiftbag;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;


class ActivityGiftbagService{

    public static function getList($pagesize=10,$page=1,$make=false,$tag=1){
        $result = ActivityGiftbag::getList($pagesize,$page,$make,$tag);
        $gids =$agids = $temp = array();
        foreach($result['result'] as $k=>$v){
            $v['gid'] && ($v['plat'] == 'ios' ? $gids[] = $v['gid'] : $agids[] = $v['gid']);
            $v['gid'] && $temp[$k] = $v['gid'];
        }
        $igame = GameService::getMultiInfoById($gids,'ios');
        $igame == 'game_not_exists' && $igame =array();
        $agame = GameService::getMultiInfoById($agids,'android');
        $agame == 'game_not_exists' && $agame =array();
        $game = array_merge($igame,$agame);
        foreach($game as $v){
            $k = array_search($v['gid'],$temp);
            $result['result'][$k]['gname'] = $v['gname'];
            $result['result'][$k]['shortgname'] = $v['shortgname'];
            $result['result'][$k]['icon'] = Utility::getImageUrl($v['ico']);
        }
        return $result;
    }

    public static function getDetail($id){
        if(!$id) return false;
        $result = ActivityGiftbag::getDetail($id);
        if($result['gid']){
            $game = GameService::getOneInfoById($result['gid'],$result['plat']);
            if(is_array($game)) {
                $result['gname'] = $game['gname'];
                $result['shortgname'] = $game['shortgname'];
                $result['icon'] = Utility::getImageUrl($game['ico']);
            }
        }
        return $result;

    }
}