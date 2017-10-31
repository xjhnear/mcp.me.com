<?php
namespace modules\v4system\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;

use modules\v4system\models\Report;

class ReportController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4system';
    }

    public function getList()
    {
        $data = array();
        $pageIndex = (int)Input::get('page',1);
        $dealType = Input::get('dealType',0);
        $informerName = Input::get('informerName');
        $infomantName = Input::get('infomantName');
        $search = array(
            'dealType'=>$dealType,
            'informerName'=>$informerName,
            'infomantName'=>$infomantName
        );
        $pageSize = 10;
        $result = Report::search($dealType,$informerName,$infomantName,$pageIndex,$pageSize);
        $data['datalist'] = $result['result'];
        $totalCount = $result['totalCount'];
        $pager = Paginator::make(array(),$totalCount,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        //print_r($data['datalist']);exit;
        return $this->display('report-list',$data);
    }

    public function getInform($dealType,$id)
    {
        $result = Report::doInform($dealType,$id);
        return $this->back('');
    }

}