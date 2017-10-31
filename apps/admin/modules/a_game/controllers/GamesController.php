<?php
namespace modules\a_game\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

use Yxd\Modules\Core\BackendController;

use Yxd\Services\GameService;
use Yxd\Services\UserService;
use libraries\Helpers;
use modules\game\models\GameModel;
use Doctrine\DBAL\Types\Type;
use Yxd\Services\ClearCacheService;
use Yxd\Services\SyncgameService;

use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\GamePicture;

class GamesController extends BackendController
{
	
	public function _initialize()
	{
		$this->current_module = 'a_game';
	}
	
	public function getSearch()
	{
		$cond = $search = Input::only('type','zonetype');
		$keytype = Input::get('keytype');
		$keyword = Input::get('keyword');
		$cond['keytype'] = $keytype;
		$cond['keyword'] = $keyword;
		if($keytype=='id'){
			$search['id'] = $keyword;
		}else{
			$search['gname'] = $keyword;
		}
		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();	
		//$data['keytype'] = $keytype;
		//$data['keyword'] = $keyword;
		$data['gametype'] = array('0'=>'未分类')+(\Yxd\Services\Cms\GameService::getGameTypeOption());
		$data['pricetype'] = Config::get('yxd.game_pricetype');
		$data['zonetype'] = Config::get('yxd.game_zonetype');
		$data['imgurl'] = Config::get('app.image_url');
		$result = Game::m_search($search,$page,$pagesize);			
		$data['games'] = $result['result'];
		$data['forums'] = GameModel::getForumGids();
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($cond);
		$data['cond'] = $cond;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		return $this->display('games-search',$data);
	}

	public function getEditRedirect($game_id)
	{
		$data = array();
		$result = Config::get('linktype');
		$linkTypeListDesc = array();
		foreach($result as $key=>$row){
			$linkTypeList[$key] = $row['name'];
			$linkTypeListDesc[$key] = $row['description'];
		}
		
		$data['linkTypeList'] = $linkTypeList;
		$data['descs'] = json_encode($linkTypeListDesc);
		
		$game = Game::m_getInfo($game_id);
		$data['game'] = $game;
		return $this->display('game-redirect',$data);
	}
	
	public function postEditRedirect()
	{
		$game_id = Input::get('game_id');
		$linktype = Input::get('linktype');
		$link = Input::get('link');
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$app_adv_img = $dir . $new_filename . '.' . $mime;
		}else{
			$app_adv_img = Input::get('app_adv_img');
		}
		$is_app_hot_top = Input::get('is_app_hot_top',0);
		if($is_app_hot_top){
			Game::db()->where('is_app_hot_top','=',1)->update(array('is_app_hot_top'=>0));
		}
		$data  = array('linktype'=>$linktype,'link'=>$link,'app_adv_img'=>$app_adv_img,'is_app_hot_top'=>$is_app_hot_top);		
		Game::db()->where('id','=',$game_id)->update($data);
		return $this->back('设置成功');
	}
	
	/**
	 * 游戏添加页面
	 */
	public function getGameAdd()
	{
		$game_type = Config::get('rule.game_types');	//所有类型
		$tag_list = GameModel::getTaglist(1);	//所有标签
		$data['gametype'] = $game_type;
		$data['alltags'] = $tag_list;
		return $this->display('game_info',$data);
	}
	
	/**
	 * 游戏添加提交
	 */
	public function postGameAddDo()
	{
		$input = Input::all();
		
		$validate = self::gameValidate($input,'add');
		if(!$validate['pass']) return Redirect::to('a_game/games/game-add')->withInput()->withErrors($validate['validator']);
		
		$file = Input::file('ico');
		$dir = '/u/gameico/' . date('Y') . date('m') . '/';
		$icon = Helpers::uploadPic($dir, $file);
		$input['ico'] = $icon;
		
		$editor = $this->current_user;
		$editor_uid = $editor ? $editor['id'] : 0;
		$newid = GameService::addGameInfo($input,$editor_uid);
		if($newid){
			if(isset($input['mustplay'])){ //经典必玩
				//ClearCacheService::pushDataToQueue('commend','mustplay','add');
			}
			//SyncgameService::addSyncData($newid);
			//ClearCacheService::pushDataToQueue('game','info','add',array('game_id'=>$newid));
			return Redirect::to('a_game/games/game-edit/'.$newid)->with('global_tips','添加成功！');
		}else{
			return $this->back()->with('global_tips','添加失败，请重试');
		}
	}
	
	/**
	 * 游戏编辑页面
	 * @param int $game_id
	 */
	public function getGameEdit($game_id)
	{
		$ginfo = Game::m_getInfo($game_id);
		if(!$ginfo) return $this->back()->with('global_tips','数据错误，请重试');
		$game_type = Config::get('rule.game_types');	//所有类型
		$tag_list = GameModel::getTaglist($ginfo['type']);	//所有标签
		$game_tags = GameModel::getGametags($ginfo['id']);  //游戏标签
		$jinpin = GameModel::getGamerecommend($ginfo['id'],'h');	//精品推荐
		$biwan = GameModel::getGamemustplay($ginfo['id']);  //经典必玩		
		
		$data['hot_recommend'] = $jinpin ? 1 : 0;
		$data['mustplay'] = $biwan ? 1 : 0;		
		$data['game'] = $ginfo;
		$data['gametype'] = $game_type;
		$data['gtags'] = $game_tags;
		$data['alltags'] = $tag_list;
		return $this->display('game_info',$data);
	}
	
	public function getAjaxTaglist(){
		$typeid = Input::get('typeid');
		$tag_list = GameModel::getTaglist($typeid);
		$result = array();
		if($tag_list){
			foreach ($tag_list as $val){
				$result[] = array('label'=>$val,'value'=>$val);
			}
		}
		return Response::json($result);
	}
	
	/**
	 * 游戏编辑提交
	 */
	public function postGameEditDo(){
		$input = Input::all();
		$gid = $input['id'];
		$validate = self::gameValidate($input,'edit');
		
		if(!$validate['pass']) return Redirect::to('a_game/games/game-edit'.'/'.$gid)->withInput()->withErrors($validate['validator']);
		
		$ginfo = GameModel::getInfo($gid);
		if(!$ginfo) return $this->back()->with('gloabl_tips','游戏不存在！');
		$old_zonetype = $ginfo['zonetype'];
		$ico_url = $ginfo['ico']? realpath(storage_path().$ginfo['ico']) : false;
		if ($input['zonetype']==2){
			$isdel = 2;
		}elseif($input['zonetype']==3){
			$isdel = 3;
		}else{
			$isdel = 0;
		}
		
		$file = Input::file('ico');
		if($file){
			$dir = '/u/gameico/' . date('Y') . date('m') . '/';
			$ico = Helpers::uploadPic($dir, $file);
			$input['ico'] = $ico;
		}else{
			unset($input['ico']);
		}
		
		$editor = self::getSessionData('yxd_user');
		$editor_uid = $editor ? $editor['id'] : 0;
		if(GameService::editGameInfo($gid,$input,$editor_uid,$ico_url)){
			if(isset($input['mustplay'])){ //经典必玩
				ClearCacheService::pushDataToQueue('commend','mustplay','add');
			}
			if(($old_zonetype==2 || $old_zonetype==3)&& $isdel==0){
				SyncgameService::addSyncData($gid);
			}else{
				SyncgameService::editSyncData($gid);
			}
			ClearCacheService::pushDataToQueue('game','info','update',array('game_id'=>$gid));
			return $this->back()->with('global_tips','修改成功！');
		}else{
			return $this->back()->with('global_tips','修改失败，请重试');
		}
	}
	
	/**
	 * 添加游戏的图片页面
	 */
	public function getAddGameListPic($gid)
	{
		if(!$gid || !is_numeric($gid)) return $this->back()->with('global_tips','数据错误！');
		$data['gid'] = $gid;
		return $this->display('game-list-pic-add',$data);
	}
	
	/**
	 * 上传游戏图片
	 * @param int $gid
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function anyGameListPicInfo($game_id)
	{
		if(Request::isMethod('get')){
			$files = array('files');
			//获取该游戏的litpic
			$litpics = GamePicture::getListByGameId($game_id);
			if($litpics){
				foreach ($litpics as $pic){
					$files['files'][] = array(
							'name'=>$pic['litpic'],
							'url'=>Config::get('ueditor.imageUrlPrefix').$pic['litpic'],
							'deleteUrl'=>'/a_game/games/del-game-list-pic/'.base64_encode($pic['litpic']),
							'deleteType'=>'GET'
					);
				}
			}
			return Response:: json($files);
		}
		
		if(Request::isMethod('post')){
			$file = current(Input::file('files'));
			$path = '/u/gamepic/' . date('Y') . date('m') . '/';
			$filename = date('YmdHis').str_random(4);
			$del_url = '/a_game/games/del-game-list-pic/';
			$suffix = $file->guessClientExtension();
			$upload_handler = Helpers::jqueryFileUpload($path, $file, $filename, $suffix, $del_url);
			if($upload_handler){
				$add_data = array('type'=>'android','game_id'=>$game_id,'litpic'=>$path.$filename.'.'.$suffix);
				GamePicture::m_save($add_data);
			}else{
				return Response:: json(0);
			}
		}
	}
	
	/**
	 * 删除游戏图片
	 * @param string $path
	 * @return boolean
	 */
	public function anyDelGameListPic($path)
	{
		if(!$path) return 0;
		//$fall_path = storage_path().base64_decode($path);
		GamePicture::m_delete(0,0,base64_decode($path));		
	}		
	
	private function gameValidate($input,$do_type){
		$rule = array(
				'gname'=>'required','shortgname'=>'required','type'=>'required','editorcomt'=>'required','pricetype'=>'required',
				'version'=>'required','size'=>'required','zonetype'=>'required',
				'platform'=>'required','company'=>'required','language'=>'required'
		);
		if($do_type == 'add'){			
			$rule['ico'] = 'required';
		}else{
		}
		if(isset($input['flag']) && $input['flag'] == 1){
			$rule['shortcomt'] = 'required';
		}
		$message = array(
				'required' => '不能为空',
				'numeric' => '必须为数字',
		);
		
		$validator = Validator::make($input,$rule,$message);
		if ($validator->fails()){
			$pass = false;
		}else{
			$pass = true;
		}
		return array('pass'=>$pass,'validator'=>$validator);
	}

	public function getDownloadReport()
	{
		$startdate = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$enddate = mktime(23,59,59,date('m'),date('d'),date('Y'));

		$s = Input::get('startdate','');
		$d = Input::get('enddate','');
		!empty($s) && $startdate = strtotime($s);
		!empty($d) && $enddate = strtotime($d)+60*60*24-1;

		$search = array('startdate'=>date('Y-m-d',$startdate),'enddate'=>date('Y-m-d',$enddate));
		$data['search'] = $search;

		$result = \Youxiduo\Android\Model\GameDownloadFlow::db()->select(\Youxiduo\Android\Model\GameDownloadFlow::raw('date(FROM_UNIXTIME(ctime)) as everyday,count(*) as total'))
			->where('status','=',1)->where('ctime','>',$startdate)
			->where('ctime','<',$enddate)->groupBy(\Youxiduo\Android\Model\GameDownloadFlow::raw('date(FROM_UNIXTIME(ctime))'))->get();
		$data['datalist'] = $result;
		return $this->display('game-download-report',$data);
	}
}