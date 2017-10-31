<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\ESports\ESportsService;


class SportsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'yxvl_eSports';
	}

    public function getIndex()
    {
        $pageIndex = (int)Input::get('page',1);
        $keyword = Input::get('keyword');
        $pageSize = 10;
        $search = array('titleContain'=>$keyword,'size'=>$pageSize,'page'=>$pageIndex);
        $res = ESportsService::excute($search,"GetSaiShiList",true);
        if($res['data']){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$totalPage*$pageSize,$pageSize,$search);

        return $this->display('sports-list',$data);
    }

    public function getAdd()
    {
        $data = array('catalogs'=>array());
        $id = Input::get('id',"");
        if($id){
            $res = ESportsService::excute(array('id'=>$id),"GetSaiShiDetail",true);
            if($res['data']){
                $data['data'] = $res['data'];
                $data['data']['publishTime'] = date('Y-m-d H:i:s',$data['data']['publishTime']);
            }
        }
        return $this->display('sports-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        $img = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img ? $img:$input['img'];unset($input['img']);
        $input['editor'] = $this->current_user['authorname'];
        $input['publishTime'] = strtotime($input['publishTime']);
        if($id){
            $res= ESportsService::excute2($input,"UpdateSaiShi",false);
        }else{
            unset($input['id']);
            $res= ESportsService::excute2($input,"CreateSaiShi",false);
        }
        if($res['success']){
            return $this->redirect('yxvl_eSports/sports/index','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }


    public function postAjaxDel()
    {
        $data = Input::all();
        $res = ESportsService::excute($data,"RemoveSaiShi",false);
        echo json_encode($res);
    }
}