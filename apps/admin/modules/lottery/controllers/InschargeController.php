<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/27
 * Time: 16:42
 */
namespace modules\lottery\controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Activity\Lottery\LotteryService;
use Youxiduo\Activity\Model\DcActivity;
use Youxiduo\Activity\Model\DcJoin;
use Youxiduo\Activity\Model\DcLottery;
use Youxiduo\Android\PushService;
use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Youxiduo\Activity\Model\DcPrize;
use libraries\Helpers;
use Illuminate\Support\Facades\Config;

class InschargeController extends BackendController{
    private $form_set = array();

    public function _initialize(){
        $this->current_module = 'lottery';
        $this->form_set = Config::get('yxd.charge_form');
    }

    public function getActivitySearch(){
        $page = Input::get('page',1);
        $page_size = 10;
        $title = Input::get('name','');
        $starttime = Input::get('start_time','') ? strtotime(Input::get('start_time')) : '';
        $endtime = Input::get('end_time','') ? strtotime(Input::get('end_time')) : '';
        $result = DcActivity::getInfo(false,$title,$starttime,$endtime,'',$page,$page_size);

        if($result){
            $lotids = $prizes = array();
            foreach($result as $row){
                $lotids[] = $row['lottery_id'];
            }
            $tmp = DcLottery::getAllInfoByIds($lotids);
            if($tmp){
                foreach($tmp as $row){
                    $prizes[$row['lottery_id']][]= $row;
                }
                foreach($result as &$row){
                    $row['lot_info'] = array_key_exists($row['lottery_id'],$prizes) ? $prizes[$row['lottery_id']] : false;
                }
            }
        }
        $total = DcActivity::getInfoCount(false,$title,$starttime,$endtime,'');
        $pager = Paginator::make(array(),$total,$page_size);
        $pager->appends(array());
        $vdata = array(
            'activities' => $result,
            'pagination' => $pager->links()
        );
        return $this->display('activity-list',$vdata);
    }

    public function getActivityAdd($lotid=''){
        $cj_infos = DcLottery::getInfo();
        $form_set = $this->form_set;
        return $this->display('activity-add',array('lotteries'=>$cj_infos,'lotid'=>$lotid,'formset'=>$form_set));
    }

    public function postActivityAdd(){
        $input = Input::all();
        $rule = array('name'=>'required|validformset:'.implode(',',array_keys($input)),'start_time'=>'required','end_time'=>'required','command'=>'required');
        $prompt = array('name.required'=>'请填写活动名称','name.validformset'=>'请至少选择一个表单设置','start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间',
            'command.required'=>'请输入活动暗语');
        $form_set = $this->form_set;
        Validator::extend('validformset',function($attr,$val,$param)use($form_set){
            $valid = false;
            foreach($param as $row){
                if(array_key_exists($row,$form_set)){
                    $valid = true;
                }
            }
            return $valid;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $file = Input::file('icon');
            $dir = '/lottery/';
            $path = Helpers::uploadPic($dir,$file);

            $hashcode = sha1(str_random(8));
            $data['hashcode'] = $hashcode;
            $sub_info = array();
            foreach($input as $key=>$row){
                if(array_key_exists($key,$this->form_set)) $sub_info[$key] = $this->form_set[$key];
            }
            if($sub_info){
                foreach($sub_info as &$row){
                    $row = urlencode($row);
                }
            }
            $data['lottery_id'] = $input['lottery_id'];
            $data['name'] = $input['name'];
            $data['command'] = $input['command'];
            $data['sub_info'] = serialize($sub_info);
            $data['icon_path'] = $path;
            $data['add_time'] = time();
            $data['start_time'] = strtotime($input['start_time']);
            $data['end_time'] = strtotime($input['end_time']);
            if(isset($input['is_open'])){
                $data['is_open'] = 1;
            }else{
                $data['is_open'] = 0;
            }

            if(DcActivity::add($data)){
                $commands = DcActivity::getValidCommands();
                $commands = implode(',',$commands);
                LotteryService::updateCommandsCache($commands);
                return Redirect::to('lottery/inscharge/activity-search')->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请刷新页面重试');
            }
        }
    }

    public function getActivityEdit($actid=''){
        if(!$actid || !is_numeric($actid)) return Redirect::to('lottery/inscharge/activity-edit');
        $lotinfo = DcLottery::getInfo();
        $actinfo = DcActivity::getInfo($actid);
        if($actinfo) {
            $sub_info = json_encode(unserialize($actinfo['sub_info']));
            $actinfo['sub_info'] = json_decode(urldecode($sub_info),true);
            foreach($this->form_set as $key=>$item){
                if(array_key_exists($key,$actinfo['sub_info'])) $actinfo['sub_info'][$key] = true;
            }
            $actinfo['icon_path'] = Utility::getImageUrl($actinfo['icon_path']);
        }
        return $this->display('activity-edit',array('lotinfo'=>$lotinfo,'actinfo'=>$actinfo,'formset'=>$this->form_set));
    }

    public function postActivityEdit($actid=''){
        if(!$actid || !is_numeric($actid)) return $this->back()->with('global_tips','数据错误，请刷新页面后重试');
        $input = Input::all();
        $rule = array('name'=>'required|validformset:'.implode(',',array_keys($input)),'start_time'=>'required','end_time'=>'required','command'=>'required');
        $prompt = array('name.required'=>'请填写活动名称','name.validformset'=>'请至少选择一个表单设置','start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间',
            'command.required'=>'请输入活动暗语');
        $form_set = $this->form_set;
        Validator::extend('validformset',function($attr,$val,$param)use($form_set){
            $valid = false;
            foreach($param as $row){
                if(array_key_exists($row,$form_set)){
                    $valid = true;
                }
            }
            return $valid;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $file = Input::file('icon');
            $path = null;
            if($file){
                $dir = '/lottery/';
                $path = Helpers::uploadPic($dir,$file);
            }

            if($path) {
                $data['icon_path'] = $path;
            }

            $sub_info = array();
            foreach($input as $key=>$row){
                if(array_key_exists($key,$this->form_set)) $sub_info[$key] = $this->form_set[$key];
            }
            if($sub_info){
                foreach($sub_info as &$row){
                    $row = urlencode($row);
                }
            }
            $data['name'] = $input['name'];
            $data['command'] = $input['command'];
            $data['sub_info'] = serialize($sub_info);
            $data['start_time'] = strtotime($input['start_time']);
            $data['end_time'] = strtotime($input['end_time']);
            $data['update_time'] = time();
            $data['lottery_id'] = $input['lottery_id'];
            if(isset($input['is_open'])){
                $data['is_open'] = 1;
            }else{
                $data['is_open'] = 0;
            }

            if(DcActivity::update($actid,$data)){
                $commands = DcActivity::getValidCommands();
                $commands = implode(',',$commands);
                LotteryService::updateCommandsCache($commands);
                return $this->back()->with('global_tips','更新成功');
            }else{
                return $this->back()->with('global_tips','更新失败，请刷新页面重试');
            }
        }
    }

    public function getActivityDel(){
        $actid = Input::get('ids');
        if(!$actid) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        if(DcActivity::delete($actid)){
            return Response::json(array('status'=>1,'msg'=>'删除成功'));
        }else{
            return Response::json(array('status'=>0,'msg'=>'删除失败，请刷新页面后重试'));
        }
    }

    public function getLotterySearch(){
        $page = Input::get('page',1);
        $page_size = 10;
        $title = Input::get('titel','');
        $starttime = Input::get('starttime','') ? strtotime(Input::get('starttime')) : '';
        $endtime = Input::get('endtime','') ? strtotime(Input::get('endtime')) : '';
        $result = DcLottery::getInfo(false,$title,false,$starttime,$endtime,$page,$page_size);
        if($result){
            $actids = $prizes = array();
            foreach($result as $row){
                $actids[] = $row['lottery_id'];
            }
            $tmp = DcPrize::getInfoByIds($actids);
            if($tmp){
                foreach($tmp as $row){
                    $prizes[$row['lottery_id']][]= $row;
                }
                foreach($result as &$row){
                    $row['prize_info'] = array_key_exists($row['lottery_id'],$prizes) ? $prizes[$row['lottery_id']] : false;
                }
            }
        }
        $total = DcLottery::getInfoCount(false,$title,false,$starttime,$endtime);
        $pager = Paginator::make(array(),$total,$page_size);
        $pager->appends(array());
        $vdata = array(
            'activities' => $result,
            'pagination' => $pager->links()
        );
        return $this->display('lottery-list',$vdata);
    }

    public function getLotteryAdd(){
        return $this->display('lottery-add');
    }

    public function postLotteryAdd(){
        $input = Input::all();
        $rule = array('title'=>'required','type'=>'required');
        if(isset($input['type']) && $input['type'] == 1) $rule['join_times'] = 'required|numeric|min:1';
        if(isset($input['type']) && $input['type'] == 2) {
            $rule['prize_way'] = 'required';
            if(isset($input['prize_way']) && $input['prize_way'] == 1) {
                $rule['send_time'] = 'required';
                $rule['winner_num'] = 'required';
            }
            if(isset($input['prize_way']) && $input['prize_way'] == 2) $rule['winner_num'] = 'required';
        }

        if(isset($input['need_coin'])) $rule['coin_num'] = 'required|numeric|min:1';
        $prompt = array('title.required'=>'请填写抽奖名称','type.required'=>'请选择抽奖类型', 'join_times.required'=>'请填写限制次数','prize_way.required'=>'请选择发奖方式','send_time.required'=>'请选择发奖时间','winner_num.required'=>'请填写获奖人数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $params = array(
                'title' => $input['title'],
                'lot_type' => $input['type'],
                'valid' => isset($input['valid']) ? 1 : 0,
                'need_coin' => isset($input['need_coin']) ? 1 : 0,
                'create_time' => time()
            );
            if($params['need_coin']) $params['coin_num'] = $input['coin_num'];
            if($input['type'] == 1){    //随时抽
                $params['join_times'] = $input['join_times'];
            }else{  //定时抽
                $params['prize_way'] = $input['prize_way'];
                if($input['prize_way'] == 1){   //自动发送
                    $params['send_time'] = strtotime($input['send_time']);
                    $params['winner_num'] = $input['winner_num'];
                }else{  //手动发送
                    $params['winner_num'] = $input['winner_num'];
                }
            }
            if(DcLottery::add($params)){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请刷新页面重试');
            }
        }
    }

    public function getLotteryEdit($lotid=''){
        if(!$lotid || !is_numeric($lotid)) return Redirect::to('lottery/inscharge/lottery-search');
        $lot_info = DcLottery::getInfo($lotid);
        return $this->display('lottery-edit',array('lottery'=>$lot_info));
    }

    public function postLotteryEdit($lotid=''){
        if(!$lotid || !is_numeric($lotid)) return $this->back()->with('global_tips','数据错误');
        $input = Input::all();
        $rule = array('title'=>'required','type'=>'required');
        if(isset($input['type']) && $input['type'] == 1) $rule['join_times'] = 'required|numeric|min:1';
        if(isset($input['type']) && $input['type'] == 2) {
            $rule['prize_way'] = 'required';
            if(isset($input['prize_way']) && $input['prize_way'] == 1) {
                $rule['send_time'] = 'required';
                $rule['winner_num'] = 'required';
            }
            if(isset($input['prize_way']) && $input['prize_way'] == 2) $rule['winner_num'] = 'required';
        }

        if(isset($input['need_coin'])) $rule['coin_num'] = 'required|numeric|min:1';
        $prompt = array('title.required'=>'请填写抽奖名称','type.required'=>'请选择抽奖类型', 'join_times.required'=>'请填写限制次数','prize_way.required'=>'请选择发奖方式','send_time.required'=>'请选择发奖时间','winner_num.required'=>'请填写获奖人数');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $params = array(
                'title' => $input['title'],
                'lot_type' => $input['type'],
                'valid' => isset($input['valid']) ? 1 : 0,
                'need_coin' => isset($input['need_coin']) ? 1 : 0,
                'update_time' => time()
            );
            if($params['need_coin']) $params['coin_num'] = $input['coin_num'];
            if($input['type'] == 1){    //随时抽
                $params['join_times'] = $input['join_times'];
            }else{  //定时抽
                $params['prize_way'] = $input['prize_way'];
                if($input['prize_way'] == 1){   //自动发送
                    $params['send_time'] = strtotime($input['send_time']);
                    $params['winner_num'] = $input['winner_num'];
                }else{  //手动发送
                    $params['winner_num'] = $input['winner_num'];
                }
            }
            if(DcLottery::update($lotid,$params)){
                return $this->back()->with('global_tips','更新成功');
            }else{
                return $this->back()->with('global_tips','更新失败，请刷新页面重试');
            }
        }
    }

    public function getLotteryDel(){
        $ids = Input::get('ids');
        if(!$ids) return Response::json(array('state'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        if(DcLottery::delete($ids)){
            return Response::json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return Response::json(array('state'=>0,'msg'=>'删除失败，请刷新页面后重试'));
        }
    }

    public function getPrizeSearch(){
        $page = Input::get('page',1);
        $limit = 10;
        $prizes = DcPrize::getInfo('','',$page,$limit);
        if($prizes){
            $lot_ids = $lotinfos = array();
            foreach($prizes as $row){
                $lot_ids[$row['lottery_id']] = $row['lottery_id'];
            }
            if($lot_ids){
                $lotinfos = DcLottery::getInfoByIds($lot_ids);
                foreach($prizes as &$row){
                   $row['lot_info'] = array_key_exists($row['lottery_id'],$lotinfos) ? $lotinfos[$row['lottery_id']] : false;
                }   
            }
        }
        $total = DcPrize::getInfoCount();
        $pager = Paginator::make(array(),$total,$limit);
        $pagination = $pager->links();
        return $this->display('prize-list',array('prizes'=>$prizes,'pagination'=>$pagination));
    }

    public function getPrizeAdd($actid=''){
        $cj_infos = DcLottery::getInfo();
        return $this->display('prize-add',array('activities'=>$cj_infos,'actid'=>$actid));
    }

    public function postPrizeAdd(){
        $input = Input::only('lottery_id','title','probab','number','des');
        $rule = array('lottery_id'=>'required','title'=>'required','probab'=>'required|numeric|min:0','number'=>'required|min:1|integer');
        $prompt = array('lottery_id.required'=>'请选择抽奖活动','title.required'=>'请填写奖项名称','probab.required'=>'请填写概率','probab.numeric'=>'概率必须为数字',
            'probab.min'=>'概率不能小于0','number'=>'请填写正整数个数值');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            if(DcPrize::add($input)){
                return $this->back()->with('global_tips','添加成功');
            }else{
                return $this->back()->with('global_tips','添加失败，请刷新页面重试');
            }
        }
    }

    public function getPrizeEdit($przid=''){
        if(!$przid || !is_numeric($przid)) return Redirect::to('/lottery/inscharge/prize-search');
        $cj_infos = DcLottery::getInfo();
        $prize_info = DcPrize::getInfo($przid);
        return $this->display('prize-edit',array('lotteries'=>$cj_infos,'prize'=>$prize_info));
    }

    public function postPrizeEdit($przid=''){
        if(!$przid || !is_numeric($przid)) return $this->back()->with('global_tips','数据错误');
        $input = Input::only('lottery_id','title','probab','number','des');
        $rule = array('lottery_id'=>'required','title'=>'required','probab'=>'required|numeric|min:0','number'=>'required|min:1|integer');
        $prompt = array('lottery_id.required'=>'请选择抽奖活动','title.required'=>'请填写奖项名称','probab.required'=>'请填写概率','probab.numeric'=>'概率必须为数字',
            'probab.min'=>'概率不能小于0','number'=>'请填写正整数个数值');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            if(DcPrize::update($przid,$input)){
                return Redirect::to('/lottery/inscharge/prize-search')->with('global_tips','更新成功');
            }else{
                return Redirect::to('/lottery/inscharge/prize-search')->with('global_tips','更新失败，请刷新页面重试');
            }
        }
    }

    public function getPrizeDel($przid=''){
        if(!$przid || !is_numeric($przid)) return Response::json(array('state'=>0,'msg'=>'数据错误'));
        if(DcPrize::delete($przid)){
            return Response::json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return Response::json(array('state'=>0,'msg'=>'删除失败，请刷新页面重试'));
        }
    }

    public function getJoinList($actid=''){
        if(!$actid || !is_numeric($actid)) return Redirect::to('/lottery/inscharge/activity-search');
        $actinfo = DcActivity::getInfo($actid);
        if(!$actinfo) return $this->back()->with('global_tips','活动已删除');
        $lotinfo = DcLottery::getInfo($actinfo['lottery_id']);
        if(!$lotinfo) return $this->back()->with('global_tips','抽奖方式已删除');

        $page = Input::get('page',1);
        $limit = 10;
        $lotinfo['loted'] = $lotinfo['send_time'] > time() ? 0 : 1;
        $join_info = DcJoin::getInfo('',$actid,0,$page,$limit);
        
        $total = DcJoin::getInfoCount('',$actid,0);
        $uids = array();
        if($join_info){
            foreach($join_info as $item){
                $uids[] = $item['user_id'];
            }
            $uids = array_unique($uids);
            $uinfos = UserService::getMultiUserInfoByUids($uids);
            if(is_array($uinfos)){
                $tmp_uinfo = array();
                foreach($uinfos as $row){
                    $tmp_uinfo[$row['uid']] = $row;
                }
                foreach($join_info as &$item){
                    if(array_key_exists($item['user_id'],$tmp_uinfo)) $item['uinfo'] = $tmp_uinfo[$item['user_id']];
                }
                unset($uinfos);
            }
        }
        $pager = Paginator::make(array(),$total,$limit)->links();
        return $this->display('join-list',array('actid'=>$actid,'list'=>$join_info,'lotinfo'=>$lotinfo,'pagination'=>$pager));
    }

    public function getPrizeList(){
        $join_id = Input::get('jid',false);
        if(!$join_id) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        $joininfo = DcJoin::getInfoById($join_id);
        if(!$joininfo) return Response::json(array('status'=>0,'msg'=>'数据错误,请刷新页面后重试'));
        $actinfo = DcActivity::getInfo($joininfo['activity_id']);
        if(!$actinfo) return Response::json(array('status'=>0,'msg'=>'活动信息不存在！'));
        $lotinfo = DcLottery::getInfo($actinfo['lottery_id']);
        if(!$lotinfo) return Response::json(array('status'=>0,'msg'=>'抽奖信息不存在！'));
        $tmp_prizeinfo = DcPrize::getInfo('',$lotinfo['lottery_id']);
        if(!$tmp_prizeinfo) return Response::json(array('status'=>0,'msg'=>'奖项不存在！'));
        $prizeinfo = array();
        foreach($tmp_prizeinfo as $row){
            $prizeinfo[$row['prize_id']] = $row;
        }
        $setted = DcJoin::getWinInfo('',$joininfo['activity_id'],1,true);
        if($setted){
            $count_arr = array();
            foreach($setted as $row){
                if(array_key_exists($row['prize_id'],$prizeinfo)){
                    isset($count_arr[$row['prize_id']]) ? $count_arr[$row['prize_id']]++ : $count_arr[$row['prize_id']] = 1;
                    if($count_arr[$row['prize_id']] >= $prizeinfo[$row['prize_id']]['number']) unset($prizeinfo[$row['prize_id']]);
                }
            }
        }
        return Response::json($prizeinfo);
    }

    public function getSetWin(){
        $join_id = Input::get('join_id');
        $prize_id = Input::get('prize_id');
        if(!$join_id || !$prize_id) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        if($prize_id == -1){
            $uparr = array('if_win'=>0,'prize_id'=>'','prize_name'=>'','prize_des'=>'','update_time'=>time());
        }else{
            $prizeinfo = DcPrize::getInfo($prize_id);
            $uparr = array('if_win'=>1,'prize_id'=>$prizeinfo['prize_id'],
            'prize_name'=>$prizeinfo['title'],'prize_des'=>$prizeinfo['des'],'update_time'=>time());
        }
        if(DcJoin::update($join_id,$uparr)){
            return Response::json(array('status'=>1,'msg'=>'操作成功'));
        }else{
            return Response::json(array('status'=>0,'msg'=>'操作失败，请刷新页面后重试'));
        }
    }

    public function getSetRecharge(){
        $join_id = Input::get('join_id');
        if(!$join_id) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        $joininfo = DcJoin::getInfoById($join_id);
        if(!$joininfo) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        $actinfo = DcActivity::getInfo($joininfo['activity_id']);
        if(!$actinfo) return Response::json(array('status'=>0,'msg'=>'数据错误，请刷新页面后重试'));
        $send_rst = ActivityService::sendAndroidMsg($joininfo['user_id'], '您参与的代充抽奖活动（'.$actinfo['name'].'）已充值完成，请注意查收','users');
        if($send_rst && $send_rst['errorCode'] == 0){
            DcJoin::update($join_id,array('update_time'=>time(),'recharge_msg'=>1));
            return Response::json(array('status'=>1,'msg'=>'发送成功'));
        }else{
            return Response::json(array('status'=>0,'msg'=>'发送消息失败，请重新发送'));
        }
    }

    public function getPushWinMsg(){
        $actid = Input::get('actid');
        if(!$actid) return Response::json(array('state'=>0,'msg'=>'数据错误，请刷新后重试'));
        $actinfo = DcActivity::getInfo($actid);
        $lotinfo = DcLottery::getInfo($actinfo['lottery_id']);
        if($lotinfo['prize_way']==2){
            $notwinners = DcJoin::getWinInfo('',$actid,0);
            if($notwinners){
                foreach($notwinners as $row){
                    $res = ActivityService::sendAndroidMsg($row['user_id'], '很遗憾，您参加的'.$actinfo['name'].'（活动）未获奖，祝下次好运', 'users');
                    if($res && $res['errorCode']==0){
                        DcJoin::update($row['join_id'],array('msg_send'=>1,'update_time'=>time()));
                    }
                }
            }
        }
        $winners = DcJoin::getWinInfo('',$actid,1);
        if($winners){
            $bigtitle = $actinfo['name'];
            $sub_info = unserialize($actinfo['sub_info']);
            if($sub_info){
                foreach($sub_info as &$row){
                    $row = urldecode($row);
                }
            }
            $appends = $sub_info;
            if(PushService::sendLotterySubscribeMessage($winners,$actid,$bigtitle,$actinfo['icon_path'],$appends)){
                return Response::json(array('state'=>1,'msg'=>'发送成功！'));
            }else{
                return Response::json(array('state'=>0,'msg'=>'未完全发送完毕！请继续发送。'));
            }
        }
        return Response::json(array('state'=>1,'msg'=>'发送成功！'));
    }

    public function getFormPush(){
        return $this->display('form-push',array('formset'=>$this->form_set));
    }

    public function postFormPush(){
        $input = Input::all();
        $input2 = $input;
        unset($input2['uids']);
        unset($input2['gid']);
        unset($input2['title']);
        unset($input2['des']);
        $input_ser = serialize($input2);
        $rule = array('des'=>'required','title'=>'required','gid'=>'required','uids'=>'required|uidvalid|setvalid:'.$input_ser);
        $form_set = $this->form_set;
        $prompt = array('title.required'=>'请填写推送标题','des.required'=>'请填写推送描述','gid.required'=>'请选择游戏','uids.required'=>'请填写用户id','uids.uidvalid'=>'请按规则填写用户ID','uids.setvalid'=>'至少选择一个选项');
        Validator::extend('uidvalid',function($attr,$val,$param){
            $uids = explode(',',$val);
            foreach($uids as $uid){
                if(!is_numeric($uid)) return false;
            }
            return true;
        });
        Validator::extend('setvalid',function($attr,$val,$param)use($form_set){
            $valid = false;
            $input_all = unserialize($param[0]);
            foreach($input_all as $key=>$row){
                if(array_key_exists($key,$form_set)){
                    $valid = true;
                }
            }
            return $valid;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $uids = explode(',',$input['uids']);
            $params = array();
            foreach($input2 as $key=>$item){
                if(isset($form_set[$key])){
                    $params[$key] = $form_set[$key];
                }
            }
            $params['gid_hidden'] = $input['gid'];
            PushService::sendSubscribeMessage($uids,$input['title'],$input['des'],'http://img.youxiduo.com/bbs/logopic/2015/05/20150519105027eECB.png',$params);
            return $this->back('发送成功');
        }
    }

    public function getChargeInformation(){
        $actid = Input::get('actid');
        $joinid = Input::get('joinid');
        if(empty($actid) || empty($joinid)){
              return Response::json(array('state'=>0,'msg'=>'编号获取失败！'));   
        }
        $actinfo =DcActivity::getInfo($actid);
        $actinfo = unserialize($actinfo['sub_info']);
        $dcjoin=DcJoin::getInfoById($joinid);
        if(empty($actinfo) || empty($dcjoin)){
            return Response::json(array('state'=>0,'msg'=>'数据获取失败！'));   
        }
        $dcjoin = unserialize($dcjoin['sub_info']);
        $arr=array();
        foreach($actinfo as $key=>&$row){
            $actinfo[$key] = urldecode($row);
            if(array_key_exists($key, $dcjoin)){
                $arr[$actinfo[$key]]=$dcjoin[$key];
            }
        }
        return Response::json(array('state'=>1,'msg'=>$arr));   
    }

    public function getFormList(){
        $page = Input::get('page',1);
        $size = 10;
        $params = array(
            'pageIndex' => $page,
            'pageSize' => $size
        );
        $total = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/charge_info_number',$params);
        if(!$total['errorCode'] && $total['result']) $total = $total['result'];
        $list = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/charge_info_list',$params);
        if(!$list['errorCode']) $list = $list['result'];
        $uids = $users = array();
        if($list){
            foreach($list as $row){
                $uids[] = $row['uid'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids);
            if(is_array($tmp_users)){
                foreach($tmp_users as $user){
                    $user['avatar'] = Utility::getImageUrl($user['avatar']);
                    $users[$user['uid']] = $user;
                }
            }
        }
        $pager = Paginator::make(array(),$total,$size);
        return $this->display('form-list',array('list'=>$list,'users'=>$users,'pagination'=>$pager->links()));
    }

    public function getFormEdit($id){
        if(!$id) return $this->back('数据错误');
        $info = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/charge_info_list',array('id'=>$id));
        if(!$info['errorCode'] && $info['result']) $info = $info['result'][0];
        return $this->display('form-edit',array('info'=>$info));
    }

    public function postFormEdit(){
        $chargeId = Input::get('chargeId',false);
        if(!$chargeId) return $this->back('数据错误');
        $input = Input::all();
        $params = array(
            'chargeId'=>$chargeId,
            'gid'=>$input['gid'],
            'gname'=>$input['gname'],
            'gchannel'=>$input['gchannel'],
            'gamezone'=>$input['gamezone'],
            'gameAccount'=>$input['gameAccount'],
            'gamePassword'=>$input['gamePassword'],
            'roleName'=>$input['roleName'],
            'qq'=>$input['qq'],
            'mobile'=>$input['mobile'],
            'address'=>$input['address'],
            'uid'=>$input['uid'],
            'createTime'=>$input['createTime'],
            'updateTime'=>date('Y-m-d H:i:s',time())
        );
        $up_result = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/update_charge_info',$params,'POST');
        if(!$up_result['errorCode'] && $up_result['result']){
            return $this->redirect('lottery/inscharge/form-list','保存成功');
        }else{
            return $this->back('保存失败，请重试');
        }
    }

    public function postAjaxSendNotice(){
        $type = Input::get('type',false);
        $data = Input::get('data',false);
        if(!$type || !$data) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $vdata = array('state'=>0,'msg'=>'操作失败，请重试');
        switch($type){
            case 'success':
                foreach($data as $item){
                    if($item['success']) continue;
                    $msg = '尊敬的用户，已帮您在 '.$item['gid'].' 活动中的 '.$item['gname'].' 游戏内充值完成，请您查收。如有疑问请联系客服：QQ2985659531';
                    $res = ActivityService::sendAndroidMsg($item['uid'], $msg, 'users');
                    if($res && $res['errorCode']==0){
                        $params = array('chargeId'=>$item['id'],'isSuccess'=>'true','isNotice'=>'false');
                        $up_res = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/update_charge_info',$params,'POST');
                        if(!$up_res['errorCode'] && $up_res['result']){
                            $vdata = array('state'=>1,'msg'=>'全部发送成功');
                        }else{
                            $vdata = array('state'=>0,'msg'=>'部分用户发送失败，请重试');
                            break;
                        }
                    }
                }
                break;
            case 'error':
                foreach($data as $item){
                    if($item['success'] || $item['error']) continue;
                    $msg = '尊敬的用户，由于目前 '.$item['gid'].' 活动中的 '.$item['gname'].' 游戏无法代充，我们的客服将在1个工作日内联系您，用其他奖励替换。请您留意。如有疑问请联系客服：QQ2985659531';
                    $res = ActivityService::sendAndroidMsg($item['uid'], $msg, 'users');
                    if($res && $res['errorCode']==0){
                        $params = array('chargeId'=>$item['id'],'isNotice'=>'true');
                        $up_res = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/update_charge_info',$params,'POST');
                        if(!$up_res['errorCode'] && $up_res['result']){
                            $vdata = array('state'=>1,'msg'=>'全部发送成功');
                        }else{
                            $vdata = array('state'=>0,'msg'=>'部分用户发送失败，请重试');
                            break;
                        }
                    }
                }
                break;
            case 'broken':
                foreach($data as $item){
                    if($item['success']) continue;
                    $msg = '尊敬的用户，由于您在 '.$item['gid'].' 活动中的 '.$item['gname'].' 游戏信息填写有误，导致无法充值，请您再次填写正确信息。我们将尽快帮您完成充值。如有疑问请联系客服：QQ2985659531';
                    $form_info = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/charge_info_list',array('id'=>$item['id']));
                    $res = ActivityService::sendAndroidMsg($item['uid'], $msg, 'users');
                    if($res && $res['errorCode']==0){
                        if(!$form_info['errorCode'] && $form_info['result']){
                            $form_info = current($form_info['result']);
                            $params = array();
                            $index = 1;
                            $params['gid_hidden'] = $form_info['gid'];
                            if(isset($form_info['gname'])){
                                $params['gname_'.$index] = '游戏名';
                                $index++;
                            }
                            if(isset($form_info['gchannel'])){
                                $params['gchannel_'.$index] = '渠道';
                                $index++;
                            }
                            if(isset($form_info['gamezone'])){
                                $params['gamezone_'.$index] = '游戏区服';
                                $index++;
                            }
                            if(isset($form_info['gameAccount'])){
                                $params['gameAccount_'.$index] = '游戏登录账号';
                                $index++;
                            }
                            if(isset($form_info['gamePassword'])){
                                $params['gamePassword_'.$index] = '游戏登录密码';
                                $index++;
                            }
                            if(isset($form_info['roleName'])){
                                $params['roleName_'.$index] = '角色名';
                                $index++;
                            }
                            if(isset($form_info['qq'])){
                                $params['qq_'.$index] = 'QQ号';
                                $index++;
                            }
                            if(isset($form_info['mobile'])){
                                $params['mobile_'.$index] = '手机号';
                                $index++;
                            }
                            if(isset($form_info['address'])){
                                $params['address_'.$index] = '收货地址';
                            }
                            $gname = isset($form_info['gname']) ? $form_info['gname'] : '';
                            $title = $form_info['gid'].' 活动 '.$gname.' 游戏信息填写通知';
                            $des = '请再次填写 '.$form_info['gid'].' 活动的 '.$gname.' 游戏信息';
                            $send_res = PushService::sendSubscribeMessage(array($item['uid']),$title,$des,'http://img.youxiduo.com/bbs/logopic/2015/05/20150519105027eECB.png',$params);
                            if(!$send_res['errorCode']){
                                Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/delete_charge_info',array('chargeId'=>$item['id']));
                                $vdata = array('state'=>1,'msg'=>'全部发送成功');
                            }else{
                                $vdata = array('state'=>0,'msg'=>'部分用户发送失败，请重试');
                                break;
                            }
                        }else{
                            $vdata = array('state'=>0,'msg'=>'部分用户发送失败，请重试');
                            break;
                        }
                    }
                }
                break;
            default :
                $vdata = array('state'=>0,'msg'=>'类型错误');
        }
        return $this->json($vdata);
    }

    public function getAjaxFormDel(){
        $chargeId = Input::get('charge_id',false);
        if(!$chargeId) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/delete_charge_info',array('chargeId'=>$chargeId))){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败'));
        }
    }
}