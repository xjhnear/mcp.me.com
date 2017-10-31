<?php
use Illuminate\Support\Facades\Input;
use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;
use Yxd\Services\Cms\RankService;
/**
 * 排行榜
 */
class RankController extends BaseController
{
	/**
	 * 类型
	 */
	public function types()
	{		
		$out = GameService::getGameTypeList();
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * Tags
	 */
	public function tags()
	{
		$out = GameService::getGameTags();
		return $this->success(array('result'=>$out));
	}
	
	public function chart()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		//排行类型0:活跃度1:周下载2:最新更新
		$type = Input::get('type',0);
		//游戏类型
		$gtid = Input::get('gtid',0);
		$priceType = Input::get('priceType');
		$tag = Input::get('tag');
		
		$games = RankService::getGameList($type,$gtid,$priceType,$tag,'desc',$page,$pagesize);
		$gametype = GameService::getGameTypeOption();
		$out = array();
		foreach($games['games'] as $index=>$row){
			$out[$index]['gid'] = $row['id'];
			$out[$index]['title'] = $row['shortgname'] ? $row['shortgname'] : $row['gname'];
			$out[$index]['img'] = GameService::joinImgUrl($row['ico']);
			$out[$index]['comment'] = '';
			$out[$index]['video'] = '0';
			$out[$index]['tname'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$out[$index]['free'] = $row['pricetype']==1 ? "1" : "0";
			$out[$index]['limitfree'] = $row['pricetype']==2 ? "1" : "0";
			$out[$index]['size'] = $row['size'];
			$out[$index]['score'] = $row['score'];
			$out[$index]['oldprice'] = strval($row['oldprice']);
			$out[$index]['price'] = $row['price'];
			$out[$index]['guide'] = strval(0);//$row['guide'];
			$out[$index]['opinion'] = strval(0);//$row['opinion'];
			$out[$index]['zone'] = $row['zonetype'];
			$out[$index]['downcount'] = $type==1 ? $row['weekdown'] : $row['downtimes'];
			$out[$index]['commentcount'] = $row['commenttimes'];//$row['zonetype'];
			$out[$index]['hot'] = $row['ishot'];
			$out[$index]['week'] = $type==1 ? 1 : 0;
			$out[$index]['language'] = GameService::$languages[$row['language']?:0];
		}
		return $this->success(array('result'=>$out,'totalCount'=>$games['total']));
	}
}