<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('tianyu/price','modules\tianyu\controllers\PriceController');
Route::controller('tianyu/help','modules\tianyu\controllers\HelpController');
Route::controller('tianyu/game','modules\tianyu\controllers\GameController');
