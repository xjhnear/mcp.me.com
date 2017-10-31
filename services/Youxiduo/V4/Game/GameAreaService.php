<?php
/**
 * @package Youxiduo
 * @category Base
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\Game;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Game\Model\GameArea;
use Youxiduo\V4\Game\Model\UserGameArea;
use Youxiduo\V4\Game\Model\GameChannel;

class GameAreaService extends BaseService
{
    public static function searchCount($search)
    {
        return self::buildSearch($search)->count();
    }

    public static function searchList($search,$pageIndex=1,$pageSize=10)
    {
        return self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
    }

    public static function buildSearch($search)
    {
        $tb = GameArea::db();
        if(isset($search['nickname']) && !empty($search['nickname'])){
            $tb = $tb->where('nickname','like','%'.$search['nickname'].'%');
        }
        if(isset($search['gname']) && !empty($search['gname'])){
            $tb = $tb->where('gname','like','%'.$search['gname'].'%');
        }

        if(isset($search['dealType'])){
            $tb = $tb->where('dealType','=',$search['dealType']);
        }

        if(isset($search['is_open'])){
            $tb = $tb->where('is_open','=',$search['is_open']);
        }

        if(isset($search['game_id'])){
            $tb = $tb->where('game_id','=',$search['game_id']);
        }

        return $tb;
    }

    public static function updateInfo($id,$data)
    {
        return GameArea::db()->where('id','=',$id)->update($data);
    }

    public static function addInfo($data)
    {
        return GameArea::db()->insertGetId($data);
    }

    public static function addBatchInfo($data)
    {
        return GameArea::db()->insert($data);
    }

    public static function getInfo($id)
    {
        return GameArea::db()->where('id','=',$id)->first();
    }

    public static function isExists($typename,$area_name,$server_name)
    {
        $exists = GameArea::db()->where('','=',$typename)->where('','=',$area_name)->where('','=',$server_name)->first();
        return $exists ? true : false;
    }

    public static function isFreeze($uid)
    {
        $exists = GameArea::db()->where('uid','=',$uid)->where('dealType','=',0)->first();
        return $exists ? true : false;
    }

    public static function addGameChannel($channel_name)
    {
        return GameChannel::db()->insertGetId(array('channelname'=>$channel_name));
    }

    public static function updateGameChannel($channel_id,$channel_name)
    {
        return GameChannel::db()->where('channelid','=',$channel_id)->update(array('channelname'=>$channel_name));
    }

    public static function deleteGameChannel($channel_id)
    {
        return GameChannel::db()->where('channelid','=',$channel_id)->delete();
    }

    public static function getGameChannelInfo($channel_id)
    {
        return GameChannel::db()->where('channelid','=',$channel_id)->first();
    }

    public static function getGameChannelList($format=false)
    {
        if($format){
            return GameChannel::db()->lists('channelname','channelid');
        }
        return GameChannel::db()->get();
    }

    public static function deleteGameArea($id)
    {
        return GameArea::db()->where('id','=',$id)->delete();
    }

    public static function clearGameAreaServer($game_id)
    {
        return GameArea::db()->where('game_id','=',$game_id)->delete();
    }
}