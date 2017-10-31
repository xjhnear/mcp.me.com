<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SystemMessageModel
{
	
    public static function save($notice)
	{
		return DB::table('system_message')->insertGetId($notice);
	}
}