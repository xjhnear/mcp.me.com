<?php
namespace Youxiduo\Cms;
use Youxiduo\Base\BaseService;
use Youxiduo\Cms\Model\Archives;
use Youxiduo\Cms\Model\Addongame;
use Youxiduo\Cms\Model\Arctype;
/**
 * 模型类
 */
class GameInfo extends BaseService
{
	/**
	 * 获取mobile端游戏详情
	 * @param number $gid
	 * @param number $agid
	 * @return unknown|multitype:
	 */
	public static function getMobileGame($gid = 0,$agid = 0){
		$arc = Archives::getMobileGame($gid,$agid);
		if(!empty($arc)){
			$addon = Addongame::getMobileGame($arc['id']);
			$arc += $addon;
			$arc['gid'] = $gid;
			$arc['agid'] = $agid;
			return $arc;
		}else{
			return array();
		}
	}
	/**
	 * 查询多款游戏信息
	 * @param array $gids
	 * @param array $agids
	 * @return multitype:|multitype:Ambigous <boolean, multitype:>
	 */
	public static function getMobileGames($gids = array(),$agids = array()){
		$arc = array();
		if(!$gids && !$agids) return array();
		if(!empty($gids)){
			$where = array();
			foreach ($gids as $id){
				$where[$id] = 'g_'.$id;
			}
			$rs = Archives::getMobileGames($where);
			if($rs){ 
				foreach($rs as &$v){
					if(in_array($v['yxdid'], $where)){
						$v['gid'] = current(array_keys($where,$v['yxdid']));
					}
					$arc[$v['yxdid']] = $v ;
				}
			}
		}
		if(!empty($agids)){
			
			$where = array();
			foreach ($agids as $id){
				$where[$id] = 'apk_'.$id;
			}
			$rs = Archives::getMobileGames(array(),$where);
			if($rs){
				foreach($rs as &$v){
					if(in_array($v['yxdid'], $where)){
						$v['gid'] = current(array_keys($where,$v['yxdid']));
					}
					$arc[$v['yxdid']] = $v ;
				}
			}
		}
		return $arc;
	}
	
/**
	 * 获取所要添加的栏目的父栏目的ID
	 * @param array $gameId
	 * @param string $typename
	 * @return boolean|array
	 */
	public static function currFatherMune($gameId , $typename){
		$fristData = Arctype::db()->where('refarc',$gameId)->where('typename',$typename)->first();
		return $fristData;
	}
}