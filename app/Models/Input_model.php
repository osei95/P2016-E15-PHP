<?php
	
	class Input_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user_has_input');
		} 

		function createInput($params){
			$input = $this->getInputByName(array('input_shortname' => $params['input_name']));
			if($input){
				$this->mapper->user_id = $params['user_id'];
				$this->mapper->input_id = $input->input_id;
				$this->mapper->user_has_input_id = $params['input_key'];
				$this->mapper->user_has_input_oauth = $params['oauth'];
				$this->mapper->user_has_input_oauth_secret = $params['oauth_secret'];
				$this->mapper->user_has_input_refresh_token = $params['oauth_refresh_token'];
				$this->mapper->save();
			}
			return $this->mapper;
		}

		function updateOauth($params){
			$user_has_input = $this->mapper->load(array('user_has_input_id=?', $params['user_has_input_id']));
			$user_has_input->user_has_input_oauth = $params['oauth'];
			if(isset($params['oauth_secret']))	
				$user_has_input->user_has_input_oauth_secret = $params['oauth_secret'];
			if(isset($params['oauth_refresh_token']))	
				$user_has_input->user_has_input_refresh_token = $params['oauth_refresh_token'];
			$user_has_input->save();
		}

		function getInputs(){
			$mapper = $this->getMapper('user_input_list');
			return $mapper->find();
		}

		function getInputByName($params){
			$mapper =$this->getMapper('input');
			return $input = $mapper->load(array('input_shortname=?', $params['input_shortname']));
		}

		function getInputByUserId($params){
			$mapper = $this->getMapper('user_input_list');
			return $mapper->load(array('user_id=?', $params['user_id']));
		}
	}

?>