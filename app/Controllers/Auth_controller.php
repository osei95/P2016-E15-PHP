<?php

	class Auth_controller{

		private $oauth_controller;

		function __construct(){
			$this->oauth_controller = new Oauth_controller();
		}

		function fitbit_auth($f3){
			$vars = $f3->get('FITBIT');
			$auth_response = $this->oauth_controller->oauth_1_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/fitbit');
				exit;
			}
			$fitbit_infos = $this->oauth_controller->oauth_1_0_request(array('conskey' => $vars['conskey'], 'conssec' => $vars['conssec'], 'oauth_token' => $auth_response['oauth_token'], 'oauth_token_secret' => $auth_response['oauth_token_secret'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));		
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
				$f3->set('SESSION.registration', array('access_token' => $auth_response['oauth_token'], 'input_name' => 'FITBIT', 'input_id'=>$auth_response['encoded_user_id']));	
				echo View::instance()->render('registration.html');
			}else{
				$f3->reroute('/');
			}
		}

		function jawbone_auth($f3){
			$vars = $f3->get('JAWBONE');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/jawbone');
				exit;
			}
			$jawbone_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
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
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'input_name' => 'JAWBONE', 'input_id'=>$jawbone_infos['meta']['user_xid']));
				echo View::instance()->render('registration.html');
			}else{
				$f3->set('SESSION.user', array('user_id' => $user['user_id'], 'user_email' => $user['user_email'], 'user_firstname' => $user['user_firstname'], 'user_lastname' => $user['user_lastname'], 'user_key' => $user['user_key'], 'user_gender' => $user['user_gender'], 'user_description' => $user['user_description'], 'access_token' => $auth_response['access_token']));
				$f3->reroute('/');
			}
		}

		function moves_auth($f3){
			$vars = $f3->get('MOVES');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
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
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'input_name' => 'MOVES', 'input_id'=>$auth_response['user_id']));
				echo View::instance()->render('registration.html');
			}else{
				$f3->set('SESSION.user', $user);
				$f3->reroute('/');
			}
		}

		function runkeeper_auth($f3){
			$vars = $f3->get('RUNKEEPER');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/runkeeper');
				exit;
			}
			$general_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$runkeeper_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['profile']));
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
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'input_name' => 'MOVES', 'input_id' => $general_infos['userID']));
				echo View::instance()->render('registration.html');
			}else{
				$f3->set('SESSION.user', $user);
				$f3->reroute('/');
			}
		}

		function register($f3){
			$infos = array();
			if($f3->exists('POST.username') && $f3->exists('POST.firstname') && $f3->exists('POST.lastname') && $f3->exists('POST.email') && $f3->exists('POST.gender') && $f3->exists('POST.description')){
				$user_model = new User_model();
				$errors = array();
				$infos = $f3->get('POST');
				if($user_model->getUserByUsername($f3, array('username' => $infos['username']))!=null){
					$errors['username'] = 'Ce pseudonyme est déjà utilisé par un autre membre.';
				}
				if(strlen($infos['firstname'])<2){
					$errors['firstname'] = 'Veuillez vérifier la saisie de votre prénom.';
				}
				if(strlen($infos['lastname'])<2){
					$errors['lastname'] = 'Veuillez vérifier la saisie de votre nom.';
				}
				if(!filter_var($infos['email'], FILTER_VALIDATE_EMAIL)){
					$errors['email'] = 'Veuillez vérifier la saisie de votre adresse mail.';
				}elseif($user_model->getUserByEmail($f3, array('email' => $infos['email']))!=null){
					$errors['email'] = 'Cette adresse mail est déjà utilisée par un autre membre.';
				}
				if($infos['gender']!=0 && $infos['gender']!=1){
					var_dump($infos['gender']);
					$errors['lastname'] = 'Veuillez indiquer votre sexe.';
				}

				if(count($errors)==0){
					$user = $user_model->newUser($f3, $infos);
					$registration_infos = $f3->get('SESSION.registration');
					$input_model = new Input_model();
					$input_model->newInput($f3, array('user_id' => $user->user_id, 'input_key' => $registration_infos['input_id'], 'input_name' => $registration_infos['input_name']));
					$f3->clear('SESSION.registration');
					$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $registration_infos['access_token']));
					$f3->reroute('/');
				}else{
					$f3->set('user_infos', $infos);
					$f3->set('errors', $errors);
					echo View::instance()->render('registration.html');
				}
			}
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