<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/7
 * Time: 15:11
 */
namespace modules\plat360\controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Game\Model\Plat360Sync;
use Youxiduo\Game\Plat\Game360PlatService;
use Yxd\Modules\Core\BackendController;

class MappingController extends BackendController
{

    public function _initialize()
    {
        $this->current_module = 'plat360';
    }

    //映射表列表
    public function getList(){
        $page = Input::get('page',1);
        $type = Input::get('type','id');
        $keyword = Input::get('keyword','');
        $pagesize = 20;
        $where = $keyword == '' ? array() : array($type=>$keyword);
        $result = Plat360Sync::getLists($page,$pagesize,array(),$where);
        $cond['type'] = $type;
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $pager->appends($cond);
        $cond['keytypes'] = array('id'=>'ID' , 'gname' => '游戏名称' , 'wid' => 'Android ID' , 'mid' => 'Web站ID' , 'qid' => '360平台ID');
        $data['cond'] = $cond;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['result'] = $result['result'];

        return $this->display('mapping-list',$data);

    }

    //添加 | 编辑
    public function getEdit($id = ''){
        if($id){
            $result = Plat360Sync::getDetail(array('id'=>$id));
            $data['result'] = $result;
        }else{
            $data = array();
        }
        return $this->display('mapping-edit',$data);
    }
    //保存数据
    public function postSave(){
        $input = Input::only('id','wid','mid','qid','gname','isSync');
        $tips = !empty($input['id']) ? '修改' : '添加';
        $rule = array(
            'gname'=>'required',
            'wid'=>'required|numeric',
            'mid'=>'required|numeric',
            'qid'=>'required|numeric'
        );
        $input['isSync'] = ($input['isSync']=='on')? 1 : 0 ;
        $prompt = array(
            'gname.required'=>'游戏名不能为空',
            'wid.required'=>'Android ID不能为空',
            'qid.required'=>'360平台游戏ID不能为空',
            'mid.numeric'=>'必须为数字',
            'qid.numeric'=>'必须为数字',
            'wid.numeric'=>'必须为数字',
        );
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return Response::json(array('state'=>300,'msg'=>$valid->messages()->first()));
        }

        if($input['id']){
            //检测wid
            $chick_wid = Plat360Sync::getState(array('wid'=>$input['wid']));
            if($chick_wid && $input['id'] != $chick_wid['id']) return $this->back($tips.'Android ID与'.$chick_wid['id'].'冲突，请检查');
            //检测qid
            $chick_qid = Plat360Sync::getState(array('qid'=>$input['qid']));
            if($chick_qid && $input['id'] != $chick_qid['id']) return $this->back($tips.'360平台游戏ID与'.$chick_qid['id'].'冲突，请检查');
            //检测mid
        }else{
            $where['wid'] = $input['wid'];
            $where['qid'] = $input['qid'];
            if($input['mid']) $where['mid'] = $input['mid'];
            $chick = Plat360Sync::getState($where,false);
            if($chick && $input['id'] != $chick['id']){
                return $this->back($tips.'与'.$chick['id'].'冲突，请检查');
            }
        }

        $result = Plat360Sync::save($input);
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
//        print_r($result);exit;
        return $this->redirect('plat360/mapping/list',$tips);
    }

    /******************************************以下方法只有第一次上线执行，其他时间不要执行*************************************************/

    //导入数据
    public function getGame(){
        $result = Game360PlatService::minSyncGame();
    }

    //执行匹配www
    public function getAssociateGame(){
        $result = Game360PlatService::associateGame();
        return $this->back('执行完成');
    }
    //执行匹配mobile 注意：需要执行www匹配完成才能执行此方法
    public function getAssociateGame2(){
        $result = Game360PlatService::associateGame2();
        return $this->back('执行完成');
    }


    //执行同步
    public function getStartSync(){
        $result = Game360PlatService::startSync();
        return $this->back('执行完成');
    }

    //回滚 一天之内所有同步
    public function getGoBack(){
        $endtime = time();
        $starttime = $endtime - 24*3600;
        $result = Game360PlatService::goBack('wid',$starttime,$endtime);
        $result_m = Game360PlatService::goBack('mid',$starttime,$endtime);
        return $this->back('执行完成');
    }





}