<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/4/21
 * Time: 11:11
 */
namespace modules\duang\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Youxiduo\Activity\Duang\VariationService;
use Youxiduo\Activity\Model\Variation\ActDepRelate;
use Youxiduo\Activity\Model\Variation\GiftbagDepot;
use Youxiduo\Activity\Model\Variation\VariationActivity;
use Youxiduo\Activity\Model\Variation\VariationMain;
use Youxiduo\Activity\Model\Variation\VariationMoney;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\Common\ShareService;
use Youxiduo\Task\TaskV3Service;

class VariationController extends BackendController{
    public function _initialize(){
        $this->current_module = 'duang';
    }

    public function getList(){
        $page = Input::get('page',1);
        $search = Input::get('title');
        $limit = 10;
        $total = VariationActivity::getListCount($search);
        $tmp_list = VariationActivity::getList($page,$limit,$search);
        $list = array();
        if($tmp_list){
            $act_ids = array();
            foreach($tmp_list as $item){
                $item['share_pic'] = Utility::getImageUrl($item['share_pic']);
                $item['depots'] = array();
                $item['money_img'] = 'http://share.youxiduo.com/share/static/img/money.png';
                $item['share_link'] = VariationService::makeShareUrl('http://share.youxiduo.com/android/share/home?hashcode='.$item['hashcode'].'&uid=5345536',$item['activity_id'],'5345536',$item['share_title']);
                $act_ids[] = $item['activity_id'];
                $list[$item['activity_id']] = $item;
            }
            $tmp_relates = ActDepRelate::getTargetList('variation',$act_ids);
            if($tmp_relates){
                $relates = $depot_ids = array();
                foreach($tmp_relates as $row){
                    $depot_ids[] = $row['depot_id'];
                    $relates[$row['depot_id']] = $row;
                    $relates[$row['depot_id']]['depot_info'] = false;
                }
                $depots = GiftbagDepot::getInfo($depot_ids);
                if($depots){
                    foreach($depots as &$row){
                        $row['icon'] = Utility::getImageUrl($row['icon']);
                        $relates[$row['depot_id']]['depot_info'] = $row;
                    }
                    unset($row);
                }
                foreach($relates as $row){
                    if(array_key_exists($row['activity_id'],$list)) $list[$row['activity_id']]['depots'][] = $row;
                }
            }
        }
        $pager = Paginator::make(array(),$total,$limit);
        $pager->appends(array('search'=>$search));
        return $this->display('variation/activity-list',array('list'=>$list,'pagination'=>$pager->links(),'title'=>$search));
    }

    public function getAdd(){
        //获取所有有效礼包库
        $tmp_depots = GiftbagDepot::getAllValidDepot();
        $depots = array();
        if($tmp_depots){
            $depot_ids = array();
            foreach($tmp_depots as $row){
                $depots[$row['depot_id']] = $row;
                $depot_ids[] = $row['depot_id'];
            }
            //获取关系
            $relate = ActDepRelate::getTargetList('variation','',$depot_ids);
            if($relate){
                //过滤掉已被使用的礼包库
                foreach($relate as $item){
                    if(array_key_exists($item['depot_id'],$depots)) unset($depots[$item['depot_id']]);
                }
            }
        }
        return $this->display('variation/activity-add',array('depots'=>$depots));
    }

    public function postAdd(){
        $input = Input::all();
        $direcotr = isset($input['director']) ? serialize($input['director']) : serialize(array());
        $set_money = isset($input['money']) ? true : false;
        $set_s_money = isset($input['s_money']) ? true : false;

        $rule = array('title'=>'required','starttime'=>'required','endtime'=>'required','need_times'=>'required',
            'reward'=>'required','join'=>'required','rule'=>'required','share_pic'=>'required','article_id'=>'required',
            'director'=>'directvalid:'.$set_money,'sharer'=>'depotvalid:'.$direcotr,'share_title'=>'required',
            'money_value'=>'integer|required_with:money','s_money_value'=>'integer|required_with:s_money',
            'more_newer'=>'integer|required_with:is_spread','more_coin'=>'integer|required_with:is_spread');
        $prompt = array('title.required'=>'请填写标题','starttime.required'=>'请选择开始时间','endtime.required'=>'请选择结束时间',
            'need_times.required'=>'请填写分享次数','reward.required'=>'请填写活动奖励','join.required'=>'请填写参与方式','rule.required'=>'请填写活动规则',
            'money_value.required_with'=>'请填写新用户游币数','s_money_value.required_with'=>'请填写老用户游币数','article_id.required'=>'请填写文章ID',
            'share_title.required'=>'请填写分享标题','share_pic.required'=>'请选择分享图片','director.directvalid'=>'新用户奖励数量上限',
            'sharer.depotvalid'=>'老用户奖励设置有误','more_newer.integer'=>'人数必须为整数','more_newer.required_with'=>'请填写人数',
            'more_coin.integer'=>'游币数必须为整数','more_coin.required_with'=>'请填写游币数');
        Validator::extend('directvalid',function($attr,$val,$param){
            $selet_count = count($val);
            if($param[0]){
                if($selet_count > 3) return false;
            }else{
                if($selet_count > 4) return false;
            }
            return true;
        });
        Validator::extend('depotvalid',function($attr,$val,$param)use($set_s_money){
            $selet_count = count($val);
            if($set_s_money){
                if($selet_count > 3) return false;
            }else{
                if($selet_count > 4) return false;
            }
            $directors = unserialize($param[0]);
            $sharer = $val;
            foreach($sharer as $row){
                if(in_array($row,$directors)) return false;
            }
            return true;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $hashcode = sha1(str_random(8));
            $is_show = isset($input['is_show']) ? 1 : 0;
            $act_data = array(
                'title'=>$input['title'],
                'starttime'=>strtotime($input['starttime']),
                'endtime'=>strtotime($input['endtime']),
                'need_times'=>$input['need_times'],
                'is_show'=>$is_show,
                'reward'=>$input['reward'],
                'join'=>$input['join'],
                'rule'=>$input['rule'],
                'article_id'=>$input['article_id'],
                'share_title'=>$input['share_title'],
                'share_des'=>$input['share_des'],
                'addtime'=>time(),
                'hashcode'=>$hashcode,
                'money'=>$input['money_value'],
                's_money'=>$input['s_money_value']
            );
            if(isset($input['is_spread'])){
                $act_data['is_spread'] = 1;
                $act_data['more_newer'] = $input['more_newer'];
                $act_data['more_coin'] = $input['more_coin'];
            }
            if($input['share_pic']){
                $dir = '/userdirs/duang/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['share_pic']);
                $act_data['share_pic'] = $path;
            }
            if($input['top_pic']){
                $dir = '/userdirs/duang/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['top_pic']);
                $act_data['top_pic'] = $path;
            }
            $directors = isset($input['director']) ? $input['director'] : array();
            $sharers = isset($input['sharer']) ? $input['sharer'] : array();
            if(VariationActivity::addActivityAndRelate($act_data,$directors,$sharers)){
                $target_id=$input['article_id'];
                $target_title = $input['title'];
                $platform = 'android';
                $tpl_ename = 'android_share_tpl_activity_info';
                $title = $input['share_title'];
                $icon = $act_data['share_pic'];
                $content = $input['share_des'];
                $redirect_url = 'http://share.youxiduo.com/android/share/home?hashcode='.$hashcode;
                $start_time = strtotime($input['starttime']);
                $end_time = strtotime($input['endtime']);
                $is_show = $is_show;
                ShareService::saveAdvInfoByTargetId($target_id,$target_title,$platform,$tpl_ename,$title,$icon,$content,$redirect_url,$start_time,$end_time,$is_show);
                return $this->redirect('/duang/variation/list','添加成功');
            }else{
                return $this->back('添加失败，请重试');
            }
        }
    }

    public function getEdit($act_id=''){
        if(!$act_id) return $this->back('数据错误');
        $actinfo = VariationActivity::getInfo($act_id);
        if(!$actinfo) return $this->back('无此活动信息');
        $depots = array();
        $tmp_depots = GiftbagDepot::getAllValidDepot();
        if($tmp_depots){
            $depot_ids = array();
            foreach($tmp_depots as &$row){
                $row['belong'] = false;
                $row['select'] = false;
                $depots[$row['depot_id']] = $row;
                $depot_ids[] = $row['depot_id'];
            }
            //获取关系
            $relate = ActDepRelate::getTargetList('variation','',$depot_ids);
            if($relate){
                //过滤掉已被使用的礼包库
                foreach($relate as $item){
                    if(array_key_exists($item['depot_id'],$depots)){
                        if($item['activity_id'] != $act_id){
                            unset($depots[$item['depot_id']]);
                        }else{
                            $depots[$item['depot_id']]['select'] = true;
                            $depots[$item['depot_id']]['belong'] = $item['belong'];
                        }
                    }else{
                        $depots[$item['depot_id']]['belong'] = $item['belong'];
                    }
                }
            }
        }
        if($actinfo) $actinfo['share_pic'] = Utility::getImageUrl($actinfo['share_pic']);
        if($actinfo && $actinfo['top_pic']) $actinfo['top_pic'] = Utility::getImageUrl($actinfo['top_pic']);
        return $this->display('variation/activity-edit',array('info'=>$actinfo,'depots'=>$depots));
    }

    public function postEdit(){
        $act_id = Input::get('act_id',false);
        if(!$act_id) return $this->back('数据错误');
        $act_info = VariationActivity::getInfo($act_id);
        if(!$act_info) return $this->back('数据错误');
        $input = Input::all();
        $share_pic = Input::file('share_pic') ? true : false;
        $direcotr = isset($input['director']) ? serialize($input['director']) : serialize(array());
        $set_money = isset($input['money']) ? true : false;
        $set_s_money = isset($input['s_money']) ? true : false;
        $rule = array('title'=>'required','starttime'=>'required','endtime'=>'required','need_times'=>'required', 'reward'=>'required',
            'join'=>'required','rule'=>'required','article_id'=>'required','sharer'=>'depotvalid:'.$direcotr,'share_title'=>'required',
            'share_des'=>'required','director'=>'directvalid:'.$set_money,'money_value'=>'integer|required_with:money',
            's_money_value'=>'integer|required_with:s_money',
            'more_newer'=>'integer|required_with:is_spread','more_coin'=>'integer|required_with:is_spread');
        if(!$input['has_pic'] && !$share_pic) $rule['share_pic'] = 'required';
        $prompt = array('title.required'=>'请填写标题','starttime.required'=>'请选择开始时间','endtime.required'=>'请选择结束时间',
            'need_times.required'=>'请填写分享次数','sharer_des.required'=>'请填写分享人描述','reward.required'=>'请填写活动奖励',
            'join.required'=>'请填写参与方式','rule.required'=>'请填写活动规则','article_id.required'=>'请填写文章ID',
            'share_title.required'=>'请填写分享标题','sharer.depotvalid'=>'老用户奖励设置有误','money_value.required_with'=>'请填写新用户游币数',
            's_money_value.required_with'=>'请填写老用户游币数','director.directvalid'=>'新用户礼包数量上限','more_newer.integer'=>'人数必须为整数',
            'more_newer.required_with'=>'请填写人数','more_coin.integer'=>'游币数必须为整数','more_coin.required_with'=>'请填写游币数');
        if(!$input['has_pic'] && !$share_pic) $prompt['share_pic.required'] = '请选择分享LOGO';
        Validator::extend('directvalid',function($attr,$val,$param){
            $selet_count = count($val);
            if($param[0]){
                if($selet_count > 3) return false;
            }else{
                if($selet_count > 4) return false;
            }
            return true;
        });
        Validator::extend('depotvalid',function($attr,$val,$param)use($set_s_money){
            $directors = unserialize($param[0]);
            $sharer = $val;
            foreach($sharer as $row){
                if(in_array($row,$directors)) return false;
            }
            $selet_count = count($val);
            if($set_s_money){
                if($selet_count > 3) return false;
            }else{
                if($selet_count > 4) return false;
            }
            return true;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $is_show = isset($input['is_show']) ? 1 : 0;
            $act_data = array(
                'title'=>$input['title'],
                'starttime'=>strtotime($input['starttime']),
                'endtime'=>strtotime($input['endtime']),
                'need_times'=>$input['need_times'],
                'is_show'=>$is_show,
                'reward'=>$input['reward'],
                'join'=>$input['join'],
                'rule'=>$input['rule'],
                'article_id'=>$input['article_id'],
                'share_title'=>$input['share_title'],
                'share_des'=>$input['share_des'],
                'money'=>$set_money ? $input['money_value'] : 0,
                's_money'=>$set_s_money ? $input['s_money_value'] : 0,
                'updatetime'=>time()
            );
            if(isset($input['is_spread'])){
                $act_data['is_spread'] = 1;
                $act_data['more_newer'] = $input['more_newer'];
                $act_data['more_coin'] = $input['more_coin'];
            }
            if($input['share_pic']){
                $dir = '/userdirs/duang/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['share_pic']);
                $act_data['share_pic'] = $path;
            }
            if($input['top_pic']){
                $dir = '/userdirs/duang/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['top_pic']);
                $act_data['top_pic'] = $path;
            }
            $directors = isset($input['director']) ? $input['director'] : array();
            $sharers = isset($input['sharer']) ? $input['sharer'] : array();
            if(VariationActivity::updateActivityAndRelate($act_id,$act_data,$directors,$sharers)){
                $target_id=$input['article_id'];
                $target_title = $input['title'];
                $platform = 'android';
                $tpl_ename = 'android_share_tpl_activity_info';
                $title = $input['share_title'];
                $icon = isset($act_data['share_pic']) ? $act_data['share_pic'] : '';
                $redirect_url = 'http://share.youxiduo.com/android/share/home?hashcode='.$act_info['hashcode'];
                $content = $input['share_des'];
                $start_time = strtotime($input['starttime']);
                $end_time = strtotime($input['endtime']);
                $is_show = $is_show;
                ShareService::saveAdvInfoByTargetId($target_id,$target_title,$platform,$tpl_ename,$title,$icon,$content,$redirect_url,$start_time,$end_time,$is_show);
                return $this->redirect('/duang/variation/list','更新成功');
            }else{
                return $this->back('更新失败，请重试');
            }
        }
    }

    public function getDel(){
        $act_id = Input::get('act_id',false);
        if(!$act_id) return Response::json(array('state'=>0,'msg'=>'数据错误'));
        if(VariationActivity::deleteActAndRelate($act_id)){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请刷新页面后重试'));
        }
    }

    public function getMoney($act_id){
        if(!$act_id) return $this->back('数据错误');
        $page = Input::get('page',1);
        $limit= 10;
        $list = VariationMoney::getList($act_id,$page,$limit);
        $users = array();
        if($list){
            $uids = array();
            foreach($list as $row){
                $uids[] = $row['user_id'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
            if(is_array($tmp_users)){
                foreach($tmp_users as $user){
                    $users[$user['uid']] = $user;
                }
            }
        }
        $total = VariationMoney::getListCount($act_id);
        $pager = Paginator::make(array(),$total,$limit)->links();
        return $this->display('variation/money',array('list'=>$list,'users'=>$users,'pagination'=>$pager));
    }

    public function getShareRecord($act_id){
        if(!$act_id) return $this->back('数据错误');
        $fuid = Input::get('from_uid',false);
        $page = Input::get('page',1);
        $limit= 10;
        $list = VariationMain::getShareRecordList($act_id,$page,$limit,$fuid);
        $users = array();
        if($list){
            $uids = array();
            foreach($list as $row){
                $uids[] = $row['from_uid'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
            if(is_array($tmp_users)){
                foreach($tmp_users as $user){
                    $users[$user['uid']] = $user;
                }
            }
        }
        $total = VariationMain::getShareRecordListCount($act_id,$fuid);
        $pager = Paginator::make(array(),$total,$limit);
        $pager->appends(array('from_uid'=>$fuid));
        return $this->display('variation/share-record',array('list'=>$list,'users'=>$users,'pagination'=>$pager->links(),'search'=>array('act_id'=>$act_id,'from_uid'=>$fuid)));
    }
    public function getVariationSearch()
    {
        $keyword = Input::get('keyword');
        $search = array();
        $search['title'] = $keyword;

        $page = Input::get('page',1);
        $pagesize = 6;
        $data = array();
        $data['keyword'] = $keyword;
        $result = VariationActivity::getList($page,$pagesize,$keyword);
        if(isset($result)&&$result){
            foreach($result as &$item){
                $item['share_url'] = 'http://share.youxiduo.com/android/share/home?hashcode='.$item['hashcode'];
            }
        }
        $data['games'] = $result;
        $pager = Paginator::make(array(),VariationActivity::getListCount($keyword),$pagesize);
        $pager->appends(array('keyword'=>$keyword));
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = VariationActivity::getListCount($keyword);
//        print_r($result);
        $html = $this->html('pop-variation-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function postSaveQueryTaskReport()
    {

    }

    public function getQueryTaskReport()
    {
        $task_id = Input::get('task_id');
        $activity_id = Input::get('activity_id');

        $data['task_id'] = $task_id;
        $data['activity_id'] = $activity_id;
        $yesterday = date('Y-m-d 00:00:00');
        if($activity_id){
            //获取所有完成指定任务的uid
            $search = array('taskId'=>$task_id,'taskStatus'=>1,'createTimeEnd'=>$yesterday,'pageIndex'=>1,'pageSize'=>10000);
            $res = TaskV3Service::query_screenshot_list($search);
            $uids = array();
            if(!$res['errorCode']&&$res['result']){
                //$total = $res['totalCount'];
                $result = $res['result'];
                foreach($result as $k=>$row){
                    $uids[] = $row['uid'];
                }
            }
            if($uids && count($uids)){
                //获取所有已完成邀请任务的记录
                $finish_rows = VariationMain::db()
                    ->where('activity_id','=',$activity_id)
                    ->whereIn('from_uid',$uids)
                    ->where('uid','>',0)
                    ->select('from_uid','uid')->get();

                //只保留推广的用户完成任务的推广员
                foreach($finish_rows as $row){
                    if(in_array($row['uid'],$uids)){
                        $finish_uids[] = $row['from_uid'];
                    }
                }

                $activity = VariationActivity::db()->where('activity_id','=',$activity_id)->first();
                if($activity){
                    if($activity['save_datetime'] != date('Ymd')){
                        //已发奖的
                        $exists_uids = $activity['task_reward_uids'] ? explode(',',$activity['task_reward_uids']) : array();
                        //未发奖的
                        $reward_uids = array_diff($finish_uids,$exists_uids);
                        VariationActivity::db()->where('activity_id','=',$activity_id)
                            ->where('save_datetime','<>',date('Ymd'))
                            ->update(array(
                                    'save_datetime'=>date('Ymd'),
                                    'task_reward_uids'=>implode(',',array_merge($exists_uids,$reward_uids)),
                                    'task_today_uids'=>implode(',',$reward_uids)
                                )
                            );
                    }else{
                        $reward_uids = $activity['task_today_uids'] ? explode(',',$activity['task_today_uids']) : array();
                    }

                }
                $reward_uids = array_unique($reward_uids);
                $data['uids_count'] = count($reward_uids);
                $data['uids_str'] = implode(',',$reward_uids);
            }

        }
        return $this->display('activity-task-query',$data);
    }
}