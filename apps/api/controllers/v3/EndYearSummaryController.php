<?php
use Yxd\Modules\Core\CacheService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Youxiduo\Cms\Model\EndYearSummary;

/**
 * 游戏
 */
class EndYearSummaryController extends BaseController
{
	/**
	 * 评论列表
	 */
	public function getlist()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		//查询游戏
		$result = EndYearSummary::getList($page,$pagesize);
		return $this->success(array('result'=>$result['result'],'totalCount'=>$result['total']));
	}
	
	/**
	 * 添加评论
	 */
	public function addComment()
	{
		$data['nick'] = htmlentities(Input::get('nick','游戏多玩家'),ENT_QUOTES,'UTF-8');
		$data['content'] = htmlentities(Input::get('content',''),ENT_QUOTES,'UTF-8');
		$result = EndYearSummary::save($data);
		return $this->success(array('result'=>$result));
		
	}
}