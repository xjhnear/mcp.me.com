<?php
namespace modules\forum\models;
use Yxd\Services\UserService;

use Illuminate\Support\Facades\DB;

use Yxd\Services\Cms\GameService;

use Yxd\Modules\Message\NoticeService;
use Yxd\Utility\ForumUtility;
use Yxd\Services\CreditService;
use Illuminate\Support\Facades\Event;
use Yxd\Modules\Core\BaseModel;

class TopicModel extends BaseModel
{
    /**
	 * 搜索帖子
	 */
	public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::buildSearch($search)->count();
		$tb = self::buildSearch($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('forum_topic')
		         ->leftJoin('forum','forum_topic.gid','=','forum.gid')
		         ->leftJoin('forum_channel','forum_topic.cid','=','forum_channel.cid')
		         ->select('forum_topic.*','forum.name as name','forum_channel.channel_name as channel_name')->where('forum_topic.gid','>',0);
		         
		//关键词
		if(isset($search['keytype'])&& !empty($search['keytype'])){		
			if(isset($search['keyword'])&& !empty($search['keyword']))
			{
				switch($search['keytype']){
					case 'title':
						$tb = $tb->where('subject','like','%'.$search['keyword'].'%');
						break;
					case 'uid':
						$tb = $tb->where('author_uid','=',(int)$search['keyword']);
						break;
					case 'tid':
						$tb = $tb->where('tid','=',(int)$search['keyword']);
						break;
				}				
			}
		}
		//
		if(isset($search['game_id'])&& !empty($search['game_id']))
		{
			$tb = $tb->where('forum_topic.gid','=',$search['game_id']);
		}
	    
	    //
		if(isset($search['cid'])&& !empty($search['cid']))
		{
			$tb = $tb->where('cid','=',$search['cid']);
		}
		
		//开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('dateline','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('dateline','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		//
		if(!isset($search['displayorder']))
		{
			//$tb = $tb->where('forum_topic.displayorder','=',0);
		}
		
	    if(isset($search['recycle']) && $search['recycle']==1)
		{
			$tb = $tb->where('forum_topic.displayorder','=',-1);
		}else{
			$tb = $tb->where('forum_topic.displayorder','=',0);
		}
		
		return $tb;
	}

    /**
	 * 保存规则帖
	 */
	public static function saveRuleTopic($tid,$subject,$message,$uid=1)
	{
		$topic = array();
		$topic['tid'] = $tid;
		$topic['gid'] = 0;
		$topic['subject'] = $subject;
		$topic['format_message'] = $message;
		$topic['displayorder'] = 0;
		$topic['is_admin'] = 1;
		
		$topic['cid'] = 0;
		$topic['summary'] = '';
		$topic['listpic'] = '';
		$topic['message'] = '';
		$topic['author'] = '';
		$topic['author_uid'] = $uid;
		$topic['dateline'] = time();
		
		$res = self::save($topic);
		return $res;
	}
	
	/**
	 * 获取规则列表
	 */
	public static function getRuleList($pageIndex=1,$pageSize=10)
	{
		return self::dbClubSlave()->table('forum_topic')->where('gid','=',0)->where('cid','=',0)->forPage($pageIndex,$pageSize)->orderBy('dateline','desc')->get();
	}
	
	/**
	 * 获取规则数量
	 */
	public static function getRuleCount()
	{
		return self::dbClubSlave()->table('forum_topic')->where('gid','=',0)->where('cid','=',0)->count();
	}
	
	/**
	 * 获取规则信息
	 */
	public static function getRuleInfo($tid)
	{
		$topic  = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->first();
		if(!$topic) return array();
		return $topic;
	}
	
	/**
	 * 删除规则信息
	 */
	public static function deleteRuleInfo($tid)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('displayorder'=>-3));
	}
	
	/**
	 * 保存公告帖
	 */
	public static function saveNoticeTopic($tid,$gid,$cid,$subject,$message,$uid=1)
	{
		$topic = array();
		$topic['tid'] = $tid;
		$topic['gid'] = $gid;
		$topic['subject'] = $subject;
		$topic['format_message'] = $message;
		$topic['displayorder'] = 2;
		$topic['is_admin'] = 1;
		
		$topic['cid'] = $cid;
		if($cid !== 0){
			$topic['stick'] = 1;
		}else{
			$topic['stick'] = 0;
		}
		$topic['summary'] = '';
		$topic['listpic'] = '';
		$topic['message'] = '';
		$topic['author'] = '';
		$topic['author_uid'] = $uid;		
		
		$res = self::save($topic);
		return $res;
	}
	
	/**
	 * 获取公告列表
	 */
	public static function getNoticeList($pageIndex=1,$pageSize=10,$recycle=0)
	{
		$displayorder = $recycle==1 ? -2 : 2;
		return self::dbClubSlave()->table('forum_topic')->where('displayorder','=',$displayorder)->forPage($pageIndex,$pageSize)->orderBy('dateline','desc')->get();
	}
	
	/**
	 * 获取公告数量
	 */
	public static function getNoticeCount($recycle=0)
	{
		$displayorder = $recycle==1 ? -2 : 2;
		return self::dbClubSlave()->table('forum_topic')->where('displayorder','=',$displayorder)->count();
	}
	
	/**
	 * 获取公告信息
	 */
	public static function getNoticeInfo($tid)
	{
		$topic  = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->first();
		if(!$topic) return array();
		return $topic;
	}
	
	/**
	 * 删除公告信息
	 */
	public static function deleteNoticeInfo($tid)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('displayorder'=>-2));
	}
	
	/**
	 * 保存帖子信息
	 */
	public static function saveTopicInfo($tid,$gid,$cid,$subject,$message,$listpic,$award,$uid=1,$highlight=0)
	{
		$topic = array();
		$topic['tid'] = $tid;
		$topic['gid'] = $gid;
		$topic['subject'] = $subject;
		$topic['format_message'] = $message;
		$topic['displayorder'] = 0;
		$topic['is_admin'] = 1;
		$topic['author_uid'] = $uid;
		$topic['highlight'] = $highlight;
		$cid && $topic['cid'] = $cid;		
		//$summary && $topic['summary'] = '';
		$topic['award'] = $cid==2 ? $award : 0;
		$topic['ask'] = $cid==2 ? 1 : 0;
		$listpic && $topic['listpic'] = $listpic;
		//$topic['message'] = '';
		//$topic['author'] = '';
		!$tid && $topic['author_uid'] = $uid;
		!$tid && $topic['dateline'] = time();
		!$tid && $topic['lastpost'] = time();
		
		$res = self::save($topic);
		return $res;
	}
	
	/**
	 * 
	 */
	public static function getTopicInfo($tid)
	{
		$topic  = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->first();
		if(!$topic) return array();
		
		if($topic['is_admin']==0){
			$message = json_decode($topic['message'],true);
			$topic['format_message'] = ForumUtility::formatTopicMessage($message);
		}
		
		
		return $topic;
	}
	
	public static function getTopicFullInfo($tid,$page=1,$size=20)
	{
	    $topic  = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->first();
		if(!$topic) return array();
		
		if($topic['is_admin']==0){
			$message = $topic['message'] ? json_decode($topic['message'],true) : null;
			if($message!==null){
			    $topic['format_message'] = ForumUtility::formatTopicMessage($message);
			}
		}
		$replies = self::dbClubSlave()->table('comment')
			->where('target_table','=','yxd_forum_topic')
			->where('target_id','=',$tid)
			->where('isdel','=',0)
			->forPage($page,$size)
			->orderBy('id','desc')
			->get();
		$total = self::dbClubSlave()->table('comment')
			->where('target_table','=','yxd_forum_topic')
			->where('isdel','=',0)
			->where('target_id','=',$tid)
			->count();
		foreach($replies as $key=>$row){
			if($row['is_admin']==0){
				$content = $row['content'] ? json_decode($row['content'],true) : null;
				if($content!==null){
				    $row['format_content'] = ForumUtility::formatTopicMessage($content);
				}
			}
			$replies[$key] = $row;
		}
		//print_r($replies);exit;
		return array('topic'=>$topic,'replies'=>$replies,'total'=>$total);
	}
	
	public static function deleteTopicInfo($tid)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('displayorder'=>-1));
	}
			
	/**
	 * 保存帖子信息
	 */
	public static function save($data)
	{
	    if(isset($data['tid']) && $data['tid']>0){
	    	$tid = $data['tid'];
	    	unset($data['tid']);
	    	$data['is_sync'] = 0;
	    	$data['sync_type'] = 'edit';
			self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update($data);			
		}else{
			$data['dateline'] = time();
			$tid = self::dbClubMaster()->table('forum_topic')->insertGetId($data);
		}
		return $tid;
	}
	
	/**
	 * 删帖
	 */
	public static function delTopic($tid)
	{
		if(is_array($tid)){
			$topics = self::dbClubMaster()->table('forum_topic')->whereIn('tid',$tid)->get();
			foreach($topics as $row){
				$uid = $row['author_uid'];
				$game = GameService::getGameInfo($row['gid']);
		        $params = array('catename'=>$game['shortgname'],'title'=>$row['subject']);
				$uid && NoticeService::sendTopicDeletedByAdmin($uid,$params);
				$uid && CreditService::doUserCredit($uid,CreditService::CREDIT_RULE_ACTION_DELETE_TOPIC,'帖子被管理员删除');
			}
			return self::dbClubMaster()->table('forum_topic')->whereIn('tid',$tid)->update(array('displayorder'=>-1,'is_sync'=>0,'sync_type'=>'delete'));
		}
		$topic = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->first();
		$game = GameService::getGameInfo($topic['gid']);
		$params = array('catename'=>$game['shortgname'],'title'=>$topic['subject']);
		$topic && $topic['author_uid'] && NoticeService::sendTopicDeletedByAdmin($topic['author_uid'],$params);
		$topic && $topic['author_uid'] && CreditService::doUserCredit($topic['author_uid'],CreditService::CREDIT_RULE_ACTION_DELETE_TOPIC,'帖子被管理员删除');
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('displayorder'=>-1,'is_sync'=>0,'sync_type'=>'delete'));
	}
	
	/**
	 * 设置精华
	 */
	public static function updateTopicDigest($tid,$digest)
	{
		$topic = self::dbClubSlave()->table('forum_topic')->where('tid','=',$tid)->first();
		if($topic){
			if($topic['cid']==2) return false;
		}
		
		if($digest==1){
			$uid = $topic['author_uid'];
			if(!$uid) return false;			
			$credit = CreditService::getUserOpCredit(CreditService::CREDIT_RULE_ACTION_DIGEST_TOPIC);
			if(!$credit) return false;			
			$money = $credit['score'];
			$title = $topic['subject'];
			$game_id = $topic['gid'];
			$game = GameService::getGameInfo($game_id);
			$game_name = $game ? $game['shortgname'] : '';
			$result = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('digest'=>$digest,'rate'=>$money));
			//奖励游币
			CreditService::doUserCredit($uid,CreditService::CREDIT_RULE_ACTION_DIGEST_TOPIC,'帖子被管理员加精');
			//发系统消息
			$params = array('game_name'=>$game_name,'title'=>$title,'money'=>$money);
			NoticeService::sendTopicDigestByAdmin($uid, $params);
		    Event::fire('topic.digest', array($tid));
		}else{
			$uid = $topic['author_uid'];
			if(!$uid) return false;			
			$credit = CreditService::getUserOpCredit(CreditService::CREDIT_RULE_ACTION_UNDIGEST_TOPIC);
			if(!$credit) return false;
			$money = abs($credit['score']);
			$title = $topic['subject'];
			$game_id = $topic['gid'];
			$game = GameService::getGameInfo($game_id);
			$game_name = $game ? $game['shortgname'] : '';
			$result = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('digest'=>$digest,'rate'=>0));
			//取消奖励游币
			CreditService::doUserCredit($uid,CreditService::CREDIT_RULE_ACTION_UNDIGEST_TOPIC,'帖子被管理员取消加精');
			//发系统消息
			$params = array('game_name'=>$game_name,'title'=>$title,'money'=>$money);
			NoticeService::sendTopicUnDigestByAdmin($uid, $params);
			Event::fire('topic.undigest', array($tid));
		}
		return true;
	}
	
	/**
	 * 设置置顶
	 */
    public static function updateTopicStick($tid,$stick)
	{
		$result = self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('stick'=>$stick));
		if($result){
			if($stick==1){
			    Event::fire('topic.stick', array($tid));
			}else{
				Event::fire('topic.unstick', array($tid));
			}
		}
		return $result;
	}
	
	public static function restoreTopic($tid,$order)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('displayorder'=>$order));
	}
	
    public static function doTopicStatus($tid,$status)
	{
		return self::dbClubMaster()->table('forum_topic')->where('tid','=',$tid)->update(array('status'=>$status));
	}
	
	public static function exportDataToUser($tid)
	{
		$result = self::dbClubMaster()->table('comment')->select('uid')->where('target_id','=',$tid)->where('target_table','=','yxd_forum_topic')->where('isdel','=',0)->get();
		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('所有回帖人');
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$excel->getActiveSheet()->setCellValue('A1','用户UID');
		foreach($result as $index=>$row){
			$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['uid']);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
		
		$writer->save('php://output');
		
	}
	
    public static function exportDataToUserFloor($tid)
	{
		$result = self::dbClubMaster()->table('comment')->select(DB::raw('uid,min(storey) as floor,count(*) as total'))
		->where('target_id','=',$tid)
		->where('target_table','=','yxd_forum_topic')
		->where('isdel','=',0)
		->groupBy('uid')
		->orderBy('floor','asc')
		->get();
		
		$uids = array();
		foreach($result as $row){
			$uids[] = $row['uid'];
		}
		
		if($uids){
			$users = UserService::getAppleIdentifyByUids($uids);
		}
		$out = array();
		$lock = array();
		foreach($result as $row){
			if(!isset($lock[$users[$row['uid']]])){
				$lock[$users[$row['uid']]] = true;
				$out[] = $row;
			}
		}
		
		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('所有回帖人');
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$excel->getActiveSheet()->setCellValue('A1','用户UID');
		$excel->getActiveSheet()->setCellValue('B1','回帖楼层');
		$excel->getActiveSheet()->setCellValue('C1','回帖数');
		$excel->getActiveSheet()->setCellValue('D1','设备号');
		foreach($out as $index=>$row){
			$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['uid']);
			$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['floor']);
			$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['total']);
			$excel->getActiveSheet()->setCellValue('D'.($index+2),$users[$row['uid']]);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
		
		$writer->save('php://output');
		
	}
}