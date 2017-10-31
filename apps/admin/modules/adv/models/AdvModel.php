<?php
namespace modules\adv\models;
use Yxd\Services\Cms\GameService;

use Yxd\Modules\Core\BaseModel;

class AdvModel extends BaseModel
{
	public static function search($search)
	{
		$tb = self::buildSearch($search);
		return $tb->orderBy('location','asc')->orderBy('id','asc')->get();
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::dbCmsSlave()->table('appadv');
		if(isset($search['type']) && $search['type']){
			$tb = $tb->where('type','=',$search['type']);
		}
		
	    if(isset($search['version']) && $search['version']){
			$tb = $tb->where('version','=',$search['version']);
		}
		
		return $tb;
	}
	
	public static function getInfo($id)
	{
		$adv = self::dbCmsSlave()->table('appadv')->where('id','=',$id)->first();
		return $adv;
	}
}