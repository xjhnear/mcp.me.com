<?php
namespace modules\game\controllers;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Yxd\Modules\Core\BackendController;


class DataController extends BackendController
{
	
	public function _initialize()
	{
		$this->current_module = 'game';
	}
	
	public function getSearch()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = Input::only('zone','pricetype','type','gid','gname','tag','startdate','enddate');
		$data = array();
		$data['zone'] = array(''=>'全部','xzone'=>'精品','zone'=>'普通');
		$data['pricetype'] = array(''=>'全部','1'=>'免费','2'=>'限免','3'=>'收费');
		$data['gametypes'] = array(''=>'全部') + GameService::getGameTypeOption();
		$data['tags'] = json_encode(GameService::getGameTags());
		$data['search'] = $search;
		$result = $this->loadData(false, $search,$page,$pagesize);
		$total = $result['total'];
		$data['totalDownload'] = $result['totalDownload'];
		$data['datalist'] = $result['result'];
		$data['games'] = $result['games'];
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);	
		$data['form_route'] = 'game/data/search';	
		$data['pagelinks'] = $pager->links();
		
		return $this->display('download-data',$data);
	}
	
	public function getRealSearch()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = Input::only('zone','pricetype','type','gid','gname','tag','startdate','enddate');
		$data = array();
		$data['zone'] = array(''=>'全部','1'=>'精品','0'=>'普通');
		$data['pricetype'] = array(''=>'全部','1'=>'免费','2'=>'限免','3'=>'收费');
		$data['gametypes'] = array(''=>'全部') + GameService::getGameTypeOption();
		$data['tags'] = json_encode(GameService::getGameTags());
		$data['search'] = $search;
		$result = $this->loadData(true, $search,$page,$pagesize);
		$total = $result['total'];
		$data['totalDownload'] = $result['totalDownload'];
		$data['datalist'] = $result['result'];
		$data['games'] = $result['games'];
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);	
		$data['form_route'] = 'game/data/real-search';	
		$data['pagelinks'] = $pager->links();
		
		return $this->display('download-data',$data);
	}
	
	public function getGameMap()
	{
		$game_id = (int)Input::get('game_id',0);
		$month = (int)Input::get('month',date('m'));
		$year = (int)Input::get('year',date('Y'));
		$data = array();
		$days = date('t',$month);
		$map = array();
		$max = 0;
		for($i=1;$i<=$days;$i++){
			$startdate = date('Y-m-d H:i:s',mktime(0,0,0,$month,$i,$year));
			$enddate = date('Y-m-d H:i:s',mktime(23,59,59,$month,$i,$year));
			$number = self::loadGameData($game_id, $startdate, $enddate,false);
			$number>$max && $max = $number;
			$map[] = array($i,$number);
		}
		$data['map'] = json_encode($map);
		$data['max'] = $max + ($max%100);
		$data['month'] = $month;
		$data['year'] = $year;
		$data['months'] = array('1'=>'1月','2'=>'2月','3'=>'3月','4'=>'4月','5'=>'5月','6'=>'6月','7'=>'7月','8'=>'8月','9'=>'9月','10'=>'10月','11'=>'11月','12'=>'12月');
		$data['form_route'] = 'game/data/game-map';
		return $this->display('download-data-month-map',$data);
	}
	
    public function getRealGameMap()
	{
		$game_id = (int)Input::get('game_id',0);
		$month = (int)Input::get('month',date('m'));
		$year = (int)Input::get('year',date('Y'));
		$data = array();
		$days = date('t',$month);
		$map = array();
		$max = 0;
		for($i=1;$i<=$days;$i++){
			$startdate = date('Y-m-d H:i:s',mktime(0,0,0,$month,$i,$year));
			$enddate = date('Y-m-d H:i:s',mktime(23,59,59,$month,$i,$year));
			$number = self::loadGameData($game_id, $startdate, $enddate,true);
			$number>$max && $max = $number;
			$map[] = array($i,$number);
		}
		$data['map'] = json_encode($map);
		$data['max'] = $max + ($max%100);
		$data['month'] = $month;
		$data['year'] = $year;
		$data['months'] = array('1'=>'1月','2'=>'2月','3'=>'3月','4'=>'4月','5'=>'5月','6'=>'6月','7'=>'7月','8'=>'8月','9'=>'9月','10'=>'10月','11'=>'11月','12'=>'12月');
		$data['form_route'] = 'game/data/real-game-map';
		return $this->display('download-data-month-map',$data);
	}
	
	protected function loadGameData($game_id,$startdate,$enddate,$real=true)
	{
		$url_total = Config::get('app.module_data_url') . ($real==true ? 'module_data/game_download_count_list_amount' : 'module_data/game_display_download_count_list_amount');
		$params = array(
		    'platform'=>1,		    
		    'time_from'=>$startdate,
		    'time_to'=>$enddate
		);
		$game_id && $params['game_id'] = $game_id;
		$count_str = \CHttp::request($url_total,$params,'GET','text');
		$total = $totalDownload = 0;
		strpos($count_str,',')!==false && list($total,$totalDownload) = explode(',',$count_str);
		return (int)$totalDownload;
	}
	
	protected function loadData($real,$search,$pageIndex=1,$pageSize=10)
	{
		$url = Config::get('app.module_data_url') . ($real==true ? 'module_data/game_download_count_list' : 'module_data/game_display_download_count_list');
		$params = array();
		$params['platform'] = 1;//1:IOS2:android
		if(isset($search['zone']) && !empty($search['zone'])) $params['game_type'] = $search['zone'];
		if(isset($search['pricetype']) && !empty($search['pricetype'])) $params['price_type'] = $search['pricetype'];
		if(isset($search['gid']) && !empty($search['gid'])) $params['game_id'] = $search['gid'];
		if(isset($search['gname']) && !empty($search['gname'])) $params['game_name'] = ($search['gname']);
		if(isset($search['type']) && !empty($search['type'])) $params['game_category'] = $search['type'];
		if(isset($search['tag']) && !empty($search['tag'])) $params['game_tag'] = ($search['tag']);
		if(isset($search['startdate']) && !empty($search['startdate'])) $params['time_from'] = ($search['startdate'] . ' 00:00:00');
		if(isset($search['enddate']) && !empty($search['enddate'])) $params['time_to'] = ($search['enddate'] . ' 23:59:59');
		$params['page'] = $pageIndex;
		$params['size'] = $pageSize;
		//echo $url . '?' . http_build_query($params);
		$url_total = Config::get('app.module_data_url') . ($real==true ? 'module_data/game_download_count_list_amount' : 'module_data/game_display_download_count_list_amount');
		$count_str = \CHttp::request($url_total,$params,'GET','text');
        echo "totle:".$url_total;
		$result = \CHttp::request($url,$params);
        echo $url;
        print_r($params);
		$total = $totalDownload = 0;
		strpos($count_str,',')!==false && list($total,$totalDownload) = explode(',',$count_str);
		$out = array('total'=>0,'result'=>array(),'games'=>array(),'totalDownload'=>0);
		//print_r($result);exit;
		if(!is_array($result)) return $out;
		$gids = array();
		foreach($result as $row){
			$data = array();
			$data['gid'] = $row['gameId'];
			$data['downloadCount'] = $row['downloadCount'];
			$data['totalDownload'] = $row['totalDownload']; 
			$gids[] = $row['gameId'];			
			$out['result'][] = $data;
		}
		$games = GameService::getGamesByIds($gids);
		$out['games'] = $games;
		$out['total'] = $total;
		$out['totalDownload'] = $totalDownload;
		
		return $out;
	}
}