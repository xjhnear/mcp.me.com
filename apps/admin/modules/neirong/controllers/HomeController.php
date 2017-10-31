<?php
namespace modules\neirong\controllers;

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


class HomeController extends BackendController
{
    public static $prizeArr = array(''=>'选择类型','1'=>'活动','2'=>'礼包','3'=>'帖子','4'=>'任务','5'=>'视频','6'=>'评论','7'=>'文章','8'=>'商城','10'=>'内链','12'=>'外链');
    public static $hejiArr = array(''=>'选择合集类型','1'=>'活动','2'=>'礼包','3'=>'帖子','4'=>'任务','5'=>'视频','6'=>'评论','7'=>'文章','8'=>'商城','10'=>'内链','12'=>'外链');
    public function _initialize()
    {
        $this->current_module = 'neirong';
    }

    function getIndex(){
        $data = $search = $input = array();
        $search['page'] = (int)Input::get('page',1);
        $search['size'] = 10;
        $res = AllService::excute("NR",$search,"rss/list");
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = "";
        if($res['success']){
            $data['list'] = $res['data']['list'];
            $total = $res['data']['totalCount'];
            $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['size'],$search);
        }

        $data['search'] = $search;
        $data['prize'] = self::$prizeArr;
        return $this->display('list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('id',"");
        if($id){
            $res = AllService::excute("NR",array('id'=>$id),"rss/getinfo");
            if($res['data']){
                $data['data'] = $res['data'];
                if(isset($res['data']['imgurl'])){
                    $data['imgs'] = json_decode($res['data']['imgurl']);
                }
                if(isset($res['data']['object'])){
                    $data['other'] = json_encode($res['data']['object']);
                }
            }
        }
        $data['type'] = self::$prizeArr;
        return $this->display('add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        $other = array('link'=>Input::get('link'));
        if($other['link']&&Input::get('type')=='10'){
            $other['linkType'] = '13';
        }

        $input_other = json_decode(Input::get('other'),true);
        if($input_other){
            $other = array_merge($other,$input_other);
        }

        $input['other'] = json_encode($other);
        $input['autoShow'] = Input::get('autoShow')?"1":"0";
//        $img = MyHelpLx::save_img($input['pic']);unset($input['pic']);
        $img_arr = array();
        if(isset($input['picFile'])){
            foreach($input['picFile'] as $k=>$v){
                if($v){
                    $img_arr[] =  MyHelpLx::save_img($v);
                }else{
                    $img_arr[] = $input['img'][$k];
                }
            }
        }

        $img = json_encode(array_filter($img_arr));
        $input['contentImg'] =$img ? $img:$input['img'];unset($input['img']);
//        $input['editor'] = $this->current_user['authorname'];
        if($id){
            $res= AllService::excute("NR",$input,"rss/update",false);
        }else{
            unset($input['id']);
            $res= AllService::excute("NR",$input,"rss/add",false);
        }
        if($res['success']){
            return $this->redirect('neirong/home/index','保存成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function postAjaxDo()
    {
        $data = Input::get();
        $url = $data['url'];unset($data['url']);
        $res = AllService::excute("NR",$data,$url,false);
        echo json_encode($res);
    }

}