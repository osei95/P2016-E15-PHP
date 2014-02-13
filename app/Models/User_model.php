<?php
	
	class User_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user');
		}

		function getUserByInputId($params){
			$mapper = $this->getMapper('user_infos');
			return $mapper->load(array('user_input_id=? AND input_shortname=?', $params['input_id'], $params['input_name']));
		}

		function getUserByUsername($params){
			$user = $this->mapper->load(array('user_username=?', $params['username']));
			return (!$user?null:$user);
		}

		function getUserByEmail($params){
			return $this->mapper->load(array('user_email=?', $params['email']));
		}

		function createUser($params){
			$key = uniqid();
			$this->mapper->user_username = $params['username'];
			$this->mapper->user_password = $params['password'];
			$this->mapper->user_email = $params['email'];
			$this->mapper->user_gender = $params['gender'];
			$this->mapper->user_description = $params['description'];
			$this->mapper->user_firstname = $params['firstname'];
			$this->mapper->user_lastname = $params['lastname'];
			$this->mapper->user_city = $params['city'];
			$this->mapper->user_postcode = $params['postcode'];
			$this->mapper->user_birthday = $params['birthday'];
			$this->mapper->user_sport = $params['sport'];
			$this->mapper->user_appearance = $params['appearance'];
			$this->mapper->user_key = $key;
			$this->mapper->save();
			return $this->mapper;
		}
	}
?>