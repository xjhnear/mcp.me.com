<?php
namespace modules\game\models;
use Yxd\Modules\Core\BaseModel;

class GameModel extends BaseModel
{
    public static function search($search,$pageIndex=1,$pageSize=15,$sort=array())
	{
		$out = array();
		$out['total'] = self::buildSearch($search)->count();
		$tb = self::buildSearch($search)->forPage($pageIndex,$pageSize);

		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		if(!$sort){
			$tb = $tb->orderBy('id','desc');
		}
		$out['result'] = $tb->get();
		return $out;		
	}
	
    public static function buildSearch($search)
	{
		$tb = self::dbCmsSlave()->table('games');
		$tb = $tb->where('isdel','=',0);
		if(isset($search['type'])){
			$tb = $tb->where('type','=',$search['type']);
		}
	    if(isset($search['zonetype'])){
			$tb = $tb->where('zonetype','=',$search['zonetype']);
		}
		if(isset($search['id']) && !empty($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		
	    if(isset($search['gname']) && !empty($search['gname'])){
			$tb = $tb->where('gname','like','%'.$search['gname'].'%');
		}
		
		return $tb;
	}
	
	public static function getForumGids()
	{
		return self::dbClubSlave()->table('forum')->lists('gid');
	}
	
	public static function getInfo($id,$itunesid='')
	{
		if(!$id && !$itunesid) return array();
		$query = self::dbCmsSlave()->table('games');
		if($id) $query->where('id',$id);
		if($itunesid) $query->where('itunesid',$itunesid);
		$result = $query->first();
		if($result){
			$result['price'] = number_format($result['price'],2);
			$result['oldprice'] = number_format($result['oldprice'],2);
		}
		return $result;
	}
	
	/**
	 * 通过游戏名称查询游戏
	 * @param string $gname
	 * @return array
	 */
	public static function getnameInfo($gname)
	{
		if(!$gname) return array();
		$query = self::dbCmsSlave()->table('games');
		if($gname) $query->where('gname',$gname);
		$result = $query->first();
		if($result){
			$result['price'] = number_format($result['price'],2);
			$result['oldprice'] = number_format($result['oldprice'],2);
		}
		return $result;
	}
	/**
	 * 查询游戏名和ID
	 * @param array $data
	 */
	public static function getGameList($data){
		return self::dbCmsSlave()->table('games')->select('id', 'gname')->whereIn('id',$data)->get();
	}
	
	public static function setGameControl($data){
		if(self::getGameControl($data['id'])){
			self::dbClubMaster()->table('game_control')->where('id',$data['id'])->update($data);
			return true;
		}else{
			return self::dbClubMaster()->table('game_control')->insert($data);
		}
	}
	
	public static function getGameControlList($game_id){
		return self::dbClubMaster()->table('game_control')->where('game_id',$game_id)->get();
	}
	
	public static function getGameControl($id){
		return self::dbClubMaster()->table('game_control')->where('id',$id)->first();
	}
	
	public static function getAllGameType(){
		return self::dbCmsMaster()->table('game_type')->lists('typename');
	}
	
	public static function getTaglist($typeid=null){
		$query = self::dbCmsMaster()->table('tag');
		if($typeid) $query->where('typeid',$typeid);
		return $query->lists('tag');
	}
	
	/**
	 * 获取经典必玩游戏
	 * @param int $gid app游戏id
	 */
	public static function getGamemustplay($gid){
		return self::dbCmsMaster()->table('game_mustplay')->where('gid',$gid)->first();
	}
	
	/**
	 * 添加经典必玩游戏
	 * @param array $data
	 */
	public static function addGamemustplay($data){
		return self::dbCmsMaster()->table('game_mustplay')->insert($data);
	}
	
	/**
	 * 删除经典必玩游戏
	 * @param int $gid
	 * @return boolean
	 */
	public static function delGamemustplay($gid){
		if(!$gid) return false;
		return self::dbCmsMaster()->table('game_mustplay')->where('gid',$gid)->delete();
	}
	
	/**
	 * 获取精品热门推荐游戏
	 * @param int $gid
	 */
	public static function getGamerecommend($gid,$type){
		if(!$gid && !$type) return array();
		$query = self::dbCmsMaster()->table('game_recommend');
		if($gid) $query->where('gid',$gid);
		if($type) $query->where('type',$type);
		return $query->first();
	}
	
	/**
	 * 添加精品热门推荐游戏
	 * @param array $data
	 */
	public static function addGamerecommend($data){
		return self::dbCmsMaster()->table('game_recommend')->insert($data);
	}
	
	/**
	 * 更新精品热门推荐游戏(根据gid、type)
	 * @param int $gid
	 * @param int $agid
	 * @param string $type
	 * @param array $data
	 */
	public static function updateGamerecommend($gid,$type,$data){
		if(!$gid) return false;
		$query = self::dbCmsMaster()->table('game_recommend');
		if($gid) $query->where('gid',$gid);
		if($type) $query->where('type',$type);
		return $query->where('agid','>',0)->update($data);
	}
	
	/**
	 * 删除精品热门推荐游戏(仅根据gid、type)
	 * @param int $gid
	 * @param string $type
	 */
	public static function delGamerecommend($gid,$type){
		if(!$gid) return false;
		$query = self::dbCmsMaster()->table('game_recommend');
		if($gid) $query->where('gid',$gid);
		if($type) $query->where('type',$type);
		return self::dbCmsMaster()->table('game_recommend')->where('agid',0)->delete();
	}
	
	/**
	 * 获取游戏标签
	 * @param int $gid
	 */
	public static function getGametags($gid){
		return self::dbCmsMaster()->table('games_tag')->where('gid',$gid)->lists('tag');
	}
	
	/**
	 * 添加游戏标签
	 * @param array $data
	 */
	public static function addGametags($data){
		return self::dbCmsMaster()->table('games_tag')->insert($data);
	}
	
	/**
	 * 删除游戏标签
	 * @param int $gid
	 * @param int $agid
	 */
	public static function delGametags($gid=0,$agid=0){
		return self::dbCmsMaster()->table('games_tag')->where('gid',$gid)->where('agid',$agid)->delete();
	}
	
	/**
	 * 更新游戏标签
	 * @param int $gid
	 * @param int $agid
	 * @param array $data
	 * @return boolean
	 */
	public static function updateGametags($gid=0,$agid=0,$data){
		if($gid>0){
			return self::dbCmsMaster()->table('games_tag')->where('gid',$gid)->where('agid','>',0)->update($data);
		}
		if($agid>0){
			return self::dbCmsMaster()->table('games_tag')->where('agid',$agid)->where('gid','>',0)->update($data);
		}
		return false;
	}
	
	/**
	 * 添加游戏信息
	 * @param unknown $data
	 */
	public static function addGameInfo($data){
		if(!$data) return false;
		return self::dbCmsMaster()->table('games')->insertGetId($data);
	}
	
	/**
	 * 更新游戏信息
	 * @param int $gid
	 * @param array $data
	 */
	public static function updateGameInfo($gid,$data){
		if(!$gid) return false;
		return self::dbCmsMaster()->table('games')->where('id',$gid)->update($data);
	}
	
	/**
	 * 获取游戏的图片
	 * @param int $id
	 * @param int $gid
	 * @return multitype:
	 */
	public static function getGamelitpic($id=0,$gid=0,$path=''){
		if(!$id && !$gid && !$path) return array();
		$query = self::dbCmsMaster()->table('games_litpic');
		if($id) $query->where('id',$id);
		if($gid) $query->where('gid',$gid);
		if($path) $query->where('litpic',$path);
		return $query->get();
	}
	
	/**
	 * 添加游戏图片
	 * @param array $data
	 */
	public static function addGamelitpic($data){
		return self::dbCmsMaster()->table('games_litpic')->insert($data);
	}
	
	/**
	 * 删除游戏的图片
	 * @param string $litpic
	 * @return boolean
	 */
	public static function delGamelitpic($litpic){
		if(!$litpic) return false;
		return self::dbCmsMaster()->table('games_litpic')->where('litpic',$litpic)->delete();
	}
	
	/**
	 * 获取H5游戏详情图
	 * @param int $gid
	 * @return multitype:
	 */
	public static function getGameinfopic($gid){
		if(!$gid) return array();
		return self::dbCmsMaster()->table('games_infopic')->where('gid',$gid)->get();
	}
	
	/**
	 * 添加H5游戏详情图
	 * @param unknown $data
	 */
	public static function addGameinfopic($data){
		return self::dbCmsMaster()->table('games_infopic')->insert($data);
	}
	
	/**
	 * 删除H5游戏详情图
	 * @param string $litpic
	 * @return boolean
	 */
	public static function delGameinfopic($litpic){
		if(!$litpic) return false;
		return self::dbCmsMaster()->table('games_infopic')->where('litpic',$litpic)->delete();
	}
}