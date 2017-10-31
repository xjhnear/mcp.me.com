<?php
namespace modules\game\controllers;

use Yxd\Modules\Core\BackendController;
use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Input;

use modules\game\models\GameModel;
use Youxiduo\V4\Game\MygameService; 
class GametypeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'game';
	}

	public function getList()
	{	
		$search=$tagvals=array();
		$search['page']=Input::get('page',1);
		$search['pageSize']=15;
		$datalist=MygameService::getGameTypeList($search,'sort');
		if(!empty($datalist['result'])){
			foreach($datalist['result'] as $key=>&$value){
				$typetag=MygameService::getTagInfo($value['id'],'typeid');
				foreach($typetag as $tagvalue)
				{
					if(!empty($tagvalue))
						$tagvals[]=$tagvalue['tag'];
				}
				$value['tagname']=join(',',$tagvals);
			}
		} 
		$datalist=MygameService::_processingInterface($datalist,$search,$search['pageSize']);
		return $this->display('/game-type',$datalist);
	}

	public function  getViewAddEdit($id=0)
	{
	 	
	 	$datainfo['type']=MygameService::getGameTypeInfo($id);
	 	$datainfo['Tag']=MygameService::getTagInfo($id,'typeid');
	 	$arr=array();
	 	foreach($datainfo['Tag'] as $key=>&$value)
	 	{
	 		$arr[]=$value['tag'];
	 	}
	 	$datainfo['gametype']=join(',',$arr);
	 	return $this->display('/game-type-edit',$datainfo);
	}


	public function postEdit()
	{
		$input = Input::all();
	    if(!empty($input['tags']))
	    {
	    	$del = MygameService::deltag($input['id']);
	    	$arr=explode(",", $input['tags']);
	    	$datainfo['typeid']=$input['id'];
	    	$datainfo['addtime']=time();
	    	foreach($arr as $key => $value){	
	    		$datainfo['tag']=$value;
	    		$id= MygameService::insertTag($datainfo);
	    	}
	    }
		return $this->redirect('game/gametype/list','修改成功');
	}

	//置顶
	public function getApptop($id=0,$isapptop=0)
	{
		if(empty($id)){
            return $this->back()->with('global_tips','编号缺失');
		}
		$datainfo['id']=$id;
		$datainfo['isapptop']=($isapptop == 1) ? 0 : 1;
		MygameService::updateGametype($datainfo,$id);
		return $this->redirect('game/gametype/list','操作成功');
	}

	public function postSetsort()
	{	
		$input = Input::all();
		if(empty($input['id'])){
            return $this->json(array('ok'=>0));
		}
		$datainfo['id']=$input['id'];
		$datainfo['sort']=(!empty($input['sort'])) ? $input['sort'] : 0;
		MygameService::updateGametype($datainfo,$datainfo['id']);
		return $this->json(array('ok'=>1));
	}

}