<?php
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;

class ProfileModel extends BaseModel
{
	public static function syncFileToDb()
	{
		chdir(storage_path() . '/logs/');
		/*
		$files = glob('profile-apache2handler-*.txt');
		foreach($files as $file){
			self::fileToDb($file);
		}
		*/
	    $files = glob('sql-apache2handler-*.txt');
		foreach($files as $file){
			self::sqlToDb($file);					
		}				
	}
	
	public static function syncOneFileToDb()
	{
		 $file = 'profile-apache2handler-' . date('Y-m-d' , strtotime('-1 day')) . '.txt';
		 self::fileToDb($file);
	}
	
	protected static function sqlToDb($filename)
	{		
	    $file = storage_path() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $filename;
		if(!file_exists($file)) return ;
		ini_set('max_execution_time',0);
		ini_set('memory_limit','512M');
		$table = file($file,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
		if($table === false) return;
		$total = count($table);
	    $data = array();
	    $sqldata = array();
		foreach($table as $key=>$row){			
			if(strpos($row,'sql:')===0){
				$line = substr($row,4);
				list($exectime,$sqlcmd) = explode(" ",$line,2);
				$sqldata[] = array('exec_time'=>$exectime,'sqlcmd'=>$sqlcmd);
			}elseif(strpos($row,'2014')===0){
				list($date,$time,$api,$exectime,$url) = explode(" ",$row,5);
				$kudata = array();
				foreach($sqldata as $one){
					$onedata = array();
					$onedata['api_name'] = $api;
					$onedata['exec_time'] = rtrim($exectime,'ms');
					$onedata['request_time'] = strtotime($date.' '.$time);
					$onedata['url'] = $url;
					$onedata['debug'] = '';
					$onedata['sql_time'] = $one['exec_time'];
					$onedata['sqlcmd'] = $one['sqlcmd'];
					$kudata[] = $onedata;
				}
				if($kudata){
					DB::connection('profile')->table('sqlprofile_logs')->insert($kudata);
				}
				$sqldata = array();
			}
			
		}
	}
	
	protected static function fileToDb($filename)
	{
		$file = storage_path() . '/logs/' . $filename;
		if(!file_exists($file)) return ;
		$table = file($file,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
		if($table === false) return;
		$total = count($table);
	    $data = array();
		foreach($table as $key=>$row){
			$table[$key] = explode(' ',$row);
		}
		rsort($table);
		$datalist = array();
		foreach($table as $key=>$row){
			$item = array();
			$item['api_name'] = $row[2];
			$item['exec_time'] = rtrim($row[3],'ms');
			$item['request_time'] = strtotime($row[0] . ' ' . $row[1]);
			$item['url'] = $row[4];
			$query = array();
			parse_str(parse_url($row[4],PHP_URL_QUERY),$query);
			$item['debug'] = isset($query['debug']) ? $query['debug'] : '';
			$datalist[] = $item;
		}
		DB::connection('profile')->table('profile_logs')->insert($datalist); 
	}
}