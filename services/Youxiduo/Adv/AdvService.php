<?php
/**
 * @package Youxiduo
 * @category adv 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Adv;
use Youxiduo\Adv\Model\Apptypes;
use Youxiduo\Adv\Model\Appadv;
use Youxiduo\Adv\Model\Location;
use Youxiduo\Adv\Model\Nature;
use Youxiduo\Adv\Model\Recommend; 
use Youxiduo\Adv\Model\Recommendedadv;
use Youxiduo\Adv\Model\Version;
use Youxiduo\Adv\Model\Task;
use Youxiduo\Adv\Model\V4appadv;
use Youxiduo\Adv\Model\V4appadvthird;
use Youxiduo\Adv\Model\Statisticss;
use Youxiduo\Base\BaseService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\log; 
class AdvService extends BaseService
{	
	//Apptype列表数据获取
	public static function getApptypeList($search=array())
	{	
		return Apptypes::pagelist($search['page'],$search['pageSize'],Apptypes::setsearch($search));
	}
	//Apptype数据详情获取
	public static function getApptypeInfo($id,$key=''){
		return empty($key)?Apptypes::getInfo('Id',$id):Apptypes::getinfos($key,$id);
	}
	//查询Version表数据
	public static function FindVersion($where='')
	{	
		return Version::find($where);
	}
	//查询Recommend表数据
	public static function FindRecommend($where='')
	{
		return Recommend::find($where);
	}
	//Apptype数据添加修改
	public static function ApptypeAddEdit($datainfo=array())
	{	
		$session=Session::get('youxiduo_admin');
		$datainfo['is_show']=1;
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Apptypes::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Apptypes::update($datainfo,$datainfo['Id']);
	}
	//Version数据添加修改
    public static function VersionAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Version::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Version::update($datainfo,$datainfo['Id']);
    } 
    //========================Apptype模块所需结束 ===================================
    //========================Recommend模块所需开始 =================================       
    //Recommend列表数据获取
	public static function getRecommendList($search=array())
	{	
		return Recommend::pagelist($search['pageIndex'],$search['pageSize'],Recommend::setsearch($search));
	}

	public static function FindApptypes($where='')
	{
		return Apptypes::find($where);
	}

	public static function getRecommendInfo($id)
	{
		return empty($key)?Recommend::getInfo('Id',$id):Recommend::getinfos($key,$id);
	}
   
    public static function RecommendAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Recommend::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Recommend::update($datainfo,$datainfo['Id']);
    }
    //========================Recommend模块所需结束=================================
    //========================Nature模块所需开始===================================
    public static function  getNatureList($search=array())
	{
		return Nature::pagelist($search['page'],$search['pageSize'],Nature::setsearch($search));
	}
	//Nature数据详情获取
	public static function getNatureInfo($id,$key=''){
		return empty($key)?Nature::getInfo('Id',$id):Nature::getinfos($key,$id);
	}
	//查询Location表数据
	public static function FindLocation($where='')
	{
		return Location::find($where);
	}

	public static function NatureAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Nature::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Nature::update($datainfo,$datainfo['Id']);
    }
    //添加修改前判断是否拥有相同记录
    public static function is_Nature_name($where=array())
    {
    	return Nature::getInfos_($where);
    }

    //========================Nature模块所需结束===================================
     
    //========================recommendedadv模块所需开始==================================
    public static function getRecommendedadvList($search=array(),$where='')
    {
    	return Recommendedadv::pagelist($search['page'],$search['pageSize'],!empty($where)?$where:Appadv::setsearch($search));
    }

    //Recommendedadv数据详情获取
	public static function getRecommendedadvInfo($id,$key=''){
		return empty($key)?Recommendedadv::getInfo('Id',$id):Recommendedadv::getinfos($key,$id);
	}

	public static function RecommendedadvAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Recommendedadv::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Recommendedadv::update($datainfo,$datainfo['Id']);
    }


     //查询Recommendedadv表数据
    public static function FindRecommendedadv($where='')
    {
        return Recommendedadv::find($where);
    }


    //查询Recommendedadv表数据 带排序 返回一条数据
    public static function FindRecommendedadv_($where='',$orderbykey)
    {
        return Recommendedadv::find_($where,$orderbykey,'desc');
    }
    


    //========================recommendedadv模块所需结束==================================

    //========================appadv模块所需开始==================================
 	public static function getReleaseadvList($search=array(),$where='')
	{
		return Appadv::pagelist($search['page'],$search['pageSize'],!empty($where)?$where:Appadv::setsearch($search));
	}

	//Appadv数据详情获取
	public static function getAppadvInfo($id,$key=''){
		return empty($key)?Appadv::getInfo('Id',$id):Appadv::getinfos($key,$id);
	}

    //查询Appadv表数据
    public static function FindAppadv($where='')
    {
        return Appadv::find($where);
    }

    //
    public static function  FindAppadv_($where='',$orderbykey='',$orderby='desc')
    {
        return Appadv::find_($where,$orderbykey,$orderby);
    }
     

    public static function ReleaseadvAddEdit($datainfo=array())
    {
        $session=Session::get('youxiduo_admin');
        
        if(empty($datainfo['Id'])){
            $datainfo['is_show']=1;
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Appadv::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
       
        return Appadv::update($datainfo,$datainfo['Id']);
    }
    //========================appadv模块所需结束===================================

    //========================Location模块所需开始==================================    
	public static function  getLocationList($search=array())
	{
		return Location::pagelist($search['pageIndex'],$search['pageSize'],Location::setsearch($search));
	}
	
	//Location数据详情获取
	public static function getLocationInfo($id,$key=''){
		return empty($key)?Location::getInfo('Id',$id):Location::getinfos($key,$id);
	}
	//查询Nature表数据
	public static function FindNature($where='')
	{
		return Nature::find($where);  
	}


	public static function LocationAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Location::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Location::update($datainfo,$datainfo['Id']);
    }
    
    //========================Location模块所需结束=================================
    public static function getLocationids($type='')
    {
          $location_ids=self::FindLocation($type);
          if(!empty($location_ids)){
            $ids=$location_info=array();
            foreach ($location_ids as $key => &$value) {
                $ids[]=$value['Id'];
                $location_info[$value['Id']]['sort']=$value['sort'];
            }
          }   
          if(empty($ids)){
                Log::error('errorDescription->推荐位置ID获取失败');
                return 0;
                //return Response::json(array('errorCode'=>1,"errorDescription"=>'推荐位置ID获取失败'));
          }
          return array('ids'=>join(',',$ids),'datalist'=>$location_info);
    }



     public static function getdataAdvlist($adv_type='轮播',$platform,$ids,$datalist)
     {    
          $datainfo=array();
          $where=" location_id in($ids) and adv_type = '$adv_type'  and versionform='$platform' and is_show=1 ";
          //首先查询推荐中的数据
          $result=self::FindRecommendedadv($where); 
          if(!empty($result)){
                foreach($result as $key=>&$value_){
                    $str_=' apptypes_id = '.$value_['apptypes_id'].' and is_show =1 ';
                    $version=self::FindVersion($str_);
                    if(!empty($version)){ 
                            $datainfo_=&$datainfo[$datalist[$value_['location_id']]['sort']] ;
                            $datainfo_['title']=$value_['title'];
                            $datainfo_['linkid']=($adv_type == '游戏列表' || $adv_type=='游戏信息') ? $value_['gid'] : $value_['link_id'];
                            $datainfo_['type']=$value_['recommended_id'];
                            $datainfo_['img']='http://test.img.youxiduo.com'.$value_['litpic'];
                            $datainfo_['words']=$value_['words'];
                    }
                }   

            }
            //再次查询广告表中的数据 如果有数据将替换推荐表中的数据
            if(!empty($input['appname'])) $str=" and appname='".$input['appname']."'";
            if(!empty($input['channel'])) $str.=" and channel='".$input['channel']."'";
            $where="adv_type='轮播'  and versionform='$platform'";
            if(!empty($str)) $where="adv_type='$adv_type' and versionform='$platform'  $str";
            $result=self::FindAppadv($where); //print_r($result);exit;
            if(!empty($result)){
                foreach($result as $key=>&$value){
                    if($value['is_show'] == 1){
                        $str=' apptypes_id = '.$value['apptypes_id'].' and versionNumber='.$input['version'].' and is_show =1 ';
                        $version=self::FindVersion($str);
                        if(!empty($version)){
                            $location=self::getLocationInfo($value['location_id']);
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
            return $datainfo;

        
    }


     public static function my_validator($input=array())
     {
         $biTian=array('advSpaceId'=>'required','version'=>'required');
         $message = array(
            'advSpaceId.required' => 'advSpaceId不能为空',
            'version.required' => 'version不能为空',
            //'platform.required' => 'platform不能为空',
        );
        $validator = Validator::make($input,$biTian,$message);
        if($validator->fails()){
            Log::error('errorDescription->'.$validator->messages()->first());
            return 0;
            //return Response::json(array('errorCode'=>1,"errorDescription"=>$validator->messages()->first()));
        }
        return 1;
     }


	/**处理返回数据**/
    public static function _processingInterface($result,$data,$pagesize=10,$is_ajax=0){
        $data['search']=$data;
        $data['datalist']  = !empty($result['result'])?$result['result']:array();
        if($is_ajax == 1){
            $data['totalCount']=$result['totalCount'];
            return $data;
        } 
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['search']['pageIndex']);
        $pager->appends($data['search']);
        $data['pagelinks'] = $pager->links();
        return $data;
    }

    //
    public static function getBanner($input=array(),$ids=0)
    {
        $result=array();
        if(!empty($input['appname'])) $str=" and appname='".$input['appname']."'";
        if(!empty($input['channel'])) $str.=" and channel='".$input['channel']."'";
        $where=" location_id in ($ids)  and adv_type='条幅'  and versionform='".$input['platform']."' and is_show=1 ";
        if(!empty($str)) $where="location_id in ($ids)  and adv_type='条幅'   and versionform='".$input['platform']."' and is_show=1  $str";
        $result=self::FindAppadv_($where,'createdatetime','DESC');//echo 1; print_r($result);exit;
        return $result;
    }

    public static function getTaskList($search=array()){
        return Task::pagelist($search['pageIndex'],$search['pageSize'],Task::setsearch($search));
    }

    public static function getTaskInfo($id)
    {
        return Task::getInfo('id',$id);
    }

    public static function taskSave($datainfo=array())
    {
        $session=Session::get('youxiduo_admin');
        if(empty($datainfo['id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdate']= date('Y-m-d G:i:s');
            return Task::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydate']= date('Y-m-d G:i:s');
        $id = $datainfo['id'];unset($datainfo['id']);
        return Task::update($datainfo,$id);
    }

    public static function taskDel($value,$key='id', $where='=')
    {
        return Task::delete($key, $where, $value);
    }
    
    //V4广告第三方链接
    public static function AdvthirdSave($datainfo=array())
    {
        $session=Session::get('youxiduo_admin');
        if(empty($datainfo['id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdate']= date('Y-m-d G:i:s');
            return V4appadvthird::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydate']= date('Y-m-d G:i:s');
        $id = $datainfo['id'];unset($datainfo['id']);
        return V4appadvthird::update($datainfo,$id);
    }
    
    public static function AdvthirdDel($value,$key='id', $where='=')
    {
        return V4appadvthird::delete($key, $where, $value);
    }
    
    public static function FindAdvthird($where='')
    {
        return V4appadvthird::find($where);
    }
    
    public static function FindAdvSort($where='')
    {
        $out = array();
        $adv = V4appadv::find($where);
        foreach ($adv as $item) {
            $out[$item['sort']] = true;
        }
        return $out;
    }
    
    //V4广告统计
    public static function Advstat($appname,$version,$adv_logo,$osversion,$advid,$idfa,$openudid,$type,$linkid)
    {
		$dateline = strtotime(date("Y-m-d"));
		$tb = Statisticss::db()->where('appname','=',$appname)->where('version','=',$version)->where('adv_logo','=',$adv_logo)->where('addtime','=',$dateline);
		if($advid){
			$tb = $tb->where('aid','=',$advid);
		}else{
			$tb = $tb->where('type','=',$type)->where('link_id','=',$linkid);
		}
		$tb = $tb->where('idfa','=',$idfa);
		$adv = $tb->first();
		if($adv){
			Statisticss::db()->where('id','=',$adv['id'])->increment('number');
			return true;
		}else{		
			$data = array();
	    	$data['appname'] = $appname;
	    	$data['version'] = $version;
	    	$data['adv_logo'] = $adv_logo;

	    	$data['aid'] = $advid;
	    	$data['idfa'] = $idfa;
	    	$data['openudid'] = $openudid;
	    	$data['type'] = $type;
	    	$data['link_id'] = $linkid;
	    	$data['number'] = 1;
    	    $data['addtime'] = $dateline;
    	    Statisticss::db()->insertGetId($data);
    	    return true;
		}
	}
    
}
