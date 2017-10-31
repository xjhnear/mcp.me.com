<?php
namespace modules\weba_forum\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\User\UserService;
use Youxiduo\Bbs\TopicService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Bbs\Model\BbsAppend;

class ReplylimitController extends BackendController
{
   
    public function _initialize()
    {
        $this->current_module = 'weba_forum';
        
    }


    public function getList(){
        $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['targetId']=Input::get('targetId');
        $result=TopicService::replylimitList($params);//print_r($result);
        if($result['errorCode'] != null){
            $data=self::processingInterface($result,$params);
            return $this->display('/4web/replylimit-list',$data);
        }
        self::error_html($result);
	}

	public function getDel($id=0){
		$data = $params = array();
		if(!empty($id)){
			$params['limitId']=$id;
			$result=TopicService::del_replylimit($params);
			 if($result['errorCode'] != null){
           		return $this->back()->with(array('global_tips'=>'删除成功','suc'=>1));
       		 }
		}
	}


    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){ //echo $result['totalCount'];exit;
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['pageIndex']);
        $pager->appends($data);
		$data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }


    /**错误输出 **/
    private static function errorHtml($result=array()){
        header("Content-type: text/html; charset=utf-8");
        echo '出错啦->:'.json_encode($result);
        exit;
    }
}