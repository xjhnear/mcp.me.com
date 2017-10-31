<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Game;
use modules\gamelive\models\Anchor;
use modules\gamelive\models\Video;


class ApiController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getPopAnchorSearch()
    {
        $page = Input::get('page',1);
        $size = 10;
        $search = array();
        $data = array();
        $result = Anchor::GetPeopleList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$size,$size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $html = $this->html('pop-anchor-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function getPopGameSearch()
    {
        $page = Input::get('page',1);
        $size = 10;
        $search = array();
        $data = array();
        $result = Game::GetGameList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$size,$size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $html = $this->html('pop-game-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function getPopVideoSearch()
    {
        $page = Input::get('page',1);
        $size = 10;
        $search = array();
        $data = array();
        $result = Video::GetVideoList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$size,$size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $html = $this->html('pop-video-list',$data);
        return $this->json(array('html'=>$html));
    }
}