<?php
namespace modules\v4_my\controllers;

use Yxd\Modules\Core\BackendController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\V4\User\UserService;
use modules\v4_my\models\Image;
use modules\web_forum\controllers\TopicController;

class ImageController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_my';
    }

    /**游戏相册列表**/
    public function getList()
    {
        $keytype = Input::get('keytype', '');
        $keyword = Input::get('keyword', '');
        if ($keytype == 'nickname' && $keyword<>'') {
            $keyword = UserService::getUserIdByNickname($keyword);
        }
        $page = Input::get('page', 1);
        $pagesize = 70;
        $data = array();
        $result = Image::getList($page,$pagesize,$keyword);
        //var_dump($result);die();
        if ($result) {
            $pager = Paginator::make(array(), $result['totalCount'], $pagesize);
            $data['pagelinks'] = $pager->links();
            $data['datalist'] = $result['result'];
            foreach ($data['datalist'] as &$item) {
                $item['fileId'] = Config::get('app.v4my_img_url').$item['fileId'].'?150';
                $uinfo = UserService::getUserInfoByUid($item['uid']);
                $item['nickname'] = ($uinfo != 'user_not_exists')? $uinfo['nickname'] : "";
                unset($uinfo);
            }
        }
        return $this->display('image/image-list', $data);
    }
    
    //发放商品
    public function getDel($id=0)
    {
        $id=Input::get('id','');
        $uid=Input::get('uid','');
        if(empty($id)){
            return $this->redirect('v4my/image/list')->with('global_tips','参数丢失');
        }
        $result=Image::del($id);
        
        if($result['errorCode']==0)
        {
                  $input['type'] = '1';
                  $input['linkType'] = '1';
                  $input['uid'] =$uid;
                  $input['content'] ='' ;
                 TopicController::system_send($input); 
            
            return $this->redirect('v4my/image/list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4my/image/list')->with('global_tips','操作失败');
        }
    }

}