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
final class GamePlat extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getListByGameIds($gids)
	{
		if(!$gids) return array();
		$result = self::db()->whereIn('agid',$gids)->orderBy('istop','desc')->orderBy('sort','desc')->orderBy('id','desc')->get();
		$out = array();
		foreach($result as $row){
			$out[$row['agid']][] = $row;			
		}		
		return $out;
	}

    //检测游戏是否有下载平台
    public static function _checkHaveApkDownPlat($agid)
    {
        if(!$agid) {
            return false;
        }
        $plat_count = 0;
        $plat_count = self::db()->where('agid','=',$agid)->where('downurl','!=','')->count();
        return ($plat_count > 0) ? true : false;
    }

    /**
     * 	获取游戏的一个下载地址
     */
    public static function _getOneGameDownurl($gid)
    {
        $res = self::db()->where('agid','=',$gid)->where('downurl','!=','')->orderBy('istop','desc')->orderBy('sort','desc')->orderBy('id','desc')->first();
        return $res ? trim($res['downurl']) : '';
    }

    /**
     * 获取游戏下载平台
     */
    public static function getPlatListByGameId($agid)
    {
        $fields = array('id','pid','downurl','istop','psize','pversion');
        $result = self::db()
            ->where('agid','=',$agid)->where('pid','>',0)
            ->select($fields)
            ->orderBy('istop','desc')->orderBy('sort','desc')
            ->get();
        return $result;
    }

    public static function downloadCount($gid,$pid,$num)
    {
        $query = self::db()->where('agid','=',$gid)->where('pid','=',$pid);
        $query->increment('pdowntimes',$num);
        $query->increment('prealdown');
    }

    /**
     * 获取指定游戏平台的下载地址
     * @param $agid
     * @param array $fields
     * @return mixed
     */
    public static function getDownload($agid,$fields = array('id','pid','downurl','istop','psize','pversion')){
        $result = self::db()
            ->where('agid',$agid)
            ->select($fields)
            ->orderby('istop','desc')->orderby('sort','desc')->orderby('id','desc')
            ->first();
        return $result;
    }

    /**
     * 更新360平台的
     * @param $id
     * @param array $data array('downurl'=>'http://m.youxiduo.com')
     * @return bool
     */
    public static function setDownload($id,array $data){
        if(!$id || empty($data)) return false;
        return self::db()->where('id',$id)->where('pid',1)->update($data);
    }



}