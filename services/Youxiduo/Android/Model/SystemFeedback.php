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
 * 用户反馈模型类
 */
final class SystemFeedback extends Model implements IModel
{
	public static function getClassName()
	{
		return __CLASS__;
	}

    /**
     * 提交用户反馈
     * @param array $data
     * @return
     */
    public static function save(array $data){
        return self::db()->insertGetId($data);
    }

    public static function getAllList($page=1,$limit=10){
        return self::db()->where('pid',0)->forPage($page,$limit)->orderBy('addtime','desc')->get();
    }

    public static function getAllListCount(){
        return self::db()->where('pid',0)->count();
    }

    public static function getDialogListByPid($pid,$page=1,$limit=10){
        if(!$pid) return false;
        $query = self::db();
        $query->where('id',$pid);
        $query->orWhere('pid',$pid);
        return $query->forPage($page,$limit)->orderBy('addtime')->get();
    }

    public static function getDialogListCountByPid($pid){
        if(!$pid) return 0;
        return self::db()->where('id',$pid)->orWhere('pid',$pid)->count();
    }
}