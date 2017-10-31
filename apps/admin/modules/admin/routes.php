<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;


//操作记录
Route::controller('admin/operate','modules\admin\controllers\OperateController');
Route::controller('admin/module','admin\controllers\ModuleController');
Route::controller('admin/group','admin\controllers\GroupController');
Route::controller('admin/admin','admin\controllers\AdminController');
