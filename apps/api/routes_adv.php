<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\log;
use Youxiduo\V4\Helper\OutUtility;
use Youxiduo\MyService\QueryService;

Route::get('v4/advs/launch',array('before'=>'uri_verify',function(){
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = Input::get('version');
	$advSpaceId = Input::get('advSpaceId');
	$result = Youxiduo\V4\Advs\AdvSpaceService::getLaunch($appname,$channel,$version,$advSpaceId);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//轮播广告
/***
Route::get('v4/advs/slide',array('before'=>'uri_verify',function(){
	
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = '3.0.0';//Input::get('version');
	$advSpaceId = Input::get('advSpaceId');
	$result = Youxiduo\V4\Advs\AdvSpaceService::getSlide($appname,$channel,$version,$advSpaceId);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));
****/
//轮播广告 //http://api.youxiduo.dev/v4/advs/slide?version=3.3&advSpaceId=home_slide&platform=ios
Route::get('v4/advs/slide',array('before'=>'uri_verify',function(){  
	
	$datainfo=array();
	$input=Input::only('appname','channel','version','advSpaceId','platform');
	//Log::error('slide->advSpaceId:'.$input['advSpaceId'].'platform:'.$input['platform'].'version:'.$input['version']);
	$biTian=array('advSpaceId'=>'required','version'=>'required',);
	$message = array(
            'advSpaceId.required' => 'advSpaceId不能为空',
            'version.required' => 'version不能为空',
            
    );
    $validator = Validator::make($input,$biTian,$message);
    if($validator->fails()){
    	Log::error('errorDescription->'.$validator->messages()->first());
    	return OutUtility::outSuccess(array());
        //return Response::json(array('errorCode'=>1,"errorDescription"=>$validator->messages()->first()));
    }
    if(empty($input['platform'])){
     	$input['platform']='ios';
     }
    $type='';
    switch ($input['advSpaceId']) {
    	case 'home_slide':
    		$type="biaoshi = 'home_slide' and is_show=1 ";
    		break;
    	case 'forum_home_slide':
    		$type="biaoshi = 'forum_home_slide' and is_show=1 ";
    		break;
    	case 'shop_home_slide':
    		$type="biaoshi = 'shop_home_slide' and is_show=1 ";
    		break;
        case 'game_video_pic_slide':
            $type="biaoshi = 'game_video_pic_slide' and is_show=1 ";
            break;
        case 'video_home_slide':
            $type="biaoshi = 'video_home_slide' and is_show=1 ";
            break;
    }
    if(empty($type)){
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>'advSpaceId参数错误'));
    	Log::error('errorDescription->advSpaceId参数错误');
    	return OutUtility::outSuccess(array());
    }
    $location_ids=Youxiduo\Adv\AdvService::FindLocation($type);
    if(!empty($location_ids)){
    	$ids=$location_info=array();
    	foreach ($location_ids as $key => &$value) {
    		# code...
    		$ids[]=$value['Id'];
    		$location_info[$value['Id']]['sort']=$value['sort'];
    	}
    	$ids=join(',',$ids);
    }
    if(empty($ids)){
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>'地址缺失'));
    	Log::error('errorDescription->地址缺失');
        return OutUtility::outSuccess(array());
    }
    $platform=$input['platform'];
    if(empty($ids)){
    	Log::error('errorDescription->推荐位置ID获取失败');
        return OutUtility::outSuccess(array());
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>'推荐位置ID获取失败'));
    }
    $where=" location_id in($ids) and adv_type = '轮播'  and versionform='$platform' and is_show=1 ";
    //首先查询推荐中的数据
    $result=Youxiduo\Adv\AdvService::FindRecommendedadv($where);
   	if(!empty($result)){
    	foreach($result as $key=>&$value_){
    		if(sizeof($datainfo) > 4){
    			break;
    		}
    		$str_=' apptypes_id = '.$value_['apptypes_id'].' and is_show =1 ';
			$version=Youxiduo\Adv\AdvService::FindVersion($str_);
			if(!empty($version)){ 
						$datainfo_=&$datainfo[$location_info[$value_['location_id']]['sort']] ;
	 					$datainfo_['title']=$value_['title'];
						$datainfo_['linkid']=$value_['link_id'];
						$datainfo_['type']=$value_['recommended_id'];
						$datainfo_['img']='http://test.img.youxiduo.com'.$value_['litpic'];
			}
    	}	

    }
    //再次查询广告表中的数据 如果有数据将替换推荐表中的数据
    if(!empty($input['appname'])) $str=" and appname='".$input['appname']."'";
    if(!empty($input['channel'])) $str.=" and channel='".$input['channel']."'";
	$where="adv_type='轮播'  and versionform='".$input['platform']."'";
    if(!empty($str)) $where="adv_type='轮播' and versionform='".$input['platform']."'  $str";
	$result=Youxiduo\Adv\AdvService::FindAppadv($where); //print_r($result);exit;
	if(!empty($result)){
		foreach($result as $key=>&$value){
			if($value['is_show'] == 1){
				$str=' apptypes_id = '.$value['apptypes_id'].' and versionNumber='.$input['version'].' and is_show =1 ';
				$version=Youxiduo\Adv\AdvService::FindVersion($str);
				if(!empty($version)){
					$location=Youxiduo\Adv\AdvService::getLocationInfo($value['location_id']);
					if($location['is_show'] == 1 && !empty($location['sort'])){
						if($location['sort'] > 5){ continue;}
						$datainfo_=&$datainfo[$location['sort']];
		 				$datainfo_['title']=$value['advname'];
						$datainfo_['linkid']=$value['downurl'];
						$datainfo_['type']='';
						$datainfo_['img']='http://test.img.youxiduo.com'.$value['litpic'];
					}	
					
				}
			}
		}
	} 
	
	if(!empty($datainfo)){
		$parms=array();
		foreach($datainfo as $value){
			$parms[]=array(
				'title'=>$value['title'],
				'linkid'=>$value['linkid'],
				'type'=>$value['type'],
				'img'=>$value['img'],
			);
		}
		return OutUtility::outSuccess($parms);
	}
	return OutUtility::outSuccess(array());
	
}));
//Banner广告
/***
Route::get('v4/advs/banner',array('before'=>'uri_verify',function(){
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = '3.0.0';//Input::get('version');
	$advSpaceId = Input::get('advSpaceId');
	$result = Youxiduo\V4\Advs\AdvSpaceService::getBanner($appname,$channel,$version,$advSpaceId);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

***/

//Banner广告  api.youxiduo.dev/v4/advs/banner?version=3.3&advSpaceId=home_banner_1&platform=ios
Route::get('v4/advs/banner',array('before'=>'uri_verify',function(){
	 //Log::error('banner->'.print_r($input));
	 $datainfo=array();
	 $input=Input::only('appname','channel','version','advSpaceId','platform');
	 //Log::error('slide->advSpaceId:'.$input['advSpaceId'].'version:'.$input['version']);
	 $biTian=array('advSpaceId'=>'required','version'=>'required');
	 $message = array(
            'advSpaceId.required' => 'advSpaceId不能为空',
            'version.required' => 'version不能为空',
            //'platform.required' => 'platform不能为空',
     );
     $validator = Validator::make($input,$biTian,$message);
     if($validator->fails()){
     	Log::error('errorDescription->'.$validator->messages()->first());
    	return OutUtility::outSuccess(array());
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>$validator->messages()->first()));
     }
     if(empty($input['platform'])){
     	$input['platform']='ios';
     }
     
     switch ($input['advSpaceId']) {
    	case 'home_banner_1':
    		$type="biaoshi = 'home_banner_1' and is_show=1 ";
    		break;
    	case 'home_banner_2':
    		$type="biaoshi = 'home_banner_2' and is_show=1 ";
    		break;
    	case 'game_detail_banner_1':
    		$type="biaoshi = 'game_detail_banner_1' and is_show=1 ";
    		break;
    	case 'shop_banner_1':
    		$type="biaoshi = 'shop_banner_1' and is_show=1 ";
    		break;
        
    }
    if(empty($type)){
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>'advSpaceId参数错误'));
    	 Log::error('errorDescription->advSpaceId参数错误');
    	 return OutUtility::outSuccess(array());
    	 
    }
    $location_ids=Youxiduo\Adv\AdvService::FindLocation($type);
    if(!empty($location_ids)){
    	$ids=$location_info=array();
    	foreach ($location_ids as $key => &$value) {
    		# code...
    		$ids[]=$value['Id'];
    		$location_info[$value['Id']]['sort']=$value['sort'];
    	}
    	$ids=join(',',$ids);
    }
    if(empty($ids)){
    	//return Response::json(array('errorCode'=>1,"errorDescription"=>'地址缺失'));
    	Log::error('errorDescription->地址缺失');
    	return OutUtility::outSuccess(array());
    }
    $result=array();
    $result=Youxiduo\Adv\AdvService::getBanner($input,$ids);
    if(!empty($result)){ 
    	$str=' apptypes_id = '.$result['apptypes_id'].' and versionNumber='.$input['version'].' and is_show =1 ';
		$version=Youxiduo\Adv\AdvService::FindVersion($str);
		if(!empty($version)){ 
			//$location=Youxiduo\Adv\AdvService::getLocationInfo($value['location_id']);
			$datainfo_=&$datainfo[];
		 	$datainfo_['title']=$result['advname'];
			$datainfo_['linkid']='';
			$datainfo_['type']='';
			$datainfo_['img']='http://test.img.youxiduo.com'.$result['litpic'];	
		}	
	}
	if(!empty($datainfo)){ 
		return OutUtility::outSuccess($datainfo);
	}
	/***
	$Nature_ids=Youxiduo\Adv\AdvService::FindNature(" recommend_type='推荐位' and is_show=1");
  	if(empty($Nature_ids)){ 
  	 	return OutUtility::outError(300,array('errorDescription'=>'没有相关推荐位数据'));
  	}
    $ids=array();
    foreach ($Nature_ids as $key => &$value) {
    	# code...
    	$ids[]=$value['Id'];
	}
    $ids=join(',',$ids);
    ***/
	$platform=$input['platform'];
    //$where=" nature_id in($ids) and adv_type='条幅'  and versionform='$platform' and is_show=1 ";
	$where=" location_id in ($ids) and adv_type='条幅'  and versionform='$platform' and is_show=1  ";	
	$result=Youxiduo\Adv\AdvService::FindRecommendedadv_($where,'createdatetime');//print_r($result);exit;
	if(!empty($result)){
		//foreach($result as $key=>&$value_){3
    		$str_=' apptypes_id = '.$result['apptypes_id'].' and is_show =1 ';
			$version=Youxiduo\Adv\AdvService::FindVersion($str_); //print_r($version);exit;
			if(!empty($version)){
					$datainfo_=&$datainfo[];
 					$datainfo_['title']=$result['title'];
					$datainfo_['linkid']=$result['link_id'];
					$datainfo_['type']=$result['recommended_id'];
					$datainfo_['img']='http://test.img.youxiduo.com'.$result['litpic'];
					$datainfo_['words']=$result['words'];                 
			}
    	//}
	}
    if(!empty($datainfo)){ 
    	// 返回成功
    	return OutUtility::outSuccess($datainfo);
    }
    return OutUtility::outSuccess(array());
    
}));

//推荐位广告
/***
Route::get('v4/advs/recommend_space',array('before'=>'uri_verify',function(){
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = '3.0.0';//Input::get('version');
	$advSpaceId = Input::get('advSpaceId');
	$result = Youxiduo\V4\Advs\AdvSpaceService::getRecommendSpace($appname,$channel,$version,$advSpaceId);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));
****/
//api.youxiduo.dev/v4/advs/v4recommend_space?version=3.3&advSpaceId=home_recommend&platform=ios
Route::get('v4/advs/v4recommend_space',array('before'=>'uri_verify',function(){
     $datainfo=array();
     $input=Input::only('appname','channel','version','advSpaceId','platform');
     Log::error('recommend_space->advSpaceId:'.$input['advSpaceId'].'version:'.$input['version']);
     $v=Youxiduo\Adv\AdvService::my_validator($input);
     if(empty($v)){
        return OutUtility::outSuccess(array());
     }
     if(empty($input['platform'])){
            $input['platform']='ios';
     }
     $type='';
     switch ($input['advSpaceId']) {
        case 'home_recommend': 
            $type="biaoshi = 'home_recommend' and is_show=1 ";
            break;
        case 'shop_recommend':
            $type="biaoshi = 'shop_recommend' and is_show=1 ";
            break;
        case 'ios_v4_task_center_ad':
            $type="biaoshi = 'ios_v4_task_center_ad' and is_show=1 ";
            break;
         case 'ios_v4_me_rec_suspension':
             $type="biaoshi = 'ios_v4_me_rec_suspension' and is_show=1 ";
             break;
         case 'ios_v4_me_rec_game':
             $type="biaoshi = 'ios_v4_me_rec_game' and is_show=1 ";
             break;
     }
     if(empty($type)){
        //return Response::json(array('errorCode'=>1,"errorDescription"=>'advSpaceId参数错误'));
        Log::error('errorDescription->advSpaceId参数错误');
        return OutUtility::outSuccess(array());
     }
     $ids=Youxiduo\Adv\AdvService::getLocationids($type);
     if(empty($ids['ids'])){
        //return Response::json(array('errorCode'=>1,"errorDescription"=>'advSpaceId参数错误'));
        Log::error('errorDescription->地址获取失败');
        return OutUtility::outSuccess(array());
     }
     $datainfo = Youxiduo\Adv\AdvService::getdataAdvlist('推荐',$input['platform'],$ids['ids'],$ids['datalist']);
     if(!empty($datainfo)){
        $parms=array();
        foreach($datainfo as $value){
            $parms[]=array(
                'title'=>$value['title'],
                'linkid'=>$value['linkid'],
                'type'=>$value['type'],
                'img'=>$value['img'],
                'words'=>$value['words'],
            );
        }
        return OutUtility::outSuccess($parms);
    }
    return OutUtility::outSuccess(array());


}));	
//推荐游戏广告
//api.youxiduo.dev/v4/advs/v4recommend_games?version=3.3&advSpaceId=home_single&platform=ios
Route::get('v4/advs/v4recommend_games',array('before'=>'uri_verify',function(){
     $datainfo=array();
     $input=Input::only('appname','channel','version','advSpaceId','platform');
     Log::error('recommend_games->advSpaceId:'.$input['advSpaceId'].'version:'.$input['version']);
     $v=Youxiduo\Adv\AdvService::my_validator($input);
     if(empty($v)){
        return OutUtility::outSuccess(array());
     }
     if(empty($input['platform'])){
            $input['platform']='ios';
     }
     $type='';
     switch ($input['advSpaceId']) {
        case 'home_network': //首页网络游戏
            $type="biaoshi = 'home_network' and is_show=1 ";
            $mytype='游戏';
            break;
        case 'home_single'://首页单机游戏
            $type="biaoshi = 'home_single' and is_show=1 ";
            $mytype='游戏';
            break;
        case 'search_hot'://热门搜索
            $type="biaoshi = 'search_hot' and is_show=1 ";
            break;
        case 'guess_like'://猜你喜欢
            $type="biaoshi = 'guess_like' and is_show=1 ";
            break;
        case 'type_hot'://游戏类型中的热门游戏
            $type="biaoshi = 'type_hot' and is_show=1 ";
            break;
     }
     $ids=Youxiduo\Adv\AdvService::getLocationids($type);
     if(empty($ids['ids'])){
        //return Response::json(array('errorCode'=>1,"errorDescription"=>'advSpaceId参数错误'));
        Log::error('errorDescription->地址获取失败');
        return OutUtility::outSuccess(array());
     }
     $datainfo = Youxiduo\Adv\AdvService::getdataAdvlist('游戏信息',$input['platform'],$ids['ids'],$ids['datalist']);
     if(!empty($datainfo)){
        $parms=array();
        foreach($datainfo as $value){
            $parms[]=array(
                'title'=>$value['title'],
                'linkid'=>$value['linkid'],
                'type'=>$value['type'],
                'img'=>$value['img'],
                'words'=>$value['words'],
            );
        }
        return OutUtility::outSuccess($parms);
    }
    return OutUtility::outSuccess(array());

}));


//推荐游戏广告
Route::get('v4/advs/recommend_games',array('before'=>'uri_verify',function(){
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = '3.0.0';//Input::get('version');
	$advSpaceId = Input::get('advSpaceId');
	$result = Youxiduo\V4\Advs\AdvSpaceService::getRecommendGames($appname,$channel,$version,$advSpaceId);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

// V4 广告 前台api //首页弹窗广告 Popup v4adv adv_logo advSpaceId
Route::get('v4/advs/adv_list',array('before'=>'uri_verify',function(){
    //api.youxiduo.dev/v4/advs/v4recommend_space?version=3.3&advSpaceId=home_recommend&platform=ios
    $inputinfo=Input::all();
    $result=Youxiduo\V4\Advs\AdvV4Service::getByList($inputinfo);
    return OutUtility::outSuccess($result);
}));

//广告点击接口
Route::get('v4/advs/adv_statistics',array('before'=>'uri_verify',function(){
    //api.youxiduo.dev/v4/advs/adv_statistics?version=4.0&adv_logo=游戏详情顶部&platform=ios&adv_id=11c378ca-b85a-4a2f-81a1-cdd95c42&click=1&uid=11&appname=appname&idfa=idfa
    //$inputinfo=Input::all();
    //$result=Youxiduo\V4\Advs\AdvV4Service::statistics($inputinfo);
    $appname = Input::get('appname','');
    $version = Input::get('version','4.0.0');
    $advid = Input::get('advid');
    $mac = Input::get('mac');
    $idfa = Input::get('idfa');
    $osversion = Input::get('osversion','');
    $linkid = Input::get('linkid',0);
    $adv_logo = Input::get('adv_logo','');
    $openudid = Input::get('openudid','');
    $source = Input::get('source','');
    $type = Input::get('type',0);
    $os = Input::get('os');

    if(empty($idfa) || empty($advid)){
        Log::error('errorDescription->advstatisticsd参数错误');
        return OutUtility::outSuccess(array());
    }
    $result = Youxiduo\Adv\AdvService::Advstat($appname,$version,$adv_logo,$osversion,$advid,$idfa,$openudid,$type,$linkid);
    return OutUtility::outSuccess($result);
}));

//游戏广告是否激活
Route::get('v4/advs/adv_isactive',array('before'=>'uri_verify',function(){
    $inputinfo = array();
    $advid = Input::get('advid');
    $idfa = Input::get('idfa');

    if(empty($advid) || empty($idfa)){
        Log::error('errorDescription->advstatisticsd参数错误');
        return OutUtility::outSuccess(array());
    }
    $result=Yxd\Services\Cms\AdvService::isactive($advid,$idfa);
    return OutUtility::outSuccess($result);
}));
