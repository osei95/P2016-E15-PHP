<?php

	class Oauth_controller{

		function __construct(){}

		function oauth_1_0_auth($f3,$vars){

			$query = parse_url($f3->get('PARAMS')[0],PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);

			$oauth = new OAuth($vars['conskey'], $vars['conssec'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

			if(!isset($params['oauth_token']) || !$f3->exists('SESSION.temp_secret_token')){

				$request_token_info = $oauth->getRequestToken($vars['req_url'], $vars['callbackUrl']);
			    $f3->set('SESSION.temp_secret_token', $request_token_info['oauth_token_secret']);
			    $f3->reroute($vars['authurl'].'?oauth_token='.$request_token_info['oauth_token']);
			    exit;
			}else{

				$oauth->setToken($params['oauth_token'], $f3->get('SESSION.temp_secret_token'));
				$f3->clear('SESSION.temp_secret_token');
				$auth_response = $oauth->getAccessToken($vars['acc_url']);

				return $auth_response;
			}
		}

		function oauth_1_0_request($params){    

		  $oauth = new OAuth($params['conskey'], $params['conssec'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);   
          $oauth->setToken($params['oauth_token'], $params['oauth_token_secret']);
          $oauth->fetch($params['url']);
          $json_response = $oauth->getLastResponse();
          $response = json_decode($json_response, true);

		  return $response;
		}

		function oauth_2_0_auth($f3, $vars){

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
				exit;
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

				return $auth_response;
			}
		}

		function oauth_2_0_refresh_token($f3, $vars){
			$params = array(
		        "client_id" => $vars['clientid'],
		        "client_secret" => $vars['clientsecret'],
		        "grant_type" => $vars['grantType'],
		        "refresh_token" => $vars['refresh_token']
		    );

			$ch = curl_init($vars['token_url']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$auth_response = json_decode(curl_exec($ch),true);
			curl_close($ch);

			return $auth_response;
		}

		function oauth_2_0_request($params){        
			$headers = array('Authorization: Bearer ' . $params['access_token']);
			if(isset($params['accept']))		$headers['Accept'] = $params['accept'];

			$ch = curl_init($params['url']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = json_decode(curl_exec($ch),true);
			curl_close($ch);

			return $response;
		}

	}
?>