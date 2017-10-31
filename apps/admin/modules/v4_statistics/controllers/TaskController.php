<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 16/3/1
 * Time: 下午6:13
 */

namespace modules\v4_statistics\controllers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;
use Config,Input;

class TaskController extends SuperController
{

    const API_MODULE_TASK_URL ='app.api_module_task_url';//http://121.40.78.19:8080/module_task/task/
    public function __construct()
    {
        $this->current_module = "v4_statistics";
        //$this->_config['lookLog']=true;

        parent::__construct($this);
    }

    //任务总数
    public function getTaskcount()
    {
        $this->url_array['list_url']=Config::get(self::API_MODULE_TASK_URL).'task/query_user_task_info_count';
        $data['type']='任务总数';
        $data['from']='Taskcount';
        if(Input::get('StartDate') != '' || Input::get('EndDate') != ''){
            $inputinfo['finishTimeBegin']=Input::get('StartDate');
            $inputinfo['finishTimeEnd']=Input::get('EndDate');
        }
        $inputinfo['taskStatus']=1;
        $inputinfo['isLine']='false';
        $data['result']=$this->getResult($inputinfo);
        if($data['result']['errorCode'] == 0){
            return $this->display('statistics-info',$data);
        }

    }
    //任务查看/任务完成数
    public function getTaskselect()
    {

    }




}