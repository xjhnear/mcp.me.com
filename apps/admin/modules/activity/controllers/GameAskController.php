<?php
namespace modules\activity\controllers;
use modules\activity\models\GameAskModel;
use modules\activity\models\AskQuestionModel;
use modules\activity\models\PrizeModel;
use Illuminate\Support\Facades\Validator;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;

class GameAskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
			
    public function getPrize($id)
	{
		$data = array();
		$ask = GameAskModel::getInfo($id);
		if($ask){
			$reward = json_decode($ask['reward'],true);
			if($reward){
				$prize_ids = array();
				foreach($reward as $row){
					$prize_ids[] = $row['prize_id'];
				}
				$prizes = PrizeModel::getListByIds($prize_ids);
				foreach($reward as $key=>$row){
					$row['prize_name'] = isset($prizes[$row['prize_id']]) ? $prizes[$row['prize_id']]['name'] : '';
					$reward[$key] = $row;
				}
			}
			$ask['prize'] = $reward;			
		}
		$data['id'] = $id;
		$data['ask'] = $ask;
		return $this->display('askpager-info',$data);
	}    
	
	public function postSavePrize()
	{
		$id = Input::get('id');		
		$prize_1_id = Input::get('prize_id_1');
		$prize_1_num = (int)Input::get('num_1');
		$prize_2_id = Input::get('prize_id_2');
		$prize_2_num = (int)Input::get('num_2');
		$prize_3_id = Input::get('prize_id_3');
		$prize_3_num = (int)Input::get('num_3');
		$reward = array(
		    'prize_1'=>array('prize_id'=>$prize_1_id,'num'=>$prize_1_num),
		    'prize_2'=>array('prize_id'=>$prize_2_id,'num'=>$prize_2_num),
		    'prize_3'=>array('prize_id'=>$prize_3_id,'num'=>$prize_3_num),
		);
		
		$rule = array(
		    'prize_1'=>'required',		    
		    'prize_2'=>'required',
		    'prize_3'=>'required',
		);
		$input = array(
		    'prize_1'=>$prize_1_id,
		    'prize_2'=>$prize_2_id,
		    'prize_3'=>$prize_3_id,
		);
		$validator = Validator::make($input,$rule);
		if($validator->fails()){
			return $this->back()->with('global_tips','奖品不能为空');
		}
		
		$ask = array(
		    'id'=>$id,
		    'reward'=>json_encode($reward)		    
		);		
		$ret = GameAskModel::save($ask);
		if($ret){
			return $this->redirect('activity/gameask/prize/' . $id);
		}
		return $this->redirect('activity/gameask/prize/' . $id);
	}
	
	public function getQuestionList($ask_id)
	{
		$data = array();
		$data['ask_id'] = $ask_id;
		$data['questions'] = AskQuestionModel::getList($ask_id);
		return $this->display('question-list',$data);
	}
	
	public function getAddQuestion($ask_id)
	{
		//$ask = GameAskModel::getInfo($ask_id);
		$data = array();
		$question = array('ask_id'=>$ask_id);
		$data['question'] = $question;
		return $this->display('question-info',$data);
	}
	
	public function getEditQuestion($id)
	{
		$data = array();
		$data['question'] = AskQuestionModel::getInfo($id);
		return $this->display('question-info',$data);
	}
	
	public function postSaveQuestion()
	{
		$id = Input::get('id');
		$ask_id = Input::get('ask_id');
		$title = Input::get('title');;
		$options = Input::only('option_a','option_b','option_c','option_d');
		$answer = Input::get('answer');
		$status = Input::get('status',0);
		$sort = Input::get('sort',50);
		
		$question = array(
		    'id'=>$id,
		    'ask_id'=>$ask_id,
		    'title'=>$title,
		    'options'=>json_encode($options),
		    'answer'=>$answer,
		    'sort'=>$sort,
		    'status'=>$status
		);
		
	    $id = AskQuestionModel::save($question);
		if($id){
			return $this->redirect('activity/gameask/question-list/' . $ask_id);
		}
	}
	
	public function getFullMark($ask_id)
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$result = AskQuestionModel::searchFullMark($ask_id,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['datalist'] = $result['result'];
		$data['pagelinks'] = $pager->links();
		return $this->display('fullmark-list',$data);
	}
	
	public function getReport($ask_id)
	{
		$result = GameAskModel::getAllAskResult($ask_id);
		$asks = AskQuestionModel::getList($ask_id);
		$out = array();
		$datalist = array();
		foreach($asks as $row){
			$out[$row['id']] = array('A'=>0,'B'=>0,'C'=>0,'D'=>0);
			$datalist[] = array('id'=>$row['id'],'title'=>$row['title']);			
		}
		foreach($result as $row){
			$data = json_decode($row['answers'],true);
			foreach($out as $key=>$val){
				if(isset($data[$key])){
					isset($out[$key][$data[$key]]) && $out[$key][$data[$key]]++;
				}
			}
		}
		foreach($out as $key=>$row){
			$html = '';
			$html .= '<span class="badge badge-warning">'.'A:'.'</span><span class="badge badge-important">'.$row['A'].'</span>';
			$html .= '<span class="badge badge-warning">'.'B:'.'</span><span class="badge badge-important">'.$row['B'].'</span>';
			$html .= '<span class="badge badge-warning">'.'C:'.'</span><span class="badge badge-important">'.$row['C'].'</span>';
			$html .= '<span class="badge badge-warning">'.'D:'.'</span><span class="badge badge-important">'.$row['D'].'</span>';
			$out[$key]['result'] = $html;
		}
		foreach($datalist as $key=>$row){
			$row['result'] = $out[$row['id']]['result'];
			$datalist[$key] = $row;
		}		
		$data['datalist'] = $asks;
		$data['result'] = $out;
		return $this->display('ask-report',$data);
	}
}