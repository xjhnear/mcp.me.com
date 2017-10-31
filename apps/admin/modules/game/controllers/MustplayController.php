<?php
namespace modules\game\controllers;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\Game\MygameService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\Game\Model\GameMustPlay;
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

	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$mustplay = GameMustPlay::db()->where('id','=',$id)->first();
			$mustplay['pic'] = Utility::getImageUrl($mustplay['pic']);
			$data['mustplay'] = $mustplay;
		}
		return $this->display('mustplay-edit',$data);
	}

	public function postEdit()
	{
		$id = Input::get('id');
		$title = Input::get('title');
		$sort = Input::get('sort');
		$gid = Input::get('gid');
		$pic = Input::get('pic');

		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
		$path = storage_path() . $dir;
		//列表图
		if(Input::hasFile('filedata')){

			$file = Input::file('filedata');
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();
			$file->move($path,$new_filename . '.' . $mime );
			$pic = $dir . $new_filename . '.' . $mime;
		}
		$pic = str_replace('http://img.youxiduo.com','',$pic);
		$input = array(
			'title'=>$title,
			'gid'=>$gid,
			'pic'=>$pic,
			'sort'=>$sort,
			'addtime'=>time()
		);
		$result = false;
		if($id){
			$result = GameMustPlay::db()->where('id','=',$id)->update($input);
		}else{
			$result = GameMustPlay::db()->insertGetId($input);
		}
		if($result){
			return $this->redirect('game/mustplay/list');
		}
		return $this->back('保存失败');
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