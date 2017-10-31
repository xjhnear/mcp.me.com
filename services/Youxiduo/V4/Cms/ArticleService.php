<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\Cms;

use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\Game\Model\IosGame;
use Youxiduo\V4\Game\Model\GameType;
use Youxiduo\V4\Cms\Model\VideoGame;
use Youxiduo\V4\Game\Model\GameCollectType;
use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Cms\Model\Article;
use Youxiduo\V4\Cms\Model\News;
use Youxiduo\V4\Cms\Model\Guide;
use Youxiduo\V4\Cms\Model\Opinion;
use Youxiduo\V4\Cms\Model\GameVideo;
use Youxiduo\V4\Cms\Model\Video;
use Youxiduo\V4\Cms\Model\VideoType; 
use Youxiduo\V4\Cms\Model\NewGame;

use Youxiduo\V4\Game\GameService;

class ArticleService extends BaseService
{
	const ERROR_VIDEO_NOT_EXISTS = 'video_not_exists';
	const ERROR_ARTICLE_NOT_EXISTS = 'article_not_exists';
	
	const CHANNEL_NEWS = 'news';
	const CHANNEL_GUIDE = 'guide';
	const CHANNEL_OPINION = 'opinion';
	const CHANNEL_NEWGAME = 'newgame';
	
	/**
	 * 文章列表
	 */
    public static function getListByCond($platform,$channel,$type_id,$game_id,$series=0,$sort,$pageIndex=1,$pageSize=10)
    {
        $result = array('result'=>array(),'totalCount'=>0);
        if($channel==self::CHANNEL_NEWS){
            $result = News::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }elseif($channel==self::CHANNEL_GUIDE){
            $result = Guide::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }elseif($channel==self::CHANNEL_OPINION){
            $result = Opinion::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }elseif($channel==self::CHANNEL_NEWGAME){
            $result = NewGame::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }
        if($result['totalCount']==0) return self::ERROR_ARTICLE_NOT_EXISTS;

        $out = array();
        foreach($result['result'] as $row){
            $tmp = array();
            $tmp['aid'] = $row['id'];
            $tmp['title'] = $row['title'];
            $tmp['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
            $tmp['series'] = $row['pid']==-1 ? true : false;
            !empty($row['litpic']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic']));
            !empty($row['litpic2']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic2']));
            !empty($row['litpic3']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic3']));

            $out[] = $tmp;
        }

        return array('result'=>$out,'totalCount'=>$result['totalCount']);
    }

    //ChinaJoy 文章列表
    public static function getListByCond2($platform,$channel,$type_id,$game_id,$series=0,$sort,$pageIndex=1,$pageSize=10,$keyword = array(),$newkeyword = '')
    {
        $result = array('result'=>array(),'totalCount'=>0);
        $klist = array_keys($keyword);
        if($channel==self::CHANNEL_NEWS){
            if($newkeyword){
                $result = News::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize,$newkeyword);
            }else{
                $result = News::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize,$klist);
            }
        }elseif($channel==self::CHANNEL_GUIDE){
            $result = Guide::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }elseif($channel==self::CHANNEL_OPINION){
            $result = Opinion::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }elseif($channel==self::CHANNEL_NEWGAME){
            $result = NewGame::getListByCond($platform, $type_id,$game_id, $series, $sort, $pageIndex, $pageSize);
        }
        if($result['totalCount']==0) return self::ERROR_ARTICLE_NOT_EXISTS;
        $out = array();
        foreach($result['result'] as $row){
            $tmp = array();
            $tmp['aid'] = $row['id'];
            $tmp['title'] = $row['title'];
            $tmp['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
            $tmp['series'] = $row['pid']==-1 ? true : false;
            !empty($row['litpic']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic']));
            !empty($row['litpic2']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic2']));
            !empty($row['litpic3']) && $tmp['imgList'][] = array('imgUrl'=>Utility::getImageUrl($row['litpic3']));
            !empty($row['webkeywords']) && $tmp['keywordList'] = explode(',',strtoupper($row['webkeywords']));
            if(is_array($keyword) && isset($tmp['keywordList'])){
                foreach($klist as $k){
                    in_array(strtoupper($k),$tmp['keywordList']) && $tmp['keyword'] = $keyword[$k];
                }
            }
            $out[] = $tmp;
        }
        $totalpage = ceil($result['totalCount']/$pageSize);
        return array('result'=>$out,'totalCount'=>$result['totalCount'],'totalpage'=>$totalpage);
    }

    /**
     * ChinaJoy文章详情
     */
    public static function getDetail2($platform,$channel,$id)
    {
        $article = array();
        if($channel==self::CHANNEL_NEWS){
            $article = News::getDetailById($platform,$id);
        }elseif($channel==self::CHANNEL_GUIDE){
            $article = Guide::getDetailById($platform,$id);
        }elseif($channel==self::CHANNEL_OPINION){
            $article = Opinion::getDetailById($platform,$id);
        }elseif($channel==self::CHANNEL_NEWGAME){
            $article = NewGame::getDetailById($platform,$id);
        }
        if(!$article) return self::ERROR_ARTICLE_NOT_EXISTS;

        $out = array();
        $out['aid'] = $article['id'];
        $out['title'] = $article['title'];
        $out['writer'] = $article['writer'];
        $out['pid'] = $article['pid'];
        !empty($article['webkeywords']) && $out['keywords'] = $article['webkeywords'];
        !empty($article['webdesc']) && $out['description'] = $article['webdesc'];


        $out['content'] = $article['content'];
        //$out['litpic'] = Utility::getImageUrl($article['litpic']);
        //$out['litpic2'] = Utility::getImageUrl($article['litpic2']);
        //$out['litpic3'] = Utility::getImageUrl($article['litpic3']);
        $out['editor'] = $article['editor'];
        $out['addtime'] = date('Y-m-d H:i:s',$article['addtime']);
        $out['gid'] = $platform=='ios' ? $article['gid'] : $article['agid'];
        $out['commenttimes'] = $article['commenttimes'];
        return $out;
    }

	
	/**
	 * 文章详情
	 */
	public static function getDetail($platform,$channel,$id)
	{
		$article = array();
		if($channel==self::CHANNEL_NEWS){
			$article = News::getDetailById($platform,$id);
		}elseif($channel==self::CHANNEL_GUIDE){
			$article = Guide::getDetailById($platform,$id);
		}elseif($channel==self::CHANNEL_OPINION){
			$article = Opinion::getDetailById($platform,$id);
		}elseif($channel==self::CHANNEL_NEWGAME){
			$article = NewGame::getDetailById($platform,$id);
		}
		if(!$article) return self::ERROR_ARTICLE_NOT_EXISTS;
		
		$out = array();
		$out['aid'] = $article['id'];
		$out['title'] = $article['title'];
		$out['writer'] = $article['writer'];
		$out['pid'] = $article['pid'];
		$out['content'] = $article['content'];
		//$out['litpic'] = Utility::getImageUrl($article['litpic']);
		//$out['litpic2'] = Utility::getImageUrl($article['litpic2']);
		//$out['litpic3'] = Utility::getImageUrl($article['litpic3']);
		$out['editor'] = $article['editor'];
		$out['addtime'] = date('Y-m-d H:i:s',$article['addtime']);
		$out['gid'] = $platform=='ios' ? $article['gid'] : $article['agid'];
		$out['commenttimes'] = $article['commenttimes'];
		
		return $out;
	}
	
	public static function getVideoTypeToList($platform,$isTop)
	{
		$result = VideoType::getList($platform, $isTop);
		$out = array();
		foreach($result as $row){
			$tmp = array();
			$tmp['type_id'] = $row['type_id'];
			$tmp['type_name'] = $row['type_name'];
			$out[] = $tmp;
		}
		return $out;
	}
	
	/**
	 * 获取游戏视频列表
	 * @param string $platform
	 * @param int $game_id
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function getGameVideoList($platform,$game_id,$pageIndex=1,$pageSize=10)
	{
		$total = GameVideo::getCountByGameId($platform, $game_id);
		$result = GameVideo::getListByGameId($platform, $game_id,$pageIndex,$pageSize);
		$out = array();
		foreach($result as $row){
			$tmp = array();
			$tmp['vid'] = $row['id'];
			$tmp['title'] = $row['title'];
			$tmp['image'] = Utility::getImageUrl($row['ico']);
			$tmp['url'] = $row['video'];
			$tmp['anchor'] = $row['writer'];
			$tmp['duration'] = $row['duration'] ? : '';
			$tmp['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[] = $tmp;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 视频列表
	 */
    public static function getVideoListByCond($platform,$type_id,$isTop,$sort,$pageIndex=1,$pageSize=10)
	{
		$total = Video::getCount($platform,$type_id,$isTop);
		$result = Video::getList($platform,$type_id,$isTop,$pageIndex,$pageSize, $sort);
		$out = array();
		foreach ($result as $row){
			$tmp = array();
			$tmp['vid'] = $row['id'];
			$tmp['title'] = $row['vname'];
			$tmp['image'] = Utility::getImageUrl($row['litpic']);
			//$tmp['type_id'] = $row['type'];
			$tmp['desc'] = $row['description'];
			$tmp['times'] = $row['viewtimes'];
			$tmp['commenttimes'] = $row['commenttimes'];
			$tmp['anchor'] = $row['writer'];
			$tmp['duration'] = $row['duration'];//时长
			$tmp['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[] = $tmp;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 视频详情
	 */
	public static function getVideoDetail($platform,$vid)
	{
		$video = Video::getInfoById($platform,$vid);
		if(!$video) return self::ERROR_VIDEO_NOT_EXISTS;
		$type = $video['type'];
		$pre_vid = Video::getPreId($platform,$vid,$type);
		$next_vid = Video::getNextId($platform,$vid,$type);
		
		$v['vid'] = $video['id'];
		$v['title'] = $video['vname'];
		$v['type'] = $video['type'];
		$v['score'] = $video['score'];
		$v['image'] = Utility::getImageUrl($video['litpic']);
		$v['url'] = $video['video'];
		$v['anchor'] = $video['writer'];
		$v['desc'] = $video['description'];
		$v['updatetime'] = date("Y-m-d", $video['addtime']);						
		$v['pre_vid'] = $pre_vid;
		$v['next_vid'] = $next_vid;
		$v['times'] = $video['viewtimes'];
		$v['duration'] = $video['duration'];//时长		
		$out['video'] = $v;
		$gid = VideoGame::getGameIdByVid($platform, $vid);
		if($gid){
			$game = $platform=='ios' ? IosGame::getInfoById($gid) : AndroidGame::getInfoById($gid);
			if($game){
				$gametype = GameType::getListToKeyValue();
				$out['game'] = array(
				    'gid'=>$game['id'],
				    'gname'=>$game['shortgname'],
				    'img'=>Utility::getImageUrl($game['ico']),
				    'typename'=>isset($gametype[$game['type']]) ? $gametype[$game['type']] : '',
				    'language'=>GameService::$languages[$game['language']],
				    'score'=>$game['score'],
				    'size'=>$game['size'],
				    'downcount'=>$game['downtimes']
				);
			}
		}
		if(!isset($out['game'])) $out['game'] = array();
		return $out;
	}
	
/**
	 * 视频详情
	 */
	public static function getGameVideoDetail($platform,$vid)
	{
		$video = GameVideo::getInfoById($platform,$vid);
		if(!$video) return self::ERROR_VIDEO_NOT_EXISTS;
		$type = $video['type'];
		$pre_vid = GameVideo::db()->where('id','<',$vid)->orderBy('id','desc')->select('id')->pluck('id');
		$next_vid = GameVideo::db()->where('id','>',$vid)->orderBy('id','asc')->select('id')->pluck('id');
		
		$v['vid'] = $video['id'];
		$v['title'] = $video['title'];
		$v['type'] = $video['type'];
		$v['score'] = '5.0';
		$v['image'] = Utility::getImageUrl($video['ico']);
		$v['url'] = $video['video'];
		$v['anchor'] = $video['writer'];
		$v['desc'] = '';
		$v['updatetime'] = date("Y-m-d", $video['addtime']);						
		$v['pre_vid'] = $pre_vid;
		$v['next_vid'] = $next_vid;
		$v['times'] = $video['viewtimes'];
		$v['duration'] = $video['duration']?:'';//时长		
		$out['video'] = $v;
		$gid = $video['gid'];
		if($gid){
			$game = $platform=='ios' ? IosGame::getInfoById($gid) : AndroidGame::getInfoById($gid);
			if($game){
				$gametype = GameType::getListToKeyValue();
				$out['game'] = array(
				    'gid'=>$game['id'],
				    'gname'=>$game['shortgname'],
				    'img'=>Utility::getImageUrl($game['ico']),
				    'typename'=>isset($gametype[$game['type']]) ? $gametype[$game['type']] : '',
				    'language'=>GameService::$languages[$game['language']],
				    'score'=>$game['score'],
				    'size'=>$game['size'],
				    'downcount'=>$game['downtimes']
				);
			}
		}
		if(!isset($out['game'])) $out['game'] = array();
		return $out;
	}

	public static function getArticleNumber($channel,$time)
	{
		$number = 0;
		switch($channel){
			case self::CHANNEL_NEWS:
				$number = News::db()->where('addtime','>',$time)->count();
				break;
			case self::CHANNEL_NEWGAME:
				$number = NewGame::db()->where('addtime','>',$time)->count();
				break;
			case self::CHANNEL_OPINION:
				$number = Opinion::db()->where('addtime','>',$time)->count();
				break;
			case self::CHANNEL_GUIDE:
				$number = Guide::db()->where('addtime','>',$time)->count();
				break;
			case 'video':
				$number = Video::db()->where('addtime','>',$time)->count();
				break;
			default:
				break;
		}
		return $number;
	}

	public static function getGameArticleNumber($gids)
	{
		$news_num = News::db()->whereIn('gid',$gids)->where('pid','<=',0)->groupBy('gid')->select(News::raw('gid,count(*) as total'))->lists('total','gid');
		$guide_num = Guide::db()->whereIn('gid',$gids)->where('pid','<=',0)->groupBy('gid')->select(Guide::raw('gid,count(*) as total'))->lists('total','gid');
		$opinion_num = Opinion::db()->whereIn('gid',$gids)->groupBy('gid')->select(Opinion::raw('gid,count(*) as total'))->lists('total','gid');
		$video_num = Video::db()->whereIn('gid',$gids)->groupBy('gid')->select(Video::raw('gid,count(*) as total'))->lists('total','gid');
		$out = array();
		foreach($gids as $gid){
			$out[] = array(
				'gid'=>$gid,
				'news_number'=>isset($news_num[$gid]) ? $news_num[$gid] : 0,
				'guide_number'=>isset($guide_num[$gid]) ? $guide_num[$gid] : 0,
				'opinion_number'=>isset($opinion_num[$gid]) ? $opinion_num[$gid] : 0,
				'video_number'=>isset($video_num[$gid]) ? $video_num[$gid] : 0,
			);
		}

		return $out;
	}
	
}