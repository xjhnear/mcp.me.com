<?php
/**
 * @package Youxiduo
 * @category Cms 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 年终总结留言服务模型
 */
final class EndYearSummary extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	/**
	 * 保存
	 * @param array $data
	 */
	public static function save($data){
		if(!empty($data['id'])){
			$id = $data['id'];
			unset($data['id']);
			return self::db()->where('id','=',$id)->update($data);
		}else{
			$data['addtime'] = time();
			return self::db()->insertGetId($data);
		}
	}
	/**
	 * 批量审核留言
	 * @param array $ids
	 * @param array $data
	 */
	public static function chickSave($ids,$data){
		return self::db()->whereIn('id',$ids)->update($data);
	}
	/**
	 * 查询列表
	 * @param number $page
	 * @param number $pagesize
	 */
	public static function getList($page=1,$pagesize=10,$audit=1,$keyword='',$keytype=''){
		$out =array();
		
		$tb = self::db();
		if($audit==1){
			$tb->where('audit','=',1);
		}elseif($audit==0){
			$tb->where('audit','=',0);
		}
		//判断是否按照游戏名称查询数据
		if($keytype != '' && $keyword != ''){
			switch ($keytype){
				case 'id':
					$tb =  $tb->where($keytype,'=',"$keyword");
					break;
				case 'nick':
				case 'content':
					$tb =  $tb->where($keytype,'like',"%{$keyword}%");
					break;
			}
			
		}

		
	
		$tb = $tb->orderBy('addtime','desc');
		$out['total'] = $tb->count();
		$out['result'] = $tb->forPage($page,$pagesize)->get();
		return $out;
	}
	
	/**
	 * 删除文章
	 */
	public static function del($id) {
		return self::db()->where('id', '=', $id)->delete();
	}

	
}