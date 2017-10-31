<?php
use Illuminate\Support\Facades\Input;
use Yxd\Services\Cms\ShareService;
/**
 * 分享
 */
class ShareController extends BaseController
{
	public function to()
	{		
		/**
		*
		*/
		$type = (int)Input::get('type',0);
		$shareid = (int)Input::get('shareid');
		$ishtml5 = 1;//(int)Input::get('ishtml5',0);
		if($type==1){
		    $result = ShareService::shareGame($shareid,4,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==2){
		    $result = ShareService::shareNewGame($shareid,5,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==3){
		    $result = ShareService::shareSpecial($shareid,3,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==4){
		    $result = ShareService::shareGuide($shareid,6,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==5){
		    $result = ShareService::shareOpinion($shareid,7,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==6){
		    $result = ShareService::shareNews($shareid,8,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==7){
			$result = ShareService::shareVideo($shareid,1,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
			
		}elseif($type==8){
		    $result = ShareService::shareTopic($shareid,12,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==9){
		    $result = ShareService::shareActivity($shareid,9,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==10){
		    $result = ShareService::shareGift($shareid,10,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==11){
			$result = ShareService::shareGoods($shareid,13,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==12){//寻宝箱中奖
			$result = ShareService::shareHunt($shareid,14,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==13){//寻宝箱未中奖
			$result = ShareService::shareHunt($shareid,15,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==16){//论坛
		    $result = ShareService::shareForum($shareid,16,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==17){//小游戏首页
		    $result = ShareService::shareXyxHome(17,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==18){//小游戏详情
		    $result = ShareService::shareXyxDetail($shareid,18,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}elseif($type==19){//小游戏列表
		    $result = ShareService::shareXyxList($shareid,19,$ishtml5);
		    if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}else{
		    $result = ShareService::shareAbout(11,$ishtml5);
			if($result){
				return $this->success(array('result'=>$result));
			}else{
				return $this->fail('11211','信息不存在');
			}
		}
	}
}