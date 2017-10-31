<?php
 namespace modules\a_yiyuan\controllers;

 use Illuminate\Support\Facades\Input;
 use Illuminate\Support\Facades\Validator;
 use Illuminate\Support\Facades\Paginator;
 use Illuminate\Support\Facades\Log;
 use Youxiduo\Helper\Utility;
 use Youxiduo\Helper\MyHelpLx;
 use Youxiduo\Base\AllService;

 class text extends HelpController
 {
     public function _initialize()
     {
         $this->current_module='a_yiyuan';
     }
     public function  getList()
     {
        $date = $search = array();
        $search['pagesize'] = 10;
        $total = 0;
        $search['title'] = Input::get('title');
        $search['status'] = Input::get('status');
        $pageIndex = (int) Input::get('page',1);
        $search['offerset'] = ($pageIndex-1)*$search['pagesize'];
        $search['channelId']  = self::$channelId;
        $res = AllService::excute2('8089',$search,"luckyDraw/QueryMerchandise");
     }
 }