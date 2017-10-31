<?php
namespace Youxiduo\Cms\Model;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
class Videos extends BaseModel
{
	const TABLE = 'videos';
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
	public static function getList($page=1,$pagesize=10,$keyword='',$t='vname'){
		$flag = 0;
		$dbSlave = self::dbCmsMaster()->table(self::TABLE);
		if($keyword!=''){
			$dbSlave =  $dbSlave->where($t,'like',"%{$keyword}%");
		}
		$dbSlave =  $dbSlave ->orderBy('addtime','desc');
		$total = $dbSlave ->count();
		$results =  $dbSlave ->forPage($page,$pagesize) ->get();
		return array('results'=>$results,'total'=>$total);
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


    public static function getAutoSearch($name)
    {
        return  self::dbCmsMaster()->table(self::TABLE)->where('vname','like',"%{$name}%")->select('id','vname as value ')->get();
    }

}