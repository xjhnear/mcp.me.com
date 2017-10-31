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
 * 地区模型类
 */
final class Area extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getProvinceToKeyValue()
	{
		$all = self::getAllArea();
		return $all['province'];
	}
	
    public static function getCityToKeyValue($province_id)
	{
		$all = self::getAllArea();
		return isset($all['city'][$province_id]) ? $all['city'][$province_id] : null;
	}
	
    public static function getRegionToKeyValue($city_id)
	{
		$all = self::getAllArea();
		return isset($all['region'][$city_id]) ? $all['region'][$city_id] : null;
	}
	
    public static function getAllArea()
	{
		$res = self::db()->orderBy('pid','asc')->get();
		$out = array();
		$province_list = array();
		$city_list = array();
		$region_list = array();	
		$data = array();	
		foreach($res as $row){
			$data[$row['pid']][] = array('id'=>$row['area_id'],'name'=>$row['title']);
		}
		$province_list = $data[0];
        
		foreach($province_list as $pid=>$row){
			$city_list[$row['id']] = $data[$row['id']];
		}

		foreach($city_list as $pid=>$row){
			$region_list[$row[0]['id']] = $data[$row[0]['id']];
		}
		$out = array('province'=>array_values($province_list),'city'=>$city_list,'region'=>$region_list);
		return $out;
	}
}