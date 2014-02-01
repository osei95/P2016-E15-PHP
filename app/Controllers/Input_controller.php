<?php

	class Input_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		}

		function renewToken($f3, $params){
			$oauth_controller = new Oauth_controller();
			$vars = $f3->get($params['input_name']);
			$vars['grantType'] = 'refresh_token';
			$vars['refresh_token'] = $params['refresh_token'];
			$auth_response = $oauth_controller->oauth_2_0_refresh_token($f3, $vars);
			if(isset($auth_response['access_token']) && isset($auth_response['refresh_token'])){
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $params['user_has_input_id'], 'oauth' => $auth_response['access_token'], 'oauth_refresh_token' => $auth_response['refresh_token']));
				return array('refresh_token' => $auth_response['refresh_token'], 'access_token' => $auth_response['access_token'], 'access_secret_token' => (isset($auth_response['access_secret_token'])?$auth_response['access_secret_token']:''));
			}else{
				return false;
			}
		}

	}

?>