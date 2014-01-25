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

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			if(!isset($params['code'])){

				$rerouteParams = array(
				    "response_type" => "code",
				    "client_id" => $f3->get('JAWBONE.clientid'),
				    "redirect_uri" => $f3->get('JAWBONE.callbackUrl'),
				    "scope" => $f3->get('JAWBONE.scope')
				);
				$f3->reroute($f3->get('JAWBONE.authurl').'?'.http_build_query($rerouteParams));

			}else{

			  	$params = array(
			        "code" => $params['code'],
			        "client_id" => $f3->get('JAWBONE.clientid'),
			        "client_secret" => $f3->get('JAWBONE.clientsecret'),
			        "redirect_uri" => $f3->get('JAWBONE.callbackUrl'),
			        "grant_type" => $f3->get('JAWBONE.grantType')
			    );

				$ch = curl_init($f3->get('JAWBONE.token_url'));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$auth_response = json_decode(curl_exec($ch),true);
				curl_close($ch);

				// Test de l'API
				if(isset($auth_response['access_token'])){
					
					$url = "https://jawbone.com/nudge/api/v.1.0/users/@me";
					$opts = array(
					    'http'=>array(
					            'method'=>"GET",
					            'header'=>"Authorization: Bearer {$auth_response['access_token']}\r\n"
					        )
					);
					$context = stream_context_create($opts);
					$response = file_get_contents($url, false, $context);
					$user = json_decode($response, true);

					var_dump($user);

				}
			}
		}

		function moves_auth($f3){

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			if(!isset($params['code'])){

				$rerouteParams = array(
				    "response_type" => "code",
				    "client_id" => $f3->get('MOVES.clientid'),
				    "redirect_uri" => $f3->get('MOVES.callbackUrl'),
				    "scope" => $f3->get('MOVES.scope')
				);
				$f3->reroute($f3->get('MOVES.authurl').'?'.http_build_query($rerouteParams));

			}else{

			  	$params = array(
			        "code" => $params['code'],
			        "client_id" => $f3->get('MOVES.clientid'),
			        "client_secret" => $f3->get('MOVES.clientsecret'),
			        "redirect_uri" => $f3->get('MOVES.callbackUrl'),
			        "grant_type" => $f3->get('MOVES.grantType')
			    );

				$ch = curl_init($f3->get('MOVES.token_url'));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$auth_response = json_decode(curl_exec($ch),true);
				curl_close($ch);

				// Test de l'API
				if(isset($auth_response['access_token'])){
					
					$url = "https://api.moves-app.com/api/v1/user/profile";
					$opts = array(
					    'http'=>array(
					            'method'=>"GET",
					            'header'=>"Authorization: Bearer {$auth_response['access_token']}\r\n"
					        )
					);
					$context = stream_context_create($opts);
					$response = file_get_contents($url, false, $context);
					$user = json_decode($response, true);

					var_dump($user);

				}
			}
		}

		function runkeeper_auth($f3){

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			if(!isset($params['code'])){

				$rerouteParams = array(
				    "response_type" => "code",
				    "client_id" => $f3->get('RUNKEEPER.clientid'),
				    "redirect_uri" => $f3->get('RUNKEEPER.callbackUrl')
				);
				$f3->reroute($f3->get('RUNKEEPER.authurl').'?'.http_build_query($rerouteParams));

			}else{

			  	$params = array(
			        "code" => $params['code'],
			        "client_id" => $f3->get('RUNKEEPER.clientid'),
			        "client_secret" => $f3->get('RUNKEEPER.clientsecret'),
			        "redirect_uri" => $f3->get('RUNKEEPER.callbackUrl'),
			        "grant_type" => $f3->get('RUNKEEPER.grantType')
			    );

				$ch = curl_init($f3->get('RUNKEEPER.token_url'));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$auth_response = json_decode(curl_exec($ch),true);
				curl_close($ch);

				// Test de l'API
				if(isset($auth_response['access_token'])){
					
					$url = "https://api.runkeeper.com/profile";
					$opts = array(
					    'http'=>array(
					            'method'=>"GET",
					            'header'=>"Authorization: Bearer {$auth_response['access_token']}\r\n".
					            "Accept: application/vnd.com.runkeeper.Profile+json\r\n"
					        )
					);
					$context = stream_context_create($opts);
					$response = file_get_contents($url, false, $context);
					$user = json_decode($response, true);

					var_dump($user);

				}
			}
		}
		
	}

?>