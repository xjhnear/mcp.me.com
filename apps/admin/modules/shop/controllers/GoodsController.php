<?php
namespace modules\shop\controllers;

use modules\shop\models\CateModel;

use Yxd\Modules\Message\PromptService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;

use modules\shop\models\GoodsModel;

class GoodsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'shop';
	}
	
	public function getList()
	{
		$search = array();
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$result = GoodsModel::search($search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['list'];
		return $this->display('goods-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$data['goods'] = array('gtype'=>1);
		$data['catelist'] = CateModel::getKV();
		return $this->display('goods-edit',$data);
	}
	
	public function getEdit($goods_id)
	{
		$goods = GoodsModel::getInfo($goods_id);
		$data['catelist'] = CateModel::getKV();
		$data['goods'] = $goods;
		return $this->display('goods-edit',$data);
	}
	
	public function postSave()
	{
		
		$input = Input::only('day_limit_goods_last','id','cate_id','name','shortname',
		'summary','instruction','starttime',
		'endtime','score','totalnum','max_exchange_times','isrecommend','ishot','isnew','gtype','status','sort');
		$input['gift_id'] = Input::get('gift_id',0);
		$input['expense'] = Input::get('expense','');
		$input['limit_flag'] = Input::get('limit_flag', 0);
		$input['day_limit_goods_total'] = Input::get('day_limit_goods_total', 0);
		$input['limit_time'] = mktime(23,59,59,date('m'),date('d'),date('Y'));
		if(!isset($input['day_limit_goods_last']) || !$input['day_limit_goods_last']){
			$input['day_limit_goods_last'] = Input::get('day_limit_goods_total', 0);
		}

		$input['limit_register_time'] = Input::get('limit_register_time');

		if($input['limit_register_time']){
			$input['limit_register_time'] = strtotime($input['limit_register_time']);
		}else{
			$input['limit_register_time'] = 0;
		}

		//检查该礼包是否已经对应了商品
		if(!isset($input['id']) && !$input['id']){
			$result = GoodsModel::checkShop($input['gift_id']);
			if($result){
				return $this->back()->with('global_tips','商品添加失败,该礼包已经有对应的商品了，请填写其他的礼包id');
			}
		}
		//gtype==2表示添加的是虚拟产品 等于1表示实物产品
		if($input['gtype'] == 2){
			$giftinfo = GoodsModel::getGiftnums($input['gift_id']);
			if(!$giftinfo){
				return $this->back()->with('global_tips','商品添加失败,关联的礼包不存在');
			}
			$input['totalnum'] = $giftinfo['total_num'];
			$input['usednum'] = $giftinfo['total_num'] - $giftinfo['last_num'];
		}
	    if(!isset($input['id'])){
			//$input['usednum'] = $input['totalnum'];
		}
	    if(!empty($input['starttime'])){
			$input['starttime'] = strtotime($input['starttime']);
		}
		
	    if(!empty($input['endtime'])){
			$input['endtime'] = strtotime($input['endtime']);
		}
		
		if(!isset($input['isrecommend'])){
			$input['isrecommend'] = 0;
		}
	    if(!isset($input['ishot'])){
			$input['ishot'] = 0;
		}
		
	    if(!isset($input['isnew'])){
			$input['isnew'] = 0;
		}
		
	    if(!isset($input['status'])){
			$input['status'] = 0;
		}
		
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    
	    //大图
	    if(Input::hasFile('bigpic_1')){	    	
			$file = Input::file('bigpic_1');			
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();		
			$file->move($path,$new_filename . '.' . $mime );
			$input['bigpic_1'] = $dir . $new_filename . '.' . $mime;
		}
	    
		//列表图
	    if(Input::hasFile('listpic')){	    	
			$file = Input::file('listpic');			
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();		
			$file->move($path,$new_filename . '.' . $mime );
			$input['listpic'] = $dir . $new_filename . '.' . $mime;
		}
		
	    $result = GoodsModel::save($input);
		if($result){
			return $this->redirect('shop/goods/list')->with('global_tips','商品添加成功');
		}else{
			return $this->back()->with('global_tips','商品添加失败');
		}
	}
	
	public function getDelete($goods_id)
	{
		GoodsModel::delete($goods_id);
		$this->operationPdoLog('商品删除', $goods_id);
		return $this->redirect('shop/goods/list')->with('global_tips','商品删除成功');
	}
	
	public function getStatus($goods_id,$status)
	{
		GoodsModel::doStatus($goods_id,$status);
		if(intval($status)==1){
			$data = array('goods_id'=>$goods_id);
			PromptService::pushShopToQueue($data);
		}
		return $this->redirect('shop/goods/list')->with('global_tips','商品操作成功');
	}
	
    /**
	 * 快捷操作
	 */
	public function getOperator($id,$type,$val)
	{
		$data = array();
		$data['id'] = $id;
		$data[$type] = $val == 1 ? 0 : 1;
		$result = GoodsModel::save($data);
		if($result){
			return $this->redirect('shop/goods/list')->with('global_tips','操作成功');
		}else{
			return $this->redirect('shop/goods/list')->with('global_tips','操作失败');
		}
	}
	
	public function getCateList()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$result = CateModel::getList($page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		return $this->display('cate-list',$data);
	}
	
	public function getCateEdit($cate_id=0)
	{
		$data = array();
		if($cate_id){
			$data['cate'] = CateModel::getInfo($cate_id);
		}
		return $this->display('cate-edit',$data);
	}
	
	public function postSaveCate()
	{
		$input = Input::only('id','cate_name','summary','sort','istaobao','taobaotype','taobaoid');
		$input['show'] = Input::get('show',1);
	    //列表图
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    if(Input::hasFile('icon')){	   
	    	 	
			$file = Input::file('icon');			
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();		
			$file->move($path,$new_filename . '.' . $mime );
			$input['icon'] = $dir . $new_filename . '.' . $mime;
		}
		
		$result = CateModel::save($input);
	    if($result){
			return $this->redirect('shop/goods/cate-list')->with('global_tips','商品分类添加成功');
		}else{
			return $this->back()->with('global_tips','商品分类添加失败');
		}
	}
	
    public function getRule()
	{
		$data = array();		
		$data['topic'] = GoodsModel::getRuleInfo();
		return $this->display('wish-rule',$data);
	}
	
	public function postSaveRule()
	{
		$tid = (int)Input::get('tid');
		$subject = Input::get('subject');
		$message = Input::get('format_message','');
		$uid = 1;
		$res = GoodsModel::saveRule($tid, $subject, $message, $uid);
		if($res){
			return $this->redirect('shop/goods/rule')->with('global_tips','许愿规则保存成功');
		}else{
			return $this->back()->with('global_tips','许愿规则保存失败');
		}
	}
}