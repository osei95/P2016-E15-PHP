<?php
	
	class Goal_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('goal');
		} 

		function addGoalUser($params){
			$this->mapper->goal_from = $params['from'];
			$this->mapper->goal_to = $params['to'];
			$this->mapper->goal_unit = $params['unit'];
			$this->mapper->goal_value = $params['value'];
			$this->mapper->goal_deadline = $params['deadline'];
			$this->mapper->goal_date = $params['date'];
			$this->mapper->goal_accepted = $params['accepted'];
			$this->mapper->save();
			return $this->mapper;
		}

		function updateGoal($params){
			$goal = $this->mapper->load(array('goal_id=?', $params['id']));
			if(isset($params['from']))	$goal->goal_from=$params['from'];
			if(isset($params['to']))	$goal->goal_to=$params['to'];
			if(isset($params['unit']))	$goal->goal_unit=$params['unit'];
			if(isset($params['value']))	$goal->goal_value=$params['value'];
			if(isset($params['date']))	$goal->goal_date=$params['date'];
			if(isset($params['deadline']))	$goal->goal_deadline=$params['deadline'];
			if(isset($params['accepted']))	$goal->goal_accepted=$params['accepted'];
			if(isset($params['achievement']))	$goal->goal_achievement=$params['achievement'];
			$goal->save();
		}

		function getAllGoalsByUserFromId($params){
			return $this->mapper->find(array('goal_from=?', $params['user_id']));
		}

		function getAllGoalsByUserToId($params){
			return $this->mapper->find(array('goal_to=?', $params['user_id']));
		}

		function getAllUnachievedGoals($params){
			return $this->mapper->find(array('goal_achievement<100 AND goal_accepted=1'));
		}
	}

?>