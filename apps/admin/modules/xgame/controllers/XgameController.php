<?php
namespace modules\xgame\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use libraries\Upload;
use libraries\MakeDir;
use Yxd\Services\Cms\XgameService;
use modules\xgame\models\XgameModel;


class XgameController extends BackendController {
	public function _initialize() {
		$this->current_module = 'xgame';
	}
	/**
	 * 列表
	 */
	public function getIndex() {
		$cond = $search = Input::only('type','zonetype');
		$page = Input::get('page',1);
		$pagesize = 10;
		$type = 0;
		$keyword = Input::get('keyword','');
		$keytype = Input::get('keytype','');
		$cond['keytype'] = $keytype;
		$cond['keyword'] = $keyword;
		$data = array();
		//查询游戏
		$result = XgameService::getList($page,$pagesize,$type,$keyword,$keytype);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
	
		$pager->appends($cond);
		$cond['keytype'] = empty($keytype)?'gname':$keytype;
		$data['cond'] = $cond;
		
		$data['keyword'] = $keyword;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['games'] = $result['games'];
		return $this->display('xgame-list',$data);
	}
	/**
	 * 添加显示界面
	 */
	public function getAdd() {
		$data['type'] = XgameService::getType();
		return $this->display('xgame-add',$data);
	}
	public function getDel($gid) {
		//查询游戏缩略
		$game = XgameService::getArticle($gid);
		//查询关联图片
		$litpics = XgameService::getPic($gid);
		//删除游戏
		$rs = XgameModel::delXgame($gid);
		$url = $path = '';
		if($rs>0){
			$path = storage_path() . $game['result']['litpic'];
			//删除缩略图
			if(file_exists($path) && is_file($path)){
				unlink($path);
			}
			//循环删除图片
			foreach ($litpics as $pic){
				$url= base64_encode($pic['url']);
				$arr[] = $this->anyXgameDelPic($url);
			}
		}
		return $this->redirect('xgame/forum/index');
	}
	/**
	 * 修改显示界面
	 * @param unknown $gid
	 */
	public function getEdit($gid) {
		$data = XgameService::getArticle($gid);
		$data['type'] = XgameService::getType();
		return $this->display('xgame-edit',$data);
	}

	/**
	 * banner图list
	 */
	public function getBannerList() {
		$data = XgameService::getBanner();
		return $this->display('xgame-banner-list',$data);
	}
	
	/**
	 * 添加banner
	 */
	public function postAddBanner() {
		if($_FILES['uploadinput']['name'] <> ""){
			//设置文件上传目录
			$dir = '/userdirs/xgamebanner/' . date('Y') . '/' . date('m') . '/';
			$savePath = storage_path() . $dir;
			//创建目录
			MakeDir::makeDirectory($savePath);
			
			//允许的文件类型
			$fileFormat = array('gif','jpg','jpge','png');
			//文件大小限制，单位: Byte，1KB = 1000 Byte
			//0 表示无限制，但受php.ini中upload_max_filesize设置影响
			$maxSize = 0;
			//覆盖原有文件吗？ 0 不允许  1 允许
			$overwrite = 0;
			//初始化上传类
			$f = new Upload( $savePath, $fileFormat, $maxSize, $overwrite);
			//如果想生成缩略图，则调用成员函数 $f->setThumb();
			//参数列表: setThumb($thumb, $thumbWidth = 0,$thumbHeight = 0)
			//$thumb=1 表示要生成缩略图，不调用时，其值为 0
			//$thumbWidth  缩略图宽，单位是像素(px)，留空则使用默认值 130
			//$thumbHeight 缩略图高，单位是像素(px)，留空则使用默认值 130
			//$f->setThumb(1);
		
			//参数中的uploadinput是表单中上传文件输入框input的名字
			//后面的0表示不更改文件名，若为1，则由系统生成随机文件名

			if (!$f->run('uploadinput',1)){
				//通过$f->errmsg()只能得到最后一个出错的信息，
				//详细的信息在$f->getInfo()中可以得到。
				echo $f->errmsg()."<br>\n";
			}
			//上传结果保存在数组returnArray中。
			
			$all = Input::all();
			$data = array();
			$dataimg = $f->getInfo();
			$sort = $all['sort'];
			$url = $all['url'];
			$title = $all['title'];
			foreach($dataimg as $key => $pic){
				if(!isset($pic['error'])){
					$data[$key] = array('litpic' => $dir.$pic['saveName'] , 'sort' => $sort[$key] , 'linkurl' => $url[$key] , 'title' => $title[$key]);
				}
			}

			$rs='';
			if(!empty($data)){
				$rs = XgameModel::saveBanner($data);
			}
		}
		return $this->redirect('xgame/forum/banner-list');	
	}
	
	/**
	 * 修改banner显示界面
	 */
	public function getEditBanner($ids) {
		$ids = explode(',' , $ids);
		$data = XgameService::getBanner($ids);
		return $this->display('xgame-banner-edit',$data);
	}
	
	/**
	 * 修改banner
	 * @param unknown $gid
	 */
	public function postEditBanner() {
		if($_FILES['uploadinput']['name'] <> ""){
			//设置文件上传目录
			$dir = '/userdirs/xgamebanner/' . date('Y') . '/' . date('m') . '/';
			$savePath = storage_path() . $dir;
			//创建目录
			MakeDir::makeDirectory($savePath);
			
			//允许的文件类型
			$fileFormat = array('gif','jpg','jpge','png');
			//文件大小限制，单位: Byte，1KB = 1000 Byte
			//0 表示无限制，但受php.ini中upload_max_filesize设置影响
			$maxSize = 0;
			//覆盖原有文件吗？ 0 不允许  1 允许
			$overwrite = 0;
			//初始化上传类
			$f = new Upload( $savePath, $fileFormat, $maxSize, $overwrite);
			//如果想生成缩略图，则调用成员函数 $f->setThumb();
			//参数列表: setThumb($thumb, $thumbWidth = 0,$thumbHeight = 0)
			//$thumb=1 表示要生成缩略图，不调用时，其值为 0
			//$thumbWidth  缩略图宽，单位是像素(px)，留空则使用默认值 130
			//$thumbHeight 缩略图高，单位是像素(px)，留空则使用默认值 130
			//$f->setThumb(1);
		
			//参数中的uploadinput是表单中上传文件输入框input的名字
			//后面的0表示不更改文件名，若为1，则由系统生成随机文件名

			if (!$f->run('uploadinput',1)){
				//通过$f->errmsg()只能得到最后一个出错的信息，
				//详细的信息在$f->getInfo()中可以得到。
				echo $f->errmsg()."<br>\n";
			}
			//上传结果保存在数组returnArray中。
			
			$all = Input::all();
			$data = array();
			$dataimg = $f->getInfo();
			$id =  $all['id'];
			$sort = $all['sort'];
			$url = $all['url'];
			$title = $all['title'];
			$uploadinputold = $all['uploadinputold'];
			$i = 0;
			foreach($dataimg as $key => $pic){
				$data = array('id' => $id[$key] , 'sort' => $sort[$key] , 'linkurl' => $url[$key] , 'title' => $title[$key]);
				if(!isset($pic['error'])){
					$data['litpic'] = $dir.$pic['saveName'];
				}
				$rs = XgameModel::saveBanner($data);
				if($rs > 0){
					$fall_path = storage_path() . str_replace(Config::get('app.img_url'), '', $uploadinputold[$key]);
					if(file_exists($fall_path) && is_file($fall_path)){
						unlink($fall_path);
					}
					$i++;
				}
			}
		}
		return $this->redirect('xgame/forum/banner-list');	
	}
	
	/**
	 * banner图删除
	 */
	public function getDelBanner($id) {
		//查询banner图
		$ids = array($id);
		$data = XgameService::getBanner($ids);
		$fall_path = storage_path() . $data['result'][0]['litpic'];
		if(file_exists($fall_path) && is_file($fall_path)){
			unlink($fall_path);
		}
		$result = XgameModel::delXgameinfopic($ids);
		return $this->redirect('xgame/forum/banner-list');
	}
	
	/**
	 * 多张banner图删除
	 */
	public function postDelBanner() {
		//查询banner图
		$all = Input::all();
		
		//$id = (int)$id; 
		$ids = $all['ids'];
		$data = XgameService::getBanner($ids);
		foreach ($data['result'] as $item){
			$fall_path = storage_path() . $item['litpic'];
			if(file_exists($fall_path) && is_file($fall_path)){
				unlink($fall_path);
			}
		}
		
		$result = XgameModel::delXgameinfopic($ids);
		if($result){
			return $this->json(array('status'=>200,'global_tips'=>'删除成功'));
		}else{
			return $this->json(array('status'=>600,'global_tips'=>'删除失败'));
		}
	}
	
	
	public function getAddPic($gid) {
		//查询
		$data['gid'] = $gid;
		return $this->display('xgame-add-pic',$data);
	}
	
	public function getEditPic($gid) {
		//查询现有图片
		$data['gid'] = $gid;
		return $this->display('xgame-add-pic',$data);
	}
	public function postSave() {
		
		
		
		//验证标题和缩略图
		$parameter['gamename'] = Input::get('gamename');
		$parameter['phrase'] = Input::get('phrase');
		$parameter['introduced'] = Input::get('introduced');
		$parameter['instructions'] = Input::get('instructions');
		
		
		//$parameter['litpic'] = Input::hasFile('litpic') ? Input::file('litpic') : '';
		
		$dir = '/userdirs/xgame/' . date('Y') . '/' . date('m') . '/';
		$path = storage_path() . $dir;
		if(Input::hasFile('litpic')){
			$file = Input::file('litpic');
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();
			$file->move($path,$new_filename . '.' . $mime );
			$parameter['litpic'] = $dir . $new_filename . '.' . $mime;
		}
		$parameter['gameaddress'] = Input::get('gameaddress');
		
		//验证规则
		$validator['gamename'] = $validator['phrase'] = $validator['introduced'] = $validator['instructions'] = 'required';
		$validator['litpic'] = 'image';
		$validator['gameaddress'] = 'url';

		
		//错误信息返回
		$errmessage['required'] = '不能为空';
		$errmessage['image'] = '缩略图格式不正确';
		$errmessage['url'] = '网址格式不正确';
		//验证
		$validator = Validator::make($parameter, $validator, $errmessage);
		if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
		}
		
		$id = Input::get('id');
		if(!empty($id)){
			$parameter['id'] = $id;
		}
		$parameter['tid'] = Input::get('tid');
		$editorrecommend = Input::get('editorrecommend',0);
		$parameter['editorrecommend'] = ($editorrecommend=='on')? 1 : 0 ;
		$hot = Input::get('hot',0);
		$parameter['hot'] = ($hot=='on')? 1 : 0 ;
		$parameter['editorsort'] = Input::get('editorsort');
		$parameter['hotsort'] = Input::get('hotsort');
		$parameter['newsort'] = Input::get('newsort');

		$id = XgameModel::save($parameter);

		//判断修改还是添加
		if($id>0 && empty($parameter['id'])){
			//添加进入下一步
			return $this->redirect('xgame/forum/add-pic/'.$id);
		}else{
			//修改 进入下一步
			return $this->redirect('xgame/forum/edit-pic/'.$parameter['id']);
		}
	}
	/**
	 * 删除图片
	 */

	public function anyXgameDelPic($path){
		if(!$path) return 0;
		//判断$path 是否带有Http
		$path = str_replace(Config::get('app.img_url'), '', base64_decode($path));
		$re = preg_match("/(http:)/",$path);
		if($re) return Response::json(2);
		//print_r($path);
		//$fall_path = storage_path().base64_decode($path);
		$fall_path = storage_path().$path;
		if(XgameModel::delXgamePic($path)){
			if(file_exists($fall_path) && is_file($fall_path)){
				unlink($fall_path);
			}
		}else{
			return Response::json(0);
		}
	}
	
	function getXgamePic() {
		$gid = Input::get('gid');
		$files = array('files');
		//获取该游戏的litpic
		$litpics = XgameService::getPic($gid);
		if($litpics){
			foreach ($litpics as $pic){
				$spilit = explode('/', $pic['url']);
				$files['files'][] = array(
						'name'=>end($spilit),
						//'url'=>Config::get('ueditor.imageUrlPrefix').$pic['url'],
						'url'=>$pic['url'],
						'deleteUrl'=>'/xgame/forum/xgame-del-pic/'.base64_encode($pic['url']),
						'deleteType'=>'GET'
				);
			}
		}
		return Response:: json($files);
	}
	
	function postXgamePic() {
		$gid = Input::get('gid');
	
		$file = current(Input::file('files'));
		$path = '/userdirs/xgamepic/' . date('Y') . date('m') . '/';
		$filename = date('YmdHis').str_random(4);
		$del_url = '/xgame/forum/xgame-del-pic/';
		$suffix = $file->guessClientExtension();
		$upload_handler = Helpers::jqueryFileUpload($path, $file, $filename, $suffix,$del_url);
		if($upload_handler){
			$add_data = array('gid'=>$gid,'url'=>$path.$filename.'.'.$suffix);
			XgameModel::addXgamePic($add_data);
		}else{
			return Response:: json(0);
		}
	}
	
	function anyXgameCount(){
		$input = Input::all();
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		empty($input['gid']) ? : $data['gid'] = $input['gid'];
		empty($input['begin']) ? : $data['begin'] = strtotime($input['begin']);
		empty($input['after']) ? : $data['after'] = strtotime($input['after'])+3600*24-1;
		empty($input['type']) || $input['type'] == 'all'  ? : $data['type'] = $input['type'];
		empty($input['flag']) || $input['flag'] == 'all'  ? : $data['flag'] = $input['flag'];
		$data['page'] = $page;
		$data['pagesize'] = $pagesize;
		$result = XgameService::getXgameCountList($data);
		$cond['gid'] = empty($data['gid'])?'':$data['gid'];
		$cond['begin'] = empty($input['begin'])?'':$input['begin'];
		$cond['after'] = empty($input['after'])?'':$input['after'];
		$cond['type'] = empty($input['type']) || $input['type'] == 'all' ? '':$input['type'];
		$cond['flag'] = empty($input['flag']) || $input['flag'] == 'all' ? '':$input['flag'];
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($cond);
		$cond['keytypes'] = array('all'=>'全部' , 'web' => 'web' , 'h5' => 'H5');
		$cond['flags'] = array('all'=>'全部' , 'mapping' => '首页&列表' , 'nextList' => '列表下一页','detail'=>'详情页&开始玩游戏');
		$data['cond'] = $cond;
		empty($input['begin']) ? : $data['begin'] = $input['begin'];
		empty($input['after']) ? : $data['after'] = $input['after'];
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['result'] = $result['result'];
		$data['totalSum'] = $result['totalSum'];
		return $this->display('xgame-count',$data);
	}
	
	
	
}