<?php
use Illuminate\Support\Facades\Route;
Route::controller('v4product/goods','modules\v4_product\controllers\GoodsController');
Route::controller('v4product/label','modules\v4_product\controllers\LabelController');
Route::controller('v4product/welfare','modules\v4_product\controllers\WelfareController');
Route::controller('v4product/order','modules\v4_product\controllers\OrderController');
Route::controller('v4product/order','modules\v4_product\controllers\OrderController');
Route::controller('v4product/form','modules\v4_product\controllers\FormController');
Route::controller('v4product/rmb','modules\v4_product\controllers\RmbController');
Route::controller('v4product/wechat','modules\v4_product\controllers\WechatController');
