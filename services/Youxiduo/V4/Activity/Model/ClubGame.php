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
namespace Youxiduo\V4\Activity\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 公会游戏模型类
 */
final class ClubGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getInfo($id)
	{
		return self::db()->where('id','=',$id)->first();
	}
	
	public static function search($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['keyword']) && $search['keyword']){
			$tb = $tb->where('name','like','%'.$search['keyword'].'%');
		}
		
		if(isset($search['club_id']) && $search['club_id']){
			$tb = $tb->where('club_id','=',$search['club_id']);
		}
		
		return $tb;
	}
	
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			$data['update_time'] = date('Y-m-d H:i:s');
			return self::db()->where('id','=',$id)->update($data);
		}else{
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['update_time'] = date('Y-m-d H:i:s');
			return self::db()->insertGetId($data);
		}
	}
	
	public static function delete($id)
	{
		return self::db()->where('id','=',$id)->delete();
	}
}