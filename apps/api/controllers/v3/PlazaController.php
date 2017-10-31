<?php
use Illuminate\Support\Facades\Input;
use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;
/**
 * 广场
 */
class PlazaController extends BaseController
{
	/**
	 * 首页
	 */
	public function home()
	{
		$v = '1';
		$out = array();
		$out['titleImage'] = 'http://img.youxiduo.com/userdirs/home/paihang@2x.png' . '?v=' . $v;
		$out['signImage'] = 'http://img.youxiduo.com/userdirs/home/xingzuo@2x.png' . '?v=' . $v;
		$out['hotSearch'] = array();
		$games = GameService::getHotSearchGames();
		foreach($games as $index=>$row){
			$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'] ? : $row['gname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);			
			
			$out['hotSearch'][$index] = $game;
		}
		
		return $this->success(array('result'=>$out));
	}
}