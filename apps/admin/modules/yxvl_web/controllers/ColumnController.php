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

class ColumnController extends BackendController
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
        $totalPage = 1;
		$search = array('titleContain'=>$keyword,'size'=>$pageSize,'page'=>$pageIndex);
		$res = ESportsService::excute($search,"GetColumnList",true);
        if($res['data']&&isset($res['data']['list'])&&isset($res['data']['totalPage'])){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
		$data['pagelinks'] = MyHelpLx::pager(array(),$totalPage*$pageSize,$pageSize,$search);
		
		return $this->display('column-list',$data);
	}
	
	public function getAdd()
	{
		$data = array('catalogs'=>array());
        $data['catalogs'] = HelpController::getCategoryArr(array(),"Column");
        $id = Input::get('id',"");
        if($id){
            $res = ESportsService::excute(array('id'=>$id),"GetColumnDetail",true);
            if($res['data']){
                $data['data'] = $res['data'];
                $data['data']['publishTime'] = date('Y-m-d H:i:s',$data['data']['publishTime']);
                $data['data']['tags'] = implode(',',$data['data']['tags']);
            }
        }
		return $this->display('column-add',$data);
	}

    public function postAdd()
    {
        $id = Input::get("id");
        $input = Input::all();
        $input['tag'] = explode(',',Input::get('tag'));
        $img = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img ? $img:$input['img'];unset($input['img']);
        $img2 = MyHelpLx::save_img($input['smallIconUrl']);
        $input['smallIconUrl'] =$img2 ? $img2:$input['img2'];unset($input['img2']);
        $input['editor'] = $this->current_user['authorname'];
        $input['publishTime'] = strtotime($input['publishTime']);
        $input['columnId'] = Input::get("id");unset($input['id']);
        if($id){
            $res= ESportsService::excute2($input,"UpdateColumn",false);
        }else{
            $res= ESportsService::excute2($input,"CreateColumn",true);
            if($res['success']){
                $urls = array(
                    'http://dj.vlong.tv/column/'.$res['data'].'.html',
                );
                $api = 'http://data.zz.baidu.com/urls?site=dj.vlong.tv&token=bQ4PZLCdJmpp2asj&type=original';
                $result = MyHelpLx::baidu_weburl($urls,$api);
                if(isset($result['error'])){
                    $str = $result['error'].':'.$result['message'];
                    return $this->redirect('yxvl_eSports/column/index','添加成功，但是推送百度失败，请尝试手动添加！(错误码'.$str.')');
                    $dir = '/logs/baidu_webs_error_log.txt';
                    $path = storage_path() . $dir;
                    MyHelpLx::error_log($path,$str.";http://dj.vlong.tv/column/".$res['data'].".html\r\n");
                }else{
                    $dir = '/logs/baidu_webs_error_log.txt';
                    $path = storage_path() . $dir;
                    MyHelpLx::error_log($path,"推送成功：http://dj.vlong.tv/column/".$res['data'].".html\r\n");
                }
            }
        }
        if($res['success']){
            return $this->redirect('yxvl_eSports/column/index','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }


    public function postAjaxDel()
    {
        $data = Input::all();
        $res = ESportsService::excute($data,"RemoveColumn",false);
        echo json_encode($res);
    }


	

}