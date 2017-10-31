<?php
namespace modules\a_giftbag\controllers;


use Youxiduo\Android\BaiduPushService;
use Youxiduo\Message\NoticeService;

use Youxiduo\Android\Model\GiftbagAccount;

use Yxd\Modules\Message\PromptService;

use Yxd\Modules\Activity\GiftbagService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;

use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\GiftbagAppoint;
use Youxiduo\Android\Model\GiftbagCard;
use Youxiduo\Android\PushService;
use Yxd\Services\UserService;

class GiftController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_giftbag';
	}
	
	/**
	 * 
	 */
	public function getSearch()
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = Input::only('keyword','game_id','is_activity','is_appoint','from_type');
		$result = Giftbag::m_search($search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['result'];	
		return $this->display('gift-search',$data);
	}
	
	public function getPopSearch($no=0)
	{
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword,'is_activity'=>1);		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$data['no'] = $no;	
		$data['keyword'] = $keyword;
		$result = Giftbag::m_search($search,$page,$pagesize);
		$total = $result['total'];
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$html = $this->html('pop-giftbag-list',$data);
		return $this->json(array('html'=>$html));
	}
	
    public function getPopInfo($id)
	{
		$giftbag = array();
		if($id){
		    $giftbag = Giftbag::m_getInfo($id);
		}
		return $this->json(array('giftbag'=>$giftbag));
	}

	/**
	 * 添加礼包
	 */
	public function getAdd()
	{
		$data = array();
		$data['gift'] = array('is_show'=>1,'show_at_client'=>1,'show_at_suspendsion'=>1,'from_type'=>1);
		return $this->display('gift-info',$data);
	}
	
    /**
	 * 编辑礼包
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gift'] = Giftbag::m_getInfo($id);
		return $this->display('gift-info',$data);
	}
	
	/**
	 * 保存礼包信息
	 */
	public function postSave()
	{
		$input = array();
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['shorttitle'] = Input::get('shorttitle','');
		$input['is_ios'] = 1;//Input::get('is_ios',1);
		//$input['is_android'] = Input::get('is_android',0);
		$input['game_id'] = (int)Input::get('game_id');
		$input['listpic'] = Input::get('listpic','');
		$input['starttime'] = strtotime(Input::get('starttime'));
		$input['endtime'] = strtotime(Input::get('endtime'));
		$input['sort'] = (int)Input::get('sort',50);
		$input['is_hot'] = (int)Input::get('is_hot',0);
		$input['is_top'] = (int)Input::get('is_top',0);
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['is_activity'] = (int)Input::get('is_activity',0);
		$input['is_appoint'] = (int)Input::get('is_appoint',0);
		$input['show_at_client'] = (int)Input::get('show_at_client',0);
		$input['show_at_suspendsion'] = (int)Input::get('show_at_suspendsion',0);
		$input['content'] = Input::get('content');
		$condition = array('score'=>Input::get('score',0));
		$input['condition'] = json_encode($condition);
		$input['from_type'] = (int)Input::get('from_type');
		//$input['ctime'] = time();
		$id = Giftbag::m_save($input);
		if($id){
			return $this->redirect('a_giftbag/gift/search')->with('global_tips','礼包保存成功');
		}
	}
	
	public function getPush($id)
	{
        $push = BaiduPushService::sendGiftbagSubscribeMessage($id);
		if($push){
			$update = array('id'=>$id,'is_send'=>1);
			Giftbag::m_save($update);
			return $this->redirect('a_giftbag/gift/search')->with('global_tips','推送成功');
		}else{
			return $this->redirect('a_giftbag/gift/search')->with('global_tips','推送失败');
		}		
		
	}
	
	/**
	 * 专属礼包
	 */
	public function getAppointUser($giftbag_id)
	{
		$data = array();
		$data['giftbag_id'] = $giftbag_id;
		$result = GiftbagAppoint::db()->where('giftbag_id','=',$giftbag_id)->orderBy('id','asc')->get();
		if($result){
			$uids = array();
			foreach($result as $row){
				$uids[] = $row['uid'];
			}
			if($uids){
				$users = UserService::getBatchUserInfo($uids);
				$data['users'] = $users;
			}
		}
		$data['datalist'] = $result;
		return $this->display('gift-appoint-user',$data);
	}
	
    public function getAddAppointUser($giftbag_id)
	{
		$data = array();
		$data['giftbag_id'] = $giftbag_id;
		return $this->display('gift-appoint-add',$data);
	}
	
	public function postAddAppointUser()
	{
		$giftbag_id = (int)Input::get('giftbag_id',0);		
		$giftbag = Giftbag::m_getInfo($giftbag_id);
		if(!$giftbag) return $this->back('礼包不存在');
		
		$uids = (int)Input::get('uid');
		$messages = Input::get('message');
		$template = Input::get('template','');
		if(!$uids || !is_array($uids)){
			return $this->back('没有要发送消息的用户');
		}
		$data_appoint = array();
		$data_mygiftbag = array();
		foreach($uids as $key=>$uid){
			if(!$uid) continue;
			$content = str_replace('{cardno}',$messages[$key], $template);
			$data_appoint[] = array(
			    'giftbag_id'=>$giftbag_id,
			    'uid'=>$uid,
			    'cardno'=>$messages[$key],
			    'message'=>$content,
			    'add_time'=>time(),
			    'is_send'=>0
			);
			
			$data_mygiftbag = array(
			    'gift_id'=>$giftbag_id,
			    'uid'=>$uid,
			    'card_no'=>$messages[$key],
			    'addtime'=>time(),
			    'game_id'=>$giftbag['game_id']
			);
		}
		//保存到专属表
		if($data_appoint){
			GiftbagAppoint::db()->insert($data_appoint);
		}
		//保存到礼包箱
		if($data_mygiftbag){
			GiftbagAccount::db()->insert($data_mygiftbag);
		}
		//发送小秘书消息
		$result = GiftbagAppoint::db()->where('giftbag_id','=',$giftbag_id)->where('is_send','=',0)->orderBy('id','asc')->get();
		if($result){
			foreach($result as $row){
				$uid = $row['uid'];				
				$success = NoticeService::sendOneMessage($row['message'], $row['message'], -1, '', $uid, false);
				if($success==true){
					GiftbagAppoint::db()->where('id','=',$row['id'])->update(array('is_send'=>1));
				}
			}
		}		
				
		return $this->redirect('a_giftbag/gift/appoint-user/'.$giftbag_id,'发送完成');
	}
	
	public function getPushAppoint($id)
	{
		$info = GiftbagAppoint::db()->where('id','=',$id)->first();
		if(!$info) return $this->back('没有发送的消息对象');
		$success = NoticeService::sendOneMessage($info['message'],$info['message'],-1,'',$info['uid'],false);
	    if($success==true){
			GiftbagAppoint::db()->where('id','=',$id)->update(array('is_send'=>1));
			return $this->back('发送成功');
		}
		return $this->back('发送失败');
	}
	
    public function getReport()
	{
		$startdate = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$enddate = mktime(23,59,59,date('m'),date('d'),date('Y'));		
		$pageIndex = (int)Input::get('page',1);
		$s = Input::get('startdate','');
		$d = Input::get('enddate','');
		!empty($s) && $startdate = strtotime($s);
		!empty($d) && $enddate = strtotime($d)+60*60*24-1;
		$from_type = (int)Input::get('from_type');
		$search = array('startdate'=>date('Y-m-d',$startdate),'enddate'=>date('Y-m-d',$enddate),'from_type'=>$from_type);
		$gfid= array();
		if($from_type>0){
			$gfid = Giftbag::db()->where('from_type','=',$from_type)->lists('id');
			if(empty($gfid)) $gfid = array(0);
		}
		$pageSize=15;
		$data = array();
		$result = GiftbagCard::getReport($startdate, $enddate,$pageIndex,$pageSize,$gfid);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['total_count'] = $result['total_count'];
		return $this->display('gift-report',$data);
	}
	
	
}