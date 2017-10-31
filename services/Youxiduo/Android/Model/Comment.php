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
use Youxiduo\User\Model\Account;
/**
 * 游戏视频模型类
 */
final class Comment extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		return self::db()->whereIn('agid',$gids)->where('pcid','=',0)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
	}

    public static function getCountByGameOrVideo($gid,$vid)
    {
        $query = self::db()->where('pcid',0)->where('ptype','!=',1);
        if($gid){
            $query = $query->where('agid','=',$gid);
            return $query->count();
        }elseif($vid){
            $query = $query->where('vid','=',$vid);
            return $query->count();
        }
        return 0;
    }

    public static function getListByGameOrVideo($gid,$vid,$uid,$order,$sort,$pageIndex,$pageSize)
    {
        $query = self::db()->where('pcid','=',0)->where('ptype','!=',1);
        $rs = array();
        if($gid){
            $query = $query->where('agid','=',$gid);
            $rs = $query->orderBy($order,$sort)->forPage($pageIndex,$pageSize)->get();
        }elseif($vid){
            $query = $query->where('vid','=',$vid);
            $rs = $query->orderBy($order,$sort)->forPage($pageIndex,$pageSize)->get();
        }
        return self::parseData($rs,$uid,$gid,$vid);
        return $rs;
    }

    public static function getHotComment($gid,$vid,$uid)
    {
        $query = self::db()->where('up','>',10)->where('pcid','=',0)->where('ptype','!=',1);
        $rs = array();
        if($gid){
            $query = $query->where('agid','=',$gid);
            $rs = $query->orderBy('up','desc')->take(3)->get();
        }elseif($vid){
            $query = $query->where('vid',$vid);
            $rs = $query->orderBy('up','desc')->take(3)->get();
        }
        return self::parseData($rs,$uid,$gid,$vid);
    }

    public static function parseData($rs,$uid,$gid,$vid)
    {
        $out = array();
        $commOpera = CommentOperator::getCommOperaData($uid,$gid,$vid);
        if($commOpera){
            foreach ($commOpera as $v){
                $upDownData[$v['cid']][0] = $v['comment_up_status'];
                $upDownData[$v['cid']][1] = $v['comment_down_status'];
            }
        }
        if($rs){
            foreach ($rs as $k => $v){
                $out[$k]['cid'] = $v['id'];
                $out[$k]['uid'] = $v['uid'];

                //$row = User::getDetailById($v['uid']);
                $row = Account::getUserInfoById($uid);
                if ($row){
                    $out[$k]['avatar'] = Utility::getImageUrl($row['avatar']);
                }else{
                    $out[$k]['avatar'] = '';
                }
                $out[$k]['nick'] = $row["nickname"] ? $row["nickname"] : '玩家'.$v['uid'];
                $out[$k]['mobile'] = $v['mobile'];
                $out[$k]['content'] = $v['content'];
                $out[$k]['updatetime'] = date("Y-m-d H:i:s", $v['addtime']);
                $out[$k]['reply'] = Comment::getReplyCount($v['id']);
                $out[$k]['up'] = $v['up'];
                $out[$k]['down'] = $v['down'];

                $out[$k]['up_status'] = isset($upDownData[$v['id']][0]) && $upDownData[$v['id']][0] ? true : false;
                $out[$k]['down_status'] = isset($upDownData[$v['id']][1]) && $upDownData[$v['id']][1] ? true : false;
            }
        }
        return $out;
    }

    public static function getReplyCount($pcid)
    {
        return self::db()->where('pcid','=',$pcid)->count();
    }

    public static function getReplys($pcid,$order='addtime',$sort='desc',$pageIndex=1,$pageSize=15)
    {
        $result = array('rs'=>array(),'count'=>0);
        $query = self::db()->where('pcid','=',$pcid)->where('ptype','!=',1);
        $count = $query->count();
        $rs = $query->orderBy($order,$sort)->forPage($pageIndex,$pageSize)->get();
        $result['rs'] = $rs;
        $result['count'] = $count;
        return $result;
    }

    public static function upOrDown($uid,$cid,$updown)
    {
        $commoperaInfo = CommentOperator::commOperaFlag($uid,$cid);
        $result = 0;
        switch($updown){
            case 2:
                if(!$commoperaInfo || $commoperaInfo['comment_down_status'] == 0){
                    $result = self::db()->where('id','=',$cid)->increment('down');
                }
                break;
            case 1:
            default:
                if(!$commoperaInfo || $commoperaInfo['comment_up_status'] == 0){
                    $result = self::db()->where('id','=',$cid)->increment('up');
                }
                break;
        }
        if($result) return $result;
        else return false;
    }

    public static function commentOpeartor($uid,$cid,$updown)
    {
        $rs = self::db()->where('id','=',$cid)->first();
        if(!$rs) return false;

        $data['gid'] = $rs['agid'];
        $data['vid'] = $rs['vid'];
        $data['uid'] = $uid;
        $data['cid'] = $cid;
        $updown == 1 ? $data['comment_up_status'] = 1 : $data['comment_down_status'] = 1;
        $data['operator_time'] = time();
        $commentOperaData = CommentOperator::commOperaFlag($uid,$cid);
        if($commentOperaData){
            $updown == 1 ? $uData['comment_up_status'] = 1 : $uData['comment_down_status'] = 1;
            $map = array('cid'=>array('=',$cid),'uid'=>array('=',$uid));
            return CommentOperator::save($map,$uData);
        }else{
            return CommentOperator::save(array(),$data);
        }
    }

    public static function save(array $data){
        if($data)
            return self::db()->insertGetId($data);
        else
            return false;
    }
}