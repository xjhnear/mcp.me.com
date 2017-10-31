<?php
//
namespace Youxiduo\Topic;
use Youxiduo\Topic\Model\Gametype;
use Youxiduo\Topic\Model\Zt;
use Youxiduo\Topic\Model\ZtGames;
use Youxiduo\Topic\Model\Iosgame;
use Youxiduo\Topic\Model\Androidgame;
use Youxiduo\Base\BaseService;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Session;

class TopicService extends BaseService
{
	
	public static function getIosGameList($search=array())
	{
		return Iosgame::pagelist($search['page'],$search['pageSize'],Iosgame::setsearch($search));
	}

	public static function getaGameList($search=array())
	{
		return Androidgame::pagelist($search['page'],$search['pageSize'],Androidgame::setsearch($search));
	}

	public static function getGametypeInfo($id)
	{
		return Gametype::getInfo('Id',$id);
	}
	//专题列表数据获取
	public static function getTopicList($search=array())
	{	
		return Zt::pagelist($search['page'],$search['pageSize'],Zt::setsearch($search));
	}
	//专题数据详情获取
	public static function getTopicInfo($id,$key=''){
		return empty($key)?Zt::getInfo('Id',$id):Zt::getinfos($key,$id);
	}

	//专题数据详情获取
	public static function getTopicGameInfo($id,$key=''){
		return empty($key)?ZtGames::getInfo('Id',$id):ZtGames::getinfos($key,$id);
	}

	//查询专题表数据
	public static function FindTopic($where='')
	{	
		return Zt::find($where);
	}
	//删除zt
	public static function deleteTopic($id)
	{	
		return Zt::delete('id','=',$id);
	}

	//删除zt_GAME
	public static function deleteTopicGame($id,$key)
	{	
		return ZtGames::delete($key,'=',$id);
	}
	//zt数据添加修改
	public static function TopicAddEdit($datainfo=array())
	{	
		$session=Session::get('youxiduo_admin');
		$datainfo['editor']=$session['id'];
		
		if(empty($datainfo['id'])){
			$datainfo['addtime']=time();
            return Zt::insert($datainfo);
        }
        $datainfo['updatetime']=time();
        return Zt::update($datainfo,$datainfo['id']);
	}

	//ztGame数据添加修改
	public static function TopicGameAddEdit($datainfo=array())
	{	
		return ZtGames::insert($datainfo);
    }

	/**处理返回数据**/
    public static function _processingInterface($result,$data,$pagesize=10,$is_ajax=0){
        $data['search']=$data;
        $data['datalist']  = !empty($result['result'])?$result['result']:array();
        $data['totalCount']=$result['totalCount'];
        if($is_ajax == 1){
            return $data;
        } 
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['search']['pageIndex']);
        $pager->appends($data['search']);
        $data['pagelinks'] = $pager->links();
        return $data;
    }

}