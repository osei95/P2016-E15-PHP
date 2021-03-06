<?php
	
	class Body_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('body');
		} 

		function addBodyUser($params){
			$this->mapper->user_id = $params['user_id'];
			$this->mapper->body_date = $params['date'];
			if(isset($params['weight']))	$this->mapper->body_weight = $params['weight'];
			if(isset($params['height']))	$this->mapper->body_height = $params['height'];
			$this->mapper->save();
		}

		function getAllAppareances($params){
			$mapper=$this->getMapper('appearance');
			return $mapper->find(array());
		}

		function getAllTemperaments($params){
			$mapper=$this->getMapper('temperament');
			return $mapper->find(array());
		}

		function getAllSports($params){
			$mapper=$this->getMapper('sport');
			return $mapper->find(array());
		}
	}

?>