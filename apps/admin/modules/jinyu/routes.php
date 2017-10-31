<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('jinyu/price','modules\jinyu\controllers\PriceController');
Route::controller('jinyu/help','modules\jinyu\controllers\HelpController');
Route::controller('jinyu/game','modules\jinyu\controllers\GameController');
