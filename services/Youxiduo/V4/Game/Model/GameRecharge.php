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
namespace Youxiduo\V4\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏模型类
 */
final class GameRecharge extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function searchCount($search)
    {
        return self::buildSearch($search)->count();
    }

    public static function searchList($search,$pageIndex=1,$pageSize=10)
    {
        return self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('update_time','desc')->get();
    }

    protected static function buildSearch($search)
    {
        $tb = self::db();
        if(isset($search['gname'])&&!empty($search['gname'])){
            $tb =  $tb->where('gname','=',$search['gname']);
        }
        return $tb;
    }

    public static function getInfo($game_id)
    {
        return self::db()->where('gid','=',$game_id)->first();
    }

    public static function SaveInfo($game_id,$game_name,$url,$linkType,$isAutoLogin)
    {
        $exists = self::db()->where('gid','=',$game_id)->first();
        if($exists){
            return self::db()->where('gid','=',$game_id)->update(array('url'=>$url,'linkType'=>$linkType,'isAutoLogin'=>$isAutoLogin,'update_time'=>time()));
        }else{
            return self::db()->insert(array('gid'=>$game_id,'gname'=>$game_name,'url'=>$url,'linkType'=>$linkType,'isAutoLogin'=>$isAutoLogin,'create_time'=>time(),'update_time'=>time()));
        }
    }

    public static function DeleteInfo($game_id)
    {
        return self::db()->where('gid','=',$game_id)->delete();
    }
}