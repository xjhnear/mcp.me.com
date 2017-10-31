<?php
namespace Youxiduo\Cms\Model;
use Yxd\Modules\Core\BaseModel;

class VideosGames extends BaseModel
{
	const TABLE = 'videos_games';
	/**
	 * 保存文章
	 */
	public static function save($data)
	{
	    if(!empty($data['id'])){
	    	$id = $data['id'];
	    	unset($data['id']);	
			return self::dbCmsMaster()->table(self::TABLE)->where('id','=',$id)->update($data);
			// print_r(self::dbCmsMaster()->getQueryLog());exit;
		}else{
			return self::dbCmsMaster()->table(self::TABLE)->insertGetId($data);
		}
	}
	/**
	 * 查询文章
	 * @param int $id
	 */
	public static function getDetails($id){
		return self::dbCmsMaster()->table(self::TABLE) ->where('id','=',$id) ->first();
		//print_r(self::dbCmsMaster()->getQueryLog());exit;
	}
	/**
	 * 查询所有关联信息
	 * @param number $id
	 * @return multitype:
	 */
	public static function getLists($id = 0){
		if($id == 0) return array();
		return self::dbCmsMaster()->table(self::TABLE) ->where('vid','=',$id) ->get();
	}
	
	/**
	 * 删除文章
	 */
	public static function delArticle($id) {
		return self::dbCmsMaster()->table(self::TABLE)->where('vid', '=', $id)->delete();
	}
	/**
	 * 获取当前游戏下所有系列栏目
	 * @param int $gid
	 * @param string $op gid or agid
 	 */
	public static function getMenu($gid , $op='gid'){
		return self::dbCmsMaster()->table(self::TABLE)->where($op,'=',$gid)->where('pid','=','-1')->orderBy('addtime','desc')->get();
	}

}