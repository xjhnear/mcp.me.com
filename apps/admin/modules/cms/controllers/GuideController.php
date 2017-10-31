<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;

use libraries\Helpers;
use Youxiduo\Cms\Model\Gonglue;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;


class GuideController extends BestController
{
    public function _initialize()
    {
        $this->current_module = 'cms';
    }

    /**
     * 列表页
     * @param string $type
     */
    public function getSearch($type='')
    {
        $data = array();
        $type = empty($type) ? Input::get('type') : $type ;

        $cond = $search = Input::only('type','zonetype');
        $page = Input::get('page',1);
        $pagesize = 10;
        $keytype = Input::get('keytype','');
        $keyword = empty($keytype) ? '' : Input::get('keyword','') ;
        $cond['keyword'] = $keyword;
        $cond['keytype'] = empty($keytype)?'gtitle':$keytype;
        $data['keyword'] = $keyword;
        $cond['keytypes'] = array('id' => 'ID' , 'gtitle' => '名称' , 'gname' => '游戏名称');
        $data['type'] = $type;
        if(empty($keytype)){
            $result = Gonglue::getList($page,$pagesize);
        }else{
            $result = Gonglue::getList($page,$pagesize,$keyword,$keytype);
        }
        if(empty($result)){
            return $this->back()->with('global_tips','参数出错，请联系技术。');
            exit;
        }
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $pager->appends($cond);
        $data['cond'] = $cond;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['datalist'] = $result['results'];
        return $this->display('guide-list',$data);
    }

    /**
     * 攻略添加界面显示
     */
    public function getAdd()
    {
        $data = array();
        $data['pid'] = 0;
        $data['editor'] = 1;
        $data['dosave'] = 'add';
        return $this->display('guide-add',$data);
    }

    public function anySave(){
        $input = Input::all();
        $aid = 0 ;
        //游戏进行关联
        $this->artCorrelate($input);
        //处理pid 判断是否为系列文章 并检测目录是否符合规则
        $this->checkDir($input);
        //print_r($input);exit;
        //查询游戏信息
        $data = array(
            'gtitle' => $input['gtitle'],
            'shorttitle' => $input['shorttitle'],
            'writer' => $input['writer'],
            'content' => empty($input['content']) ? '' : $this->make_content($input['content']),
            'linkgame' => $input['game_name'],
            'webkeywords' => $input['webkeywords'],
            'webdesc' => $input['webdesc'],
            'webcatedir' =>  empty($input['webcatedir']) ? '' : $input['webcatedir'],
            'pid' => $input['pid'],
            'gid' => $input['gid'],
            'agid' => $input['agid'],
            'editor' => $input['gtitle'],
            'sort' => $input['sort']
        );

        if($input['dosave']=='edit'){
            $data['id'] = $input['id'];
            $aid = $input['id'];
            $tips = '修改';
        }else{
            $tips = '添加';
        }

        //print_r($data);exit;
        $rs = Gonglue::save($data);
        //$tips .= '成功！';
        $aid = ($aid==0) ? $rs : $aid ;
        //执行同步
        $syncID = SyncarticleService::syncGonglue($aid);
        if($syncID){
            $this->checkShow($syncID,'checkArchives');
            $tips .= '成功！';
        }else{
            $tips .= "成功,但同步失败。请记住文章【ID:{$aid}】-【guide】并告诉技术解决";
        }
        return Redirect::to('cms/guide/search')->with('global_tips',$tips);
    }
    /**
     * 攻略编辑界面显示
     * @param int $id
     */
    public function getEdit($id)
    {
        $data = array();
        $data['gid'] = Input::get('gid',0);
        $data['agid'] = Input::get('agid',0);
        $data['result'] = Gonglue::getDetails($id);
        if($data['result']['pid']=='-1'){
            $data['result']['seriesid'][0] = '无';
        }
        if($data['result']['gid']>0){
            $data['result']['gametype'] = 'ios';
            //查询该游戏下面所有系列栏目
            $menu = Gonglue::getMenu($data['result']['gid']);
            //查询游戏name and pic
            $iosGame = GameModel::getInfo($data['result']['gid']);
            $data['result']['gamename'] = $iosGame['gname'];
            $data['result']['advpic'] = $iosGame['advpic'];
        }else{
            $data['result']['gametype'] = 'android';
            $menu = Gonglue::getMenu($data['result']['agid'],'agid');
            //查询游戏name and pic
            $androidGame = Game::m_getInfo($data['result']['agid']);
            $data['result']['gamename'] = $androidGame['gname'];
            $data['result']['advpic'] = $androidGame['advpic'];
        }
        foreach ($menu as $m){
            $data['result']['seriesid'][$m['id']] = $m['gtitle'];
        }
        if(empty($data['result']['seriesid'])){
            $data['result']['seriesid'][0] = '无';
        }
        return $this->display('guide-edit',$data);
    }


    /**
     * 删除
     * @param int $id
     */
    public function getDel($id)
    {
        $type = 'guide';
        $data = array();
        $yxdid = SyncarticleService::getYxdId($id, $type);
        $arc = Gonglue::getDetails($id);
        $result = Gonglue::delArticle($id);
        //同步删除
        if($result > 0){
            $website = Config::get('app.mobile_service_path');
            $url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
            $rs = Helpers::curlGet($url);
            SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
        }
        return $this->redirect("cms/guide/search");
    }
}