<?php

	class Auth_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		}

		function jawbone_auth($f3){
			$jawbone_controller = new Jawbone_controller();
			$jawbone_controller->auth($f3);
		}

		function fitbit_auth($f3){
			$fitbit_controller = new Fitbit_controller();
			$fitbit_controller->auth($f3);
		}

		function moves_auth($f3){
			$moves_controller = new Moves_controller();
			$moves_controller->auth($f3);
		}

		function runkeeper_auth($f3){
			$runkeeper_controller = new Runkeeper_controller();
			$runkeeper_controller->auth($f3);
		}

		function auth($f3){
			$this->tpl=array('sync'=>'home.html');
			$errors = array();
			if($f3->exists('POST.username') && $f3->exists('POST.password')){
				$user_model = new User_model();
				$user = $user_model->getUserByUsername(array('username' => $f3->get('POST.username')));
				if($user!=null && $user->user_password == hash('md5', $f3->get('POST.password'), true)){
					$input_model = new Input_model();
					$inputs = $input_model->getInputByUserId(array('user_id' => $user->user_id));
					var_dump($inputs);
					$input = $inputs[0];
					$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $input['user_input_oauth'], 'access_secret_token' => (($input['user_input_oauth_secret']!='NULL')?$input['user_input_oauth_secret']:'')));
				}else{
					$errors['auth'] = 'Erreur d\'authentification.';
				}
			}else{
				$errors['data'] = 'Une erreur s\'est produite lors de l\'envoi du formulaire. Veuillez recommencer.';
			}
			if(count($errors)>0){
				$f3->set('errors', $errors);
			}else{
				$f3->reroute('/');
			}
		}

		function register($f3){
			$infos = array();
			$errors = array();
			if($f3->exists('POST.username') && $f3->exists('POST.password') && $f3->exists('POST.firstname') && $f3->exists('POST.lastname') && $f3->exists('POST.email') && $f3->exists('POST.gender') && $f3->exists('POST.description')){
				$user_model = new User_model();
				$infos = $f3->get('POST');
				if($user_model->getUserByUsername(array('username' => $infos['username']))!=null){
					$errors['username'] = 'Ce pseudonyme est déjà utilisé par un autre membre.';
				}
				if(strlen($infos['password'])<6){
					$errors['password'] = 'Votre mot de passe doit contenir 6 caractères au minimum.';
				}
				if(strlen($infos['firstname'])<2){
					$errors['firstname'] = 'Veuillez vérifier la saisie de votre prénom.';
				}
				if(strlen($infos['lastname'])<2){
					$errors['lastname'] = 'Veuillez vérifier la saisie de votre nom.';
				}
				if(!filter_var($infos['email'], FILTER_VALIDATE_EMAIL)){
					$errors['email'] = 'Veuillez vérifier la saisie de votre adresse mail.';
				}elseif($user_model->getUserByEmail(array('email' => $infos['email']))!=null){
					$errors['email'] = 'Cette adresse mail est déjà utilisée par un autre membre.';
				}
				if($infos['gender']!=0 && $infos['gender']!=1){
					var_dump($infos['gender']);
					$errors['lastname'] = 'Veuillez indiquer votre sexe.';
				}
			}else{
				$errors['data'] = 'Une erreur s\'est produite lors de l\'envoi du formulaire. Veuillez recommencer.';
			}
			if(count($errors)==0){
				$infos['password'] = hash('md5', $infos['password'], true);
				$user = $user_model->createUser($infos);
				$registration_infos = $f3->get('SESSION.registration');
				$input_model = new Input_model();
				$input_model->createInput(array('user_id' => $user->user_id, 'input_key' => $registration_infos['input_id'], 'input_name' => $registration_infos['input_name'], 'oauth' => $registration_infos['access_token'], 'oauth_secret' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:'')));
				$f3->clear('SESSION.registration');
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $registration_infos['access_token'], 'access_secret_token' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:'')));
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $registration_infos['input_id'], 'oauth' => (isset($registration_infos['access_token'])?$registration_infos['access_token']:'NULL')));
				
				$f3->reroute('/');
			}else{
				$f3->set('user_infos', $infos);
				$f3->set('errors', $errors);
			}
		}
		
	}

?>