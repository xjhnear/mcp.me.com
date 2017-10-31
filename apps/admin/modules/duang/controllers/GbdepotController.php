<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/4/21
 * Time: 13:56
 */
namespace modules\duang\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use libraries\Helpers;
use Illuminate\Support\Facades\DB;
use modules\game\models\GameModel;
use Youxiduo\Activity\Model\Variation\ActDepRelate;
use Youxiduo\Activity\Model\Variation\GiftbagDepot;
use Youxiduo\Activity\Model\Variation\GiftbagList;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;

class GbdepotController extends BackendController{
    public function _initialize(){
        $this->current_module = 'duang';
    }

    public function getList(){
        $page = Input::get('page',1);
        $name = Input::get('name',false);
        $type = Input::get('type',array(1,2));
        $start_date = Input::get('startdate');
        $end_date = Input::get('enddate');
        $limit = 10;
        $result = array();
        $total = GiftbagDepot::getListCount($name,$type);
        $tmp_result = GiftbagDepot::getList($name,$page,$limit,$type);
        $depot_ids = $gids = $games = array();
        if($tmp_result){
            foreach($tmp_result as $row){
                $gids[] = $row['gid'];
                $depot_ids[] = $row['depot_id'];
                $row['icon'] = Utility::getImageUrl($row['icon']);
                $row['used'] = false;
                $result[$row['depot_id']] = $row;
            }
            $tmp_games = GameService::getMultiInfoById($gids,'android');
            if($tmp_games){
                foreach($tmp_games as $row){
                    $games[$row['gid']] = $row;
                }
            }
            $relates = ActDepRelate::getTargetList('','',$depot_ids);
            if($relates){
                foreach($relates as $item){
                    if(array_key_exists($item['depot_id'],$result)) $result[$item['depot_id']]['used'] = true;
                }
            }
        }
        $pager = Paginator::make(array(),$total,$limit);
        $pager->appends(array('name'=>$name));
        $search_dpids = GiftbagDepot::getSearchDepotids($name,$type);
        $search_dpids = $search_dpids ? $search_dpids : -1;
        $start_time = $start_date ? strtotime($start_date) : '';
        $end_time = $end_date ? strtotime($end_date) : '';
        $all_count = GiftbagList::getStatisticsCount('',$start_time,$end_time);
        $search_count = GiftbagList::getStatisticsCount($search_dpids,$start_time,$end_time);
        $vdata = array(
            'list'=>$result,
            'games'=>$games,
            'pagination'=>$pager->links(),
            'search'=>array('name'=>$name,'start_date'=>$start_date,'end_date'=>$end_date,'type'=>$type),
            'all_count' => $all_count,
            'search_count'=>$search_count
        );
        return $this->display('variation/depot-list',$vdata);
    }

    public function getAdd(){
        return $this->display('variation/depot-add');
    }

    public function postAdd(){
        $input = Input::all();
        $rule = array('gid'=>'required','name'=>'required','description'=>'required','type'=>'required');
        $prompt = array('gid.required'=>'请选择关联游戏','name.required'=>'请填写礼包名称','description.required'=>'请填写礼包描述',
                        'type.required'=>'请选择类型');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $game = AndroidGame::getInfoById($input['gid']);
        $data = array(
            'gid' => $input['gid'],
            'name' => $input['name'],
            'description' => $input['description'],
            'addtime' => time(),
            'type' => $input['type'],
            'icon' => $game ? $game['ico'] : ''
        );
        if(GiftbagDepot::add($data)){
            return $this->redirect('/duang/gbdepot/list','添加成功');
        }else{
            return $this->back()->with('global_tips','添加失败，请重试');
        }
    }

    public function getEdit($depot_id=''){
        if(!$depot_id) return $this->back('数据错误');
        $info = GiftbagDepot::getInfo($depot_id);
        if(!$info) return $this->back('无效礼包');
        $gameinfo = AndroidGame::getInfoById($info['gid']);
        if($gameinfo){
            $info['gid'] = $gameinfo['id'];
            $info['gpic'] = Utility::getImageUrl($gameinfo['ico']);
            $info['gname'] = $gameinfo['shortgname'];
        }
        return $this->display('variation/depot-edit',array('depot'=>$info));
    }

    public function postEdit(){
        $input = Input::all();
        $rule = array('gid'=>'required','depot_id'=>'required','name'=>'required','description'=>'required','type'=>'required');
        $prompt = array('gid.required'=>'请选择关联游戏','depot_id.required'=>'数据错误','name.required'=>'请填写礼包名称','description.required'=>'请填写礼包描述',
                        'type.required'=>'请选择类型');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $game = AndroidGame::getInfoById($input['gid']);
        $data = array(
            'gid' => $input['gid'],
            'name' => $input['name'],
            'description' => $input['description'],
            'type' => $input['type'],
            'icon' => $game ? $game['ico'] : ''
        );

        if(GiftbagDepot::update($input['depot_id'],$data)){
            return $this->redirect('/duang/gbdepot/list','修改成功');
        }else{
            return $this->back()->with('global_tips','修改失败，请重试');
        }
    }

    public function getExport(){
        $depot_id = Input::get('depot_id',false);
        $number = Input::get('number',0);
        if(!is_numeric($number) || $number <= 0) return $this->back('数据错误');
        $depot_info = GiftbagDepot::getInfo($depot_id);
        if(!$depot_info) return $this->back('数据错误');
        header("Content-Type: application/octet-stream");
        if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) {
            header('Content-Disposition:  attachment; filename="'.$depot_info['name'].'.txt"');
        } elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) {
            header('Content-Disposition: attachment; filename*="'.$depot_info['name'].'.txt"');
        } else {
            header('Content-Disposition: attachment; filename="'.$depot_info['name'].'.txt"');
        }
        $bag_list = GiftbagList::getUnusedCard($depot_id,$number,$depot_info['valid']);
        $list_ids = array();
        $list_str = "";
        if($bag_list){
            foreach($bag_list as $row){
                $list_ids[] = $row['list_id'];
                $list_str .= $row['cardno']."\r\n";
            }
        }
        if($list_ids && $list_str){
            self::getClear($depot_id,$list_ids);
        }
        echo $list_str;
    }

    public function getImport($depot_id = ''){
        if(!$depot_id) return $this->back('数据错误');
        $depot = GiftbagDepot::getInfo($depot_id);
		return $this->display('variation/gbdepot-import',array('depot'=>$depot));
    }

    public function postImport(){
		$depot_id = Input::get('depot_id');
		$type_repeat = (int)Input::get('type_repeat',0);
		$depot = GiftbagDepot::getInfo($depot_id);
		if(!$depot) return $this->back('礼包库不存在');
	    if(!Input::hasFile('filedata')) return $this->back('礼包卡文件不存在');
		$file = Input::file('filedata');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();
		if(!in_array($ext,array('txt'))) return $this->back()->with('global_tips','上传文件格式错误');
		$server_path = storage_path() . '/tmp/';
		$newfilename = microtime() . '.' . $ext;
		$target = $server_path . $newfilename;
		$file->move($server_path,$newfilename);
		$card = array();
		if($ext == 'txt'){
			$fp = fopen($target, 'r');
            while (!feof($fp)) {
                $row = trim(fgets($fp));
                if (strlen($row) < 1) {
                	continue;
                }
                $card[] = $row;
            }
		}

	    if(!$card) return $this->back('礼包卡无效');
		$new_card = array();
		$exists_card = array();

		if($type_repeat==1){
			$card = array_unique($card);
			$exists_result = GiftbagList::getInfo('',$depot_id);
            $exists_card = array();
            if($exists_card){
                foreach($exists_card as $row){
                    $exists_card[] = $row['cardno'];
                }
            }
			$new_card = array_diff($card,$exists_card);
		}else{
			$new_card = $card;
		}
		if(!$new_card) return $this->back('礼包卡无效');

		$data_cards = array();
		foreach($new_card as $card){
            $data_cards[] = array('depot_id'=>$depot_id,'cardno'=>$card,'addtime'=>time());
		}
		if(GiftbagList::importCardno($depot_id,$data_cards)){
			@unlink($target);
			return $this->redirect('duang/gbdepot/list','礼包卡导入成功');
		}else{
			return $this->back('礼包卡导入失败');
		}
	}

    public function getCardlist($depot_id=''){
        if(!$depot_id) return $this->back('数据错误');
        $uid = Input::get('uid','');
        $page = Input::get('page',1);
        $limit = 10;
        $total = GiftbagList::getListCount($depot_id,$uid);
        $list = GiftbagList::getList($depot_id,$page,$limit,$uid);
        if($list){
            $uids = $users = array();
            foreach($list as $row){
                if(!$row['user_id']) continue;
                $uids[] = $row['user_id'];
            }
            if($uids){
                $tmp_uinfos = UserService::getMultiUserInfoByUids($uids,'full');
                if(is_array($tmp_uinfos)){
                    $uinfos = array();
                    foreach($tmp_uinfos as $user){
                        $uinfos[$user['uid']] = $user;
                    }
                    foreach($list as &$item){
                        $item['uinfo'] = array_key_exists($item['user_id'],$uinfos) ? $uinfos[$item['user_id']] : false;
                    }
                }
            }
        }
        $pager = Paginator::make(array(),$total,$limit);
        $pager->appends(array('uid'=>$uid));
        return $this->display('variation/gbdepot-cardlist',array('list'=>$list,'pagination'=>$pager->links(),'search'=>array('depot_id'=>$depot_id,'uid'=>$uid)));
    }

    private function getClear($depot_id,$list_ids){
        if(!$depot_id || !$list_ids) return false;
        if(GiftbagList::deleteUnusedCard($depot_id,$list_ids)){
            return true;
        }else{
            return false;
        }
    }

    public function getDelete(){
        $depot_id = Input::get('depot_id',false);
        if(!$depot_id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(GiftbagList::delete($depot_id)){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    public function getUpvalid(){
        $depot_id = Input::get('depot_id',false);
        $state = Input::get('state',false);
        if(!$depot_id || $state === false) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(GiftbagDepot::updateSelf($depot_id,array('valid'=>$state))){
            return $this->json(array('state'=>1,'msg'=>'更新成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'更新失败，请重试'));
        }
    }
}