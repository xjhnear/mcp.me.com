<?php
namespace modules\activity\controllers;
use modules\giftbag\models\GiftbagModel;

use modules\activity\models\HuntModel;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;
use modules\activity\models\PrizeModel;

class PrizeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
    public function getHome()
	{		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$search = array();			
		$totalcount = PrizeModel::searchCount($search);			
		$data['prizes'] = PrizeModel::searchList($search,$page,$pagesize);
		$ids = array();
		foreach($data['prizes'] as $row){
			$ids[] = $row['gift_id'];
		}
		$data['gifts'] = GiftbagModel::getInfoByIds($ids);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;
		return $this->display('prize-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$prize = array(		    
		);
		$data['prize'] = $prize;
		$data['cate'] = array('寻宝箱'=>'寻宝箱','有奖问答'=>'有奖问答');
		return $this->display('prize-info',$data);
	}
	
    public function getEdit($id)
	{
		$data = array();
		$data['prize'] = PrizeModel::getInfo($id);
		$data['cate'] = array('寻宝箱'=>'寻宝箱','有奖问答'=>'有奖问答');
		return $this->display('prize-edit',$data);
	}
	
	public function postSave()
	{
		$input['id'] = (int)Input::get('id');
		$input['type'] = (int)Input::get('type');
		$input['name'] = Input::get('name');
		$input['shortname'] = Input::get('shortname');
		$input['score'] = (int)Input::get('score',0);
		$input['gift_id'] = (int)Input::get('gift_id',0);
		$input['expense'] = Input::get('expense','');
		
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('listpic')){
	    	
			$file = Input::file('listpic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['listpic'] = $dir . $new_filename . '.' . $mime;
		}
		
		$result = PrizeModel::save($input);
		
	    if($result){
			return $this->redirect('activity/prize/home')->with('global_tips','奖品添加成功');
		}else{
			return $this->back()->with('global_tips','奖品添加失败');
		}
	}
	
	public function getSearch($no=0)
	{
		$keytype = Input::get('keytype');
		$keyword = Input::get('keyword');
		
		if($keytype=='id'){
			$search['id'] = $keyword;
		}else{
			$search['name'] = $keyword;
		}		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$data['no'] = $no;	
		$data['keytype'] = $keytype;
		$data['keyword'] = $keyword;
		$data['imgurl'] = '';
		$totalcount = PrizeModel::searchCount($search);			
		$data['prizes'] = PrizeModel::searchList($search,$page,$pagesize);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;
		$html = $this->html('pop-prize-list',$data);
		return $this->json(array('html'=>$html));
	}
}