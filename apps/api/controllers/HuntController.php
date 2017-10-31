<?php
use Yxd\Services\HuntService;
use Illuminate\Support\Facades\Input;
/**
 * 寻宝箱
 */
class HuntController extends BaseController
{
	/**
	 * 寻宝箱首页
	 */
	public function home()
	{
		$result = HuntService::homePage();
		
		return $this->success(array('result'=>$result));
	}
}