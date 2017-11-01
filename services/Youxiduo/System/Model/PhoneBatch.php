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
namespace Youxiduo\System\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelp;
/**
 * 应用配置模型类
 */
final class PhoneBatch extends Model implements IModel
{		
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getVersionInfo($appname,$version,$channel)
	{
		$config = self::db()->where('appname','=',$appname)->where('version','=',$version)->where('channel','=',$channel)->first();
		if(!$config) return array();		
		return $config;
	}
	
    public static function m_search($search)
	{
		$tb = self::m_buildSearch($search);
		return $tb->orderBy('id','asc')->get();
	}
	
	protected static function m_buildSearch($search)
	{
		$tb = self::db()->where('isshow','=',1);
		if(isset($search['platform'])){
			$tb = $tb->where('platform','=',$search['platform']);
		}
	    if(isset($search['appname'])){
			$tb = $tb->where('appname','=',$search['appname']);
		}
	    if(isset($search['channel'])){
			$tb = $tb->where('channel','=',$search['channel']);
		}
	    if(isset($search['version'])){
			$tb = $tb->where('version','=',$search['version']);
		}
		return $tb;
	}
	
	public static function m_getVersionInfo($id)
	{
		$info = self::db()->where('id','=',$id)->first();
		if($info){
			$append = json_decode($info['append'],true);
			is_array($append) && $info = array_merge($info,$append);
			$info['syspicVendorsList'] = explode(',', $info['sys_img']);
			foreach ($info['syspicVendorsList'] as &$item) {
			    $item = MyHelp::getImageUrl($item);
			}
			if (isset($info['syspicVendorsList'])) {
			    $info['syspiccount'] = implode(',',array_keys($info['syspicVendorsList']));
			} else {
			    $info['syspiccount'] = '';
			}
		}
		return $info;
	}
	
	public static function m_saveVersionInfo($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return self::db()->where('id','=',$id)->update($data);
		}else{
			$data['addtime'] = time();
			return self::db()->insertGetId($data);
		}
	}
}