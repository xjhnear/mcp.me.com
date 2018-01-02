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
namespace Youxiduo\Phone\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelp;
/**
 * 应用配置模型类
 */
final class Category extends Model implements IModel
{		
    public static function getClassName()
	{
		return __CLASS__;
	}

	public static function getList($search,$pageIndex=1,$pageSize=20)
	{
		$tb = self::db();
		return $tb->orderBy('created_at','desc')->forPage($pageIndex,$pageSize)->get();
	}

	public static function getListAllName()
	{
		$tb = self::db();
		$category = $tb->orderBy('created_at','desc')->get();
		$out = array();
		foreach ($category as $item) {
			$out[$item['category_id']] = $item['name'];
		}
		return $out;
	}

	public static function getCount($search)
	{
		$tb = self::db();
		return $tb->count();
	}

	public static function getInfo($category_id)
	{
		$batch = self::db()->where('category_id','=',$category_id)->first();
		if(!$batch) return array();
		return $batch;
	}

	public static function getInfoByName($name)
	{
		$batch = self::db()->where('name','=',$name)->first();
		if(!$batch) return array();
		return $batch;
	}

    public static function m_search($search)
	{
		$tb = self::m_buildSearch($search);
		return $tb->orderBy('category_id','desc')->get();
	}

	public static function save($data)
	{
		if(isset($data['category_id']) && $data['category_id']){
			$category_id = $data['category_id'];
			unset($data['category_id']);
			$data['updated_at'] = time();
			return self::db()->where('category_id','=',$category_id)->update($data);
		}else{
			unset($data['category_id']);
			$data['created_at'] = time();
			$data['updated_at'] = time();
			return self::db()->insertGetId($data);
		}
	}

	public static function del($category_id)
	{
		if($category_id > 0){
			$re = self::db()->where('category_id','=',$category_id)->delete();
		}
		return $re;
	}
}