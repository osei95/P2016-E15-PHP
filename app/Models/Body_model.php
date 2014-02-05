<?php
	
	class Body_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('body');
		} 

		function addBodyUser($params){
			$mapper = $this->getMapper('body');
			$mapper->body_user_id = $params['user_id'];
			$mapper->body_date = $params['date'];
			if(isset($params['weight']))	$mapper->body_weight = $params['weight'];
			if(isset($params['height']))	$mapper->body_height = $params['height'];
			$mapper->save();
		}
	}

?>