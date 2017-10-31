<?php
namespace modules\wcms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Yxd\Modules\System\SettingService;
use Youxiduo\Helper\Utility;
use Youxiduo\Cms\WebGameService;

use modules\wcms\models\Article;
use modules\wcms\models\Picture;
use modules\wcms\models\Video;
use modules\wcms\models\HomeSetting;

class HomeController extends BackendController
{
	/**
	 * 首屏背投轮播图
	 */
	private $idx_one_options = array();
	/**
	 * 首屏轮播图
	 */
	private $idx_two_options = array();
	/**
	 * 六个图推
	 */
	private $idx_third_options = array();
	/**
	 * 二屏轮播图
	 */
	private $idx_fourth_options = array();
	/**
	 * 福利活动
	 */
	private $idx_fifth_options = array();
	/**
	 * 热门礼包
	 */
	private $idx_sixth_options = array('1'=>'位置1','2'=>'位置2');
	/**
	 * 视频推荐
	 */
	private $idx_seventh_options = array('1'=>'位置1');
	/**
	 * 原创栏目
	 */
	private $idx_eighth_options = array('1'=>'位置1','2'=>'位置2','3'=>'位置3','4'=>'位置4');
	/**
	 * 产业推荐
	 */
	private $idx_ninth_options = array('1'=>'位置1');
	/**
	 * 产业栏目
	 */
	private $idx_tenth_options = array('1'=>'位置1','2'=>'位置2');
	
	private $idx_game_options = array();
	private $idx_goods_options = array();
	private $idx_activity_options = array();
	private $idx_special_options = array('1'=>'位置1','2'=>'位置2','3'=>'位置3','4'=>'位置4');
	
	private $idx_guide_options = array();
	private $idx_await_options = array();
	
	public function _initialize()
	{
		$this->current_module = 'wcms';
	    for($i=1;$i<=5;$i++){
			$this->idx_one_options[$i] = '位置'.$i;
		}
		for($i=1;$i<=5;$i++){
			$this->idx_two_options[$i] = '位置'.$i;
			$this->idx_activity_options[$i] = '位置'.$i;

		}
		for($i=1;$i<=6;$i++){
			$this->idx_third_options[$i] = '位置'.$i;
			$this->idx_await_options[$i] = '位置'.$i;
			$this->idx_fifth_options[$i] = '位置'.$i;
		}
		for($i=1;$i<=3;$i++){
			$this->idx_fourth_options[$i] = '位置'.$i;
		}
		for($i=1;$i<=18;$i++){
			$this->idx_game_options[$i] = '位置'.$i;
		}
	    for($i=1;$i<=5;$i++){
			$this->idx_goods_options[$i] = '位置'.$i;
		}
		
	    for($i=1;$i<=10;$i++){
			$this->idx_guide_options[$i] = '位置'.$i;
		}
		
	}

	public function getShan()
	{
		$data = array();
		$setting = HomeSetting::GetIndexShanPing();
		if($setting) {
			$data['setting'] = $setting;
		}else{
			$data['setting'] = array('type'=>2,'autoClose'=>true,'onOff'=>'on','closeTime'=>0);
		}
		return $this->display('home-shan',$data);
	}

	public function postShan()
	{
		$type = Input::get('type',2);
		$flashUrl = Input::get('flashUrl');
		$skipUrl = Input::get('skipUrl');
		$autoClose = Input::get('autoClose');
		$closeTime = Input::get('closeTime');
		$onOff = Input::get('onOff');

		$dir = '/userdirs/flash/';
		$path = storage_path() . $dir;
		//列表图
		if(Input::hasFile('filedata')){

			$file = Input::file('filedata');
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();
			$file->move($path,$new_filename . '.' . $mime );
			$flashUrl = 'flash/' . $new_filename;
		}

		$result = HomeSetting::SaveIndexShanPing($type,$flashUrl,$skipUrl,$autoClose,$onOff,$closeTime);
		if($result){
			return $this->redirect('wcms/home/shan');
		}
		return $this->back('保存失败');
	}

	/**
	 * 推荐游戏
	 */
	public function getGameList($default='hot')
	{
		$data = array();	
		$datalist = array();
		$type = Input::get('type',$default);
		$res = HomeSetting::GetIndexTopRecommend();
		$res = isset($res[$type]) ? $res[$type] : array();
		$gids = array();
		foreach($res as $row){
			$gids[] = $row['gameId'];
		}
		$_games = WebGameService::getGameInfoList($gids);
		$games = array();
		foreach($_games as $row){
			$games[$row['web_game_id']] = $row;
		}
		foreach($res as $row){
			$game = $row;
			if(isset($games[$row['gameId']])){
				$game = array_merge($game,$games[$row['gameId']]);
			}
			$datalist[] = $game;
		}
		$data['datalist'] = $datalist;	
		$data['type'] = $type;
		return $this->display('home-game-list',$data);		
	}

	public function postGameSort()
	{
		$type = Input::get('type');
		//$idx_1 = Input::get('idx_1');
		$input = Input::all();
		$old_idxs = array();
		$new_idxs = array();
		foreach($input as $key=>$val){
			if(strpos($key,'idx_')===0){
				$old_idxs[] = str_replace('idx_','',$key);
				$new_idxs[] = $val;
			}
		}
		$new_idxs = array_unique($new_idxs);
		if(count($old_idxs)!=count($new_idxs)) return $this->back('新位置有重复值');
		$update_idx = array();
		foreach($old_idxs as $key=>$idx){
			if($idx==$new_idxs[$key]) continue;
			$update_idx[$idx] = $new_idxs[$key];
		}
		$result = HomeSetting::SaveIndexTopRecommendSort($update_idx,$type);
		if($result==true){
			return $this->redirect('wcms/home/game-list','更新成功');
		}else{
			return $this->back('更新失败');
		}
	}

	public function getRecoveryGame($type)
	{
		$result = HomeSetting::RecoveryIndexTopRecommend($type);
		if($result===0) return $this->back('没有可恢复的数据');
		if($result==true){
			return $this->redirect('wcms/home/game-list','恢复成功');
		}else{
			return $this->back('恢复失败');
		}
	}
	
	/**
	 * 添加推荐游戏
	 */
	public function getAddGame($type='hot')
	{
		$data = array();
		$data['idx_options'] = $this->idx_game_options;
		$data['setting'] = array('type'=>$type);
		return $this->display('home-game-edit',$data);
	}
	
	/**
	 * 修改推荐游戏
	 */
	public function getEditGame($type='hot')
	{
		$idx = Input::get('idx');
		$type = Input::get('type',$type);
		$data = array();
		if($idx){
			$res = HomeSetting::GetIndexTopRecommend();
			$res = isset($res[$type]) ? $res[$type] : array();
			foreach($res as $row){
				if($row['idx']==$idx){
					$setting = $row;
					$setting['type'] = $type;
					$data['setting'] = $setting;
					break;
				}
			}
		}
		$data['idx_options'] = $this->idx_game_options;
		return $this->display('home-game-edit',$data);
	}
	
	public function postEditGame()
	{
		$idx = Input::get('idx');
		$gameId = Input::get('gameId');
		$picUrl = Input::get('picUrl');
		$type = Input::get('type');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		
		$result = HomeSetting::SaveIndexTopRecommend($gameId,$picUrl,$type,$idx);
		
	    if($result==true){
			return $this->redirect('wcms/home/game-list/'.$type,'设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	/**
	 * 轮播图
	 */
	public function getSlideList($position)
	{
		
		
		$data = array();		
		$data['position'] = $position;
		$data['nav_bar_title'] = $this->slide_title($position);
		if($position=='one'){
		    $data['datalist'] = HomeSetting::GetIndexFirstProjection();
		}
	    if($position=='two'){
		    $data['datalist'] = HomeSetting::GetIndexDuoFocus();
		}
	    if($position=='third'){
		    $data['datalist'] = HomeSetting::GetIndexDuoPicRecommend();
		}
		
		if($position=='fourth'){
		    $data['datalist'] = HomeSetting::GetIndexSecondProjectionBroadcast();
		}

		if($position == 'fifth'){
			$data['datalist'] = HomeSetting::GetIndexGameTestRecommend();
		}
		
		if($position == 'seventh'){
			$data['datalist'] = HomeSetting::GetIndexVideoRecommend();
		}
		
		if($position == 'eighth'){
			$data['datalist'] = HomeSetting::GetIndexOriginalSection();
		}
		
		if($position == 'ninth'){
			$data['datalist'] = HomeSetting::GetIndexIndustryRecommend();
		}
		
		if($position == 'tenth'){
			$data['datalist'] = HomeSetting::GetIndexIndustrySection();
		}
		
		return $this->display('home-slide-list',$data);	
	}
	
	private function slide_title($position)
	{
		$titles = array(
			'one'=>'首屏背投轮播图',
			'two'=>'首屏轮播图',
			'third'=>'首屏六个图推',
			'fourth'=>'二屏轮播图',
			'fifth'=>'游戏评测推荐',
			//'sixth'=>'热门礼包',
			'seventh'=>'推荐视频',
			'eighth'=>'原创栏目',
			'ninth'=>'产业推荐',
			'tenth'=>'产业栏目'
		);
		
		return $titles[$position];
	}
	
	/**
	 * 获取位置列表
	 */
	private function idx_options($keyname)
	{
		if($keyname=='one') return $this->idx_one_options;
		if($keyname=='two') return $this->idx_two_options;
		if($keyname=='third') return $this->idx_third_options;
		if($keyname=='fourth') return $this->idx_fourth_options;
		if($keyname=='fifth') return $this->idx_fifth_options;
		if($keyname=='seventh') return $this->idx_seventh_options;
		if($keyname=='eighth') return $this->idx_eighth_options;
		if($keyname=='ninth') return $this->idx_ninth_options;
		if($keyname=='tenth') return $this->idx_tenth_options;
	}
	
	/**
	 * 添加轮播图
	 * @param string $position
	 * @return string
	 */
	public function getAddSlide($position)
	{
		$data = array();		
		$data['position'] = $position;
		$data['nav_bar_title'] = $this->slide_title($position);
		$data['idx_options'] = $this->idx_options($position);		
		$data['setting'] = array('status'=>1);
		return $this->display('home-slide-edit',$data);
	}

	/**
	 * @param string $position
	 * @return mixed
	 */
    public function getSlideEdit($position='one')
	{
		$idx = Input::get('idx');
		$data = array();		
		$data['position'] = $position;
		$data['nav_bar_title'] = $this->slide_title($position);
		$data['idx_options'] = $this->idx_options($position);	
		if($position=='one'){
			$result = HomeSetting::GetIndexFirstProjection();
		}
		if($position=='two'){
			$result = HomeSetting::GetIndexDuoFocus();
		}
		if($position=='third'){
			$result = HomeSetting::GetIndexDuoPicRecommend();
		}
		
		if($position=='fourth'){
		    $result = HomeSetting::GetIndexSecondProjectionBroadcast();
		}

		if($position=='fifth'){
			$result = HomeSetting::getIndexGameTestRecommend();
		}
		
		if($position == 'seventh'){
			$result = HomeSetting::GetIndexVideoRecommend();
		}
		
		if($position == 'eighth'){
			$result = HomeSetting::GetIndexOriginalSection();
		}
		
		if($position == 'ninth'){
			$result = HomeSetting::GetIndexIndustryRecommend();
		}
		
		if($position == 'tenth'){
			$result = HomeSetting::GetIndexIndustrySection();
		}
		$setting = array('status'=>1);
		foreach($result as $row){
			if($row['idx']==$idx){
				$setting = $row;
				$setting['status'] = !empty($row['articleId']) ? 1 : 2;
				break;
			}
		}
		$data['setting'] = $setting;
		return $this->display('home-slide-edit',$data);
	}
	
	/**
	 * 保存轮播图
	 */
	public function postSlideEdit()
	{
		$position = Input::get('position');
		$idx = Input::get('idx');
		$type = Input::get('type');
		$picUrl = Input::get('picUrl');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}


		$articleId = $type==1 ? Input::get('articleId') : null;		
		$title = Input::get('title');
		$summary = Input::get('summary');
		$gameId = Input::get('gameId');
		$url = Input::get('url');
		$containVideo = (int)Input::get('containVideo',0);
		$autoPlay = (int)Input::get('autoPlay',0);
		$videoUrl = Input::get('videoUrl');
		$videoTime = Input::get('videoTime');

		//视频
		if(Input::hasFile('filevideo')){
			$dir = '/userdirs/flashflv/';
			$path = storage_path() . $dir;
			$file = Input::file('filevideo');
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();
			$file->move($path,$new_filename . '.' . $mime );
			$videoUrl = '/flashflv/' . $new_filename;
			//$videoUrl = Utility::getImageUrl($videoUrl);
		}
		
		$input = array();
		$rules = array();
		$prompts = array();
		
		if($position=='one'){
			$result = HomeSetting::SaveIndexFirstProjection($articleId,$picUrl,$idx,$title,$summary,$gameId,$url,$containVideo,$videoUrl,$videoTime,$autoPlay);
		}
		
		if($position=='two'){
			$result = HomeSetting::SaveIndexDuoFocus($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}
		
		if($position=='third'){
			$result = HomeSetting::SaveIndexDuoPicRecommend($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}
		
	    if($position=='fourth'){
			$result = HomeSetting::SaveIndexSecondProjectionBroadcast($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}

		if($position=='fifth'){
			$result = HomeSetting::SaveIndexGameTestRecommend($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}
		
		if($position=='seventh'){
			$result = HomeSetting::SaveIndexVideoRecommend($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}

		if($position=='eighth'){
			$result = HomeSetting::SaveIndexOriginalSection($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}
		
		if($position=='ninth'){
			$result = HomeSetting::SaveIndexIndustryRecommend($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}
		
		if($position=='tenth'){
			$result = HomeSetting::SaveIndexIndustrySection($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
		}				
		
	    if($result==true){
			return $this->redirect('wcms/home/slide-list/'.$position,'设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
		
	}

	public function postSlideSort()
	{
		$position = Input::get('position');
		$input = Input::all();
		$old_idxs = array();
		$new_idxs = array();
		foreach($input as $key=>$val){
			if(strpos($key,'idx_')===0){
				$old_idxs[] = str_replace('idx_','',$key);
				$new_idxs[] = $val;
			}
		}
		$new_idxs = array_unique($new_idxs);
		if(count($old_idxs)!=count($new_idxs)) return $this->back('新位置有重复值');
		$update_idx = array();
		foreach($old_idxs as $key=>$idx){
			if($idx==$new_idxs[$key]) continue;
			$update_idx[$idx] = $new_idxs[$key];
		}
		$result = false;
		if($position=='third'){
			$result = HomeSetting::SaveIndexDuoPicRecommendSort($update_idx);
		}
		if($result==true){
			return $this->redirect('wcms/home/slide-list/'.$position,'更新成功');
		}else{
			return $this->back('更新失败');
		}
	}

	public function getRecoverySlide($position)
	{
		$result = 0;
		if($position=='third'){
			$result = HomeSetting::RecoveryIndexDuoPicRecommend();
		}

		if($result===0) return $this->back('没有可恢复的数据');
		if($result==true){
			return $this->redirect('wcms/home/slide-list/'.$position,'恢复成功');
		}else{
			return $this->back('恢复失败');
		}
	}

	public function getSlideDelete($position)
	{
		$idx = Input::get('idx');
		$position = Input::get('position',$position);
		$result = false;
		if($position=='one') {
			$result = HomeSetting::DeleteIndexFirstProjection($idx);
		}
		if($result==true){
			return $this->redirect('wcms/home/slide-list/'.$position,'删除成功');
		}else{
			return $this->back('删除失败');
		}
	}
	
	/**
	 * 多头条
	 */
	public function getToplineList()
	{
		$data = array();	
		$data['datalist'] = HomeSetting::GetIndexDuoTopic();	
		return $this->display('home-topline-list',$data);	
	}
	
	public function getAddTopline()
	{
		$data = array();
		
		return $this->display('home-topline-edit',$data);
	}
	
	public function postAddTopline()
	{
		$type = Input::get('type');
		$picUrl = Input::get('picUrl');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		$articleId = $type==1 ? Input::get('articleId') : null;		
		$title = Input::get('title');
		$summary = Input::get('summary');
		$up = Input::get('up');
		$url = Input::get('url');
		
		$result = HomeSetting::SaveIndexDuoTopic($articleId,$picUrl,$up,$title,$summary,$url);
	    if($result==true){
			return $this->redirect('wcms/home/topline-list/','设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	public function getToplineDelete()
	{
		$idx = Input::get('idx');
		$result = HomeSetting::DeleteIndexDuoTopic($idx);
		return $this->back('删除成功');
	}
	
	/**
	 * 背景图
	 */
	public function getBgimage($position)
	{
		$data = array();		
		if($position=='two'){//二屏背景图
			$data['setting'] = HomeSetting::GetIndexSecondProjectionBg();
			$data['nav_bar_title'] = '二屏背景图';
		}
		
		if($position=='third'){//三屏背景图
			//$data['setting'] = HomeSetting::GetIndexSecondProjectionBg();
			$data['nav_bar_title'] = '三屏背景图';
		}
		
		if($position=='fourth'){//新游期待背景图
			$data['setting'] = HomeSetting::GetIndexNewGameExpectBg();
			$data['nav_bar_title'] = '新游期待背景图';
		}
		
		if($position=='fifth'){//四屏背景图
			//$data['setting'] = HomeSetting::GetIndexSecondProjectionBg();
			$data['nav_bar_title'] = '四屏背景图';
		}
		
		$data['position'] = $position;
		return $this->display('home-bgimage',$data);	
	}
	
	/**
	 * 保存背景图
	 */
	public function postBgimage()
	{
		$picUrl = Input::get('picUrl');
		$position = Input::get('position');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		if($position=='two'){//二屏背景图
		    $result = HomeSetting::SaveIndexSecondProjectionBg($picUrl);
		}
		
		if($position=='third'){//二屏背景图
		    //$result = HomeSetting::SaveIndexSecondProjectionBg($picUrl);
		}
		
		if($position=='fourth'){//二屏背景图
		    $result = HomeSetting::SaveIndexNewGameExpectBg($picUrl);
		}
		
		if($position=='fifth'){//二屏背景图
		    //$result = HomeSetting::SaveIndexSecondProjectionBg($picUrl);
		}
		
	    if($result==true){
			return $this->redirect('wcms/home/bgimage/'.$position,'设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	public function getGametagList()
	{
		$data = array();
		$data['datalist'] = HomeSetting::GetIndexSecondProjectionArticleSetting();		
		$data['nav_bar_title'] = '二屏游戏文章Tag';
		return $this->display('home-gametag-list',$data);
	}
	
	public function getGametagEdit()
	{
		$idx = Input::get('idx');
		if($idx){
		    $datalist = HomeSetting::GetIndexSecondProjectionArticleSetting();
		    foreach($datalist as $row){
		    	if($row['idx']==$idx){
		    		$setting = $row;
		    		$data['setting'] = $setting;
		    		break;
		    	}
		    }
		}
		$data['idx_options'] = array('1'=>'位置1','2'=>'位置2','3'=>'位置3','4'=>'位置4');
		$data['nav_bar_title'] = '二屏游戏文章Tag';
		return $this->display('home-gametag-edit',$data);
	}
	
	public function postGametagEdit()
	{
		$idx = Input::get('idx');
		$gameId = Input::get('gameId');
		$title = Input::get('title');
		$summary = Input::get('summary');
		$tag = Input::get('tag');
		
		$result = HomeSetting::SaveIndexSecondProjectionArticleSetting($title,$summary,$gameId,$tag,$idx);
		if($result==true){
			return $this->redirect('wcms/home/gametag-list','保存成功');
		}else{
			return $this->back('保存失败');
		}
	}
	
	/**
	 * 热门商品
	 */
	public function getGoodsList()
	{
		$data = array();	
		$data['datalist'] = HomeSetting::GetMerchantRecommend();
		return $this->display('home-goods-list',$data);	
	}
	
	/**
	 * 添加推荐商品
	 */
	public function getAddGoods()
	{
		$data = array();
		$data['idx_options'] = $this->idx_goods_options;
		return $this->display('home-goods-edit',$data);
	}
	
	/**
	 * 修改推荐商品
	 */
	public function getEditGoods()
	{
		$idx = Input::get('idx');
		$data = array();
		if($idx){
			 $datalist = HomeSetting::GetMerchantRecommend();
			 foreach($datalist as $row){
			 	if($idx==$row['idx']){
			 		$setting = $row;
			 		$data['setting'] = $setting;
			 		break;
			 	}
			 }
		}
		$data['idx_options'] = $this->idx_goods_options;
		return $this->display('home-goods-edit',$data);
	}
	
	/**
	 * 保存推荐商品
	 */
	public function postEditGoods()
	{
		$idx = Input::get('idx');
		$picUrl = Input::get('picUrl');
		$title = Input::get('title');
		$url = Input::get('url');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		$input = array('idx'=>$idx,'picUrl'=>$picUrl,'url'=>$url);
		$rules = array('idx'=>'required','picUrl'=>'required','url'=>'required');
		$tips = array('idx.required'=>'位置不能为空','picUrl.required'=>'图片不能为空','url.required'=>'跳转URL不能为空');
		$valid = Validator::make($input,$rules,$tips);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		
		$result = HomeSetting::SaveMerchantRecommend($idx,$title,$picUrl,$url);
		if($result==true){
			return $this->redirect('wcms/home/goods-list','保存成功');
		}
		return $this->back('保存失败');
	}

	
	
	/**
	 * 福利活动
	 */
	public function getActivityList($position)
	{
		$data = array();	
		if($position=='fifth'){
		    $data['datalist'] = HomeSetting::GetIndexWelfareEvent();
		    $data['nav_bar_title'] = '福利活动';
		}	
		if($position=='sixth'){
			$data['datalist'] = HomeSetting::GetIndexHotGiftBagPicRecommend();
		    $data['nav_bar_title'] = '热门礼包';
		}
		$data['position'] = $position;
		return $this->display('home-activity-list',$data);	
	}
	
	/**
	 * 添加福利活动
	 */
	public function getAddActivity($position)
	{
		$data = array();
		if($position=='fifth'){
			$data['idx_options'] = $this->idx_fifth_options;
			$data['nav_bar_title'] = '福利活动';
		}
		if($position=='sixth'){
			$data['idx_options'] = $this->idx_sixth_options;
			$data['nav_bar_title'] = '热门礼包';
		}
		$data['position'] = $position;
		return $this->display('home-activity-edit',$data);
	}
	
	/**
	 * 修改福利活动
	 */
	public function getEditActivity($position)
	{
		$idx = Input::get('idx');
		$data = array();
		if($idx){
			
			if($position=='fifth'){
				$data['nav_bar_title'] = '福利活动';
				$data['idx_options'] = $this->idx_fifth_options;
				$datalist = HomeSetting::GetIndexWelfareEvent();
				foreach($datalist as $row){
					if($idx==$row['idx']){
						$setting = $row;
						$data['setting'] = $setting;
						break;
					}
				}
			}
			if($position=='sixth'){
				$data['nav_bar_title'] = '热门礼包';
				$data['idx_options'] = $this->idx_sixth_options;
				$datalist = HomeSetting::GetIndexHotGiftBagPicRecommend();
				foreach($datalist as $row){
					if($idx==$row['idx']){
						$setting = $row;
						$data['setting'] = $setting;
						break;
					}
				}
			}
			
		}
		$data['position'] = $position;
		return $this->display('home-activity-edit',$data);
	}
	
	/**
	 * 保存福利活动
	 */
	public function postEditActivity()
	{
		$idx = Input::get('idx');
		$picUrl = Input::get('picUrl');
		$title = Input::get('title');
		$url = Input::get('url');
		$position = Input::get('position');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		$input = array('idx'=>$idx,'picUrl'=>$picUrl,'url'=>$url);
		$rules = array('idx'=>'required','picUrl'=>'required','url'=>'required');
		$tips = array('idx.required'=>'位置不能为空','picUrl.required'=>'图片不能为空','url.required'=>'跳转URL不能为空');
		$valid = Validator::make($input,$rules,$tips);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		if($position=='fifth'){
		    $result = HomeSetting::SaveIndexWelfareEvent($idx,$title,$picUrl,$url);
		}
		if($position=='sixth'){
			$result = HomeSetting::SaveIndexHotGiftBagPicRecommend($idx,$title,$picUrl,$url);
		}
		if($result==true){
			return $this->redirect('wcms/home/activity-list/'.$position,'保存成功');
		}
		return $this->back('保存失败');
	}	
	
	/**
	 * 直播视频
	 */
	public function getLiveCode()
	{
		$data = array();	
		$data['setting'] = HomeSetting::GetIndexThirdProjection();	
		return $this->display('home-live-code',$data);	
	}
	
	public function postLiveCode()
	{
		$bgPicUrl = Input::get('bgPicUrl');
		$picUrl = Input::get('picUrl');
		$liveVideo = Input::get('liveVideo');
		$announce = Input::get('announce');
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //背景图
	    if(Input::hasFile('filedata_bg')){
	    	
			$file = Input::file('filedata_bg'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$bgPicUrl = $dir . $new_filename . '.' . $mime;
			$bgPicUrl = Utility::getImageUrl($bgPicUrl);
		}
		
	    //视频图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		
		$result = HomeSetting::SaveIndexThirdProjection($picUrl,$bgPicUrl,$liveVideo,$announce);
		if($result==true){
			return $this->redirect('wcms/home/live-code','保存成功');
		}
		return $this->back('保存失败');
	}
	
	/**
	 * 四屏设置
	 */
	public function getFourthProjection()
	{
		$data = array();
		$data['setting'] = HomeSetting::GetIndexFourthProjection();		
		return $this->display('home-fourth-projection',$data);	
	}
	
	public function postFourthProjection()
	{
	    $bgPicUrl = Input::get('bgPicUrl');
		$gameId = Input::get('gameId');
		$communityUrl = Input::get('communityUrl');
		$specialUrl = Input::get('specialUrl');
		$officialUrl = Input::get('officialUrl');
		$url = Input::get('url');
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //背景图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$bgPicUrl = $dir . $new_filename . '.' . $mime;
			$bgPicUrl = Utility::getImageUrl($bgPicUrl);
		}
		$result = HomeSetting::SaveIndexFourthProjection($gameId,$bgPicUrl,$communityUrl,$specialUrl,$officialUrl,$url);
		if($result==true){
			return $this->redirect('wcms/home/fourth-projection','保存成功');
		}
		return $this->back('保存失败');
	}
	
	/**
	 * 期待榜
	 */
	public function getAwaitList()
	{
		$data = array();		
		$result = HomeSetting::GetIndexNewGameExpect();
		if(isset($result['list'])){
			$data['datalist'] = $result['list'];
		}
		return $this->display('home-await-list',$data);	
	}
	
	public function getAddAwait()
	{
		$data = array();
		$data['idx_options'] = $this->idx_await_options;
		return $this->display('home-await-edit',$data);
	}
	
	public function getEditAwait()
	{
		$idx = Input::get('idx');
		$data = array();
		if($idx){
			$result = HomeSetting::GetIndexNewGameExpect();
		    if(isset($result['list'])){
				$datalist = $result['list'];
				foreach($datalist as $row){
					if($idx == $row['idx']){
						$data['setting'] = $row;
						break;
					}
				}
		    }
		}		
		
		$data['idx_options'] = $this->idx_await_options;
		return $this->display('home-await-edit',$data);
	}
	
	public function postEditAwait()
	{
	   $idx = Input::get('idx');
		$gameId = Input::get('gameId');
		$title = Input::get('title');	    
		
		$result = HomeSetting::SaveIndexNewGameExpectEntry($idx,$gameId,$title);
		
	    if($result==true){
			return $this->redirect('wcms/home/await-list','设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	/**
	 * 预告
	 */
	public function getAdvance()
	{
		$data = array();	
		$data['setting'] = HomeSetting::GetIndexNewGamePreview();	
		return $this->display('home-advance',$data);	
	}
	
	public function postAdvance()
	{
	    $picUrl = Input::get('picUrl');
		$title = Input::get('title');
		$url = Input::get('url');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		$input = array('title'=>$title,'picUrl'=>$picUrl,'url'=>$url);
		$rules = array('title'=>'required','picUrl'=>'required','url'=>'required');
		$tips = array('title.required'=>'标题不能为空','picUrl.required'=>'图片不能为空','url.required'=>'跳转URL不能为空');
		$valid = Validator::make($input,$rules,$tips);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		$result = HomeSetting::SaveIndexNewGamePreview($picUrl,$title,$url);
	    if($result==true){
			return $this->redirect('wcms/home/advance','设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	/**
	 * 专题
	 */
	public function getSpecialList()
	{
		$data = array();		
		$data['datalist'] = HomeSetting::GetIndexNewGameSpecial();
		return $this->display('home-special-list',$data);	
	}
	
	public function getAddSpecial()
	{
		$data = array();		
		$data['setting'] = array('status'=>1);
		$data['idx_options'] = $this->idx_special_options;
		return $this->display('home-special-edit',$data);	
	}
	
	public function getEditSpecial()
	{
		$idx = Input::get('idx');
		$data = array();
		if($idx){
			$datalist = HomeSetting::GetIndexNewGameSpecial();
			foreach($datalist as $row){
				if($idx == $row['idx']){
					$data['setting'] = $row;
				}
			}
		}
		$data['idx_options'] = $this->idx_special_options;
		return $this->display('home-special-edit',$data);	
	}
	
	public function postEditSpecial()
	{
		$idx = Input::get('idx');
		$type = Input::get('type');
		$picUrlB = Input::get('picUrlB');
		$picUrlS = Input::get('picUrlS');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //图一
	    if(Input::hasFile('filedata_small')){
	    	
			$file = Input::file('filedata_small'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrlS = $dir . $new_filename . '.' . $mime;
			$picUrlS = Utility::getImageUrl($picUrlS);
		}
		//图二
	    if(Input::hasFile('filedata_big')){
	    	
			$file = Input::file('filedata_big'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrlB = $dir . $new_filename . '.' . $mime;
			$picUrlB = Utility::getImageUrl($picUrlB);
		}
		
		$articleId = $type==1 ? Input::get('articleId') : null;		
		$title = Input::get('title');
		$summary = Input::get('summary');
		$gameId = Input::get('gameId');
		$url = Input::get('url');
		
		$result = HomeSetting::SaveIndexNewGameSpecial($picUrlB,$picUrlS,$title,$url,$idx);
	    if($result==true){
			return $this->redirect('wcms/home/special-list/','设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
	
	/**
	 * 视频推荐
	 */
	public function getVideoRecommend()
	{
		$data = array();		
		return $this->display('home-video-recommend',$data);	
	}
	
	/**
	 * 原创栏目
	 */
	public function getVideoOriginalList()
	{
		$data = array();		
		return $this->display('home-video-original-list',$data);	
	}
	
	/**
	 * 产业推荐
	 */
	public function getIndustryRecommend()
	{
		$data = array();		
		return $this->display('home-industry-recommend',$data);	
	}
	
	/**
	 * 产业专栏
	 */
	public function getIndustryList()
	{
		$data = array();		
		return $this->display('home-industry-list',$data);	
	}
	
	/**
	 * 推荐攻略
	 */
	public function getGuideList()
	{
		$data = array();		
		$data['datalist'] = HomeSetting::GetIndexStrategyRecommend();
		return $this->display('home-guide-list',$data);	
	}
	
	public function getAddGuide()
	{
		$data = array();
		$data['idx_options'] = $this->idx_guide_options;
		return $this->display('home-guide-edit',$data);
	}
	
	public function getEditGuide()
	{
		$idx = Input::get('idx');
		$data = array();
		if($idx){
			$datalist = HomeSetting::GetIndexStrategyRecommend();
			foreach($datalist as $row){
				if($idx == $row['idx']){
					$data['setting'] = $row;
					break;
				}
			}
		}
		$data['idx_options'] = $this->idx_guide_options;
		return $this->display('home-guide-edit',$data);
	}
	
	public function postEditGuide()
	{
		$idx = Input::get('idx');
		$picUrl = Input::get('picUrl');
		$articleId = Input::get('articleId');		
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$picUrl = $dir . $new_filename . '.' . $mime;
			$picUrl = Utility::getImageUrl($picUrl);
		}
		
		$result = HomeSetting::SaveIndexStrategyRecommend($idx,$articleId,$picUrl);
		if($result==true){
			return $this->redirect('wcms/home/guide-list','保存成功');
		}
		return $this->back('保存失败');
	}
	
	public function getRankSetting()
	{
		$data = array();
		$config = SettingService::getConfig('www_home_rank');

		if($config){
			$data['result'] = $config['data'];
		}
		return $this->display('home-rank-setting',$data);
	}
	
	public function postRankSetting()
	{
		$ios_ids = Input::get('ios_ids');
		$android_ids = Input::get('android_ids');
		$network_ids = Input::get('network_ids');
		$single_ids = Input::get('single_ids');

		$data = array(
		    'ios_ids'=>$ios_ids,
		    'android_ids'=>$android_ids,
		    'network_ids'=>$network_ids,
		    'single_ids'=>$single_ids
		);
		SettingService::setConfig('www_home_rank',$data);
		return $this->redirect('wcms/home/rank-setting','保存成功');
	}
	
	
    /**
	 * 预告
	 */
	public function getTopAdv()
	{
		$data = array();	
		$data['setting'] = HomeSetting::GetTopAdvertisingPic();	
		return $this->display('home-top-adv',$data);	
	}
	
	public function postTopAdv()
	{
	    $smallPicUrl = Input::get('smallPicUrl');
	    $bigPicTopUrl = Input::get('bigPicTopUrl');
	    $bigPicBottomUrl = Input::get('bigPicBottomUrl');
		$url = Input::get('url');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata_small')){
	    	
			$file = Input::file('filedata_small'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$smallPicUrl = $dir . $new_filename . '.' . $mime;
			$smallPicUrl = Utility::getImageUrl($smallPicUrl);
		}
		
		if(Input::hasFile('filedata_big_top')){
	    	
			$file = Input::file('filedata_big_top'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$bigPicTopUrl = $dir . $new_filename . '.' . $mime;
			$bigPicTopUrl = Utility::getImageUrl($bigPicTopUrl);
		}
		
		if(Input::hasFile('filedata_big_bottom')){
	    	
			$file = Input::file('filedata_big_bottom'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$bigPicBottomUrl = $dir . $new_filename . '.' . $mime;
			$bigPicBottomUrl = Utility::getImageUrl($bigPicBottomUrl);
		}
		$input = array('smallPicUrl'=>$smallPicUrl,'bigPicTopUrl'=>$bigPicTopUrl,'bigPicBottomUrl'=>$bigPicBottomUrl,'url'=>$url);
		$rules = array('smallPicUrl'=>'required','bigPicTopUrl'=>'required','bigPicBottomUrl'=>'required','url'=>'required');
		$tips = array('smallPicUrl.required'=>'小图不能为空','bigPicTopUrl.required'=>'图片不能为空','bigPicBottomUrl.required'=>'图片不能为空','url.required'=>'跳转URL不能为空');
		$valid = Validator::make($input,$rules,$tips);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		$result = HomeSetting::SaveTopAdvertisingPic($smallPicUrl,$bigPicTopUrl,$bigPicBottomUrl,$url);
	    if($result==true){
			return $this->redirect('wcms/home/top-adv','设置保存成功');
		}else{
			return $this->back('设置保存失败');
		}
	}
}