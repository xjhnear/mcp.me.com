<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 16/2/26
 * Time: 上午10:57
 */

namespace modules\v4_statistics\controllers;
//use Youxiduo\MyService\SuperController;
use Youxiduo\MyService\StatisticsService;
use Yxd\Modules\Core\BackendController;
use Log,Input;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Adv\Model\Statisticss;
use Youxiduo\Adv\Model\Statisticssv3;
class AccountController extends BackendController{
    public function _initialize(){

        $this->current_module = 'v4_statistics';
        self::boot();
    }

    //APP 日新增
    public function getStatistics()
    {
        $data['type']='App日新增';
        $data['from']='statistics';
        if(Input::get('StartDate') == '' || Input::get('EndDate') == ''){
            $Start=strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
            $End=strtotime(date('Y-m-d 24:00:00',strtotime('-1 day')));
            //$data['dateType']=$dateType;
        }else{
            $Start=strtotime(Input::get('StartDate'));
            $data['Start']=Input::get('StartDate');
            $data['End']=Input::get('EndDate');
            $End=strtotime(Input::get('EndDate'));
        }
        StatisticsService::$databas_table='yxd_club_beta.yxd_account';
        $data['result']=StatisticsService::Statistics($data['type'],'dateline',$Start,$End);
        return $this->display('statistics-info',$data);
    }

    //日活跃
    public function getDay()
    {
        $data['type']='App日活跃';
        $data['from']='day';
        if(Input::get('StartDate') == '' || Input::get('EndDate') == ''){
            $Start=date('Y-m-d 00:00:00',strtotime('-1 day'));
            $End=date('Y-m-d 24:00:00',strtotime('-1 day'));
        }else{
            $Start=strtotime(Input::get('StartDate'));
            $data['Start']=Input::get('StartDate');
            $data['End']=Input::get('EndDate');
            $End=strtotime(Input::get('EndDate'));
        }
        StatisticsService::$databas_table='yxd_club_beta.yxd_account_startup_info';
        $data['result']=StatisticsService::Statistics($data['type'],'create_time',$Start,$End);
        return $this->display('statistics-info',$data);
    }

    //游戏前10
    public function getGametop()
    {
        $data['type']='我的游戏排行前十';
        StatisticsService::$databas_table='yxd_club_beta.yxd_account_circle';
        $data['result']=StatisticsService::Statistics($data['type']);//print_r($data['result']);exit;
        $game_ids=array();
        foreach($data['result'] as $val){
            $game_ids[$val['game_id']]=$val['game_id'];
        }
        $gameinfos=GameService::getMultiInfoById($game_ids,'ios');
        foreach($gameinfos as $key=>&$val)
        {
            $game_ids[$val['gid']]=$val['shortgname'];
        }
        $data['game_info']=$game_ids;
        return $this->display('statistics-orderby-game',$data);

    }

    //用户性别统计
    public function getSex()
    {
        $data['type']='用户性别统计';
        StatisticsService::$databas_table='yxd_club_beta.yxd_account';
        $data['result']=StatisticsService::Statistics($data['type']);
        return $this->display('statistics-sex',$data);
    }

    //用户年龄统计
    public function getAge()
    {
        $data['type']='用户年龄统计';
        StatisticsService::$databas_table='yxd_club_beta.yxd_account';
        $data['result']=StatisticsService::Statistics($data['type']);
        return $this->display('statistics-age',$data);
    }

    //用户广告统计
    public function getAdv()
    {
        $data = array();
        $tb = Statisticss::db();
        $pageSize = 100;
        $tb = $tb->where('appname','=','yxdjqb');
        $tb = $tb->where('addtime','>=',strtotime(date('Y-m-d 00:00:00',time())));
        $tb = $tb->where('addtime','<',strtotime(date('Y-m-d 24:00:00',time())));
        $tb = $tb->forPage(1,$pageSize);
        $result = $tb->orderBy('addtime', 'desc')->get();
        $data['datalist'] = $result;
        $data['aid'] = '';
        $data['pageSize'] = 100;
        $data['is_v4'] = 'on';
        $data['is_yxd'] = 'on';
        $data['startTime'] = date('Y-m-d 00:00:00',time());
        $data['endTime'] = date('Y-m-d 24:00:00',time());
        return $this->display('statistics-adv',$data);
    }

    public function postAdv()
    {
        $aid = Input::get('aid');
        $start = Input::get('startdate');
        $end = Input::get('enddate');
        $pageSize = (int)Input::get('pageSize',100);
        $export = (int)Input::get('export',0);
        $is_v4 = Input::get('is_v4','off');
//         $is_yxd = Input::get('is_yxd','off');
        $appname = Input::get('appname','yxdjqb');
        if($pageSize<1) $pageSize = 100;
//        if(!$start) {
//            //$start = date('Y-m-d 00:00:00');
//        }else{
//            $start = $start . ' 00:00:00';
//        }
//        if(!$end) {
//            //$end = date('Y-m-d 23:59:59');
//        }else{
//            $end = $end . ' 23:59:59';
//        }
        $data['aid'] = $aid;
        $data['pageSize'] = $pageSize;
        $data['is_v4'] = $is_v4;
        $data['appname'] = $appname;
        if(!empty($start)){
            $data['startTime'] = $start;
        }
        if(!empty($end)){
            $data['endTime'] = $end;
        }
        if ($is_v4=='on') {
            $tb = Statisticss::db();
            $title = "V4广告点击报表";
        } else {
            $tb = Statisticssv3::db();
            $title = "V3广告点击报表";
        }
        if ($aid) {
            $tb = $tb->where('aid','=',$aid);
        }
        if ($appname) {
            $tb = $tb->where('appname','=',$appname);
        }
        if($start){
            $tb = $tb->where('addtime','>=',strtotime($start));
        }
        if($end){
            $tb = $tb->where('addtime','<',strtotime($end));
        }
        if($export!=0){
            $tb = $tb->forPage(1,$pageSize);
        }
        $result = $tb->orderBy('addtime', 'desc')->get();
        if($result){
            $out = array();
            foreach($result as $row){
                if (!isset($row['adv_logo'])) {
                    $row['adv_logo'] = $row['location'];
                }
                $out[] = array('appname'=>$row['appname'],'version'=>$row['version'],'adv_logo'=>$row['adv_logo'],'idfa'=>$row['idfa'],'openudid'=>$row['openudid'],'number'=>$row['number'],'addtime'=>date('Y-m-d',$row['addtime']));
            }
            if($export==0){
                require_once base_path() . '/libraries/PHPExcel.php';
                $excel = new \PHPExcel();
                $excel->setActiveSheetIndex(0);
                $excel->getActiveSheet()->setTitle($title);
                $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
                $excel->getActiveSheet()->setCellValue('A1','应用名');
                $excel->getActiveSheet()->setCellValue('B1','版本号');
                $excel->getActiveSheet()->setCellValue('C1','广告模块');
                $excel->getActiveSheet()->setCellValue('D1','idfa');
                $excel->getActiveSheet()->setCellValue('E1','点击量');
                $excel->getActiveSheet()->setCellValue('F1','时间');
                foreach($out as $index=>$row){
                    $excel->getActiveSheet()->setCellValue('A'.($index+2),$row['appname']);
                    $excel->getActiveSheet()->setCellValue('B'.($index+2),$row['version']);
                    $excel->getActiveSheet()->setCellValue('C'.($index+2),$row['adv_logo']);
                    $excel->getActiveSheet()->setCellValue('D'.($index+2),$row['idfa']);
                    $excel->getActiveSheet()->setCellValue('E'.($index+2),$row['number']);
                    $excel->getActiveSheet()->setCellValue('F'.($index+2),$row['addtime']);
                }
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');

                $writer->save('php://output');
                return $this->back('数据导出成功');
            }else{
                $data['datalist'] = $out;
                return $this->display('statistics-adv',$data);
            }

        }else{
            return $this->back('数据不存在');
        }

    }

    //用户登录统计
    public function getLogin()
    {
        $data['type']='登录统计';
        StatisticsService::$databas_table='yxd_club_beta.yxd_account_thirdlogin';
        $data['result']=StatisticsService::Statistics($data['type']);
        $data['three']=$data['result']['3']['0']['count'];
        $data['my']=$data['result']['3']['0']['count']-$data['result']['1']['0']['count'];
        return $this->display('statistics-login',$data);
    }

    //搜索关键字前十
    public function getSearch()
    {
        $data['type']='搜索关键字前十';
    }

    //活动详情查看前十
    public function getActivity()
    {
        $data['type']='活动详情查看前十';
    }

    //视频详情查看前十
    public function getVideo()
    {
        $data['type']='视频详情查看前十';
    }


    private function boot()
    {

        \DB::listen(function($sql, $bindings, $time)
        {
            $monolog=Log::getMonolog();
            $monolog->pushHandler(new \Monolog\Handler\FirePHPHandler());
            header("Content-type: text/html; charset=utf-8");
            $monolog->addInfo('Log Message', array('sql' =>$sql,'bindings'=>json_encode($bindings),'time'=>$time,'date'=>date('Y-m-d H:i:s',time())));
        });
    }


}