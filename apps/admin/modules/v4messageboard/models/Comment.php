<?php
namespace modules\v4messageboard\models;

use Illuminate\Support\Facades\Config;
use Youxiduo\Helper\Utility;

class Comment extends BaseHttp
{
    public static function search($search,$pageIndex=1,$pageSize=10)
    {
        $out = array('result'=>array(),'totalCount'=>0);
        $apiurl = Config::get(self::HOST_URL) . 'module_forum/get_reply_comment';
        $params = array(
            'type'=>'COMMON_SHARE',
            'hasComment'=>'false',
            'orderBy'=>2,
            'isLoadCount'=>'true'
        );
        if($search['replier']) $params['replier'] = $search['replier'];
        if($search['tid']) $params['tid'] = $search['tid'];
        if($search['startTime']) $params['startTime'] = $search['startTime'] . ' 00:00:00';
        if($search['endTime']) $params['endTime'] = $search['endTime'] . ' 23:59:59';
        if($search['score']) $params['score'] = $search['score'];
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;

        $result = self::http($apiurl,$params);
        if($result['errorCode']==0 && $result['result']){
            $res = $result['result']['replys'];
            foreach($res as $key=>$row){
                $row['content'] = json_decode($row['content'],true);
                $res[$key] = $row;
            }
            return array('result'=>$res,'totalCount'=>$result['totalCount']);
        }
        return $out;
    }

    public static function doRecommend($id,$tid,$replier)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_forum/set_best_reply';
        $params = array(
            'tid'=>$tid,
            'uid'=>$replier,
            'replyId'=>$id,
            'type'=>'COMMON_SHARE',
            'isBest'=>'true'
        );
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0){
            return true;
        }
        print_r($result);exit;
    }
    
    public static function doDel($id,$uid)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_forum/del_reply';
        $params = array(
            'id'=>$id,
            'uid'=>$uid,
            'isAdmin'=>'true',
            'isDel'=>'false',
        );
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0){
            return true;
        }
        print_r($result);exit;
    }

    public static function doUnRecommend($id,$tid,$replier)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_forum/set_best_reply';
        $params = array(
            'tid'=>$tid,
            'uid'=>$replier,
            'replyId'=>$id,
            'type'=>'COMMON_SHARE',
            'isBest'=>'false'
        );
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0){
            return true;
        }
        print_r($result);exit;
    }
    
    public static function getReplyDetail($replyId,$pageIndex=1,$pageSize=10)
    {
        $out = array('result'=>array());
        $apiurl = Config::get(self::HOST_URL) . 'module_forum/reply_detail';
        $params = array(
            'replyId'=>$replyId,
            'pageIndex'=>$pageIndex,
            'pageSize'=>$pageSize
        );
        
        $result = self::http($apiurl,$params);
        if($result['errorCode']==0 && $result['result']){
            $res = $result['result'];
            return array('result'=>$res);
        }
        return $out;
        
    }
    
    public static function add_reply($data)
    {
        $result = Utility::loadByHttp(Config::get(self::HOST_URL).'module_forum/add_reply',$data,'POST');
        if($result['errorCode']==0){
            return true;
        }
        print_r($result);exit;
    }
    
    public static function update_reply($data)
    {
        $result = Utility::loadByHttp(Config::get(self::HOST_URL).'module_forum/update_reply',$data,'POST');
        if($result['errorCode']==0){
            return true;
        }
        print_r($result);exit;
    }

}