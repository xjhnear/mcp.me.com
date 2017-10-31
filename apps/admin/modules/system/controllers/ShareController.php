<?php
namespace modules\system\controllers;

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
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{		
		$data = array();
		$search['platform'] = 'ios';
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
		$input = Input::only('id','title','ename','weixin','weibo','var_json');
		
		$data = array(
		    'id'=>$input['id'],
		    'title'=>$input['title'],
		    'ename'=>$input['ename'],
		    'content'=>json_encode(array('weixin'=>$input['weixin'],'weibo'=>$input['weibo'])),
		    'var_json'=>$input['var_json'],
		    'platform'=>'ios'
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
			return $this->redirect('system/share/index','保存成功');
		}
		return $this->back('保存失败');
	}
	
	public function getAdvList($tpl_ename)
	{
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$data = array();
		$data['tpl_ename'] = $tpl_ename;
		$search = array();
		$result = ShareService::searchAdv($search);
		$pager = Paginator::make(array(),$result['totalCount'],$pageSize);
		$pager->appends($search);
		$data['datalist'] = $result['result'];
		return $this->display('share-adv-list',$data);
	}
	
    public function getAdvEdit($id=0)
	{
		$data = array();
		if($id){
			$data['adv'] = ShareService::getAdvInfoById($id);
		}
		$data['tpls'] = ShareService::getTplToKV('ios');
		return $this->display('share-adv-info',$data);
	}
	
	public function postAdvEdit()
	{
		$input = Input::only('id','platform','tpl_ename','target_id','target_title','title','weixin','weibo','redirect_url','start_time','end_time','is_show');				
		//数据验证
		$rule = array();
		
		
		//
	    if($input['start_time']){
			$input['start_time'] = strtotime($input['start_time']);
		}
	    if($input['end_time']){
			$input['end_time'] = strtotime($input['end_time']);
		}
		$tpl = ShareService::getTplInfoByEname($input['tpl_ename']);
		if(!$tpl) return $this->back('类别不存在');
		$input['tpl_title'] = $tpl['title'];
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
			return $this->redirect('system/share/adv-list','数据保存成功');
		}else{
			return $this->back('数据保存失败');
		}
	}
	
	public function getAdvDelete($id)
	{
		ShareService::deleteAdvInfo($id);
		return $this->back('数据删除成功');
	}
}