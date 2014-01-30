<?php
	
	class Activity_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('activity');
		} 

		function getActivitybyShortName($params){
			$activity = $this->mapper->load(array('activity_shortname=?', $params['activity_shortname']));
			return (!$activity?null:$activity);
		}

		function removeActivityUser($params){
			$mapper = $this->getMapper('user_has_activity');
			$user_has_activity = $mapper->load(array('user_id=? AND input_id=? AND date=? AND activity_id=?', $params['user_id'], $params['input_id'], $params['date'], $params['activity']->activity_id));
			if($user_has_activity!=false)	$user_has_activity->erase();

		}

		function addActivityUser($params){
			$this->mapper->user_id = $params['user_id'];
			$this->mapper->input_id = $params['input_id'];
			$this->mapper->date = $params['date'];
			$this->mapper->activity_id = $params['activity']->activity_id;
			$this->mapper->activity_input_id = $params['activity_input_id'];
			$this->mapper->duration = $params['duration'];
			$this->mapper->distance = $params['distance'];
			$this->mapper->calories = $params['calories'];
			$this->mapper->save();

		}
	}

?>