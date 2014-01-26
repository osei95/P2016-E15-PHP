<?php
	
	class User_model{

		function _construct(){

		}

		function getUserByInputId($f3, $params){
			$users = $f3->get('dB')->exec(
			    'SELECT user.user_id, user.user_username, user.user_email, user.user_gender, user.user_key FROM user LEFT JOIN user_has_input ON user.user_id=user_has_input.user_id LEFT JOIN input ON user_has_input.input_id=input.input_id WHERE user_has_input.user_has_input_id=:input_id AND input.input_shortname=:input_name',
			    array(':input_id'=>$params['input_id'], ':input_name'=>$params['input_name'])
			);
			return (!is_array($users)?null:(count($users)>0)?$users[0]:null);
		}
	}
?>