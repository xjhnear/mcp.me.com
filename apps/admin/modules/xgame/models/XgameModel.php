<?php
namespace modules\xgame\models;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;

class XgameModel extends BaseModel
{
	/**
	 * 保存游戏信息
	 */
	public static function save($data)
	{
	    if(!empty($data['id'])){			
			return self::dbCmsMaster()->table('xyx_game')->where('id','=',$data['id'])->update($data);
		}else{
			$data['senddate'] = time();
			return self::dbCmsMaster()->table('xyx_game')->insertGetId($data);
		}
	}

	/**
	 * 添加图片
	 */
	public static function addXgamePic($data) {
		
		$id = self::dbCmsMaster()->table('xyx_pic')->insertGetId($data);
		return $id;
	}
	
	/**
	 * 添加banner图片
	 */
	public static function addXgameInfopic($data) {
		$id = self::dbCmsMaster()->table('xyx_game_infopic')->insert($data);
		return $id;
	}
	
	/**
	 * 删除banner图片
	 */
	public static function delXgameinfopic($ids) {
		return self::dbCmsMaster()->table('xyx_game_infopic')->whereIn('id' , $ids)->delete();
	}
	
	/**
	 * 保存banner信息
	 */
	public static function saveBanner($data)
	{
		if(!empty($data['id'])){
			return self::dbCmsMaster()->table('xyx_game_infopic')->where('id','=',$data['id'])->update($data);
		}else{
			return self::dbCmsMaster()->table('xyx_game_infopic')->insert($data);
		}
	}
	
	/**
	 * 删除图片
	 */
	public static function delXgamePic($path) {
		
		return self::dbCmsMaster()->table('xyx_pic')->where('url', '=', $path)->delete();
	}
	
	/**
	 * 删除游戏
	 */
	public static function delXgame($gid) {
		return self::dbCmsMaster()->table('xyx_game')->where('id', '=', $gid)->delete();
	}
	
}