<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class System
{
    public static function getAuthorizeNodeList($page=1,$pagesize=10)
	{
		return DB::table('authorize_node')->orderBy('appname','asc')->orderBy('module','desc')->orderBy('id','asc')->forPage($page,$pagesize)->get();		
	}
	
    public static function getAuthorizeNodeListByModule($module)
	{		
		return DB::table('authorize_node')->where('module','=',$module)->orderBy('id','asc')->get();
	}
	
	public static function getAuthorizeNodeCount()
	{
		return DB::table('authorize_node')->count();		
	} 
	
	public static function getAuthorizeNode($id)
	{
		return DB::table('authorize_node')->where('id','=',$id)->first();
	}
	
	public static function addAuthorizeNode($data)
	{
		return DB::table('authorize_node')->insertGetId($data);
	}
	
    public static function updateAuthorizeNode($id,$data)
	{
		return DB::table('authorize_node')->where('id','=',$id)->update($data);
	}
	
    public static function deleteAuthorizeNode($id)
	{
		return DB::table('authorize_node')->where('id','=',$id)->delete();
	}
	
	public static function getClientList()
	{
		$driver = Config::get('app.oauth2.driver');
		if($driver=='redis'){
			$prefix = 'oauth_clients:';
			$redis = Illuminate\Support\Facades\Redis::connection();
			$client_ids = $redis->keys($prefix . '*');
			$clients = $redis->mget($client_ids);
			$clients = array_map(function($val){return json_decode($val,true);},$clients);
			//print_r($clients);die;
			return $clients;
		}
		return DB::table('oauth2_clients')->get();
	}
	
	public static function saveClient($data)
	{
	    $driver = Config::get('app.oauth2.driver');
		if($driver=='redis'){
			$prefix = 'oauth_clients:';
			$redis = Illuminate\Support\Facades\Redis::connection();
			$client_id = $data['client_id'];			
			return $redis->set($prefix . $client_id,json_encode($data));
		}
		
		$exists = DB::table('oauth2_clients')->where('client_id','=',$data['client_id'])->count();
		if($exists){
			DB::table('oauth2_clients')->where('client_id','=',$data['client_id'])->update($data);
		}else{
		    return DB::table('oauth2_clients')->insert($data);
		}
	}
	
	public static function getClient($client_id)
	{
	    $driver = Config::get('app.oauth2.driver');
		if($driver=='redis'){
			$prefix = 'oauth_clients:';
			$redis = Illuminate\Support\Facades\Redis::connection();
			$client = $redis->get($prefix . $client_id);
			$client = json_decode($client,true);
			//print_r($clients);die;
			return $client;
		}
		return DB::table('oauth2_clients')->where('client_id','=',$client_id)->first();
	}
}