<?php
namespace Yxd\Services\Cms;

use Yxd\Services\LikeService;
use Yxd\Services\Service;

class ArticleService extends Service
{		
	/**
	 * 资料大全首页
	 */
	public static function getArticleHome($gameid)
	{
		$result = array();
		$news = self::dbCmsSlave()->table('news')
		                   ->where('gid','=',$gameid)
		                   ->where('pid','<=','0')
		                   ->orderBy('sort','desc')
		                   ->orderBy('addtime','desc')
		                   ->forPage(1,5)
		                   ->get();
		$out = array();
	    foreach($news as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['ptitle'] = $row['title'];
			$out[$index]['video'] = strstr($row['content'], "video") ? 1 : 0;;
		}
		$result['news'] = $out;
		$guides = self::dbCmsSlave()->table('gonglue')
		                   ->where('gid','=',$gameid)
		                   ->where('pid','<=','0')
		                   ->orderBy('sort','desc')
		                   ->orderBy('addtime','desc')
		                   ->get();
		$out = array();
	    foreach($guides as $index=>$row){
			$out[$index]['guid'] = $row['id'];
			$out[$index]['title'] = $row['gtitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['video'] = strstr($row['content'], "video") ? 1 : 0;;
		}
		$result['guides'] = $out;
		$opinions = self::dbCmsSlave()->table('feedback')
		                   ->where('gid','=',$gameid)
		                   ->where('pid','<=','0')
		                   ->orderBy('sort','desc')
		                   ->orderBy('addtime','desc')
		                   ->forPage(1,5)
		                   ->get();
		$out = array();
	    foreach($opinions as $index=>$row){
			$out[$index]['goid'] = $row['id'];
			$out[$index]['title'] = $row['ftitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['video'] = strstr($row['content'], "video") ? 1 : 0;;
		}
		$result['opinions'] = $out;
		$videos = self::dbCmsSlave()->table('games_video')
		                   ->where('gid','=',$gameid)
		                   ->where('type','=','1')
		                   ->orderBy('id','desc')
		                   ->forPage(1,2)
		                   ->get();
		$out = array();
	    foreach($videos as $index=>$row){
			$out[$index]['gvid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['img'] = ArticleService::joinImgUrl($row['ico']);
			$out[$index]['url'] = $row['video'];
		}
		$result['videos'] = $out;
		return $result;
	}		
	
	/**
	 * 获取新闻
	 */
	public static function getNewsList($page=1,$pagesize=10)
	{
		$total = self::dbCmsSlave()->table('news')->where('pid','<=',0)->count();
		$artlist = self::dbCmsSlave()->table('news')->where('pid','<=',0)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['commentcount'] = '0';
			$out[$index]['pictures'] = array();
			$pic1 = trim($row['litpic']); 
			if($pic1){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic']));
			}
			
		    if(trim($row['litpic2'])){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic2']));
			}
			
		    if(trim($row['litpic3'])){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic3']));
			}
			
		}
		return array('result'=>$out,'totalCount'=>$total);
		
		$tb = self::dbCmsSlave()->table('news');
		$total = $tb->where('pid','<=',0)->count();
		$artlist = $tb->where('pid','<=',0)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['ptitle'] = $row['title'];
			$out[$index]['video'] = 0;
		}
		return array('artlist'=>$out,'total'=>$total);
	}
	
    /**
	 * 获取攻略
	 */
	public static function getGuideList($page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('gonglue');
		$total = $tb->where('pid','<=',0)->count();
		$artlist = $tb->where('pid','<=',0)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['guid'] = $row['id'];
			$out[$index]['title'] = $row['gtitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['video'] = 0;
		}
		return array('artlist'=>$out,'total'=>$total);
	}
	
    /**
	 * 获取评测
	 */
	public static function getOpinionList($page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('feedback');
		$total = $tb->where('pid','<=',0)->count();
		$artlist = $tb->where('pid','<=',0)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['goid'] = $row['id'];
			$out[$index]['title'] = $row['ftitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['series'] = $row['pid']==-1 ? 1 : 0;
			$out[$index]['video'] = 0;
		}
		return array('artlist'=>$out,'total'=>$total);
	}
	
	
	
	protected static function getVideoDetail($id)
	{
	    $detail = self::dbCmsSlave()->table('video')->where('id','=',$id)->first();
		$out = array();
		if($detail){
			$out['gnid'] = $detail['id'];
			$out['body'] = $detail['content'];
			$out['title'] = $detail['ftitle'];
			//$out['desc'] = $detail[''];
			//$out['next_vid'] = $detail[''];
			//$out['type'] = $detail[''];
			$out['img'] = self::joinImgUrl($detail['litpic']);			
			$out['updatetime'] = date('Y-m-d H:i:s',$detail['addtime']);
			//$out['vid'] = $detail[''];
			//
			//$out['url'] = $detail[''];
			//$out['viewcount'] = 0;//$detail[''];
			$out['editor'] = $detail['writer'];
			//$out['pre_vid'] = $detail[''];
			//$out['gfid'] = $detail[''];
			//游戏
			$game = GameService::getGameInfo($detail['gid']);
			$out['url'] = self::joinImgUrl($game['ico']);
			$out['games']['gid'] = $game['id'];
			$out['games']['title'] = $game['shortgname'];
			$out['games']['summary'] = $game['shortcomt'];
			$out['games']['download'] = $game['downtimes'];
			$out['games']['score'] = $game['score'];
			$out['games']['img'] = self::joinImgUrl($game['ico']);
			$out['games']['commentcount'] = 0;//$game[''];
			//评论
			$comments = CommentService::getList($id,'m_news',1,10);
			$out['commentInfos'] = $comments['result'];
			return array('result'=>$out);
		}
		return null;
	}
}