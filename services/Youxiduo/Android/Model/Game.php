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
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\Config;
use Youxiduo\Helper\Utility;
/**
 * 游戏模型类
 */
final class Game extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	/**
	 * 首页热门推荐
	 */
	public static function getHomeList($limit)
	{
		$field = array();
		$result = self::db()->where('isdel','=',0)->where('flag','=',1)->where('advpic','!=','')
		->orderBy('isapptop','desc')
		->orderBy('recommendsort','desc')
		->orderBy('addtime','desc')
		->forPage(1,$limit)
		->get();
		
		return $result;
	}

    public static function getCountByIds($ids){
        if(!$ids) return array();
        $ids = array_unique($ids);
        $count = self::db()->where('isdel','=',0)->whereIn('id',$ids)->count();
        return $count;
    }
    
    public static function getGidsByGnames(array $gname_list)
    {
    	if(!$gname_list) return array();
    	$gname_list = array_unique($gname_list);
    	return  self::db()->where('isdel','=',0)->whereIn('shortgname',$gname_list)->lists('id');		
    }
	
	public static function getListByIds($ids)
	{
		if(!$ids) return array();
		$ids = array_unique($ids);
		$result = self::db()->where('isdel','=',0)->whereIn('id',$ids)->get();
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}

    public static function getListPageByIds($ids,$pageIndex=1,$pageSize=15)
    {
        if(!$ids) return array();
        $ids = array_unique($ids);
        $result = self::db()->where('isdel','=',0)->whereIn('id',$ids)->forpage($pageIndex,$pageSize)->get();
        $out = array();
        foreach($result as $row){
            $out[$row['id']] = $row;
        }
        return $out;
    }
	
	public static function getListByType($type,$pageIndex,$pageSize)
	{
		
	}
	
	public static function getCountByTypeGroup()
	{
		$result = self::db()->where('isdel','=',0)
		->groupBy('type')
		->select(self::raw('type,count(*) as total'))
		->lists('total','type');
		
		return $result;
	}

    public static function getGames($order,$sort,$pageIndex,$pageSize,$gids=null,$gametype=0,$pricetype=0)
    {
        $query = self::db()->where('isdel',0);
        if($gids) $query->whereIn('id',$gids);
        if($gametype) $query = $query->where('type',$gametype);
        if($pricetype) $query = $query->where('pricetype',$pricetype);
        $count = $query->count();
        $rs = $query->orderBy($order,$sort)->forPage($pageIndex,$pageSize)->get();
        $result = array('rs'=>$rs,'count'=>$count);
        return $result;
    }

    public static function getGamesByTag($tag,$pageIndex,$pageSize,$order,$sort,$pricetype,$gametype){
        $result = array();
        $agids = GameTag::getGameByTag($tag);
        $result = self::getGames($order,$sort,$pageIndex,$pageSize,$agids,$gametype,$pricetype);
        return $result;
    }

    public static function _exportGamesRes($res, $week='')
    {
        $out = array();
        foreach ($res as $k => $v){
            $out[$k]['gid'] = $v['id'];
            $out[$k]['title'] = $v['shortgname'];
            $out[$k]['img'] = Utility::getImageUrl($v['ico']);
            $out[$k]['comment'] = $v['shortcomt'];
            $tmp[$k] = GameVideo::getGameVideos($v['id']);
            if ($tmp[$k]){
                $out[$k]['video'] = true;
            }else{
                $out[$k]['video'] = false;
            }
            if ($v['pricetype'] == 1){
                $out[$k]['free'] = true;
                $out[$k]['limitfree'] = false;
            }
            if ($v['pricetype'] == 2){
                $out[$k]['free'] = false;
                $out[$k]['limitfree'] = true;
            }
            if ($v['pricetype'] == 3){
                $out[$k]['free'] = false;
                $out[$k]['limitfree'] = false;
            }
            $out[$k]['summary'] = $v['editorcomt'];
            $out[$k]['size'] = $v['size'];
            $out[$k]['score'] = $v['score'];//gamescore_count($v['id']);
            $out[$k]['oldprice'] = isset($v['oldprice']) && $v['oldprice'] ? $v['oldprice'] : '0.0';
            $out[$k]['price'] = isset($v['price']) && $v['price'] ? $v['price'] : '0.0';

            $out[$k]['guide'] = false;
            if(Guide::getCountByGameIds(array($v['id']))) $out[$k]['guide'] = true;
            $out[$k]['opinion'] = false;
            if(Opinion::getCountByGameIds(array($v['id']))) $out[$k]['opinion'] = true;

            $out[$k]['zone'] = $v['zonetype'];
            $type =  GameType::getInfoById($v['type']);
            $out[$k]['tname'] = $type['typename'];
            if ($week) {
                $out[$k]['downcount'] = $v['weekdown'];//$v['isup'] ? $v['weekdown'] : 0;
            } else {
                $out[$k]['downcount'] = $v['downtimes'];//$v['isup'] ? $v['downtimes'] : 0;
            }
            $out[$k]['commentcount'] = 0;
            $comments = Comment::getCountByGameIds(array($v['id']));
            if($comments && $comments[$v['id']]){
                $out[$k]['commentcount'] = $comments[$v['id']];
            }
            $out[$k]['hot'] = $v['ishot'];
            $out[$k]['week'] = $week;
        }
        return $out;
    }

    public static function getGameLanguageName($type)
    {
        if ($type == '1'){
            $language = "中文";
        }elseif ($type == '2'){
            $language = "英文";
        }elseif ($type == '3'){
            $language = "其他";
        }else{
            $language = "其他";
        }
        return $language;
    }

    /**
     * 猜你喜欢(同类型游戏)
     */
    public static function guessYouLike($gtype)
    {
        $games = array();
        $out = array();
        $sametype_games = self::db()->where('type','=',$gtype)->where('isdel','=',0)->select('id','shortgname','ico')->forPage(1,100)->get();
        if ($sametype_games){
            if (count($sametype_games) > 5){
                $sametype_games_rand = array_rand($sametype_games, 5);
                foreach ($sametype_games_rand as $v){
                    $games[] = $sametype_games[$v];
                }
            }else{
                $games = $sametype_games;
            }
        }
        foreach ($games as $k3 => $v3){
            $out[$k3]['gid'] = $v3['id'];
            $out[$k3]['title'] = $v3['shortgname'];
            $out[$k3]['img'] = Utility::getImageUrl($v3['ico']);
        }

        return $out;
    }

    public static function getDevOtherGames($company)
    {
        $games = array();
        $out = array();
        $company_games = self::db()->where('company','=',$company)->where('isdel','=',0)->select('id','shortgname','ico')->forPage(1,50)->get();
        if ($company_games){
            if (count($company_games) > 5){
                $company_games_rand = array_rand($company_games, 5);
                foreach ($company_games_rand as $v){
                    $games[] = $company_games[$v];
                }
            }else{
                $games = $company_games;
            }
        }
        foreach ($games as $k3 => $v3){
            $out[$k3]['gid'] = $v3['id'];
            $out[$k3]['title'] = $v3['shortgname'];
            $out[$k3]['img'] = Config::get('app.img_url').$v3['ico'];
        }
        return $out;
    }

    public static function search($keyword,$pageIndex=1,$pageSize=10)
    {
        $fields = array('id', 'shortgname', 'ico', 'score', 'size', 'pricetype', 'type', 'downtimes');
        return self::db()->where(function($query)use($keyword){
            $query = $query->where('gname','=',$keyword)->orWhere('gname','like','%'.$keyword.'%');
        })->where('isdel','=',0)->orderBy('gname','asc')->orderBy('score','desc')->select($fields)->forPage($pageIndex,$pageSize)->get();
    }
    
    
    public static function m_search($search,$pageIndex=1,$pageSize=15,$sort=array())
    {
    	$out = array();
		$out['total'] = self::m_buildSearch($search)->count();
		$tb = self::m_buildSearch($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		if(!$sort){
			$tb = $tb->orderBy('id','desc');
		}
		$out['result'] = $tb->get();
		return $out;
    }
    
    public static function m_buildSearch($search)
	{
		$tb = self::db();
		$tb = $tb->where('isdel','=',0);
		if(isset($search['type'])){
			$tb = $tb->where('type','=',$search['type']);
		}
	    if(isset($search['zonetype'])){
			$tb = $tb->where('zonetype','=',$search['zonetype']);
		}
		if(isset($search['id']) && !empty($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		
	    if(isset($search['gname']) && !empty($search['gname'])){
			$tb = $tb->where('gname','like','%'.$search['gname'].'%');
		}
		
		return $tb;
	}
	
    public static function m_getInfo($id)
	{
		if(!$id) return array();
		$query = self::db();
		if($id) $query->where('id',$id);
		$result = $query->first();		
		return $result;
	}
	/**
	 * 通过游戏名称进行反查游戏信息
	 * @param string $gname
	 * @return array
	 */
	public static function mname_getInfo($gname)
	{
		if(!$gname) return array();
		$query = self::db();
		if($gname) $query->where('gname',$gname);
		$result = $query->first();
		return $result;
	}
	
	/**
	 * 查询游戏名和ID
	 * @param array $data
	 */
	public static function getGameList($data){
		return self::db()->select('id', 'gname')->whereIn('id',$data)->get();
	}
	
    /**
     * 游戏下载统计
     */
    public static function downloadCount($gid,$num){
        $query = self::db()->where('id',$gid)->where('isup',1);
        $query->increment('downtimes',$num);
        $query->increment('weekdown',$num);
        $query->increment('realdown');
    }
}