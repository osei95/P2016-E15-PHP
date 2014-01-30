<?php
	
	class User_model{

		function _construct(){

		}

		function getUserByInputId($f3, $params){
			$users = $f3->get('dB')->exec(
			    'SELECT user.user_id, user.user_username, user.user_firstname, user.user_lastname, user.user_email, user.user_gender, user.user_key, user.user_description FROM user LEFT JOIN user_has_input ON user.user_id=user_has_input.user_id LEFT JOIN input ON user_has_input.input_id=input.input_id WHERE user_has_input.user_has_input_id=:input_id AND input.input_shortname=:input_name',
			    array(':input_id'=>$params['input_id'], ':input_name'=>$params['input_name'])
			);
			return (!is_array($users)?null:(count($users)>0)?$users[0]:null);
		}

		function getUserByUsername($f3, $params){
			$mapper = new DB\SQL\Mapper($f3->get('dB'),'user');
			$user = $mapper->load(array('user_username=?', $params['username']));
			return (!$user?null:$user);
		}

		function getUserByEmail($f3, $params){
			$mapper = new DB\SQL\Mapper($f3->get('dB'),'user');
			$user = $mapper->load(array('user_email=?', $params['email']));
			return (!$user?null:$user);
		}

		function newUser($f3, $params){
			$key = uniqid();
			$user = new DB\SQL\Mapper($f3->get('dB'), 'user');
			$user->user_username = $params['username'];
			$user->user_password = $params['password'];
			$user->user_email = $params['email'];
			$user->user_gender = $params['gender'];
			$user->user_description = $params['description'];
			$user->user_firstname = $params['firstname'];
			$user->user_lastname = $params['lastname'];
			$user->user_key = $key;
			$user->save();
			return $user;
		}
	}
?>