<?php
/**
 * @package Youxiduo
 * @category Cms 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Archives extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	public static function getLists($id){
		$result =  self::db()->where('id','=',$id)->first();
		return $result;
	}
	/**
	 * 获取mobile后台中游戏ID
	 * @param number $ios
	 * @param number $android
	 * @return boolean
	 */
	public static function getMobileGame($ios = 0 , $android = 0){
		if(!$ios && !$android) return array();
		if($ios != 0){
			return self::db()->where('yxdid','g_'.$ios)->where('channel',3)->first();
		}
		if($android != 0){
			return self::db()->where('yxdid','apk_'.$android)->where('channel',3)->first();
		}
		return false;
	}
    /**
     * 通过游戏名称 和 www库里面游戏ID来获取游戏
     * @param $gameName
     * @param int $ios
     * @param int $android
     * @return array|bool
     */
    public static function getMobileNameGame($gameName , $ios = 0 , $android = 0){
        if(!$ios && !$android) return array();
        $tb = self::db()->where('title','like',"%$gameName%")->where('channel',3);
        if($ios != 0 && $android != 0){
            $tb = $tb->Where(function($query)use($ios,$android)
            {
                $query->where('yxdid', 'g_'.$ios);
                $query->orWhere('yxdid', 'apk_'.$android);
            });
        }elseif($ios != 0){
            $tb = $tb->where('yxdid','g_'.$ios);
        }elseif($android != 0){
            $tb = $tb->where('yxdid','apk_'.$android);
        }
        $result = $tb->first();
        return $result;
    }
	
	/**
	 * 获取mobile后台中游戏ID
	 * @param array $ios
	 * @param array $android
	 * @return boolean
	 */
	public static function getMobileGames($ios = array() , $android = array()){
		if(!$ios && !$android) return array();
		if($ios){
			return self::db()->whereIn('yxdid',$ios)->where('channel',3)->get();
		}
		if($android){
			return self::db()->whereIn('yxdid',$android)->where('channel',3)->get();
		}
		return false;
	}

    /**
     * 获取所有游戏信息
     * @return mixed
     */
    public static function getGames($feilds = array(),$mid = array()){
        $tb = self::db();
        $select = '';
        foreach($feilds as $v){
            $select .= $v . ',';
        }
        $select = $select ? $select.'count(id) as c' : '* , count(id) as c';
        $tb->select(DB::raw($select));
        if($mid){
            $tb->whereNotIn('id',$mid);
        }
        return $tb->where('yxdid','!=','')->where('channel',3)->groupBy('title')->having('c','=',1)->orderBy('id','asc')->get();

    }

    /**
     * 通过yxdid 查找游戏
     * @param $yxdid
     * @return array
     */
    public static function getGamesPassYxdid($yxdid,$ganme){
        if(!$yxdid) return array();
        return self::db()->where('yxdid',$yxdid)->where('title',$ganme)->first();
    }


}