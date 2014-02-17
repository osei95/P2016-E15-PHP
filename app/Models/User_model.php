<?php
	
	class User_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user_infos');
		}

		function getUserById($params){
			return $this->mapper->load(array('user_id=?', $params['id']));
		}

		function getUserByInputId($params){
			return $this->mapper->load(array('user_input_id=? AND input_shortname=?', $params['input_id'], $params['input_name']));
		}

		function getUserByUsername($params){
			return $this->mapper->load(array('user_username=?', $params['username']));
		}

		function getUserByEmail($params){
			return $this->mapper->load(array('user_email=?', $params['email']));
		}

		function createUser($params){
			$mapper=$this->getMapper('user');
			$key = uniqid();
			$mapper->user_username = $params['username'];
			$mapper->user_password = $params['password'];
			$mapper->user_email = $params['email'];
			$mapper->user_gender = $params['gender'];
			$mapper->user_description = $params['description'];
			$mapper->user_firstname = $params['firstname'];
			$mapper->user_lastname = $params['lastname'];
			$mapper->user_city = $params['city'];
			$mapper->user_postcode = $params['postcode'];
			$mapper->user_birthday = $params['birthday'];
			$mapper->user_sport = $params['sport'];
			$mapper->user_appearance = $params['appearance'];
			$mapper->user_key = $key;
			$mapper->save();
			return $mapper;
		}
	}
?>