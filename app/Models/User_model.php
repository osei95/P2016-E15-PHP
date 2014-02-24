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
			$key=uniqid();
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


		/* Followers */

		function follow($params){
			$mapper=$this->getMapper('following');
			$mapper->following_from = $params['from'];
			$mapper->following_to = $params['to'];
			$mapper->save();
		}

		function unfollow($params){
			$mapper=$this->getMapper('following');
			$follow=$mapper->load(array('following_from=? AND following_to=?', $params['from'], $params['to']));
			if($follow)	$follow->erase();
		}

		function isFollow($params){
			$mapper=$this->getMapper('following');
			return $mapper->load(array('following_from=? AND following_to=?', $params['from'], $params['to']));
		}

		/* Goals */

		function getAllGoalsToByUserId($params){
			$mapper=$this->getMapper('goal_infos');
			return $mapper->find(array('user_to_id=?', $params['id']));
		}

		/* Goals */

		function getAllRelationsByUserId($params){
			 return $this->dB->exec("SELECT `user`.`user_id`, `user`.`user_username`, `user`.`user_firstname`, `user`.`user_lastname` FROM `following` `following_from` INNER JOIN `user` ON `following_from`.`following_to` = `user`.`user_id` WHERE EXISTS( SELECT `following_to`.`following_from`, `following_to`.`following_to` FROM `following` `following_to` WHERE `following_from`.`following_from` = `following_to`.`following_to` AND `following_to`.`following_from` = `following_from`.`following_to`) AND `following_from`.`following_from`=".intval($params['id']));
		}

		function getElementsByDatas($params){
			$tableDatas = array();
			$mapper = $this->getMapper('user_has_activity');
			return $mapper->find(array('user_id=?', $params['username']), array('order'=>'date DESC','limit'=>0,15));
		}
	}
?>