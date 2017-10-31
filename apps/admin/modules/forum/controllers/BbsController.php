<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/6
 * Time: 10:26
 */
namespace modules\forum\controllers;

use Youxiduo\Bbs\Model\BbsBanner;
use Youxiduo\Bbs\Model\BbsGiftbag;
use Youxiduo\Bbs\Model\BbsRecommend;
use Youxiduo\Helper\Utility;
use libraries\Helpers;
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
		$this->current_module = 'forum';
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

            $games = GameInfo::getMobileGames($gids);
            if($games){
                foreach ($games as $key=>$gm) {
                    unset($games[$key]);
                    $games[$gm['gid']] = $gm;
                }
            }

            foreach ($base_forum as &$item) {
                if(!array_key_exists($item['gid'],$games)) continue;
                $item['gname'] = $games[$item['gid']]['shorttitle'];
                $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                $item['icon'] = Utility::getImageUrl($games[$item['gid']]['litpic']);
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
            $fid = TopicService::getForumIdByGameId($gid,1);
            $fid = $fid ? $fid['result'] : false;
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
            $fid = TopicService::getForumIdByGameId($gid,1);
            $fid = $fid ? $fid['result'] : false;
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
            $result = TopicService::getForumIdByGameId($gameid,1);
            if($result['errorCode']) {
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
            $dir = '/u/bbs/banner/';
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
}