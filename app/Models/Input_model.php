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
			if($input!=null){
				$this->mapper->user_id = $params['user_id'];
				$this->mapper->input_id = $input->input_id;
				$this->mapper->user_has_input_id = $params['input_key'];
				$this->mapper->user_has_input_oauth = $params['oauth'];
				$this->mapper->user_has_input_oauth_secret = $params['oauth_secret'];
				$this->mapper->save();
			}
		}

		function updateOauth($params){
			$user_has_input = $this->mapper->load(array('user_has_input_id=?', $params['user_has_input_id']));
			$user_has_input->user_has_input_oauth = $params['oauth'];
			if(isset($params['oauth_secret']))	
				$user_has_input->user_has_input_oauth_secret = $params['oauth_secret'];
			$user_has_input->save();
		}

		function getInputByName($params){
			$mapper = $this->mapper=$this->getMapper('input');
			$input = $mapper->load(array('input_shortname=?', $params['input_shortname']));
			return (!$input?null:$input);
		}

		function getInputByUserId($params){
			$mapper = $this->getMapper('user_input_list');
			$inputs = $mapper->find(array('user_id=?', $params['user_id']));
			return (!is_array($inputs)?null:$inputs);
		}
	}

?>