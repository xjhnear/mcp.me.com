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
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏专题游戏模型类
 */
final class GameTool extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		$result = self::db()->whereIn('agid',$gids)->where('toolid','!=',0)->get();
		$out = array();
		foreach($result as $row){
			if($row['toolid']){
				$toolids = explode(',',$row['toolid']);
				$out[$row['agid']] = $toolids;
			}
		}
		return $out;
	}

    public static function getGameTools($gid)
    {
    	$ids = array();
        $tool_ids = self::getCountByGameIds(array($gid));
        $tool_ids && $ids = $tool_ids[$gid];
        if(!$ids) return array();
        $games = Game::getListByIds($ids);
        $out = array();
        foreach($games as $k=>$v){
            $out[$k]['toolid'] = $v['id'];
            $out[$k]['title'] = $v['shortgname'];
            $out[$k]['series'] = false;
            $out[$k]['updatetime'] = $v['addtime'];
        }
        return $out;
    }
}