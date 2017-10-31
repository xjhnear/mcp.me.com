<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\ESports\ESportsService;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'yxvl_eSports';
    }

    public function getGameVideo()
    {
        $keyword = Input::get('keyword');
        $search = array('titleContain'=>$keyword);
        $res = ESportsService::excute($search,"GetIndexGameVideo",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        $data['search'] = $search;

        return $this->display('game-video-list',$data);
    }

    public function getAddGameVideo()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexGameVideo",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('game-video-add',$data);
    }

    public function postAddGameVideo()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $img2 = MyHelpLx::save_img($input['videoPic']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $input['videoPic'] =$img2 ? $img2:$input['img2'];unset($input['img2']);
        $res= ESportsService::excute($input,"SaveIndexGameVideo",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/game-video','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getGameZq()
    {
        $keyword = Input::get('keyword');
        $search = array('titleContain'=>$keyword);
        $res = ESportsService::excute($search,"GetIndexGameZQ",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
            foreach($data['datalist'] as &$item){
                $item['gameName'] = json_decode($item['gameName'],true);
            }
        }
        $data['search'] = $search;
        return $this->display('game-zq-list',$data);
    }

    public function getAddGameZq()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexGameZQ",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
                $data['data']['gameName'] = json_decode($data['data']['gameName'],true);
            }else{
                $data['data']['gameName'] = array();
            }
        }
        return $this->display('game-zq-add',$data);
    }

    public function postAddGameZq()
    {
        $input = Input::all();
        $game = array();
        $name = $input['gameName'];unset($input['gameName']);
        $url = $input['gameUrl'];unset($input['gameUrl']);
        foreach($name as $k=>$v){
            $game[] = array('name'=>$v,'url'=>$url[$k]);
        }
        $input['gameName'] = json_encode($game);
        $input['gameName'] = substr($input['gameName'],1,-1);
        $res= ESportsService::excute($input,"SaveIndexGameZQ",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/game-zq','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getHotDj()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexHotDJ",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('hot-dj-list',$data);
    }

    public function getAddHotDj()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexHotDJ",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('hot-dj-add',$data);
    }

    public function postAddHotDj()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $res= ESportsService::excute($input,"SaveIndexHotDJ",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/hot-dj','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getLeftHd()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexLeftHuanDeng",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('left-hd-list',$data);
    }

    public function getAddLeftHd()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexLeftHuanDeng",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('left-hd-add',$data);
    }

    public function postAddLeftHd()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $res= ESportsService::excute($input,"SaveIndexLeftHuanDeng",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/left-hd','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getRightSs()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexRightSaiShi",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('right-ss-list',$data);
    }

    public function getAddRightSs()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexRightSaiShi",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('right-ss-add',$data);
    }

    public function postAddRightSs()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $img2 = MyHelpLx::save_img($input['maxPicUrl']);
        $input['maxPicUrl'] =$img2 ? $img2:$input['img2'];unset($input['img2']);
        $res= ESportsService::excute($input,"SaveIndexRightSaiShi",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/right-ss','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getSsZd()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexSaiShiZhanDui",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('ss-zd-list',$data);
    }

    public function getAddSsZd()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexSaiShiZhanDui",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('ss-zd-add',$data);
    }

    public function postAddSsZd()
    {
        $input = Input::all();
        $res= ESportsService::excute($input,"SaveIndexSaiShiZhanDui",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/ss-zd','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getGgFooter()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexGuangGaoFooter",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('gg-footer-list',$data);
    }

    public function getAddGgFooter()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexGuangGaoFooter",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('gg-footer-add',$data);
    }

    public function postAddGgFooter()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['backPic']);
        $img2 = MyHelpLx::save_img($input['titlePic']);
        $input['backPic'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $input['titlePic'] =$img2 ? $img2:$input['img2'];unset($input['img2']);
        $res= ESportsService::excute($input,"SaveIndexGuangGaoFooter",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/gg-footer','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }

    public function getGgHeader()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetIndexGuangGaoHeader",true);
        if(isset($res['data'])&&$res['data']){
            $data['datalist'] = $res['data'];
        }
        return $this->display('gg-header-list',$data);
    }

    public function getAddGgHeader()
    {
        $data = array('catalogs'=>array());
        $idx = (int)Input::get('idx',"");
        if($idx){
            $idx--;
            $res = ESportsService::excute(array(),"GetIndexGuangGaoHeader",true);
            if(isset($res['data'])&&$res['data']){
                $data['data'] = $res['data'][$idx];
            }
        }
        return $this->display('gg-header-add',$data);
    }

    public function postAddGgHeader()
    {
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['backPic']);
        $input['backPic'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $res= ESportsService::excute($input,"SaveIndexGuangGaoHeader",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/gg-header','添加成功');
        }else{
            return $this->back($res['error']);
        }
    }


    public function getPageDesc()
    {
        $data = array();
        $res = ESportsService::excute(array(),"GetWebPageDesc",true);
        if(isset($res['data'][0])&&$res['data'][0]){
            $data['data'] = $res['data'][0];
        }
        return $this->display('page-desc-add',$data);
    }

    public function postAddPageDesc()
    {
        $input = Input::all();
        if($input['id']){
            $res= ESportsService::excute($input,"UpdateWebPageDesc",false);
        }else{
            unset($input['id']);
            $res= ESportsService::excute($input,"CreateWebPageDesc",false);
        }
        echo json_encode($res);
    }

    public function postAjaxDel()
    {
        $data = Input::all();
        $res = ESportsService::excute($data,"RemoveHome",false);
        echo json_encode($res);
    }
}