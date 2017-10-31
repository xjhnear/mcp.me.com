<?php
namespace Youxiduo\Activity\Share;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Service
{
	public static function db()
	{
		return DB::connection('share_activity');
	}	
}