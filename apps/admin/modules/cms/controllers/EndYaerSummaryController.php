<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;
use Youxiduo\Cms\Model\EndYearSummary;


class EndYaerSummaryController extends BestController
{
	public function _initialize()
	{
		$this->current_module = 'cms';
	}

    /**
     * 列表页
     * @param int $audit
     * @return
     * @internal param string $type
     */
	public function getSearch($audit=0)
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$keytype = Input::get('keytype','');
		$keyword = empty($keytype) ? '' : Input::get('keyword','') ;
		$cond['keyword'] = $keyword;
		$cond['keytype'] = empty($keytype)?'content':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'nick' => '名称' , 'content' => '内容');
		$data['keyword'] = $keyword;
		
		$result = EndYearSummary::getList($page,$pagesize,$audit,$keyword,$keytype);
		if(empty($result)){
			return $this->back()->with('global_tips','参数出错，请联系技术。');
			exit;
		}
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($cond);
		$data['cond'] = $cond;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['result'];
		return $this->display('endyear-summary-list',$data);
	}
	
	public function anyCheck($id=0,$audit=0){
		$data['id'] = $id;
		if($audit==1){
			$audit = 0 ;
		}else{
			$audit = 1 ;
		}
		$data['audit'] = $audit;
		$rs = EndYearSummary::save($data);
		return Redirect::to('cms/summary/search')->with('global_tips','操作完成');
	}
	
	public function anyChecks(){
		$input = Input::all();
		$ids = explode(',', $input['tids']);
		$data['audit'] = $input['audit'];
		$rs = EndYearSummary::chickSave($ids,$data);
		return $this->json(array('html'=>'操作完成'));
	}

	/**
	 * 删除
	 * @param int $id
	 */
	public function getDel($id)
	{
		$result = EndYearSummary::del($id);
		return $this->redirect("cms/summary/search");
	}
}