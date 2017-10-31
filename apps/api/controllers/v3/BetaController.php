<?php

use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
/**
 * 临时版
 */
class BetaController extends BaseController
{
	public function news()
	{
		$gid = Input::get('gid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$tb = DB::connection('cms')->table('news')
		->where('pid','>=',0);
		if($gid>0){
			$tb = $tb->where('gid','=',$gid);
		}
		$tb = $tb->where('is_show_at_audit','=',1);
		$total = $tb->count();
		$list = $tb->orderBy('addtime','desc')
		->forPage($page,$pagesize)
		->get();
		$out = array();
		foreach($list as $row){
			$news = array();
			$news['gnid'] = $row['id'];
			$news['title'] = $row['title'];
			$news['content'] = mb_substr(strip_tags($row['content']),0,50,'utf-8');
			$news['addtime'] = date('Y-m-d',$row['addtime']);
			$out[] = $news;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
	
	public function newgame()
	{
		$gid = Input::get('gid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$tb = DB::connection('cms')->table('game_notice')->where('apptype','!=',2);
		$tb = $tb->where('is_show_at_audit','=',1);
		$total = $tb->count();
		$list = $tb->orderBy('addtime','desc')
		->forPage($page,$pagesize)
		->get();
		$out = array();
		foreach($list as $row){
			$news = array();
			$news['gnid'] = $row['id'];
			$news['title'] = $row['title'] ? : $row['gname'];
			$news['content'] = mb_substr(strip_tags($row['art_content']),0,50,'utf-8');
			$news['addtime'] = date('Y-m-d',$row['addtime']);
			$out[] = $news;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
	
    public function guide()
	{
		$gid = Input::get('gid',0);
		$pageIndex = (int)Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		if($pageIndex<=0) $pageIndex = 1;
		$page = ($pageIndex - 1) * $pagesize;
		
		if($gid>0){
			$sql = "SELECT gid FROM (SELECT gid,addtime FROM m_gonglue WHERE gid=".$gid." and is_show_at_audit=1 ORDER BY addtime DESC) AS b GROUP BY gid ORDER BY addtime DESC LIMIT ".$page.",".$pagesize;
		}else{
		    $sql = "SELECT gid FROM (SELECT gid,addtime FROM m_gonglue WHERE is_show_at_audit=1 ORDER BY addtime DESC) AS b GROUP BY gid ORDER BY addtime DESC LIMIT ".$page.",".$pagesize;
		}
		$_gids = DB::connection('cms')->select($sql);
		$gids = array();
		foreach($_gids as $gid){
			$gids[] = $gid['gid'];
		}
		$gids = array_unique($gids);
		$games = GameService::getGamesByIds($gids);		
		if(!$gids){
			return $this->success(array('result'=>array(),'totalCount'=>0));
		}
		$_guides = DB::connection('cms')->select("select gid,gtitle,addtime from m_gonglue as t1 where gid in (".implode($gids,',').") and (select count(*) from m_gonglue where gid=t1.gid and addtime>t1.addtime)<2 order by gid,addtime desc");
		$guides = array();
		foreach($_guides as $row){
			$guides[$row['gid']][]['title'] = $row['gtitle'];
		}
		//print_r($guides);
		$out = array();
		foreach($gids as $gid){
			$guide['gid'] = $gid;
			$guide['gname'] = isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$guide['title1'] = isset($guides[$gid][0]) ? $guides[$gid][0]['title'] : '';
			$guide['title2'] = isset($guides[$gid][1]) ? $guides[$gid][1]['title'] : '';
			$out[] = $guide; 
		}
		if($gid>0){
			$sql = "SELECT gid FROM m_gonglue WHERE is_show_at_audit=1 AND gid = ".$gid." GROUP BY gid";
		}else{
		    $sql = "SELECT gid FROM m_gonglue WHERE is_show_at_audit=1 GROUP BY gid";
		}
		$total = DB::connection('cms')->select($sql);
		
		return $this->success(array('result'=>$out,'totalCount'=>count($total)));
	}
	
    public function guideList()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$gid = Input::get('gid');
		$result = DB::connection('cms')->table('gonglue')->where('pid','>=',0)->where('gid','=',$gid)->where('is_show_at_audit','=',1)->orderBy('addtime','desc')->forPage($page,$pagesize)->get();
		$total = DB::connection('cms')->table('gonglue')->where('pid','>=',0)->where('gid','=',$gid)->where('is_show_at_audit','=',1)->count();
		$out = array();
		foreach($result as $row){
			$guide = array();
			$guide['guid'] = $row['id'];
			$guide['title'] = $row['gtitle'];
			$guide['video'] = strstr($row['content'], "video") ? 1 : 0;
			$guide['updatetime'] = date("Y-m-d H:i:s", $row['addtime']);
			$out[] = $guide;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
}