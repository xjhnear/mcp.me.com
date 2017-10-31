<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('shop/goods','modules\shop\controllers\GoodsController');
Route::controller('shop/exchange','modules\shop\controllers\ExchangeController');
