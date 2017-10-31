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
final class AppAdvStat extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}

    public static function appAdvStatCount($appname,$version,$datetime,$location,$advid,$type,$linkid)
    {
        $tb = self::db();
        $tb->where('appname','',$appname);
        $tb->where('version','',$version);
        $tb->where('addtime','',$datetime);
        $tb->where('location','',$location);

        if($advid){
            $tb->where('aid','=',$advid);
        } else {
            $tb->where('type','=',$type);
            $tb->where('link_id','=',$linkid);
        }

        $res = $tb->pluck('id');
        return $res;
    }

    public static function setIncById($id)
    {
        return self::db()->where('id','=',$id)->increment('number');
    }

    public static function save(array $data)
    {
        if(!$data) return 0;
        return self::db()->insertGetId($data);
    }
}