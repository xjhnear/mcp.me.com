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
use Illuminate\Support\Facades\Config;
/**
 * 广告模型类
 */
final class AppAdv extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($appname,$version,$type,$limit)
	{
		$result = self::db()
		->where('appname','=',$appname)
		->where('version','=',$version)
		->where('type','=',$type)
		->orderBy('location','asc')
		->forPage(1,$limit)
		->get();
		
		return $result;
	}
	
	public static function getInfo($appname,$version,$type)
	{
		$adv = self::db()->where('appname','=',$appname)
		->where('version','=',$version)
		->where('type','=',$type)
		->first();
		return $adv;
	}

    public static function getDetailByGid($appname,$version,$gid,$type){
        $fields = array("title", "litpic", "downurl", "url", "location", "aid", "sendmac", "sendidfa", "sendudid", "sendos", "sendplat", "sendactive", "tosafari");
        return self::db()->where("appname","=",$appname)->where("version","=",$version)->where("type","=",$type)->where("gid",$gid)->select($fields)->first();
    }

    public static function getDetailByAid($appname,$version,$advid,$location)
    {
        $adv = self::db()->where('appname','=',$appname)
            ->where('version','=',$version)
            ->where('aid','=',$advid)
            ->where('location','=',$location)
            ->first();
        return $adv;
    }

    /**
     * 更新
     * @param $appadv_id
     * @param $data
     * @return mixed
     */
    public static function update($appadv_id,$data)
    {
        $query = self::db()->where('id','=',$appadv_id)->update($data);
        return $query;
    }
}