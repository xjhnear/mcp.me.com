<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/11/12
 * Time: 上午10:32
 */

namespace modules\v4a_giftbag\controllers;

use Youxiduo\MyService\SuperService;
use Yxd\Modules\Core\BackendController;
use Input,Log;
class ReportController extends BackendController
{

    protected $service;

    /**
     * @param SuperService $SuperService
     */
    public function __construct(SuperService $SuperService)
    {
        $this->service=$SuperService;
        $this->current_module = 'v4a_giftbag';
        /**初始化**/
        $this->service->_setObj($this);
        parent::__construct();
    }

    //http://121.40.78.19:8080/module_mall/product/exportquery?isBelongUs=false&createTimeBegin=&createTimeEnd=&isGift=true&pageIndex=1
    public function getList()
    {
        $input=Input::all();
        $input['pageSize']=15;
        $input_=array();
        if(!empty($input['timeEnd'])){
            $input_['timeEnd']=date('Y-m-d H:i:s',strtotime($input['timeEnd']));
        }
        if(!empty($input['timeBegin'])){
            $input_['timeBegin']=date('Y-m-d H:i:s',strtotime($input['timeBegin']));
        }
        $input['isGift']='true';
        $data=$this->service->_ListByView($input);
        unset($input_['pageIndex'],$input_['page'],$input_['pageSize']);
        $input_['operationType']='gift_consume';
        if(!empty($input['isBelongUs'])){
            $input_['isBelongUs']=$input['isBelongUs'];
        }
        $input_['isGift']='true';
        $monolog= Log::getMonolog();
        $monolog->pushHandler(new \Monolog\Handler\FirePHPHandler());
        $monolog->addInfo('Log Message', array('items' => json_encode($input_)));
        $result=$this->service->_getData('opercount',$input_);
        $data['inputinfo']=$input;
        $data['opercount']=!empty($result['result'])?$result['result']:0;
        return $this->display('report/report-list',$data);
    }
}