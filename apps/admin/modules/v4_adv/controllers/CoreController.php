<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/15
 * Time: 11:25
 */
namespace modules\v4_adv\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use modules\v4_adv\models\Core;

class CoreController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4_adv';
    }
    
    public function getDelcache()
    {
        $data['type'] = 1;
        $result = Core::delcache($data);
        if ($result) {
            return $this->back('缓存清除成功');
        } else {
            return $this->back('缓存清除失败');
        }
    
    }
    
}