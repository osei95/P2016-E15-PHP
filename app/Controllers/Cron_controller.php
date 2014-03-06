<?php

	class Cron_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		}

		function importActivityUsers($f3){
			$input_model = new Input_model();
			$inputs = $input_model->getInputs(array('user_fake'=>0));
			if(is_array($inputs)){
				foreach($inputs as $input){
					$input_controller = new Input_controller();
					if($f3->get($input['input_shortname'])['oauth']==2){
						$auth_response = $input_controller->renewToken($f3, array('input_name' => $input['input_shortname'], 'user_has_input_id' => $input['user_input_id'], 'refresh_token' => $input['user_input_oauth_refresh_token']));
					}else{
						$auth_response = array('access_token' => $input['user_input_oauth'], 'access_secret_token' => $input['user_input_oauth_secret']);
					}
					$input_api_controller = $input_controller->getInputAPIController($f3, array('input_shortname' => $input['input_shortname']));
					//$input_api_controller->importBody($f3, array('user_id' => $input['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'access_token' => $auth_response['access_token'], 'access_token_secret' => $auth_response['access_secret_token']));
 					$input_api_controller->importActivity($f3, array('user_id' => $input['user_id'], 'user_firstname' => $input['user_firstname'], 'user_lastname' => $input['user_lastname'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'date' => 'all', 'access_token' => $auth_response['access_token'], 'access_token_secret' => $auth_response['access_secret_token']));
				}
			}
		}

		function importBodyUsers($f3){
			$input_model = new Input_model();
			$inputs = $input_model->getInputs(array('user_fake'=>0));
			if(is_array($inputs)){
				foreach($inputs as $input){
					if($input['input_shortname']=='FITBIT' || $input['input_shortname']=='RUNKEEPER' || $input['input_shortname']=='JAWBONE'){
						$input_controller = new Input_controller();
						if($f3->get($input['input_shortname'])['refresh_token']=='true'){
							$auth_response = $input_controller->renewToken($f3, array('input_name' => $input['input_shortname'], 'user_has_input_id' => $input['user_input_id'], 'refresh_token' => $input['user_input_oauth_refresh_token']));
						}else{
							$auth_response = array('access_token' => $input['user_input_oauth'], 'access_secret_token' => $input['user_input_oauth_secret']);
						}
						$input_api_controller = $input_controller->getInputAPIController($f3, array('input_shortname' => $input['input_shortname']));
						$input_api_controller->importBody($f3, array('user_id' => $input['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'access_token' => $auth_response['access_token'], 'access_token_secret' => $auth_response['access_secret_token']));
					}
				}
			}
		}

		function updateGoals($f3){
			$goal_model = new Goal_model();
			$activity_model = new Activity_model();
			$goals = $goal_model->getAllUnachievedGoals(array());
			if(is_array($goals)){
				foreach($goals as $goal){
					if($goal->goal_unit=='distance'){
						$distance = $activity_model->getSumDistanceUser(array('user_id'=>$goal->goal_to, 'date_min'=>$goal->goal_date, 'date_max'=>$goal->goal_deadline));
						$current_distance = (!is_array($distance) || !isset($distance[0]['distance'])?0:$distance[0]['distance']);
						$new_achievement = (100*$current_distance)/$goal->goal_value;
						$goal_model->updateGoal(array('id'=>$goal->goal_id, 'achievement'=>$new_achievement));
					}
				}
			}
		}

		public function afterroute($f3){
         	exit;
      	}

	}

?>