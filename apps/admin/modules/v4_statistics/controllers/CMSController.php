<?php
namespace modules\v4_statistics\controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\MyService\StatisticsService;
use Yxd\Services\Models\AppAdvActiveStat;



class CMSController extends BackendController
{

    public function _initialize()
    {
        $this->current_module = 'v4_statistics';
    }

    function getList(){
        $data = $search = array();
        $search['startTime']    = Input::get('startTime');
        $search['endTime']      = Input::get('endTime');
        if(empty($search['startTime'])&&empty($search['endTime'])){
            $search['startTime'] = date('Y-m-d 00:00:00',time());
            $search['endTime'] = date('Y-m-d 24:00:00',time());
        }
        $search['aid']          = Input::get('aid');
        $search['type']         = Input::get('type');

        $pageSize               = 10;
        $page                   = Input::get('page');

        $db = StatisticsService::statisticsCMSList($search);
        $paginate = $db->paginate($pageSize, $search, $pageName = 'page', $page);
//        var_dump(AppAdvActiveStat::getQueryLog());
        $data['list']       = $paginate;
        $data['pageLinks']  = $paginate->appends($search)->links();
        $data['search']     = $search;
        return $this->display('statistics-cms-list',$data);
    }

    /**
     * excel导出
     */
    public function getDataDownload($id,$type)
    {
        $data = array();
        $typeList = array(1=>'游戏多3.0',2=>'游戏多4.0',3=>'攻略APP',4=>'游戏多4.0业内版',5=>'狮吼');
        $result = StatisticsService::statisticsCMSListbyAID(array('aid'=>$id,'type'=>$type));
 
        //var_dump($result);die;
        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->setTitle('广告激活统计');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $excel->getActiveSheet()->setCellValue('A1','#');
        $excel->getActiveSheet()->setCellValue('B1','广告AID');
        $excel->getActiveSheet()->setCellValue('C1','广告平台');
        $excel->getActiveSheet()->setCellValue('D1','IDFA');
        $excel->getActiveSheet()->setCellValue('E1','激活时间');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($result as $index=>$row){
            $aid = isset($row['aid'])?$row['aid']:'';
            $type = isset($typeList[$row['type']])?$typeList[$row['type']]:'';
            $idfa = isset($row['idfa'])?$row['idfa']:'';
            $addtime = isset($row['addtime'])?date('Y-m-d H:i:s',$row['addtime']):'';
    
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $index+1);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $aid);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $type);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $idfa);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $addtime);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'广告激活统计.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
        $writer->save('php://output');
    }

}