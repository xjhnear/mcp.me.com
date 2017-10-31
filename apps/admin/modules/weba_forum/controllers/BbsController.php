<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/6
 * Time: 10:26
 */
namespace modules\weba_forum\controllers;

use Youxiduo\Bbs\Model\BbsAppend;
use Youxiduo\Bbs\Model\BbsBanner;
use Youxiduo\Bbs\Model\BbsGiftbag;
use Youxiduo\Bbs\Model\BbsRecommend;
use Youxiduo\Helper\Utility;
use libraries\Helpers;
use Youxiduo\V4\Game\GameService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Bbs\Model\BbsHome;
use Youxiduo\Cms\GameInfo;
use Youxiduo\Bbs\TopicService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BbsController extends BackendController{  

    public function _initialize(){
		$this->current_module = 'weba_forum';
	}

    public function getForumList(){
        $bbs_list = $fids = $tmp = $gids = array();
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 20;
        $search_name = Input::get('name','');
        $bbs = TopicService::getForums($search_name,$page,$limit);

        if($bbs['errorCode']) return $this->back()->with('global_tips','获取论坛列表接口错误，请联系管理员');
        $bbs = $bbs['result'];
        $bbs_count = TopicService::getForumsCount($search_name);
        $bbs_count = $bbs_count['totalCount'];
        if($bbs){
            foreach ($bbs as $item) {
                $fids[] = $item['fid'];
            }

            $relat_result = TopicService::getForumAndGameRelation(1,1,$fids);
            if($relat_result['errorCode']) return $this->back()->with('global_tips','获取论坛与游戏关系接口错误，请联系管理员');
            foreach ($relat_result['result'] as $relat) {
                $tmp[$relat['fid']] = $relat['gid'];
                $gids[] = $relat['gid'];
            }

            foreach ($bbs as $item) {
                if(array_key_exists($item['fid'],$tmp)) $gid = $tmp[$item['fid']];
                $bbs_list[$item['fid']] = array(
                    'fid' => $item['fid'],
                    'gid' => $gid,
                    'threads' => $item['totalTopicNumber'],
                    'people' => 0
                );
            }

            if($gids){
                $games = GameInfo::getMobileGames($gids);
                $people_result = TopicService::getForumPeopleNum('ios',implode(',',$gids));
                if(!$people_result['errorCode']){
                    $people_result = $people_result['result'];
                    foreach ($people_result as $key=>$row) {
                        $people_result[$row['game_id']] = $row['total'];
                        unset($people_result[$key]);
                    }
                }
                if($games){
                    foreach ($games as $key=>$game) {
                        unset($games[$key]);
                        $games[$game['gid']] = $game;
                    }

                    foreach ($bbs as $row) {
                        if(array_key_exists($row['fid'],$bbs_list)){
                            if(!array_key_exists($bbs_list[$row['fid']]['gid'],$games)) continue;
                            $bbs_list[$row['fid']]['name'] = $games[$bbs_list[$row['fid']]['gid']]['shorttitle'];
                            $bbs_list[$row['fid']]['icon_img'] = Utility::getImageUrl($games[$bbs_list[$row['fid']]['gid']]['litpic']);
                            $bbs_list[$row['fid']]['des'] = $games[$bbs_list[$row['fid']]['gid']]['description'];
                            $bbs_list[$row['fid']]['center_url'] = 'http://www.youxiduo.com/game/'.$games[$bbs_list[$row['fid']]['gid']]['id'];
                            $bbs_list[$row['fid']]['people'] = array_key_exists($bbs_list[$row['fid']]['gid'],$people_result) ? $people_result[$bbs_list[$row['fid']]['gid']] : 0;
                        }
                    }
                }
            }
        }

        $pagination = Paginator::make(array(),$bbs_count,$limit);
        $pagination->appends(array('fname'=>$search_name));
        $paginator = $pagination->links();
        return $this->display('4web/forum-list',array('forumlist'=>$bbs_list,'paginator'=>$paginator,'search_name'=>$search_name));
    }

    public function getAddForum(){
        return $this->display('4web/forum-add');
    }

    public function postAddForum(){
        $input = Input::all();
        $rule = array('gid'=>'required','forum_name'=>'required','platform'=>'required');
        $msg = array('gid.required'=>'请选择游戏','forum_name.required'=>'请填写论坛名称','platform.required'=>'请选择平台');
        $valid = Validator::make($input,$rule,$msg);
        if($valid->fails()){
            return $this->back()->with('global_tips',$valid->messages()->first());
        }else{
            $platform = 1;
            //新建论坛
            $add_forum_result = TopicService::addForum($input['forum_name'],$input['gicon']);
            if($add_forum_result['errorCode'] || !$add_forum_result['result']) return $this->back()->with('global_tips','添加论坛失败，请重试');
            //保存论坛游戏关系
            $add_relate_result = TopicService::saveForumAndGameRelation($add_forum_result['result'],$input['gid'],$platform);
            if($add_relate_result['errorCode']) return $this->back()->with('global_tips','添加论坛失败，请重试');
            //插入论坛图片
            $file = Input::file('top_banner');
            $path = '';
            if($file){
                $dir = '/bbs/topbanner/';
                $path = Helpers::uploadPic($dir,$file);
            }
            $result = BbsAppend::add(array('fid'=>$add_forum_result['result'],'top_pic'=>$path,'short_name'=>''));
            if($result){
                return $this->back()->with('global_tips','添加社区成功');
            }else{
                return $this->back()->with('global_tips','论坛添加成功，论坛图片添加失败，请重新编辑修改');
            }
        }
    }

    public function getHotlist(){
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 10;
        $base_forum = BbsHome::getBbsInfo($page,$limit);
        $forum_count = BbsHome::getBbsInfoCount();

        $gids = array();
        if($base_forum){
            foreach ($base_forum as $forum) {
                $gids[] = $forum['gid'];
            }

            $games = GameService::getMultiInfoById($gids,'ios');
            if($games){
                foreach ($games as $key=>$gm) {
                    unset($games[$key]);
                    $games[$gm['id']] = $gm;
                }
            }

            foreach ($base_forum as &$item) {
                $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                if(!array_key_exists($item['gid'],$games)) continue;
                $item['gname'] = $games[$item['gid']]['shortgname'];
                $item['icon'] = Utility::getImageUrl($games[$item['gid']]['ico']);
            }

        }
        $data['games'] = $base_forum;
        $data['pagination'] = Paginator::make(array(),$forum_count,$limit)->links();
        return $this->display('hotlist',$data);
    }

    public function getAddHotlist(){
        return $this->display('hotlist-add');
    }

    public function postAddHotlistDo(){
        $input = Input::all();
        $rule = array('gid'=>'required','s_type'=>'required');
        $prompt = array('gid.required'=>'关联游戏不能为空','s_type.required'=>'类型不能为空');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $gid = Input::get('gid');
            $relate = TopicService::getForumAndGameRelation(1,1,false,$gid);
            $fid = $relate['errorCode'] ? false : $relate['result'][0]['fid'];
            $data = array(
                'fid' => $fid,
                'gid' => $gid,
                'add_time' => time(),
                'sort' => Input::get('sort') ? Input::get('sort') : 0,
                'type' => Input::get('s_type'),
                'show' => Input::get('show') ? 1 : 0
            );
            $result = BbsHome::addInfo($data);
            if($result){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请重试');
            }
        }
    }

    public function getEditHotlist($home_id=false){
        if(!$home_id) return $this->back()->with('global_tips','数据错误');
        $info = BbsHome::getInfo('',0,$home_id);
        if(!$info) return $this->back()->with('global_tips','数据错误');
        $data = array();
        if($info){
            $gid = $info['gid'];
            $gameinfo = GameInfo::getMobileGame($gid);
            $data['home_id'] = $info['bbs_home_id'];
            $data['gid'] = $gid;
            $data['s_type'] = $info['type'];
            $data['sort'] = $info['sort'];
            $data['show'] = $info['show'];
            $data['icon'] = Utility::getImageUrl($gameinfo['litpic']);
            $data['gname'] = $gameinfo['shorttitle'];
        }
        return $this->display('hotlist-edit',$data);
    }

    public function getDelHotlist(){
        $data = array('state'=>0,'msg'=>'删除失败，请刷新后重试');
        $hids = Input::get('hids');
        if(!$hids) return Response::json($data);
        $result = BbsHome::deleteInfo($hids);
        if($result){
            $data['state'] = 1;
            $data['msg'] = '删除成功';
        }else{
            $data['state'] = 0;
            $data['msg'] = '删除失败，请刷新后重试';
        }
        return Response::json($data);
    }

    public function postEditHotlistDo($home_id=false){
        if(!$home_id) return $this->back()->with('global_tips','数据错误');
        $input = Input::all();
        $rule = array('gid'=>'required','s_type'=>'required');
        $prompt = array('gid.required'=>'关联游戏不能为空','s_type.required'=>'类型不能为空');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $gid = Input::get('gid');
            $relate = TopicService::getForumAndGameRelation(1,1,false,$gid);
            $fid = $relate['errorCode'] ? false : $relate['result'][0]['fid'];
            $data = array(
                'fid' => $fid,
                'gid' => $gid,
                'add_time' => time(),
                'sort' => Input::get('sort') ? Input::get('sort') : 0,
                'type' => Input::get('s_type'),
                'show' => Input::get('show') ? 1 : 0
            );
            $result = BbsHome::updateInfo($home_id,$data);
            if($result){
                return $this->back()->with('global_tips','保存成功');
            }else{
                return $this->back()->with('global_tips','保存失败，请重试');
            }
        }
    }

    public function getCheckGameHasBbs(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'');
        if($gameid && is_numeric($gameid)){
            $relate = TopicService::getForumAndGameRelation(1,1,false,$gameid);
            if($relate['errorCode'] || !$relate['result']) {
                $data['state'] = 0;
                $data['msg'] = '该游戏暂无社区，请重新选择';
                return Response::json($data);
            }
            $exist = BbsHome::getInfo('',$gameid);
            if($exist){
                $data['state'] = 0;
                $data['msg'] = '该游戏已存在，请勿重复添加';
                return Response::json($data);
            }
        }
        return Response::json($data);
    }

    public function getCheckForumExist(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'');
        if($gameid && is_numeric($gameid)){
            $relate = TopicService::getForumAndGameRelation(1,1,false,$gameid);
            if($relate['errorCode']) {
                $data['state'] = 0;
                $data['msg'] = '社区游戏关系查询接口错误，请联系管理员';
            }
            if($relate['result']) {
                $data['state'] = 0;
                $data['msg'] = '该游戏已经拥有社区了，新重新选择';
            }
        }
        return Response::json($data);
    }

    //首页礼包
    public function getHomeGiftbag(){
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 10;
        $giftbag = BbsGiftbag::getBbsGiftbagList($page,$limit);
        $count = BbsGiftbag::getBbsGiftbagCount();
        $data['giftbag'] = $giftbag;
        $data['pagination'] = Paginator::make(array(),$count,$limit)->links();
        return $this->display('home-giftbag',$data);
    }

    public function getAddHomeGiftbag(){
        return $this->display('home-giftbag-add');
    }

    public function postAddHomeGiftbag(){
        $input = Input::all();
        $rule = array('title'=>'required','last'=>'required|integer','price'=>'required|integer');
        $prompt = array('title.required'=>'标题不能为空','last.required'=>'剩余数不能为空','last.digits'=>'剩余数必须为整数','price.required'=>'价格不能为空','price.digits'=>'价格必须为整数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $data = array(
                'title' => $input['title'],
                'last' => $input['last'],
                'price' => $input['price'],
                'add_time' => time(),
                'is_show' => isset($input['show']) ? 1 : 0
            );
            $result = BbsGiftbag::insertBbsGiftbag($data);
            if($result){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请稍后重试');
            }
        }
    }

    public function getEditHomeGiftbag($giftbag_id=false){
        if(!$giftbag_id || !is_numeric($giftbag_id)) return $this->back()->with('global_tips','数据错误');
        $giftbag_info = BbsGiftbag::getBbsGiftbagById($giftbag_id);
        if(!$giftbag_info) return $this->back()->with('global_tips','数据错误');
        $data['giftbag'] = $giftbag_info;
        return $this->display('home-giftbag-edit',$data);
    }

    public function postEditHomeGiftbag($giftbag_id=false){
        if(!$giftbag_id || !is_numeric($giftbag_id)) return $this->back()->with('global_tips','数据错误');
        $input = Input::all();
        $rule = array('title'=>'required','last'=>'required|integer','price'=>'required|integer');
        $prompt = array('title.required'=>'标题不能为空','last.required'=>'剩余数不能为空','last.digits'=>'剩余数必须为整数','price.required'=>'价格不能为空','price.digits'=>'价格必须为整数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $data = array(
                'title' => $input['title'],
                'last' => $input['last'],
                'price' => $input['price'],
                'add_time' => time(),
                'is_show' => isset($input['show']) ? 1 : 0
            );
            $result = BbsGiftbag::updateBbsGiftbag($giftbag_id,$data);
            if($result){
                return $this->back()->with('global_tips','更新成功');
            }else{
                return $this->back()->with('global_tips','更新失败，请稍后重试');
            }
        }
    }

    public function getDelHomeGiftbag($giftbag_id){
        if(!$giftbag_id) return Response::json(array('state'=>0,'msg'=>'数据错误'));
        $result = BbsGiftbag::deleteBbsGiftbag($giftbag_id);
        if($result){
            return Response::json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return Respones::json(array('state'=>0,'msg'=>'删除失败，请稍后重试'));
        }
    }

    //web社区推荐帖子
    public function getRctopic(){
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 10;
        $topics = BbsRecommend::getBbsRecommendList($page,$limit);
        $count = BbsRecommend::getBbsRecommendCount();
        $data['topics'] = $topics;
        $data['pagination'] = Paginator::make(array(),$count,$limit)->links();
        return $this->display('rctopic-list',$data);
    }

    public function getAddRctopic(){
        return $this->display('rctopic-add');
    }

    public function postAddRctopic(){
        $input = Input::all();
        $rule = array('tid'=>'required','title'=>'required','short'=>'required','sort'=>'integer');
        $prompt = array('tid.required'=>'帖子ID不能为空','title.required'=>'帖子标题不能为空','short.required'=>'帖子短标题不能为空','sort.integer'=>'排序必须为整数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $data = array(
                'topic_id' => $input['tid'],
                'topic_title' => $input['title'],
                'topic_short' => $input['short'],
                'action_url' => $input['action_url'],
                'sort' => $input['sort'],
                'add_time' => time(),
                'is_show' => isset($input['show']) ? 1 : 0
            );
            $result = BbsRecommend::insertBbsRecommend($data);
            if($result){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请稍后重试');
            }
        }
    }

    public function getEditRctopic($recommend_id=false){
        if(!$recommend_id || !is_numeric($recommend_id)) return $this->back()->with('global_tips','数据错误');
        $info = BbsRecommend::getBbsRecommendById($recommend_id);
        if(!$info) return $this->back()->with('global_tips','数据错误');
        $data['topic'] = $info;
        return $this->display('rctopic-edit',$data);
    }

    public function postEditRctopic($recommend_id=false){
        if(!$recommend_id || !is_numeric($recommend_id)) return $this->back()->with('global_tips','数据错误');
        $input = Input::all();
        $rule = array('tid'=>'required','title'=>'required','short'=>'required','sort'=>'integer');
        $prompt = array('tid.required'=>'帖子ID不能为空','title.required'=>'帖子标题不能为空','short.required'=>'帖子短标题不能为空','sort.integer'=>'排序必须为整数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $data = array(
                'topic_id' => $input['tid'],
                'topic_title' => $input['title'],
                'topic_short' => $input['short'],
                'action_url' => $input['action_url'],
                'sort' => $input['sort'],
                'add_time' => time(),
                'is_show' => isset($input['show']) ? 1 : 0
            );
            $result = BbsRecommend::updateBbsRecommend($recommend_id,$data);
            if($result){
                return $this->back()->with('global_tips','更新成功');
            }else{
                return $this->back()->with('global_tips','更新失败，请稍后重试');
            }
        }
    }

    public function getDelRctopic($recommend_id=false){
        if(!$recommend_id) return Response::json(array('state'=>0,'msg'=>'数据错误'));
        $result = BbsRecommend::deleteBbsRecommend($recommend_id);
        if($result){
            return Response::json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return Response::json(array('state'=>0,'msg'=>'删除失败'));
        }
    }

    /**
     * web社区轮播
     * @return mixed
     */
    public function getBanner(){
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 10;
        $banners= BbsBanner::getBbsBanner($page,$limit);
        if($banners){
            foreach ($banners as &$bann) {
                $bann['img_path'] = Utility::getImageUrl($bann['img_path']);
            }
        }
        $count = BbsBanner::getBbsBannerCount();
        $data['banners'] = $banners;
        $data['pagination'] = Paginator::make(array(),$count,$limit)->links();
        return $this->display('banner-list',$data);
    }

    public function getAddBanner(){
        return $this->display('banner-add');
    }

    public function postAddBanner(){
        $input = Input::all();
        $rule = array('banner'=>'required');
        $prompt = array('banner.required'=>'请选择轮播图');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $file = Input::file('banner');
            $dir = '/bbs/banner/';
            $path = Helpers::uploadPic($dir,$file);

            $data = array(
                'img_path' => $path,
                'url_path' => $input['path'] ? $input['path'] : '#',
                'sort' => $input['sort'],
                'is_show' => isset($input['show']) ? 1 : 0,
                'add_time' => time()
            );

            $result = BbsBanner::insertBannerCount($data);
            if($result){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请重试');
            }
        }
    }

    public function getEditBanner($banner_id=false){
        if(!$banner_id || !is_numeric($banner_id)) return $this->back()->with('global_tips','数据错误');
        $info = BbsBanner::getBannerById($banner_id);
        if(!$info) return $this->back()->with('global_tips','数据错误');
        $info['img_path'] = Utility::getImageUrl($info['img_path']);
        $data['banner'] = $info;
        return $this->display('banner-edit',$data);
    }

    public function postEditBanner($banner_id=false){
        if(!$banner_id) return $this->back()->with('global_tips','数据错误');
        $banner_info = BbsBanner::getBannerById($banner_id);
        if(!$banner_info) return $this->back()->with('global_tips','数据错误');
        $input = Input::all();
        $rule = array();
        if(!$banner_info['img_path']) $rule['banner'] = 'required';
        $prompt = array('banner.required'=>'请选择轮播图');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->with('global_tips',current($valid->messages()->all()));
        }else{
            $file = Input::file('banner');
            $path = null;
            if($file){
                $dir = '/bbs/banner/';
                $path = Helpers::uploadPic($dir,$file);
            }

            $data = array(
                'url_path' => $input['path'] ? $input['path'] : '#',
                'sort' => $input['sort'],
                'is_show' => isset($input['show']) ? 1 : 0,
                'add_time' => time()
            );
            if($path) $data['img_path'] = $path;

            $result = BbsBanner::updateBbaBanner($banner_id,$data);
            if($result){
                return $this->back()->with('global_tips','更新成功');
            }else{
                return $this->back()->with('global_tips','更新失败，请重试');
            }
        }
    }

    public function getDelBanner($banner_id=false){
        $result = BbsBanner::deleteBbsBanner($banner_id);
        if($result){
            return Response::json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return Response::json(array('state'=>0,'msg'=>'删除失败，请稍后重试'));
        }
    }

    public function getOpen($fid,$status)
    {
        $status = $status==1 ? 'true' : 'false';
        $result = TopicService::openForum($fid,$status,2);
        if($result['errorCode']==0){
            return $this->back('操作成功');
        }
        return $this->back('操作失败');
    }
}