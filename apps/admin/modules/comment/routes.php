<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('comment/comments','modules\comment\controllers\CommentsController');
Route::controller('comment/inform','modules\comment\controllers\InformController');