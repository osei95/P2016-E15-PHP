<?php

	class Cron_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		}

		function importActivityUsers($f3){
			$input_model = new Input_model();
			$inputs = $input_model->getInputs();
			if(is_array($inputs)){
				foreach($inputs as $input){
					if($f3->get($input['input_shortname'])['oauth']==2){
						$input_controller = new Input_controller();
						$auth_response = $input_controller->renewToken($f3, array('input_name' => $input['input_shortname'], 'user_has_input_id' => $input['user_input_id'], 'refresh_token' => $input['user_input_oauth_refresh_token']));
					}else{
						$auth_response = array('access_token' => $input['user_input_oauth'], 'access_secret_token' => $input['user_input_oauth_secret']);
					}
					$activity_controller = new Activity_controller();
					$activity_controller->importActivity($f3, array('user_id' => $input['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'access_token' => $auth_response['access_token'], 'access_token_secret' => $auth_response['access_secret_token']));
				}
			}
		}

		public function afterroute($f3){
         	exit;
      	}

	}

?>