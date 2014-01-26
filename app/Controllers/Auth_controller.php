<?php

	class Auth_controller{

		function _construct(){
		}

		function fitbit_auth($f3){
			$vars = $f3->get('FITBIT');
			$auth_response = $this->oauth_1_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/fitbit');
				exit;
			}
			$fitbit_infos = $this->oauth_1_0_request(array('conskey' => $vars['conskey'], 'conssec' => $vars['conssec'], 'oauth_token' => $auth_response['oauth_token'], 'oauth_token_secret' => $auth_response['oauth_token_secret'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));		
			$user_model = new User_model();
			$user = $user_model->getUserByInputId($f3, array('input_id'=>$auth_response['encoded_user_id'], 'input_name'=>'FITBIT'));
			if($user==null){			
				$name = ((isset($fitbit_infos['user']['fullName']) && valid($fitbit_infos['user']['fullName'],array('','NA',false,null)))?explode(' ', $fitbit_infos['user']['fullName'], 2):null);
				$user_infos = array();
				$user_infos['username'] = (isset($fitbit_infos['user']['nickname']) && valid($fitbit_infos['user']['nickname'],array('','NA'))?$fitbit_infos['user']['nickname']:null);
				$user_infos['firstname'] = (($name==null || (is_array($name) && count($name)==0))?null:$name[0]);
				$user_infos['lastname'] = (($name==null || (is_array($name) && count($name)<2))?null:$name[1]);
				$user_infos['gender'] = (isset($fitbit_infos['user']['gender']) && valid($fitbit_infos['user']['gender'],array('','NA'))?(($fitbit_infos['user']['gender']=='MALE')?0:1):null);
				$user_infos['email'] = null;
				$user_infos['description'] = (isset($fitbit_infos['user']['aboutMe']) && valid($fitbit_infos['user']['aboutMe'],array('','NA'))?$fitbit_infos['user']['aboutMe']:null);
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('auth_response' => $auth_response, 'input_name' => 'FITBIT', 'input_id'=>$auth_response['encoded_user_id']));	
				echo View::instance()->render('registration.html');
			}else{
				echo View::instance()->render('dashboard.html');
			}
		}

		function jawbone_auth($f3){
			$vars = $f3->get('JAWBONE');
			$auth_response = $this->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/jawbone');
				exit;
			}
			$jawbone_infos = $this->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId($f3, array('input_id'=>$jawbone_infos['meta']['user_xid'], 'input_name'=>'JAWBONE'));
			if($user==null){
				$user_infos = array();
				$user_infos['username'] = null;
				$user_infos['firstname'] = (isset($jawbone_infos['data']['first']) && valid($jawbone_infos['data']['first'],array('','NA',false,null))?$jawbone_infos['data']['first']:null);
				$user_infos['lastname'] = (isset($jawbone_infos['data']['last']) && valid($jawbone_infos['data']['last'],array('','NA',false,null))?$jawbone_infos['data']['last']:null);
				$user_infos['gender'] = (isset($jawbone_infos['data']['gender']) && valid($jawbone_infos['data']['gender'],array('','NA',false,null))?(($jawbone_infos['data']['gender']=='MALE')?0:1):null);
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('auth_response' => $auth_response, 'input_name' => 'JAWBONE', 'input_id'=>$jawbone_infos['meta']['user_xid']));
				echo View::instance()->render('registration.html');
			}else{
				echo View::instance()->render('dashboard.html');
			}
		}

		function moves_auth($f3){
			$vars = $f3->get('MOVES');
			$auth_response = $this->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/moves');
				exit;
			}
			// L'API moves ne donne accès à aucune information personnelle
			//$moves_infos = $this->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId($f3, array('input_id'=>$auth_response['user_id'], 'input_name'=>'MOVES'));
			if($user==null){
				$user_infos = array();
				$user_infos['username'] = null;
				$user_infos['firstname'] = null;
				$user_infos['lastname'] = null;
				$user_infos['gender'] = null;
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('auth_response' => $auth_response, 'input_name' => 'MOVES', 'input_id'=>$auth_response['user_id']));
				echo View::instance()->render('registration.html');
			}else{
				echo View::instance()->render('dashboard.html');
			}
		}

		function runkeeper_auth($f3){
			$vars = $f3->get('RUNKEEPER');
			$auth_response = $this->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/runkeeper');
				exit;
			}
			$general_infos = $this->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$runkeeper_infos = $this->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['profile']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId($f3, array('input_id' => $general_infos['userID'], 'input_name'=>'MOVES'));
			if($user==null){
				$name = ((isset($runkeeper_infos['name']) && valid($runkeeper_infos['name'],array('','NA',false,null)))?explode(' ', $runkeeper_infos['name'], 2):null);
				$user_infos = array();
				$user_infos['username'] = null;
				$user_infos['firstname'] = (($name==null || (is_array($name) && count($name)==0))?null:$name[0]);
				$user_infos['lastname'] = (($name==null || (is_array($name) && count($name)<2))?null:$name[1]);
				$user_infos['gender'] = (isset($runkeeper_infos['gender']) && valid($runkeeper_infos['gender'],array('','NA',false,null))?(($runkeeper_infos['gender']=='M')?0:1):null);
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('auth_response' => $auth_response, 'input_name' => 'MOVES', 'input_id' => $general_infos['userID']));
				echo View::instance()->render('registration.html');
			}else{
				echo View::instance()->render('dashboard.html');
			}
		}

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

		function oauth_1_0_request($params){    

		  $oauth = new OAuth($params['conskey'], $params['conssec'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);   
          $oauth->setToken($params['oauth_token'], $params['oauth_token_secret']);
          $oauth->fetch($params['url']);
          $json_response = $oauth->getLastResponse();
          $response = json_decode($json_response, true);

		  return $response;
		}

		function oauth_2_0_request($params){        
			$opts = array(
			   'http'=>array(
			           'method'=>(isset($params['method'])?$params['method']:'GET'),
			           'header'=>"Authorization: Bearer {$params['access_token']}\r\n".
			           (isset($params['accept'])?"Accept: ".$params['accept']."\r\n":"")
			       )
			);
			$context = stream_context_create($opts);
			$json_response = file_get_contents($params['url'], false, $context);
			$response = json_decode($json_response, true);

			return $response;
		}
		
	}

function valid($value, $bans=array()){
	$bool = true;
	if(!isset($value) || empty($value)){
		$bool = false;
	}else{
		foreach($bans as $val){
			if($value==$val){
				$bool = false;
				break;
			}
		}
	}
	return $bool;
}

?>