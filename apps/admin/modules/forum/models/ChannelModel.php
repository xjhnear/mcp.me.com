<?php
namespace modules\forum\models;
use Illuminate\Support\Facades\Event;
use Yxd\Modules\Core\BaseModel;

class ChannelModel extends BaseModel
{
	/**
	 * 获取版块列表
	 */
	public static function getList($gid)
	{
		if($gid==2){
			return self::dbClubSlave()->table('forum_channel')->where('gid','=',$gid)->orderBy('displayorder','asc')->get();
		}
		return self::dbClubSlave()->table('forum_channel')->where('gid','=',0)->orWhere('gid','=',$gid)->orderBy('displayorder','asc')->get();
	}
	
	/**
	 * 获取版块列表键值对
	 */
	public static function getOptions($gid)
	{
		if($gid==2){
			return self::dbClubSlave()->table('forum_channel')->where('gid','=',$gid)->orderBy('displayorder','asc')->lists('channel_name','cid');
		}
		return self::dbClubSlave()->table('forum_channel')->where('gid','=',0)->orWhere('gid','=',$gid)->orderBy('displayorder','asc')->lists('channel_name','cid');
	}
	
	/**
	 * 获取版块信息
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('forum_channel')->where('cid','=',$id)->first();
	}
	
	/**
	 * 保存版块信息
	 */
	public static function save($data)
	{
		if(isset($data['cid']) && $data['cid']>0){
			$cid = $data['cid'];
			unset($data['cid']);
			return self::dbClubSlave()->table('forum_channel')->where('cid','=',$cid)->update($data);
		}else{
			return self::dbClubSlave()->table('forum_channel')->insertGetId($data);
		}
	}
	
	public static function doDelete($gid,$cid)
	{
		$count = self::dbClubSlave()->table('forum_topic')->where('gid','=',$gid)->where('cid','=',$cid)->count();
		if($count){
			return -1;
		}
		return self::dbClubMaster()->table('forum_channel')->where('cid','=',$cid)->delete();
	}
}