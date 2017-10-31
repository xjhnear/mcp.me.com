<?php
namespace Yxd\Services;

use modules\game\models\GameModel;
use Yxd\Services\Models\Games;
/**
 * 游戏服务
 */
class GameService extends Service{
	/**
	 * 新增游戏信息
	 * @param array $data	所有录入数据
	 * @param int $editor_uid	登录者id
	 */
	public static function addGameInfo($data,$editor_uid){
		$addtime = time();
		$data['addtime'] = $addtime;
		$data['updatetime'] = $addtime;
		$data['editor'] = $editor_uid;
		$data['downtimes'] = 0;
		$data['price'] = (float)$data['price'];
		$data['oldprice'] = (float)$data['oldprice'];
		if(isset($data['recommendtime'])) {
			$data['recommendtime'] = strtotime($data['recommendtime']);
			if($data['recommendtime'] == 0){
				$data['recommendtime'] = strtotime(date('Y-m-d',time()));
			}
		}
		$result = self::dbCmsMaster()->transaction(function()use($data,$addtime){
			$gdata = $data;
			if(isset($gdata['mustplay'])) unset($gdata['mustplay']);
			if(isset($gdata['hot_recommend'])) unset($gdata['hot_recommend']);
			if(isset($gdata['tags'])) unset($gdata['tags']);
			$newid = GameModel::addGameInfo($gdata);
			//经典必玩
			if(isset($data['mustplay'])){
				$mustplay_data = array('gid'=>$newid,'title'=>$data['gname'],'addtime'=>$addtime);
				GameModel::addGamemustplay($mustplay_data);
			}
			//精品热门推荐
			if(isset($data['hot_recommend'])){
				$recommend_data = array('gid'=>$newid,'type'=>'h','addtime'=>$addtime);
				GameModel::addGamerecommend($recommend_data);
			}
			//游戏标签
			if(isset($data['tags']) && !empty($data['tags'])){
				$tag_data = array();
				foreach ($data['tags'] as $tag){
					$tag_data[] = array('gid'=>$newid,'tag'=>$tag);
				}
				GameModel::addGametags($tag_data);
			}
			return $newid;
		});
		return $result ? $result : false;
	}
	
	/**
	 * 编辑游戏信息
	 * @param array $data
	 * @param int $editor_uid
	 */
	public static function editGameInfo($gid,$data,$editor_uid,$ico_url){
		$addtime = time();
		$data['updatetime'] = $addtime;
		$data['price'] = (float)$data['price'];
		$data['oldprice'] = (float)$data['oldprice'];
		if(isset($data['recommendtime'])) {
			$data['recommendtime'] = strtotime($data['recommendtime']);
			if($data['recommendtime'] == 0){
				$data['recommendtime'] = strtotime(date('Y-m-d',time()));
			}
		}
		
		$result = self::dbCmsMaster()->transaction(function()use($gid,$data,$addtime,$ico_url){
			$gdata = $data;
			
			if(isset($gdata['mustplay'])) unset($gdata['mustplay']);
			if(isset($gdata['hot_recommend'])) unset($gdata['hot_recommend']);
			if(isset($gdata['tags'])) unset($gdata['tags']);
			
			if ($gdata['zonetype']==2){
				$gdata['isdel'] = 2;
			}elseif($gdata['zonetype']==3){
				$gdata['isdel'] = 3;
			}else{
				$gdata['isdel'] = 0;
			}
			
			if(!isset($gdata['flag'])){		//主页推荐
				$gdata['flag'] = 0;
				$gdata['recommendtime'] = '';
			}
			if(!isset($gdata['ishot'])){	//热门推荐
				$gdata['ishot'] = 0;
			} 
			if(!isset($gdata['isstarting'])){	//首发
				$gdata['isstarting'] = 0;
			}
			if(!isset($gdata['isup'])){	//上架
				$gdata['isup'] = 0;
			}
			
			if(isset($gdata['ico']) && file_exists($ico_url)){	//重新选择了icon图片
				//删除已有的
				unlink($ico_url);
			}
			
			GameModel::updateGameInfo($gid,$gdata);
			//经典必玩
			if(isset($data['mustplay'])){
				if(!GameModel::getGamemustplay($gid)){
					$mustplay_data = array('gid'=>$gid,'title'=>$data['gname'],'addtime'=>$addtime);
					GameModel::addGamemustplay($mustplay_data);
				}
			}
			//精品热门推荐
			if(isset($data['hot_recommend'])){
				if(!GameModel::getGamerecommend($gid,'h')){
					$recommend_data = array('gid'=>$gid,'type'=>'h','addtime'=>$addtime);
					GameModel::addGamerecommend($recommend_data);
				}
			}else{
				GameModel::delGamerecommend($gid,'h');
				GameModel::updateGamerecommend($gid,'h',array('gid'=>0));
			}
			//游戏标签
			if(isset($data['tags']) && !empty($data['tags'])){
				$tag_data = array();
				foreach ($data['tags'] as $tag){
					$tag_data[] = array('gid'=>$gid,'tag'=>$tag);
				}
				GameService::changeGametags($gid, array('gid'=>0), $tag_data);
			}
		});
		return $result ? false : true;
	}
	
	/**
	 * 更新游戏标签
	 * @param int $gid
	 * @param array $data
	 */
	public static function changeGametags($gid,$updata,$indata){
		//1.删除独有的
		GameModel::delGametags($gid);
		//2.更新共有的
		GameModel::updateGametags($gid,0,$updata);
		//3.重新添加新的
		GameModel::addGametags($indata);
	}
}