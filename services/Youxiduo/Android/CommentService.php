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

use Youxiduo\Android\Model\GameScore;

use Youxiduo\Android\Model\SystemConfig;
use Youxiduo\Android\Model\User;

use Youxiduo\Android\Model\Comment;

use Illuminate\Support\Facades\Config;
use Youxiduo\Android\Model\UserDisable;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;

class CommentService extends BaseService
{

    /**
     * 获取游戏/视频评论列表
     */
    public static function getComment($pageIndex,$pageSize,$order,$sort,$gid,$vid,$uid)
    {
        $order_field = 'addtime';
    	if ($order == 1) $order_field = 'hot';
        if ($order == 2) $order_field = 'addtime';
        
        $order_sort = 'desc';
        if ($sort == 1) $order_sort = 'asc';
        if ($sort == 2) $order_sort = 'desc';
        

        $out = array();
        $totalCount = 0;
        if($order_field=='hot'){
            $out = Comment::getHotComment($gid,$vid,$uid);
        }else{
            $out = Comment::getListByGameOrVideo($gid,$vid,$uid,$order_field,$order_sort,$pageIndex,$pageSize);
        }
        $totalCount = Comment::getCountByGameOrVideo($gid,$vid);

        return self::trace_result(array('result'=>$out,'totalCount'=>$totalCount));
    }

    /**
     * 获取评论回复列表
     */
    public static function getReplysByComment($pcid,$sort,$pageIndex,$pageSize)
    {
        $order = 'addtime';
        if ($sort == 1){
            $sort = 'asc';
        }elseif ($sort == 2){
            $sort = 'desc';
        }else{
            $sort = 'desc';
        }

        $result = Comment::getReplys($pcid,$order,$sort,$pageIndex,$pageSize);
        $rs = $result['rs'];
        $totalCount = $result['count'];
        $out = array();
        if($rs){
            foreach ($rs as $k => $v){
                $out[$k]['cid'] = $v['id'];
                $out[$k]['uid'] = $v['uid'];
                $row = User::getDetailById($v['uid']);
                if ($row){
                    $out[$k]['avatar'] = Config::get('app.img_url') . '/u'.$row["avatar"];
                    $out[$k]['nick'] = $row["nick"];
                }else{
                    $out[$k]['avatar'] = '';
                    $out[$k]['nick'] = '玩家'.$v['uid'];
                }
                $out[$k]['mobile'] = $v['mobile'];
                $out[$k]['content'] = $v['content'];
                $out[$k]['updatetime'] = date("Y-m-d H:i:s", $v['addtime']);
            }
        }
        return self::trace_result(array('result'=>$out,'totalCount'=>$totalCount));
    }

    /**
     * 顶/踩一条评论
     */
    public static function setCommentUpOrDown($uid,$cid,$gid,$vid,$updown)
    {
        $rs = Comment::upOrDown($uid,$cid,$updown);
        //记录用户对评论顶采操作
        if($rs){
            Comment::commentOpeartor($uid,$cid,$updown);
        }else{
            return self::trace_error('E1','已进行顶/采操作');
        }
    }

    /**
     * 回复评论
     */
    public static function reply($input)
    {
        $pcid = (int)$input['cid'];
        $uid = (int)$input['uid'];
        $vid = (int)$input['vid'];
        $agid = (int)$input['gid'];
        $gid = 0;
        $content = $input['content'];
        $mobile = $input['mobile'];
        $nick = $input['nick'];

        if($pcid<=0 || $uid<=0 || ($vid<=0 && $agid<=0) || empty($content) ){
            return self::trace_error('E11');
        }

        if(strlen($nick)> 32 || strlen($content) > 280){
            return self::trace_error('E32');
        }

        $res = UserDisable::getInfoById($uid);
        if($res){
            //用户被禁言
            return self::trace_error('E1','您休息会吧');
        }

        $ip_blacklist = SystemConfig::getIpBlackList();
        $cur_ip = Utility::get_real_ip();
        //ip黑名单
        if($ip_blacklist && trim($ip_blacklist)!= ''){
            $blacklist = explode(',', $ip_blacklist['value']);
            if(in_array($cur_ip, $blacklist)){
                return self::trace_error('E1','您休息会吧');
            }
        }
        $nick = strip_tags($nick);
        $content = strip_tags($content);
        //对当前内容进行敏感关键字过滤
        $content = Utility::_filterCommentStr($content);

        $data['pcid'] = $pcid;
        $data['uid'] = $uid;
        $data['nick'] = $nick;
        $data['agid'] = $agid;
        $data['gid'] = $gid;
        $data['vid'] = $vid;
        $data['mobile'] = $mobile;
        $data['addtime'] = time();
        $data['content'] = $content;

        if(Comment::save($data)){
            self::trace_result();
        }
        self::trace_error('E12');


    }

    /**
     * 发布评论
     */
    public static function comment($input)
    {
        $uid = (int)$input['uid'];
        $vid = (int)$input['vid'];
        $agid = (int)$input['gid'];
        $gid = 0;
        $content = $input['content'];
        $mobile = $input['mobile'];
        $nick = $input['nick'];

        if($uid<=0 || ($vid<=0 && $agid<=0) || empty($content) ){
            return self::trace_error('E11');
        }

        if(strlen($nick)> 32 || strlen($content) > 280){
            return self::trace_error('E32');
        }

        $res = UserDisable::getInfoById($uid);
        if($res){
            //用户被禁言
            return self::trace_error('E1','您休息会吧');
        }

        $ip_blacklist = SystemConfig::getIpBlackList();
        $cur_ip = Utility::get_real_ip();
        //ip黑名单
        if($ip_blacklist && trim($ip_blacklist)!= ''){
            $blacklist = explode(',', $ip_blacklist['value']);
            if(in_array($cur_ip, $blacklist)){
                return self::trace_error('E1','您休息会吧');
            }
        }
        $nick = strip_tags($nick);
        $content = strip_tags($content);
        //对当前内容进行敏感关键字过滤
        $content = Utility::_filterCommentStr($content);

        $data['uid'] = $uid;
        $data['nick'] = $nick;
        $data['agid'] = $agid;
        $data['gid'] = $gid;
        $data['vid'] = $vid;
        $data['mobile'] = $mobile;
        $data['addtime'] = time();
        $data['content'] = $content;

        if(Comment::save($data)){
            self::trace_result();
        }
        self::trace_error('E12');
    }
    
    public static function doScore($gid,$uid,$score)
    {
    	$exists = GameScore::db()->where('agid','=',$gid)->where('uid','=',$uid)->first();
    	if($exists){
    		return self::trace_error('E1','您已经对该游戏评过分了');
    	}
    	$data = array('agid'=>$gid,'gid'=>0,'uid'=>$uid,'score'=>$score,'addtime'=>time());
    	GameScore::db()->insert($data);
    	return self::trace_result();
    }

}


