<?php

	class Auth_controller{

		function _construct(){}

		function fitbit_auth($f3){
			$this->oauth_1_0_auth($f3, 'FITBIT');
		}

		function jawbone_auth($f3){
			$this->oauth_2_0_auth($f3, 'JAWBONE');
		}

		function moves_auth($f3){
			$this->oauth_2_0_auth($f3, 'MOVES');
		}

		function runkeeper_auth($f3){
			$this->oauth_2_0_auth($f3, 'RUNKEEPER');	
		}

		function oauth_1_0_auth($f3,$apiName){

			$vars = $f3->get($apiName);

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			$oauth = new OAuth($vars['conskey'], $vars['conssec'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

			if(!isset($params['oauth_token']) || !$f3->exists('SESSION.temp_secret_token')){

				$request_token_info = $oauth->getRequestToken($vars['req_url'], $vars['callbackUrl']);
			    $f3->set('SESSION.temp_secret_token', $request_token_info['oauth_token_secret']);
			    $f3->reroute($vars['authurl'].'?oauth_token='.$request_token_info['oauth_token']);

			}else{

				$oauth->setToken($params['oauth_token'], $f3->get('SESSION.temp_secret_token'));
				$f3->clear('SESSION.temp_secret_token');
				$auth_response = $oauth->getAccessToken($vars['acc_url']);

				var_dump($auth_response);

			}
		}

		function oauth_2_0_auth($f3,$apiName){

			$vars = $f3->get($apiName);

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			if(!isset($params['code'])){

				$rerouteParams = array(
				    "response_type" => "code",
				    "client_id" => $vars['clientid'],
				    "redirect_uri" => $vars['callbackUrl'],
				    "scope" => $vars['scope']
				);
				$f3->reroute($vars['authurl'].'?'.http_build_query($rerouteParams));

			}else{

			  	$params = array(
			        "code" => $params['code'],
			        "client_id" => $vars['clientid'],
			        "client_secret" => $vars['clientsecret'],
			        "redirect_uri" => $vars['callbackUrl'],
			        "grant_type" => $vars['grantType']
			    );

				$ch = curl_init($vars['token_url']);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$auth_response = json_decode(curl_exec($ch),true);
				curl_close($ch);

				var_dump($auth_response);
			}
		}
		
	}

?>