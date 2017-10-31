<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;

use Youxiduo\Android\Model\Article;

use Youxiduo\Android\Model\GameTool;
use Youxiduo\Android\Model\GameVideo;
use Youxiduo\Android\Model\Guide;
use Youxiduo\Android\Model\News;
use Youxiduo\Android\Model\Opinion;

use Youxiduo\Android\Model\SystemFeedback;
use Youxiduo\Android\Model\User;
use Youxiduo\Android\Model\UserFavorite;

use Youxiduo\Android\Model\GamePlat;

use Youxiduo\Android\Model\Comment;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;

use Youxiduo\Android\Model\Video;
use Youxiduo\Android\Model\NewGame;
use Youxiduo\Android\Model\VideoGame;
use Youxiduo\Android\Model\Game;

use Youxiduo\Helper\Utility;

class ArticleService extends BaseService
{
	/**
	 * 添加收藏
	 */
	public static function addUserFavorite($uid,$link_id,$link_type)
	{		
		$gid = 0;
		switch($link_type){
			case 'news':
				$tmp = News::getShortInfoById($link_id);
				$gid = isset($tmp['agid']) ? $tmp['agid'] : 0;
				break;
			case 'guide':
				$tmp = Guide::getShortInfoById($link_id);
				$gid = isset($tmp['agid']) ? $tmp['agid'] : 0;
				break;
			case 'opinion':
				$tmp = Opinion::getShortInfoById($link_id);
				$gid = isset($tmp['agid']) ? $tmp['agid'] : 0;
				break;
			case 'video':
				$tmp = GameVideo::getShortInfoById($link_id);
				$gid = isset($tmp['agid']) ? $tmp['agid'] : 0;
				break;
			default:
				break;
		}
		$result = UserFavorite::addUserFavorite($uid, $link_id, $link_type,$gid);
		if($result){
			return self::trace_result(array('result'=>true));
		}else{
			return self::trace_error('E1','已经被收藏了');
		}
	}
	
	/**
	 * 移除收藏
	 */
	public static function removeUserFavorite($uid,$link_id,$link_type)
	{		
		$result = UserFavorite::removeUserFavorite($uid, $link_id, $link_type);				
		return self::trace_result(array('result'=>true));		
	}
	
	/**
	 * 是否已经收藏
	 */
    public static function isExistsUserFavorite($uid,$link_id,$link_type)
	{		
		$result = UserFavorite::isExistsUserFavorite($uid, $link_id, $link_type);				
		return self::trace_result(array('result'=>$result));		
	}
	
	/**
	 * 获取收藏列表
	 */
	public static function getUserFavorite($uid,$pageIndex=1,$pageSize=20,$gid=0)
	{
		$favs = UserFavorite::getUserFavorite($uid,$pageIndex,$pageSize,$gid);
		$total = UserFavorite::getUserFavoriteCount($uid,$gid);
		$news_ids = array();
		$guide_ids = array();
		$opinion_ids = array();
		$video_ids = array();
		$newgame_ids = array();
		foreach($favs as $row){
			switch($row['link_type']){
				case 'news':
					$news_ids[] = $row['link_id'];
					break;
				case 'guide':
					$guide_ids[] = $row['link_id'];
					break;
				case 'opinion':
					$opinion_ids[] = $row['link_id'];
					break;
				case 'newgame':
					$newgame_ids[] = $row['link_id'];
					break;
				case 'video':
					$video_ids[] = $row['link_id'];
					break;
				default:
					break;
			}
		}
		
		$news_list = News::getListByIds($news_ids);
		$guide_list = Guide::getListByIds($guide_ids);
		$opinion_list = Opinion::getListByIds($opinion_ids);
		$video_list = GameVideo::getListByIds($video_ids);
		$out = array();
		foreach($favs as $row){
			$data = array();
		    switch($row['link_type']){
				case 'news':
					if(!isset($news_list[$row['link_id']])) continue;
					$data['article_id'] = $row['link_id'];
					$data['article_type'] = $row['link_type'];
					$data['title'] = $news_list[$row['link_id']]['title'];
					$data['series'] = $news_list[$row['link_id']]['pid']==0 ? false : true;
					break;
				case 'guide':
					if(!isset($guide_list[$row['link_id']])) continue;
					$data['article_id'] = $row['link_id'];
					$data['article_type'] = $row['link_type'];
					$data['title'] = $guide_list[$row['link_id']]['title'];
					$data['series'] = $guide_list[$row['link_id']]['pid']==0 ? false : true;
					break;
				case 'opinion':
					if(!isset($opinion_list[$row['link_id']])) continue;
					$data['article_id'] = $row['link_id'];
					$data['article_type'] = $row['link_type'];
					$data['title'] = $opinion_list[$row['link_id']]['title'];
					$data['series'] = $opinion_list[$row['link_id']]['pid']==0 ? false : true;
					break;
				case 'video':
					if(!isset($video_list[$row['link_id']])) continue;
					$data['article_id'] = $row['link_id'];
					$data['article_type'] = $row['link_type'];
					$data['title'] = $video_list[$row['link_id']]['title'];
					$data['series'] = false;
					break;
				default:
					break;
			}
			if($data){
				$out[] = $data;
			}
		}
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getNewGameList($pageIndex,$pageSize)
	{
		$total = NewGame::getCount();
		$result = NewGame::getList($pageIndex,$pageSize);
		$out = array();
		foreach($result as $row)
		{
			$data = array();
			$data['agnid'] = $row['id'];
			$data['type'] = $row['type'];
			$data['title'] = $row['title'] ? : $row['gname'];
			$data['date'] = $row['date'];
			$data['adddate'] = $row['adddate'];
			$data['pic'] = Config::get('app.image_url') . $row['pic'];
			
			$out[] = $data;
		}
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getVideoList($pageIndex,$pageSize,$type)
	{
		$total = Video::getCount($type);
		$result = Video::getList($pageIndex,$pageSize,$type);
		$out = array();
		
		foreach($result as $row){
			$data = array();
			$data['vid'] = $row['id'];
			$data['title'] = $row['vname'];
			$data['img'] = Config::get('app.image_url') . $row['litpic'];
			$data['type'] = $row['type'];
			$data['summary'] = $row['description'];
			$data['editor'] = $row['writer'];
			$data['updatetime'] = date('Y-m-d',$row['addtime']);
			$data['viewcount'] = $row['viewtimes'];
			$out[] = $data;
		}
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getVideoDetail($id)
	{
		$video = Video::getInfoById($id);
		if($video){
			$views_num = rand(14,19);
			Video::updateViewTimes($id,$views_num);
			$out = array();
			$out['vid'] = $video['id'];
			$out['atid'] = 0;
			$out['title'] = $video['vname'];
			$out['type'] = $video['type'];
			$out['score'] = $video['score'];
			$out['img'] = Config::get('app.image_url') . $video['litpic'];
			$out['url'] = $video['video'];
			$out['editor'] = $video['writer'];
			$out['desc'] = $video['description'];
			$out['updatetime'] = date('Y-m-d',$video['addtime']);
			$out['pre_vid'] = Video::getNextId($id,0);
			$out['next_vid'] = Video::getPreId($id,0);
			$out['viewcount'] = $video['viewtimes'];
			$out['games'] = array();
			$gids = VideoGame::getGameIdsByVideoId($id);
			if($gids){
				$games = Game::getListByIds($gids);
				$comment_count = Comment::getCountByGameIds($gids);
				$game_plats = GamePlat::getListByGameIds($gids);
				foreach($gids as $gid){
					if(!isset($games[$gid])) continue;
					$data = array();
					$data['gid'] = $games[$gid]['id'];
					$data['title'] = $games[$gid]['shortgname'];
					$data['summary'] = $games[$gid]['shortcomt'];
					$data['download'] = isset($game_plats[$gid][0]['downurl']) ? $game_plats[$gid][0]['downurl'] : '';
					$data['score'] = $games[$gid]['score'];
					$data['img'] = Config::get('app.image_url') . $games[$gid]['ico'];
					$data['commentcount'] = isset($comment_count[$gid]) ? $comment_count[$gid] : 0;
					$data['have_downplat'] = isset($game_plats[$gid][0]['downurl']) ? true : false;
					$out['games'][] = $data;
				}
			}
			
			
			return self::trace_result(array('result'=>$out));
		}
		return self::trace_error('E1','视频不存在');
	}
	
	/**
	 * 搜索
	 * @param string $keyword
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function search($keyword,$pageIndex=1,$pageSize=20,$gid=0)
	{
		$total = Article::searchCount($keyword,$gid);
		$out = array();
		//$guide_count = Guide::searchCount($keyword,$gid);
		//$guides = Guide::search($keyword,$pageIndex,$pageSize,$gid);
		$result = Article::search($keyword,$pageIndex,$pageSize,$gid);		
		foreach($result as $row){
			$data = array();
			$data['type'] = $row['cate_id'];
			$data['id'] = $row['aid'];
			$data['title'] = $row['title'];
			$data['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[] = $data;
		}
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}

    /**
     * 游戏文章列表
     */
    public static function getGameArticleList($gid=0){
        $out = array();
        $guides = Guide::getGameGuides($gid);
        $opinions = Opinion::getGameOpinion($gid);
        $news = News::getGameNews($gid);
        $videos = GameVideo::getGameVideos($gid);
        $tools = GameTool::getGameTools($gid);

        $out = array(
            'guides' => $guides,
            'opinions' => $opinions,
            'news' => $news,
            'videos' => $videos,
            'tools' => $tools
        );
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 显示文章详情
     */
    public static function getArticleDetail($guid,$goid,$gnid,$agnid,$showimg)
    {
        $out = array();
        if ($guid){
            $row = Guide::getShortInfoById($guid);
            if ($row){
                $out['guid'] = $row['id'];
                $out['body'] = Utility::_getArticleContent($row['gtitle'], $row['writer'], date("Y-m-d", $row['addtime']), Utility::_processVideoContent($row['content']), $showimg);
                $out['title'] = $row['gtitle'];
                $games = Game::getListByIds(array($row['agid']));
                $out['url']   = Utility::getImageUrl($games[$row['agid']]['ico']);
                unset($games);
            }
            return self::trace_result(array('result'=>$out));
        }else if($goid){
            $row = Opinion::getShortInfoById($goid);
            if ($row){
                $out['goid'] = $row['id'];
                $out['body'] = Utility::_getArticleContent($row['ftitle'], $row['writer'], date("Y-m-d", $row['addtime']), Utility::_processVideoContent($row['content']), $showimg);
                $out['title'] = $row['ftitle'];
                $games = Game::getListByIds(array($row['agid']));
                $out['url']   = Utility::getImageUrl($games[$row['agid']]['ico']);
                unset($games);
            }
            return self::trace_result(array('result'=>$out));
        }else if($gnid){
            $row = News::getShortInfoById($gnid);
            if ($row){
                $out['gnid'] = $row['id'];
                $out['body'] = Utility::_getArticleContent($row['title'], $row['writer'], date("Y-m-d", $row['addtime']), Utility::_processVideoContent($row['content']), $showimg);
                $out['title'] = $row['title'];
                $games = Game::getListByIds(array($row['agid']));
                $out['url']   = Utility::getImageUrl($games[$row['agid']]['ico']);
                unset($games);
            }
            return self::trace_result(array('result'=>$out));
        }else if($agnid){
            if ($agnid != 1 && $agnid != 9999 &&  $agnid != 10000){
                //$notice = NewGame::getShortInfoById($agnid);
                $notice = NewGame::db()->where('id','=',$agnid)->first();
                if($notice) $notice['video_url'] = '';
                $out['agnid'] = $notice['id'];
                $out['body']  = Utility::_noticeDetailOld($notice);//$notice['content'];
                $out['title'] = $notice['title'] ? $notice['title'] : $notice['gname'];
                $out['url']   = Utility::getImageUrl($notice['pic']);
                //CountAction::schemeThree(1,$agnid);//统计新游预告 20130904 ztl
            }else{
                $api_base_url = Config::get('app.api');
                if ($agnid == 1){
                    //$content	=	"<script type='text/javascript'>window.location.href='".$api_base_url."/article/getlistnotice'</script>";
                    $content	=	"<script type='text/javascript'>window.location.href='".$api_base_url."/article/noticelist'</script>";
                    $out['agnid'] 	= 	"1";
                }elseif($agnid == 9999){	//游戏礼包
                    $content	=	"<script type='text/javascript'>window.location.href='".$api_base_url."/article/giftlist'</script>";
                    $out['agnid'] 	= 	"9999";
                }elseif($agnid == 10000){	//热门活动
                    $content	=	"<script type='text/javascript'>window.location.href='".$api_base_url."/article/activitylist'</script>";
                    $out['agnid'] 	= 	"10000";
                }else{
                    $content	=	"<script type='text/javascript'>window.location.href='".$api_base_url."/article/noticelist'</script>";
                    $out['agnid'] 	= 	"1";
                }
                $out['body']	=	$content;
            }
            return self::trace_result(array('result'=>$out));
        }else{
            return self::trace_error('E1','参数不能为空');
        }
    }

    /**
     * 获取游戏视频列表
     */
    public static function getGamevideos($gid=0)
    {
        $out = array();
        if($gid){
            $out = GameVideo::getGameVideos($gid);
        }
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 获取游戏视频详情
     */
    public static function getGamevideoShow($gvid=0)
    {
        $out = array();
        if($gvid){
            $out = GameVideo::getGameVideoDetail($gvid);
        }
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 按照文章ID返回系列文章列表
     */
    public static function getArticleSeriesByID($guid,$goid,$gnid)
    {
        $out = array();
        if($guid || $goid || $gnid){
            if ($guid != 0){
                $out = Guide::getArticleSeriesById($guid);
            }else if ($goid != 0){
                $out = Opinion::getArticleSeriesById($goid);
            }else if ($gnid != 0){
                $out = News::getArticleSeriesById($gnid);
            }
        }
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 提交用户反馈
     */
    public static function postSystemFeedback($feedback,$appver,$iosver,$ostype)
    {
        if (mb_strlen($feedback,'utf8') > 140 || trim($feedback) == ''){
            return self::trace_error("E11");
        }
        $feedback = rawurldecode($feedback);
        $data['feedback'] = strip_tags($feedback);
        $data['appver'] = strip_tags($appver);
        $data['iosver'] = strip_tags($iosver);
        $data['ostype'] = strip_tags($ostype);
        $data['addtime'] = time();
        $id = SystemFeedback::save($data);
        if(!$id){
            return self::trace_error('E50');
        }
        return self::trace_result();
    }

}


