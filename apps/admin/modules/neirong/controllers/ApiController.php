<?php
namespace modules\neirong\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\Base\AllService;
use Youxiduo\Android\Model\Video;

class ApiController extends BackendController
{
    public static $prizeArr = array(''=>'选择类型','1'=>'活动','2'=>'礼包','3'=>'帖子','4'=>'任务','5'=>'视频','6'=>'评论','7'=>'文章','8'=>'商城','10'=>'内链');
    public function _initialize()
    {
        $this->current_module = 'neirong';
    }

    public function getArticleSearch()
    {
        $data = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 5;
        $title = Input::get('keyword','');
        $data['keyword'] = $title;
        $search = array(
            'pageSize'=>$pageSize,
            'pageIndex'=>$pageIndex,

        );
        $res = AllService::excute('android',$search,'android/articlelist');
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $data['list'] = $res['result'];
        }else{
            $total = 0;
            $data['list']= array();
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
//        print_r($data['list']);
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;
        $html = $this->html('pop-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function getVideoSearch()
    {
        $data = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 5;
        $title = Input::get('keyword','');
        $data['keyword'] = $title;
        $search = array(
            'pageSize'=>$pageSize,
            'pageIndex'=>$pageIndex,

        );
        $total = Video::getCount(0,$title);
        $res = Video::getList($pageIndex,$pageSize,0,NULL,$title);
        foreach($res as &$v){
            if(isset($v['litpic'])&&$v['litpic']){
                $v['litpic'] = Config::get('app.image_url') .$v['litpic'];
            }
        }
        if($res){
            $data['list'] = $res;
        }else{
            $data['list']= array();
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
//        print_r($data['list']);
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;
        $html = $this->html('pop-video-list',$data);
        return $this->json(array('html'=>$html));
    }





}