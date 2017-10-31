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
use Youxiduo\Cms\Model\Archives;
use Youxiduo\Game\Model\GamesApk;
use Youxiduo\Game\Model\Plat360SyncLog;
use Youxiduo\Game\Plat\Game360PlatService;
use Yxd\Modules\Core\BackendController;

class SyncController extends BackendController
{

    public function _initialize()
    {
        $this->current_module = 'plat360';
    }

    //同步记录列表
    public function getList(){

        $page = Input::get('page',1);
        $pagesize = 20;
        $result = Plat360SyncLog::getLists($page,$pagesize);
        foreach ($result['result'] as &$v) {
            if($v['plat'] == 1 && $v['wid'] != 0){
                $gamesInfo = GamesApk::getGameInfo($v['wid']);
                $v['gname'] = $gamesInfo['gname'];
            }elseif($v['plat'] == 2 && $v['mid'] != 0){
                $gamesInfo = Archives::getLists($v['mid']);
                $v['gname'] = $gamesInfo['title'];
            }else{
                $v['gname'] = '';
            }
        }
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['result'] = $result['result'];

        return $this->display('sync-list',$data);

    }

    //回滚
    public function getBack($id,$destroy){
        $tips = $destroy ? '回滚' : '同步';
        if(!$id) return $this->back('参数不对，'.$tips.'失败');
        $result = Plat360SyncLog::getDetail($id);
        $re = false;
        if($result){
            $url = $destroy ? $result['oldurl'] : $result['newurl'];
            $destroy = $destroy ? 0 : 1;
            $data['destroy'] = $destroy;
            $data['logid'] = $id;
            switch($result['plat']){
                case 2:
                    $data['aid'] = $result['mid'];
                    $data['apkurl'] = $url;
                   // print_r($data);exit;
                    $re =  Game360PlatService::upMobileGames($data,false);
                    break;
                case 1:
                    $data['id'] = $result['wid'];
                    $data['downurl'] = $url;
                    $re = Game360PlatService::upWwwGames($data,false);
                    break;
                default:
                    $re = false;
            }
        }
        if($re){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->back($tips);
    }
}