<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('product/goods','modules\product\controllers\GoodsController');
//Route::controller('product/exchange','modules\product\controllers\ExchangeController');
