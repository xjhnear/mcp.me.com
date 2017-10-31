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
namespace Youxiduo\V4\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 手机黑名单模型类
 */
final class MobileBlackList extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function addMobile($mobile)
	{
		$exists = self::checkMobileExists($mobile);
		if($exists) return true;
		return self::db()->insert(array('mobile'=>$mobile,'ctime'=>time()));
	}
	
	public static function deleteMobile($mobile)
	{
		return self::db()->where('mobile','=',$mobile)->delete();
	}
	
	public static function checkMobileExists($mobile)
	{
		$exists = self::db()->where('mobile','=',$mobile)->first();
		return $exists ? true : false;
	}
	
	public static function searchMobile($mobile,$pageIndex=1,$pageSize=10)
	{
		$search['mobile'] = $mobile;
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->orderBy('ctime','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['mobile']) && $search['mobile']){
			$tb = $tb->where('mobile','like','%'.$search['mobile'].'%');
		}
		return $tb;
	}
}