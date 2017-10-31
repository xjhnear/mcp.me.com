<?php
namespace modules\game\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\Game\MygameService;
use Youxiduo\V4\Game\GameService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
class MustplayController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'game';
	}

	public function getList()
	{	
		$search=$datalist=array(); 
        $search['page']=Input::get('page',1);
        $search['pageSize']=15;
        $datalist['result']=MygameService::getGameMustPlayList('ios',$search['page'],$search['pageSize']);
        if(!empty($datalist))
        {
        	foreach ($datalist['result'] as $key=>&$value) {
        		$arr=GameService::getMultiInfoById(array('0'=>$value['gid']),'ios');
        		$value['gname']=!empty($arr)?$arr['0']['shortgname']:'';
        	}
        }
        $datalist['totalCount']=MygameService::getGameMustPlayCount('ios');
        $datalist=MygameService::_processingInterface($datalist,$search,$search['pageSize']);
        return $this->display('/mustplay-list',$datalist);
	}

	public function postSetsort()
	{	
		$input = Input::all();
		if(empty($input['id'])){
            return $this->json(array('ok'=>0));
		}
		$datainfo['id']=$input['id'];
		$datainfo['sort']=(!empty($input['sort'])) ? $input['sort'] : 0;
		MygameService::setGameMustPlay($datainfo,$datainfo['id']);
		return $this->json(array('ok'=>1,'sort'=>$datainfo['sort']));
	}

}