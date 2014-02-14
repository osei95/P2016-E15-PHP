<?php
	
	class Activity_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('activity');
		} 

		function getActivityByShortName($params){
			return $activity = $this->mapper->load(array('activity_shortname=?', $params['activity_shortname']));
		}

		function getActivityUserByDate($params){
			$mapper=$this->getMapper('user_has_activity');
			return $activity =$mapper->load(array('user_id=? AND date=?', $params['user_id'], $params['date']));
		}

		function removeActivityUser($params){
			$mapper = $this->getMapper('user_has_activity');
			$user_has_activity = $mapper->load(array('user_id=? AND input_id=? AND date=? AND activity_id=?', $params['user_id'], $params['input_id'], $params['date'], $params['activity']->activity_id));
			if($user_has_activity)	$user_has_activity->erase();

		}

		function addActivityUser($params){
			$mapper = $this->getMapper('user_has_activity');
			$mapper->user_id = $params['user_id'];
			$mapper->input_id = $params['input_id'];
			$mapper->date = $params['date'];
			$mapper->activity_id = $params['activity_id'];
			$mapper->activity_input_id = $params['activity_input_id'];
			$mapper->duration = $params['duration'];
			$mapper->distance = $params['distance'];
			$mapper->calories = $params['calories'];
			$mapper->save();
		}
	}

?>