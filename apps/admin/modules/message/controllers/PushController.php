<?php
namespace modules\message\controllers;

use Yxd\Modules\Message\PushService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Yxd\Modules\Message\NoticeService;

use ApnsPHP\Push;
use ApnsPHP\AbstractClass;
use ApnsPHP\Message\BaiduCustom;



class PushController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'message';
	}
	
	public function getList()
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = array();
		$result = NoticeService::search($search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		return $this->display('push-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$data['linkTypeList'] = NoticeService::$LinkTypeList;
		return $this->display('push-info',$data);
	}		
	
	public function postSave()
	{		
		$push = Input::get('is_push',0);
		$type = 1;
		$linktype = Input::get('linktype');
		$link = Input::get('link');
		$title = Input::get('title');
		$content = Input::get('content');
		$to_uids = Input::get('to_uids','');
		$msgtype = Input::get('msgtype');
		if($to_uids){
			$uids = explode(',',$to_uids);
		}else{
			$uids = null;
		}
	    $validator = Validator::make(array(
	        'title'=>$title,
	        'content'=>$content,
	    	'msgtype'=>$msgtype
	    ),
	    array(
		    'title'=>'required',
		    'content'=>'required',
	    	'msgtype'=>'required'
		));
		if($validator->fails()){
			if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
		    if($validator->messages()->has('content')){
				return $this->back()->with('global_tips','内容不能为空');
			}
			if($validator->messages()->has('msgtype')){
				return $this->back()->with('global_tips','发送方式不能为空');
			}
		}
		
		if($msgtype == 1 && !$uids){
			return $this->back()->with('global_tips','定向发送方式下，用户UID不能为空');
		}
		if($msgtype == 2){
			$uids = null;
		}
		NoticeService::sendInitiativeMessage($type, $linktype, $link, $title, $content,0,$push,$uids);
		
		if($push){
			PushService::sendSystemMessage($uids, $content, $linktype, $link);
		}
		
		return $this->redirect('message/push/list');
	}

	public function postImport()
	{
		$data = array();
	    if(!Input::hasFile('filedata')){
			return $this->back()->with('global_tips','礼包卡文件不存在');
		}
		$file = Input::file('filedata');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();				
		if(!in_array($ext,array('xls','xlsx','csv','txt'))) return $this->back()->with('global_tips','上传文件格式错误');
		$server_path = storage_path() . '/tmp/'; 
		$newfilename = microtime() . '.' . $ext;
		$target = $server_path . $newfilename;
		$file->move($server_path,$newfilename);
		$datalist = array();
		if($ext == 'txt'){
			$fp = fopen($target, 'r');
	        $line = 1;                
            while (!feof($fp)) {
                $row = trim(fgets($fp));
                if (strlen($row) < 1) {
                	continue;                        
                }
                $line++;
                $res = preg_split("/[\s,]+/",$row,2);
                
                list($uid,$message) = $res;
                $message = iconv('GBK','UTF-8//IGNORE',$message);
                $datalist[] = array('uid'=>$uid,'message'=>$message);
            }   
		}
		if(!$datalist) return $this->back('消息文件无效');
		$data['datalist'] = $datalist;
		return $this->display('batch_push_info',$data);
	}
	
	public function getBatchPush()
	{
		$data = array();
		
		return $this->display('batch_push_info',$data);
	}
	
	public function postBatchPush()
	{
		$uids = Input::get('uid');
		$messages = Input::get('message');
		if(!$uids || !is_array($uids)){
			return $this->back('没有要发送消息的用户');
		}
		$data = array();
		foreach($uids as $key=>$uid){
			if(!$uid) continue;
			$tmp = array(
			    'linktype'=>0,
			    'link'=>'',
			    'title'=>$messages[$key],
			    'content'=>$messages[$key],
			    'uid'=>$uid
			);
			$data[] = $tmp;
		}
		if($data){
			NoticeService::sendInitiativeMessageFromArray($data);
			return $this->redirect('message/push/list','发送成功');
		}
		return $this->back('没有要发送消息的用户');
	}
}