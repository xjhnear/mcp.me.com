<?php
namespace Youxiduo\Bbs;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Youxiduo\Base\BaseService;
use Youxiduo\Bbs\Model\BbsAppend;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;

class TopicService extends BaseService{

    const API_URL_CONF = 'app.ios_core_api_url';
    const API_RELATE_CONF = 'app.game_forum_api_url';
    const API_PHONE_CONF = 'app.android_phone_api_url';
    const API_V4_CONF = 'app.php_v4_module_api_url';

    /***
     * 帖子限制回复条数修改
     **
     * @param array $arr
     * @return bool|mixed|string
     */
    public static function edit_replylimit($arr=array())
    {
        $arr = self::arr_filter($arr);
        $res =  Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_replyLimit',$arr,'POST');
        return $res;
    }

    /***
     * 帖子限制回复条数添加
     **
     * @param array $arr
     * @return bool|mixed|string
     */
    public static function add_replylimit($arr=array()){
        $arr = self::arr_filter($arr);
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_replylimit',$arr,'POST');
    }

    /***
     * 帖子限制回复
     * 空数据转为null
     */
    public static function arr_filter($arr){
        if($arr){
            foreach($arr as $k=>$val){
                if($val==""){
                    $arr[$k] = null;
                }
            }
        }
        return $arr;
    }

    /***
     * 帖子限制回复条数列表
     **
     * @param array $params
     * @return bool|mixed|string
     */
    public static function replylimitList($params=array()){
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/replylimit_list',$params);
    }

    /***
     * 帖子限制回复条数删除
     **
     * @param array $params
     * @return bool|mixed|string
     */
    public static function del_replylimit($params=array()){
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/del_replylimit',$params);
    }

    /**
     * 新建社区
     * @param $name 社区名称
     * @param string $logo
     * @return bool|mixed|string
     */
    public static function addForum($name,$logo=''){
       return   Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_forum',array('name'=>$name,'logo'=>$logo),'POST');
    }
    
    /**
     * 新建社区
     * @param $name 社区名称
     * @param string $logo
     * @return bool|mixed|string
     */
    public static function updateForum($fid,$name,$logo='')
    {
        return   Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_forum',array('fid'=>$fid,'name'=>$name,'logo'=>$logo),'POST');
    }

    /**
     * 删除论坛
     * @param string $fid
     * @return bool|mixed|string
     * @internal param 社区名称 $name
     * @internal param 社区logo $logo
     */
    public static function deleteForum($fid=''){
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/delete_forum',array('fid'=>$fid));
    }

    /*
     * 
     */
    public static function openForum($fid,$status,$fromTag)
    {
    	return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/open_forum',array('fid'=>$fid,'isOpen'=>$status,'fromTag'=>$fromTag));
    }


    /**
     * 保存游戏与社区关系
     * @param $fid  社区id
     * @param $gid  游戏id
     * @param $genre    所属平台1.ios 2.android
     * @return bool|mixed|string
     */
    public static function saveForumAndGameRelation($fid,$gid,$genre){
        $params = array('fid'=>$fid,'gid'=>$gid,'genre'=>$genre);
        $result = Utility::loadByHttp(Config::get(self::API_RELATE_CONF).'save_forum_game',$params,'POST');
        return $result;
    }

    /**
     * 保存游戏与社区关系2
     * @param $fid  社区id
     * @param $gid  游戏id
     * @param $genre    所属平台1.ios 2.android
     * @return bool|mixed|string
     */
    public static function add_game_link($fid,$gid,$genre,$isOpen="false"){
        $params = array('fid'=>$fid,'gid'=>$gid,'fromTag'=>$genre,'isOpen'=>$isOpen);
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_game_link',$params,'POST');
        return $result;
    }

    /**
     * 删除游戏与社区关系
     * @param $fid  社区id
     * @param $gid  游戏id
     * @param $genre    所属平台1.ios 2.android
     * @return bool|mixed|string
     */
    public static function delForumAndGameRelation($fid,$gid,$genre){
        $params = array('fid'=>$fid,'gid'=>$gid,'genre'=>$genre);
        $result = Utility::loadByHttp(Config::get(self::API_RELATE_CONF).'del_forum_game',$params,'GET');
        return $result;
    }
    /**
     * 删除游戏与社区关系2
     */
    public static function del_game_link($linkId){
        $params = array('gameLinkId'=>$linkId);
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/del_game_link',$params,'GET');
        return $result;
    }

    /**
     * @param string $name 社区名称
     * @param int $pageIndex 当前页
     * @param int $pageSize 每页几条
     * @param bool $platform
     * @param bool $fid
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function getForums($name='',$pageIndex=1,$pageSize=10,$platform=false,$fid=false,$hashValue=false,$displayType=2){
        $params = array(
            'name' => $name,
            'pageIndex' => $pageIndex,
            'pageSize' => $pageSize,
            'displayType' => "2"
        );

        if($platform) $params['platform'] = $platform;
        if($fid) $params['fid'] = $fid;
        if($hashValue) $params['hashValue'] = $hashValue;
        if($displayType!==null) $params['displayType'] = $displayType;
        $api_url = Config::get(self::API_URL_CONF).'module_forum/search_forum';
        $result = Utility::loadByHttp($api_url,$params);
        return $result;
    }

    /**
     * 获取论坛详情
     * @param $fid
     * @return bool|mixed|string
     */
    public static function getForumDetail($fid){
        $params = array('fid'=>$fid);
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/forum_detail',$params);
        if($result && $result['errorCode'] == 0 && $result['result']){
            $result['result']['top_pic'] = false;
            $result['result']['short_name'] = false;
            $append_info = BbsAppend::getBbsinfoByFid($result['result']['fid']);
            if($append_info){
                $result['result']['top_pic'] = Utility::getImageUrl($append_info['top_pic']);
                $result['result']['short_name'] = $append_info['short_name'];
            }
        }
        return $result;
    }

    /**
     * @param string $name 社区名称
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function getForumsCount($name='',$hashValue=false,$displayType=2){
        $params = array();
        if($name) $params['name'] = $name;
        if($hashValue) $params['hashValue'] = $hashValue;
        if($displayType!==null) $params['displayType'] = $displayType;
        $api_url = Config::get(self::API_URL_CONF).'module_forum/forum_number';
        $result = Utility::loadByHttp($api_url,$params);
        return $result;
    }

    /**
     * 获取版块列表
     * @param string $name
     * @param string $pid
     * @param int $pageIndex
     * @param int $pageSize
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function getBoardList($name='',$pid='',$bid=false,$pageIndex=1,$pageSize=10,$hashValue=false,$displayType=2){
        $params = array();
        if($name) $params['name'] = $name;
        if($pid) $params['pid'] = $pid;
        if($bid) $params['bid'] = $bid;
        if($pageIndex) $params['pageIndex'] = $pageIndex;
        if($pageSize) $params['pageSize'] = $pageSize;
        if($hashValue) $params['hashValue'] = $hashValue;
        if($displayType!==null) $params['displayType'] = $displayType;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/board_list',$params);
        return $result;
    }

    /**
     * 获取游戏和社区关联关系
     * @param $isActive
     * @param $genre
     * @param bool $fid
     * @param bool $gid
     * @param bool $pageIndex
     * @param bool $pageSize
     * @return bool|mixed|string
     */
    public static function getForumAndGameRelation($isActive,$genre,$fid=false,$gid=false,$pageIndex=false,$pageSize=false){
        $params = array('isActive'=>$isActive,'genre'=>$genre);
        if($fid){
            if(is_array($fid)) $fid = implode(',',$fid);
            $params['fid'] = $fid;
        }
        if($gid){
            if(is_array($gid)) $gid = implode(',',$gid);
            $params['gid'] = $gid;
        }
        if($pageIndex) $params['pageIndex'] = $pageIndex;
        if($pageSize) $params['pageSize'] = $pageSize;
        $result = Utility::loadByHttp(Config::get(self::API_RELATE_CONF).'get_forum_game_list',$params);
        return $result;
    }

    /**
     * 获取游戏和社区关联关系2
     * @param $isActive
     * @param $genre
     * @param bool $fid
     * @param bool $gid
     * @param bool $pageIndex
     * @param bool $pageSize
     * @return bool|mixed|string
     */
    public static function query_game_link_list($isActive,$genre,$fid=false,$gid=false,$fromTag="2",$pageIndex=false,$pageSize=false){
        $params = array('isOpen'=>$isActive,'fromTag'=>$fromTag);
        if($fid){
            if(is_array($fid)) $fid = implode(',',$fid);
            $params['fid'] = $fid;
        }
        if($gid){
            if(is_array($gid)) $gid = implode(',',$gid);
            $params['gid'] = $gid;
        }
        if($pageIndex) $params['pageIndex'] = $pageIndex;
        if($pageSize) $params['pageSize'] = $pageSize;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/query_game_link_list',$params,'GET');
        return $result;
    }

    /**
     * 社区帖子总数
     * @param string $fid
     * @param string $bid
     * @param string $createTimeBegin
     * @param string $createTimeEnd
     * @param string $hashValue
     * @param string $tagid
     * @param int $sortType
     * @param bool $isActivity
     * @param bool $isRule
     * @param bool $isGood
     * @param bool $isActive
     * @param string $uid
     * @param string $tid
     * @param string $displayOrder
     * @param bool $isTop
     * @return bool|mixed|string
     */
    public static function getTopicNumber($fid='',$bid='',$createTimeBegin='',$createTimeEnd='',$hashValue='',$tagid='',$sortType=0,$isActivity=false,
                            $isRule=false,$isGood=false,$isActive=false,$uid='',$tid='',$displayOrder='',$isTop=false){
        $params = array();
        if($fid) $params['fid'] = $fid;
        if($bid) $params['bid'] = $bid;
        if($createTimeBegin) $params['createTimeBegin'] = $createTimeBegin;
        if($createTimeEnd) $params['createTimeEnd'] = $createTimeEnd;
        if($hashValue) $params['hashValue'] = $hashValue;
        if($tagid) $params['tagid'] = $tagid;
        if($sortType) $params['sortType'] = $sortType;
        if($isActivity) $params['isActivity'] = $isActivity;
        if($isRule) $params['isRule'] = $isRule;
        if($isGood) $params['isGood'] = $isGood;
        if($isActive) $params['isActive'] = $isActive;
        if($uid) $params['uid'] = $uid;
        if($tid) $params['tid'] = $tid;
        if($displayOrder) $params['displayOrder'] = $displayOrder;
        if($isTop) $params['isTop'] = $isTop;

        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/topic_number',$params);
        return $result;
    }

    /**
     * 获取社区帖子列表
     * @param string $fid 社区id
     * @param number|string $bid 板块id
     * @param string $tagid 标签id
     * @param int|number $pageIndex 第几页，默认第1页
     * @param int|number $pageSize 每页记录数，默认10条
     * @param string $subject 标题
     * @param bool|int|number $sortType 排序方式：0：按照发布时间 1：按照回复数量；2：按围观数（即点赞数）
     * @param bool|string $isActivity 是否是活动
     * @param bool|string $isRule 是否为规则贴
     * @param bool|string $isGood 是否精华帖
     * @param string $isActive 是否有效
     * @param string $uid 用户ID
     * @param string $tid 帖子ID
     * @param string $createTimeBegin 创建时间区间的起始时间
     * @param string $createTimeEnd 创建时间区间的末尾时间
     * @param bool|string $displayOrder 主题显示顺序 2全局公告 1公告 0正常 -1回收站 -2审核中 -3审核忽略 -4草稿
     * @param bool|string $isTop 是否置顶
     * @param int $fromTag 平台 1：ios 2：android 3：web
     * @param bool $iosDisplay
     * @param bool $androidDisplay
     * @param bool $webDisplay
     * @param bool $isAdmin
     * @return mixed
     */
	public static function getPostsList($fid='',$bid='',$tagid='',$pageIndex=1,$pageSize=10,$subject='',$sortType=false,$isActivity=false,
										$isRule='false',$isGood=flase,$isActive='true',$uid='',$tid='',$createTimeBegin='',
										$createTimeEnd='',$displayOrder=false,$isTop=false,$fromTag=0,$iosDisplay=false,$androidDisplay=false,$webDisplay=false,$isAdmin=false){

        if($fid) $params['fid'] = $fid;
        if($bid) $params['bid'] = $bid;
        if($tagid) $params['tagid'] = $tagid;
        if($pageIndex) $params['pageIndex'] = $pageIndex;
        if($pageSize) $params['pageSize'] = $pageSize;
        if($sortType !== false) $params['sortType'] = $sortType;
        if($isActivity) $params['isActivity'] = $isActivity;
        if($isRule) $params['isRule'] = $isRule;
        if($isGood) $params['isGood'] = $isGood;
        if($isActive) $params['isActive'] = $isActive;
        if($uid) $params['uid'] = $uid;
        if($tid) $params['tid'] = $tid;
        if($createTimeBegin) $params['createTimeBegin'] = $createTimeBegin;
        if($createTimeEnd) $params['createTimeEnd'] = $createTimeEnd;
        if($isTop) $params['isTop'] = $isTop;
        if($subject) $params['subject'] = $subject;
//        $displayOrder && $params['displayOrder'] = $displayOrder;
        if($displayOrder == 3){
            $params['startDisplayOrder'] = 1;
            $params['endDisplayOrder'] = 2;
        }else if($displayOrder == 1){
            $params['displayOrder'] = 0;
        }
        $iosDisplay && $params['iosDisplay'] = $iosDisplay;
        $androidDisplay && $params['androidDisplay'] = $androidDisplay;
        $webDisplay && $params['webDisplay'] = $webDisplay;
        $isAdmin && $params['isAdmin'] = $isAdmin;

        $fromTag ? $params['fromTag'] = $fromTag : '' ;
	    $api_url = Config::get(self::API_URL_CONF).'module_forum/topic_list';
		$result = Utility::loadByHttp($api_url,$params);
		return $result;
	}

    /**
     * 帖子总数
     * @param string $fid
     * @param string $bid
     * @param string $uid
     * @param string $tid
     * @param string $isTop
     * @param string $isActive
     * @param string $isGood
     * @param string $isRule
     * @param string $isActivity
     * @param int $sortType
     * @param string $tagid
     * @param string $createTimeBegin
     * @param string $createTimeEnd
     * @param string $subject
     * @param string $iosDisplay
     * @param string $androidDisplay
     * @param string $webDisplay
     * @param bool $displayOrder
     * @return bool|mixed|string
     */
    public static function getPostsNum($fid='',$bid='',$uid='',$tid='',$isTop='',$isActive='',$isGood='',$isRule='',$isActivity='',$sortType=0,$tagid='',$createTimeBegin='',
                                        $createTimeEnd='',$subject='',$iosDisplay='',$androidDisplay='',$webDisplay='',$displayOrder=false,$isAdmin=false){
        $params = array();
        if($fid) $params['fid'] = $fid;
        if($bid) $params['bid'] = $bid;
        if($uid) $params['uid'] = $uid;
        if($tid) $params['tid'] = $tid;
        if($isTop) $params['isTop'] = $isTop;
        if($isActive) $params['isActive'] = $isActive;
        if($isGood) $params['isGood'] = $isGood;
        if($isRule) $params['isRule'] = $isRule;
        if($isActivity) $params['isActivity'] = $isActivity;
        if($sortType !== false) $params['sortType'] = $sortType;
        if($tagid) $params['tagid'] = $tagid;
        if($createTimeBegin) $params['createTimeBegin'] = $createTimeBegin;
        if($createTimeEnd) $params['createTimeEnd'] = $createTimeEnd;
        $subject && $params['subject'] = $subject;
//        $androidDisplay && $params['androidDisplay'] = $androidDisplay;
        if($displayOrder == 3){
            $params['startDisplayOrder'] = 1;
            $params['endDisplayOrder'] = 2;
        }else if($displayOrder == 1){
            $params['displayOrder'] = 0;
        }
        $iosDisplay && $params['iosDisplay'] = $iosDisplay;
        $webDisplay && $params['webDisplay'] = $webDisplay;
        $isAdmin && $params['isAdmin'] = $isAdmin;
//        $displayOrder!==false && $params['displayOrder'] = $displayOrder;
        $api_url = Config::get(self::API_URL_CONF).'module_forum/topic_number';
        $result = Utility::loadByHttp($api_url,$params);
        return $result;
    }
	
	/**
     * 获取指定用户发帖数
     * @param string|array $uids
	 * @return bool|mixed|string
	 */
	public static function getPostsNums($uids){
		if(is_array($uids)) $uids = implode(',',$uids);
		$result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/get_user_topic_number',array('uid'=>$uids));
		return $result;
	}
	
	/**
	 * 获取帖子详情
	 * @param string $tid
	 * @param string $hashValue
	 * @return boolean|mixed
	 */
	public static function getPostDetail($tid,$hashValue=''){
		if(!$tid) return false;
		$params = array(
			'tid' => $tid,
			'hashValue' => $hashValue
		);
		
		$result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/topic_detail',$params,'GET');
		return $result;
	}

    /**
     * @param bool $id  主键id
     * @param bool $targetId    论坛或板块id
     * @param bool $targetType  forum或board
     * @param bool $master  版主uid
     * @param bool $isDeputy    false:正版主 true:副版主
     * @param int $pageIndex 第几页
     * @param int $pageSize 每页条数
     * @return bool|mixed|string
     */
    public static function getWebMaster($id=false,$targetId=false,$targetType=false,$master=false,$isActive="true",$isDeputy=false,$pageIndex=1,$pageSize=10){
        $params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
        if($id) $params['id'] = $id;
        if($targetId) $params['targetId'] = $targetId;
        if($targetType) $params['targetType'] = $targetType;
        if($master) $params['master'] = $master;
        if($isDeputy) $params['isDeputy'] = $isDeputy;
        if($isActive) $params['isActive'] = $isActive;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/master_list',$params);
        return $result;
    }

    /**
     * @param bool $id  主键id
     * @param bool $targetId    论坛或板块id
     * @param bool $targetType  forum或board
     * @param bool $master  版主uid
     * @param bool $isDeputy    false:正版主 true:副版主
     * @param int $pageIndex 第几页
     * @param int $pageSize 每页条数
     * @return bool|mixed|string
     */
    public static function getMasterpplicationAList($pageIndex=1,$pageSize=2,$id=false,$targetId=false,$targetType=false,$proposer=false,$isDeputy=false){
        $params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
        if($id) $params['id'] = $id;
        if($targetId) $params['targetId'] = $targetId;
        if($targetType) $params['targetType'] = $targetType;
        if($proposer) $params['proposer'] = $proposer;
        if($isDeputy) $params['isDeputy'] = $isDeputy;
        $params['isLoadCount'] = 'true';
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/application_list',$params);
        return $result;
    }

    /**
     * 设置正（副）版主
     * @param targetId  目标ID，可以是论坛Id，或板块Id
     * @param targetType  目标类型，forum：论坛，board: 板块
     * @param $uid  用户id
     * @param isDeputy false：表示正版主 true：表示副版主
     */
    public static function SetMaster($id,$targetId,$targetType,$uid,$isDeputy){
        $params = array(
            'applicationId' => $id,
            'targetId' => $targetId,
            'targetType' => $targetType,
            'uid' => $uid,
            'isDeputy' => $isDeputy
        );
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/set_master',$params);
        return $result;
    }

    /**
 * 删除版主
 * @param $id
 */
    public static function DelMaster($id){
        $params = array(
            'id' => $id,
        );
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/delete_master',$params);
        return $result;
    }
    /**
     * 拒绝申请版主
     * @param $id
     */
    public static function RefuseApplication($id){
        $params = array(
            'id' => $id,
        );
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/refuse_application',$params);
        return $result;
    }

    /**
     * 获取回复
     * @param string $tid
     * @param int|number $pageIndex
     * @param int|number $pageSize
     * @param string $type
     * @param bool|string $isActive
     * @param string $replier
     * @param bool|string $isBest
     * @param string $hashValue
     * @return mixed
     */
	public static function getReplyList($tid,$pageIndex=1,$pageSize=10,$type='TOPIC',$isActive=false,$replier='',$isBest=false,$hashValue=''){
		$params = array(
            'tid' => $tid,
			'pageIndex' => $pageIndex,
			'pageSize' => $pageSize,
            'type' => $type
		);
		$isActive && $params['isActive'] = $isActive;
		$replier && $params['replier'] = $replier;
		$isBest && $params['isBest'] = $isBest;
		$hashValue && $params['hashValue'] = $hashValue;
		
		$result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/get_reply',$params,'GET');
		return $result;
	}



    /**
     * 获取回复详情（一级）
     * @param $replyId
     * @param int $pageIndex
     * @param int $pageSize
     * @return bool|mixed|string
     */
    public static function getReplyDetail($replyId,$pageIndex=1,$pageSize=10){
        $params = array('replyId' =>$replyId,'pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/reply_detail',$params);
    }

    /**
     * 编辑回复（一级）
     * @param $id
     * @param $replier
     * @param $content
     * @param string $formatContent
     * @param string $listpic
     * @param bool $isAdmin
     * @return bool|mixed|string
     */
    public static function updateReply($id,$replier,$content,$formatContent='',$listpic='',$isAdmin=false){
        $params = array(
            'id' => $id,
            'replier' => $replier,
            'content' => $content
        );
        $formatContent && $params['formatContent'] = $formatContent;
        $listpic && $params['listpic'] = $listpic;
        $isAdmin && $params['isAdmin'] = $isAdmin;
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_reply',$params,'POST');
    }

    /**
     * 获取回复列表（包含评论）
     * @param $tid
     * @param int $pageIndex
     * @param int $pageSize
     * @param int $commentSize
     * @param string $type
     * @param bool $replier
     * @param bool $isActive
     * @param bool $isBest
     * @param string $replyId
     * @param string $orderBy
     * @param string $hasComment
     * @param bool $keyword
     * @param bool $hasPic
     * @param bool $intervalFloor
     * @param bool $addUserToList
     * @return bool|mixed|string
     */
    public static function getReplyCommentList($tid,$pageIndex=1,$pageSize=10,$commentSize=3,$type='TOPIC',
                                               $replier=false,$isActive=false,$isBest=false,$replyId='',$orderBy='',
                                                $hasComment='false',$keyword=false,$hasPic=false,$intervalFloor=false,
                                                $addUserToList=false){
        $params = array('tid'=>$tid,'pageIndex'=>$pageIndex,'pageSize'=>$pageSize,'type'=>$type);
        if($commentSize) $params['commentSize'] = $commentSize;
        if($replier) $params['replier'] = $replier;
        if($isActive=='false'){
            $params['isActive'] = $isActive;
        }
        if($isBest) $params['isBest'] = $isBest;
        $replyId && $params['replyId'] = $replyId;
        $hasComment && $params['hasComment'] = $hasComment;
        $keyword && $params['keyword'] = $keyword;
        $hasPic && $params['hasPic'] = $hasPic;
        $intervalFloor &&  $params['intervalFloor'] = $intervalFloor;
        $addUserToList && $params['addUserToList'] = $addUserToList;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/get_reply_comment',$params);
        return $result;
    }

    /**
     * 设置最佳答案
     * @param $replyId  回复ID
     * @param $tid  帖子ID
     * @param $uid  回复人
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function setBestReply($replyId,$tid,$uid,$hashValue=false){
        $params = array(
            'replyId' => $replyId,
            'tid' => $tid,
            'uid' => $uid
        );

        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/set_best_reply',$params);
        return $result;
    }

    /**
     * 删除回复
     * @param mixed $id   回复id
     * @param $isAdmin  是否管理员操作
     * @param $isDel    是否删除
     * @param mixed $uid  操作人id
     * @param $hashValue
     * @return bool|mixed|string
     */
    public static function delReply($id,$isAdmin,$isDel,$uid=false,$hashValue=false){
        $params = array(
            'id' => $id,
            'isAdmin' => $isAdmin,
            'isDel' => $isDel
        );

        if($uid) $params['uid'] = $uid;
        if($hashValue) $params['hashValue'] = $hashValue;

        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/del_reply',$params);
        return $result;
    }

    /**
     * 发帖子
     * @param $fid  社区ID
     * @param $uid  发帖人
     * @param $bid  版块ID
     * @param $subject  标题
     * @param string $content 内容
     * @param string $award 悬赏
     * @param $fromTag  平台1.ios 2.Android 3.web
     * @param int $displayOrder
     * @param bool $isActivity
     * @param bool $isAdmin
     * @param bool $isRule
     * @param bool $isGood
     * @param bool $isAsk
     * @param bool $askStatus
     * @param bool $hashValue
     * @param bool $summary
     * @param bool $listpic
     * @param bool $formatContent
     * @param bool $tagid
     * @param int $tid
     * @param bool $iosDisplay
     * @param bool $androidDisplay
     * @param bool $webDisplay
     * @param bool $isTop
     * @param bool $topEndTime
     * @param bool $replyInvisible
     * @return bool|mixed|string
     */
    public static function doPostAdd($fid,$uid,$bid,$subject,$content='',$award='',$fromTag,$displayOrder=0,$isActivity=false,
									$isAdmin=false,$isRule=false,$isGood=false,$isAsk=false,$askStatus=false,$hashValue=false,
									$summary=false,$listpic=false,$formatContent=false,$tagid=false,$tid=0,$iosDisplay=false,
                                    $androidDisplay=false,$webDisplay=false,$isTop=false,$topEndTime=false,$replyInvisible=false){
		$params = array(
			'fid' => $fid,
			'uid' => $uid,
			'bid' => $bid,
			'subject' => $subject,
			'fromTag' => $fromTag,
			'formatContent' => $formatContent,
		);
		
		if($content) $params['content'] = $content;
		if($award) $params['award'] = $award;
		if($displayOrder) $params['displayOrder'] = $displayOrder;
		if($isActivity) $params['isActivity'] = $isActivity;
		if($isAdmin) $params['isAdmin'] = $isAdmin;
		if($isRule) $params['isRule'] = $isRule;
		if($isGood) $params['isGood'] = $isGood;
		if($isAsk) $params['isAsk'] = $isAsk;
		if($askStatus) $params['askStatus'] = $askStatus;
		if($hashValue) $params['hashValue'] = $hashValue;
		if($summary) $params['summary'] = $summary;
		if($listpic) $params['listpic'] = $listpic;
		if($tagid) $params['tagid'] = $tagid;
		if($tid) $params['tid'] = $tid;
        if($isTop) $params['isTop'] = $isTop;
        if($topEndTime) $params['topEndTime'] = $topEndTime;
        $iosDisplay && $params['iosDisplay'] = $iosDisplay;
        $androidDisplay && $params['androidDisplay'] = $androidDisplay;
        $webDisplay && $params['webDisplay'] = $webDisplay;
        $replyInvisible && $params['replyInvisible'] = $replyInvisible;
        //给图片加样式
        $params['formatContent'] = preg_replace("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/", "<span class='topic-img' >$0</span>", $params['formatContent']);
		$result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/post_topic',$params,'POST');
		return $result;
	}

    /**
     * 编辑帖子
     * @param $tid
     * @param bool $uid
     * @param bool $fid
     * @param bool $bid
     * @param bool $subject
     * @param bool $content
     * @param bool $award
     * @param bool $formatContent
     * @param bool $listpic
     * @param bool $hashValue
     * @param bool $iosDisplay
     * @param bool $androidDisplay
     * @param bool $webDisplay
     * @param bool $isTop
     * @param bool $topEndTime
     * @param bool $replyInvisible
     * @param int $displayOrder
     * @return bool|mixed|string
     */
    public static function modifyTopic($tid,$uid=false,$fid=false,$bid=false,$subject=false,$content=false,$award=false,$formatContent=false,$listpic=false,
                                       $hashValue=false,$iosDisplay=false,$androidDisplay=false,$webDisplay=false,$isTop=false,$topEndTime=false,$replyInvisible=false,$displayOrder=0,$summary="")
    {
        $params = array('tid'=>$tid);
        if($uid) $params['uid'] = $uid;
        if($fid) $params['fid'] = $fid;
        if($bid) $params['bid'] = $bid;
        if($subject) $params['subject'] = $subject;
        if($content) $params['content'] = $content;
        if($award !== false) $params['award'] = $award;
        if($formatContent) $params['formatContent'] = $formatContent;
        if($listpic) $params['listpic'] = $listpic;
        if($hashValue) $params['hashValue'] = $hashValue;
        $iosDisplay && $params['iosDisplay'] = $iosDisplay;
        $androidDisplay && $params['androidDisplay'] = $androidDisplay;
        $webDisplay && $params['webDisplay'] = $webDisplay;

//        $params['isTop'] = $isTop;
        $params['displayOrder'] = $displayOrder;
        if($topEndTime){
            $params['topEndTime'] = $topEndTime;
        }else{
            $params['topEndTime'] = null;
        }
        $replyInvisible && $params['replyInvisible'] = $replyInvisible;
        $params['summary'] = $summary;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/modify_topic',$params,'POST');
        return $result;
    }

    /**
     * 删除帖子
     * @param $tid  帖子ID
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function delTopic($tid,$type=true,$hashValue=false){

        $params = array('tid'=>$tid,'isDel'=>$type==='false'?'true':'false');
        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/delete_topic',$params);
        return $result;
    }

    /**
     * 获取论坛人数（游戏圈圈友数）
     * @param $platform
     * @param $gid
     * @return bool|mixed|string
     */
    public static function getForumPeopleNum($platform,$gid){
        $params = array('platform'=>$platform,'gid'=>$gid);
        $result = Utility::loadByHttp(Config::get(self::API_V4_CONF).'v4/game/member_count',$params);
        return $result;
    }


    /**
     * 回帖
     * @param string $replier
     * @param string $tid
     * @param string $content
     * @param string $formatContent
     * @param string $hashValue
     * @param bool $isAdmin
     * @param string $type
     * @param int $fromTag
     * @param string $listpic
     * @param string $message
     * @return mixed
     * @internal param int $version
     */
	public static function doReplyAdd($replier,$tid,$content='',$formatContent='',$hashValue='',$isAdmin=false,$type='TOPIC',$fromTag=3,$listpic='',$message=''){
		$params = array(
			'replier' => $replier,
			'tid' => $tid,
            'type' => $type,
            'fromTag' => $fromTag
		);
		if($content) $params['content'] = $content;
		if($formatContent) $params['formatContent'] = $formatContent;
		if($hashValue) $params['hashValue'] = $hashValue;
        if($isAdmin) $params['isAdmin'] = $isAdmin;
        $listpic && $params['listpic'] = $listpic;
        $message && $params['message'] = $message;
		$result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_reply',$params,'POST');
		return $result;
	}

    /**
     * 获取评论
     * @param $replyId
     * @param int $pageIndex
     * @param int $pageSize
     * @param bool $isActive
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function getComments($replyId,$pageIndex=1,$pageSize=2,$isActive=false,$hashValue=false){
        $params = array(
            'replyId' => $replyId,
            'pageIndex' => $pageIndex,
            'pageSize' => $pageSize
        );
        if($isActive) $params['isActive'] = $isActive;
        if($hashValue) $params['hashValue'] = $hashValue;

        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/get_comments',$params);
        return $result;
    }


/**
     * 获取评论总数
     * @param $replyId
     * @param bool $isActive
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function getCommentsCount($replyId,$isActive=false,$hashValue=false){
        $params = array('replyId'=>$replyId);
        if($isActive) $params['isActive'] = $isActive;
        if($hashValue) $params['hashValue'] = $hashValue;

        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/comment_number',$params);
        return $result;
    }
    /**
     * 评论回复
     * @param $replyId  回复Id
     * @param $uid  评论人
     * @param string $content 评论内容
     * @param string $formatContent 评论内容（格式化后）
     * @param bool $isActive 是否有效
     * @param bool $isAdmin 是否管理员编辑过
     * @return bool|mixed|string
     */
    public static function doCommentReplyAdd($replyId,$uid,$content='',$formatContent='',$isActive=true,$isAdmin=false){
        $params = array(
            'replyId' => $replyId,
            'uid' => $uid
        );
        if($content) $params['content'] = $content;
        if($formatContent) $params['formatContent'] = $formatContent;
        if($isActive) $params['isActive'] = $isActive;
        if($isAdmin) $params['isAdmin'] = $isAdmin;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_comment',$params,'POST');
        return $result;
    }

    /**
     * 删除评论
     * @param $id   评论id
     * @param $uid  评论人
     * @param $hashValue
     * @return bool|mixed|string
     */
    public static function delComment($id,$uid,$hashValue=false){
        $params = array('id'=>$id,'uid'=>$uid);
        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/del_comment',$params);
        return $result;
    }

    /**
     *  获取帖子回复总数
     * @param string $tid 帖子ID
     * @param string $replier 回复人ID
     * @param bool $isActive 是否删除
     * @param bool $isBest 是否是最佳答案
     * @param string $hashValue 校验值
     * @return bool|mixed|string
     */
    public static function getReplyTotalCount($tid,$replier='',$isActive=false,$isBest=false,$hashValue='',$fromTag=null,$start_time=null,$end_time=null,$keyword=null,$intervalFloor=null,$hasPic=null){
        $params = array(
            'tid' => $tid
        );
        if($replier) $params['replier'] = $replier;
        if($isActive) $params['isActive'] = $isActive;
        if($isBest) $params['isBest'] = $isBest;
        if($hashValue) $params['hashValue'] = $hashValue;
        if($fromTag!==null) $params['fromTag'] = (int)$fromTag;
        if($start_time!==null) $params['startTime'] = $start_time;
        if($end_time!==null) $params['endTime'] = $end_time;
        $keyword && $params['keyword'] = $keyword;
        $hasPic!==null && $params['hasPic'] = $hasPic;
        $intervalFloor!==null &&  $params['intervalFloor'] = $intervalFloor;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/reply_number',$params);
        return $result;
    }

    /**
     * php这边的点赞
     * @param $uid
     * @param $target_id
     * @param $target_table
     * @return bool|mixed|string
     */
    public static function addTopicWatchInV4($uid,$target_id,$target_table){
        $params = array('uid'=>$uid,'target_id'=>$target_id,'target_table'=>$target_table);
        $result = Utility::loadByHttp(Config::get(self::API_V4_CONF).'v4/do_like',$params);
        return $result;
    }
    /**
     * 增加帖子围观（点赞）数
     * @param $tid
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function addTopicWatch($tid,$hashValue=false){
        $params = array('tid'=>$tid);
        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_watch',$params);
        return $result;
    }

    /**
     * 设置帖子状态
     * @param $tid
     * @param bool $isTop
     * @param bool $isGood
     * @param bool $isActivity
     * @param bool $askStatus
     * @param string $hashValue
     * @return bool|mixed|string
     */
    public static function setTopicStatus($tid,$isTop=false,$isGood=false,$isActivity=false,$askStatus=false,$hashValue=''){
        $params = array('tid'=>$tid);
        if($isTop) $params['isTop'] = $isTop;
        if($isGood) $params['isGood'] = $isGood;
        if($isActivity) $params['isActivity'] = $isActivity;
        if($askStatus) $params['askStatus'] = $askStatus;
        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/topic_status',$params);
        return $result;
    }

    /**
     * 增加帖子浏览量
     * @param $tid
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function addTopicView($tid,$hashValue=false){
        $params = array('tid'=>$tid);
        if($hashValue) $params['hashValue'] = $hashValue;
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_view',$params);
        return $result;
    }

    /**
     * 发送短信验证码
     * @param $mobile   手机号
     * @return bool|mixed|string
     */
    public static function sendVerifycode($mobile){
        $param = array('mobile'=>$mobile);
        $result = Utility::loadByHttp(Config::get(self::API_PHONE_CONF).'send_phone_verifycode',$param);
        return $result;
    }

    /**
     * 验证短信验证码
     * @param $mobile   手机号
     * @param $verifycode   验证码
     * @return bool|mixed|string
     */
    public static function checkVerifycode($mobile,$verifycode){
        $param = array('mobile'=>$mobile,'verifycode'=>$verifycode);
        $result = Utility::loadByHttp(Config::get(self::API_PHONE_CONF).'check_phone_verifycode',$param);
        return $result;
    }
	
	/**
	 * 解析帖子内容
	 * @param array $message
	 * @return string
	 */
	public static function formatTopicMessage($message){
		$format_message = '';
        if($message){
            $msg_arr = json_decode($message,true);
            if($msg_arr){
                foreach($msg_arr as $val){
                    if($val['text']){
                        $format_message .= '<p class="topic-text">' . $val['text'] . '</p>';
                    }
                    if($val['img']){
                        $val['img'] = Utility::getImageUrl($val['img']);
                        $format_message .= '<p class="topic-img"><img src="' . $val['img'] . '" /></p>';
                    }
                }
            }else{
                $format_message = $message;
            }
        }

		return $format_message;
	}

    public static function entityHtmlExcpt($content,$tags=array()){
        if(!$tags) $tags = array('p','strong','em','a');
        if($content && $tags){
            preg_match_all('/<img.*?src=\"(.*?.*?)\".*?>/i',$content,$images);
            preg_match_all('/<a.*?href=\"(.*?.*?)\".*?>/i',$content,$hrefs);
            $content = preg_replace('/<img.*?src=\"(.*?.*?)\".*?>/i','[:img]',$content);
            $content = preg_replace('/<a.*?href=\"(.*?.*?)\".*?>/i','[:a]',$content);

            $content = preg_replace('/<p style="text-align: left;">/i','[:p-left]',$content);
            $content = preg_replace('/<p style="text-align: center;">/i','[:p-center]',$content);
            $content = preg_replace('/<p style="text-align: right;">/i','[:p-right]',$content);

            $content = preg_replace('/<span style="text-decoration: line-through;">/i','[:through]',$content);
            $content = preg_replace('/<\/span>/i','[:/through]',$content);
            $content = preg_replace('/<span style="text-decoration: underline;">/i','[:under]',$content);
            $content = preg_replace('/<\/span>/i','[:/under]',$content);
            $content = preg_replace('/<br\/>/i','[:br]',$content);
            foreach ($tags as $tag) {
                if($tag == 'a'){
                    $content = preg_replace('/<\/'.$tag.'>/i','[:/'.$tag.']',$content);
                }else{
                    $content = preg_replace('/<'.$tag.'>/i','[:'.$tag.']',$content);
                    $content = preg_replace('/<\/'.$tag.'>/i','[:/'.$tag.']',$content);
                }
            }
            $content = htmlentities($content,ENT_QUOTES,'UTF-8');
            $content = preg_replace('/\[\:p-left\]/','<p style="text-align: left;">',$content);
            $content = preg_replace('/\[\:p-center\]/','<p style="text-align: center;">',$content);
            $content = preg_replace('/\[\:p-right\]/','<p style="text-align: right;">',$content);
            $content = preg_replace('/\[\:through\]/','<span style="text-decoration: line-through;">',$content);
            $content = preg_replace('/\[\:\/through\]/','</span>',$content);
            $content = preg_replace('/\[\:under\]/','<span style="text-decoration: underline;">',$content);
            $content = preg_replace('/\[\:\/under\]/','</span>',$content);
            $content = preg_replace('/\[\:br]/','</br>',$content);
            foreach ($tags as $tag) {
                if($tag != 'a'){
                    $content = preg_replace('/\[\:'.$tag.'\]/','<'.$tag.'>',$content);
                    $content = preg_replace('/\[\:\/'.$tag.'\]/','</'.$tag.'>',$content);
                }else{
                    $content = preg_replace('/\[\:\/'.$tag.'\]/','</'.$tag.'>',$content);
                }
            }
            if($images[0]){
                foreach ($images[0] as $item) {
                    $content = preg_replace('/\[\:img\]/',$item,$content,1);
                }
            }
            if($hrefs[0]){
                foreach ($hrefs[0] as $item) {
                    $content = preg_replace('/\[\:a\]/',$item,$content,1);
                }
            }
        }
        return $content;
    }

    /**
     * 社区下版块列表
     * @param bool $fid
     * @param bool $fromTag
     * @return bool|mixed|string
     */
    public static function getForumBoardList($fid=false,$fromTag=false,$displayType=2){
        $params = array();
        $fid && $params['fid'] = $fid;
        $fromTag && $params['fromTag'] = $fromTag;
        if($displayType!==null) $params['displayType'] = $displayType;
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/forum_board_list',$params);
    }

    /**
     * 新增版块
     * @param $name 版块名称
     * @param $type 版块类型，0：无需游币 1：需要游币
     * @param bool $fid 论坛ID
     * @param bool $displayOrder    显示排序
     * @param bool $allowPost   是否允许发言
     * @param bool $pid
     * @param bool $hashValue
     * @return bool|mixed|string
     */
    public static function addBoard($name,$type,$fid=false,$logo=false,$displayOrder=false,$allowPost=false,$pid=false,$hashValue=false){
        $params = array('name'=>$name,'type'=>$type);
        $fid && $params['fid'] = $fid;
        $displayOrder && $params['displayOrder'] = $displayOrder;
        $allowPost && $params['allowPost'] = $allowPost;
        $pid && $params['pid'] = $pid;
        $hashValue && $params['hashValue'] = $hashValue;
        $logo && $params['logo'] = $logo;
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_board',$params,'POST');
    }
    
    public static function getBoardDetail($bid)
    {
    	$params = array('bid'=>$bid);
    	return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/board_detail',$params);
    }
    
    public static function updateBoard($bid,$name,$type,$fid=false,$logo=false,$displayOrder=false,$allowPost=false,$pid=false,$hashValue=false)
    {
    	$params = array('bid'=>$bid,'name'=>$name,'fid'=>$fid,'type'=>$type);
        $fid && $params['fid'] = $fid;
        $displayOrder && $params['displayOrder'] = $displayOrder;
        $allowPost && $params['allowPost'] = $allowPost;
        $pid && $params['pid'] = $pid;
        $hashValue && $params['hashValue'] = $hashValue;
        $logo && $params['logo'] = $logo;
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_board',$params,'POST');
    }

    /**
     * 开启关闭版块
     * @param $bid
     * @param $isOpen ture false
     * @return bool|mixed|string
     */
    public static function openCloseBoard($bid,$isOpen){
        $params = array('bid'=>$bid,'isOpen'=>$isOpen);
        return Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/open_board',$params);
    }

    public static function add_recruit_rule($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_recruit_rule',$data,'POST');
        return $result;
    }

    public static function recruit_rule_list($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/recruit_rule_list',$data,'GET');
        return $result;
    }

    public static function delete_recruit_rule($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/delete_recruit_rule',$data,'GET');
        return $result;
    }

    public static function update_recruit_rule($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_recruit_rule',$data,'POST');
        return $result;
    }

    public static function add_recommend($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_recommend',$data,'POST');
        return $result;
    }

    public static function del_recommend($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/del_recommend',$data,'GET');
        return $result;
    }

    public static function forum_recommend_list($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/forum_recommend_list',$data,'GET');
        return $result;
    }

    public static function system_send($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_message/message/system_send',$data,'POST');
        return $result;
    }

    public static function query_dictionary_list($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/query_dictionary_list',$data,'POST');
        return $result;
    }
    public static function update_dictionary($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_dictionary',$data,'POST');
        return $result;
    }
    public static function add_reply($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/add_reply',$data,'POST');
        return $result;
    }
    public static function update_reply($data){
        $result = Utility::loadByHttp(Config::get(self::API_URL_CONF).'module_forum/update_reply',$data,'POST');
        return $result;
    }
}