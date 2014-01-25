<?php

	class Auth_controller{

		function _construct(){

		}

		function fitbit_auth($f3){

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			$oauth = new OAuth($f3->get('FITBIT.conskey'), $f3->get('FITBIT.conssec'), OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

			if(!$f3->exists('SESSION.oauth_token') || !$f3->exists('SESSION.oauth_token_secret')){
				if(!isset($params['oauth_token']) || !$f3->exists('SESSION.oauth_token_secret')){

					$request_token_info = $oauth->getRequestToken($f3->get('FITBIT.req_url'), $f3->get('FITBIT.callbackUrl'));
				    $f3->set('SESSION.oauth_token_secret', $request_token_info['oauth_token_secret']);
				    $f3->reroute($f3->get('FITBIT.authurl').'?oauth_token='.$request_token_info['oauth_token']);

				}else{

					$oauth->setToken($params['oauth_token'], $f3->get('SESSION.oauth_token_secret'));
					$access_token_info = $oauth->getAccessToken($f3->get('FITBIT.acc_url'));

					$f3->set('SESSION.oauth_token', $access_token_info['oauth_token']);
					$f3->set('SESSION.oauth_token_secret', $access_token_info['oauth_token_secret']);

				}
			}

			// Test de l'API
			if($f3->exists('SESSION.oauth_token') && $f3->exists('SESSION.oauth_token_secret')){

				$url = 'http://api.fitbit.com/1/user/-/profile.json';

				$oauth->setToken($f3->get('SESSION.oauth_token'),$f3->get('SESSION.oauth_token_secret'));
				$oauth->fetch($url);
			   	$response = $oauth->getLastResponse();

			   	print_r($response);
			}
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