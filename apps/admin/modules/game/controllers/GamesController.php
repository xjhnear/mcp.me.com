<?php
namespace modules\game\controllers;

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

class GamesController extends BackendController
{
	
	public function _initialize()
	{
		$this->current_module = 'game';
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
		$data['imgurl'] = Config::get('app.img_url');
		$result = GameModel::search($search,$page,$pagesize);			
		$data['games'] = $result['result'];
		$data['forums'] = GameModel::getForumGids();
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($cond);
		$data['cond'] = $cond;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		return $this->display('games-search',$data);
	}
	
	
	
	public function getGameControl($id=0,$game_id){
		$ginfo = GameModel::getInfo($game_id);
		if(!$ginfo) return $this->back()->with('global_tips','数据错误，请重试');
		$gsetinfo = GameModel::getGameControl($id);
		if($gsetinfo){
			$gsetinfo['control_data'] = unserialize($gsetinfo['control_data']);
			$data = array(
			        'id'=>$gsetinfo['id'],
					'gid' => $gsetinfo['game_id'],
					'gname' => $gsetinfo['game_name'],
					'zone_type' => array(1=>'简易',2=>'精品'),
					'select_type' => $gsetinfo['zone_type'],
					'version' => $gsetinfo['version']
			);
			foreach ($gsetinfo['control_data'] as $key=>$value){
				$data[$key] = $value;
			}
		}else{
			$data = array(
					'gid' => $ginfo['id'],
					'gname' => $ginfo['shortgname'],
					'zone_type' => array(1=>'简易',2=>'精品')
			);
		}
		return $this->display('game-control',$data);
	}
	
	public function getGameControlList($game_id)
	{
		$data = array();
		$result = GameModel::getGameControlList($game_id);
		foreach($result as $key=>$row){
			$row['option'] = unserialize($row['control_data']);
			$result[$key] = $row;
		}
		$data['datalist'] = $result;
		$data['gid'] = $game_id;
		return $this->display('game-control-list',$data);
	}
	
	public function postGameControlSave(){
		$input = Input::all();
		$data = array();
		if($input){
			foreach ($input as $key=>$row){
				if($key=='id'){
					$data['id'] = $row;
				}elseif($key == 'gid'){
					$data['game_id'] = $row;
				}elseif ($key == 'gamename'){
					$data['game_name'] = $row;
				}elseif ($key == 'zonetype'){
					$data['zone_type'] = $row;
				}elseif ($key == 'version'){
					$data['version'] = $row;
				}else{
					$data['control_data'][$key] = $row;
				}
			}
			$data['control_data'] = serialize($data['control_data']);
		}
		
		if(GameModel::setGameControl($data)){
			return $this->redirect('game/games/game-control-list/'.$data['game_id'])->with('global_tips','保存成功');
		}else{
			return $this->back()->with('global_tips','操作失败，请重试');
		}
	}
	
	/**
	 * 游戏添加页面
	 */
	public function getGameAdd(){
		$game_type = Config::get('rule.game_types');	//所有类型
		$tag_list = GameModel::getTaglist(1);	//所有标签
		$data['gametype'] = $game_type;
		$data['alltags'] = $tag_list;
		return $this->display('game-add',$data);
	}
	
	/**
	 * 游戏添加提交
	 */
	public function postGameAddDo(){
		$input = Input::all();
		
		$validate = self::gameValidate($input,'add');
		if(!$validate['pass']) return Redirect::to('game/games/game-add')->withInput()->withErrors($validate['validator']);
		
		$file = Input::file('ico');
		$dir = '/u/gameico/' . date('Y') . date('m') . '/';
		$icon = Helpers::uploadPic($dir, $file);
		$input['ico'] = $icon;
		
		$editor = self::getSessionData('yxd_user');
		$editor_uid = $editor ? $editor['id'] : 0;
		$newid = GameService::addGameInfo($input,$editor_uid);
		if($newid){
			if(isset($input['mustplay'])){ //经典必玩
				ClearCacheService::pushDataToQueue('commend','mustplay','add');
			}
			SyncgameService::addSyncData($newid);
			ClearCacheService::pushDataToQueue('game','info','add',array('game_id'=>$newid));
			return Redirect::to('game/games/game-edit/'.$newid)->with('global_tips','添加成功！');
		}else{
			return $this->back()->with('global_tips','添加失败，请重试');
		}
	}
	
	/**
	 * 游戏编辑页面
	 * @param int $game_id
	 */
	public function getGameEdit($game_id){
		$ginfo = GameModel::getInfo($game_id);
		if(!$ginfo) return $this->back()->with('global_tips','数据错误，请重试');
		$game_type = Config::get('rule.game_types');	//所有类型
		$tag_list = GameModel::getTaglist($ginfo['type']);	//所有标签
		$game_tags = GameModel::getGametags($ginfo['id']);  //游戏标签
		$jinpin = GameModel::getGamerecommend($ginfo['id'],'h');	//精品推荐
		$biwan = GameModel::getGamemustplay($ginfo['id']);  //经典必玩
		
		$ginfo['recommendtime'] = date("Y-m-d",$ginfo['recommendtime']?$ginfo['recommendtime']:time()); //主页推荐时间转换
		
		$data['hot_recommend'] = $jinpin ? 1 : 0;
		$data['mustplay'] = $biwan ? 1 : 0;		
		$data['game'] = $ginfo;
		$data['gametype'] = $game_type;
		$data['gtags'] = $game_tags;
		$data['alltags'] = $tag_list;
		return $this->display('game-edit',$data);
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
		
		if(!$validate['pass']) return Redirect::to('game/games/game-edit'.'/'.$gid)->withInput()->withErrors($validate['validator']);
		
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
	public function getAddGameListPic($gid){
		if(!$gid || !is_numeric($gid)) return $this->back()->with('global_tips','数据错误！');
		$data['gid'] = $gid;
		return $this->display('game-list-pic-add',$data);
	}
	
	/**
	 * 上传游戏图片
	 * @param int $gid
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function anyGameListPicInfo($gid){
		if(Request::isMethod('get')){
			$files = array('files');
			//获取该游戏的litpic
			$litpics = GameModel::getGamelitpic(0,$gid);
			if($litpics){
				foreach ($litpics as $pic){
					$spilit = explode('/', $pic['litpic']);
					$files['files'][] = array(
							'name'=>end($spilit),
							'url'=>Config::get('ueditor.imageUrlPrefix').$pic['litpic'],
							'deleteUrl'=>'/game/games/del-game-list-pic/'.base64_encode($pic['litpic']),
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
			$del_url = '/game/games/del-game-list-pic/';
			$suffix = $file->guessClientExtension();
			$upload_handler = Helpers::jqueryFileUpload($path, $file, $filename, $suffix, $del_url);
			if($upload_handler){
				$add_data = array('gid'=>$gid,'litpic'=>$path.$filename.'.'.$suffix);
				GameModel::addGamelitpic($add_data);
				SyncgameService::picSyncData($gid);
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
	public function anyDelGameListPic($path){
		if(!$path) return 0;
		$fall_path = storage_path().base64_decode($path);
		$litpicinfo = current(GameModel::getGamelitpic(0,0,base64_decode($path)));
		$gid = $litpicinfo ? $litpicinfo['gid'] : false;
		if(GameModel::delGamelitpic(base64_decode($path))){
			if($gid) SyncgameService::picSyncData($gid);
			if(file_exists($fall_path) && is_file($fall_path)){
				unlink($fall_path);
			}
		}else{
			return Response::json(0);
		}
	}
	
	/**
	 * 添加H5游戏详情图片页面
	 * @param int $gid
	 */
	public function getAddH5GameDetailPic($gid){
		if(!$gid || !is_numeric($gid)) return $this->back()->with('global_tips','数据错误！');
		$data['gid'] = $gid;
		return $this->display('h5-game-detail-pic-add',$data);
	}
	
	/**
	 * 上传H5游戏详情图片
	 * @param unknown $gid
	 */
	public function anyH5GameDetailPicInfo($gid){
		if(Request::isMethod('get')){
			$files = array('files');
			//获取该游戏的litpic
			$litpics = GameModel::getGameinfopic($gid);
			if($litpics){
				foreach ($litpics as $pic){
					$spilit = explode('/', $pic['litpic']);
					$files['files'][] = array(
							'name'=>end($spilit),
							'url'=>Config::get('ueditor.imageUrlPrefix').$pic['litpic'],
							'sort'=>$pic['sort'],
							'linkurl'=>$pic['linkurl'],
							'deleteUrl'=>'/game/games/del-h5-game-detail-pic/'.base64_encode($pic['litpic']),
							'deleteType'=>'GET'
					);
				}
			}
			return Response:: json($files);
		}
		
		if(Request::isMethod('post')){
			$file = current(Input::file('files'));
			$file_name = $file->getClientOriginalName();
			$file_name = str_replace('.', '_',$file_name);
			$sort = $_POST['sort'.$file_name];
			$linkurl = $_POST['linkurl'.$file_name];
			$path = '/u/gameinfopic/' . date('Y') . date('m') . '/';
			$filename = date('YmdHis').str_random(4);
			$del_url = '/game/games/del-h5-game-detail-pic/';
			$suffix = $file->guessClientExtension();
			$upload_handler = Helpers::jqueryFileUpload($path, $file, $filename, $suffix, $del_url);
			if($upload_handler){
				$add_data = array('gid'=>$gid,'agid'=>0,'litpic'=>$path.$filename.'.'.$suffix,'sort'=>$sort,'linkurl'=>$linkurl);
				GameModel::addGameinfopic($add_data);
			}else{
				return Response:: json(0);
			}
		}
	}
	
	/**
	 * 删除h5游戏详情图片
	 * @param string $path
	 * @return boolean
	 */
	public function anyDelH5GameDetailPic($path){
		if(!$path) return 0;
		$fall_path = storage_path().base64_decode($path);
		if(GameModel::delGameinfopic(base64_decode($path))){
			if(file_exists($fall_path) && is_file($fall_path)){
				unlink($fall_path);
			}
		}else{
			return Response::json(0);
		}
	}
	
	private function gameValidate($input,$do_type){
		$rule = array(
				'gname'=>'required','shortgname'=>'required','price'=>'required|numeric',
				'oldprice'=>'required|numeric','type'=>'required','editorcomt'=>'required','pricetype'=>'required',
				'version'=>'required','size'=>'required','downurl'=>'required','zonetype'=>'required',
				'platform'=>'required','company'=>'required','language'=>'required'
		);
		if($do_type == 'add'){
			$rule['itunesid'] = 'required|hasgame:'.$input['itunesid'];
			$rule['ico'] = 'required';
		}else{
			$rule['itunesid'] = 'required|hasgame:'.$input['id'];
		}
		if(isset($input['flag']) && $input['flag'] == 1){
			$rule['recommendtime'] = 'required';
			$rule['shortcomt'] = 'required';
		}
		$message = array(
				'required' => '不能为空',
				'numeric' => '必须为数字',
				'hasgame' => $do_type == 'add' ? '该识别码已存在' : '识别码 < '.$input['itunesid'].' > 已存在'
		);
		Validator::extend('hasgame', function($attribute, $value, $parameters)
		{
			$exist_game = GameModel::getInfo(false,$value);
			return $exist_game ? ($exist_game['id'] == $parameters[0] ? true : false) : true;
		});
		$validator = Validator::make($input,$rule,$message);
		if ($validator->fails()){
			$pass = false;
		}else{
			$pass = true;
		}
		return array('pass'=>$pass,'validator'=>$validator);
	}
}