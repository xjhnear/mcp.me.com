<?php
namespace Youxiduo\Cms\Model;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
class Opinion extends BaseModel
{
	const TABLE = 'feedback';
	/**
	 * 保存文章
	 */
	public static function save($data)
	{
	    if(!empty($data['id'])){
	    	$id = $data['id'];
	    	unset($data['id']);	
			return self::dbCmsMaster()->table(self::TABLE)->where('id','=',$id)->update($data);
		}else{
			$data['addtime'] = time();
			return self::dbCmsMaster()->table(self::TABLE)->insertGetId($data);
		}
	}
	/**
	 * 查询攻略列表
	 * @param number $page
	 * @param number $pagesize
	 * @param string $keyword
	 * @param string $t
	 * @return multitype:unknown number
	 */
	public static function getList($page=1,$pagesize=10,$keyword='',$t='ftitle'){
		$flag = 0;
		$dbSlave = self::dbCmsMaster()->table(self::TABLE);
		if($keyword != '' && $t != ''){
			//判断是否按照游戏名称查询数据
			if($t == 'gname' ){
				$iosGame = GameModel::getnameInfo($keyword);
				$androidGame = Game::mname_getInfo($keyword);
				if(!empty($iosGame)){
					$dbSlave =  $dbSlave->where('gid','=',"{$iosGame['id']}");
				}
				if(!empty($androidGame)){
					$dbSlave =  $dbSlave->orwhere('agid','=',"{$androidGame['id']}");
				}
			}else{
				$dbSlave =  $dbSlave->where($t,'like',"%{$keyword}%");
			}
		}
		$dbSlave =  $dbSlave ->forPage($page,$pagesize) ->orderBy('addtime','desc');
		$total = $dbSlave ->count();
		$results =  $dbSlave  ->get();
// 		print_r(self::dbCmsMaster()->getQueryLog());exit;
		foreach ($results as &$v){
			//查询游戏名称
			if($v['gid'] != 0){
				$iosGame = GameModel::getInfo($v['gid']);
				$v['gname'] = '';
				if($iosGame){
					$v['gname'] = "【IOS-{$iosGame['gname']}】";
				}else{
					$androidGame = Game::m_getInfo($v['agid']);
					if($androidGame){
						$v['gname'] = "<font style='color:red;'>【Android-{$androidGame['gname']}】</font>";
					}else{
						$v['gname'] = "<font style='color:red;'>【关联游戏不存在】</font>";
					}
				}
			}else if($v['agid'] != 0){
				$androidGame = Game::m_getInfo($v['agid']);
				$v['gname'] = '';
				if($androidGame){
					$v['gname'] = "【Android-{$androidGame['gname']}】";
				}else{
					$iosGame = GameModel::getInfo($v['gid']);
					if($iosGame){
						$v['gname'] = "<font style='color:red;'>【Android-{$iosGame['gname']}】</font>";
					}else{
						$v['gname'] = "<font style='color:red;'>【关联游戏不存在】</font>";
					}
				}
			}else{
				$v['gname'] = "<font style='color:red;'>【暂无关联游戏】</font>";
			}
			$v['type'] = '评测';
		}
		return array('results'=>$results,'total'=>$total);
	}
	/**
	 * 查询文章
	 * @param int $id
	 */
	public static function getDetails($id){
		return self::dbCmsMaster()->table(self::TABLE) ->where('id','=',$id) ->first();
	}
	
	/**
	 * 删除文章
	 */
	public static function delArticle($id) {
		return self::dbCmsMaster()->table(self::TABLE)->where('id', '=', $id)->delete();
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