<?php
use Yxd\Services\Cms\CommentService;
use Yxd\Services\ThreadService;
use Illuminate\Support\Facades\Response;
use Yxd\Services\AtmeService;
use Yxd\Services\Cms\GameService;
use Yxd\Modules\Activity\GiftbagService;
use Yxd\Services\UserFeedService;
use Yxd\Services\RelationService;
use Yxd\Services\TaskService;
use Illuminate\Support\Facades\Input;
use Yxd\Services\UserService;
use Yxd\Modules\System\SettingService;
use Yxd\Utility\ImageHelper;

use PHPImageWorkshop\ImageWorkshop;

class UserController extends BaseController
{	
	/**
	 * 获取用户信息
	 * 把取出的数据进行整理放入一个数组， 然后把数组传给一个函数返回给调用这个接口的程序进行显示
	 * UserService::joinImgUrl对图片路径进行修改，转换为绝对路径
	 * 获取用户的信息涉及到
	 */
	public function getInfo()
	{
		$ouid = Input::get('ouid',0);//他人信息
		$muid = Input::get('uid');//登录者
		$uid = $ouid ? $ouid : $muid;
		$user = UserService::getUserInfo($uid,'full');
		if(!$user || !isset($user['uid'])){
			return $this->fail('11211','用户不存在');
		}
		$out = array();
		$out['nick'] = $user['nickname'];
		$out['avatar'] = UserService::joinImgUrl($user['avatar']);
		$out['levelImg'] = UserService::joinImgUrl($user['level_icon']);
		$out['background'] = UserService::joinImgUrl($user['homebg']);
		/*
		 * array
			  'keyname' => string 'credit_rule' (length=11)
			  'data' => 
			    array
			      'rule_id' => int 931
		 */
		$config = SettingService::getConfig('credit_rule');
		$out['levelUrl'] = isset($config['data']['rule_id']) ? $config['data']['rule_id'] : 0;
		//$out['email'] = $user['email'];
		$out['gender'] = $user['sex'];
		$out['level'] = $user['level_name'];
		$out['phonenumber'] = $user['mobile'];
		$out['birthday'] = $user['birthday'] ? date('Y-m-d',$user['birthday']) : '';
		$out['money'] = $user['score'];
		//待完成任务数
	
		$out['tasknum'] = TaskService::getCanExecTaskCount($uid,Input::get('version','3.0.0'));
		//关注数	
		$follow = RelationService::getFollowList($uid);
			
		$out['attencount'] = $follow['total'];
		//粉丝数
		$follower = RelationService::getFollowerList($uid);
		$out['fanscount'] = $follower['total'];
		$out['signature'] = $user['summary'];
		//判断是否被关注
		$out['hasAtten'] = $ouid ? (int)RelationService::isFollow($muid, $ouid) : 0;
		return $this->success(array('result'=>$out));
	}
	/**file_exists($file) && is_readable($file)检查文件是否存在并且文件是否可读
	 * pathinfo() 函数以数组的形式返回文件路径的信息。
	 * print_r(pathinfo("/testweb/test.txt"));
		Array
		(
		[dirname] => /testweb
		[basename] => test.txt
		[extension] => txt
		)
		PATHINFO_DIRNAME - 只返回 dirname
		PATHINFO_BASENAME - 只返回 basename
		PATHINFO_EXTENSION - 只返回 extension
	 */
	public function getAvatar($uid)
	{
		$user = UserService::getUserInfo($uid);
		$file = storage_path() . ($user ? $user['avatar'] : '/userdirs/common/avatar@2x.png');
		$default = storage_path() . '/userdirs/common/avatar@2x.png';
		if(file_exists($file) && is_readable($file)){
			$ext = pathinfo($file,PATHINFO_EXTENSION);
			switch($ext){
				case 'png':
					$img = imagecreatefrompng($file);
					break;
				case 'jpg':
					$img = imagecreatefromjpeg($file);
					break;
				case 'gif':
					$img = imagecreatefromgif($file);
					break;
			}
						
		}else{
			$img = imagecreatefrompng($default);
		}

		return Response::stream(function()use($img){
				return imagepng($img);
			},
			200,
			array('Cache-Control'=>'max-age=10','Content-Type'=>'image/png',)
		);
	}
	
	
	
	/**
	 * 获取用户实时游币
	 */
	public function getMoney()
	{
		$uid = Input::get('uid');
		$money = UserService::getUserRealTimeCredit($uid,'score');
		return $this->success(array('result'=>array('awardValue'=>$money)));
	}
	
	/**
	 * 修改用户资料
	 */
	public function postEdit()
	{
		$msg = '';
		$uid = Input::get('uid');
		$input['nickname'] = Input::get('nick','');
		$input['summary'] = Input::get('signature');
		$input['mobile'] = Input::get('phonenumber');
		$input['sex'] = Input::get('gender');
		$input['birthday'] = Input::get('birthday');
		if($input['birthday']) {
			$input['birthday'] = strtotime($input['birthday']);			
		}
		
		if($input['nickname'] || $input['sex'] || $input['summary'] || $input['mobile'] || $input['birthday']){
			$msg = '资料修改成功';
		}
		//判断文件是否上传成功
	    if(Input::hasFile('avatar')){
	    	$config = array(
	    	    'savePath'=>'/userdirs/avatar/',
	    	    'driverConfig'=>array('autoSize'=>array(320,120,100,80,60,50))
	    	);
	    	$uploader = new ImageHelper($config);
	    	$avatar = $uploader->upload('avatar');
	    	if($avatar !== false){
	    		$input['avatar'] = $avatar['filepath'] . '/' . $avatar['filename'];
	    	}	    	
		}
		
	    if(Input::hasFile('background')){
		    $file = Input::file('background');
		    if($file->isValid()){
		    	$server_path = storage_path() . '/userdirs/homebg/';
		    	//$new_filename = $uid . '.jpg';
		    	$new_filename = date('YmdHis') . str_random(4);
		    	$file->move($server_path,$new_filename . '.png');
		    	$url = '/userdirs/homebg/' . $new_filename . '.png' . '?time=' . time();		    	
		    	$input['homebg'] = $url;		    			    	
		    }else{
		    	//return $this->fail(1121,'图片文件无效');
		    }
		    
		}		
		
		$success = UserService::updateUserInfo($uid,$input);
        if($success===-1){
		    return $this->fail(11211,'昵称已经存在');
		}else{
		    //return $this->fail('11211','没有修改');
		    
		    $reward_avatar = $reward_homebg = $reward_info = false;
		    $avatar_score  = $homebg_score  = $info_score = 0;
            if(isset($input['avatar']) && !empty($input['avatar'])) {
            	$score = TaskService::doUploadAvatar($uid);
            	is_numeric($score) && $avatar_score = $score;
            	is_numeric($score) && $reward_avatar = true;
            	is_numeric($score) && $msg = '上传头像成功 +'.$score.'游币';
            }
			if(isset($input['homebg']) && $input['homebg']) {
				$score = TaskService::doUploadHomebg($uid);
				is_numeric($score) && $homebg_score = $score;
				is_numeric($score) && $reward_homebg = true;
				is_numeric($score) && $msg = '上传背景成功 +'.$score.'游币';
			}
			$user = UserService::getUserInfo($uid);
			if($input['nickname'] && $input['nickname']!='玩家'.$uid && $input['sex']!=null && $input['summary'] && $input['mobile'] && $input['birthday']){
				$score = TaskService::doPerfectInfo($uid);
				is_numeric($score) && $info_score = $score;
				is_numeric($score) && $reward_info = true;
				is_numeric($score) && $msg = '完善资料成功 +'.$score.'游币';
			}
			if($reward_avatar && $reward_homebg){
				
				$msg = '头像上传成功,背景上传成功 游币+' . ($avatar_score+$homebg_score);
			}
			if($reward_avatar && $reward_info){
				$msg = '头像上传成功,完善资料成功 游币+' . ($avatar_score+$info_score);
			}
			if($reward_homebg && $reward_info){
				$msg = '背景上传成功,完善资料成功 游币+' . ($homebg_score+$info_score);
			}
			if($reward_avatar && $reward_homebg && $reward_info){
				$msg = '头像上传成功,背景上传成功,完善资料成功 游币+' . ($avatar_score+$homebg_score+$info_score);
			}	
							
			if($reward_avatar || $reward_homebg || $reward_info){
				return $this->fail(600,$msg);
			}
		    return $this->success(array('result'=>null,'errorMessage'=>$msg));
		}
	}		
	
	/**
	 * AT我
	 */
	public function atme()
	{
		$uid = Input::get('uid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pagesize',10);
		$out = array();
		$result = AtmeService::getAtmeList($uid,$page,$pagesize);
		foreach($result['result'] as $row){
			$data = unserialize($row['data']);
			switch($data['type']){				
				case 'yxd_forum_topic'://回帖
					$out[] = $this->atme_reply($data);
					break;
				case 'm_games'://游戏评论
					$out[] = $this->atme_game_comment($data);
					break;
				case 'm_news'://新闻评论
					$out[] = $this->atme_news_comment($data);
					break;
				case 'm_gonglue'://攻略
					$out[] = $this->atme_guide_comment($data);
					break;
				case 'm_feedback'://评测
					$out[] = $this->atme_opinion_comment($data);
					break;
				case 'm_game_notice'://新游评论
					$out[] = $this->atme_newgame_comment($data);
					break;
				case 'm_videos'://视频评论
					$out[] = $this->atme_video_comment($data);
					break;
			}
		}
				
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}

	protected function atme_topic($row)
	{
		$uid = Input::get('uid',0);
		$me = UserService::getUserInfo($uid);
		$atme = array();
		$atme['mid'] = $row['topic']['author_uid'];
		$author = UserService::getUserInfo($row['topic']['author_uid']);
		$atme['nick'] = $author['nickname'];
		$atme['avatar'] = AtmeService::joinImgUrl($author['avatar']);
		$atme['level'] = $author['level_name'];
		$atme['atPerson'] = $me['nickname'];
		$atme['type'] = AtmeService::ATME_TOPIC;//回复主题
		$atme['linkid'] = $row['topic']['tid'];		
		$atme['comContent'] = $row['topic']['subject'];
		$reply = json_decode($row['topic']['message'],true);
		$atme['reply'] = isset($reply['0']['text']) ? $reply['0']['text'] : '';
		$atme['comImg'] = AtmeService::joinImgUrl($row['game']['ico']);
		$atme['comTitle'] = $row['game']['shortgname'];
		$atme['addTime'] = date('Y-m-d H:i:s',$row['topic']['dateline']);
		return $atme;
	}
	
	protected function atme_reply($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		//$atme['cid']应该是评论信息的id
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_REPLY;//回复帖子
		//nID是文章帖子的id
		$atme['nID'] = $row['topic']['tid'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['topic']['subject'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['topic']['listpic']);
		$info['gameIcon'] = AtmeService::joinImgUrl($row['game']['ico']);
		$info['gameName'] = $row['game']['shortgname'];
		$info['replyTopic'] = 1;
		//悬赏积分
		$info['award'] = $row['topic']['award'];
		//分类标签ID
		$info['articleType'] = $row['topic']['cid'];
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $tmp[0]['text'] : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
	protected function atme_game_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_GAME;//回复游戏
		$atme['nID'] = $row['game']['id'];
				
		$info['isGame'] = 1;
		$info['topicTitle'] = '';
		$info['topicImage'] = '';
		$info['gameIcon'] = AtmeService::joinImgUrl($row['game']['ico']);
		$info['gameName'] = $row['game']['shortgname'];
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
	protected function atme_news_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_NEWS;//回复游戏
		$atme['nID'] = $row['news']['id'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['news']['title'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['news']['litpic']);
		$info['gameIcon'] = '';
		$info['gameName'] = '';
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
    protected function atme_guide_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_GUIDE;//回复游戏
		$atme['nID'] = $row['guide']['id'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['guide']['gtitle'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['guide']['litpic']);
		$info['gameIcon'] = '';
		$info['gameName'] = '';
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
    protected function atme_opinion_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_OPINION;//回复游戏
		$atme['nID'] = $row['opinion']['id'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['opinion']['ftitle'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['opinion']['litpic']);
		$info['gameIcon'] = '';
		$info['gameName'] = '';
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
    protected function atme_newgame_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_NEWGAME;//回复游戏
		$atme['nID'] = $row['newgame']['id'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['newgame']['title'] ? : $row['newgame']['gname'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['newgame']['litpic']);
		$info['gameIcon'] = '';
		$info['gameName'] = '';
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
    protected function atme_video_comment($row)
	{
		$uid = Input::get('uid',0);		
		$atme = array();
		$atme['mid'] = 0;
		//评论id
		$atme['cid'] = $row['comment']['id'];
		$atme['hasRead'] = 1;
		$atme['typeID'] = AtmeService::ATME_COMMENT_VIDEO;//回复游戏
		$atme['nID'] = $row['video']['id'];
				
		$info['isGame'] = 0;
		$info['topicTitle'] = $row['video']['vname'];
		$info['topicImage'] = AtmeService::joinImgUrl($row['video']['litpic']);
		$info['gameIcon'] = '';
		$info['gameName'] = '';
		$info['replyTopic'] = 0;
		$info['award'] = 0;
		$info['articleType'] = 0;
					    
	    if(isset($row['reply']) && $row['reply'] && isset($row['reply']['content'])){
			$tmp = json_decode($row['reply']['content'],true);
		    $info['toContent'] = isset($tmp[0]['text']) ? $this->replace_nickname($uid,$tmp[0]['text']) : '图片';
		    $content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}else{
			$info['toContent'] = '';
			$content = json_decode($row['comment']['content'],true);
		    $info['replyContent'] = isset($content[0]['text']) ? $this->replace_nickname($uid,$content[0]['text']) : '图片';
		    $info['replyImage'] = AtmeService::joinImgUrl($content[0]['img']);
		    $info['toImage'] = (isset($content[0]['text']) && $content[0]['text']) ? 1 : 0;
		    $info['replyDate'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		}
		
		$from = UserService::getUserInfo($row['comment']['uid']);
		$info['fromUser'] = array(
		    'userID'=>$from['uid'],
		    'userName'=>$from['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($from['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($from['level_icon']),
		    'signature'=>$from['summary']
		);
		$to = UserService::getUserInfo($uid);
		$info['toUser'] = array(
		    'userID'=>$to['uid'],
		    'userName'=>$to['nickname'],
		    'userAvator'=>AtmeService::joinImgUrl($to['avatar']),
		    'userLevel'=>$from['level_name'],
		    'userLevelImage'=>AtmeService::joinImgUrl($to['level_icon']),
		    'signature'=>$to['summary']
		);
		$atme['replyMessageInfo'] = $info;
		return $atme;
	}
	
	protected function replace_nickname($uid,$str)
	{
		if(!$uid) return $str;
		$user = UserService::getUserInfo($uid);
		if(isset($user['nickname']) && !empty($str)){
			$str = str_replace($user['nickname'],'我',$str);
		}
		return $str;
	}
	
	public function feeds()
	{
		$uid = Input::get('uid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$feeds = UserFeedService::getDataFeed($uid,$page,$pagesize);
		$out = array();
		foreach($feeds['result'] as $row){			
			$data = @unserialize($row['data']);
			if($data===false) continue;
			switch($data['type']){
				case 'topic'://发帖
					$feed = $this->feed_topic($data);
					$feed && $out[] = $feed; 
					break;
				case 'reply'://回帖
				case 'reply_topic'://回帖
					$feed = $this->feed_reply($data);
					$feed && $out[] = $feed;
					break;
				case 'game_comment'://游戏评论
					$feed = self::feed_game_comment($data);
					$feed && $out[] = $feed;
					break;
				case 'newgame_comment'://
					//$out[] = $this->feed_newgame_comment($data);
					break;
				case 'news_comment'://新闻评论
					$feed = $this->feed_news_comment($data);
					$feed && $out[] = $feed;
					break;
				case 'video_comment'://视频评论
					$feed = $this->feed_video_comment($data);
					$feed && $out[] = $feed;
					break;
				case 'activity'://参加活动
					$out[] = $this->feed_activity($data);
					break;
				case 'gift'://领取礼包
					$feed = $this->feed_gift($data);
					$feed && $out[] = $feed;
					break;
				case 'reserve'://预定礼包
					$out[] = $this->feed_reserve($data);
					break;
				case 'circle'://加入圈子
					$out[] = $this->feed_addcircle($data);
					break;
				default:
					break;
			}
		}
		return $this->success(array('result'=>$out,'totalCount'=>$feeds['total']));
	}
		
	//发帖动态
	protected function feed_topic($row)
	{
		//判断该帖子是否是最新的置顶的帖子，如果是
		$del = ThreadService::isDeleted($row['topic']['tid']);
		if($del==true) return false;
		$feed = array();
		$feed['type'] = 1;
		//$feed['linkid'] = $row['topic']['tid'];
		$feed['date'] = $this->formatDate($row['topic']['dateline']);		
		//$feed['content'] = $row['topic']['summary'];
		$feed['image'] = UserFeedService::joinImgUrl($row['topic']['listpic']);
		/*		
		$width = $height = 0;
		$file = storage_path() . $row['topic']['listpic'];
		if($row['topic']['listpic'] && file_exists($file) && is_readable($file)){
			list($width,$height,$type,$attr) = getimagesize(storage_path() . $row['topic']['listpic']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['dynamic'] = $row['topic']['subject'];
		$feed['title'] = $row['topic']['subject'];		
		$feed['tid'] = $row['topic']['tid'];
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['game']['ico']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		//$feed['commentCount'] = $row['topic']['replies'];
		return $feed;
	}
	//回帖动态
    protected function feed_reply($row)
	{
		$feed = array();
		$feed['type'] = 0;
		$feed['tid'] = $row['topic']['tid'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		//把json数据转换为数组的形式
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		//$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*		
		$width = $height = 0;
		$file = storage_path() . $cmt[0]['img'];
		if($cmt[0]['img'] && file_exists($file) && is_readable($file)){
			list($width,$height,$type,$attr) = getimagesize($file);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		//把图片的路径转换为绝对lujing
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['game']['ico']);
		$feed['title'] = $row['topic']['subject'];
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		return $feed;
	}
	//游戏评论
	protected function feed_game_comment($row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) return false;
		$feed = array();
		$feed['type'] = 2;
		$feed['state'] = 0;
		$feed['tid'] = $row['game']['id'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*
		$width = $height = 0;
		if(file_exists($cmt[0]['img'])){
			list($width,$height,$type,$attr) = getimagesize($cmt[0]['img']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['game']['ico']);
		$feed['title'] = $row['game']['shortgname'];
		$feed['content'] = $row['game']['typename'].'|'.$row['game']['language'];
		$feed['rating'] = $row['game']['score'];
		$feed['time'] = date('Y-m-d',$row['game']['updatetime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		$feed['commentcount'] = $row['game']['commenttimes'];
		return $feed;
	}
	
    //新游评论
	protected function feed_newgame_comment($row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) return false;
		$feed = array();
		$feed['type'] = 2;
		$feed['state'] = 5;
		$feed['tid'] = $row['notice']['id'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*
		$width = $height = 0;
		if(file_exists($cmt[0]['img'])){
			list($width,$height,$type,$attr) = getimagesize($cmt[0]['img']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['notice']['pic']);
		$feed['title'] = $row['notice']['gname'];	
		$feed['content'] = '';
		$feed['rating'] = '';
		$feed['time'] = date('Y-m-d',$row['notice']['addtime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();	
		$feed['commentcount'] = $row['notice']['commenttimes'];
		return $feed;
	}
	
    //加入游戏圈
	protected function feed_addcircle($row)
	{
		$feed = array();
		$feed['type'] = 3;
		$feed['date'] = $this->formatDate($row['joindate']);
		$names = array();
		foreach($row['games'] as $game){
			$circle = array();
			$circle['tid'] = $game['id'];
			$names[] = $game['shortgname'];
			$circle['gameicon'] = UserFeedService::joinImgUrl($game['ico']);
			$feed['gamearray'][] = $circle;
		}
		$count = count($names);
		$feed['atarray'] = array();
		//array_slice() 函数在数组中根据条件取出一段值，并返回。array_slice(array,offset,length,preserve)
		if($count>4){
		    $feed['dynamic'] = '"' . implode(',',array_slice($names,0,4)) . '"' . '等' . $count . '个游戏圈子';
		}else{
			$feed['dynamic'] = '"' . implode(',',array_slice($names,0,4)) . '"' . $count . '个游戏圈子';
		}
		return $feed;
	}
	
    //新闻评论
	protected function feed_news_comment($row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) return false;
		$feed = array();
		$feed['type'] = 2;
		$feed['state'] = 2;
		$feed['tid'] = $row['news']['id'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*
		$width = $height = 0;
		if(file_exists($cmt[0]['img'])){
			list($width,$height,$type,$attr) = getimagesize($cmt[0]['img']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['news']['litpic']);
		$feed['title'] = $row['news']['title'];
		$feed['content'] = '';
		$feed['rating'] = '';
		$feed['time'] = date('Y-m-d',$row['news']['addtime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();	
		$feed['commentcount'] = isset($row['news']['commenttimes']) ? $row['news']['commenttimes'] : '0';
		return $feed;
	}
    //视频评论
	protected function feed_video_comment($row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) return false;
		$feed = array();
		$feed['type'] = 2;
		$feed['state'] = 1;
		$feed['tid'] = $row['video']['id'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*
		$width = $height = 0;
		if(file_exists($cmt[0]['img'])){
			list($width,$height,$type,$attr) = getimagesize($cmt[0]['img']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['video']['litpic']);
		$feed['title'] = $row['video']['vname'];
		$feed['content'] = '';
		$feed['rating'] = '';
		$feed['time'] = date('Y-m-d',$row['video']['addtime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		$feed['commentcount'] = isset($row['video']['commenttimes']) ? $row['video']['commenttimes'] : '0';
		return $feed;
	}
   //评测评论
	protected function feed_opinion_comment($row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) return false;
		$feed = array();
		$feed['type'] = 2;
		$feed['state'] = 4;
		$feed['tid'] = $row['opinion']['id'];
		$feed['date'] = $this->formatDate($row['comment']['addtime']);
		$cmt = json_decode($row['comment']['content'],true);
		$feed['dynamic'] = $cmt[0]['text'];
		$feed['image'] = UserFeedService::joinImgUrl($cmt[0]['img']);
		/*
		$width = $height = 0;
		if(file_exists($cmt[0]['img'])){
			list($width,$height,$type,$attr) = getimagesize($cmt[0]['img']);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;
		*/
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['opinion']['litpic']);
		$feed['title'] = $row['opinion']['ftitle'];
		$feed['content'] = '';
		$feed['rating'] = '';
		$feed['time'] = date('Y-m-d',$row['opinion']['addtime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		$feed['commentcount'] = isset($row['opinion']['commenttimes']) ? $row['opinion']['commenttimes'] : '0';
		return $feed;
	}
	
    //礼包
	protected function feed_gift($row)
	{
		$feed = array();
		$feed['type'] = 5;
		$feed['tid'] = $row['gift_id'];
		$feed['date'] = $this->formatDate($row['addtime']);
		$gift = GiftbagService::getInfo($row['gift_id']);
		if(!$gift) return false;
		if($gift['game_id']){
		    $game = GameService::getGameInfo($gift['game_id']);
		}else{
			$game['shortgname'] = $gift['gname'];
			$game['ico'] = $gift['pic'];
		}
		$feed['gameicon'] = UserFeedService::joinImgUrl($game['ico']);
		$feed['dynamic'] = $gift['title'];
		$feed['title'] = $game['shortgname'];
		$feed['content'] = $gift['title'];
		$feed['state'] = $gift['endtime'] < time() ? '已过期' : '进行中';
		$feed['surplus'] = $gift['last_num'] .'/'. $gift['total_num'];
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		return $feed;
	}
	//预定礼包
	protected function feed_reserve($row)
	{
		$feed = array();
		$feed['type'] = 6;
		$feed['state'] = 0;
		$feed['tid'] = $row['game']['id'];		
		$feed['dynamic'] = $row['game']['shortgname'] . '礼包';
		
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['game']['ico']);
		$feed['title'] = $row['game']['shortgname'];
		$feed['content'] = $row['game']['typename'].'|'.$row['game']['language'];
		$feed['rating'] = $row['game']['score'];
		$feed['time'] = date('Y-m-d',$row['addtime'] ? :$row['game']['updatetime']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		$feed['date'] = date('Y-m-d',$row['addtime'] ? :$row['game']['updatetime']);
		$feed['commentcount'] = $row['game']['commenttimes'];
		
		return $feed;
	}
	
    //游戏活动
	protected function feed_activity($row)
	{
		$feed = array();
		$feed['type'] = 4;
		$feed['date'] = $this->formatDate($row['joindate']);
		$feed['gameicon'] = UserFeedService::joinImgUrl($row['activity']['listpic']);
		$feed['dynamic'] = $row['activity']['title'];
		$feed['title'] = $row['activity']['title'];
		$feed['content'] = '活动时间：' . date('Y-m-d',$row['activity']['startdate']) . '至' . date('Y-m-d',$row['activity']['enddate']);
		$feed['atarray'] = array();
		$feed['gamearray'] = array();
		return $feed;
	}
	
    protected function formatDate($time,$type='normal')
	{
		$current = (int)microtime(true);
		//$diff表示一个时间戳（其实就是秒数）
		$diff = $time - $current;
		//abs() 函数返回一年的那一天
		$diffDay = (int)date('z',$time) - (int)date('z',$current);
		//abs() 函数返回一个数的绝对值
		$diff = abs($diff);
		//if($type=='mohu'){
			if($diff<60){
				return $diff . '秒前';
			}elseif($diff<3600){
				return intval($diff/60) . '分钟前';
			}elseif($diff>=3600 && $diffDay ==0){
				return intval($diff/3600) . '小时前';
			}elseif($diffDay>0 && $diffDay<=30){
				return intval($diffDay) . '天前';
			}else{
				return date('Y-m-d',$time);
			}
		//}
	}
}