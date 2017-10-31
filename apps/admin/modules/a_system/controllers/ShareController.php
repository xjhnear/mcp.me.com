<?php
namespace modules\a_system\controllers;

use modules\system\models\SystemSettingModel;

use modules\forum\models\TopicModel;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\V4\Common\Model\ShareTpl;
use Youxiduo\V4\Common\ShareService;

class ShareController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_system';
	}
	
	public function getIndex()
	{		
		$data = array();
		$search['platform'] = 'android';
		$result = ShareService::searchTpl($search);
		foreach($result as $key=>$row){
			$row['content'] = json_decode($row['content'],true);
			$result[$key] = $row;
		}
		$data['datalist'] = $result;
		return $this->display('share-tpl-list',$data);
	}
	
	public function getAddTpl($id=0)
	{
		$data = array();
		if($id){
			$tpl = ShareService::getTplInfoById($id);
			if($tpl){
				$tpl['content'] = json_decode($tpl['content'],true);
				$data['tpl'] = $tpl;
				$varlist = !empty($tpl['var_json']) ? json_decode($tpl['var_json'],true) : array();
		        $data['varlist'] = $varlist;
			}else{
				return $this->back('模板不存在');
			}
		}
		return $this->display('share-tpl-info',$data);
	}
	
	public function postAddTpl()
	{
		$input = Input::only('id','title','name','ename','weixin','weibo','var_json');
		
		$data = array(
		    'id'=>$input['id'],
		    'title'=>$input['title'],
		    'name'=>$input['name'],
		    'ename'=>$input['ename'],
		    'content'=>json_encode(array('weixin'=>$input['weixin'],'weibo'=>$input['weibo'])),
		    'var_json'=>$input['var_json'],
		    'platform'=>'android'
		);
	    $ename = $data['ename'];
		$exists = ShareService::getTplInfoByEname($ename);
		if($exists){			
			unset($data['ename']);
			unset($data['id']);
			$success = ShareService::updateTplInfo($ename, $data);
		}else{
			unset($data['id']);
			$success = ShareService::addTplInfo($data);
		}
		if($success){
			return $this->redirect('a_system/share/index','保存成功');
		}
		return $this->back('保存失败');
	}
	
	/**
	 * 分享广告信息列表
	 */
	public function getAdvList($tpl_ename='')
	{
		$tpl_ename = Input::get('tpl_ename',$tpl_ename);
		$target_id = Input::get('target_id');
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$data = array();
		$data['tpl_ename'] = $tpl_ename;	
		$data['target_id'] = $target_id;	
		$search = array();
		$search['platform'] = 'android';
		$search['tpl_ename'] = $tpl_ename;
		$search['target_id'] = $target_id;
		$result = ShareService::searchAdv($search);
		$pager = Paginator::make(array(),$result['totalCount'],$pageSize);
		$pager->appends($search);
		$data['datalist'] = $result['result'];
		return $this->display('share-adv-list',$data);
	}
	
	/**
	 * 编辑分享广告信息
	 */
    public function getAdvEdit($id=0)
	{
		$data = array();
		if($id){
			$data['adv'] = ShareService::getAdvInfoById($id);
		}else{
			$tpl_ename = Input::get('tpl_ename');
			$target_id = Input::get('target_id');
			$data['tpl_ename'] = $tpl_ename;
			$data['target_id'] = $target_id;
			$data['adv'] = array('is_show'=>1);
		}
		$data['tpls'] = ShareService::getTplToKV('ios');
		return $this->display('share-adv-info',$data);
	}
	
	/**
	 * 保存分享广告信息
	 */
	public function postAdvEdit()
	{
		$input = Input::only('id','platform','tpl_ename','target_id','target_title','title','weixin','weibo','redirect_url','start_time','end_time','is_show');				
		//数据验证
		$rule = array();
		
		$input['is_show'] = (int)$input['is_show'];
		//
	    if($input['start_time']){
			$input['start_time'] = strtotime($input['start_time']);
		}
	    if($input['end_time']){
			$input['end_time'] = strtotime($input['end_time']);
		}
		$tpl = ShareService::getTplInfoByEname($input['tpl_ename']);
		if(!$tpl) return $this->back('类别不存在');
		$input['tpl_title'] = $tpl['name'];
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    
	    if(Input::hasFile('icon')){
	    	
			$file = Input::file('icon'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['icon'] = $dir . $new_filename . '.' . $mime;
		}
		
		$success = ShareService::saveAdvInfo($input);
		if($success){
			return $this->redirect('a_system/share/adv-list/'.$input['tpl_ename'].'?target_id='.$input['target_id'],'数据保存成功');
		}else{
			return $this->back('数据保存失败');
		}
	}
	
	/**
	 * 删除分享广告
	 */
	public function getAdvDelete($id)
	{
		ShareService::deleteAdvInfo($id);
		return $this->back('数据删除成功');
	}
}