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
/**
 * 游戏视频模型类
 */
final class CommentOperator extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}

    /**
     * 获取用户是否对每条评论进行过点赞操作
     */
    public static function getCommOperaData($uid,$gid,$vid)
    {
        return self::db()->where('uid','=',$uid)->where('gid','=',$gid)->where('vid','=',$vid)->get();
    }

    /**
     * 判断评论是否进行过顶踩操作
     */
    public static function commOperaFlag($uid,$cid)
    {
        return self::db()->where('cid','=',$cid)->where('uid','=',$uid)->first();
    }

    /**
     * 更新数据
     * @param $where
     *  array('id'=>array('=',1),'uid'=>array('=',1));
     * @param $data
     *  array('vid'=>1,'uid'=>1)
     */
    public static function save(array $where,array $data)
    {
        $tb = self::db();
        if(!$data) return false;
        if($where){
            foreach($where as $k=>$v){
                if(is_array($v)){
                    $tb = $tb->where($k,$v[0],$v[1]);
                }else{
                    $tb = $tb->where($k,'=',$v);
                }
            }
            $rs = $tb->update($data);
        }else{
            $rs = $tb->insert($data);
        }
        return (bool)$rs;
    }
}