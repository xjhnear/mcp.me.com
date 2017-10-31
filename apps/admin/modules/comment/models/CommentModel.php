<?php

namespace modules\comment\models;
use Yxd\Services\Cms\CommentService;

use Yxd\Services\Cms\InfoService;

use Yxd\Services\CreditService;

use Yxd\Modules\Message\NoticeService;

use Yxd\Modules\Core\BaseModel;
use Yxd\Services\UserService;

class CommentModel extends BaseModel
{
    public static function searchCount($search)
	{
		$tb = self::bindSearch($search);
		return $tb->count();
	}
	
	public static function searchList($search,$page=1,$size=10,$sort=null)
	{
		$tb = self::bindSearch($search);
		$cmts = $tb->orderBy('id','desc')->forPage($page,$size)->get();
		foreach($cmts  as $key=>$row){
			$row['content'] = json_decode($row['content'],true);			
			$cmts[$key] = $row;
		}
		return $cmts;
	}
	
	protected static function bindSearch($search)
	{
		$tb = self::dbClubSlave()->table('comment');
	    //关键词
		if(isset($search['keyword'])&& !empty($search['keyword']))
		{
			//$keyword = json_encode($search['keyword']);
			$keyword = $search['keyword'];
			$tb = $tb->where('format_content','like','%' . $keyword . '%');
		}
	    //开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('addtime','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('addtime','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		//
		if(isset($search['target_table']) && !empty($search['target_table']) && $search['target_table']!='all'){
			$tb = $tb->where('target_table','=',$search['target_table']);
		}
		//
	    if(isset($search['target_id']) && !empty($search['target_id'])){
			$tb = $tb->where('target_id','=',$search['target_id']);
		}
	    if(isset($search['uid']) && !empty($search['uid'])){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		
		if(isset($search['recycle']) && $search['recycle']){
			$tb = $tb->where('isdel','=',1);
		}else{
			$tb = $tb->where('isdel','=',0);
		}
		
		return $tb;
	}
	
	public static function doDelete($ids)
	{
		return CommentService::deleteByAdmin($ids);
	}
	
	public static function restoreComment($id)
	{
		return self::dbClubMaster()->table('comment')->where('id','=',$id)->update(array('isdel'=>0));
	}
}	